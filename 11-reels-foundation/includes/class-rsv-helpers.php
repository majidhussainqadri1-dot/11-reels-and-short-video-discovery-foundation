<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Helpers {
	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'rsv_' . preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $name ) );
	}

	public static function now() {
		return current_time( 'mysql', true );
	}

	public static function public_id( $prefix = 'rel' ) {
		return sanitize_key( $prefix ) . '_' . wp_generate_uuid4();
	}

	public static function trace_id() {
		return 'rsv_' . substr( hash( 'sha256', wp_generate_uuid4() . microtime( true ) ), 0, 20 );
	}

	private static function bounded_substr( $value, $limit ) {
		$limit = max( 0, (int) $limit );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	}

	public static function text( $value, $limit = 255 ) {
		return self::bounded_substr( sanitize_text_field( (string) $value ), $limit );
	}

	public static function textarea( $value, $limit = 10000 ) {
		return self::bounded_substr( sanitize_textarea_field( (string) $value ), $limit );
	}

	public static function enum( $value, $allowed, $default = '' ) {
		$value = sanitize_key( (string) $value );
		return in_array( $value, (array) $allowed, true ) ? $value : $default;
	}

	public static function json_encode( $value ) {
		$json = wp_json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		return is_string( $json ) ? $json : '{}';
	}

	public static function json_decode( $value, $default = array() ) {
		$data = json_decode( (string) $value, true );
		return is_array( $data ) ? $data : $default;
	}

	public static function error( $code, $message, $status = 400, $data = array() ) {
		$data['status']   = (int) $status;
		$data['trace_id'] = self::trace_id();
		return new WP_Error( sanitize_key( $code ), $message, $data );
	}

	public static function no_cache_private() {
		nocache_headers();
		header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true );
		header( 'Pragma: no-cache', true );
		header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
	}

	private static function base64url_encode( $value ) {
		return rtrim( strtr( base64_encode( (string) $value ), '+/', '-_' ), '=' );
	}

	private static function base64url_decode( $value ) {
		$value = strtr( (string) $value, '-_', '+/' );
		$padding = strlen( $value ) % 4;
		if ( $padding ) {
			$value .= str_repeat( '=', 4 - $padding );
		}
		return base64_decode( $value, true );
	}

	/**
	 * Create a sort-bound, tamper-evident cursor.
	 *
	 * @param string $sort       recommended|latest.
	 * @param mixed  $primary    Rank or published timestamp.
	 * @param mixed  $secondary  Updated timestamp or empty string.
	 * @param int    $id         Internal stable tie-breaker.
	 * @return string
	 */
	public static function cursor_encode( $sort, $primary, $secondary, $id, $context = '' ) {
		$payload = self::json_encode(
			array(
				'v' => 3,
				's' => self::enum( $sort, array( 'recommended', 'latest' ), 'recommended' ),
				'c' => sanitize_key( (string) $context ),
				'p' => (string) $primary,
				'q' => (string) $secondary,
				'i' => absint( $id ),
			)
		);
		$encoded = self::base64url_encode( $payload );
		$mac     = hash_hmac( 'sha256', $encoded, wp_salt( 'nonce' ) );
		return $encoded . '.' . $mac;
	}

	/**
	 * Decode and validate a cursor for the requested sort and filter context.
	 *
	 * @return array<string,mixed>|WP_Error|null
	 */
	public static function cursor_decode( $cursor, $expected_sort = 'recommended', $expected_context = '' ) {
		$cursor = trim( (string) $cursor );
		if ( '' === $cursor ) {
			return null;
		}
		if ( false === strpos( $cursor, '.' ) ) {
			return self::error( 'rsv_cursor_invalid', __( 'The feed cursor is invalid or expired.', RSV_TEXT_DOMAIN ), 400 );
		}
		list( $encoded, $mac ) = explode( '.', $cursor, 2 );
		$expected = hash_hmac( 'sha256', $encoded, wp_salt( 'nonce' ) );
		if ( ! hash_equals( $expected, (string) $mac ) ) {
			return self::error( 'rsv_cursor_invalid', __( 'The feed cursor is invalid or expired.', RSV_TEXT_DOMAIN ), 400 );
		}
		$raw     = self::base64url_decode( $encoded );
		$data    = self::json_decode( $raw, array() );
		$sort    = self::enum( $data['s'] ?? '', array( 'recommended', 'latest' ), '' );
		$context = sanitize_key( (string) ( $data['c'] ?? '' ) );
		if ( 3 !== (int) ( $data['v'] ?? 0 ) || ! $sort || $sort !== $expected_sort || $context !== sanitize_key( (string) $expected_context ) || empty( $data['i'] ) ) {
			return self::error( 'rsv_cursor_invalid', __( 'The feed cursor is invalid or expired.', RSV_TEXT_DOMAIN ), 400 );
		}
		if ( 'latest' === $sort && ! self::is_mysql_datetime( $data['p'] ?? '' ) ) {
			return self::error( 'rsv_cursor_invalid', __( 'The feed cursor is invalid or expired.', RSV_TEXT_DOMAIN ), 400 );
		}
		if ( 'recommended' === $sort && ( ! is_numeric( $data['p'] ?? null ) || ! self::is_mysql_datetime( $data['q'] ?? '' ) ) ) {
			return self::error( 'rsv_cursor_invalid', __( 'The feed cursor is invalid or expired.', RSV_TEXT_DOMAIN ), 400 );
		}
		return array(
			'sort'      => $sort,
			'context'   => $context,
			'primary'   => (string) $data['p'],
			'secondary' => (string) ( $data['q'] ?? '' ),
			'id'        => absint( $data['i'] ),
		);
	}

	public static function is_mysql_datetime( $value ) {
		return 1 === preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $value );
	}

	public static function opaque_user_ref( $user_id, $purpose = 'viewer' ) {
		return substr( hash_hmac( 'sha256', absint( $user_id ) . '|' . sanitize_key( $purpose ), wp_salt( 'auth' ) ), 0, 32 );
	}

	public static function audit( $object_type, $object_id, $action, $from = '', $to = '', $reason = '', $context = array(), $actor_id = null ) {
		global $wpdb;
		$result = $wpdb->insert(
			self::table( 'audit' ),
			array(
				'trace_id'     => self::trace_id(),
				'actor_id'     => null === $actor_id ? get_current_user_id() : absint( $actor_id ),
				'object_type'  => self::text( $object_type, 40 ),
				'object_id'    => (int) $object_id,
				'action_name'  => self::text( $action, 80 ),
				'from_state'   => self::text( $from, 40 ),
				'to_state'     => self::text( $to, 40 ),
				'reason'       => self::textarea( $reason, 2000 ),
				'context_json' => self::json_encode( $context ),
				'created_at'   => self::now(),
			),
			array( '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		return false !== $result;
	}

	public static function outbox( $event_name, $object_type, $object_id, $payload = array() ) {
		global $wpdb;
		$now = self::now();
		$result = $wpdb->insert(
			self::table( 'outbox' ),
			array(
				'event_id'      => self::public_id( 'evt' ),
				'event_name'    => RSV_Contracts::event( $event_name ),
				'object_type'   => self::text( $object_type, 40 ),
				'object_id'     => (int) $object_id,
				'payload_json'  => self::json_encode( $payload ),
				'status'        => 'pending',
				'attempts'      => 0,
				'available_at'  => $now,
				'lock_token'    => '',
				'locked_at'     => null,
				'last_error'    => '',
				'created_at'    => $now,
				'updated_at'    => $now,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		return false !== $result;
	}

	public static function state_message( $code, $message, $status = 400 ) {
		return array(
			'code'     => sanitize_key( $code ),
			'message'  => (string) $message,
			'status'   => (int) $status,
			'trace_id' => self::trace_id(),
		);
	}
}
