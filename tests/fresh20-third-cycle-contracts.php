<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function ( $path ) { $value = file_get_contents( $path ); if ( false === $value ) throw new RuntimeException( "Cannot read $path" ); return $value; };
$f = array(
    'storage' => $read( $root . '/includes/trait-rsv-future30-storage.php' ),
);
$markers = array(
    'public_projection',
    "'public-projection-' . strtolower( \$feature_id )",
    "'F11-FUT-004' === \$feature_id",
    '$duration < 60 || $duration > 600',
    "'F11-FUT-014' === \$feature_id",
    "hash_equals( \$actual, \$declared )",
    "'F11-FUT-005' === \$feature_id",
    "'evidence-layer-current'",
    "'F11-FUT-008' === \$feature_id",
    "'rsv_future30_file10_derivative_valid'",
    "'F11-FUT-012' === \$feature_id",
    "'rsv_future30_coauthor_consent_valid'",
    "'F11-FUT-013' === \$feature_id",
    "'rsv_future30_peer_review_attestation_valid'",
    "'F11-FUT-016' === \$feature_id",
    "'rsv_future30_transcript_ref_public_valid'",
    "'F11-FUT-017' === \$feature_id",
    "'F11-FUT-018' === \$feature_id",
    "'knowledge-card-current'",
);
foreach ( $markers as $needle ) {
    if ( false === strpos( $f['storage'], $needle ) ) {
        fwrite( STDERR, "Missing third fresh20 Round-2 invariant: $needle\n" );
        exit( 1 );
    }
}
echo "third fresh 20-round corrective invariants PASS\n";
