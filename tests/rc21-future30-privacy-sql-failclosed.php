<?php
/**
 * Future30 privacy SQL-failure regression. Uses WordPress-shaped isolated
 * fixtures: does NOT prove a real Hostinger database or live erasure.
 */
require __DIR__ . '/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A', 'ARRAY_A');

$GLOBALS['rc_privacy_hooks'] = array();
function add_filter($hook, $callback, $priority = 10) {
    $GLOBALS['rc_privacy_hooks'][$hook][$priority][] = $callback;
    return true;
}
function get_user_by($field, $value) {
    return $field === 'email' && $value === 'found@example.test' ? (object) array('ID'=>7) : false;
}
final class RSV_Helpers {
    public static function table($name) { return 'wp_rsv_' . $name; }
    public static function error($code, $message, $status = 503) {
        return new WP_Error($code, $message, array('status'=>$status));
    }
    public static function opaque_user_ref($id, $purpose) { return 'opaque-only'; }
    public static function audit(...$args) { return true; }
    public static function outbox(...$args) { return true; }
}
final class RSV_DB {
    public static function transaction($callback) {
        $GLOBALS['wpdb']->tx[] = 'BEGIN';
        $result = $callback();
        $GLOBALS['wpdb']->tx[] = is_wp_error($result) ? 'ROLLBACK' : 'COMMIT';
        return $result;
    }
}
final class RC21_Future_Privacy_DB {
    public $last_error = '';
    public $readResult = array();
    public $readError = '';
    public $idsResult = array();
    public $idsError = '';
    public $remainingResult = '0';
    public $remainingError = '';
    public $tx = array();
    public $queries = array();
    public function prepare($sql, ...$params) { return $sql; }
    public function get_results($sql, $format) {
        $this->queries[]='EXPORT SELECT';
        $this->last_error=$this->readError;
        return $this->readResult;
    }
    public function get_col($sql) {
        $this->queries[]='ERASE SELECT';
        $this->last_error=$this->idsError;
        return $this->idsResult;
    }
    public function get_var($sql) {
        $this->queries[]='COUNT';
        $this->last_error=$this->remainingError;
        return $this->remainingResult;
    }
    public function query($sql) {
        $this->queries[]='DELETE';
        $this->last_error='';
        return 1;
    }
}
require dirname(__DIR__) . '/11-reels-foundation/includes/class-rsv-future30-privacy-integrity.php';

$failed=array();$count=0;
$assert=static function($ok,$label)use(&$failed,&$count){++$count;if(!$ok)$failed[]=$label;};
$exporters=RSV_Future30_Privacy_Integrity::exporters(array(
    'rsv-future30'=>array('callback'=>array('RSV_Future30','privacy_export'))
));
$erasers=RSV_Future30_Privacy_Integrity::erasers(array());
$assert(($exporters['rsv-future30']['callback']??null) === array('RSV_Future30_Privacy_Integrity','export'),
    'Future30 export must use the fail-closed callback');
$assert(($erasers['rsv-future30']['callback']??null) === array('RSV_Future30_Privacy_Integrity','erase'),
    'Future30 erase must use the transactional callback');

foreach(array(
    'null result'=>array(null,''),
    'false result'=>array(false,''),
    'SQL last_error'=>array(array(),'SQL connection lost'),
    'invalid result'=>array('bad',''),
) as $label=>$case) {
    $GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
    $GLOBALS['wpdb']->readResult=$case[0];
    $GLOBALS['wpdb']->readError=$case[1];
    $result=RSV_Future30_Privacy_Integrity::export('found@example.test',1);
    $assert(is_wp_error($result) && $result->get_error_code()==='rsv_future30_export_unavailable'
        && ($result->get_error_data()['status']??null)===503,
        "Export $label must reject incomplete personal-data export");
}
$GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
$result=RSV_Future30_Privacy_Integrity::export('found@example.test',1);
$assert(is_array($result) && ($result['done']??false)===true &&
    ($result['data']??null)===array(), 'Healthy empty export must complete');
$GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
$GLOBALS['wpdb']->readResult=array(array(
    'feature_id'=>'F11-FUT-020','object_ref'=>'reel_example','state_key'=>'collection',
    'payload_json'=>'{"private":true}', 'updated_at'=>'2026-11-13 00:00:00'
));
$result=RSV_Future30_Privacy_Integrity::export('found@example.test',2);
$assert(is_array($result) && count($result['data']??array())===1 &&
    ($result['data'][0]['item_id']??'')==='rsv-f30-2-0',
    'Successful nonempty page must preserve personal-data item');
$GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
$result=RSV_Future30_Privacy_Integrity::export('unknown@example.test',1);
$assert(is_array($result) && ($result['done']??false)===true && !$GLOBALS['wpdb']->queries,
    'Unknown identity must not query private SQL');

foreach(array(
    'null result'=>array(null,''),
    'false result'=>array(false,''),
    'SQL last_error'=>array(array(),'DB unavailable'),
    'invalid result'=>array('invalid',''),
) as $label=>$case) {
    $GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
    $GLOBALS['wpdb']->idsResult=$case[0];$GLOBALS['wpdb']->idsError=$case[1];
    $result=RSV_Future30_Privacy_Integrity::erase('found@example.test',1);
    $assert(is_array($result)&&($result['done']??true)===false
        && ($result['items_retained']??false)===true
        && in_array('ROLLBACK',$GLOBALS['wpdb']->tx,true)
        && !in_array('COMMIT',$GLOBALS['wpdb']->tx,true),
        "Erase lookup $label must fail closed and rollback");
}
$GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
$result=RSV_Future30_Privacy_Integrity::erase('found@example.test',1);
$assert(is_array($result)&&($result['done']??false)===true
    && in_array('COMMIT',$GLOBALS['wpdb']->tx,true)
    && !in_array('DELETE',$GLOBALS['wpdb']->queries,true),
    'Verified healthy empty erasure succeeds without DELETE');

foreach(array(
    'null count'=>array(null,''),
    'false count'=>array(false,''),
    'SQL error'=>array('0','DB unavailable'),
    'nonnumeric count'=>array('unknown',''),
    'empty count'=>array('',''),
) as $label=>$case) {
    $GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
    $GLOBALS['wpdb']->idsResult=array('123');
    $GLOBALS['wpdb']->remainingResult=$case[0];$GLOBALS['wpdb']->remainingError=$case[1];
    $result=RSV_Future30_Privacy_Integrity::erase('found@example.test',1);
    $assert(is_array($result)&&($result['done']??true)===false
        && ($result['items_retained']??false)===true
        && in_array('ROLLBACK',$GLOBALS['wpdb']->tx,true)
        && !in_array('COMMIT',$GLOBALS['wpdb']->tx,true),
        "Erase count $label must rollback rather than falsely certify completion");
}
foreach(array('0'=>true,'2'=>false) as $value=>$expectedDone) {
    $GLOBALS['wpdb']=new RC21_Future_Privacy_DB();
    $GLOBALS['wpdb']->idsResult=array(123);
    $GLOBALS['wpdb']->remainingResult=$value;
    $result=RSV_Future30_Privacy_Integrity::erase('found@example.test',1);
    $assert(is_array($result) && ($result['done']??null)===$expectedDone
        && ($result['items_removed']??false)===true
        && in_array('COMMIT',$GLOBALS['wpdb']->tx,true),
        "Verified count $value must remain correctly paginated");
}
if ($failed) {
    fwrite(STDERR, "Future30 privacy SQL fail-closed regressions:\n" . implode("\n", $failed) . "\n");
    exit(1);
}
echo "Future30 SQL privacy isolation: $count/$count cases PASS (synthetic, not staging/live)\n";
