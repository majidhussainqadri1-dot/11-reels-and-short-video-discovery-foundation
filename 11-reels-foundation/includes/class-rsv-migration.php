<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Migration {
	const LOCK_OPTION       = 'rsv_migration_lock';
	const CHECKPOINT_OPTION = 'rsv_migration_checkpoint';
	const SNAPSHOT_OPTION   = 'rsv_pre_migration_snapshot';

	public static function migrate_legacy( $dry_run = false, $limit = 100 ) {
		$limit = min( 500, max( 1, absint( $limit ) ) );
		$lock  = self::acquire_lock();
		if ( is_wp_error( $lock ) ) {
			return $lock;
		}

		try {
			$checkpoint = (array) get_option( self::CHECKPOINT_OPTION, array( 'post_id' => 0, 'history_id' => 0 ) );
			if ( ! $dry_run && ! get_option( self::SNAPSHOT_OPTION ) ) {
				update_option(
					self::SNAPSHOT_OPTION,
					array(
						'created_at'       => RSV_Helpers::now(),
						'source_version'   => (string) get_option( 'srl_version', '' ),
						'source_db_version'=> (string) get_option( 'srl_db_version', '' ),
						'checkpoint'       => $checkpoint,
					),
					false
				);
			}

			$result = array(
				'dry_run'             => (bool) $dry_run,
				'found'               => 0,
				'created'             => 0,
				'history_created'     => 0,
				'quarantined'         => 0,
				'skipped'             => 0,
				'errors'              => array(),
				'next_checkpoint'     => $checkpoint,
			);

			$post_result = self::migrate_reels( $checkpoint, $dry_run, $limit );
			$result       = array_merge( $result, $post_result );
			$remaining    = max( 1, $limit - absint( $post_result['found'] ?? 0 ) );
			$history      = self::migrate_history( $post_result['next_checkpoint'], $dry_run, $remaining );
			$result['history_created'] = absint( $history['created'] ?? 0 );
			$result['skipped']        += absint( $history['skipped'] ?? 0 );
			$result['quarantined']    += absint( $history['quarantined'] ?? 0 );
			$result['errors']          = array_merge( $result['errors'], (array) ( $history['errors'] ?? array() ) );
			$result['next_checkpoint'] = (array) ( $history['next_checkpoint'] ?? $post_result['next_checkpoint'] );

			if ( ! $dry_run ) {
				update_option( self::CHECKPOINT_OPTION, $result['next_checkpoint'], false );
				RSV_Helpers::audit( 'migration', 0, 'legacy_batch', '', 'complete', '', $result, get_current_user_id() );
			}
			return $result;
		} finally {
			self::release_lock( $lock );
		}
	}

	private static function migrate_reels( $checkpoint, $dry_run, $limit ) {
		global $wpdb;
		$after = absint( $checkpoint['post_id'] ?? 0 );
		$statuses = array( 'publish', 'pending', 'draft', 'private' );
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$params = array_merge( array( '_svw_is_reel', '1', 'svw_video' ), $statuses, array( $after, $limit ) );
		$ids = (array) $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID AND pm.meta_key=%s AND pm.meta_value=%s WHERE p.post_type=%s AND p.post_status IN ($placeholders) AND p.ID>%d ORDER BY p.ID ASC LIMIT %d",
				$params
			)
		);
		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );

		$result = array(
			'found'           => count( $ids ),
			'created'         => 0,
			'quarantined'     => 0,
			'skipped'         => 0,
			'errors'          => array(),
			'next_checkpoint' => array( 'post_id' => $after, 'history_id' => absint( $checkpoint['history_id'] ?? 0 ) ),
		);

		foreach ( $ids as $post_id ) {
			$result['next_checkpoint']['post_id'] = $post_id;
			$mapped_video_id = absint( get_post_meta( $post_id, '_rsv_vwlb_video_id', true ) );
			if ( ! $mapped_video_id ) {
				$result['quarantined']++;
				self::mark_source( $post_id, 'quarantined_missing_file10_mapping', $dry_run );
				continue;
			}
			$owner_id = absint( get_post_field( 'post_author', $post_id ) );
			$video    = RSV_File10::validate_for_reel( $mapped_video_id, $owner_id );
			if ( is_wp_error( $video ) ) {
				$result['quarantined']++;
				self::mark_source( $post_id, 'quarantined_' . $video->get_error_code(), $dry_run );
				continue;
			}
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT id FROM ' . RSV_Helpers::table( 'reels' ) . ' WHERE legacy_source_id=%d OR video_id=%d LIMIT 1',
					$post_id,
					$mapped_video_id
				)
			);
			if ( $exists ) {
				$result['skipped']++;
				self::mark_source( $post_id, 'already_migrated', $dry_run );
				continue;
			}
			if ( $dry_run ) {
				$result['created']++;
				continue;
			}

			$topic = sanitize_key( get_post_meta( $post_id, '_svw_category', true ) );
			if ( ! in_array( $topic, RSV_Contracts::TOPICS, true ) ) {
				$topic = 'clinical-learning';
			}
			$status = 'publish' === get_post_status( $post_id ) ? 'review' : 'draft';
			$created = RSV_DB::transaction(
				static function () use ( $wpdb, $post_id, $mapped_video_id, $owner_id, $video, $topic, $status ) {
					$title = RSV_Helpers::text( get_the_title( $post_id ), 255 );
					if ( ! $title ) {
						return RSV_Helpers::error( 'rsv_migration_title_missing', __( 'Legacy Reel title is missing.', RSV_TEXT_DOMAIN ), 422 );
					}
					$public_id = RSV_Helpers::public_id( 'reel' );
					$inserted = $wpdb->insert(
						RSV_Helpers::table( 'reels' ),
						array(
							'public_id'         => $public_id,
							'video_id'          => $mapped_video_id,
							'owner_id'          => $owner_id,
							'legacy_source_id'  => $post_id,
							'title'              => $title,
							'slug'               => sanitize_title( get_post_field( 'post_name', $post_id ) ?: $title . '-' . substr( $public_id, -6 ) ),
							'topic'              => $topic,
							'language'           => RSV_Helpers::text( get_post_meta( $post_id, '_svw_language', true ) ?: 'en-US', 20 ),
							'caption'            => RSV_Helpers::textarea( get_post_field( 'post_excerpt', $post_id ), 4000 ),
							'disclosure'         => '',
							'cover_id'           => absint( get_post_thumbnail_id( $post_id ) ?: ( $video['thumbnail_id'] ?? 0 ) ),
							'visibility'         => 'public',
							'status'             => $status,
							'rights_status'      => RSV_Helpers::enum( $video['rights_status'] ?? 'declared', array( 'declared', 'verified', 'disputed', 'restricted' ), 'declared' ),
							'consent_status'     => RSV_Helpers::enum( $video['consent_status'] ?? 'not_patient_case', array( 'not_patient_case', 'documented', 'anonymized', 'approved', 'missing' ), 'missing' ),
							'safety_labels_json' => '[]',
							'rank_score'         => 0,
							'version'            => 1,
							'published_at'       => null,
							'created_at'         => get_post_time( 'Y-m-d H:i:s', true, $post_id ) ?: RSV_Helpers::now(),
							'updated_at'         => RSV_Helpers::now(),
						)
					);
					if ( 1 !== $inserted ) {
						return RSV_Helpers::error( 'rsv_migration_write_failed', __( 'Legacy Reel could not be migrated.', RSV_TEXT_DOMAIN ), 500 );
					}
					$id = absint( $wpdb->insert_id );
					if ( ! RSV_Helpers::audit( 'reel', $id, 'migrate', '', $status, '', array( 'legacy_source_id' => $post_id ), get_current_user_id() ) ) {
						return RSV_Helpers::error( 'rsv_migration_audit_failed', __( 'Legacy Reel migration evidence could not be stored.', RSV_TEXT_DOMAIN ), 500 );
					}
					return $id;
				}
			);
			if ( is_wp_error( $created ) ) {
				$result['quarantined']++;
				$result['errors'][] = array( 'source_id' => $post_id, 'code' => $created->get_error_code() );
				self::mark_source( $post_id, 'quarantined_' . $created->get_error_code(), false );
				continue;
			}
			update_post_meta( $post_id, '_rsv_migration_status', 'migrated' );
			update_post_meta( $post_id, '_rsv_new_reel_id', absint( $created ) );
			$result['created']++;
		}
		return $result;
	}

	private static function migrate_history( $checkpoint, $dry_run, $limit ) {
		global $wpdb;
		$source = $wpdb->prefix . 'srl_history';
		$result = array( 'created' => 0, 'skipped' => 0, 'quarantined' => 0, 'errors' => array(), 'next_checkpoint' => $checkpoint );
		if ( $source !== $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $source ) ) ) ) {
			return $result;
		}
		$after = absint( $checkpoint['history_id'] ?? 0 );
		$rows  = (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $source WHERE id>%d ORDER BY id ASC LIMIT %d", $after, $limit ), ARRAY_A );
		foreach ( $rows as $row ) {
			$result['next_checkpoint']['history_id'] = absint( $row['id'] );
			$reel_id = absint( $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . RSV_Helpers::table( 'reels' ) . ' WHERE legacy_source_id=%d LIMIT 1', absint( $row['reel_id'] ) ) ) );
			if ( ! $reel_id ) {
				$result['quarantined']++;
				continue;
			}
			if ( $dry_run ) {
				$result['created']++;
				continue;
			}
			$sql = $wpdb->prepare(
				'INSERT INTO ' . RSV_Helpers::table( 'progress' ) . ' (user_id,reel_id,position_seconds,completed,replays,last_event_bucket,version,updated_at) VALUES (%d,%d,%d,%d,%d,%d,1,%s) ON DUPLICATE KEY UPDATE position_seconds=GREATEST(position_seconds,VALUES(position_seconds)),completed=GREATEST(completed,VALUES(completed)),replays=GREATEST(replays,VALUES(replays)),updated_at=GREATEST(updated_at,VALUES(updated_at))',
				absint( $row['user_id'] ),
				$reel_id,
				absint( $row['progress'] ),
				! empty( $row['completed'] ) ? 1 : 0,
				absint( $row['replays'] ),
				(int) floor( absint( $row['progress'] ) / 30 ),
				RSV_Helpers::is_mysql_datetime( $row['updated_at'] ) ? $row['updated_at'] : RSV_Helpers::now()
			);
			if ( false === $wpdb->query( $sql ) ) {
				$result['errors'][] = array( 'history_id' => absint( $row['id'] ), 'code' => 'history_write_failed' );
				$result['quarantined']++;
			} else {
				$result['created']++;
			}
		}
		return $result;
	}

	private static function mark_source( $post_id, $status, $dry_run ) {
		if ( ! $dry_run ) {
			update_post_meta( absint( $post_id ), '_rsv_migration_status', sanitize_key( $status ) );
		}
	}

	private static function acquire_lock() {
		$token = RSV_Helpers::public_id( 'migration' );
		$lock  = array( 'token' => $token, 'expires_at' => time() + 15 * MINUTE_IN_SECONDS );
		if ( add_option( self::LOCK_OPTION, $lock, '', 'no' ) ) {
			return $token;
		}
		$current = (array) get_option( self::LOCK_OPTION, array() );
		if ( absint( $current['expires_at'] ?? 0 ) < time() ) {
			delete_option( self::LOCK_OPTION );
			if ( add_option( self::LOCK_OPTION, $lock, '', 'no' ) ) {
				return $token;
			}
		}
		return RSV_Helpers::error( 'rsv_migration_locked', __( 'Another migration batch is already running.', RSV_TEXT_DOMAIN ), 409 );
	}

	private static function release_lock( $token ) {
		$current = (array) get_option( self::LOCK_OPTION, array() );
		if ( hash_equals( (string) ( $current['token'] ?? '' ), (string) $token ) ) {
			delete_option( self::LOCK_OPTION );
		}
	}

	public static function reconcile( $limit = 100 ) {
		if ( ! RSV_File10::ready() ) {
			return array( 'checked' => 0, 'restricted' => 0, 'advanced' => 0 );
		}
		global $wpdb;
		$rows = (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM " . RSV_Helpers::table( 'reels' ) . " WHERE status IN ('published','media_processing','review') ORDER BY updated_at ASC LIMIT %d",
				min( 500, max( 1, absint( $limit ) ) )
			),
			ARRAY_A
		);
		$result = array( 'checked' => 0, 'restricted' => 0, 'advanced' => 0 );
		foreach ( $rows as $row ) {
			$result['checked']++;
			$video = RSV_File10::video( $row['video_id'] );
			if ( ! $video || in_array( $video['status'] ?? '', array( 'restricted', 'removed', 'failed' ), true ) ) {
				if ( RSV_State_Machine::allowed( $row['status'], 'restricted' ) ) {
					$updated = RSV_Repository::update_versioned( $row['id'], $row['version'], array( 'status' => 'restricted' ) );
					if ( ! is_wp_error( $updated ) ) {
						$result['restricted']++;
						RSV_Helpers::audit( 'reel', $row['id'], 'reconcile', $row['status'], 'restricted', 'File 10 unavailable or restricted', array(), 0 );
					}
				}
				continue;
			}
			if ( 'media_processing' === $row['status'] && 'published' === ( $video['status'] ?? '' ) ) {
				$updated = RSV_Repository::update_versioned( $row['id'], $row['version'], array( 'status' => 'review' ) );
				if ( ! is_wp_error( $updated ) ) {
					$result['advanced']++;
					RSV_Helpers::audit( 'reel', $row['id'], 'reconcile', 'media_processing', 'review', 'File 10 media ready', array(), 0 );
				}
			}
		}
		return $result;
	}

	public static function rollback_legacy_cutover() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot run this rollback.', RSV_TEXT_DOMAIN ), 403 );
		}
		$affected = (int) apply_filters( 'rsv_before_legacy_rollback', 0 );
		update_option( 'rsv_legacy_cutover_enabled', false, false );
		delete_option( self::CHECKPOINT_OPTION );
		RSV_Helpers::audit( 'migration', 0, 'rollback', 'cutover', 'legacy_read_enabled', '', array( 'downstream_affected' => $affected ) );
		return array( 'rolled_back' => true, 'new_data_preserved' => true );
	}
}
