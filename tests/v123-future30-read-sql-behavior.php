<?php
/**
 * Source-level executable SQL read-truth fixture for V12.3 / File11 Issue #45.
 * This uses mocked WordPress DB methods, not MySQL, staging or production.
 */
require __DIR__ . '/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A', 'ARRAY_A');
if (!defined('HOUR_IN_SECONDS')) define('HOUR_IN_SECONDS', 3600);
function is_user_logged_in() { return true; }
function rest_ensure_response($response) { return $response; }
function apply_filters($hook, $value, ...$args) { return $value; }
function do_action(...$args) { return true; }
function sanitize_html_class($v) { return sanitize_key($v); }
function wp_strip_all_tags($v) { return strip_tags((string)$v); }

final class RSV_Helpers {
    public static function table($name) { return 'wp_rsv_'.$name; }
    public static function error($code, $message, $status=503) {
        return new WP_Error($code, $message, array('status'=>$status));
    }
    public static function text($value, $max) { return substr((string)$value, 0, $max); }
    public static function textarea($value, $max) { return substr((string)$value, 0, $max); }
    public static function json_decode($value, $default=array()) { return json_decode((string)$value, true)??$default; }
    public static function json_encode($value) { return json_encode($value); }
    public static function no_cache_private() { return true; }
    public static function now() { return '2026-11-14 18:00:00'; }
    public static function public_id($prefix) { return $prefix.'_11111111-2222-4333-8444-555555555555'; }
}
final class RSV_Contracts { const CAP_MANAGE='manage'; }
final class RSV_Security {
    public static $failCount=0;
    public static function can_view_reel(...$args) { return true; }
    public static function rate_limit(...$args) { return true; }
    public static function can(...$args) { return true; }
    public static function idempotency_begin(...$args) { return array('replay'=>false); }
    public static function idempotency_fail(...$args) { ++self::$failCount; return true; }
}
final class RSV_Repository {
    public static function find($ref, $public=false) {
        return array('id'=>27, 'public_id'=>'reel_11111111-2222-4333-8444-555555555555',
            'owner_id'=>7, 'video_id'=>17, 'language'=>'en-US', 'title'=>'Test', 'caption'=>'Education', 'topic'=>'remedy');
    }
    public static function public_dto($r) { return array('duration_seconds'=>125, 'id'=>$r['public_id']); }
}
final class RSV_Future_Read_DB {
    public $last_error='';
    public $queue=array();
    public $writes=0;
    public $reads=0;
    public function prepare($sql, ...$params) { return $sql; }
    private function pop() {
        ++$this->reads;
        if (!$this->queue) { $this->last_error=''; return array(); }
        $next=array_shift($this->queue);
        $this->last_error=$next[1];
        return $next[0];
    }
    public function get_results($sql,$format=ARRAY_A) { return $this->pop(); }
    public function get_row($sql,$format=ARRAY_A) { return $this->pop(); }
    public function insert(...$args) { ++$this->writes; return 1; }
    public function update(...$args) { ++$this->writes; return 1; }
    public function query($sql) { ++$this->writes; return 1; }
}
final class RSV_Read_Request implements ArrayAccess {
    public function offsetExists($x): bool { return $x==='id'||$x==='feature'; }
    public function offsetGet($x): mixed { return $x==='id'?'reel_11111111-2222-4333-8444-555555555555':'F11-FUT-003'; }
    public function offsetSet($x,$v): void {}
    public function offsetUnset($x): void {}
    public function get_json_params() { return array('second'=>30, 'text'=>'test note', 'answers'=>array()); }
    public function get_header($x) { return 'mock-key'; }
}
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-storage.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-feature-write.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-experience.php';
final class RSV_Read_Service {
    use RSV_Future30_Storage_Trait;
    use RSV_Future30_Feature_Write_Trait;
    use RSV_Future30_User_Features_Trait;
    use RSV_Future30_Experience_Trait;
    public static function definitions() { return array('F11-FUT-003'=>array('slug'=>'related-knowledge')); }
    public static function objects() { return self::object_rows('F11-FUT-001',0,0,5); }
    public static function edges() { return self::edge_rows('F11-FUT-004',27,5); }
    public static function state() { return self::state_get('F11-FUT-025','user','feed_preferences',array('payload'=>array('autoplay'=>false),'version'=>0)); }
    public static function projection() { return self::public_projection('F11-FUT-004',RSV_Repository::find('reel')); }
}
$failed=array();$tests=0;
$assert=static function($ok,$label) use (&$failed,&$tests) { ++$tests; if (!$ok) $failed[]=$label; };
$set=static function($result,$sqlError='') {
    $GLOBALS['wpdb']=new RSV_Future_Read_DB();
    $GLOBALS['wpdb']->queue[]=array($result,$sqlError);
    return $GLOBALS['wpdb'];
};
$testError=static function($result,$code) use ($assert) {
    $assert(is_wp_error($result) && $result->get_error_code()===$code
        && ($result->get_error_data()['status']??null)===503,
        $code.' must be typed 503 (not a false success)');
};
foreach(array(null,false,'invalid') as $bad) {
    $set($bad);
    $testError(RSV_Read_Service::objects(),'rsv_future_objects_unavailable');
    $set($bad);
    $testError(RSV_Read_Service::edges(),'rsv_future_edges_unavailable');
}
$set(array(),'SQL connection failed');
$testError(RSV_Read_Service::objects(),'rsv_future_objects_unavailable');
$set(array(),'SQL connection failed');
$testError(RSV_Read_Service::edges(),'rsv_future_edges_unavailable');
$set(array());
$assert(RSV_Read_Service::objects()===array(),'healthy zero objects remains zero');
$set(array());
$assert(RSV_Read_Service::edges()===array(),'healthy zero edges remains zero');
$set(array(array('payload_json'=>'{"grade":"primary"}','public_id'=>'obj1')));
$out=RSV_Read_Service::objects();
$assert(is_array($out)&&($out[0]['payload']['grade']??'')==='primary'&&!isset($out[0]['payload_json']),'object payload parsed');
$set(array(array('payload_json'=>'{"start":10}','target_ref'=>'src1')));
$out=RSV_Read_Service::edges();
$assert(is_array($out)&&($out[0]['payload']['start']??0)===10&&!isset($out[0]['payload_json']),'edge payload parsed');
$set(array('malformed'));
$testError(RSV_Read_Service::objects(),'rsv_future_objects_invalid');
$set(array('malformed'));
$testError(RSV_Read_Service::edges(),'rsv_future_edges_invalid');
foreach(array(false,'malformed') as $bad) {
    $set($bad);
    $testError(RSV_Read_Service::state(),'rsv_future_state_unavailable');
}
$set(null,'DB read unavailable');
$testError(RSV_Read_Service::state(),'rsv_future_state_unavailable');
$set(null);
$assert(RSV_Read_Service::state()===array('payload'=>array('autoplay'=>false),'version'=>0),
    'healthy missing state retains default');
$set(array('payload_json'=>'{"autoplay":true}','version'=>8,'updated_at'=>'2026-11-14 18:00:00'));
$v=RSV_Read_Service::state();
$assert(is_array($v)&&($v['payload']['autoplay']??null)===true&&($v['version']??0)===8,
    'healthy stored state still readable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->series_list(),'rsv_future_objects_unavailable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->path_list(),'rsv_future_objects_unavailable');
$set(array());
$assert((new RSV_Read_Service())->series_list()===array(),'healthy empty series yields 200 empty');
$set(array());
$assert((new RSV_Read_Service())->path_list()===array(),'healthy empty learning paths yields 200 empty');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->collections(),'rsv_future_state_unavailable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->feed_preferences(),'rsv_future_state_unavailable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->accessibility(),'rsv_future_state_unavailable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->notes(new RSV_Read_Request()),'rsv_future_state_unavailable');
$set(null,'SQL failed');
$assert(RSV_Read_Service::feed_allows_row(array('topic'=>'materia'))===false,
    'feed fails closed when mute preference query unavailable');
$set(null,'SQL failed');
$classes=RSV_Read_Service::body_classes(array('member'));
$assert(in_array('rsv-a11y-state-unavailable',$classes,true) && in_array('rsv-a11y-transcript-only',$classes,true),
    'accessibility query outage produces conservative UI class');
$set(null,'SQL failed');
$testError(RSV_Read_Service::projection(),'rsv_future_objects_unavailable');
$db=$set(array());
$db->queue[]=array(null,'SQL failed');
$testError(RSV_Read_Service::projection(),'rsv_future_edges_unavailable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->reel_feature_get(new RSV_Read_Request()),'rsv_future_objects_unavailable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->knowledge_graph(new RSV_Read_Request()),'rsv_future_objects_unavailable');
$db=$set(array());
$db->queue[]=array(array(),'');
$healthy=(new RSV_Read_Service())->reel_feature_get(new RSV_Read_Request());
$assert(is_array($healthy)&&($healthy['objects']??null)===array()&&($healthy['edges']??null)===array(),
    'healthy empty Reel feature projection is not a 503');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->quiz_attempt(new RSV_Read_Request()),'rsv_future_objects_unavailable');
$set(null,'SQL failed');
$testError((new RSV_Read_Service())->ai_context(new RSV_Read_Request()),'rsv_future_edges_unavailable');
$db=$set(null,'SQL failed');
RSV_Security::$failCount=0;
$res=(new RSV_Read_Service())->note_write(new RSV_Read_Request());
$testError($res,'rsv_future_state_unavailable');
$assert($db->writes===0 && RSV_Security::$failCount===1,'failed note read prevents write and closes idempotency');
if ($failed) {
    fwrite(STDERR, "V12.3 Future30 SQL read regression failures:\n".implode("\n",$failed)."\n");
    exit(1);
}
echo "V12.3 Future30 SQL read truth: $tests/$tests isolated behaviors PASS (not staging/live)\n";
