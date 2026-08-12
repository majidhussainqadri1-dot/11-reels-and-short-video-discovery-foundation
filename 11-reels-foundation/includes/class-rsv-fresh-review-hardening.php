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
			$validation = self::validate_linked_language( $reel, $params );
			return is_wp_error( $validation ) ? $validation : $response;
		}
		if ( 'F11-FUT-015' === $m[2] ) {
			$validation = self::validate_ai_target_language( $reel, $params );
			return is_wp_error( $validation ) ? $validation : $response;
		}
		return $response;
	}

	public static function after_dispatch( $response, $server, $request ) {
		unset( $server );
		if ( ! ( $response instanceof WP_REST_Response ) || 'GET' !== strtoupper( $request->get_method() ) ) return $response;
		if ( ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/F11-FUT-015$#', $request->get_route(), $m ) ) return $response;
		$reel = RSV_Repository::find( $m[1], true );
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

	private static function validate_linked_language( $reel, $params ) {
		$raw  = RSV_Helpers::text( $params['language'] ?? '', 20 );
		$lang = self::canonical_language_tag( $raw );
		if ( ! $lang ) return RSV_Helpers::error( 'rsv_language_invalid', __( 'Use a valid language tag for the linked Reel version.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! hash_equals( $raw, $lang ) ) {
			return RSV_Helpers::error( 'rsv_language_tag_not_canonical', __( 'Use the canonical language-tag form before linking this Reel.', RSV_TEXT_DOMAIN ), 422, array( 'canonical_language' => $lang ) );
		}
		$source = self::canonical_language_tag( $reel['language'] ?? '' );
		if ( $source && hash_equals( strtolower( $source ), strtolower( $lang ) ) ) {
			return RSV_Helpers::error( 'rsv_language_duplicate_source', __( 'The original source language must not be duplicated as a linked translation.', RSV_TEXT_DOMAIN ), 409 );
		}

		global $wpdb;
		$table = RSV_Helpers::table( 'future_edges' );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT payload_json FROM $table WHERE feature_id=%s AND source_reel_id=%d AND status=%s ORDER BY id ASC LIMIT 20",
				'F11-FUT-014',
				absint( $reel['id'] ),
				'active'
			),
			ARRAY_A
		);
		foreach ( (array) $rows as $row ) {
			$payload   = RSV_Helpers::json_decode( $row['payload_json'] ?? '{}', array() );
			$edge_lang = self::canonical_language_tag( $payload['language'] ?? '' );
			if ( $edge_lang && hash_equals( strtolower( $edge_lang ), strtolower( $lang ) ) ) {
				return RSV_Helpers::error( 'rsv_language_duplicate', __( 'This language version is already linked.', RSV_TEXT_DOMAIN ), 409 );
			}
		}
		return true;
	}

	private static function validate_ai_target_language( $reel, $params ) {
		$raw  = RSV_Helpers::text( $params['language'] ?? '', 20 );
		$lang = self::canonical_language_tag( $raw );
		if ( ! $lang ) return RSV_Helpers::error( 'rsv_translation_language_invalid', __( 'Use a valid target language tag for AI translation or dubbing.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! hash_equals( $raw, $lang ) ) return RSV_Helpers::error( 'rsv_translation_language_not_canonical', __( 'Use the canonical target language-tag form.', RSV_TEXT_DOMAIN ), 422, array( 'canonical_language' => $lang ) );
		$source = self::canonical_language_tag( $reel['language'] ?? '' );
		if ( $source && hash_equals( strtolower( $source ), strtolower( $lang ) ) ) return RSV_Helpers::error( 'rsv_translation_source_language_invalid', __( 'AI translation or dubbing must target an additional language, not duplicate the canonical source language.', RSV_TEXT_DOMAIN ), 409 );
		return true;
	}

	private static function canonical_language_tag( $value ) {
		$lang = str_replace( '_', '-', trim( RSV_Helpers::text( $value, 20 ) ) );
		if ( ! preg_match( '/^[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/', $lang ) ) return '';
		$parts = explode( '-', $lang );
		$parts[0] = strtolower( $parts[0] );
		for ( $i = 1, $count = count( $parts ); $i < $count; $i++ ) {
			$part = $parts[$i];
			if ( 4 === strlen( $part ) && ctype_alpha( $part ) ) $parts[$i] = ucfirst( strtolower( $part ) );
			elseif ( ( 2 === strlen( $part ) && ctype_alpha( $part ) ) || ( 3 === strlen( $part ) && ctype_digit( $part ) ) ) $parts[$i] = strtoupper( $part );
			else $parts[$i] = strtolower( $part );
		}
		return implode( '-', $parts );
	}
}

RSV_Fresh_Review_Hardening::boot();
