<?php
/**
 * V12.3 legacy Fresh20 read-truth behavior. WordPress-shaped isolated fixture,
 * not staging, real MySQL, or a live incident acceptance test.
 */
require __DIR__.'/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
function add_action(...$a){return true;}
function add_filter(...$a){return true;}
function esc_html__($m,...$rest){return $m;}
function esc_html($m){return htmlspecialchars((string)$m,ENT_QUOTES,'UTF-8');}
function rest_ensure_response($x){return $x;}
function get_query_var($x){return '';}
function shortcode_atts($defaults,$a,$slug){return array_merge($defaults,(array)$a);}
function apply_filters($filter,$default,...$args){return $default;}
final class RSV_Helpers {
    public static function table($name){return 'wp_rsv_'.$name;}
    public static function text($value,$max){return substr((string)$value,0,$max);}
    public static function json_decode($v,$default=array()){return json_decode((string)$v,true)??$default;}
    public static function error($code,$message,$status=503){return new WP_Error($code,$message,array('status'=>$status));}
}
final class RSV_Repository {
    public static function find($id,$private=false){return array('id'=>44,'public_id'=>'reel_11111111-2222-4333-8444-555555555555','title'=>'Test','caption'=>'Educational');}
    public static function public_dto($reel){return array('duration_seconds'=>120);}
}
final class RSV_Security {
    public static $failed=0;
    public static function can_view_reel(...$args){return true;}
    public static function rate_limit(...$args){return true;}
    public static function idempotency_begin(...$args){return array('replay'=>false);}
    public static function idempotency_fail(...$args){self::$failed++;return true;}
}
final class V123_Fresh_Read_DB {
    public $last_error='';
    public $results=array();
    public $readError='';
    public $row=null;
    public $rowError='';
    public $writes=0;
    public $requests=0;
    public function prepare($q,...$args){return $q;}
    public function get_results($q,$type=ARRAY_A){++$this->requests;$this->last_error=$this->readError;return $this->results;}
    public function get_row($q,$type=ARRAY_A){++$this->requests;$this->last_error=$this->rowError;return $this->row;}
    public function query($sql){++$this->writes;return 1;}
    public function insert(...$args){++$this->writes;return 1;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-fresh20-hardening.php';
$method=static function($name,...$args){
    $reflection=new ReflectionMethod(RSV_Fresh20_Hardening::class,$name);
    return $reflection->invoke(null,...$args);
};
$bad=[];$n=0;
$check=static function($ok,$message)use(&$bad,&$n){++$n;if(!$ok)$bad[]=$message;};
$reset=static function($val=null,$error='',$row=null,$rowError=''){
    $db=new V123_Fresh_Read_DB();
    $db->results=$val;
    $db->readError=$error;
    $db->row=$row;
    $db->rowError=$rowError;
    $GLOBALS['wpdb']=$db;
    return $db;
};
$reel=RSV_Repository::find('example');
foreach(array(null,false,'malformed',array()) as $row) {
    $isSQL=($row===array());
    $db=$reset($row,$isSQL?'forced SQL failure':'');
    $result=$method('safe_edges','F11-FUT-004',44,3,$reel);
    $check(is_wp_error($result)&&$result->get_error_code()==='rsv_future_tools_read_unavailable',
        'safe_edges must return typed SQL unavailable, not false-empty');
    $db=$reset($row,$isSQL?'forced SQL failure':'');
    $result=$method('safe_objects','F11-FUT-018',44,3,$reel);
    $check(is_wp_error($result)&&$result->get_error_code()==='rsv_future_tools_read_unavailable',
        'safe_objects must reject malformed and failed reads');
}
$db=$reset(array());
$check($method('safe_edges','F11-FUT-004',44,3,$reel)===array(),
    'healthy empty edge list remains empty');
$db=$reset(array());
$check($method('safe_objects','F11-FUT-018',44,3,$reel)===array(),
    'healthy empty object list remains empty');
$db=$reset(array(array('invalid')));
$check(is_wp_error($method('safe_edges','F11-FUT-004',44,3,$reel)),
    'edge row must be an array');
$db=$reset(array(array('invalid')));
$check(is_wp_error($method('safe_objects','F11-FUT-018',44,3,$reel)),
    'object row must be an array');
$db=$reset(null,'DB failed');
$html=$method('render_safe_tools',$reel);
$check(is_string($html)&&str_contains($html,'temporarily unavailable')
 && !str_contains($html,'target_ref'),'public HTML shows truthful safe outage, no raw source ID');
$db=$reset(array());
$check($method('render_safe_tools',$reel)==='','healthy no tools returns empty HTML');
foreach(array(null,false,'invalid') as $val) {
    $db=$reset(array(),'', $val, $val===null?'forced SQL':'');
    $res=$method('latest_future_object','F11-FUT-019',44);
    $check(is_wp_error($res)&&$res->get_error_code()==='rsv_future_quiz_read_unavailable',
        'quiz lookup fails closed instead of false 404');
}
$db=$reset(array(),'',null,'');
$check($method('latest_future_object','F11-FUT-019',44)===null,
    'healthy empty quiz lookup stays absent');
$db=$reset(array(),'',array('public_id'=>'f30_1','payload_json'=>'{"questions":[]}','version'=>1,'title'=>'Quiz','updated_at'=>'2026-11-14'),'');
$res=$method('latest_future_object','F11-FUT-019',44);
$check(is_array($res)&&is_array($res['payload']??null),'healthy quiz lookup retains payload');
$check($GLOBALS['wpdb']->writes===0,'all fixture reads create no DB mutations');
if($bad){fwrite(STDERR,"V12.3 Fresh20 read truth failures:\n".implode("\n",$bad)."\n");exit(1);}
echo "V12.3 legacy Fresh20 read SQL truth $n/$n isolated assertions PASS (not staging/live)\n";
