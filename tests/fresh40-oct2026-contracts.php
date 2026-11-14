<?php
/**
 * File 11 fresh October 2026: current-source and cross-provider public-read invariants.
 * These source-contract checks supplement the runtime/provider/permission test gates.
 */
$root = dirname( __DIR__ ) . '/11-reels-foundation/includes/';
$files = array(
    'experience' => file_get_contents( $root . 'trait-rsv-future30-experience.php' ),
    'storage' => file_get_contents( $root . 'trait-rsv-future30-storage.php' ),
    'write' => file_get_contents( $root . 'trait-rsv-future30-feature-write.php' ),
);
foreach ( $files as $name => $source ) {
    if ( ! is_string( $source ) ) {
        fwrite( STDERR, "Cannot inspect $name\n" );
        exit( 1 );
    }
}
$required = array(
    array( 'experience', "self::public_projection('F11-FUT-030',\$reel)" ),
    array( 'experience', "'related'        => array( 'F11-FUT-003', 'edges', 12 )" ),
    array( 'experience', "'citations'      => array( 'F11-FUT-004', 'edges', 20 )" ),
    array( 'experience', "'evidence'       => array( 'F11-FUT-005', 'objects', 10 )" ),
    array( 'experience', "'transcript'     => array( 'F11-FUT-016', 'objects', 1 )" ),
    array( 'experience', "'chapters'       => array( 'F11-FUT-017', 'edges', 30 )" ),
    array( 'experience', "'knowledge_card' => array( 'F11-FUT-018', 'objects', 1 )" ),
    array( 'experience', "\$projection = self::public_projection( \$spec[0], \$reel );" ),
    array( 'experience', "if ( is_wp_error( \$projection ) ) return \$projection;" ),
    array( 'experience', "if ( is_wp_error( \$tools ) )" ),
    array( 'experience', "'knowledge-graph-evidence-current'" ),
    array( 'storage', "if ( ! \$refs ) return false;" ),
    array( 'storage', "'evidence-layer-current'" ),
    array( 'write', "'rsv_knowledge_source_required'" ),
    array( 'write', "array_merge( \$remedies, \$diseases, \$books, \$sources )" ),
    array( 'write', "'rsv_graph_evidence_invalid'" ),
);
foreach ( $required as $test ) {
    list( $name, $needle ) = $test;
    if ( false === strpos( $files[ $name ], $needle ) ) {
        fwrite( STDERR, "Missing File 11 source-integrity invariant: $name / $needle\n" );
        exit( 1 );
    }
}
$forbidden = array(
    array( 'experience', "\$local=self::edge_rows('F11-FUT-030'," ),
    array( 'experience', "'transcript'=>self::object_rows(" ),
    array( 'experience', "'knowledge_card'=>self::object_rows(" ),
);
foreach ( $forbidden as $test ) {
    list( $name, $needle ) = $test;
    if ( false !== strpos( $files[ $name ], $needle ) ) {
        fwrite( STDERR, "Bypassing public projection on $name / $needle\n" );
        exit( 1 );
    }
}
echo "File 11 public knowledge/reference hardening contracts PASS\n";
