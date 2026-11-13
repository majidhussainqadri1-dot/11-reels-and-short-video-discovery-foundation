<?php
/** Native Future30 private-state write: failed SELECT must not trigger INSERT/UPDATE.
 * WordPress-shaped isolated behavior; not an installed WordPress test. */
require __DIR__ . '/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
if (!defined('HOUR_IN_SECONDS')) define('HOUR_IN_SECONDS',3600);
function add_action(...$a){return true;}
function add_filter(...$a){return true;}
function is_user_logged_in(){return true;}
function rest_ensure_response($data){return $data;}
final class RSV_Helpers {
    public static function text($value,$max){return substr((string)$value,0,$max);}
    public static function textarea($value,$max){return substr((string)$value,0,$max);}
    public static function public_id($type){return $type.'_00000000-2222-4333-8444-555555555555';}
    public static function table($name){return 'wp_rsv_'.$name;}
    public static function now(){return '2026-11-13 09:00:00';}
    public static function json_decode($value,$default=array()){return json_decode($value,true)??$default;}
    public static function json_encode($value){return json_encode($value);}
    public static function error($code,$message,$status=503){return new WP_Error($code,$message,array('status'=>$status));}
    public static function audit(...$args){return true;}
    public static function outbox(...$args){return true;}
    public static function opaque_user_ref($id,$context){return 'private-ref';}
    public static function no_cache_private(){return true;}
}
final class RSV_Security {
    public static function rate_limit(...$a){return true;}
    public static function idempotency_begin(...$a){return array('replay'=>false);}
    public static function idempotency_finish(...$a){return true;}
    public static function idempotency_fail(...$a){return true;}
    public static function can_view_reel(...$a){return true;}
}
final class RSV_DB {
    public static function transaction($callback){
        $GLOBALS['wpdb']->tx[]='BEGIN';
        $value=$callback();
        $GLOBALS['wpdb']->tx[]=is_wp_error($value)?'ROLLBACK':'COMMIT';
        return $value;
    }
}
final class RSV_Repository {
    public static function find($id,$public=false){
        return array('public_id'=>'reel_11111111-2222-4333-8444-555555555555','id'=>7,'video_id'=>1);
    }
    public static function public_dto($row){return array('duration_seconds'=>120);}
}
final class RC21_SQL_Error_Store {
    public $last_error='';
    public $tx=array();
    public $writes=0;
    public $errorOnRead=1;
    public $reads=0;
    public function prepare($sql,...$args){return $sql;}
    public function get_row($sql,$output=ARRAY_A){
        ++$this->reads;
        $this->last_error=$this->reads===$this->errorOnRead?'SQL SELECT refused':'';
        return null;
    }
    public function get_var($sql){$this->last_error='';return '0';}
    public function insert(...$args){$this->writes++;return 1;}
    public function update(...$args){$this->writes++;return 1;}
}
final class RC21_Priv_Request implements ArrayAccess {
    private $route,$data;
    public function __construct($route,$data){$this->route=$route;$this->data=$data;}
    public function offsetExists($offset):bool{return $offset==='id';}
    public function offsetGet($offset):mixed{return $offset==='id'?'reel_11111111-2222-4333-8444-555555555555':null;}
    public function offsetSet($offset,$value):void{}
    public function offsetUnset($offset):void{}
    public function get_route(){return $this->route;}
    public function get_method(){return 'POST';}
    public function get_json_params(){return $this->data;}
    public function get_header($name){return 'fixture-idempotency-key';}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-fresh20-hardening.php';
$errors=array();$n=0;
foreach(array(
 array('/rsv/v1/future30/collections',array('name'=>'Test Collection','reels'=>array()),'rsv_future30_private_read_unavailable'),
 array('/rsv/v1/reels/reel_11111111-2222-4333-8444-555555555555/notes',array('second'=>10,'text'=>'Test private note'),'rsv_future30_private_read_unavailable'),
 array('/rsv/v1/future30/feed-preferences',array('diversity'=>60),'rsv_future30_private_read_unavailable'),
 array('/rsv/v1/future30/accessibility',array('high_contrast'=>true),'rsv_future30_private_read_unavailable')
) as $test) {
    ++$n;$GLOBALS['wpdb']=new RC21_SQL_Error_Store();
    $result=RSV_Fresh20_Hardening::before_callbacks(null,null,new RC21_Priv_Request($test[0],$test[1]));
    if(!is_wp_error($result)||$result->get_error_code()!==$test[2]||
        $GLOBALS['wpdb']->writes!==0||
        !in_array('ROLLBACK',$GLOBALS['wpdb']->tx,true)||
        in_array('COMMIT',$GLOBALS['wpdb']->tx,true)) {
        $errors[]=$test[0].' must reject SQL failure and avoid mutation';
    }
}
++$n;
$GLOBALS['wpdb']=new RC21_SQL_Error_Store();
$GLOBALS['wpdb']->errorOnRead=2;
$result=RSV_Fresh20_Hardening::before_callbacks(null,null,
    new RC21_Priv_Request('/rsv/v1/future30/feed-preferences',array('history_paused'=>true)));
if(!is_wp_error($result) || $result->get_error_code()!=='rsv_future30_private_read_unavailable' ||
    !in_array('ROLLBACK',$GLOBALS['wpdb']->tx,true) ||
    in_array('COMMIT',$GLOBALS['wpdb']->tx,true)){
    $errors[]='History-pause secondary SELECT SQL failure must roll back the entire transaction';
}
++$n;
$GLOBALS['wpdb']=new RC21_SQL_Error_Store();
$GLOBALS['wpdb']->errorOnRead=0;
$result=RSV_Fresh20_Hardening::before_callbacks(null,null,
    new RC21_Priv_Request('/rsv/v1/future30/accessibility',array('high_contrast'=>true)));
if(is_wp_error($result)||$GLOBALS['wpdb']->writes!==1 ||
    !in_array('COMMIT',$GLOBALS['wpdb']->tx,true)){
    $errors[]='Healthy nonexistent private-state row must allow initial INSERT';
}
$fixture=dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-fresh20-hardening.php';
$source=file_get_contents($fixture);
foreach(array(
 'F11-FUT-020','F11-FUT-021','F11-FUT-025','F11-FUT-029'
) as $id) {
    ++$n;
    if(strpos($source,$id)===false)$errors[]="$id missing";
}
if($errors){fwrite(STDERR,"Future30 private SELECT failed-write regressions:\n".implode("\n",$errors)."\n");exit(1);}
echo "Future30 private-state write SQL fail-closed $n/$n isolated/static scenarios PASS\n";
