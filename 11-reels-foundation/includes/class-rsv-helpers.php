<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Helpers {
	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'rsv_' . preg_replace( '/[^a-z0-9_]/', '', strtolower( $name ) );
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

	public static function text( $value, $limit = 255 ) {
		$value = sanitize_text_field( (string) $value );
		return mb_substr( $value, 0, $limit );
	}

	public static function textarea( $value, $limit = 10000 ) {
		$value = sanitize_textarea_field( (string) $value );
		return mb_substr( $value, 0, $limit );
	}

	public static function enum( $value, $allowed, $default = '' ) {
		$value = sanitize_key( (string) $value );
		return in_array( $value, $allowed, true ) ? $value : $default;
	}

	public static function json_encode( $value ) {
		return wp_json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	public static function json_decode( $value, $default = array() ) {
		$data = json_decode( (string) $value, true );
		return is_array( $data ) ? $data : $default;
	}

	public static function error( $code, $message, $status = 400, $data = array() ) {
		$data['status'] = (int) $status;
		$data['trace_id'] = self::trace_id();
		return new WP_Error( sanitize_key( $code ), $message, $data );
	}

	public static function no_cache_private() {
		nocache_headers();
		header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true );
		header( 'Pragma: no-cache', true );
		header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
	}

	public static function cursor_encode( $updated_at, $id ) {
		return rtrim( strtr( base64_encode( $updated_at . '|' . (int) $id ), '+/', '-_' ), '=' );
	}

	public static function cursor_decode( $cursor ) {
		$raw = base64_decode( strtr( (string) $cursor, '-_', '+/' ), true );
		if ( ! $raw || false === strpos( $raw, '|' ) ) {
			return null;
		}
		list( $time, $id ) = explode( '|', $raw, 2 );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $time ) || ! ctype_digit( $id ) ) {
			return null;
		}
		return array( $time, (int) $id );
	}

	public static function audit( $object_type, $object_id, $action, $from = '', $to = '', $reason = '', $context = array() ) {
		global $wpdb;
		$wpdb->insert(
			self::table( 'audit' ),
			array(
				'trace_id' => self::trace_id(),
				'actor_id' => get_current_user_id(),
				'object_type' => self::text( $object_type, 40 ),
				'object_id' => (int) $object_id,
				'action_name' => self::text( $action, 80 ),
				'from_state' => self::text( $from, 40 ),
				'to_state' => self::text( $to, 40 ),
				'reason' => self::textarea( $reason, 2000 ),
				'context_json' => self::json_encode( $context ),
				'created_at' => self::now(),
			),
			array( '%s','%d','%s','%d','%s','%s','%s','%s','%s','%s' )
		);
	}

	public static function outbox( $event_name, $object_type, $object_id, $payload = array() ) {
		global $wpdb;
		$wpdb->insert(
			self::table( 'outbox' ),
			array(
				'event_id' => self::public_id( 'evt' ),
				'event_name' => RSV_Contracts::event( $event_name ),
				'object_type' => self::text( $object_type, 40 ),
				'object_id' => (int) $object_id,
				'payload_json' => self::json_encode( $payload ),
				'status' => 'pending',
				'attempts' => 0,
				'available_at' => self::now(),
				'created_at' => self::now(),
				'updated_at' => self::now(),
			)
		);
	}

	public static function state_message( $code, $message, $status = 400 ) {
		return array( 'code' => $code, 'message' => $message, 'status' => $status, 'trace_id' => self::trace_id() );
	}
}
