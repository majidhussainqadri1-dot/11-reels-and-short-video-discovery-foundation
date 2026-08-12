<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function ( $path ) { $value = file_get_contents( $path ); if ( false === $value ) throw new RuntimeException( "Cannot read $path" ); return $value; };
$f = array(
    'bootstrap' => $read( $root . '/11-reels-foundation.php' ),
    'storage' => $read( $root . '/includes/trait-rsv-future30-storage.php' ),
    'write' => $read( $root . '/includes/trait-rsv-future30-feature-write.php' ),
    'third' => $read( $root . '/includes/class-rsv-third-review-hardening.php' ),
    'privacy' => $read( $root . '/includes/class-rsv-future30-privacy-integrity.php' ),
);
$markers = array(
    array( 'bootstrap', 'Version: 1.2.0-rc5' ),
    array( 'bootstrap', "define( 'RSV_CONTRACT_VERSION', 11 )" ),
    array( 'storage', 'public_projection' ),
    array( 'storage', "'public-projection-' . strtolower( \$feature_id )" ),
    array( 'storage', "'F11-FUT-004' === \$feature_id" ),
    array( 'storage', '$duration < 60 || $duration > 600' ),
    array( 'storage', "'F11-FUT-014' === \$feature_id" ),
    array( 'storage', "\$validation_owner = 'F11-FUT-014' === \$feature_id ? 'File 11' : \$owner" ),
    array( 'storage', "hash_equals( \$actual, \$declared )" ),
    array( 'write', "case 'F11-FUT-014'" ),
    array( 'write', "'language-version','File 11',\$linked" ),
    array( 'write', "if(!\$linked||!self::canonical_ref_public_valid('File 11',\$linked,'translated-reel'))" ),
    array( 'storage', "'F11-FUT-005' === \$feature_id" ),
    array( 'storage', "'evidence-layer-current'" ),
    array( 'storage', "'F11-FUT-008' === \$feature_id" ),
    array( 'storage', "'rsv_future30_file10_derivative_valid'" ),
    array( 'storage', "'F11-FUT-012' === \$feature_id" ),
    array( 'storage', "'rsv_future30_coauthor_consent_valid'" ),
    array( 'storage', "'F11-FUT-013' === \$feature_id" ),
    array( 'storage', "'rsv_future30_peer_review_attestation_valid'" ),
    array( 'storage', "'F11-FUT-016' === \$feature_id" ),
    array( 'storage', "'rsv_future30_transcript_ref_public_valid'" ),
    array( 'storage', "'F11-FUT-017' === \$feature_id" ),
    array( 'storage', "'F11-FUT-018' === \$feature_id" ),
    array( 'storage', "'knowledge-card-current'" ),
    array( 'bootstrap', "'class-rsv-third-review-hardening.php'" ),
    array( 'third', 'MIN_CREATOR_RESEARCH_SAMPLE = 5' ),
    array( 'third', 'rsv_future30_creator_research_aggregates' ),
    array( 'third', "\$data['_sample_sizes']" ),
    array( 'third', "\$value['aggregate_count']" ),
    array( 'third', 'if ( $count < $required || ! is_numeric( $value ) ) continue;' ),
    array( 'privacy', "RSV_Helpers::audit( 'privacy', 0, 'future30_erase'" ),
    array( 'privacy', "'subject_ref'=>\$opaque_user_ref" ),
    array( 'privacy', "'ReelFuturePrivateStateErased'" ),
    array( 'privacy', "\t\t\t\t\t0," ),
    array( 'privacy', "'user_ref' => \$opaque_user_ref" ),
);
foreach ( $markers as $pair ) {
    list( $key, $needle ) = $pair;
    if ( false === strpos( $f[ $key ], $needle ) ) {
        fwrite( STDERR, "Missing third fresh20 invariant [$key]: $needle\n" );
        exit( 1 );
    }
}
if ( false !== strpos( $f['privacy'], "RSV_Helpers::audit( 'privacy', \$user_id" ) ) {
    fwrite( STDERR, "Future30 privacy erasure must not re-identify the subject in audit object_id\n" );
    exit( 1 );
}
if ( false !== strpos( $f['write'], "'language-version','translation-provider'" ) ) {
    fwrite( STDERR, "F11-FUT-014 linked Reel edges must not use translation-provider as canonical target owner\n" );
    exit( 1 );
}
echo "third fresh 20-round corrective invariants PASS under rc5\n";
