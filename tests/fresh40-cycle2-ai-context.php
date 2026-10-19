<?php
/** Behavioral source-provider regression for File 16 AI context boundary. */
require __DIR__ . '/bootstrap.php';
function add_filter( ...$args ) { return true; }
function apply_filters( $hook, $initial, ...$args ) {
    if ( 'rsv_future30_public_ref_valid' === $hook ) {
        return ( $args[0] ?? '' ) === 'File 06' && in_array( $args[1] ?? '', array( 'public-source', 'public-book' ), true );
    }
    return $initial;
}
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-future30-ai-context-safety.php';
$class = RSV_Future30_AI_Context_Safety::class;
$source = new ReflectionMethod( $class, 'source_object_valid' );
$time = new ReflectionMethod( $class, 'timed_row_valid' );
$cases = array(
    array( 'evidence', array( 'payload'=>array( 'source_refs'=>array( 'public-source' ) ) ), true ),
    array( 'evidence', array( 'payload'=>array( 'source_refs'=>array( 'withdrawn-source' ) ) ), false ),
    array( 'evidence', array( 'payload'=>array( 'source_refs'=>array() ) ), false ),
    array( 'knowledge_card', array( 'payload'=>array( 'remedy_refs'=>array( 'public-source' ) ) ), true ),
    array( 'knowledge_card', array( 'payload'=>array( 'remedy_refs'=>array( 'public-source' ), 'source_refs'=>array( 'withdrawn-source' ) ) ), false ),
    array( 'knowledge_card', array( 'payload'=>array() ), false ),
    array( 'knowledge_card', array( 'payload'=>array( 'source_refs'=>array( 'public-book' ) ) ), true ),
);
foreach ( $cases as $idx => $case ) {
    if ( $source->invoke( null, $case[0], $case[1] ) !== $case[2] ) {
        fwrite( STDERR, 'AI current-source regression failed: ' . ( $idx + 1 ) . "\n" );
        exit( 1 );
    }
}
$bounds = array(
    array( 0, 600, 0, true ),
    array( 600, 600, 0, true ),
    array( 601, 600, 0, false ),
    array( 20, 20, 10, false ),
    array( 40, 600, 39, false ),
    array( 0, 59, 0, false ),
    array( 0, 601, 0, false ),
);
foreach ( $bounds as $idx => $v ) {
    if ( $time->invoke( null, array( 'start_second'=>$v[0], 'end_second'=>$v[2] ), $v[1] ) !== $v[3] ) {
        fwrite( STDERR, 'AI timestamp-bound regression failed: ' . ( $idx + 1 ) . "\n" );
        exit( 1 );
    }
}
echo 'File11 AI provider evidence and time bounds (14 cases) PASS' . "\n";
