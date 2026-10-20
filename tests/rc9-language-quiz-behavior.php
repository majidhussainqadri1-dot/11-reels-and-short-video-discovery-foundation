<?php
/** File 11: real behavioral tests for canonical linked languages and public quiz DTO. */
require __DIR__ . '/bootstrap.php';
function add_filter( ...$args ) { return true; }
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-future30-public-safety.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
final class RSV_Repository {
    public static function find( $ref, $public = false ) {
        $map = array(
            'source'=>array( 'id'=>9, 'public_id'=>'source', 'language'=>'en-US' ),
            'french'=>array( 'id'=>12, 'public_id'=>'french', 'language'=>'fr-FR' ),
            'german'=>array( 'id'=>13, 'public_id'=>'german', 'language'=>'de-DE' ),
        );
        return $map[ $ref ] ?? false;
    }
}
final class RSV_Language_Probe {
    use RSV_Future30_Feature_Write_Trait;
    public static $edges = array();
    private static function edge_rows( $id, $reel, $limit = 20 ) { return self::$edges; }
    private static function canonical_ref_public_valid( $owner, $ref, $purpose ) {
        return 'File 11' === $owner && (bool) RSV_Repository::find( $ref, true );
    }
    private static function put_edge( $id, $reel, $type, $owner, $ref, $payload, $start = 0, $end = 0 ) {
        return array( 'target_ref'=>$ref, 'language'=>$payload['language'] ?? '' );
    }
}
function check( $yes, $message ) {
    if ( ! $yes ) { fwrite( STDERR, $message . "\n" ); exit( 1 ); }
}
$reel = RSV_Repository::find( 'source', true );
$write = new ReflectionMethod( RSV_Language_Probe::class, 'feature_write' );
$attempt = static function( $lang, $target ) use ( $write, $reel ) {
    return $write->invoke( null, 'F11-FUT-014', $reel, array( 'language'=>$lang, 'linked_reel_ref'=>$target ) );
};
check( is_wp_error( $attempt( 'fr-FR', 'source' ) ), 'Self-linked translation accepted' );
check( is_wp_error( $attempt( 'ar-SA', 'french' ) ), 'Mismatched translated Reel language accepted' );
check( ! is_wp_error( $attempt( 'fr-FR', 'french' ) ), 'Valid current French translation rejected' );
RSV_Language_Probe::$edges = array( array( 'target_ref'=>'french', 'payload'=>array( 'language'=>'es-ES' ) ) );
check( is_wp_error( $attempt( 'fr-FR', 'french' ) ), 'Existing linked Reel target reused' );
RSV_Language_Probe::$edges = array( array( 'target_ref'=>'german', 'payload'=>array( 'language'=>'fr-FR' ) ) );
check( is_wp_error( $attempt( 'fr-FR', 'french' ) ), 'Duplicate language accepted' );
RSV_Language_Probe::$edges = array_fill( 0, 9, array( 'target_ref'=>'german', 'payload'=>array( 'language'=>'de-DE' ) ) );
check( is_wp_error( $attempt( 'fr-FR', 'french' ) ), '10th additional language accepted' );
RSV_Language_Probe::$edges = array();
check( is_wp_error( $attempt( 'en-US', 'french' ) ), 'Original source language duplicated' );
$public = RSV_Future30_Public_Safety::public_quiz_questions( array(
    array( 'type'=>'multiple-choice', 'prompt'=>'Which answer?', 'options'=>array( 'A', 'B' ),
           'correct'=>array( 'B' ), 'answer'=>'B', 'explanation_private'=>'B is correct',
           'patient_email'=>'private@example.com', 'instructor_notes'=>array( 'correct'=>'B' ) ),
    array( 'type'=>'true-false', 'prompt'=>'Agree?', 'correct'=>'true', 'private_key'=>'secret' ),
) );
check( count( $public ) === 2, 'Valid quiz question projection missing' );
check( array_keys( $public[0] ) === array( 'type','prompt','options' ), 'Public quiz exposes undeclared fields' );
check( array_keys( $public[1] ) === array( 'type','prompt' ), 'True/false quiz exposes answer metadata' );
check( $public[0]['options'] === array( 'A','B' ), 'Public options changed unexpectedly' );
check( ! in_array( 'secret', array_values( $public[1] ), true ), 'Private quiz data leaked' );
echo "RC9 translated Reel target behavior (7 cases) and public quiz DTO (5 checks) PASS\n";
