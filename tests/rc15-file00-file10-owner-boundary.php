<?php
/** File 00/10 runtime-shaped adversarial regression: no admin identity bypass,
 * no resurrected retired MFA, no unlisted media promoted to a public Reel. */
require __DIR__ . '/bootstrap.php';
function wp_parse_args( $input, $defaults ) { return array_merge( $defaults, (array) $input ); }
function is_user_logged_in() { return true; }
$GLOBALS['rsv_is_admin'] = false;
$GLOBALS['rsv_founder'] = false;
$GLOBALS['rsv_raw_claims'] = array();
$GLOBALS['rsv_media'] = array();
$GLOBALS['rsv_identity_override'] = null;
function current_user_can( $capability ) {
    return ! empty( $GLOBALS['rsv_is_admin'] ) || in_array( $capability, array( 'rsv_submit_reel','rsv_publish_reel' ), true );
}
function user_can( $user, $capability ) { return ! empty( $GLOBALS['rsv_is_admin'] ); }
function apply_filters( $hook, $default, ...$args ) {
    if ( 'rsv_identity_claims' === $hook ) return $GLOBALS['rsv_identity_override'];
    return $default;
}
function smc_membership_assertions( $user_id ) { return $GLOBALS['rsv_raw_claims']; }
function smc_publishing_assertions( $user_id ) {
    return array( 'can_open_composer' => true, 'can_direct_publish' => true );
}
function smc_is_founder( $user_id ) { return ! empty( $GLOBALS['rsv_founder'] ); }
define('VWLB_VERSION','1.2.6');
define('VWLB_CONTRACT_VERSION',3);
final class VWLB_Contracts {}
final class VWLB_Repository {
    public static function find( $type, $id ) { return $GLOBALS['rsv_media']; }
}
final class VWLB_Videos {
    public static function playback( $video ) { return array(); }
    public static function progress( $video, $seconds, $duration ) { return array(); }
    public static function interact( $video, $type ) { return array(); }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-file10.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-security.php';
$failures=array(); $assertions=0;
$check=static function ( $ok, $label ) use ( &$failures, &$assertions ) {
    ++$assertions; if ( ! $ok ) $failures[] = $label;
};
$base=array(
    'user_id'=>7,'contract_version'=>'2.1.0',
    'status'=>'approved','approved'=>true,'eligible'=>true,'suspended'=>false,
    'age_context'=>array('known'=>true,'minor'=>false),
    'guardian_verified'=>false,
    'approved_membership_types'=>array('doctor'),'professional_verified'=>true,
    'mfa_required'=>false,'session_two_factor'=>false,
    'publishing'=>array('can_open_composer'=>true,'can_direct_publish'=>true),
);
$GLOBALS['rsv_raw_claims']=$base;
$check(RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Valid doctor denied by retired MFA');
$check(RSV_Security::can(RSV_Contracts::CAP_PUBLISH),'Valid doctor cannot publish');

$GLOBALS['rsv_raw_claims']=array_merge($base,array('eligible'=>false));
$check(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Ineligible member granted submission');
$GLOBALS['rsv_raw_claims']=array_merge($base,array('suspended'=>true));
$check(!RSV_Security::can(RSV_Contracts::CAP_PUBLISH),'Suspended member granted publish');
$GLOBALS['rsv_raw_claims']=array_merge($base,array('age_context'=>array('known'=>false)));
$check(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Unknown age treated as adult');
$GLOBALS['rsv_raw_claims']=array_merge($base,array('age_context'=>array('known'=>true,'minor'=>true)));
$check(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Minor without verified guardian allowed');
$GLOBALS['rsv_raw_claims']=array_merge($base,array('age_context'=>array('known'=>true,'minor'=>true),'guardian_verified'=>true));
$check(RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Minor with guardian wrongly denied');

$GLOBALS['rsv_raw_claims']=array_merge($base,array('approved_membership_types'=>array(),'professional_verified'=>false));
$GLOBALS['rsv_is_admin']=true;
$check(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Admin bypassed doctor/Founder identity on submit');
$check(!RSV_Security::can(RSV_Contracts::CAP_PUBLISH),'Admin bypassed doctor/Founder identity on publish');
$GLOBALS['rsv_identity_override']=array(
    'user_id'=>7,'contract_version'=>'999','status'=>'active','source'=>'custom',
    'is_founder'=>true,'membership_approved'=>true,'guardian_ok'=>true,
    'session_two_factor'=>true,'capabilities'=>array('rsv_publish_reel')
);
$check(!RSV_Security::can(RSV_Contracts::CAP_PUBLISH),'Foreign filter overrode canonical File00 user identity');
$GLOBALS['rsv_identity_override']=null;
$GLOBALS['rsv_is_admin']=false;
$GLOBALS['rsv_founder']=true;
$check(RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Canonical Founder denied without WordPress admin');
$GLOBALS['rsv_founder']=false;
$GLOBALS['rsv_raw_claims']=array_merge($base,array('mfa_required'=>true,'session_two_factor'=>false));
$check(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Currently required MFA ignored');
$GLOBALS['rsv_raw_claims']=array_merge($base,array('mfa_required'=>true,'session_two_factor'=>true));
$check(RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Verified current MFA denied');
$GLOBALS['rsv_raw_claims']=array_merge($base,array('user_id'=>99));
$check(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Foreign subject membership assertions accepted');
$GLOBALS['rsv_raw_claims']=array();
$check(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT),'Missing File00 assertions granted access');
$GLOBALS['rsv_raw_claims']=$base;
$GLOBALS['rsv_media']=array(
    'id'=>45,'owner_id'=>7,'status'=>'published','visibility'=>'unlisted',
    'duration_seconds'=>80,'rights_status'=>'verified','consent_status'=>'not_patient_case',
);
$public=array('status'=>'published','visibility'=>'public','video_id'=>45,'owner_id'=>7);
$unlisted=array_merge($public,array('visibility'=>'unlisted'));
$check(!RSV_Security::can_view_reel($public,0),'Unlisted File10 asset exposed through public Reel');
$check(RSV_Security::can_view_reel($unlisted,0),'Unlisted Reel with unlisted File10 source incorrectly denied');
$GLOBALS['rsv_media']['visibility']='public';
$check(RSV_Security::can_view_reel($public,0),'Public Reel with public File10 source denied');
$GLOBALS['rsv_media']['owner_id']=99;
$GLOBALS['rsv_is_admin']=true;
$check(is_wp_error(RSV_File10::validate_for_reel(45,7)),'WordPress admin linked another media owner');
$GLOBALS['rsv_media']['owner_id']=7;
$check(is_array(RSV_File10::validate_for_reel(45,7)),'Own approved File10 media denied');
final class RSV_Repository {
    public static function find( $id ) {
        return array( 'id'=>3, 'owner_id'=>7, 'video_id'=>45, 'visibility'=>'public',
            'status'=>'review', 'cover_id'=>12, 'caption'=>'Valid educational caption', 'version'=>1 );
    }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-reels.php';
$GLOBALS['rsv_media']['visibility']='unlisted';
$GLOBALS['rsv_is_admin']=false;
$result=RSV_Reels::publish(3,1);
$check(is_wp_error($result) && $result->get_error_code()==='rsv_public_media_visibility_mismatch',
    'Unlisted File10 video was allowed to be published as public Reel');
$GLOBALS['rsv_media']['visibility']='public';
$result=RSV_Reels::publish(3,1);
$check(is_wp_error($result) && $result->get_error_code()==='rsv_publication_safety_unavailable',
    'Missing mandatory publication safety provider was not fail-closed');
final class RSV_Top20_Test {
    public static $allowed=false;
    public static function publication_gate( $reel ) {
        return self::$allowed ? true : new WP_Error('rsv_source_safety_required','Missing reviewed source card');
    }
}
class_alias( RSV_Top20_Test::class, 'RSV_Top20' );
$result=RSV_Reels::publish(3,1);
$check(is_wp_error($result) && $result->get_error_code()==='rsv_source_safety_required',
    'Unreviewed source/safety/caption claim passed native publish');
if($failures){fwrite(STDERR,"FAIL FILE11 CROSS OWNER\n- ".implode("\n- ",$failures)."\n");exit(1);}
echo "FILE11 FILE00/FILE10 CROSS OWNER: $assertions behavioral assertions PASS\n";
