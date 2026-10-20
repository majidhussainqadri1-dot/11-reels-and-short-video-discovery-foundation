<?php
/** Current public File05 paths and File26 aggregate privacy, real PHP behavioral fixtures. */
require __DIR__ . '/bootstrap.php';
function apply_filters( $hook, $default, ...$args ) {
    if ( 'rsv_future30_file05_public_ref_valid' === $hook ) return in_array( $args[0] ?? '', array( 'lesson-live', 'course-live' ), true );
    if ( 'rsv_future30_public_ref_valid' === $hook )
        return 'File 26' === ( $args[0] ?? '' ) && 'search-live' === ( $args[1] ?? '' );
    return $default;
}
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
final class RSV_Readtime_Path_Probe { use RSV_Future30_Feature_Write_Trait; }
final class RSV_Readtime_Search_Probe { use RSV_Future30_User_Features_Trait; }
function assert_gate( $test, $name ) {
    if ( ! $test ) { fwrite( STDERR, "FAILED: " . $name . "\n" ); exit( 1 ); }
}
$path = new ReflectionMethod( RSV_Readtime_Path_Probe::class, 'public_learning_path_rows' );
$base = array(
    'public_id'=>'f30_public', 'feature_id'=>'F11-FUT-002', 'title'=>'Learning Series',
    'owner_id'=>42, 'id'=>987, 'version'=>3, 'patient_email'=>'secret@example.com',
    'payload'=>array(
        'level'=>'beginner','description'=>'Appropriate course','private_note'=>'private',
        'steps'=>array( array( 'file05_ref'=>'lesson-live', 'level'=>'beginner' ) ),
        'file05_course_refs'=>array( 'course-live' ),
    ),
);
$result = $path->invoke( null, array( $base ) );
assert_gate( count( $result ) === 1, 'Current public learning path must be visible' );
assert_gate( $result[0]['payload']['steps'][0]['file05_ref'] === 'lesson-live', 'Valid step lost' );
assert_gate( ! isset( $result[0]['owner_id'] ) && ! isset( $result[0]['id'] ) && ! isset( $result[0]['patient_email'] ), 'Private top-level fields leaked' );
assert_gate( ! isset( $result[0]['payload']['private_note'] ), 'Private learning payload leaked' );
$revoked = $base; $revoked['payload']['steps'][0]['file05_ref'] = 'lesson-withdrawn';
assert_gate( array() === $path->invoke( null, array( $revoked ) ), 'Revoked File05 lesson exposed' );
$revoked = $base; $revoked['payload']['file05_course_refs'] = array( 'course-withdrawn' );
assert_gate( array() === $path->invoke( null, array( $revoked ) ), 'Revoked File05 course exposed' );
$revoked = $base; $revoked['payload']['steps'] = array();
assert_gate( array() === $path->invoke( null, array( $revoked ) ), 'Empty learning path exposed' );
$revoked = $base; $revoked['payload']['steps'] = array_fill( 0, 101, array( 'file05_ref'=>'lesson-live' ) );
assert_gate( array() === $path->invoke( null, array( $revoked ) ), 'Oversize learning path exposed' );

$search = new ReflectionMethod( RSV_Readtime_Search_Probe::class, 'public_search_opportunity_rows' );
$row = array( 'topic'=>'Education','query_label'=>'Learning demand', 'aggregate_count'=>5, 'source_ref'=>'search-live', 'user_id'=>99, 'medical_record'=>'secret' );
$good = $search->invoke( null, array( $row ), 20 );
assert_gate( 1 === count( $good ) && 5 === $good[0]['aggregate_count'], 'Verified threshold=5 should be visible' );
assert_gate( ! isset( $good[0]['user_id'] ) && ! isset( $good[0]['medical_record'] ), 'User-level File26 fields leaked' );
foreach ( array( 0, 1, 4, -5, null ) as $value ) {
    $wrong = $row; $wrong['aggregate_count'] = $value;
    assert_gate( array() === $search->invoke( null, array( $wrong ), 20 ), 'Private/unknown aggregate_count escaped threshold' );
}
$wrong = $row; unset( $wrong['aggregate_count'] );
assert_gate( array() === $search->invoke( null, array( $wrong ), 20 ), 'Missing aggregate count escaped threshold' );
$wrong = $row; $wrong['source_ref'] = 'search-withdrawn';
assert_gate( array() === $search->invoke( null, array( $wrong ), 20 ), 'Revoked File26 source escaped read gate' );
$wrong = $row; $wrong['topic'] = '';
assert_gate( array() === $search->invoke( null, array( $wrong ), 20 ), 'Unlabeled source exposed' );
$wrong = $row; $wrong['aggregate_count'] = 6; unset( $wrong['source_ref'] );
assert_gate( 1 === count( $search->invoke( null, array( $wrong ), 20 ) ), 'Valid anonymous aggregate unexpectedly rejected' );
echo "File 11 rc10 read-time File05 + File26 privacy and provenance behaviors PASS\n";
