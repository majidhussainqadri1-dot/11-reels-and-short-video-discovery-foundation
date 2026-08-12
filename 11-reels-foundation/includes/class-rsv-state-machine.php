<?php
defined( 'ABSPATH' ) || exit;

final class RSV_State_Machine {
	private static $reel_transitions = array(
		'draft'            => array( 'media_processing', 'review', 'archived' ),
		'media_processing' => array( 'review', 'restricted', 'removed', 'archived' ),
		'review'           => array( 'published', 'restricted', 'removed', 'draft', 'archived' ),
		'published'        => array( 'restricted', 'removed', 'archived' ),
		'restricted'       => array( 'published', 'removed', 'archived' ),
		'removed'          => array( 'published', 'restricted', 'archived' ),
		'archived'         => array( 'draft' ),
	);

	private static $report_transitions = array(
		'submitted' => array( 'triaged', 'action', 'no_action', 'closed' ),
		'triaged'   => array( 'action', 'no_action', 'closed' ),
		'action'    => array( 'appealed', 'closed' ),
		'no_action' => array( 'appealed', 'closed' ),
		'appealed'  => array( 'triaged', 'action', 'no_action', 'closed' ),
		'closed'    => array(),
	);

	public static function assert( $from, $to ) {
		return self::assert_map( self::$reel_transitions, $from, $to, 'rsv_invalid_transition', __( 'The requested Reel state change is not allowed.', RSV_TEXT_DOMAIN ) );
	}

	public static function assert_report( $from, $to ) {
		return self::assert_map( self::$report_transitions, $from, $to, 'rsv_invalid_report_transition', __( 'The requested report state change is not allowed.', RSV_TEXT_DOMAIN ) );
	}

	private static function assert_map( $map, $from, $to, $code, $message ) {
		$from = sanitize_key( $from );
		$to   = sanitize_key( $to );
		if ( ! isset( $map[ $from ] ) || ! in_array( $to, $map[ $from ], true ) ) {
			return RSV_Helpers::error( $code, $message, 409 );
		}
		return true;
	}

	public static function allowed( $from, $to ) {
		return true === self::assert( $from, $to );
	}

	public static function report_allowed( $from, $to ) {
		return true === self::assert_report( $from, $to );
	}
}
