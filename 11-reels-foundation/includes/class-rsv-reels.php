<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Reels {
	public static function create( $data, $idempotency_key ) {
		if ( ! RSV_Security::can( RSV_Contracts::CAP_SUBMIT, null, 'create_reel' ) ) {
			return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot create Reels.', RSV_TEXT_DOMAIN ), 403 );
		}
		$rate = RSV_Security::rate_limit( 'create', 20, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$data     = is_array( $data ) ? $data : array();
		$video_id = absint( $data['video_id'] ?? 0 );
		$video    = RSV_File10::validate_for_reel( $video_id, get_current_user_id() );
		if ( is_wp_error( $video ) ) {
			return $video;
		}
		$title = RSV_Helpers::text( $data['title'] ?? ( $video['title'] ?? '' ), 255 );
		$topic = RSV_Helpers::enum( $data['topic'] ?? '', RSV_Contracts::TOPICS, '' );
		if ( ! $title || ! $topic ) {
			return RSV_Helpers::error( 'rsv_metadata_required', __( 'Title and an approved educational topic are required.', RSV_TEXT_DOMAIN ), 422 );
		}
		$idem = RSV_Security::idempotency_begin( 'create_reel', $idempotency_key, $data );
		if ( is_wp_error( $idem ) || ! empty( $idem['replay'] ) ) {
			return is_wp_error( $idem ) ? $idem : $idem['response'];
		}

		$result = RSV_DB::transaction(
			static function () use ( $data, $video, $video_id, $title, $topic, $idempotency_key ) {
				global $wpdb;
				$now       = RSV_Helpers::now();
				$public_id = RSV_Helpers::public_id( 'reel' );
				$slug      = sanitize_title( $data['slug'] ?? $title . '-' . substr( $public_id, -6 ) );
				$inserted  = $wpdb->insert(
					RSV_Helpers::table( 'reels' ),
					array(
						'public_id'         => $public_id,
						'video_id'          => $video_id,
						'owner_id'          => get_current_user_id(),
						'legacy_source_id'  => 0,
						'title'              => $title,
						'slug'               => $slug,
						'topic'              => $topic,
						'language'           => RSV_Helpers::text( $data['language'] ?? 'en-US', 20 ),
						'caption'            => RSV_Helpers::textarea( $data['caption'] ?? '', 4000 ),
						'disclosure'         => RSV_Helpers::text( $data['disclosure'] ?? '', 500 ),
						'cover_id'           => absint( $data['cover_id'] ?? ( $video['thumbnail_id'] ?? 0 ) ),
						'visibility'         => RSV_Helpers::enum( $data['visibility'] ?? 'public', RSV_Contracts::VISIBILITIES, 'public' ),
						'status'             => 'draft',
						'rights_status'      => RSV_Helpers::enum( $video['rights_status'] ?? 'declared', array( 'declared', 'verified', 'disputed', 'restricted' ), 'declared' ),
						'consent_status'     => RSV_Helpers::enum( $video['consent_status'] ?? 'not_patient_case', array( 'not_patient_case', 'documented', 'anonymized', 'approved', 'missing' ), 'missing' ),
						'safety_labels_json' => RSV_Helpers::json_encode( array_values( array_unique( array_map( 'sanitize_key', (array) ( $data['safety_labels'] ?? array() ) ) ) ) ),
						'rank_score'         => 0,
						'version'            => 1,
						'published_at'       => null,
						'created_at'         => $now,
						'updated_at'         => $now,
					),
					array( '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s', '%s' )
				);
				if ( 1 !== $inserted ) {
					return RSV_Helpers::error( 'rsv_database_error', __( 'The Reel could not be created.', RSV_TEXT_DOMAIN ), 500 );
				}
				$id = absint( $wpdb->insert_id );
				if ( ! RSV_Helpers::audit( 'reel', $id, 'create', '', 'draft', '', array( 'video_id' => $video_id ) ) || ! RSV_Helpers::outbox( 'ReelCreated', 'reel', $id, array( 'public_id' => $public_id, 'video_id' => $video_id ) ) ) {
					return RSV_Helpers::error( 'rsv_evidence_write_failed', __( 'The Reel could not be created with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				$response = RSV_Repository::public_dto( RSV_Repository::find( $id ), true );
				if ( ! RSV_Security::idempotency_finish( 'create_reel', $idempotency_key, $response ) ) {
					return RSV_Helpers::error( 'rsv_idempotency_finish_failed', __( 'The Reel could not be safely finalized.', RSV_TEXT_DOMAIN ), 500 );
				}
				return $response;
			}
		);
		if ( is_wp_error( $result ) ) {
			RSV_Security::idempotency_fail( 'create_reel', $idempotency_key );
		}
		return $result;
	}

	public static function submit( $id, $expected_version ) {
		$reel = RSV_Repository::find( $id );
		if ( ! $reel || ! RSV_Security::can( RSV_Contracts::CAP_SUBMIT, $reel, 'submit_reel' ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		$video = RSV_File10::validate_for_reel( $reel['video_id'], $reel['owner_id'] );
		if ( is_wp_error( $video ) ) {
			return $video;
		}
		$to = 'published' === ( $video['status'] ?? '' ) ? 'review' : 'media_processing';
		$assert = RSV_State_Machine::assert( $reel['status'], $to );
		if ( is_wp_error( $assert ) ) {
			return $assert;
		}
		return RSV_DB::transaction(
			static function () use ( $reel, $expected_version, $to ) {
				$updated = RSV_Repository::update_versioned( $reel['id'], $expected_version, array( 'status' => $to ) );
				if ( is_wp_error( $updated ) ) {
					return $updated;
				}
				if ( ! RSV_Helpers::audit( 'reel', $reel['id'], 'submit', $reel['status'], $to ) || ! RSV_Helpers::outbox( 'ReelSubmitted', 'reel', $reel['id'], array( 'status' => $to ) ) ) {
					return RSV_Helpers::error( 'rsv_submit_evidence_failed', __( 'The Reel could not be submitted with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return RSV_Repository::public_dto( $updated, true );
			}
		);
	}

	public static function publish( $id, $expected_version ) {
		$reel = RSV_Repository::find( $id );
		if ( ! $reel || ! RSV_Security::can( RSV_Contracts::CAP_PUBLISH, $reel, 'publish_reel' ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		if ( ! in_array( $reel['status'], array( 'review', 'restricted' ), true ) ) {
			return RSV_Helpers::error( 'rsv_not_reviewable', __( 'The Reel is not ready for publication.', RSV_TEXT_DOMAIN ), 409 );
		}
		$video = RSV_File10::validate_for_reel( $reel['video_id'], $reel['owner_id'] );
		if ( is_wp_error( $video ) || 'published' !== ( $video['status'] ?? '' ) ) {
			return is_wp_error( $video ) ? $video : RSV_Helpers::error( 'rsv_video_not_published', __( 'File 10 video must be published first.', RSV_TEXT_DOMAIN ), 422 );
		}
		if ( ! absint( $reel['cover_id'] ) ) {
			return RSV_Helpers::error( 'rsv_cover_required', __( 'A cover image is required.', RSV_TEXT_DOMAIN ), 422 );
		}
		if ( ! trim( (string) $reel['caption'] ) && (bool) apply_filters( 'rsv_caption_text_required', false, $reel, $video ) ) {
			return RSV_Helpers::error( 'rsv_caption_required', __( 'A reviewed caption or transcript is required.', RSV_TEXT_DOMAIN ), 422 );
		}
		$assert = RSV_State_Machine::assert( $reel['status'], 'published' );
		if ( is_wp_error( $assert ) ) {
			return $assert;
		}
		return RSV_DB::transaction(
			static function () use ( $reel, $expected_version, $video ) {
				$updated = RSV_Repository::update_versioned(
					$reel['id'],
					$expected_version,
					array( 'status' => 'published', 'published_at' => RSV_Helpers::now(), 'rank_score' => self::base_rank_score( $reel, $video ) )
				);
				if ( is_wp_error( $updated ) ) {
					return $updated;
				}
				if ( ! RSV_Helpers::audit( 'reel', $reel['id'], 'publish', $reel['status'], 'published', 'Publication gates passed' ) || ! RSV_Helpers::outbox( 'ReelPublished', 'reel', $reel['id'], array( 'public_id' => $reel['public_id'], 'video_id' => $reel['video_id'] ) ) ) {
					return RSV_Helpers::error( 'rsv_publish_evidence_failed', __( 'The Reel could not be published with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return RSV_Repository::public_dto( $updated, true );
			}
		);
	}

	private static function base_rank_score( $reel, $video ) {
		$score = 50.0;
		$score += 'verified' === ( $video['rights_status'] ?? '' ) ? 10 : 0;
		$score += in_array( $video['consent_status'] ?? '', array( 'not_patient_case', 'approved', 'anonymized' ), true ) ? 10 : 0;
		$score += ! empty( $reel['caption'] ) ? 5 : 0;
		$score -= in_array( 'medical-claim-risk', RSV_Helpers::json_decode( $reel['safety_labels_json'] ?? '[]' ), true ) ? 15 : 0;
		return (float) apply_filters( 'rsv_base_rank_score', $score, $reel, $video );
	}

	public static function recalculate_rank( $id ) {
		$reel = RSV_Repository::find( $id );
		$video = $reel ? RSV_File10::video( $reel['video_id'] ) : null;
		if ( ! $reel || ! $video ) {
			return false;
		}
		global $wpdb;
		$imp = RSV_Helpers::table( 'impressions' );
		$metrics = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(*) views,COALESCE(AVG(completed),0) completion_rate,COALESCE(AVG(swiped_rapidly),0) rapid_rate FROM $imp WHERE reel_id=%d", $id ), ARRAY_A );
		$score = self::base_rank_score( $reel, $video );
		if ( absint( $metrics['views'] ?? 0 ) >= 5 ) {
			$score += min( 20, (float) $metrics['completion_rate'] * 20 );
			$score -= min( 20, (float) $metrics['rapid_rate'] * 20 );
		}
		$updated = RSV_Repository::update_versioned( $id, $reel['version'], array( 'rank_score' => round( $score, 4 ) ) );
		return ! is_wp_error( $updated );
	}

	public static function start_view_session( $id ) {
		if ( ! is_user_logged_in() ) {
			return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to save Reel history.', RSV_TEXT_DOMAIN ), 401 );
		}
		$rate = RSV_Security::rate_limit( 'view_session', 120, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$reel = RSV_Repository::find( $id );
		if ( ! RSV_Security::can_view_reel( $reel ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		global $wpdb;
		$now = RSV_Helpers::now();
		$public_id = RSV_Helpers::public_id( 'view' );
		$inserted = $wpdb->insert(
			RSV_Helpers::table( 'view_sessions' ),
			array(
				'public_id'     => $public_id,
				'user_id'       => get_current_user_id(),
				'reel_id'       => $reel['id'],
				'started_at'    => $now,
				'last_position' => 0,
				'last_ping_at'  => $now,
				'completed'     => 0,
				'expires_at'    => gmdate( 'Y-m-d H:i:s', time() + 4 * HOUR_IN_SECONDS ),
			),
			array( '%s', '%d', '%d', '%s', '%d', '%s', '%d', '%s' )
		);
		return 1 === $inserted ? array( 'session_id' => $public_id, 'expires_in' => 4 * HOUR_IN_SECONDS ) : RSV_Helpers::error( 'rsv_view_session_failed', __( 'Viewing progress is temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
	}

	public static function progress( $id, $session_id, $seconds ) {
		if ( ! is_user_logged_in() ) {
			return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to save Reel history.', RSV_TEXT_DOMAIN ), 401 );
		}
		$rate = RSV_Security::rate_limit( 'progress', 180, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$reel = RSV_Repository::find( $id );
		if ( ! RSV_Security::can_view_reel( $reel ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		$video = RSV_File10::video( $reel['video_id'] );
		$duration = absint( $video['duration_seconds'] ?? 0 );
		$seconds  = min( max( 0, absint( $seconds ) ), $duration );
		$session_id = RSV_Helpers::text( $session_id, 80 );
		if ( ! $session_id ) {
			return RSV_Helpers::error( 'rsv_view_session_required', __( 'Restart playback before saving progress.', RSV_TEXT_DOMAIN ), 409 );
		}

		return RSV_DB::transaction(
			static function () use ( $reel, $duration, $seconds, $session_id ) {
				global $wpdb;
				$sessions = RSV_Helpers::table( 'view_sessions' );
				$session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $sessions WHERE public_id=%s AND user_id=%d AND reel_id=%d AND expires_at>=%s FOR UPDATE", $session_id, get_current_user_id(), $reel['id'], RSV_Helpers::now() ), ARRAY_A );
				if ( ! $session ) {
					return RSV_Helpers::error( 'rsv_view_session_invalid', __( 'The viewing session expired. Restart playback.', RSV_TEXT_DOMAIN ), 409 );
				}
				$elapsed = max( 0, time() - strtotime( $session['started_at'] . ' UTC' ) );
				$maximum_plausible = min( $duration, max( absint( $session['last_position'] ) + 30, $elapsed * 3 + 30 ) );
				$bounded_seconds = min( $seconds, $maximum_plausible );
				$is_complete = $duration > 0 && $bounded_seconds >= (int) floor( $duration * 0.9 ) && $elapsed >= min( 30, max( 10, (int) floor( $duration * 0.1 ) ) );
				$rapid = $elapsed < 2 && $bounded_seconds < 3;

				$progress_table = RSV_Helpers::table( 'progress' );
				$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $progress_table WHERE user_id=%d AND reel_id=%d FOR UPDATE", get_current_user_id(), $reel['id'] ), ARRAY_A );
				$was_complete = $existing && ! empty( $existing['completed'] );
				$replays = $existing ? absint( $existing['replays'] ) : 0;
				if ( $existing && $was_complete && $bounded_seconds < max( 15, (int) floor( absint( $existing['position_seconds'] ) / 2 ) ) ) {
					$replays++;
				}
				$bucket = (int) floor( $bounded_seconds / 30 );
				$data = array(
					'position_seconds' => $bounded_seconds,
					'completed'        => $is_complete || $was_complete ? 1 : 0,
					'replays'          => $replays,
					'last_event_bucket'=> max( $bucket, absint( $existing['last_event_bucket'] ?? 0 ) ),
					'version'          => $existing ? absint( $existing['version'] ) + 1 : 1,
					'updated_at'       => RSV_Helpers::now(),
				);
				$write = $existing
					? $wpdb->update( $progress_table, $data, array( 'id' => $existing['id'] ), array( '%d', '%d', '%d', '%d', '%d', '%s' ), array( '%d' ) )
					: $wpdb->insert( $progress_table, array_merge( $data, array( 'user_id' => get_current_user_id(), 'reel_id' => $reel['id'] ) ), array( '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d' ) );
				if ( false === $write ) {
					return RSV_Helpers::error( 'rsv_progress_write_failed', __( 'Viewing progress could not be saved.', RSV_TEXT_DOMAIN ), 500 );
				}
				$file10 = RSV_File10::progress( $reel['video_id'], $bounded_seconds, $duration );
				if ( is_wp_error( $file10 ) ) {
					return $file10;
				}
				$session_write = $wpdb->update(
					$sessions,
					array( 'last_position' => $bounded_seconds, 'last_ping_at' => RSV_Helpers::now(), 'completed' => $is_complete || ! empty( $session['completed'] ) ? 1 : 0 ),
					array( 'id' => $session['id'] ),
					array( '%d', '%s', '%d' ),
					array( '%d' )
				);
				if ( false === $session_write || ! self::impression( $reel['id'], $bounded_seconds, $is_complete || $was_complete, $rapid ) ) {
					return RSV_Helpers::error( 'rsv_progress_evidence_failed', __( 'Viewing progress could not be saved.', RSV_TEXT_DOMAIN ), 500 );
				}
				$previous_bucket = absint( $existing['last_event_bucket'] ?? 0 );
				$event_ok = true;
				if ( $is_complete && ! $was_complete ) {
					$event_ok = RSV_Helpers::outbox( 'ReelCompleted', 'reel', $reel['id'], array( 'viewer_ref' => RSV_Helpers::opaque_user_ref( get_current_user_id() ), 'session_ref' => substr( hash( 'sha256', $session_id ), 0, 16 ) ) );
				} elseif ( $bucket > $previous_bucket ) {
					$event_ok = RSV_Helpers::outbox( 'ReelViewed', 'reel', $reel['id'], array( 'seconds_bucket' => $bucket * 30, 'viewer_ref' => RSV_Helpers::opaque_user_ref( get_current_user_id() ) ) );
				}
				if ( ! $event_ok ) {
					return RSV_Helpers::error( 'rsv_progress_event_failed', __( 'Viewing progress could not be saved with complete event evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return array( 'saved' => true, 'position_seconds' => $bounded_seconds, 'completed' => $is_complete || $was_complete, 'replays' => $replays );
			}
		);
	}

	private static function impression( $reel_id, $seconds, $completed, $rapid ) {
		global $wpdb;
		$table  = RSV_Helpers::table( 'impressions' );
		$viewer = hash_hmac( 'sha256', (string) get_current_user_id(), wp_salt( 'auth' ) );
		$day    = gmdate( 'Y-m-d' );
		$sql = $wpdb->prepare(
			"INSERT INTO $table (reel_id,viewer_hash,viewed_seconds,completed,swiped_rapidly,day_key,created_at)
			 VALUES (%d,%s,%d,%d,%d,%s,%s)
			 ON DUPLICATE KEY UPDATE viewed_seconds=GREATEST(viewed_seconds,VALUES(viewed_seconds)),completed=GREATEST(completed,VALUES(completed)),swiped_rapidly=GREATEST(swiped_rapidly,VALUES(swiped_rapidly))",
			$reel_id, $viewer, $seconds, $completed ? 1 : 0, $rapid ? 1 : 0, $day, RSV_Helpers::now()
		);
		return false !== $wpdb->query( $sql );
	}

	public static function interact( $id, $type ) {
		if ( ! is_user_logged_in() ) {
			return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to use this action.', RSV_TEXT_DOMAIN ), 401 );
		}
		$rate = RSV_Security::rate_limit( 'interact', 240, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$reel = RSV_Repository::find( $id );
		if ( ! RSV_Security::can_view_reel( $reel ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		return RSV_File10::interact( $reel['video_id'], $type );
	}

	public static function report( $id, $reason, $details, $idempotency_key ) {
		if ( ! is_user_logged_in() ) {
			return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to report a Reel.', RSV_TEXT_DOMAIN ), 401 );
		}
		$rate = RSV_Security::rate_limit( 'report', 20, DAY_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$reel = RSV_Repository::find( $id );
		if ( ! RSV_Security::can_view_reel( $reel ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		$reason  = RSV_Helpers::enum( $reason, RSV_Contracts::REPORT_REASONS, '' );
		$details = RSV_Helpers::textarea( $details, 4000 );
		if ( ! $reason || strlen( trim( $details ) ) < 10 ) {
			return RSV_Helpers::error( 'rsv_report_incomplete', __( 'Choose a valid reason and provide at least ten characters of useful detail.', RSV_TEXT_DOMAIN ), 422 );
		}
		$payload = array( 'id' => $reel['public_id'], 'reason' => $reason, 'details' => $details );
		$idem = RSV_Security::idempotency_begin( 'report_reel', $idempotency_key, $payload );
		if ( is_wp_error( $idem ) || ! empty( $idem['replay'] ) ) {
			return is_wp_error( $idem ) ? $idem : $idem['response'];
		}
		$result = RSV_DB::transaction(
			static function () use ( $reel, $reason, $details, $idempotency_key ) {
				global $wpdb;
				$now = RSV_Helpers::now();
				$public_id = RSV_Helpers::public_id( 'rpt' );
				$inserted = $wpdb->insert(
					RSV_Helpers::table( 'reports' ),
					array(
						'public_id'        => $public_id,
						'reel_id'          => $reel['id'],
						'reporter_id'      => get_current_user_id(),
						'reason_code'      => $reason,
						'details'          => $details,
						'status'           => 'submitted',
						'action_code'      => '',
						'resolution_reason'=> '',
						'reviewer_id'      => 0,
						'appeal_text'      => '',
						'appellant_id'     => 0,
						'appealed_at'      => null,
						'version'          => 1,
						'created_at'       => $now,
						'updated_at'       => $now,
					),
					array( '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%d', '%s', '%s' )
				);
				if ( 1 !== $inserted ) {
					return RSV_Helpers::error( 'rsv_report_write_failed', __( 'The report could not be recorded.', RSV_TEXT_DOMAIN ), 500 );
				}
				$report_id = absint( $wpdb->insert_id );
				if ( ! RSV_Helpers::audit( 'report', $report_id, 'submit', '', 'submitted', $reason ) || ! RSV_Helpers::outbox( 'ReelReportSubmitted', 'report', $report_id, array( 'reel_id' => $reel['id'], 'reason' => $reason ) ) ) {
					return RSV_Helpers::error( 'rsv_report_evidence_failed', __( 'The report could not be recorded with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				$response = array( 'id' => $public_id, 'status' => 'submitted', 'version' => 1 );
				if ( ! RSV_Security::idempotency_finish( 'report_reel', $idempotency_key, $response ) ) {
					return RSV_Helpers::error( 'rsv_idempotency_finish_failed', __( 'The report could not be safely finalized.', RSV_TEXT_DOMAIN ), 500 );
				}
				return $response;
			}
		);
		if ( is_wp_error( $result ) ) {
			RSV_Security::idempotency_fail( 'report_reel', $idempotency_key );
		}
		return $result;
	}

	public static function appeal( $public_id, $text, $expected_version ) {
		if ( ! is_user_logged_in() ) {
			return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to appeal a report decision.', RSV_TEXT_DOMAIN ), 401 );
		}
		$text = RSV_Helpers::textarea( $text, 4000 );
		if ( strlen( trim( $text ) ) < 20 ) {
			return RSV_Helpers::error( 'rsv_appeal_incomplete', __( 'Provide at least twenty characters explaining the appeal.', RSV_TEXT_DOMAIN ), 422 );
		}
		return RSV_DB::transaction(
			static function () use ( $public_id, $text, $expected_version ) {
				global $wpdb;
				$report = RSV_Repository::report( $public_id, true, true );
				$reel   = $report ? RSV_Repository::find( $report['reel_id'] ) : null;
				$reporter_appeal = $report && 'no_action' === $report['status'] && absint( $report['reporter_id'] ) === get_current_user_id();
				$owner_appeal    = $report && 'action' === $report['status'] && $reel && absint( $reel['owner_id'] ) === get_current_user_id();
				if ( ! $report || ( ! $reporter_appeal && ! $owner_appeal ) ) {
					return RSV_Helpers::error( 'rsv_report_not_found', __( 'Report not found.', RSV_TEXT_DOMAIN ), 404 );
				}
				if ( absint( $report['version'] ) !== absint( $expected_version ) ) {
					return RSV_Helpers::error( 'rsv_report_conflict', __( 'The report changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
				}
				$assert = RSV_State_Machine::assert_report( $report['status'], 'appealed' );
				if ( is_wp_error( $assert ) || ! empty( $report['appeal_text'] ) || strtotime( $report['updated_at'] . ' UTC' ) < time() - 30 * DAY_IN_SECONDS ) {
					return is_wp_error( $assert ) ? $assert : RSV_Helpers::error( 'rsv_appeal_unavailable', __( 'This report decision is no longer eligible for appeal.', RSV_TEXT_DOMAIN ), 409 );
				}
				$updated = $wpdb->update(
					RSV_Helpers::table( 'reports' ),
					array( 'status' => 'appealed', 'appeal_text' => $text, 'appellant_id' => get_current_user_id(), 'appealed_at' => RSV_Helpers::now(), 'version' => absint( $expected_version ) + 1, 'updated_at' => RSV_Helpers::now() ),
					array( 'id' => $report['id'], 'version' => $expected_version ),
					array( '%s', '%s', '%d', '%s', '%d', '%s' ),
					array( '%d', '%d' )
				);
				if ( 1 !== $updated ) {
					return RSV_Helpers::error( 'rsv_report_conflict', __( 'The report changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
				}
				if ( ! RSV_Helpers::audit( 'report', $report['id'], 'appeal', $report['status'], 'appealed' ) || ! RSV_Helpers::outbox( 'ReelReportAppealed', 'report', $report['id'], array( 'reel_id' => $report['reel_id'] ) ) ) {
					return RSV_Helpers::error( 'rsv_appeal_evidence_failed', __( 'The appeal could not be saved with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return array( 'id' => $report['public_id'], 'status' => 'appealed', 'version' => absint( $expected_version ) + 1 );
			}
		);
	}

	public static function moderate( $report_id, $decision, $reason, $expected_version, $public_id = false ) {
		if ( ! RSV_Security::can( RSV_Contracts::CAP_MODERATE, null, 'moderate_reel' ) ) {
			return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot moderate Reels.', RSV_TEXT_DOMAIN ), 403 );
		}
		$decision = RSV_Helpers::enum( $decision, RSV_Contracts::MODERATION_DECISIONS, '' );
		$reason   = RSV_Helpers::textarea( $reason, 2000 );
		if ( ! $decision || strlen( trim( $reason ) ) < 10 ) {
			return RSV_Helpers::error( 'rsv_reason_required', __( 'A valid decision and a meaningful reason are required.', RSV_TEXT_DOMAIN ), 422 );
		}
		return RSV_DB::transaction(
			static function () use ( $report_id, $decision, $reason, $expected_version, $public_id ) {
				global $wpdb;
				$report = RSV_Repository::report( $report_id, (bool) $public_id, true );
				if ( ! $report || absint( $report['version'] ) !== absint( $expected_version ) ) {
					return RSV_Helpers::error( 'rsv_report_conflict', __( 'The report changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
				}
				if ( ! in_array( $report['status'], array( 'submitted', 'triaged', 'appealed' ), true ) ) {
					return RSV_Helpers::error( 'rsv_report_closed', __( 'This report is no longer open for moderation.', RSV_TEXT_DOMAIN ), 409 );
				}
				$reel = RSV_Repository::find( $report['reel_id'] );
				if ( ! $reel ) {
					return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
				}
				$next_report = 'no_action' === $decision || 'restore' === $decision ? 'no_action' : ( 'close' === $decision ? 'closed' : 'action' );
				$assert = RSV_State_Machine::assert_report( $report['status'], $next_report );
				if ( is_wp_error( $assert ) ) {
					return $assert;
				}
				if ( in_array( $decision, array( 'restrict', 'remove', 'restore' ), true ) ) {
					$to = 'restrict' === $decision ? 'restricted' : ( 'remove' === $decision ? 'removed' : 'published' );
					if ( 'restore' === $decision ) {
						$video = RSV_File10::validate_for_reel( $reel['video_id'], $reel['owner_id'] );
						if ( is_wp_error( $video ) || 'published' !== ( $video['status'] ?? '' ) ) {
							return is_wp_error( $video ) ? $video : RSV_Helpers::error( 'rsv_restore_media_invalid', __( 'The Reel cannot be restored until File 10 media is published and eligible.', RSV_TEXT_DOMAIN ), 422 );
						}
					}
					$reel_assert = RSV_State_Machine::assert( $reel['status'], $to );
					if ( is_wp_error( $reel_assert ) ) {
						return $reel_assert;
					}
					$reel_updated = RSV_Repository::update_versioned( $reel['id'], $reel['version'], array( 'status' => $to ) );
					if ( is_wp_error( $reel_updated ) ) {
						return $reel_updated;
					}
					if ( ! RSV_Helpers::outbox( 'restrict' === $decision ? 'ReelRestricted' : ( 'remove' === $decision ? 'ReelRemoved' : 'ReelRestored' ), 'reel', $reel['id'], array( 'reason_code' => $report['reason_code'] ) ) ) {
						return RSV_Helpers::error( 'rsv_moderation_event_failed', __( 'The moderation action could not be recorded with complete event evidence.', RSV_TEXT_DOMAIN ), 500 );
					}
				}
				$updated = $wpdb->update(
					RSV_Helpers::table( 'reports' ),
					array(
						'status'            => $next_report,
						'action_code'       => $decision,
						'resolution_reason' => $reason,
						'reviewer_id'       => get_current_user_id(),
						'version'           => absint( $expected_version ) + 1,
						'updated_at'        => RSV_Helpers::now(),
					),
					array( 'id' => $report['id'], 'version' => $expected_version ),
					array( '%s', '%s', '%s', '%d', '%d', '%s' ),
					array( '%d', '%d' )
				);
				if ( 1 !== $updated ) {
					return RSV_Helpers::error( 'rsv_report_conflict', __( 'The report changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
				}
				if ( ! RSV_Helpers::audit( 'report', $report['id'], 'moderate', $report['status'], $next_report, $reason, array( 'decision' => $decision ) ) || ! RSV_Helpers::outbox( 'ReelReportResolved', 'report', $report['id'], array( 'decision' => $decision, 'status' => $next_report ) ) ) {
					return RSV_Helpers::error( 'rsv_moderation_evidence_failed', __( 'The moderation decision could not be recorded with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return array( 'status' => $next_report, 'decision' => $decision, 'version' => absint( $expected_version ) + 1 );
			}
		);
	}


	public static function clear_history( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || $user_id !== get_current_user_id() ) {
			return RSV_Helpers::error( 'rsv_history_forbidden', __( 'You cannot clear this history.', RSV_TEXT_DOMAIN ), 403 );
		}
		return RSV_DB::transaction(
			static function () use ( $user_id ) {
				global $wpdb;
				$progress = $wpdb->delete( RSV_Helpers::table( 'progress' ), array( 'user_id' => $user_id ), array( '%d' ) );
				$sessions = $wpdb->delete( RSV_Helpers::table( 'view_sessions' ), array( 'user_id' => $user_id ), array( '%d' ) );
				if ( false === $progress || false === $sessions || ! RSV_Helpers::audit( 'history', $user_id, 'clear', '', 'complete', '', array( 'progress_rows' => (int) $progress, 'session_rows' => (int) $sessions ) ) ) {
					return RSV_Helpers::error( 'rsv_history_clear_failed', __( 'Private Reel history could not be cleared completely.', RSV_TEXT_DOMAIN ), 500 );
				}
				return array( 'removed' => (int) $progress, 'sessions_removed' => (int) $sessions );
			}
		);
	}

	public static function insights( $owner_id ) {
		global $wpdb;
		$reels = RSV_Helpers::table( 'reels' );
		$imp   = RSV_Helpers::table( 'impressions' );
		$sql = $wpdb->prepare(
			"SELECT r.public_id,r.title,COUNT(i.id) views,COALESCE(SUM(i.completed),0) completions,COALESCE(SUM(i.swiped_rapidly),0) rapid_swipes,COALESCE(AVG(i.viewed_seconds),0) average_seconds
			 FROM $reels r LEFT JOIN $imp i ON i.reel_id=r.id WHERE r.owner_id=%d GROUP BY r.id ORDER BY r.updated_at DESC LIMIT 100",
			absint( $owner_id )
		);
		$rows = (array) $wpdb->get_results( $sql, ARRAY_A );
		$threshold = max( 3, absint( apply_filters( 'rsv_insights_privacy_threshold', 3 ) ) );
		foreach ( $rows as &$row ) {
			$row['insufficient_data'] = absint( $row['views'] ) < $threshold;
			if ( $row['insufficient_data'] ) {
				$row['views'] = $row['completions'] = $row['rapid_swipes'] = 0;
				$row['average_seconds'] = 0;
			}
		}
		unset( $row );
		return $rows;
	}
}
