<?php
/**
 * V12.3 F11-NFR-002/003 and F11-FUT-020/021/025/029.
 * SQL private state read outage vs healthy absent state.
 * These are *isolated* PHP fixtures, NOT WordPress/MySQL or live tests.
 */
require __DIR__ . '/bootstrap.php';
if ( ! defined('ARRAY_A') ) define('ARRAY_A','ARRAY_A');
if ( ! defined('HOUR_IN_SECONDS') ) define('HOUR_IN_SECONDS',3600);
function is_user_logged_in(){ return true; }
function rest_ensure_response($data){ return $data; }
function sanitize_html_class($v){return sanitize_key($v);}
function wp_strip_all_tags($v){return strip_tags($v);}
function add_filter($name,$cb,$priority=10,$accepted=1){return true;}
function apply_filters($name,$value,...$rest){return $value;}
function do_action($name,...$rest){$GLOBALS['state_events'][]=$name;}
final class RSV_Contracts { const CAP_MANAGE='manage_reels'; }
final class RSV_Helpers {
    public static function table($name){return 'wp_rsv_'.$name;}
    public static function text($value,$max){return substr((string)$value,0,$max);}
    public static function now(){return '2026-11-14 12:00:00';}
    public static function no_cache_private(){return null;}
    public static function json_decode($raw,$default=array()){
        $val=json_decode((string)$raw,true); return is_array($val)?$val:$default;
    }
    public static function json_encode($value){return json_encode($value);}
    public static function enum($value,$options,$default=''){return in_array($value,$options,true)?$value:$default;}
    public static function error($code,$message,$status=400){
        return new WP_Error($code,$message,array('status'=>$status));
    }
}
final class RSV_Security {
    public static function can_view_reel($row,$user=0){return true;}
    public static function can($cap){return true;}
    public static function rate_limit(...$args){return true;}
    public static function idempotency_begin(...$args){return array('replay'=>false);}
    public static function idempotency_fail(...$args){return null;}
}
final class RSV_Repository {
    public static function find($id,$public=false){
        global $wpdb; $wpdb->last_error='';
        return array('id'=>9,'owner_id'=>7,'public_id'=>'reel_sample');
    }
    public static function public_dto($row){return array('duration_seconds'=>120);}
}
final class F11_State_TestDB {
    public $last_error = '';
    public $raw = null;
    public $fail = '';
    public $queries=0;
    public $reads=0;
    public $writes=0;
    public function prepare($sql,...$args){return $sql;}
    public function get_row($sql,$output=ARRAY_A){
        $this->reads++; $this->last_error=$this->fail;
        return $this->raw;
    }
    public function query($sql){$this->writes++; return 1;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-storage.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-experience.php';
final class F11_State_Service {
    use RSV_Future30_Storage_Trait, RSV_Future30_User_Features_Trait, RSV_Future30_Experience_Trait;
    public static function getState(){
        return self::state_get('F11-FUT-025','user','feed_preferences',array('payload'=>array('muted_topics'=>array()),'version'=>0));
    }
    public static function putState(){
        return self::state_put('F11-FUT-025','user','feed_preferences',array('muted_topics'=>array('clinical')));
    }
}
function check_error($value,$code,$status=503){
    return is_wp_error($value) && $value->get_error_code()===$code &&
        (int)($value->get_error_data()['status']??0)===$status;
}
$errors=array();
$tested=0;
$service=new F11_State_Service();
$default=array('payload'=>array('muted_topics'=>array()),'version'=>0);
$rows=array(
 array('01 genuine missing record',null,'','default'),
 array('02 healthy stored preference',array('payload_json'=>'{"muted_topics":["clinical"]}','version'=>3,'updated_at'=>'2026-11-14'),'','present'),
 array('03 null with SQL error',null,'connection lost','error'),
 array('04 false without SQL error',false,'','error'),
 array('05 array with SQL error',array('payload_json'=>'{}','version'=>1,'updated_at'=>'2026'),'SQL timed out','error'),
 array('06 scalar row rejected','invalid','', 'error'),
);
foreach($rows as $case){
 $db=new F11_State_TestDB();$GLOBALS['wpdb']=$db;$db->raw=$case[1];$db->fail=$case[2];
 $result=F11_State_Service::getState();$tested++;
 if($case[3]==='default'&&$result!==$default)$errors[]=$case[0].' not genuine default';
 if($case[3]==='present'&&(!is_array($result)||($result['version']??-1)!==3||($result['payload']['muted_topics'][0]??'')!=='clinical'))$errors[]=$case[0].' not stored state';
 if($case[3]==='error'&&!check_error($result,'rsv_future_state_query_unavailable'))$errors[]=$case[0].' not typed 503 SQL error';
}
$surface=array(
 array('07 collections GET','collections'),
 array('08 notes GET','notes'),
 array('09 feed preferences GET','feed_preferences'),
 array('10 accessibility GET','accessibility'),
);
foreach($surface as $case){
 $db=new F11_State_TestDB();$db->raw=null;$db->fail='DB unavailable';$GLOBALS['wpdb']=$db;
 $result=$case[1]==='notes'?$service->notes(array('id'=>'reel_sample')):$service->{$case[1]}();
 $tested++;
 if(!check_error($result,'rsv_future_state_query_unavailable'))$errors[]=$case[0].' fabricated successful empty state';
}
$db=new F11_State_TestDB();$db->fail='DB outage';$GLOBALS['wpdb']=$db;
$allow=F11_State_Service::feed_allows_row(array('topic'=>'clinical','owner_id'=>7,'title'=>'Hello'));
$tested++;
if(!check_error($allow,'rsv_future_state_query_unavailable'))$errors[]='11 feed filtering failed open or hid SQL outage on unreadable mute preferences';
$db=new F11_State_TestDB();$db->raw=array('payload_json'=>'{"muted_topics":["clinical"]}','version'=>2,'updated_at'=>'2026');$GLOBALS['wpdb']=$db;
$allow=F11_State_Service::feed_allows_row(array('topic'=>'clinical','owner_id'=>7,'title'=>'Hello'));
$tested++;
if($allow!==false)$errors[]='12 stored mute ignored';
$db=new F11_State_TestDB();$db->raw=array('payload_json'=>'{"muted_topics":["clinical"]}','version'=>2,'updated_at'=>'2026');$GLOBALS['wpdb']=$db;
$allow=F11_State_Service::feed_allows_row(array('topic'=>'research','owner_id'=>7,'title'=>'Hello'));
$tested++;
if($allow!==true)$errors[]='13 healthy unmuted topic rejected';
$db=new F11_State_TestDB();$db->fail='DB inaccessible';$GLOBALS['wpdb']=$db;
$classes=F11_State_Service::body_classes(array('original'));
$tested++;
if(!is_array($classes)||!in_array('original',$classes,true)||!in_array('rsv-a11y-preferences-unavailable',$classes,true))$errors[]='14 inaccessible accessibility settings not reflected safely';
$db=new F11_State_TestDB();$db->raw=array('payload_json'=>'{"high_contrast":true,"captions_size":"large"}','version'=>3,'updated_at'=>'2026');$GLOBALS['wpdb']=$db;
$classes=F11_State_Service::body_classes(array('original'));
$tested++;
if(!is_array($classes)||!in_array('rsv-a11y-high-contrast',$classes,true)||!in_array('rsv-captions-large',$classes,true))$errors[]='15 healthy accessibility preference not applied';
$db=new F11_State_TestDB();$db->raw=null;$db->fail='Post-write read timed out';$GLOBALS['wpdb']=$db;
$after=F11_State_Service::putState();$tested++;
if(!check_error($after,'rsv_future_state_query_unavailable')||$db->writes!==1)$errors[]='16 post-UPSERT SELECT outage reported false success';
$repoSource=file_get_contents(dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-repository.php');
$tested++;
if(!is_string($repoSource)||strpos($repoSource,'if ( is_wp_error( $pref_allowed ) ) return $pref_allowed;')===false) $errors[]='17 feed repository does not propagate typed preference SQL outage instead of healthy empty';
if($errors){fwrite(STDERR,"V12.3 Private State SQL failures:\n".implode("\n",$errors)."\n");exit(1);}
echo 'V12.3 private state read-truth '.$tested.'/'.$tested." isolated tests PASS (not WP/MySQL/staging/live)\n";
