<?php
/**
 * File11 Future30 supersession/correction-lineage SQL fail-closed checks.
 * Isolated fixtures, NOT a live or staging WordPress acceptance test.
 */
require __DIR__ . '/bootstrap.php';
function add_filter($name,$callback,$priority=10,$args=1){return true;}
final class RSV_Contracts { const CAP_PUBLISH='publish_reels'; }
final class RSV_Helpers {
    public static function table($name){return 'wp_rsv_'.$name;}
    public static function text($text,$length){return substr((string)$text,0,$length);}
    public static function error($code,$message,$status=503){
        return new WP_Error($code,$message,array('status'=>$status));
    }
}
final class RSV_Security {
    public static function can($cap,$object=null,$reason=''){return true;}
}
final class RC21_Lineage_DB {
    public $last_error='';
    public $edges=array();
    public $badRows=array();
    public $sqlErrors=array();
    public $lastId=0;
    public $calls=array();
    public function prepare($sql,...$params){
        $this->lastId=(int)($params[1]??0);
        return $sql;
    }
    public function get_col($sql){
        $this->calls[]=$this->lastId;
        $this->last_error=$this->sqlErrors[$this->lastId]??'';
        return array_key_exists($this->lastId,$this->badRows)
            ?$this->badRows[$this->lastId]
            :($this->edges[$this->lastId]??array());
    }
}
const SOURCE='reel_11111111-2222-4333-8444-555555555555';
const TARGET='reel_aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee';
const ALTERNATIVE='reel_aaaaaaaa-1111-4222-8333-eeeeeeeeeeee';
$GLOBALS['lineage'] = array();
final class RSV_Repository {
    public static function find($ref,$public=false){
        global $wpdb;
        $wpdb->last_error='';
        if($ref===SOURCE){
            if(!empty($GLOBALS['lineage']['sourceSqlError'])){$wpdb->last_error='SQL refused source';return null;}
            return array('id'=>10,'public_id'=>SOURCE);
        }
        if($ref===TARGET){
            if(!empty($GLOBALS['lineage']['targetSqlError'])){$wpdb->last_error='SQL refused target';return null;}
            return array('id'=>99,'public_id'=>TARGET);
        }
        if($ref===ALTERNATIVE)return array('id'=>88,'public_id'=>ALTERNATIVE);
        return null;
    }
}
final class RC21_Lineage_Request {
    private $route,$method,$payload;
    public function __construct($replacement,$method='POST',$route=null){
        $this->route=$route??'/rsv/v1/reels/'.SOURCE.'/future30/F11-FUT-006';
        $this->method=$method;
        $this->payload=$replacement===null?array():array('replacement_reel_ref'=>$replacement);
    }
    public function get_method(){return $this->method;}
    public function get_route(){return $this->route;}
    public function get_json_params(){return $this->payload;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-future30-public-safety.php';
$checks=array(
    array('01 healthy no active corrections','none',array(),null),
    array('02 self reference blocked','self',array(),'rsv_supersession_cycle'),
    array('03 source SQL outage','source_sql',array(),'rsv_supersession_source_unavailable'),
    array('04 target fetch SQL outage','target_sql',array(),'rsv_supersession_target_unavailable'),
    array('05 target lineage null','target_null',array(),'rsv_supersession_lookup_unavailable'),
    array('06 target lineage false','target_false',array(),'rsv_supersession_lookup_unavailable'),
    array('07 target lineage last_error','target_error',array(),'rsv_supersession_lookup_unavailable'),
    array('08 source lineage false','source_false',array(),'rsv_supersession_lookup_unavailable'),
    array('09 malformed lineage field','invalid_ref',array(),'rsv_supersession_lookup_invalid'),
    array('10 already same active correction','same',array(),null),
    array('11 conflicting correction','other',array(),'rsv_supersession_ambiguous'),
    array('12 cycle via target','cycle',array(),'rsv_supersession_cycle'),
    array('13 unknown destination remains possible','unknown',array(),null),
    array('14 irrelevant GET untouched','get',array(),null),
);
$errors=array();$n=0;
foreach($checks as $case){
    ++$n;$db=new RC21_Lineage_DB();$GLOBALS['wpdb']=$db;$GLOBALS['lineage']=array();$c=$case[1];
    $replacement=TARGET;
    if($c==='self')$replacement=SOURCE;
    if($c==='unknown')$replacement='reel_99999999-2222-4333-8444-555555555555';
    if($c==='source_sql')$GLOBALS['lineage']['sourceSqlError']=true;
    if($c==='target_sql')$GLOBALS['lineage']['targetSqlError']=true;
    if($c==='target_null')$db->badRows[99]=null;
    if($c==='target_false')$db->badRows[99]=false;
    if($c==='target_error')$db->sqlErrors[99]='SQL disconnected';
    if($c==='source_false')$db->badRows[10]=false;
    if($c==='invalid_ref')$db->badRows[99]=array(null);
    if($c==='same')$db->edges[10]=array(TARGET);
    if($c==='other')$db->edges[10]=array(ALTERNATIVE);
    if($c==='cycle')$db->edges[99]=array(SOURCE);
    $request=new RC21_Lineage_Request($replacement, $c==='get'?'GET':'POST');
    $result=RSV_Future30_Public_Safety::pre_dispatch(null,null,$request);
    $errorCode=is_wp_error($result)?$result->get_error_code():null;
    if($errorCode!==$case[3]){
        $errors[]=$case[0].': expected '.($case[3]??'null').', observed '.($errorCode??gettype($result));
    }
    if($case[3]&&is_wp_error($result)&&($result->get_error_data()['status']??0)!==($case[3]==='rsv_supersession_cycle'||$case[3]==='rsv_supersession_ambiguous'?409:503)){
        $errors[]=$case[0].': wrong status';
    }
}
if($errors){fwrite(STDERR,"Supersession SQL failures:\n".implode("\n",$errors)."\n");exit(1);}
echo "Future30 supersession fail-closed $n/$n isolated scenarios PASS (not staging/live)\n";
