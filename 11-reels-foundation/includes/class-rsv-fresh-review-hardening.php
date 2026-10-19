<?php
defined( 'ABSPATH' ) || exit;

/**
 * Additive hardening discovered by the second fresh 20-round review cycle.
 * Canonical ownership remains with the native File 11 domain and external owners.
 */
final class RSV_Fresh_Review_Hardening {
	const CONTRACT_VERSION = 1;

	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'register' ), 95 );
	}

	public static function register() {
		add_filter( 'rest_request_before_callbacks', array( __CLASS__, 'before_callbacks' ), 6, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'after_dispatch' ), 100, 3 );
	}

	public static function before_callbacks( $response, $handler, $request ) {
		unset( $handler );
		if ( null !== $response || 'POST' !== strtoupper( $request->get_method() ) ) return $response;
		$route = $request->get_route();
		if ( ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/(F11-FUT-[0-9]{3})$#', $route, $m ) ) return $response;
		$reel = RSV_Repository::find( $m[1], true );
		if ( ! $reel ) return $response;
		$params = (array) $request->get_json_params();
		if ( 'F11-FUT-014' === $m[2] ) {
			$reconciled = self::reconcile_stale_language_edges( $reel );
			if ( is_wp_error( $reconciled ) ) return $reconciled;
			$validation = self::validate_linked_language( $reel, $params );
			return is_wp_error( $validation ) ? $validation : $response;
		}
		if ( 'F11-FUT-015' === $m[2] ) {
			$validation = self::validate_ai_target_language( $reel, $params );
			return is_wp_error( $validation ) ? $validation : $response;
		}
		if ( 'F11-FUT-019' === $m[2] ) {
			$validation = self::validate_quiz_payload_schema( (array) ( $params['questions'] ?? array() ) );
			return is_wp_error( $validation ) ? $validation : $response;
		}
		return $response;
	}

	public static function after_dispatch( $response, $server, $request ) {
		unset( $server );
		if ( ! ( $response instanceof WP_REST_Response ) || 'GET' !== strtoupper( $request->get_method() ) ) return $response;
		$route = $request->get_route();
		if ( preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/F11-FUT-015$#', $route, $m ) ) return self::filter_ai_translation_public_response( $response, $m[1] );
		if ( preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/ai-context$#', $route, $m ) ) return self::filter_ai_context_timestamps( $response, $m[1] );
		return $response;
	}

	private static function filter_ai_translation_public_response( $response, $reel_ref ) {
		$reel = RSV_Repository::find( $reel_ref, true );
		if ( ! $reel || ! RSV_Security::can_view_reel( $reel, 0 ) ) return $response;
		$data = $response->get_data();
		if ( ! is_array( $data ) ) return $response;
		$safe = array();
		foreach ( (array) ( $data['objects'] ?? array() ) as $row ) {
			if ( ! is_array( $row ) ) continue;
			$payload = (array) ( $row['payload'] ?? array() );
			$status  = sanitize_key( $payload['status'] ?? '' );
			if ( ! in_array( $status, array( 'approved', 'published' ), true ) ) continue;
			if ( true !== apply_filters( 'rsv_future30_translation_request_public_valid', false, $row, $reel ) ) continue;
			$lang = self::canonical_language_tag( $payload['language'] ?? '' );
			if ( ! $lang ) continue;
			$safe[] = array(
				'public_id' => RSV_Helpers::text( $row['public_id'] ?? '', 80 ),
				'title' => RSV_Helpers::text( $row['title'] ?? 'AI translation/dubbing', 255 ),
				'payload' => array(
					'language' => $lang,
					'dubbing' => ! empty( $payload['dubbing'] ),
					'ai_label_required' => true,
					'original_audio_option_required' => true,
					'status' => $status,
				),
				'version' => absint( $row['version'] ?? 0 ),
				'updated_at' => RSV_Helpers::text( $row['updated_at'] ?? '', 40 ),
			);
		}
		$data['objects'] = $safe;
		$response->set_data( $data );
		return $response;
	}

	private static function filter_ai_context_timestamps( $response, $reel_ref ) {
		$reel = RSV_Repository::find( $reel_ref, true );
		if ( ! $reel || ! RSV_Security::can_view_reel( $reel ) ) return $response;
		$dto = RSV_Repository::public_dto( $reel );
		$duration = absint( $dto['duration_seconds'] ?? 0 );
		if ( $duration < 60 || $duration > 600 ) {
			$error = rest_ensure_response( array( 'code'=>'rsv_ai_context_duration_unavailable', 'message'=>__( 'Authoritative Reel duration is unavailable; AI grounding is temporarily disabled.', RSV_TEXT_DOMAIN ) ) );
			$error->set_status( 503 );
			$error->header( 'Cache-Control', 'private, no-store, max-age=0' );
			$error->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
			return $error;
		}
		$data = $response->get_data();
		if ( ! is_array( $data ) || ! is_array( $data['context'] ?? null ) ) return $response;
		foreach ( array( 'citations', 'chapters' ) as $bucket ) {
			$safe = array();
			foreach ( (array) ( $data['context'][ $bucket ] ?? array() ) as $row ) {
				if ( ! is_array( $row ) ) continue;
				$start = absint( $row['start_second'] ?? 0 );
				$end   = absint( $row['end_second'] ?? 0 );
				if ( $start > $duration || ( $end && ( $end < $start || $end > $duration ) ) ) continue;
				$row['start_second'] = $start;
				$row['end_second']   = $end;
				$safe[] = $row;
			}
			$data['context'][ $bucket ] = $safe;
		}
		$data['context']['authoritative_duration_seconds'] = $duration;
		$response->set_data( $data );
		$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
		$response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
		return $response;
	}

	private static function reconcile_stale_language_edges( $reel ) {
		$authorized = RSV_Security::can( RSV_Contracts::CAP_PUBLISH, $reel, 'future30_language_reconcile' ) || RSV_Security::can( RSV_Contracts::CAP_MANAGE, $reel, 'future30_language_reconcile' );
		if ( ! $authorized ) return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot manage Reel language links.', RSV_TEXT_DOMAIN ), 403 );
		return RSV_DB::transaction(
			static function () use ( $reel ) {
				global $wpdb;
				$table = RSV_Helpers::table( 'future_edges' );
				$rows = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT id,target_ref,payload_json,version FROM $table WHERE feature_id=%s AND source_reel_id=%d AND status=%s ORDER BY id ASC LIMIT 20 FOR UPDATE",
						'F11-FUT-014',
						absint( $reel['id'] ),
						'active'
					),
					ARRAY_A
				);
				$staled = 0;
				foreach ( (array) $rows as $row ) {
					$ref = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
					$payload = RSV_Helpers::json_decode( $row['payload_json'] ?? '{}', array() );
					$edge_lang = self::canonical_language_tag( $payload['language'] ?? '' );
					$target = preg_match( '/^reel_[a-f0-9-]{36}$/', $ref ) ? RSV_Repository::find( $ref, true ) : null;
					$actual_lang = $target ? self::canonical_language_tag( $target['language'] ?? '' ) : '';
					$current = $target && RSV_Security::can_view_reel( $target, 0 ) && $edge_lang && $actual_lang && hash_equals( strtolower( $edge_lang ), strtolower( $actual_lang ) );
					if ( $current ) continue;
					$changed = $wpdb->update(
						$table,
						array( 'status'=>'stale', 'version'=>absint( $row['version'] ) + 1, 'updated_at'=>RSV_Helpers::now() ),
						array( 'id'=>absint( $row['id'] ), 'version'=>absint( $row['version'] ), 'status'=>'active' ),
						array( '%s','%d','%s' ),
						array( '%d','%d','%s' )
					);
					if ( 1 !== $changed ) return RSV_Helpers::error( 'rsv_language_reconcile_conflict', __( 'Reel language links changed concurrently. Refresh and retry.', RSV_TEXT_DOMAIN ), 409 );
					$staled++;
				}
				if ( $staled ) {
					if ( ! RSV_Helpers::audit( 'reel', absint( $reel['id'] ), 'language_links_reconciled', '', '', 'Stale language links removed from active capacity', array( 'count'=>$staled ) ) ) return RSV_Helpers::error( 'rsv_language_reconcile_evidence_failed', __( 'Stale language links could not be reconciled with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
					if ( ! RSV_Helpers::outbox( 'ReelLanguageLinksReconciled', 'reel', absint( $reel['id'] ), array( 'reel_public_id'=>RSV_Helpers::text( $reel['public_id'] ?? '', 80 ), 'stale_links'=>$staled ) ) ) return RSV_Helpers::error( 'rsv_language_reconcile_event_failed', __( 'Stale language links could not be reconciled with durable event evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return true;
			}
		);
	}

	private static function validate_linked_language( $reel, $params ) {
		$raw  = RSV_Helpers::text( $params['language'] ?? '', 20 );
		$lang = self::canonical_language_tag( $raw );
		if ( ! $lang ) return RSV_Helpers::error( 'rsv_language_invalid', __( 'Use a valid language tag for the linked Reel version.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! hash_equals( $raw, $lang ) ) return RSV_Helpers::error( 'rsv_language_tag_not_canonical', __( 'Use the canonical language-tag form before linking this Reel.', RSV_TEXT_DOMAIN ), 422, array( 'canonical_language'=>$lang ) );
		$source = self::canonical_language_tag( $reel['language'] ?? '' );
		if ( $source && hash_equals( strtolower( $source ), strtolower( $lang ) ) ) return RSV_Helpers::error( 'rsv_language_duplicate_source', __( 'The original source language must not be duplicated as a linked translation.', RSV_TEXT_DOMAIN ), 409 );
		global $wpdb;
		$table = RSV_Helpers::table( 'future_edges' );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT payload_json FROM $table WHERE feature_id=%s AND source_reel_id=%d AND status=%s ORDER BY id ASC LIMIT 20", 'F11-FUT-014', absint( $reel['id'] ), 'active' ), ARRAY_A );
		foreach ( (array) $rows as $row ) {
			$payload = RSV_Helpers::json_decode( $row['payload_json'] ?? '{}', array() );
			$edge_lang = self::canonical_language_tag( $payload['language'] ?? '' );
			if ( $edge_lang && hash_equals( strtolower( $edge_lang ), strtolower( $lang ) ) ) return RSV_Helpers::error( 'rsv_language_duplicate', __( 'This language version is already linked.', RSV_TEXT_DOMAIN ), 409 );
		}
		return true;
	}

	private static function validate_ai_target_language( $reel, $params ) {
		$raw = RSV_Helpers::text( $params['language'] ?? '', 20 );
		$lang = self::canonical_language_tag( $raw );
		if ( ! $lang ) return RSV_Helpers::error( 'rsv_translation_language_invalid', __( 'Use a valid target language tag for AI translation or dubbing.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! hash_equals( $raw, $lang ) ) return RSV_Helpers::error( 'rsv_translation_language_not_canonical', __( 'Use the canonical target language-tag form.', RSV_TEXT_DOMAIN ), 422, array( 'canonical_language'=>$lang ) );
		$source = self::canonical_language_tag( $reel['language'] ?? '' );
		if ( $source && hash_equals( strtolower( $source ), strtolower( $lang ) ) ) return RSV_Helpers::error( 'rsv_translation_source_language_invalid', __( 'AI translation or dubbing must target an additional language, not duplicate the canonical source language.', RSV_TEXT_DOMAIN ), 409 );
		return true;
	}

	private static function validate_quiz_payload_schema( $questions ) {
		$allowed = array( 'type', 'prompt', 'options', 'correct', 'explanation_public' );
		foreach ( $questions as $question ) {
			if ( ! is_array( $question ) ) return RSV_Helpers::error( 'rsv_quiz_schema_invalid', __( 'Every quiz question must use the governed structured schema.', RSV_TEXT_DOMAIN ), 422 );
			$unknown = array_diff( array_keys( $question ), $allowed );
			if ( $unknown ) return RSV_Helpers::error( 'rsv_quiz_unknown_field', __( 'Quiz questions may not contain undeclared or private answer fields.', RSV_TEXT_DOMAIN ), 422 );
			if ( isset( $question['prompt'] ) && ! is_scalar( $question['prompt'] ) ) return RSV_Helpers::error( 'rsv_quiz_prompt_invalid', __( 'Quiz prompts must be plain text.', RSV_TEXT_DOMAIN ), 422 );
			if ( isset( $question['explanation_public'] ) && ! is_scalar( $question['explanation_public'] ) ) return RSV_Helpers::error( 'rsv_quiz_explanation_invalid', __( 'Public quiz explanations must be plain text.', RSV_TEXT_DOMAIN ), 422 );
			foreach ( (array) ( $question['options'] ?? array() ) as $option ) if ( ! is_scalar( $option ) ) return RSV_Helpers::error( 'rsv_quiz_option_invalid', __( 'Quiz options must be plain scalar values.', RSV_TEXT_DOMAIN ), 422 );
			$correct = $question['correct'] ?? null;
			if ( is_array( $correct ) ) {
				foreach ( $correct as $answer ) if ( ! is_scalar( $answer ) ) return RSV_Helpers::error( 'rsv_quiz_answer_schema_invalid', __( 'Private quiz answers must be scalar option values.', RSV_TEXT_DOMAIN ), 422 );
			} elseif ( null !== $correct && ! is_scalar( $correct ) ) return RSV_Helpers::error( 'rsv_quiz_answer_schema_invalid', __( 'Private quiz answers must be scalar option values.', RSV_TEXT_DOMAIN ), 422 );
		}
		return true;
	}

	private static function canonical_language_tag( $value ) {
		$lang = str_replace( '_', '-', trim( RSV_Helpers::text( $value, 20 ) ) );
		if ( ! preg_match( '/^[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/', $lang ) ) return '';
		$parts = explode( '-', $lang );
		$parts[0] = strtolower( $parts[0] );
		for ( $i=1, $count=count( $parts ); $i<$count; $i++ ) {
			$part = $parts[$i];
			if ( 4 === strlen( $part ) && ctype_alpha( $part ) ) $parts[$i] = ucfirst( strtolower( $part ) );
			elseif ( ( 2 === strlen( $part ) && ctype_alpha( $part ) ) || ( 3 === strlen( $part ) && ctype_digit( $part ) ) ) $parts[$i] = strtoupper( $part );
			else $parts[$i] = strtolower( $part );
		}
		return implode( '-', $parts );
	}
}

RSV_Fresh_Review_Hardening::boot();
