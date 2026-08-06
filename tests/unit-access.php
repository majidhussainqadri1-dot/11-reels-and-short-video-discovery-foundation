<?php
require __DIR__.'/bootstrap.php';
class RSV_Security { public static function can(){return !empty($GLOBALS['rsv_manage']);} public static function claims(){return $GLOBALS['rsv_claims']??array('status'=>'unavailable','is_suspended'=>true);} }
class RSV_File10 { public static function eligible($id,$allow=false){return $allow?($GLOBALS['rsv_f10_secure']??true):($GLOBALS['rsv_f10_public']??true);} }
require_once __DIR__.'/../11-reels-foundation/includes/class-rsv-access.php';
$passed=0;$failed=0;function check_access($condition,$label){global $passed,$failed;if($condition){$passed++;echo "PASS: $label\n";}else{$failed++;echo "FAIL: $label\n";}}
$base=array('id'=>1,'owner_id'=>9,'video_id'=>10,'status'=>'published','visibility'=>'public','share_token_hash'=>'');
$GLOBALS['rsv_logged']=false;$GLOBALS['rsv_f10_public']=true;check_access(RSV_Access::can_view($base),'public Reel view allowed when File 10 public');
$GLOBALS['rsv_f10_public']=false;check_access(!RSV_Access::can_view($base),'public Reel fails closed when File 10 not public');
$member=$base;$member['visibility']='member';$GLOBALS['rsv_logged']=true;$GLOBALS['rsv_user']=5;$GLOBALS['rsv_claims']=array('status'=>'active','is_suspended'=>false);$GLOBALS['rsv_f10_secure']=true;check_access(RSV_Access::can_view($member),'active member can view member Reel');
$GLOBALS['rsv_claims']=array('status'=>'active','is_suspended'=>true);check_access(!RSV_Access::can_view($member),'suspended member denied');
$entitled=$base;$entitled['visibility']='entitled';$GLOBALS['rsv_claims']=array('status'=>'active','is_suspended'=>false);$GLOBALS['rsv_filters']['rsv_entitlement_check']=fn()=>true;check_access(RSV_Access::can_view($entitled),'entitlement provider can grant access');
$token='secret-once';$unlisted=$base;$unlisted['visibility']='unlisted';$unlisted['share_token_hash']=hash_hmac('sha256',$token,wp_salt('auth'));check_access(RSV_Access::can_view($unlisted,'view',$token),'valid unlisted token accepted');check_access(!RSV_Access::can_view($unlisted,'view','wrong'),'invalid unlisted token rejected');
echo "Passed: $passed\nFailed: $failed\n";exit($failed?1:0);
