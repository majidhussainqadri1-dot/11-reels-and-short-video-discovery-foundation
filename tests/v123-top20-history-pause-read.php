<?php
/** V12.3 Fresh20 canonical history pause: source-level SQL read/error regression, no real DB. */
require __DIR__.'/bootstrap.php';
if(!defined('ARRAY_A'))define('ARRAY_A','ARRAY_A');
function add_action(...$a){return true;}
function add_filter(...$a){return true;}
function rest_convert_error_to_response($error){
    return new V123_Pref_Response(array('code'=>$error->get_error_code(),
        'message'=>$error->get_error_message(),
        'data'=>$error->get_error_data()),
        (int)($error->get_error_data()['status']??500));
}
final class V123_Pref_Response {
    public $body,$code,$headers=array();
    public function __construct($body,$code=200){$this->body=$body;$this->code=$code;}
    public function get_data(){return $this->body;}
    public function set_data($body){$this->body=$body;}
    public function header($name,$value){$this->headers[$name]=$value;}
}
final class RSV_Helpers {
    public static function table($n){return 'wp_rsv_'.$n;}
    public static function error($code,$msg,$status=503){return new WP_Error($code,$msg,array('status'=>$status));}
}
final class V123_History_DB {
    public $last_error='';
    public $value=null;
    public $valueError='';
    public $calls=0;
    public function prepare($sql,...$args){return $sql;}
    public function get_var($sql){++$this->calls;$this->last_error=$this->valueError;return $this->value;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-fresh20-hardening.php';
$method=static function($name,...$args){return (new ReflectionMethod(RSV_Fresh20_Hardening::class,$name))->invoke(null,...$args);};
$failed=[];$n=0;
$check=static function($ok,$why)use(&$failed,&$n){++$n;if(!$ok)$failed[]=$why;};
$reset=static function($value,$error=''){$db=new V123_History_DB();$db->value=$value;$db->valueError=$error;$GLOBALS['wpdb']=$db;return $db;};
foreach(array(
    array(null,'SQL failed'),
    array('0','SQL failed'),
    array(false,''),
    array('broken','')
) as $case){
    $reset($case[0],$case[1]);
    $r=$method('canonical_history_paused',7);
    $check(is_wp_error($r)&&$r->get_error_code()==='rsv_history_pause_read_unavailable'&&
        ($r->get_error_data()['status']??0)===503,
        'failed/malformed SELECT must not claim active history');
}
foreach(array(null,'0','1') as $val){
    $reset($val);
    $r=$method('canonical_history_paused',7);
    $check(is_bool($r)&&$r===($val==='1'),
        'legitimate missing/false/true DB state must be exact');
}
$reset(null,'SQL unavailable');
$rest=$method('filter_feed_preferences_response',new V123_Pref_Response(array('payload'=>array('autoplay'=>false))));
$check($rest instanceof V123_Pref_Response && $rest->code===503 &&
       ($rest->body['code']??'')==='rsv_history_pause_read_unavailable'&&
       ($rest->headers['Cache-Control']??'')==='private, no-store, max-age=0',
       'GET filter cannot silently set paused=false during an SQL outage');
$reset('1');
$rest=$method('filter_feed_preferences_response',new V123_Pref_Response(array('payload'=>array('autoplay'=>false))));
$check($rest instanceof V123_Pref_Response&&$rest->code===200&&
    ($rest->body['payload']['history_paused']??false)===true,
    'healthy pause true is preserved after response filtering');
$check($rest->headers['Cache-Control']==='private, no-store, max-age=0',
    'successful private preference GET remains no-store');
if($failed){fwrite(STDERR,"V12.3 history-pause read failures:\n".implode("\n",$failed)."\n");exit(1);}
echo "V12.3 canonical history-pause read truth $n/$n isolated assertions PASS\n";
