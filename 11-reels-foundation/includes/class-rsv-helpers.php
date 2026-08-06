<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Helpers {
	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'rsv_' . preg_replace( '/[^a-z0-9_]/', '', strtolower( $name ) );
	}
	public static function now() { return current_time( 'mysql', true ); }
	public static function public_id( $prefix = 'rel' ) { return sanitize_key( $prefix ) . '_' . wp_generate_uuid4(); }
	public static function trace_id() { return 'rsv_' . substr( hash( 'sha256', wp_generate_uuid4() . microtime( true ) ), 0, 24 ); }
	public static function text( $value, $limit = 255 ) { return mb_substr( sanitize_text_field( (string) $value ), 0, $limit ); }
	public static function textarea( $value, $limit = 10000 ) { return mb_substr( sanitize_textarea_field( (string) $value ), 0, $limit ); }
	public static function enum( $value, $allowed, $default = '' ) { $value = sanitize_key( (string) $value ); return in_array( $value, $allowed, true ) ? $value : $default; }
	public static function json_encode( $value ) { return wp_json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); }
	public static function json_decode( $value, $default = array() ) { $data = json_decode( (string) $value, true ); return is_array( $data ) ? $data : $default; }
	public static function error( $code, $message, $status = 400, $data = array() ) { $data['status'] = (int) $status; $data['trace_id'] = self::trace_id(); return new WP_Error( sanitize_key( $code ), $message, $data ); }
	public static function bool( $value ) { return filter_var( $value, FILTER_VALIDATE_BOOLEAN ); }

	public static function bcp47( $value, $default = 'en-US' ) {
		$value = str_replace( '_', '-', trim( (string) $value ) );
		if ( ! preg_match( '/^[A-Za-z]{2,3}(?:-[A-Za-z]{4})?(?:-[A-Za-z]{2}|-[0-9]{3})?(?:-[A-Za-z0-9]{5,8}|-[0-9][A-Za-z0-9]{3})*$/', $value ) ) return $default;
		$parts = explode( '-', $value );
		$parts[0] = strtolower( $parts[0] );
		foreach ( $parts as $i => $part ) {
			if ( 0 === $i ) continue;
			if ( 4 === strlen( $part ) ) $parts[$i] = ucfirst( strtolower( $part ) );
			elseif ( 2 === strlen( $part ) || ctype_digit( $part ) ) $parts[$i] = strtoupper( $part );
			else $parts[$i] = strtolower( $part );
		}
		return implode( '-', $parts );
	}

	public static function no_cache_private() {
		if ( headers_sent() ) return;
		nocache_headers();
		header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true );
		header( 'Pragma: no-cache', true );
		header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
	}

	public static function cursor_encode( $payload ) {
		$payload = is_array( $payload ) ? $payload : array();
		$json = self::json_encode( $payload );
		$body = rtrim( strtr( base64_encode( $json ), '+/', '-_' ), '=' );
		$sig = substr( hash_hmac( 'sha256', $body, wp_salt( 'nonce' ) ), 0, 24 );
		return $body . '.' . $sig;
	}
	public static function cursor_decode( $cursor ) {
		$parts = explode( '.', (string) $cursor, 2 );
		if ( 2 !== count( $parts ) ) return null;
		$expected = substr( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), 0, 24 );
		if ( ! hash_equals( $expected, $parts[1] ) ) return null;
		$body = strtr( $parts[0], '-_', '+/' );
		$body .= str_repeat( '=', ( 4 - strlen( $body ) % 4 ) % 4 );
		$decoded = base64_decode( $body, true );
		$data = $decoded ? json_decode( $decoded, true ) : null;
		return is_array( $data ) ? $data : null;
	}

	public static function audit( $object_type, $object_id, $action, $from = '', $to = '', $reason = '', $context = array() ) {
		global $wpdb;
		$wpdb->insert( self::table( 'audit' ), array(
			'trace_id' => self::trace_id(), 'actor_id' => get_current_user_id(),
			'object_type' => self::text( $object_type, 40 ), 'object_id' => (int) $object_id,
			'action_name' => self::text( $action, 80 ), 'from_state' => self::text( $from, 40 ),
			'to_state' => self::text( $to, 40 ), 'reason' => self::textarea( $reason, 2000 ),
			'context_json' => self::json_encode( self::redact_context( $context ) ), 'created_at' => self::now(),
		) );
	}

	public static function outbox( $event_name, $object_type, $object_id, $payload = array() ) {
		global $wpdb;
		$wpdb->insert( self::table( 'outbox' ), array(
			'event_id' => self::public_id( 'evt' ), 'event_name' => RSV_Contracts::event( $event_name ),
			'object_type' => self::text( $object_type, 40 ), 'object_id' => (int) $object_id,
			'payload_json' => self::json_encode( self::redact_context( $payload ) ), 'status' => 'pending',
			'attempts' => 0, 'available_at' => self::now(), 'lease_token' => '', 'lease_expires_at' => null,
			'created_at' => self::now(), 'updated_at' => self::now(),
		) );
	}

	public static function redact_context( $value ) {
		if ( ! is_array( $value ) ) return $value;
		$blocked = array( 'email','phone','token','secret','password','authorization','patient_name','ip','user_agent' );
		$out = array();
		foreach ( $value as $key => $item ) {
			$key_string = strtolower( (string) $key );
			$redact = false;
			foreach ( $blocked as $needle ) if ( false !== strpos( $key_string, $needle ) ) { $redact = true; break; }
			$out[$key] = $redact ? '[redacted]' : ( is_array( $item ) ? self::redact_context( $item ) : $item );
		}
		return $out;
	}

	public static function daily_viewer_hash() {
		$user = get_current_user_id();
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
		$subject = $user ? 'u:' . $user : 'g:' . hash( 'sha256', $ip . '|' . $ua );
		return hash_hmac( 'sha256', gmdate( 'Y-m-d' ) . '|' . $subject, wp_salt( 'auth' ) );
	}

	public static function safe_url( $url, $allowed_hosts = array() ) {
		$url = esc_url_raw( (string) $url, array( 'https', 'http' ) );
		if ( ! $url ) return '';
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		if ( $allowed_hosts && ! in_array( $host, $allowed_hosts, true ) ) return '';
		return $url;
	}
}
