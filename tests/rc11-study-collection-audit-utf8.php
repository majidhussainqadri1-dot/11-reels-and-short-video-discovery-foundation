<?php
/** RC11: verify Unicode collection IDs and transactional audit/outbox behavior. */
require __DIR__ . '/bootstrap.php';
if ( ! defined( 'HOUR_IN_SECONDS' ) ) define( 'HOUR_IN_SECONDS', 3600 );
function nocache_headers() {}
class RSV_RC11_DB_Stub {
    public static function transaction( $callback ) {
        $before=RSV_RC11_Write_Probe::$state;
        $result=call_user_func($callback);
        if(is_wp_error($result))RSV_RC11_Write_Probe::$state=$before;
        return $result;
    }
}
class_alias('RSV_RC11_DB_Stub','RSV_DB');
class RSV_RC11_Security_Stub {
    public static function rate_limit($scope,$limit,$window){return true;}
}
class_alias('RSV_RC11_Security_Stub','RSV_Security');
final class RSV_RC11_Wpdb_Stub {
    public $prefix='wp_';
    public $records=array();
    public $fail_table='';
    public function insert($table,$data,$formats){
        if($table===$this->fail_table)return false;
        $this->records[]=array('table'=>$table,'data'=>$data);
        return 1;
    }
}
$GLOBALS['wpdb']=new RSV_RC11_Wpdb_Stub();
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-future30-user-features.php';
final class RSV_RC11_Write_Probe {
    use RSV_Future30_User_Features_Trait;
    public static $state=array('payload'=>array('collections'=>array()),'version'=>0);
    private static function state_get($id,$ref,$key,$default=array()){return self::$state;}
    private static function state_put($id,$ref,$key,$payload){
        self::$state=array('payload'=>$payload,'version'=>self::$state['version']+1);
        return self::$state;
    }
    private static function canonical_ref_public_valid($owner,$ref,$purpose){
        return $owner==='File 11' && in_array($ref,array('public-a','public-b'),true);
    }
}
final class RSV_RC11_Request {
    private $p;
    public function __construct($p){$this->p=$p;}
    public function get_json_params(){return $this->p;}
}
function insist($valid,$message){if(!$valid){fwrite(STDERR,"FAIL: $message\n");exit(1);}}
$key=new ReflectionMethod(RSV_RC11_Write_Probe::class,'collection_storage_key');
$a=$key->invoke(null,'مطالعہ','');
$b=$key->invoke(null,'تحقیق','');
insist($a!=='' && $b!=='' && $a!==$b,'Different Urdu names cannot share empty identifiers');
insist($key->invoke(null,'مطالعہ','')===$a,'Unicode key is nondeterministic');
insist($key->invoke(null,'Research','')==='research','ASCII name should keep stable lowercase identifier');
$first=(new RSV_RC11_Write_Probe())->collection_write(new RSV_RC11_Request(array('name'=>'مطالعہ','reels'=>array('public-a'))));
insist(!is_wp_error($first),'Valid Urdu study collection rejected');
insist(isset($first['payload']['collections'][$a]),'Unicode collection not persisted under usable key');
insist(count($GLOBALS['wpdb']->records)===2,'Audit + Outbox both required');
$tables=array_column($GLOBALS['wpdb']->records,'table');
insist(in_array('wp_rsv_audit',$tables,true)&&in_array('wp_rsv_outbox',$tables,true),'No durable audit or outbox evidence');
$events=array_filter($GLOBALS['wpdb']->records,static function($x){return $x['table']==='wp_rsv_outbox';});
$event=current($events);
insist(strpos($event['data']['event_name'],'reelstudycollectionupdated')!==false,'Wrong outbox event identity');
insist(strpos($event['data']['payload_json'],'public-a')===false,'Private Reel IDs leaked into event');
$GLOBALS['wpdb']->fail_table='wp_rsv_audit';
$before=RSV_RC11_Write_Probe::$state;
$failed=(new RSV_RC11_Write_Probe())->collection_write(new RSV_RC11_Request(array('name'=>'تحقیق','reels'=>array('public-b'))));
insist(is_wp_error($failed),'Collection write survived mandatory audit failure');
insist(RSV_RC11_Write_Probe::$state===$before,'Failed audit failed to trigger transactional rollback');
$GLOBALS['wpdb']->fail_table='';
$invalid=(new RSV_RC11_Write_Probe())->collection_write(new RSV_RC11_Request(array('name'=>'غلط','reels'=>array('withdrawn-reel'))));
insist(is_wp_error($invalid),'Withdrawn Reel accepted by collection writer');
echo "RC11 Unicode Study Collection + audited transactional write behavioral tests PASS\n";
