<?php
/** V12.3 candidate-source write-identity SELECT fail-closed tests, not live DB QA. */
require __DIR__.'/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A', 'ARRAY_A');
function add_filter(...$args) { return true; }
function is_user_logged_in() { return true; }
final class RSV_Contracts { const CAP_PUBLISH='rsv_publish'; const CAP_MANAGE='rsv_manage'; }
final class RSV_Helpers {
    public static function table($t) { return 'wp_rsv_'.$t; }
    public static function text($t,$max) { return substr((string)$t,0,$max); }
    public static function now() { return '2026-11-14 22:00:00'; }
    public static function public_id($type) { return $type.'_bbbbbbbb-2222-4333-8444-555555555555'; }
    public static function json_encode($value) { return json_encode($value); }
    public static function error($code,$message,$status=503) { return new WP_Error($code,$message,array('status'=>$status)); }
}
final class RSV_Security {
    public static function can(...$args) { return true; }
}
final class RSV_Repository {
    public static function find($id,$public=false) {
        return array('id'=>2,'public_id'=>'reel_11111111-2222-4333-8444-555555555555','owner_id'=>7);
    }
}
final class V123_Failed_Object_Lookup_DB {
    public $last_error='';
    public $read=null;
    public $read_error='';
    public $writes=0;
    public function prepare($sql,...$args) { return $sql; }
    public function get_row($sql,$kind) { $this->last_error=$this->read_error;return $this->read; }
    public function insert(...$args) { ++$this->writes;return 1; }
    public function update(...$args) { ++$this->writes;return 1; }
}
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-storage.php';
final class V123_Object_Store {
    use RSV_Future30_Storage_Trait;
    public static function put($id='f30_test') {
        return self::put_object('F11-FUT-007','version_snapshot',2,'Snapshot',array('reel_version'=>2),$id);
    }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-future30-write-integrity.php';
final class V123_Object_Request {
    public function get_route() { return '/rsv/v1/reels/reel_11111111-2222-4333-8444-555555555555/future30/F11-FUT-007'; }
    public function get_method() { return 'POST'; }
    public function get_json_params() { return array('public_id'=>'f30_test'); }
}
$errors=[];$checks=0;
$check=static function($ok,$label)use(&$errors,&$checks) {++$checks;if(!$ok)$errors[]=$label;};
$reset=static function($value,$error) {
    $GLOBALS['wpdb']=new V123_Failed_Object_Lookup_DB();
    $GLOBALS['wpdb']->read=$value;
    $GLOBALS['wpdb']->read_error=$error;
    return $GLOBALS['wpdb'];
};
foreach(array(
    array(null,'forced SQL error'),
    array(false,''),
    array('broken','')
) as $case) {
    $db=$reset($case[0],$case[1]);
    $r=V123_Object_Store::put();
    $check(is_wp_error($r)&&$r->get_error_code()==='rsv_future_object_lookup_unavailable'
        && ($r->get_error_data()['status']??0)===503 && $db->writes===0,
        'put_object refuses failed/malformed identity lookup without mutation');
    $db=$reset($case[0],$case[1]);
    $r=RSV_Future30_Write_Integrity::pre_dispatch(null,null,new V123_Object_Request());
    $check(is_wp_error($r)&&$r->get_error_code()==='rsv_future_object_lookup_unavailable'
        && ($r->get_error_data()['status']??0)===503 && $db->writes===0,
        'pre-dispatch guard refuses failed/malformed identity lookup');
}
$db=$reset(null,'');
$r=V123_Object_Store::put();
$check(is_array($r)&&($r['version']??0)===1&&$db->writes===1,
    'healthy absent row allows new object create');
$db=$reset(null,'');
$r=RSV_Future30_Write_Integrity::pre_dispatch(null,null,new V123_Object_Request());
$check($r===null&&$db->writes===0,'healthy absent identity remains allowed at pre-dispatch');
$db=$reset(array('id'=>9,'owner_id'=>7,'version'=>2,'feature_id'=>'F11-FUT-007','reel_id'=>2),'');
$r=V123_Object_Store::put();
$check(is_array($r)&&($r['version']??0)===3&&$db->writes===1,
    'healthy existing row still uses versioned update');
if($errors){fwrite(STDERR,"Object identity SQL failed-read tests:\n".implode("\n",$errors)."\n");exit(1);}
echo "V12.3 object identity write fail-closed $checks/$checks isolated tests PASS\n";
