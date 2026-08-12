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
	}

	public static function before_callbacks( $response, $handler, $request ) {
		unset( $handler );
		if ( null !== $response || 'POST' !== strtoupper( $request->get_method() ) ) return $response;
		$route = $request->get_route();
		if ( ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/(F11-FUT-[0-9]{3})$#', $route, $m ) ) return $response;
		$reel = RSV_Repository::find( $m[1], true );
		if ( ! $reel ) return $response;
		if ( 'F11-FUT-014' === $m[2] ) {
			$validation = self::validate_linked_language( $reel, (array) $request->get_json_params() );
			return is_wp_error( $validation ) ? $validation : $response;
		}
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
