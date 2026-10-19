<?php
error_reporting( E_ALL );
define( 'ABSPATH', __DIR__ . '/' );
define( 'RSV_TEXT_DOMAIN', 'reels-short-video-discovery' );
define( 'RSV_CONTRACT_VERSION', 2 );

class WP_Error {
	private $code;
	private $message;
	private $data;
	public function __construct( $code = '', $message = '', $data = null ) { $this->code=$code; $this->message=$message; $this->data=$data; }
	public function get_error_code() { return $this->code; }
	public function get_error_message() { return $this->message; }
	public function get_error_data() { return $this->data; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function __( $value ) { return $value; }
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
function sanitize_text_field( $value ) { return trim( preg_replace( '/[\r\n\t]+/', ' ', strip_tags( (string) $value ) ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function absint( $value ) { return abs( (int) $value ); }
function wp_salt( $scheme = 'auth' ) { return 'unit-test-salt-' . $scheme; }
function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
function wp_generate_uuid4() { return '11111111-2222-4333-8444-555555555555'; }
function current_time() { return '2026-08-06 12:00:00'; }
function get_current_user_id() { return 7; }
