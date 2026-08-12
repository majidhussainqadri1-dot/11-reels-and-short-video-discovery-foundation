<?php
require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-state-machine.php';

$allowed = array(
	array( 'draft', 'review' ),
	array( 'review', 'published' ),
	array( 'published', 'restricted' ),
	array( 'restricted', 'published' ),
	array( 'removed', 'published' ),
	array( 'removed', 'archived' ),
);
foreach ( $allowed as $pair ) {
	if ( true !== RSV_State_Machine::assert( $pair[0], $pair[1] ) ) {
		fwrite( STDERR, "Expected Reel transition {$pair[0]} -> {$pair[1]}\n" ); exit( 1 );
	}
}
$blocked = array(
	array( 'draft', 'published' ),
	array( 'published', 'draft' ),
	array( 'archived', 'published' ),
);
foreach ( $blocked as $pair ) {
	if ( ! is_wp_error( RSV_State_Machine::assert( $pair[0], $pair[1] ) ) ) {
		fwrite( STDERR, "Expected blocked Reel transition {$pair[0]} -> {$pair[1]}\n" ); exit( 1 );
	}
}
if ( true !== RSV_State_Machine::assert_report( 'action', 'appealed' ) ) exit( 1 );
if ( ! is_wp_error( RSV_State_Machine::assert_report( 'closed', 'appealed' ) ) ) exit( 1 );
echo "state machine PASS\n";
