<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Top20_Responses_Trait {
	private static function response_row( $id, $public = true ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'responses' );
		return $public
			? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE public_id=%s LIMIT 1", (string) $id ), ARRAY_A )
			: $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d LIMIT 1", absint( $id ) ), ARRAY_A );
	}

	public static function create_response( $source_public_id, $data, $idempotency_key = '' ) {
		if ( ! RSV_Security::can( RSV_Contracts::CAP_SUBMIT, null, 'create_response' ) ) {
			return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot create Reel responses.', RSV_TEXT_DOMAIN ), 403 );
		}
		$rate = RSV_Security::rate_limit( 'create_response', 30, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) return $rate;

		$data     = is_array( $data ) ? $data : array();
		$source   = RSV_Repository::find( RSV_Helpers::text( $source_public_id, 80 ), true );
		$response = RSV_Repository::find( RSV_Helpers::text( $data['response_reel_id'] ?? '', 80 ), true );
		if ( ! RSV_Security::can_view_reel( $source, 0 ) || ! $response || absint( $response['owner_id'] ) !== get_current_user_id() || absint( $source['id'] ) === absint( $response['id'] ) ) {
			return RSV_Helpers::error( 'rsv_response_reel_invalid', __( 'Select a different eligible response Reel that you own.', RSV_TEXT_DOMAIN ), 422 );
		}
		$context = self::context_row( absint( $source['id'] ) );
		if ( ! $context || empty( $context['allow_response'] ) ) {
			return RSV_Helpers::error( 'rsv_response_disabled', __( 'The source publisher has not enabled responses for this Reel.', RSV_TEXT_DOMAIN ), 409 );
		}
		$source_video = RSV_File10::video( absint( $source['video_id'] ) );
		$patient_case = 'not_patient_case' !== ( $source_video['consent_status'] ?? '' );
		$declared     = ! empty( $data['patient_reuse_consent'] );
		if ( $patient_case && ( ! $declared || ! apply_filters( 'rsv_patient_reuse_consent_valid', false, $source, $response, $data ) ) ) {
			return RSV_Helpers::error( 'rsv_patient_reuse_consent_required', __( 'Verified patient-media reuse consent is required for this response.', RSV_TEXT_DOMAIN ), 422 );
		}
		$attribution = RSV_Helpers::text(
			$data['attribution_text'] ?? sprintf( __( 'Response to “%s” by %s', RSV_TEXT_DOMAIN ), $source['title'], get_the_author_meta( 'display_name', absint( $source['owner_id'] ) ) ),
			500
		);
		if ( '' === trim( $attribution ) ) {
			return RSV_Helpers::error( 'rsv_response_attribution_required', __( 'A meaningful attribution is required.', RSV_TEXT_DOMAIN ), 422 );
		}

		$payload = array(
			'source_reel_id'   => (string) $source['public_id'],
			'response_reel_id' => (string) $response['public_id'],
			'attribution_text' => $attribution,
			'patient_reuse'    => $declared,
		);
		$idem_key = RSV_Helpers::text( $idempotency_key, 120 );
		$idem     = RSV_Security::idempotency_begin( 'create_response', $idem_key, $payload );
		if ( is_wp_error( $idem ) ) return $idem;
		if ( ! empty( $idem['replay'] ) ) return $idem['response'];

		$result = RSV_DB::transaction(
			static function () use ( $source, $response, $attribution, $declared, $idem_key ) {
				global $wpdb;
				$public_id = RSV_Helpers::public_id( 'response' );
				$insert = $wpdb->insert(
					RSV_Helpers::table( 'responses' ),
					array(
						'public_id'            => $public_id,
						'source_reel_id'       => absint( $source['id'] ),
						'response_reel_id'     => absint( $response['id'] ),
						'owner_id'             => get_current_user_id(),
						'attribution_text'     => $attribution,
						'patient_reuse_consent'=> $declared ? 1 : 0,
						'status'               => 'review',
						'version'              => 1,
						'published_at'         => null,
						'created_at'           => RSV_Helpers::now(),
						'updated_at'           => RSV_Helpers::now(),
					),
					array( '%s','%d','%d','%d','%s','%d','%s','%d','%s','%s','%s' )
				);
				if ( 1 !== $insert ) {
					return RSV_Helpers::error( 'rsv_response_write_failed', __( 'The response could not be created. This Reel may already be linked as a response.', RSV_TEXT_DOMAIN ), 409 );
				}
				$id = absint( $wpdb->insert_id );
				if ( ! RSV_Helpers::audit( 'response', $id, 'create', '', 'review', $attribution ) || ! RSV_Helpers::outbox( 'ReelResponseCreated', 'response', $id, array( 'source_reel' => $source['public_id'], 'response_reel' => $response['public_id'] ) ) ) {
					return RSV_Helpers::error( 'rsv_response_evidence_failed', __( 'The response could not be created with complete evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				$response_data = array( 'id' => $public_id, 'status' => 'review' );
				if ( ! RSV_Security::idempotency_finish( 'create_response', $idem_key, $response_data ) ) {
					return RSV_Helpers::error( 'rsv_response_finalize_failed', __( 'The response could not be finalized safely.', RSV_TEXT_DOMAIN ), 500 );
				}
				return $response_data;
			}
		);
		if ( is_wp_error( $result ) ) RSV_Security::idempotency_fail( 'create_response', $idem_key );
		return $result;
	}

	public static function publish_response( $public_id, $expected_version ) {
		$row = self::response_row( $public_id, true );
		if ( ! $row || ! RSV_Security::can( RSV_Contracts::CAP_PUBLISH, $row, 'publish_response' ) ) {
			return RSV_Helpers::error( 'rsv_response_not_found', __( 'Response not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		if ( 'review' !== (string) $row['status'] ) {
			return RSV_Helpers::error( 'rsv_response_not_reviewable', __( 'This response is not awaiting publication review.', RSV_TEXT_DOMAIN ), 409 );
		}
		if ( absint( $row['version'] ) !== absint( $expected_version ) ) {
			return RSV_Helpers::error( 'rsv_version_conflict', __( 'This response changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
		}
		$source   = RSV_Repository::find( absint( $row['source_reel_id'] ) );
		$response = RSV_Repository::find( absint( $row['response_reel_id'] ) );
		if ( ! RSV_Security::can_view_reel( $source, 0 ) || ! RSV_Security::can_view_reel( $response, 0 ) ) {
			return RSV_Helpers::error( 'rsv_response_dependency_changed', __( 'The source or response Reel is unavailable.', RSV_TEXT_DOMAIN ), 409 );
		}
		$context = self::context_row( absint( $source['id'] ) );
		if ( ! $context || empty( $context['allow_response'] ) ) {
			return RSV_Helpers::error( 'rsv_response_disabled', __( 'Responses are no longer enabled for the source Reel.', RSV_TEXT_DOMAIN ), 409 );
		}
		$source_video = RSV_File10::video( absint( $source['video_id'] ) );
		$patient_case = 'not_patient_case' !== ( $source_video['consent_status'] ?? '' );
		if ( $patient_case && ( empty( $row['patient_reuse_consent'] ) || ! apply_filters( 'rsv_patient_reuse_consent_still_valid', false, $source, $response, $row ) ) ) {
			return RSV_Helpers::error( 'rsv_patient_reuse_consent_changed', __( 'Patient-media reuse consent must be revalidated before publication.', RSV_TEXT_DOMAIN ), 409 );
		}

		return RSV_DB::transaction(
			static function () use ( $row ) {
				global $wpdb;
				$now = RSV_Helpers::now();
				$ok = $wpdb->update(
					RSV_Helpers::table( 'responses' ),
					array( 'status' => 'published', 'published_at' => $now, 'version' => absint( $row['version'] ) + 1, 'updated_at' => $now ),
					array( 'id' => absint( $row['id'] ), 'version' => absint( $row['version'] ), 'status' => 'review' ),
					array( '%s','%s','%d','%s' ),
					array( '%d','%d','%s' )
				);
				if ( 1 !== $ok ) return RSV_Helpers::error( 'rsv_response_publish_failed', __( 'The response changed or could not be published.', RSV_TEXT_DOMAIN ), 409 );
				if ( ! RSV_Helpers::audit( 'response', absint( $row['id'] ), 'publish', 'review', 'published' ) || ! RSV_Helpers::outbox( 'ReelResponsePublished', 'response', absint( $row['id'] ), array( 'public_id' => $row['public_id'] ) ) ) {
					return RSV_Helpers::error( 'rsv_response_publish_evidence_failed', __( 'The response could not be published with complete evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return self::response_dto( self::response_row( $row['id'], false ) );
			}
		);
	}

	private static function response_dto( $row ) {
		if ( ! is_array( $row ) || 'published' !== (string) ( $row['status'] ?? '' ) ) return null;
		$source   = RSV_Repository::find( absint( $row['source_reel_id'] ) );
		$response = RSV_Repository::find( absint( $row['response_reel_id'] ) );
		if ( ! RSV_Security::can_view_reel( $source, 0 ) || ! RSV_Security::can_view_reel( $response, 0 ) ) return null;
		$source_video = RSV_File10::video( absint( $source['video_id'] ) );
		$patient_case = 'not_patient_case' !== ( $source_video['consent_status'] ?? '' );
		if ( $patient_case && ( empty( $row['patient_reuse_consent'] ) || ! apply_filters( 'rsv_patient_reuse_consent_still_valid', false, $source, $response, $row ) ) ) return null;
		return array(
			'id'           => (string) $row['public_id'],
			'attribution'  => (string) $row['attribution_text'],
			'published_at' => (string) $row['published_at'],
			'source'       => RSV_Repository::public_dto( $source ),
			'response'     => RSV_Repository::public_dto( $response ),
		);
	}

	public static function public_responses( $source_public_id, $limit = 20 ) {
		$source = RSV_Repository::find( $source_public_id, true );
		if ( ! RSV_Security::can_view_reel( $source, 0 ) ) return array();
		global $wpdb;
		$rows = (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM " . RSV_Helpers::table( 'responses' ) . " WHERE source_reel_id=%d AND status='published' ORDER BY published_at DESC,id DESC LIMIT %d", absint( $source['id'] ), min( 50, max( 1, absint( $limit ) ) ) ),
			ARRAY_A
		);
		$out = array();
		foreach ( $rows as $row ) {
			$dto = self::response_dto( $row );
			if ( $dto ) $out[] = $dto;
		}
		return $out;
	}

	public static function record_signal( $reel, $signal ) {
		if ( ! is_user_logged_in() ) return RSV_Helpers::error( 'rsv_signal_login_required', __( 'Sign in before recording creator-value signals.', RSV_TEXT_DOMAIN ), 401 );
		$signal = RSV_Helpers::enum( $signal, RSV_Contracts::VALUE_SIGNALS, '' );
		if ( ! $signal || ! RSV_Security::can_view_reel( $reel ) ) return RSV_Helpers::error( 'rsv_signal_invalid', __( 'The value signal could not be recorded.', RSV_TEXT_DOMAIN ), 422 );
		$rate = RSV_Security::rate_limit( 'value_signal_' . $signal, 60, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) return $rate;
		$column = array( 'source-open' => 'source_opens', 'share' => 'shares', 'natural-stop' => 'natural_stops' )[ $signal ];
		$viewer = hash_hmac( 'sha256', (string) get_current_user_id(), wp_salt( 'auth' ) );
		$day = gmdate( 'Y-m-d' );
		return RSV_DB::transaction(
			static function () use ( $reel, $signal, $column, $viewer, $day ) {
				global $wpdb;
				$receipts = RSV_Helpers::table( 'value_signal_receipts' );
				$inserted = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO $receipts (reel_id,viewer_hash,signal_key,day_key,created_at) VALUES (%d,%s,%s,%s,%s)", absint( $reel['id'] ), $viewer, $signal, $day, RSV_Helpers::now() ) );
				if ( false === $inserted ) return RSV_Helpers::error( 'rsv_signal_receipt_failed', __( 'The value signal could not be recorded safely.', RSV_TEXT_DOMAIN ), 500 );
				if ( 0 === (int) $inserted ) return array( 'recorded' => false, 'deduplicated' => true );
				$table = RSV_Helpers::table( 'value_signals' );
				$sql = $wpdb->prepare( "INSERT INTO $table (reel_id,day_key,$column,updated_at) VALUES (%d,%s,1,%s) ON DUPLICATE KEY UPDATE $column=$column+1,updated_at=VALUES(updated_at)", absint( $reel['id'] ), $day, RSV_Helpers::now() );
				if ( false === $wpdb->query( $sql ) ) return RSV_Helpers::error( 'rsv_signal_write_failed', __( 'The value signal could not be recorded.', RSV_TEXT_DOMAIN ), 500 );
				return array( 'recorded' => true, 'deduplicated' => false );
			}
		);
	}

	public static function value_insights( $owner_id ) {
		global $wpdb;
		$reels       = RSV_Helpers::table( 'reels' );
		$impressions = RSV_Helpers::table( 'impressions' );
		$signals     = RSV_Helpers::table( 'value_signals' );
		$reports     = RSV_Helpers::table( 'reports' );
		$rows = (array) $wpdb->get_results( $wpdb->prepare(
			"SELECT r.id,r.public_id,r.title,COUNT(DISTINCT i.viewer_hash) views,COALESCE(SUM(i.completed),0) completions,COALESCE(AVG(i.viewed_seconds),0) average_seconds,COALESCE(SUM(i.swiped_rapidly),0) rapid_swipes,COALESCE((SELECT SUM(v.source_opens) FROM $signals v WHERE v.reel_id=r.id),0) source_opens,COALESCE((SELECT SUM(v.shares) FROM $signals v WHERE v.reel_id=r.id),0) shares,COALESCE((SELECT SUM(v.natural_stops) FROM $signals v WHERE v.reel_id=r.id),0) natural_stops,COALESCE((SELECT COUNT(*) FROM $reports rp WHERE rp.reel_id=r.id),0) harm_reports FROM $reels r LEFT JOIN $impressions i ON i.reel_id=r.id WHERE r.owner_id=%d GROUP BY r.id ORDER BY r.updated_at DESC LIMIT 200",
			absint( $owner_id )
		), ARRAY_A );
		$out = array();
		foreach ( $rows as $row ) {
			$interaction = apply_filters( 'rsv_interaction_aggregate', null, absint( $row['id'] ), absint( $owner_id ) );
			$comments    = apply_filters( 'rsv_meaningful_comment_count', null, absint( $row['id'] ), absint( $owner_id ) );
			$enough      = absint( $row['views'] ) >= self::INSIGHT_MINIMUM;
			$out[] = array(
				'id' => $row['public_id'], 'title' => $row['title'], 'insufficient_data' => ! $enough,
				'views' => $enough ? absint( $row['views'] ) : null,
				'completions' => $enough ? absint( $row['completions'] ) : null,
				'average_seconds' => $enough ? round( (float) $row['average_seconds'], 1 ) : null,
				'rapid_swipes' => $enough ? absint( $row['rapid_swipes'] ) : null,
				'source_opens' => $enough ? absint( $row['source_opens'] ) : null,
				'shares' => $enough ? absint( $row['shares'] ) : null,
				'natural_stops' => $enough ? absint( $row['natural_stops'] ) : null,
				'harm_reports' => $enough ? absint( $row['harm_reports'] ) : null,
				'saves' => is_array( $interaction ) && isset( $interaction['saves'] ) ? absint( $interaction['saves'] ) : null,
				'meaningful_comments' => is_numeric( $comments ) ? absint( $comments ) : null,
				'provider_state' => array( 'interactions' => is_array( $interaction ) ? 'available' : 'unavailable', 'comments' => is_numeric( $comments ) ? 'available' : 'unavailable' ),
			);
		}
		return $out;
	}

	public static function history_cursor( $user_id, $limit = 50, $cursor = '' ) {
		$user_id = absint( $user_id );
		$limit   = min( 100, max( 1, absint( $limit ) ) );
		$context = 'history-' . substr( RSV_Helpers::opaque_user_ref( $user_id, 'history' ), 0, 16 );
		$decoded = RSV_Helpers::cursor_decode( $cursor, 'latest', $context );
		if ( is_wp_error( $decoded ) ) return $decoded;
		global $wpdb;
		$p = RSV_Helpers::table( 'progress' );
		$r = RSV_Helpers::table( 'reels' );
		$where = 'p.user_id=%d';
		$args  = array( $user_id );
		if ( $decoded ) {
			$where .= ' AND (p.updated_at<%s OR (p.updated_at=%s AND p.id<%d))';
			$args[] = $decoded['primary']; $args[] = $decoded['primary']; $args[] = $decoded['id'];
		}
		$args[] = $limit + 1;
		$rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT p.id progress_id,p.position_seconds,p.completed,p.replays,p.updated_at progress_updated_at,r.* FROM $p p JOIN $r r ON r.id=p.reel_id WHERE $where ORDER BY p.updated_at DESC,p.id DESC LIMIT %d", $args ), ARRAY_A );
		$has_more = count( $rows ) > $limit;
		if ( $has_more ) array_pop( $rows );
		$items = array();
		foreach ( $rows as $row ) {
			$progress = array( 'position_seconds' => absint( $row['position_seconds'] ), 'completed' => ! empty( $row['completed'] ), 'replays' => absint( $row['replays'] ), 'updated_at' => $row['progress_updated_at'] );
			if ( RSV_Security::can_view_reel( $row, $user_id ) ) {
				$dto = RSV_Repository::public_dto( $row ); $dto['available'] = true; $dto['progress'] = $progress; $items[] = $dto;
			} else {
				$items[] = array( 'id' => 'unavailable-' . substr( hash( 'sha256', $row['public_id'] ), 0, 12 ), 'title' => __( 'Unavailable Reel', RSV_TEXT_DOMAIN ), 'available' => false, 'url' => '', 'progress' => $progress );
			}
		}
		$last = $rows ? end( $rows ) : null;
		$next = $has_more && $last ? RSV_Helpers::cursor_encode( 'latest', $last['progress_updated_at'], '', $last['progress_id'], $context ) : null;
		return array( 'items' => $items, 'next_cursor' => $next, 'history_paused' => ! empty( self::preferences( $user_id )['history_paused'] ) );
	}
}
