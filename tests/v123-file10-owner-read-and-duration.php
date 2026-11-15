<?php
/** File11/File10 owner-source duration and SQL availability, isolated PHP only. */
require __DIR__.'/bootstrap.php';
define('VWLB_VERSION','1.2.25-rc1');
define('VWLB_CONTRACT_VERSION','2');
function apply_filters($hook,$initial,...$args){return $initial;}
final class VWLB_Contracts {}
final class VWLB_Videos {
    public static function playback($id){return array();}
    public static function progress(...$args){return true;}
    public static function interact(...$args){return true;}
}
final class V123_File10_DB { public $last_error=''; }
$GLOBALS['wpdb']=new V123_File10_DB();
final class VWLB_Repository {
    public static $video=null;
    public static $fail=false;
    public static $readError=false;
    public static $reads=0;
    public static $resets=0;
    public static function reset_read_failure(){self::$readError=false;++self::$resets;}
    public static function read_failed(){return self::$readError;}
    public static function find($type,$id){
        ++self::$reads;
        if(self::$fail){
            self::$readError=true;
            $GLOBALS['wpdb']->last_error='MySQL SELECT unavailable';
            return null;
        }
        $GLOBALS['wpdb']->last_error='';
        return self::$video;
    }
}
final class RSV_Helpers {
    public static function error($code,$text,$status=503){
        return new WP_Error($code,$text,array('status'=>$status));
    }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-file10.php';
$failed=[];$checks=0;
$assert=static function($ok,$reason)use(&$failed,&$checks){
    ++$checks;if(!$ok)$failed[]=$reason;
};
$base=array(
    'id'=>21,'owner_id'=>7,'duration_seconds'=>60,
    'status'=>'published','rights_status'=>'verified','consent_status'=>'not_patient_case',
    'visibility'=>'public'
);
$reset=static function($row=null,$fail=false){
    VWLB_Repository::$video=$row;
    VWLB_Repository::$fail=$fail;
    VWLB_Repository::$reads=0;
    VWLB_Repository::$resets=0;
    VWLB_Repository::$readError=false;
    $GLOBALS['wpdb']->last_error='';
};
$assert(RSV_File10::ready()===true,'exact VWLB candidate contract available');
foreach(array(59=>false,60=>true,600=>true,601=>false) as $seconds=>$allowed){
    $row=$base;$row['duration_seconds']=$seconds;
    $reset($row);
    $result=RSV_File10::eligible_for_reel(21);
    $assert($result===$allowed&&VWLB_Repository::$reads===1,
        'single authoritative File10 eligibility read at '.$seconds.' sec');
    $reset($row);
    $result=RSV_File10::publicly_eligible(21);
    $assert($result===$allowed&&VWLB_Repository::$reads===1,
        'public File10 eligibility must not read the video twice at '.$seconds.' sec');
    $reset($row);
    $result=RSV_File10::validate_for_reel(21,7);
    $expected=$allowed ? null : 'rsv_duration_invalid';
    $assert($allowed?is_array($result):
        is_wp_error($result)&&$result->get_error_code()===$expected&&
        ($result->get_error_data()['status']??0)===422,
        'server-verified Reel 59/60/600/601 boundary at '.$seconds.' sec');
}
$reset(null,true);
$r=RSV_File10::validate_for_reel(21,7);
$assert(is_wp_error($r)&&$r->get_error_code()==='rsv_video_read_unavailable'&&
    ($r->get_error_data()['status']??null)===503&&
    VWLB_Repository::$resets===1,'File10 SELECT error is typed 503, never missing video 404');
$reset(null);
$r=RSV_File10::validate_for_reel(21,7);
$assert(is_wp_error($r)&&$r->get_error_code()==='rsv_video_missing'&&
    ($r->get_error_data()['status']??null)===404,'healthy absent video remains true 404');
$reset($base);
VWLB_Repository::$readError=true;
$r=RSV_File10::validate_for_reel(21,7);
$assert(is_array($r)&&VWLB_Repository::$resets===1,
    'stale repository failure flag resets before verified successful lookup');
$reset($base);
$r=RSV_File10::validate_for_reel(21,99);
$assert(is_wp_error($r)&&$r->get_error_code()==='rsv_video_forbidden'&&
    ($r->get_error_data()['status']??null)===403,
    'owner mismatch remains forbidden');
$restricted=$base;$restricted['rights_status']='unverified';
$reset($restricted);$r=RSV_File10::validate_for_reel(21,7);
$assert(is_wp_error($r)&&$r->get_error_code()==='rsv_rights_incomplete',
    'rights not declared/verified still reject');
$restricted=$base;$restricted['consent_status']='withdrawn';
$reset($restricted);$r=RSV_File10::validate_for_reel(21,7);
$assert(is_wp_error($r)&&$r->get_error_code()==='rsv_consent_incomplete',
    'withdrawn consent remains rejected');
$restricted=$base;$restricted['status']='deleted';
$reset($restricted);$r=RSV_File10::validate_for_reel(21,7);
$assert(is_wp_error($r)&&$r->get_error_code()==='rsv_video_not_ready',
    'deleted/restricted video remains rejected');
if($failed){fwrite(STDERR,"File10 owner-read fixtures failed:\n".implode("\n",$failed)."\n");exit(1);}
echo "File11 V12.3 File10 duration and SQL truth: $checks/$checks isolated assertions PASS (not real File10 installed integration)\n";
