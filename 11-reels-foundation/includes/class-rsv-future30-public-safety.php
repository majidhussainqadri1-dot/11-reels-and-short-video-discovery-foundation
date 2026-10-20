<?php
defined( 'ABSPATH' ) || exit;

/**
 * Final public/read and pre-mutation guard for Future30 projections.
 * Stored external assertions are revalidated at read time and graph-changing
 * mutations are rejected before they can create ambiguous/cyclic lineage.
 */
final class RSV_Future30_Public_Safety {
	public static function register() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'pre_dispatch' ), 20, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_response' ), 90, 3 );
	}

	public static function pre_dispatch( $result, $server, $request ) {
		unset( $server );
		if ( null !== $result || 'POST' !== $request->get_method() ) return $result;
		$route = $request->get_route();
		if ( ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/(F11-FUT-[0-9]{3})$#', $route, $m ) ) return $result;
		if ( 'F11-FUT-006' !== $m[2] ) return $result;
		$source = RSV_Repository::find( $m[1], true );
		if ( ! $source || ! RSV_Security::can( RSV_Contracts::CAP_PUBLISH, $source, 'future30_supersession' ) ) return $result;
		$params = (array) $request->get_json_params();
		$replacement_ref = RSV_Helpers::text( $params['replacement_reel_ref'] ?? '', 255 );
		if ( ! $replacement_ref ) return $result;
		if ( hash_equals( (string) $source['public_id'], $replacement_ref ) || self::supersession_would_cycle( $source['public_id'], $replacement_ref ) ) {
			return RSV_Helpers::error( 'rsv_supersession_cycle', __( 'A Reel correction cannot supersede itself or create a supersession cycle.', RSV_TEXT_DOMAIN ), 409 );
		}
		$existing = self::active_supersession_targets( absint( $source['id'] ) );
		if ( $existing && ! in_array( $replacement_ref, $existing, true ) ) {
			return RSV_Helpers::error( 'rsv_supersession_ambiguous', __( 'This Reel already has an active replacement. Resolve the existing lineage before assigning another.', RSV_TEXT_DOMAIN ), 409 );
		}
		return $result;
	}

	public static function filter_response( $response, $server, $request ) {
		unset( $server );
		if ( ! ( $response instanceof WP_REST_Response ) || 'GET' !== $request->get_method() ) return $response;
		$route = $request->get_route();
		if ( ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/(F11-FUT-[0-9]{3})$#', $route, $m ) ) return $response;
		$data = $response->get_data();
		if ( ! is_array( $data ) || empty( $data['feature_id'] ) ) return $response;
		$reel = RSV_Repository::find( $m[1], true );
		if ( ! $reel || ! RSV_Security::can_view_reel( $reel, 0 ) ) return self::not_found_response();
		$data['objects'] = self::safe_objects( $m[2], (array) ( $data['objects'] ?? array() ), $reel );
		$data['edges']   = self::safe_edges( $m[2], (array) ( $data['edges'] ?? array() ), $reel );
		$response->set_data( $data );
		return $response;
	}

	private static function active_supersession_targets( $source_id ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'future_edges' );
		return array_values( array_unique( array_filter( array_map( 'strval', (array) $wpdb->get_col( $wpdb->prepare( "SELECT target_ref FROM $table WHERE feature_id=%s AND source_reel_id=%d AND edge_type=%s AND status=%s ORDER BY id ASC LIMIT 5", 'F11-FUT-006', absint( $source_id ), 'supersedes', 'active' ) ) ) ) ) );
	}

	private static function supersession_would_cycle( $source_ref, $replacement_ref ) {
		$source_ref = RSV_Helpers::text( $source_ref, 80 );
		$next_ref   = RSV_Helpers::text( $replacement_ref, 80 );
		$seen = array( $source_ref => true );
		for ( $depth = 0; $depth < 100 && $next_ref; $depth++ ) {
			if ( isset( $seen[ $next_ref ] ) ) return true;
			$seen[ $next_ref ] = true;
			$target = RSV_Repository::find( $next_ref, true );
			if ( ! $target ) return false;
			$targets = self::active_supersession_targets( absint( $target['id'] ) );
			$next_ref = $targets ? (string) $targets[0] : '';
		}
		return (bool) $next_ref;
	}

	/** Explicit public schema: never relay arbitrary nested/private quiz metadata. */
	public static function public_quiz_questions( $questions ) {
		$safe = array();
		foreach ( array_slice( (array) $questions, 0, 5 ) as $question ) {
			if ( ! is_array( $question ) ) continue;
			$type = RSV_Helpers::enum( $question['type'] ?? '', array( 'single-choice','multiple-choice','true-false' ), '' );
			$prompt = isset( $question['prompt'] ) && is_scalar( $question['prompt'] ) ? RSV_Helpers::text( $question['prompt'], 1200 ) : '';
			if ( ! $type || ! $prompt ) continue;
			$item = array( 'type' => $type, 'prompt' => $prompt );
			if ( 'true-false' !== $type ) {
				$options = array();
				foreach ( array_slice( (array) ( $question['options'] ?? array() ), 0, 8 ) as $option ) {
					if ( ! is_scalar( $option ) ) continue;
					$options[] = RSV_Helpers::text( $option, 500 );
				}
				$item['options'] = $options;
			}
			$safe[] = $item;
		}
		return $safe;
	}

	/** Permit only documented public File 06 source metadata, never historical PII fields. */
	public static function public_sourced_payload( $feature, $payload ) {
		$p = is_array( $payload ) ? $payload : array();
		$refs = static function ( $values ) {
			$out = array();
			foreach ( array_slice( (array) $values, 0, 100 ) as $value ) {
				if ( ! is_scalar( $value ) ) continue;
				$ref = RSV_Helpers::text( $value, 255 );
				if ( '' !== $ref ) $out[] = $ref;
			}
			return array_values( array_unique( $out ) );
		};
		if ( 'F11-FUT-005' === $feature ) {
			return array(
				'grade' => RSV_Helpers::text( $p['grade'] ?? '', 50 ),
				'source_owner' => RSV_Helpers::text( $p['source_owner'] ?? 'File 06', 30 ),
				'source_refs' => $refs( $p['source_refs'] ?? array() ),
				'claim_scope' => RSV_Helpers::textarea( $p['claim_scope'] ?? '', 1500 ),
				'limitations' => RSV_Helpers::textarea( $p['limitations'] ?? '', 1500 ),
			);
		}
		if ( 'F11-FUT-018' === $feature ) {
			$points = array();
			foreach ( array_slice( (array) ( $p['key_points'] ?? array() ), 0, 20 ) as $point ) {
				if ( is_scalar( $point ) ) $points[] = RSV_Helpers::text( $point, 500 );
			}
			return array(
				'summary' => RSV_Helpers::textarea( $p['summary'] ?? '', 2000 ),
				'key_points' => $points,
				'remedy_refs' => $refs( $p['remedy_refs'] ?? array() ),
				'disease_refs' => $refs( $p['disease_refs'] ?? array() ),
				'book_refs' => $refs( $p['book_refs'] ?? array() ),
				'source_refs' => $refs( $p['source_refs'] ?? array() ),
				'difficulty' => RSV_Helpers::text( $p['difficulty'] ?? '', 40 ),
			);
		}
		return array();
	}

	private static function safe_objects( $feature, $objects, $reel ) {
		$out = array();
		foreach ( $objects as $row ) {
			if ( ! is_array( $row ) ) continue;
			unset( $row['owner_id'], $row['reel_id'], $row['id'] );
			$p = (array) ( $row['payload'] ?? array() );
			if ( 'F11-FUT-005' === $feature ) {
				$owner = RSV_Helpers::text( $p['source_owner'] ?? 'File 06', 30 );
				$refs = (array) ( $p['source_refs'] ?? array() );
				if ( ! $refs || ! self::all_public_refs_valid( $owner, $refs, 'evidence-public-read' ) ) continue;
			}
			if ( 'F11-FUT-016' === $feature ) {
				$ref = RSV_Helpers::text( $p['transcript_ref'] ?? '', 255 );
				if ( empty( $p['reviewed'] ) || ! $ref || true !== apply_filters( 'rsv_future30_transcript_ref_public_valid', false, $ref, $reel ) ) continue;
			}
			if ( 'F11-FUT-018' === $feature ) {
				foreach ( array( 'remedy_refs', 'disease_refs', 'book_refs', 'source_refs' ) as $key ) {
					if ( ! self::all_public_refs_valid( 'File 06', (array) ( $p[ $key ] ?? array() ), 'knowledge-card-public-read' ) ) continue 2;
				}
			}
			if ( in_array( $feature, array( 'F11-FUT-005', 'F11-FUT-018' ), true ) ) {
				$row['payload'] = self::public_sourced_payload( $feature, $p );
			}
			if ( 'F11-FUT-019' === $feature ) {
				$row['payload'] = array(
					'questions' => self::public_quiz_questions( $p['questions'] ?? array() ),
					'streaks' => false,
					'ranking_effect' => false,
				);
			}
			$out[] = $row;
		}
		return $out;
	}

	private static function safe_edges( $feature, $edges, $reel ) {
		$out = array();
		foreach ( $edges as $row ) {
			if ( ! is_array( $row ) ) continue;
			unset( $row['source_reel_id'], $row['reel_id'], $row['owner_id'], $row['id'] );
			$owner = RSV_Helpers::text( $row['target_owner'] ?? '', 30 );
			$ref   = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
			$p     = (array) ( $row['payload'] ?? array() );
			if ( in_array( $feature, array( 'F11-FUT-003','F11-FUT-004','F11-FUT-006','F11-FUT-008','F11-FUT-011','F11-FUT-030' ), true ) && ! self::public_ref_valid( $owner, $ref, 'future30-public-read' ) ) continue;
			if ( 'F11-FUT-008' === $feature ) {
				$source = RSV_Repository::find( $ref, true );
				if ( ! $source || true !== apply_filters( 'rsv_future30_remix_allowed', false, $source, $reel, $row['edge_type'] ?? 'response', 0 ) ) continue;
				$row['payload'] = array( 'attribution_required'=>true, 'patient_media_default_denied'=>true, 'permission_rechecked'=>true );
			}
			if ( 'F11-FUT-012' === $feature ) {
				if ( empty( $p['accepted'] ) || true !== apply_filters( 'rsv_future30_identity_ref_valid', false, $ref, 'coauthor-public-read' ) ) continue;
				$row['payload'] = array( 'role'=>RSV_Helpers::text( $p['role'] ?? 'co-author', 80 ), 'accepted'=>true );
			}
			if ( 'F11-FUT-013' === $feature ) {
				$attestation = RSV_Helpers::text( $p['attestation_ref'] ?? '', 255 );
				if ( 'approved' !== ( $p['decision'] ?? '' ) || true !== apply_filters( 'rsv_future30_identity_ref_valid', false, $ref, 'peer-reviewer-public-read' ) || ! $attestation || true !== apply_filters( 'rsv_future30_peer_review_attestation_valid', false, $attestation, $ref, $reel, 'approved' ) ) continue;
				$row['payload'] = array( 'decision'=>'approved', 'reviewed_at'=>RSV_Helpers::text( $p['reviewed_at'] ?? '', 40 ), 'conflict_declared'=>false );
			}
			if ( 'F11-FUT-014' === $feature ) {
				$status = sanitize_key( $p['translation_status'] ?? '' );
				if ( empty( $p['human_reviewed'] ) || ! in_array( $status, array( 'approved','published' ), true ) ) continue;
				if ( preg_match( '/^reel_[a-f0-9-]{36}$/', $ref ) && ! self::public_ref_valid( 'File 11', $ref, 'translation-public-read' ) ) continue;
				if ( ! preg_match( '/^reel_[a-f0-9-]{36}$/', $ref ) && true !== apply_filters( 'rsv_future30_translation_projection_valid', false, $row ) ) continue;
			}
			$out[] = $row;
		}
		return $out;
	}

	private static function public_ref_valid( $owner, $ref, $purpose ) {
		if ( ! $owner || ! $ref ) return false;
		if ( 'File 11' === $owner ) {
			$target = RSV_Repository::find( $ref, true );
			return (bool) ( $target && RSV_Security::can_view_reel( $target, 0 ) );
		}
		return true === apply_filters( 'rsv_future30_public_ref_valid', false, $owner, $ref, sanitize_key( $purpose ) );
	}

	private static function all_public_refs_valid( $owner, $refs, $purpose ) {
		foreach ( (array) $refs as $ref ) if ( ! self::public_ref_valid( $owner, RSV_Helpers::text( $ref, 255 ), $purpose ) ) return false;
		return true;
	}

	private static function not_found_response() {
		$response = rest_ensure_response( array( 'code'=>'rsv_not_found', 'message'=>__( 'Reel not found.', RSV_TEXT_DOMAIN ) ) );
		$response->set_status( 404 );
		return $response;
	}
}

RSV_Future30_Public_Safety::register();
