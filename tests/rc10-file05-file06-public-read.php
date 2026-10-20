<?php
/** Executable cross-file read-time privacy and revocation regression. */
require __DIR__ . '/bootstrap.php';
function add_filter( ...$args ) { return true; }
function apply_filters( $hook, $value, ...$args ) {
    if ( 'rsv_future30_file05_public_ref_valid' === $hook ) return ( $args[0] ?? '' ) === 'live05';
    return $value;
}
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-future30-public-safety.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
final class RSV_Path_Read_Probe {
    use RSV_Future30_Feature_Write_Trait;
    public static $rows = array();
    private static function object_rows( $feature, $reel = 0, $owner = 0, $limit = 50 ) { return self::$rows; }
}
function must( $ok, $message ) {
    if ( ! $ok ) { fwrite( STDERR, "$message\n" ); exit( 1 ); }
}
$mk = static function ( $ref, $course, $public_id ) {
    return array(
        'public_id'=>$public_id, 'feature_id'=>'F11-FUT-002', 'title'=>'Learning path',
        'owner_id'=>99, 'reel_id'=>0, 'id'=>437, 'version'=>1, 'private_memo'=>'secret',
        'payload'=>array(
            'description'=>'Education only', 'level'=>'beginner', 'extra_patient_data'=>'private',
            'steps'=>array( array( 'file05_ref'=>$ref, 'level'=>'beginner', 'secret_note'=>'hide' ) ),
            'file05_course_refs'=>array( $course ),
        ),
    );
};
RSV_Path_Read_Probe::$rows = array( $mk( 'live05', 'live05', 'safe' ), $mk( 'revoked05', 'live05', 'withdrawn_step' ), $mk( 'live05', 'revoked05', 'withdrawn_course' ) );
$method = new ReflectionMethod( RSV_Path_Read_Probe::class, 'public_learning_path_rows' );
$data = $method->invoke( null, RSV_Path_Read_Probe::$rows );
must( count( $data ) === 1 && $data[0]['public_id'] === 'safe', 'File05 revoked or invalid path leaked' );
must( ! isset( $data[0]['owner_id'] ) && ! isset( $data[0]['private_memo'] ), 'Path exposed private top-level metadata' );
must( ! isset( $data[0]['payload']['extra_patient_data'] ) && ! isset( $data[0]['payload']['steps'][0]['secret_note'] ), 'Path exposed private nested metadata' );
must( $data[0]['payload']['steps'][0]['file05_ref'] === 'live05', 'Current File05 path data missing' );
RSV_Path_Read_Probe::$rows = array( $mk( 'revoked05', 'revoked05', 'all_revoked' ) );
must( count( $method->invoke( null, RSV_Path_Read_Probe::$rows ) ) === 0, 'Revoked File05 provider did not fail closed' );
$evidence = RSV_Future30_Public_Safety::public_sourced_payload( 'F11-FUT-005', array(
    'grade'=>'Research', 'source_owner'=>'File 06', 'source_refs'=>array( 'source06' ),
    'claim_scope'=>'Study', 'limitations'=>'Observational', 'patient_name'=>'Private Name',
    'patient_email'=>'private@example.com', 'undisclosed_report'=>array( 'secret'=>'x' ),
) );
must( array_keys( $evidence ) === array( 'grade','source_owner','source_refs','claim_scope','limitations' ), 'Evidence payload schema is not restrictive' );
must( $evidence['source_refs'] === array( 'source06' ), 'Evidence references not preserved' );
$card = RSV_Future30_Public_Safety::public_sourced_payload( 'F11-FUT-018', array(
    'summary'=>'Public science', 'key_points'=>array( 'Study one', 'Study two' ), 'remedy_refs'=>array( 'source06' ),
    'source_refs'=>array( 'book06' ), 'private_clinical_data'=>'patient secret',
    'medical_contraindications_private'=>array( 'data'=>'secret' ),
) );
must( array_keys( $card ) === array( 'summary','key_points','remedy_refs','disease_refs','book_refs','source_refs','difficulty' ), 'Card schema is not restrictive' );
must( ! isset( $card['private_clinical_data'] ) && ! isset( $card['medical_contraindications_private'] ), 'Card leaked private medical data' );
$source = file_get_contents( dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-storage.php' );
must( false !== strpos( $source, "RSV_Future30_Public_Safety::public_sourced_payload( \$feature_id" ), 'Generic public projection can bypass source minimization' );
echo "RC10 File05 revoked path / File06 public evidence+knowledge payload (10 assertions) PASS\n";
