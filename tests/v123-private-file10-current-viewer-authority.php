<?php
/**
 * File11 V12.3 / Issue #62: current File10 viewer rights must be an AND
 * restriction on File00/Reel permission for member, entitled and private.
 *
 * Isolated behavior fixture ONLY: no WordPress/MySQL/real File10 installed.
 * This source test must fail on the original private branch bypass.
 */
require __DIR__ . '/bootstrap.php';
function wp_parse_args($actual,$fallback){return array_merge($fallback,(array)$actual);}
function is_user_logged_in(){return true;}
function apply_filters($hook,$value,...$args){
    if($hook==='rsv_can_view_reel')return $GLOBALS['f11_reel_policy'];
    return $value;
}
function smc_membership_assertions($user){
    return array(
        'user_id'=>(int)$user,'contract_version'=>'2.1.0',
        'approved'=>true,'eligible'=>true,'suspended'=>false,
        'age_context'=>array('known'=>true,'minor'=>false),
        'guardian_verified'=>false,
        'approved_membership_types'=>array('doctor'),'professional_verified'=>true,
        'publishing'=>array('can_open_composer'=>true,'can_direct_publish'=>true),
        'mfa_required'=>false,'session_two_factor'=>false,
        'entitlements'=>array('base_services'=>array('education'=>true))
    );
}
function smc_is_founder($user){return false;}
$GLOBALS['f11_reel_policy']=true;
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
final class RSV_File10 {
    public static $viewAllowed = true;
    public static $eligible = true;
    public static $viewerChecks = 0;
    public static $publiclyAllowed = true;
    public static function eligible_for_reel($id){return self::$eligible;}
    public static function publicly_eligible($id){return self::$publiclyAllowed;}
    public static function video($id){return array('visibility'=>'public');}
    public static function current_viewer_authorized($id,$user){
        ++self::$viewerChecks;
        return self::$viewAllowed && $id === 19 && $user === get_current_user_id();
    }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-security.php';
$errs=[]; $checks=0;
$test=static function($ok,$desc)use(&$errs,&$checks){++$checks;if(!$ok)$errs[]=$desc;};
$reel=static function($visibility,$owner=99){
    return array('id'=>14,'public_id'=>'reel_11111111-2222-4333-8444-555555555555',
        'owner_id'=>$owner,'status'=>'published','visibility'=>$visibility,'video_id'=>19);
};
foreach(array(
    array('private',7),
    array('member',99),
    array('entitled',99),
) as [$visibility,$owner]){
    RSV_File10::$eligible=true;
    RSV_File10::$viewAllowed=false;
    RSV_File10::$viewerChecks=0;
    $test(RSV_Security::can_view_reel($reel($visibility,$owner),7)===false,
        $visibility.' current owner-media revocation must override Reel+File00 permit');
    $test(RSV_File10::$viewerChecks===1,
        $visibility.' must consume exactly one current File10 viewer authorization result');
    RSV_File10::$viewAllowed=true;
    RSV_File10::$viewerChecks=0;
    $test(RSV_Security::can_view_reel($reel($visibility,$owner),7)===true,
        $visibility.' current viewer may proceed when all owners allow');
    $test(RSV_File10::$viewerChecks===1,
        $visibility.' checks current media permission on each granted decision');
}
RSV_File10::$viewAllowed=true;
RSV_File10::$viewerChecks=0;
$test(RSV_Security::can_view_reel($reel('member'),99)===false,
    'explicit different viewer must not inherit current WordPress actor media rights');
$test(RSV_File10::$viewerChecks===1,
    'even foreign-subject authorized File00 claims require current File10 actor check');
RSV_File10::$viewerChecks=0;
$GLOBALS['f11_reel_policy']=false;
$test(RSV_Security::can_view_reel($reel('member'),7)===false,
    'Reel policy may narrow an otherwise permitted private viewer');
$GLOBALS['f11_reel_policy']=true;
RSV_File10::$eligible=false;
RSV_File10::$viewerChecks=0;
$test(RSV_Security::can_view_reel($reel('member'),7)===false &&
    RSV_File10::$viewerChecks===0,
    'media ineligibility denies before extra read or identity disclosure');
RSV_File10::$eligible=true;
RSV_File10::$viewerChecks=0;
$test(RSV_Security::can_view_reel($reel('public'),0)===true &&
    RSV_File10::$viewerChecks===0,
    'public branch remains separately governed and does not require a private viewer');
if($errs){fwrite(STDERR,"V12.3 private File10 media-authority regression FAIL:\n".implode("\n",$errs)."\n");exit(1);}
echo "V12.3 File11 private File10 viewer authority: $checks/$checks isolated checks PASS (not real provider/staging)\n";
