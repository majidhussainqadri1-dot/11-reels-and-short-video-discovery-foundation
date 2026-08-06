<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Migration {
	public static function migrate_legacy( $dry_run = false, $limit = 100 ) {
		$query = new WP_Query( array(
			'post_type' => 'svw_video',
			'post_status' => array( 'publish','pending','draft','private' ),
			'posts_per_page' => min( 500, max( 1, absint( $limit ) ) ),
			'meta_key' => '_svw_is_reel',
			'meta_value' => '1',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		) );
		$result = array( 'found' => count( $query->posts ), 'created' => 0, 'quarantined' => 0, 'skipped' => 0 );
		foreach ( $query->posts as $post_id ) {
			$mapped_video_id = absint( get_post_meta( $post_id, '_rsv_vwlb_video_id', true ) );
			if ( ! $mapped_video_id ) {
				$result['quarantined']++;
				if ( ! $dry_run ) update_post_meta( $post_id, '_rsv_migration_status', 'quarantined_missing_file10_mapping' );
				continue;
			}
			$video = RSV_File10::validate_for_reel( $mapped_video_id, (int) get_post_field( 'post_author', $post_id ) );
			if ( is_wp_error( $video ) ) {
				$result['quarantined']++;
				if ( ! $dry_run ) update_post_meta( $post_id, '_rsv_migration_status', $video->get_error_code() );
				continue;
			}
			global $wpdb;
			$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . RSV_Helpers::table( 'reels' ) . ' WHERE video_id=%d', $mapped_video_id ) );
			if ( $exists ) {
				$result['skipped']++;
				continue;
			}
			if ( ! $dry_run ) {
				$topic = sanitize_key( get_post_meta( $post_id, '_svw_category', true ) );
				if ( ! in_array( $topic, RSV_Contracts::TOPICS, true ) ) $topic = 'clinical-learning';
				$wpdb->insert( RSV_Helpers::table( 'reels' ), array(
					'public_id' => RSV_Helpers::public_id( 'reel' ),
					'video_id' => $mapped_video_id,
					'owner_id' => (int) get_post_field( 'post_author', $post_id ),
					'title' => get_the_title( $post_id ),
					'slug' => sanitize_title( get_post_field( 'post_name', $post_id ) ),
					'topic' => $topic,
					'language' => 'en-US',
					'caption' => RSV_Helpers::textarea( get_post_field( 'post_excerpt', $post_id ), 4000 ),
					'disclosure' => '',
					'cover_id' => get_post_thumbnail_id( $post_id ),
					'visibility' => 'public',
					'status' => 'review',
					'rights_status' => $video['rights_status'],
					'consent_status' => $video['consent_status'],
					'safety_labels_json' => '[]',
					'rank_score' => 0,
					'version' => 1,
					'created_at' => get_post_time( 'Y-m-d H:i:s', true, $post_id ),
					'updated_at' => RSV_Helpers::now(),
				) );
				update_post_meta( $post_id, '_rsv_migration_status', 'migrated' );
				update_post_meta( $post_id, '_rsv_new_reel_id', (int) $wpdb->insert_id );
			}
			$result['created']++;
		}
		return $result;
	}

	public static function reconcile( $limit = 100 ) {
		if ( ! RSV_File10::ready() ) return array( 'checked' => 0, 'restricted' => 0 );
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . RSV_Helpers::table( 'reels' ) . " WHERE status IN ('published','media_processing','review') ORDER BY updated_at ASC LIMIT %d", $limit ), ARRAY_A );
		$result = array( 'checked' => 0, 'restricted' => 0 );
		foreach ( $rows as $row ) {
			$result['checked']++;
			$video = RSV_File10::video( $row['video_id'] );
			if ( ! $video || in_array( $video['status'] ?? '', array( 'restricted','removed','failed' ), true ) ) {
				if ( RSV_State_Machine::allowed( $row['status'], 'restricted' ) ) {
					RSV_Repository::update_versioned( $row['id'], $row['version'], array( 'status' => 'restricted' ) );
					$result['restricted']++;
				}
			}
		}
		return $result;
	}
}
