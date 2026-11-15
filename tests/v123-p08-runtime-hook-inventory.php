<?php
/** V12.3 P08 read-only hook inventory; fake WP callbacks, NOT installed WP evidence. */
require __DIR__.'/bootstrap.php';
define('WP_CLI',true);
define('RSV_VERSION','1.2.0-rc21');
final class WP_CLI {
    public static $printed='';
    public static function line($text){self::$printed=$text;}
}
$GLOBALS['hook_map']=array();
function has_filter($hook,$callback){
    $key=$hook.'|'.$callback[0].'::'.$callback[1];
    return array_key_exists($key,$GLOBALS['hook_map'])?$GLOBALS['hook_map'][$key]:false;
}
$script=dirname(__DIR__).'/tools/v123-p08-runtime-hook-inventory.php';
$checks=[];$failed=[];
$assert=static function($ok,$why)use(&$checks,&$failed){$checks[]=$why;if(!$ok)$failed[]=$why;};
$wanted=array(
    'rest_request_before_callbacks|RSV_Fresh20_Hardening::before_callbacks'=>5,
    'rest_request_before_callbacks|RSV_Future30_Private_Route_Guard::guard'=>5,
    'rest_request_before_callbacks|RSV_Future30_Private_State_Integrity::before_callbacks'=>20,
    'rest_pre_dispatch|RSV_Future30_Public_Safety::pre_dispatch'=>20,
    'rest_post_dispatch|RSV_Fresh20_Hardening::after_dispatch'=>99,
);
$run=static function()use($script){
    WP_CLI::$printed='';
    require $script;
    return json_decode(WP_CLI::$printed,true);
};
$GLOBALS['hook_map']=$wanted;
$r=$run();
$assert(($r['status']??'')==='HOOK_REGISTRATION_MATCHES_CANDIDATE' &&
   ($r['total_expected_hooks']??0)===5 && ($r['missing_or_unexpected_hooks']??1)===0,
   'all five exact hook priorities can be observed');
$assert(($r['release_authorized']??true)===false &&
   ($r['transaction_atomicity']??'')==='UNVERIFIED' &&
   ($r['actual_REST_callback_execution']??'')==='NOT_TESTED',
   'even matching hooks do not establish real transaction or release');
$assert(count($r['observed_hooks']??array())===5 && ($r['observed_hooks'][0]['observed_priority']??0)===5,
   'output contains only bounded class/method/priority metadata');
$assert(strpos(WP_CLI::$printed,'patient')===false &&
   strpos(WP_CLI::$printed,'password')===false,
   'no private strings in inventory');
unset($GLOBALS['hook_map']['rest_request_before_callbacks|RSV_Fresh20_Hardening::before_callbacks']);
$r=$run();
$assert(($r['status']??'')==='HOOK_REGISTRATION_DRIFT_OR_MISSING' &&
   ($r['missing_or_unexpected_hooks']??0)===1 &&
   ($r['observed_hooks'][0]['registered']??true)===false,
   'missing transactional guard must be reported as drift');
$GLOBALS['hook_map']=$wanted;
$GLOBALS['hook_map']['rest_request_before_callbacks|RSV_Fresh20_Hardening::before_callbacks']=50;
$r=$run();
$assert(($r['status']??'')==='HOOK_REGISTRATION_DRIFT_OR_MISSING' &&
   ($r['observed_hooks'][0]['observed_priority']??0)===50,
   'misordered transactional guard must not appear accepted');
$GLOBALS['hook_map']=$wanted;
$GLOBALS['hook_map']['rest_request_before_callbacks|RSV_Future30_Private_Route_Guard::guard']=0;
$r=$run();
$assert(($r['status']??'')==='HOOK_REGISTRATION_DRIFT_OR_MISSING' &&
   ($r['observed_hooks'][1]['registered']??false)===true &&
   ($r['observed_hooks'][1]['observed_priority']??1)===0,
   'priority zero remains registered, never mistaken for missing hook');
if($failed){fwrite(STDERR,"V12.3 hook inventory fixture failures:\n".implode("\n",$failed)."\n");exit(1);}
echo "V12.3 P08 read-only hook registration inventory ".count($checks)."/".count($checks)." isolated assertions PASS (not Live WordPress)\n";
