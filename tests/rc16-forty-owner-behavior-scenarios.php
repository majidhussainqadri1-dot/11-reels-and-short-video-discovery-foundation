<?php
/** Forty *distinct behavioral scenarios* for File00 owner authority and File10 Reel read boundaries.
 * Not live WordPress integration, and not 40 human full-code reviews. */
require __DIR__ . '/bootstrap.php';
function wp_parse_args($in,$defaults) { return array_merge($defaults,(array)$in); }
function is_user_logged_in() { return true; }
$GLOBALS['f11_admin']=false;
$GLOBALS['f11_founder']=false;
$GLOBALS['f11_capabilities']=array('rsv_submit_reel','rsv_publish_reel');
$GLOBALS['f11_authorize']=true;
$GLOBALS['f11_entitlement_allow']=true;
$GLOBALS['f11_member']=array();
$GLOBALS['f11_video_visibility']='public';
$GLOBALS['f11_video_ready']=true;
function user_can($user,$cap) { return $cap==='manage_options' && $GLOBALS['f11_admin']; }
function current_user_can($cap) {
    return ($cap==='manage_options' && $GLOBALS['f11_admin'])
        || in_array($cap,$GLOBALS['f11_capabilities'],true);
}
function smc_membership_assertions($user) { return $GLOBALS['f11_member']; }
function smc_is_founder($user) { return $GLOBALS['f11_founder']; }
function apply_filters($tag,$value,...$args) {
    if($tag==='rsv_authorize') return $GLOBALS['f11_authorize'];
    if($tag==='rsv_reel_entitled') return $GLOBALS['f11_entitlement_allow'];
    return $value;
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
final class RSV_File10 {
    public static function eligible_for_reel($id) { return $id===5 && $GLOBALS['f11_video_ready']; }
    public static function publicly_eligible($id) { return $id===5 && $GLOBALS['f11_video_ready']; }
    public static function video($id) { return $id===5 ? array('visibility'=>$GLOBALS['f11_video_visibility']) : null; }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-security.php';
$normal=array(
  'user_id'=>7,'contract_version'=>'2.1.0','approved'=>true,'eligible'=>true,'suspended'=>false,
  'age_context'=>array('known'=>true,'minor'=>false),'guardian_verified'=>false,
  'approved_membership_types'=>array('doctor'),'professional_verified'=>true,
  'mfa_required'=>false,'session_two_factor'=>false,
  'publishing'=>array('can_open_composer'=>true,'can_direct_publish'=>true),
  'entitlements'=>array('base_services'=>array('education'=>true))
);
$fail=array();$n=0;
$reset=static function() use($normal) {
    $GLOBALS['f11_admin']=false;$GLOBALS['f11_founder']=false;
    $GLOBALS['f11_capabilities']=array('rsv_submit_reel','rsv_publish_reel');
    $GLOBALS['f11_authorize']=true;$GLOBALS['f11_entitlement_allow']=true;
    $GLOBALS['f11_member']=$normal;
    $GLOBALS['f11_video_visibility']='public';$GLOBALS['f11_video_ready']=true;
};
$run=static function($name,$fn,$expected) use($reset,&$n,&$fail) {
    $reset();++$n;
    try {$actual=(bool)$fn();} catch(Throwable $error) { $fail[]="$n $name raised ".$error->getMessage();return;}
    if($actual!==$expected) $fail[]="$n $name: expected ".($expected?'allow':'deny');
};
$cap=static function($which,$owner=null) {
    return RSV_Security::can($which,$owner===null?null:array('owner_id'=>$owner),$which==='rsv_publish_reel'?'publish_reel':'submit_reel');
};
$reel=static function($visibility) {
    return array('status'=>'published','visibility'=>$visibility,'video_id'=>5,'owner_id'=>99);
};
$run('01 verified doctor submits',fn()=> $cap('rsv_submit_reel'),true);
$run('02 doctor with explicit direct authority publishes',fn()=> $cap('rsv_publish_reel'),true);
$run('03 File00 refused direct publish',function()use($cap){$GLOBALS['f11_member']['publishing']['can_direct_publish']=false;return $cap('rsv_publish_reel');},false);
$run('04 File00 refused composer',function()use($cap){$GLOBALS['f11_member']['publishing']['can_open_composer']=false;return $cap('rsv_submit_reel');},false);
$run('05 admin cannot override direct refusal',function()use($cap){$GLOBALS['f11_admin']=true;$GLOBALS['f11_member']['publishing']['can_direct_publish']=false;return $cap('rsv_publish_reel');},false);
$run('06 admin cannot override composer refusal',function()use($cap){$GLOBALS['f11_admin']=true;$GLOBALS['f11_member']['publishing']['can_open_composer']=false;return $cap('rsv_submit_reel');},false);
$run('07 founder authorized composer',function()use($cap){$GLOBALS['f11_founder']=true;$GLOBALS['f11_member']['approved_membership_types']=array();return $cap('rsv_submit_reel');},true);
$run('08 founder without direct File00 permission',function()use($cap){$GLOBALS['f11_founder']=true;$GLOBALS['f11_member']['publishing']['can_direct_publish']=false;return $cap('rsv_publish_reel');},false);
$run('09 membership ineligible',function()use($cap){$GLOBALS['f11_member']['eligible']=false;return $cap('rsv_submit_reel');},false);
$run('10 suspended doctor',function()use($cap){$GLOBALS['f11_member']['suspended']=true;return $cap('rsv_publish_reel');},false);
$run('11 unknown age',function()use($cap){$GLOBALS['f11_member']['age_context']['known']=false;return $cap('rsv_submit_reel');},false);
$run('12 minor missing guardian',function()use($cap){$GLOBALS['f11_member']['age_context']['minor']=true;return $cap('rsv_submit_reel');},false);
$run('13 minor verified guardian',function()use($cap){$GLOBALS['f11_member']['age_context']['minor']=true;$GLOBALS['f11_member']['guardian_verified']=true;return $cap('rsv_submit_reel');},true);
$run('14 retired MFA remains nonblocking',fn()=> $cap('rsv_submit_reel'),true);
$run('15 current required MFA fails closed',function()use($cap){$GLOBALS['f11_member']['mfa_required']=true;return $cap('rsv_submit_reel');},false);
$run('16 current MFA proof accepted',function()use($cap){$GLOBALS['f11_member']['mfa_required']=true;$GLOBALS['f11_member']['session_two_factor']=true;return $cap('rsv_submit_reel');},true);
$run('17 foreign subject claims',function()use($cap){$GLOBALS['f11_member']['user_id']=99;return $cap('rsv_submit_reel');},false);
$run('18 missing owner contract version',function()use($cap){$GLOBALS['f11_member']['contract_version']='';return $cap('rsv_submit_reel');},false);
$run('19 administrator without medical publisher identity',function()use($cap){$GLOBALS['f11_admin']=true;$GLOBALS['f11_member']['approved_membership_types']=array();return $cap('rsv_publish_reel');},false);
$run('20 File00 assert alone may permit qualified doctor',function()use($cap){$GLOBALS['f11_capabilities']=array();return $cap('rsv_submit_reel');},true);
$run('21 authorization filter narrows allow',function()use($cap){$GLOBALS['f11_authorize']=false;return $cap('rsv_submit_reel');},false);
$run('22 authorization filter cannot invent publishing permission',function()use($cap){$GLOBALS['f11_member']['publishing']['can_direct_publish']=false;return $cap('rsv_publish_reel');},false);
$run('23 cross-owner without editorial authority',fn()=> $cap('rsv_publish_reel',99),false);
$run('24 administrator cross-owner without editorial delegation',function()use($cap){$GLOBALS['f11_admin']=true;return $cap('rsv_publish_reel',99);},false);
$run('25 separately granted File11 manager cross-owner',function()use($cap){$GLOBALS['f11_capabilities'][]='rsv_manage_reels';return $cap('rsv_publish_reel',99);},true);
$run('26 own Reel publish allowed',fn()=> $cap('rsv_publish_reel',7),true);
$run('27 empty File00 assertion denied',function()use($cap){$GLOBALS['f11_member']=array();return $cap('rsv_submit_reel');},false);
$run('28 missing native publishing snapshot denied',function()use($cap){unset($GLOBALS['f11_member']['publishing']);return $cap('rsv_publish_reel');},false);
$run('29 File00 education entitlement is free Reel access',fn()=> RSV_Security::can_view_reel($reel('entitled'),7),true);
$run('30 policy may narrow education entitlement',function()use($reel){$GLOBALS['f11_entitlement_allow']=false;return RSV_Security::can_view_reel($reel('entitled'),7);},false);
$run('31 filter cannot invent absent entitlement',function()use($reel){$GLOBALS['f11_member']['entitlements']=array();return RSV_Security::can_view_reel($reel('entitled'),7);},false);
$run('32 explicit reels entitlement still supported',function()use($reel){$GLOBALS['f11_member']['entitlements']=array('reels'=>true);return RSV_Security::can_view_reel($reel('entitled'),7);},true);
$run('33 legacy base_services reels entitlement',function()use($reel){$GLOBALS['f11_member']['entitlements']=array('base_services'=>array('reels'=>true));return RSV_Security::can_view_reel($reel('entitled'),7);},true);
$run('34 eligible member Reel read',fn()=> RSV_Security::can_view_reel($reel('member'),7),true);
$run('35 ineligible member Reel denied',function()use($reel){$GLOBALS['f11_member']['eligible']=false;return RSV_Security::can_view_reel($reel('member'),7);},false);
$run('36 admin does not bypass ineligible private read',function()use($reel){$GLOBALS['f11_admin']=true;$GLOBALS['f11_member']['eligible']=false;return RSV_Security::can_view_reel($reel('member'),7);},false);
$run('37 admin does not bypass absent entitlement',function()use($reel){$GLOBALS['f11_admin']=true;$GLOBALS['f11_member']['entitlements']=array();return RSV_Security::can_view_reel($reel('entitled'),7);},false);
$run('38 unlisted File10 never public Reel',function()use($reel){$GLOBALS['f11_video_visibility']='unlisted';return RSV_Security::can_view_reel($reel('public'),0);},false);
$run('39 unlisted File10 allowed only under unlisted Reel',function()use($reel){$GLOBALS['f11_video_visibility']='unlisted';return RSV_Security::can_view_reel($reel('unlisted'),0);},true);
$run('40 unpublished File10 fails safely',function()use($reel){$GLOBALS['f11_video_ready']=false;return RSV_Security::can_view_reel($reel('public'),0);},false);
if($n!==40) $fail[]="expected 40, got $n";
if($fail){fwrite(STDERR,"RC16 FORTY DISTINCT BEHAVIOR SCENARIOS FAILED:\n".implode("\n",$fail)."\n");exit(1);}
echo "RC16 40/40 File00 publishing, rights and File10 visibility distinct behavioral scenarios PASS\n";
