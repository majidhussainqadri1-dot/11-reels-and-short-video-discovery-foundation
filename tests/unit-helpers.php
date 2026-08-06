<?php
require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';

$latest = RSV_Helpers::cursor_encode( 'latest', '2026-08-06 12:00:00', '', 42 );
$decoded = RSV_Helpers::cursor_decode( $latest, 'latest' );
if ( is_wp_error( $decoded ) || 42 !== $decoded['id'] || 'latest' !== $decoded['sort'] ) exit( 1 );
if ( ! is_wp_error( RSV_Helpers::cursor_decode( $latest, 'recommended' ) ) ) exit( 1 );
$tampered = substr( $latest, 0, -1 ) . ( substr( $latest, -1 ) === 'a' ? 'b' : 'a' );
if ( ! is_wp_error( RSV_Helpers::cursor_decode( $tampered, 'latest' ) ) ) exit( 1 );
$recommended = RSV_Helpers::cursor_encode( 'recommended', '91.2500', '2026-08-06 11:59:59', 77 );
$decoded = RSV_Helpers::cursor_decode( $recommended, 'recommended' );
if ( is_wp_error( $decoded ) || '91.2500' !== $decoded['primary'] || 77 !== $decoded['id'] ) exit( 1 );
if ( strlen( RSV_Helpers::opaque_user_ref( 99 ) ) !== 32 ) exit( 1 );
echo "helper contracts PASS\n";
