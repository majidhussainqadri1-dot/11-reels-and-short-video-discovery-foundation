<?php
/** PHP behavioral regression against the real File 11 quiz grader. */
require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
final class RSV_Quiz_Grade_Probe { use RSV_Future30_User_Features_Trait; }
$method = new ReflectionMethod( RSV_Quiz_Grade_Probe::class, 'quiz_answer_matches' );
$cases = array(
    array( array( 'type'=>'single-choice', 'correct'=>array( 'A' ) ), 'A', true ),
    array( array( 'type'=>'single-choice', 'correct'=>array( 'A' ) ), array( 'A' ), true ),
    array( array( 'type'=>'single-choice', 'correct'=>array( 'A' ) ), 'Array', false ),
    array( array( 'type'=>'single-choice', 'correct'=>array( 'A' ) ), array( 'A', 'B' ), false ),
    array( array( 'type'=>'multiple-choice', 'correct'=>array( 'A', 'C' ) ), array( 'C', 'A' ), true ),
    array( array( 'type'=>'multiple-choice', 'correct'=>array( 'A', 'C' ) ), array( 'A' ), false ),
    array( array( 'type'=>'multiple-choice', 'correct'=>array( 'A', 'C' ) ), array( 'A', 'A' ), false ),
    array( array( 'type'=>'multiple-choice', 'correct'=>array( 'A', 'C' ) ), 'Array', false ),
    array( array( 'type'=>'multiple-choice', 'correct'=>array( 'A', 'C' ) ), array( 'A', array( 'C' ) ), false ),
    array( array( 'type'=>'true-false', 'correct'=>'1' ), true, true ),
    array( array( 'type'=>'true-false', 'correct'=>'false' ), '0', true ),
    array( array( 'type'=>'true-false', 'correct'=>'false' ), array( 'false' ), false ),
    array( array( 'type'=>'invalid', 'correct'=>'A' ), 'A', false ),
    array( array( 'type'=>'single-choice', 'correct'=>array() ), 'A', false ),
    array( array( 'type'=>'single-choice' ), 'A', false ),
);
foreach ( $cases as $idx => $case ) {
    $actual = $method->invoke( null, $case[0], $case[1] );
    if ( $actual !== $case[2] ) {
        fwrite( STDERR, 'Quiz grading regression failed for case ' . ( $idx + 1 ) . "\n" );
        exit( 1 );
    }
}
$source = file_get_contents( dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-feature-write.php' );
if ( false === $source || false === strpos( $source, "'series-public-read'" ) || false === strpos( $source, 'private static function public_series_rows()' ) || false === strpos( $source, "'rsv_series_size_invalid'" ) ) {
    fwrite( STDERR, "Public series current-reference gate missing\n" );
    exit( 1 );
}
echo 'File11 quiz behavior (15 cases) and series source guards PASS' . "\n";
