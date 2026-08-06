<?php
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/' );
if ( ! function_exists( 'sanitize_key' ) ) { function sanitize_key( $v ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $v ) ); } }
if ( ! function_exists( '__' ) ) { function __( $v ) { return $v; } }
if ( ! class_exists( 'WP_Error' ) ) { class WP_Error { public function __construct( public $code = '', public $message = '', public $data = array() ) {} } }
if ( ! class_exists( 'RSV_Helpers' ) ) { class RSV_Helpers { public static function error( $c, $m, $s = 400 ) { return new WP_Error( $c, $m, array( 'status' => $s ) ); } } }
define( 'RSV_TEXT_DOMAIN', 'test' );
require __DIR__ . '/../11-reels-foundation/includes/class-rsv-state-machine.php';
$valid = array(
	array( 'draft','review' ), array( 'review','published' ), array( 'published','restricted' ),
	array( 'restricted','published' ), array( 'published','removed' ), array( 'removed','archived' ),
);
foreach ( $valid as $pair ) if ( ! RSV_State_Machine::allowed( $pair[0], $pair[1] ) ) exit( "invalid valid transition\n" );
$invalid = array( array( 'draft','published' ), array( 'removed','published' ), array( 'archived','published' ) );
foreach ( $invalid as $pair ) if ( RSV_State_Machine::allowed( $pair[0], $pair[1] ) ) exit( "accepted invalid transition\n" );
echo "state-machine tests PASS\n";
