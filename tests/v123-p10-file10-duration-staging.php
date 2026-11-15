<?php
/** Mock WP-CLI: File11 P10 staging-only owner duration smoke with no mutations. */
require __DIR__.'/bootstrap.php';
define('WP_CLI', true);
$GLOBALS['test_environment']='staging';
final class WP_CLI {
    public static $printed='';
    public static function line($message){self::$printed=$message;}
}
function wp_get_environment_type(){return $GLOBALS['test_environment'];}
final class RSV_File10 {
    public static $videos=array();
    public static $availability=true;
    public static $reads=0;
    public static function ready(){return self::$availability;}
    public static function video($id){++self::$reads;return self::$videos[$id]??null;}
    public static function validate_for_reel($id,$owner_id=0){
        $row=self::video($id);
        if(!$row)return new WP_Error('rsv_video_missing','Missing',array('status'=>404));
        $duration=(int)$row['duration_seconds'];
        if($duration<60||$duration>600)
            return new WP_Error('rsv_duration_invalid','Wrong duration',array('status'=>422));
        if(!empty($row['fail_policy']))
            return new WP_Error('rsv_rights_incomplete','Rights not verified',array('status'=>422));
        return $row;
    }
}
$paths=array(59=>101,60=>102,600=>103,601=>104);
foreach($paths as $seconds=>$id)putenv('F11_TEST_VIDEO_'.$seconds.'='.$id);
$fixture=static function($durations)use($paths){
    RSV_File10::$videos=array();
    foreach($paths as $seconds=>$id)
        RSV_File10::$videos[$id]=array('id'=>$id,'duration_seconds'=>$durations[$seconds]??$seconds);
};
$fixture(array());
$run=static function(){
    WP_CLI::$printed='';
    require dirname(__DIR__).'/tools/v123-p10-file10-duration-staging.php';
    return json_decode(WP_CLI::$printed,true);
};
$errors=[];$n=0;
$assert=static function($condition,$message)use(&$errors,&$n){++$n;if(!$condition)$errors[]=$message;};
RSV_File10::$reads=0;
$a=$run();
$assert(($a['status']??'')==='STAGING_DURATION_OWNER_READ_ACCEPTED'&&
    ($a['cases_passed']??0)===4&&($a['cases_failed']??1)===0,
    '59/60/600/601 staging fixture correct');
$assert(($a['real_rest_reel_creation_tested']??true)===false&&
    ($a['file10_deployed_package_checksum_verified']??true)===false&&
    ($a['release_authorized']??true)===false,
    'single read smoke never certifies REST, installed ZIP or release');
$assert(RSV_File10::$reads===8,
    'four File10 fixture inspections validate source and status without writes');
$assert(!str_contains(WP_CLI::$printed,'101')&&!str_contains(WP_CLI::$printed,'102'),
    'test media DB IDs are not printed');
$GLOBALS['test_environment']='production';
RSV_File10::$reads=0;
$b=$run();
$assert(($b['status']??'')==='DENIED_NOT_STAGING'&&RSV_File10::$reads===0,
    'production denied before any File10 video reads');
$GLOBALS['test_environment']='staging';
RSV_File10::$availability=false;
$c=$run();
$assert(($c['status']??'')==='FILE10_CONTRACT_UNAVAILABLE',
    'missing File10 contract fails safe');
RSV_File10::$availability=true;
putenv('F11_TEST_VIDEO_59=102');
$d=$run();
$assert(($d['status']??'')==='TEST_IDS_MUST_BE_DISTINCT',
    'four different synthetic video IDs are required');
putenv('F11_TEST_VIDEO_59=101');
putenv('F11_TEST_VIDEO_601=');
$e=$run();
$assert(($e['status']??'')==='FOUR_TEST_IDS_REQUIRED',
    'missing fixture ID blocks instead of returning pass');
putenv('F11_TEST_VIDEO_601=104');
$fixture(array(601=>602));
$f=$run();
$assert(($f['status']??'')==='STAGING_DURATION_ACCEPTANCE_FAILED'&&
    ($f['cases_failed']??0)>=1,
    'wrong source duration rejected even if policy response is also a rejection');
$fixture(array());
RSV_File10::$videos[102]['fail_policy']=true;
$g=$run();
$assert(($g['status']??'')==='STAGING_DURATION_ACCEPTANCE_FAILED'&&
    ($g['cases_failed']??0)>=1,
    'missing media rights must not produce false pass');
$assert(!str_contains(WP_CLI::$printed,'patient')&&!str_contains(WP_CLI::$printed,'password'),
    'read-only staging report never includes personal records/credentials');
if($errors){fwrite(STDERR,"P10 staging duration fixture failures:\n".implode("\n",$errors)."\n");exit(1);}
echo "V12.3 File11 P10 File10 staging-only inspector: $n/$n synthetic assertions PASS (not actual Staging)\n";
