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
		$video_id = absint( $data['video_id'] ?? 0 );
		$video = RSV_File10::validate_for_reel( $video_id, get_current_user_id() );
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
		global $wpdb;
		$now = RSV_Helpers::now();
		$public_id = RSV_Helpers::public_id( 'reel' );
		$slug = sanitize_title( $data['slug'] ?? $title . '-' . substr( $public_id, -6 ) );
		$inserted = $wpdb->insert(
			RSV_Helpers::table( 'reels' ),
			array(
				'public_id' => $public_id,
				'video_id' => $video_id,
				'owner_id' => get_current_user_id(),
				'title' => $title,
				'slug' => $slug,
				'topic' => $topic,
				'language' => RSV_Helpers::text( $data['language'] ?? 'en-US', 20 ),
				'caption' => RSV_Helpers::textarea( $data['caption'] ?? '', 4000 ),
				'disclosure' => RSV_Helpers::text( $data['disclosure'] ?? '', 500 ),
				'cover_id' => absint( $data['cover_id'] ?? ( $video['thumbnail_id'] ?? 0 ) ),
				'visibility' => RSV_Helpers::enum( $data['visibility'] ?? 'public', RSV_Contracts::VISIBILITIES, 'public' ),
				'status' => 'draft',
				'rights_status' => RSV_Helpers::enum( $video['rights_status'] ?? 'declared', array( 'declared','verified','disputed','restricted' ), 'declared' ),
				'consent_status' => RSV_Helpers::enum( $video['consent_status'] ?? 'not_patient_case', array( 'not_patient_case','documented','anonymized','approved','missing' ), 'missing' ),
				'safety_labels_json' => RSV_Helpers::json_encode( array_values( array_unique( array_map( 'sanitize_key', (array) ( $data['safety_labels'] ?? array() ) ) ) ) ),
				'rank_score' => 0,
				'version' => 1,
				'created_at' => $now,
				'updated_at' => $now,
			)
		);
		if ( ! $inserted ) {
			return RSV_Helpers::error( 'rsv_database_error', __( 'The Reel could not be created.', RSV_TEXT_DOMAIN ), 500 );
		}
		$id = (int) $wpdb->insert_id;
		RSV_Helpers::audit( 'reel', $id, 'create', '', 'draft', '', array( 'video_id' => $video_id ) );
		RSV_Helpers::outbox( 'ReelCreated', 'reel', $id, array( 'public_id' => $public_id, 'video_id' => $video_id ) );
		$response = RSV_Repository::public_dto( RSV_Repository::find( $id ), true );
		RSV_Security::idempotency_finish( 'create_reel', $idempotency_key, $response );
		return $response;
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
		$updated = RSV_Repository::update_versioned( $reel['id'], $expected_version, array( 'status' => $to ) );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}
		RSV_Helpers::audit( 'reel', $reel['id'], 'submit', $reel['status'], $to );
		RSV_Helpers::outbox( 'ReelSubmitted', 'reel', $reel['id'], array( 'status' => $to ) );
		return RSV_Repository::public_dto( $updated, true );
	}

	public static function publish( $id, $expected_version ) {
		$reel = RSV_Repository::find( $id );
		if ( ! $reel || ! RSV_Security::can( RSV_Contracts::CAP_PUBLISH, $reel, 'publish_reel' ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		if ( 'review' !== $reel['status'] && 'restricted' !== $reel['status'] ) {
			return RSV_Helpers::error( 'rsv_not_reviewable', __( 'The Reel is not ready for publication.', RSV_TEXT_DOMAIN ), 409 );
		}
		$video = RSV_File10::validate_for_reel( $reel['video_id'], $reel['owner_id'] );
		if ( is_wp_error( $video ) || 'published' !== ( $video['status'] ?? '' ) ) {
			return is_wp_error( $video ) ? $video : RSV_Helpers::error( 'rsv_video_not_published', __( 'File 10 video must be published first.', RSV_TEXT_DOMAIN ), 422 );
		}
		if ( ! $reel['cover_id'] ) {
			return RSV_Helpers::error( 'rsv_cover_required', __( 'A cover image is required.', RSV_TEXT_DOMAIN ), 422 );
		}
		$assert = RSV_State_Machine::assert( $reel['status'], 'published' );
		if ( is_wp_error( $assert ) ) {
			return $assert;
		}
		$score = self::base_rank_score( $reel, $video );
		$updated = RSV_Repository::update_versioned(
			$reel['id'],
			$expected_version,
			array( 'status' => 'published', 'published_at' => RSV_Helpers::now(), 'rank_score' => $score )
		);
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}
		RSV_Helpers::audit( 'reel', $reel['id'], 'publish', $reel['status'], 'published', 'Publication gates passed' );
		RSV_Helpers::outbox( 'ReelPublished', 'reel', $reel['id'], array( 'public_id' => $reel['public_id'], 'video_id' => $reel['video_id'] ) );
		return RSV_Repository::public_dto( $updated, true );
	}

	private static function base_rank_score( $reel, $video ) {
		$score = 50.0;
		if ( 'verified' === ( $video['rights_status'] ?? '' ) ) {
			$score += 10;
		}
		if ( in_array( $video['consent_status'] ?? '', array( 'not_patient_case','approved','anonymized' ), true ) ) {
			$score += 10;
		}
		if ( ! empty( $reel['caption'] ) ) {
			$score += 5;
		}
		return (float) apply_filters( 'rsv_base_rank_score', $score, $reel, $video );
	}

	public static function progress( $id, $seconds, $duration, $completed = false, $rapid = false ) {
		if ( ! is_user_logged_in() ) {
			return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to save Reel history.', RSV_TEXT_DOMAIN ), 401 );
		}
		$rate = RSV_Security::rate_limit( 'progress', 180, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) {
			return $rate;
		}
		$reel = RSV_Repository::find( $id );
		if ( ! $reel || 'published' !== $reel['status'] || ! RSV_File10::publicly_eligible( $reel['video_id'] ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		$video = RSV_File10::video( $reel['video_id'] );
		$authoritative_duration = (int) ( $video['duration_seconds'] ?? 0 );
		$seconds = max( 0, min( absint( $seconds ), $authoritative_duration ) );
		$is_complete = $completed || ( $authoritative_duration > 0 && $seconds >= (int) floor( $authoritative_duration * 0.9 ) );
		global $wpdb;
		$table = RSV_Helpers::table( 'progress' );
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE user_id=%d AND reel_id=%d", get_current_user_id(), $reel['id'] ), ARRAY_A );
		$replays = $existing ? (int) $existing['replays'] : 0;
		if ( $existing && ! empty( $existing['completed'] ) && $seconds < max( 15, (int) floor( (int) $existing['position_seconds'] / 2 ) ) ) {
			$replays++;
		}
		$data = array(
			'position_seconds' => $seconds,
			'completed' => $is_complete ? 1 : 0,
			'replays' => $replays,
			'version' => $existing ? (int) $existing['version'] + 1 : 1,
			'updated_at' => RSV_Helpers::now(),
		);
		if ( $existing ) {
			$wpdb->update( $table, $data, array( 'id' => $existing['id'] ) );
		} else {
			$data['user_id'] = get_current_user_id();
			$data['reel_id'] = $reel['id'];
			$wpdb->insert( $table, $data );
		}
		RSV_File10::progress( $reel['video_id'], $seconds, $authoritative_duration );
		self::impression( $reel['id'], $seconds, $is_complete, $rapid );
		if ( $is_complete ) {
			RSV_Helpers::outbox( 'ReelCompleted', 'reel', $reel['id'], array( 'user_id' => get_current_user_id() ) );
		} else {
			RSV_Helpers::outbox( 'ReelViewed', 'reel', $reel['id'], array( 'seconds' => $seconds ) );
		}
		return array( 'saved' => true, 'position_seconds' => $seconds, 'completed' => $is_complete, 'replays' => $replays );
	}

	private static function impression( $reel_id, $seconds, $completed, $rapid ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'impressions' );
		$viewer = hash( 'sha256', get_current_user_id() . '|' . wp_salt( 'auth' ) );
		$day = gmdate( 'Y-m-d' );
		$sql = $wpdb->prepare(
			"INSERT INTO $table (reel_id,viewer_hash,viewed_seconds,completed,swiped_rapidly,day_key,created_at)
			VALUES (%d,%s,%d,%d,%d,%s,%s)
			ON DUPLICATE KEY UPDATE viewed_seconds=GREATEST(viewed_seconds,VALUES(viewed_seconds)), completed=GREATEST(completed,VALUES(completed)), swiped_rapidly=GREATEST(swiped_rapidly,VALUES(swiped_rapidly))",
			$reel_id, $viewer, $seconds, $completed ? 1 : 0, $rapid ? 1 : 0, $day, RSV_Helpers::now()
		);
		$wpdb->query( $sql );
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
		if ( ! $reel || ! in_array( $reel['status'], array( 'published','restricted' ), true ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		$allowed = array( 'medical-claim','patient-privacy','harassment','copyright','spam','other' );
		$reason = RSV_Helpers::enum( $reason, $allowed, '' );
		if ( ! $reason ) {
			return RSV_Helpers::error( 'rsv_report_reason', __( 'Choose a valid report reason.', RSV_TEXT_DOMAIN ), 422 );
		}
		$idem = RSV_Security::idempotency_begin( 'report_reel', $idempotency_key, array( 'id' => $id, 'reason' => $reason, 'details' => $details ) );
		if ( is_wp_error( $idem ) || ! empty( $idem['replay'] ) ) {
			return is_wp_error( $idem ) ? $idem : $idem['response'];
		}
		global $wpdb;
		$public_id = RSV_Helpers::public_id( 'rpt' );
		$wpdb->insert(
			RSV_Helpers::table( 'reports' ),
			array(
				'public_id' => $public_id,
				'reel_id' => $reel['id'],
				'reporter_id' => get_current_user_id(),
				'reason_code' => $reason,
				'details' => RSV_Helpers::textarea( $details, 4000 ),
				'status' => 'submitted',
					'action_code' => '',
				'reviewer_id' => 0,
				'appeal_text' => '',
				'version' => 1,
				'created_at' => RSV_Helpers::now(),
				'updated_at' => RSV_Helpers::now(),
			)
		);
		$report_id = (int) $wpdb->insert_id;
		RSV_Helpers::audit( 'report', $report_id, 'submit', '', 'submitted', $reason );
		RSV_Helpers::outbox( 'ReelReportSubmitted', 'report', $report_id, array( 'reel_id' => $reel['id'], 'reason' => $reason ) );
		$response = array( 'id' => $public_id, 'status' => 'submitted' );
		RSV_Security::idempotency_finish( 'report_reel', $idempotency_key, $response );
		return $response;
	}

	public static function moderate( $report_id, $decision, $reason, $expected_version ) {
		if ( ! RSV_Security::can( RSV_Contracts::CAP_MODERATE, null, 'moderate_reel' ) ) {
			return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot moderate Reels.', RSV_TEXT_DOMAIN ), 403 );
		}
		global $wpdb;
		$table = RSV_Helpers::table( 'reports' );
		$report = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d", $report_id ), ARRAY_A );
		if ( ! $report || (int) $report['version'] !== (int) $expected_version ) {
			return RSV_Helpers::error( 'rsv_report_conflict', __( 'The report changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
		}
		$decision = RSV_Helpers::enum( $decision, array( 'no_action','restrict','remove','close' ), '' );
		if ( ! $decision || ! trim( (string) $reason ) ) {
			return RSV_Helpers::error( 'rsv_reason_required', __( 'A valid decision and reason are required.', RSV_TEXT_DOMAIN ), 422 );
		}
		$reel = RSV_Repository::find( $report['reel_id'] );
		if ( ! $reel ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		$next_report = 'no_action' === $decision ? 'no_action' : ( 'close' === $decision ? 'closed' : 'action' );
		$wpdb->update(
			$table,
			array(
				'status' => $next_report,
				'action_code' => $decision,
				'reviewer_id' => get_current_user_id(),
				'version' => (int) $expected_version + 1,
				'updated_at' => RSV_Helpers::now(),
			),
			array( 'id' => $report['id'], 'version' => $expected_version )
		);
		if ( in_array( $decision, array( 'restrict','remove' ), true ) ) {
			$to = 'restrict' === $decision ? 'restricted' : 'removed';
			if ( RSV_State_Machine::allowed( $reel['status'], $to ) ) {
				RSV_Repository::update_versioned( $reel['id'], $reel['version'], array( 'status' => $to ) );
			RSV_Helpers::outbox( 'restrict' === $decision ? 'ReelRestricted' : 'ReelRemoved', 'reel', $reel['id'], array( 'reason_code' => $report['reason_code'] ) );
			}
		}
		RSV_Helpers::audit( 'report', $report['id'], 'moderate', $report['status'], $next_report, $reason, array( 'decision' => $decision ) );
		return array( 'status' => $next_report, 'decision' => $decision, 'version' => (int) $expected_version + 1 );
	}

	public static function insights( $owner_id ) {
		global $wpdb;
		$reels = RSV_Helpers::table( 'reels' );
		$imp = RSV_Helpers::table( 'impressions' );
		$sql = $wpdb->prepare(
			"SELECT r.public_id,r.title,COUNT(i.id) views,SUM(i.completed) completions,SUM(i.swiped_rapidly) rapid_swipes,COALESCE(AVG(i.viewed_seconds),0) average_seconds
			FROM $reels r LEFT JOIN $imp i ON i.reel_id=r.id WHERE r.owner_id=%d GROUP BY r.id ORDER BY r.updated_at DESC LIMIT 100",
			$owner_id
		);
		return $wpdb->get_results( $sql, ARRAY_A );
	}
}
