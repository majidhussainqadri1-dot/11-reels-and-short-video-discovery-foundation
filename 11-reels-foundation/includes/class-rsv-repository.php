<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Repository {
	public static function find( $id, $public = false ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		if ( $public ) {
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE public_id=%s", (string) $id ), ARRAY_A );
		}
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d", (int) $id ), ARRAY_A );
	}

	public static function public_dto( $row, $include_private = false ) {
		if ( ! $row ) {
			return null;
		}
		$video = RSV_File10::video( (int) $row['video_id'] );
		$data = array(
			'id' => $row['public_id'],
			'video_id' => $video ? ( $video['public_id'] ?? (int) $row['video_id'] ) : (int) $row['video_id'],
			'title' => $row['title'],
			'slug' => $row['slug'],
			'topic' => $row['topic'],
			'language' => $row['language'],
			'caption' => $row['caption'],
			'disclosure' => $row['disclosure'],
			'cover_id' => (int) $row['cover_id'],
			'visibility' => $row['visibility'],
			'status' => $row['status'],
			'owner' => self::owner_dto( (int) $row['owner_id'] ),
			'duration_seconds' => $video ? (int) ( $video['duration_seconds'] ?? 0 ) : 0,
			'url' => home_url( '/reel/' . rawurlencode( $row['public_id'] ) . '/' . rawurlencode( $row['slug'] ) . '/' ),
			'video_url' => $video && ! empty( $video['public_id'] ) ? home_url( '/video/' . rawurlencode( $video['public_id'] ) . '/' . rawurlencode( $video['slug'] ?? '' ) . '/' ) : '',
			'published_at' => $row['published_at'],
			'updated_at' => $row['updated_at'],
			'version' => (int) $row['version'],
		);
		if ( $include_private ) {
			$data['rights_status'] = $row['rights_status'];
			$data['consent_status'] = $row['consent_status'];
			$data['safety_labels'] = RSV_Helpers::json_decode( $row['safety_labels_json'] );
		}
		return $data;
	}

	private static function owner_dto( $user_id ) {
		$user = get_userdata( $user_id );
		return array(
			'id' => (int) $user_id,
			'name' => $user ? $user->display_name : __( 'Publisher', RSV_TEXT_DOMAIN ),
			'profile_url' => apply_filters( 'rsv_profile_url', get_author_posts_url( $user_id ), $user_id ),
			'label' => RSV_Security::publisher_label( $user_id ),
		);
	}

	public static function feed( $args = array() ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		$limit = min( 50, max( 1, absint( $args['limit'] ?? 12 ) ) );
		$params = array( 'published', 'public' );
		$where = "status=%s AND visibility=%s";
		if ( ! empty( $args['topic'] ) && in_array( $args['topic'], RSV_Contracts::TOPICS, true ) ) {
			$where .= ' AND topic=%s';
			$params[] = $args['topic'];
		}
		$cursor = RSV_Helpers::cursor_decode( $args['cursor'] ?? '' );
		if ( $cursor ) {
			$where .= ' AND (updated_at < %s OR (updated_at = %s AND id < %d))';
			$params[] = $cursor[0];
			$params[] = $cursor[0];
			$params[] = $cursor[1];
		}
		$order = 'rank_score DESC, updated_at DESC, id DESC';
		if ( 'latest' === ( $args['sort'] ?? '' ) ) {
			$order = 'published_at DESC, id DESC';
		}
		$params[] = $limit + 1;
		$sql = $wpdb->prepare( "SELECT * FROM $table WHERE $where ORDER BY $order LIMIT %d", $params );
		$rows = $wpdb->get_results( $sql, ARRAY_A );
		$has_more = count( $rows ) > $limit;
		if ( $has_more ) {
			array_pop( $rows );
		}
		$items = array();
		foreach ( $rows as $row ) {
			if ( RSV_File10::publicly_eligible( (int) $row['video_id'] ) ) {
				$items[] = self::public_dto( $row );
			}
		}
		$last = end( $rows );
		return array(
			'items' => $items,
			'next_cursor' => $has_more && $last ? RSV_Helpers::cursor_encode( $last['updated_at'], $last['id'] ) : null,
		);
	}

	public static function update_versioned( $id, $expected_version, $changes, $formats = array() ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		$changes['version'] = (int) $expected_version + 1;
		$changes['updated_at'] = RSV_Helpers::now();
		$sets = array();
		$values = array();
		foreach ( $changes as $key => $value ) {
			$sets[] = preg_replace( '/[^a-z0-9_]/', '', $key ) . '=%s';
			$values[] = is_null( $value ) ? null : (string) $value;
		}
		$values[] = (int) $id;
		$values[] = (int) $expected_version;
		$sql = $wpdb->prepare( "UPDATE $table SET " . implode( ',', $sets ) . ' WHERE id=%d AND version=%d', $values );
		$updated = $wpdb->query( $sql );
		if ( 1 !== $updated ) {
			return RSV_Helpers::error( 'rsv_version_conflict', __( 'This Reel changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
		}
		return self::find( $id );
	}

	public static function history( $user_id, $limit = 100 ) {
		global $wpdb;
		$progress = RSV_Helpers::table( 'progress' );
		$reels = RSV_Helpers::table( 'reels' );
		$limit = min( 200, max( 1, absint( $limit ) ) );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.position_seconds,p.completed,p.replays,p.updated_at,r.* FROM $progress p JOIN $reels r ON r.id=p.reel_id WHERE p.user_id=%d ORDER BY p.updated_at DESC LIMIT %d",
				$user_id,
				$limit
			),
			ARRAY_A
		);
		$out = array();
		foreach ( $rows as $row ) {
			$dto = self::public_dto( $row );
			if ( $dto ) {
				$dto['progress'] = array(
					'position_seconds' => (int) $row['position_seconds'],
					'completed' => (bool) $row['completed'],
					'replays' => (int) $row['replays'],
					'updated_at' => $row['updated_at'],
				);
				$out[] = $dto;
			}
		}
		return $out;
	}
}
