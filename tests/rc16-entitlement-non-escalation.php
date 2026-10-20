<?php
/** RC15: File00 entitlement is a necessary condition; companion policy only narrows. */
require __DIR__.'/bootstrap.php';
function wp_parse_args($a,$defaults){return array_merge($defaults,(array)$a);}
function user_can($id,$cap){return false;}
function apply_filters($tag,$value,...$args) {
  if ('rsv_identity_claims' === $tag) return array(
    'user_id'=>7, 'contract_version'=>'file00-test-v1', 'status'=>'active',
    'membership_approved'=>true,'guardian_ok'=>true,'is_suspended'=>false,
    'entitlements'=>$GLOBALS['rc15_entitlements']??array()
  );
  if ('rsv_reel_entitled' === $tag) return $GLOBALS['rc15_override']??false;
  return $value;
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
final class RSV_File10 {
  public static function eligible_for_reel($id){return $id===5;}
  public static function publicly_eligible($id){return $id===5;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-security.php';
$reel=array('status'=>'published','visibility'=>'entitled','video_id'=>5,'owner_id'=>99);
$cases=array(
  array(array(),true,false,'filter cannot invent an entitlement'),
  array(array('reels'=>true),true,true,'valid entitlement and allow policy'),
  array(array('reels'=>true),false,false,'policy may narrow valid entitlement'),
  array(array('base_services'=>array('reels'=>true)),true,true,'nested approved entitlement'),
  array(array('base_services'=>array('education'=>true)),true,true,'canonical File00 free education service'),
  array(array('base_services'=>array('education'=>true)),false,false,'File00 education still subject to narrowing policy')
);
foreach($cases as $case){
  $GLOBALS['rc15_entitlements']=$case[0];
  $GLOBALS['rc15_override']=$case[1];
  if(RSV_Security::can_view_reel($reel,7)!==$case[2]){
    fwrite(STDERR,"FAIL: ".$case[3]."\n");exit(1);
  }
}
echo "RC15 File00 entitlement non-escalation 6/6 PASS\n";
