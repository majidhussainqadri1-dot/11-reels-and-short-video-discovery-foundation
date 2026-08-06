<?php
defined( 'ABSPATH' ) || exit;

final class RSV_State_Machine {
	private static $transitions = array(
		'draft' => array( 'media_processing', 'review', 'archived' ),
		'media_processing' => array( 'review', 'restricted', 'removed' ),
		'review' => array( 'published', 'restricted', 'removed', 'draft' ),
		'published' => array( 'restricted', 'removed', 'archived' ),
		'restricted' => array( 'published', 'removed', 'archived' ),
		'removed' => array( 'restricted', 'archived' ),
		'archived' => array( 'draft' ),
	);

	public static function assert( $from, $to ) {
		$from = sanitize_key( $from );
		$to = sanitize_key( $to );
		if ( ! isset( self::$transitions[ $from ] ) || ! in_array( $to, self::$transitions[ $from ], true ) ) {
			return RSV_Helpers::error( 'rsv_invalid_transition', __( 'The requested state change is not allowed.', RSV_TEXT_DOMAIN ), 409 );
		}
		return true;
	}

	public static function allowed( $from, $to ) {
		return true === self::assert( $from, $to );
	}
}
