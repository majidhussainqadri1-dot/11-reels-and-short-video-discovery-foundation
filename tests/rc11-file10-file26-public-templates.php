<?php
/** Behavioral File11 cross-owner media-template and global reason policy regression. */
require __DIR__ . '/bootstrap.php';
function add_filter( ...$args ) { return true; }
function apply_filters( $hook, $value, ...$args ) {
    if ( 'rsv_future30_file10_media_recipe_ref_valid' === $hook ) return 'valid-file10-recipe' === ( $args[0] ?? '' );
    return $value;
}
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-future30-public-safety.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
final class RSV_RC11_Probe {
    use RSV_Future30_Feature_Write_Trait;
    private static function put_object( $fid, $type, $reel_id, $title, $payload, $public_id = '' ) {
        return array( 'feature_id'=>$fid, 'object_type'=>$type, 'payload'=>$payload );
    }
}
function rc11_assert( $cond, $message ) {
    if ( ! $cond ) { fwrite( STDERR, "$message\n" ); exit( 1 ); }
}
$source = new ReflectionMethod( RSV_RC11_Probe::class, 'feature_write' );
$reel = array( 'id'=>4, 'title'=>'Public Reel' );
$payload = array(
    'template_type'=>'clinical-pearl',
    'sections'=>array(
        array( 'title'=>'Introduction', 'text'=>'Safe public description', 'duration_seconds'=>25,
               'patient_name'=>'PRIVATE PATIENT', 'clinic_secret'=>array( 'id'=>42 ) ),
        'Public closing statement'
    ),
    'max_seconds'=>180,
    'file10_media_recipe_ref'=>'valid-file10-recipe',
    'doctor_private_notes'=>'private',
);
$public = RSV_Future30_Public_Safety::public_template_or_reason_payload( 'F11-FUT-010', $payload );
rc11_assert( array_keys( $public ) === array( 'template_type','sections','max_seconds','file10_media_recipe_ref' ), 'Template has forbidden public fields' );
rc11_assert( array_keys( $public['sections'][0] ) === array( 'title','text','duration_seconds' ), 'Template section leaked a private key' );
rc11_assert( $public['sections'][1]['text'] === 'Public closing statement', 'Valid public template section lost' );
rc11_assert( ! isset( $public['doctor_private_notes'] ), 'Template leaked top-level private metadata' );
$written = $source->invoke( null, 'F11-FUT-010', $reel, $payload );
rc11_assert( ! is_wp_error( $written ) && $written['payload'] === $public, 'Valid File10 recipe rejected or write DTO not minimal' );
$payload['file10_media_recipe_ref'] = 'revoked-file10-recipe';
rc11_assert( is_wp_error( $source->invoke( null, 'F11-FUT-010', $reel, $payload ) ), 'Revoked File10 media recipe accepted' );
$payload['file10_media_recipe_ref'] = '';
rc11_assert( ! is_wp_error( $source->invoke( null, 'F11-FUT-010', $reel, $payload ) ), 'Recipe-free template unexpectedly rejected' );
$reasons = array(
    'allowed_reasons'=>array( 'topic-interest', 'diversity', 'payment-priority', array( 'patient_name'=>'PRIVATE' ), 'topic-interest' ),
    'doctor_private_notes'=>'private',
    'commercial_reason'=>true, 'donor_reason'=>true
);
$minimal = RSV_Future30_Public_Safety::public_template_or_reason_payload( 'F11-FUT-024', $reasons );
rc11_assert( array_keys( $minimal ) === array( 'allowed_reasons','commercial_reason','donor_reason' ), 'Policy leaked undeclared fields' );
rc11_assert( $minimal['allowed_reasons'] === array( 'topic-interest','diversity' ), 'Disallowed or duplicate reasons accepted' );
rc11_assert( false === $minimal['commercial_reason'] && false === $minimal['donor_reason'], 'Commercial ranking claim accepted' );
$written = $source->invoke( null, 'F11-FUT-024', $reel, $reasons );
rc11_assert( ! is_wp_error( $written ) && $written['payload'] === $minimal, 'Reason write not minimized' );
rc11_assert( is_wp_error( $source->invoke( null, 'F11-FUT-024', $reel, array( 'allowed_reasons'=>array( 'payment-priority' ) ) ) ), 'Unapproved-only reason policy accepted' );
$storage = file_get_contents( dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-storage.php' );
rc11_assert( strpos( $storage, 'RSV_Future30_Public_Safety::public_template_or_reason_payload' ) !== false, 'Native public read bypasses minimization' );
echo "RC11 File10 template / File26 reason policies (12 assertions) PASS\n";
