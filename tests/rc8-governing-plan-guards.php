<?php
/** Behavioral plan-to-code regression: publication object ownership, File05 validation, File16 DTO minimization. */
require __DIR__ . '/bootstrap.php';
function wp_parse_args( $input, $defaults ) { return array_merge( $defaults, (array) $input ); }
function is_user_logged_in() { return true; }
$GLOBALS['rsv_probe_manager'] = false;
function current_user_can( $cap ) {
    return 'rsv_publish_reel' === $cap || ( 'rsv_manage_reels' === $cap && ! empty( $GLOBALS['rsv_probe_manager'] ) );
}
function add_filter( ...$args ) { return true; }
function apply_filters( $hook, $default, ...$args ) {
    if ( 'rsv_identity_claims' === $hook ) return array(
        'user_id'=>7, 'contract_version'=>'13', 'source'=>'file00', 'status'=>'active',
        'is_verified_doctor'=>true, 'is_suspended'=>false, 'membership_approved'=>true,
        'session_two_factor'=>true, 'guardian_ok'=>true, 'capabilities'=>array( 'rsv_publish_reel' )
    );
    if ( 'rsv_future30_file05_public_ref_valid' === $hook ) return 'valid-course' === ( $args[0] ?? '' );
    return $default;
}
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-contracts.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-security.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
require dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-future30-ai-context-safety.php';
final class RSV_Plan_Step_Probe { use RSV_Future30_Feature_Write_Trait; }
function ensure( $ok, $message ) { if ( ! $ok ) { fwrite( STDERR, "$message\n" ); exit( 1 ); } }
$publish = RSV_Contracts::CAP_PUBLISH;
ensure( RSV_Security::can( $publish, array( 'owner_id'=>7 ), 'publish_reel' ), 'Owner cannot publish' );
ensure( ! RSV_Security::can( $publish, array( 'owner_id'=>8 ), 'publish_reel' ), 'Publisher can cross-publish another owner' );
$GLOBALS['rsv_probe_manager'] = true;
ensure( RSV_Security::can( $publish, array( 'owner_id'=>8 ), 'publish_reel' ), 'Authorized manager cannot publish cross-owner' );
$GLOBALS['rsv_probe_manager'] = false;
$steps = new ReflectionMethod( RSV_Plan_Step_Probe::class, 'validated_path_steps' );
$valid = $steps->invoke( null, array( array( 'file05_ref'=>'valid-course', 'level'=>'advanced' ) ) );
ensure( is_array( $valid ) && count( $valid ) === 1 && $valid[0]['file05_ref'] === 'valid-course', 'Valid File05 step rejected' );
ensure( is_wp_error( $steps->invoke( null, array() ) ), 'Empty File05 step list permitted' );
ensure( is_wp_error( $steps->invoke( null, array( array( 'file05_ref'=>'withdrawn-course' ) ) ) ), 'Withdrawn File05 step permitted' );
ensure( is_wp_error( $steps->invoke( null, array( array( 'title'=>'unvalidated' ) ) ) ), 'No source reference accepted' );
ensure( is_wp_error( $steps->invoke( null, array( 'invalid' ) ) ), 'Unstructured File05 step accepted' );
ensure( is_wp_error( $steps->invoke( null, array_fill( 0, 101, array( 'file05_ref'=>'valid-course' ) ) ) ), '101+ path steps permitted' );
$safe = new ReflectionMethod( RSV_Future30_AI_Context_Safety::class, 'safe_context_row' );
$row = $safe->invoke( null, 'evidence', array(
    'public_id'=>'ev_public', 'owner_id'=>99, 'patient_email'=>'secret@example.com',
    'payload'=>array( 'grade'=>'Clinical', 'source_owner'=>'File 06','source_refs'=>array( 'source_public' ),'patient_name'=>'private patient','claim_scope'=>'education' )
) );
ensure( isset( $row['public_id'] ) && ! isset( $row['owner_id'] ) && ! isset( $row['patient_email'] ), 'AI context leaked a top-level private field' );
ensure( ! isset( $row['payload']['patient_name'] ) && $row['payload']['source_refs'] === array( 'source_public' ), 'AI context leaked nested private fields' );
$card = $safe->invoke( null, 'knowledge_card', array( 'payload'=>array( 'summary'=>'Safe summary','private_note'=>'do not expose', 'remedy_refs'=>array( 'source_public' ) ) ) );
ensure( ! isset( $card['payload']['private_note'] ) && $card['payload']['remedy_refs'] === array( 'source_public' ), 'Knowledge card private fields leaked' );
$impl = file_get_contents( dirname( __DIR__ ) . '/11-reels-foundation/includes/class-rsv-future30-ai-context-safety.php' );
ensure( strpos( $impl, "'reel' => RSV_Repository::public_dto( $reel )" ) !== false, 'File16 context does not restore canonical Reel DTO' );
$why = file_get_contents( dirname( __DIR__ ) . '/11-reels-foundation/includes/trait-rsv-future30-user-features.php' );
ensure( strpos( $why, "if ( ! is_scalar( \$reason ) ) continue;" ) !== false && strpos( $why, "count( \$reasons ) >= 12" ) !== false, 'Global recommendation provider values are not bounded' );
echo "File11 rc8 object authorization, File05 steps, File16 data minimization and File26 reason contracts PASS\n";
