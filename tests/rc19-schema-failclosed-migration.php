<?php
/** RC19: isolated schema DDL postcondition tests, not Live DB acceptance. */
require __DIR__.'/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
final class RSV_Helpers {
    public static function error($code,$message,$status=503) {
        return new WP_Error($code,$message,array('status'=>$status));
    }
}
final class RC19_Schema_DB {
    public $last_error='';
    public $columns=array('id','public_id','updated_at');
    public $indexes=array(
        array('Key_name'=>'PRIMARY','Non_unique'=>0,'Column_name'=>'id','Seq_in_index'=>1,'Sub_part'=>null),
        array('Key_name'=>'public_id','Non_unique'=>0,'Column_name'=>'public_id','Seq_in_index'=>1,'Sub_part'=>null)
    );
    public $columns_result=true;
    public $indexes_result=true;
    public function get_col($sql,$index=0){return $this->columns_result?$this->columns:null;}
    public function get_results($sql,$type){return $this->indexes_result?$this->indexes:null;}
}
$GLOBALS['wpdb']=new RC19_Schema_DB();
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-db.php';

$ddl="CREATE TABLE wp_rsv_test (
 id bigint unsigned NOT NULL AUTO_INCREMENT,
 public_id varchar(80) NOT NULL,
 updated_at datetime NOT NULL,
 PRIMARY KEY (id),
 UNIQUE KEY public_id (public_id)
) ENGINE=InnoDB;";
$cases=0;$failed=array();
$probe=static function($label,$setup,$expected)use(&$cases,&$failed,$ddl){
  $db=$GLOBALS['wpdb'];
  $db->last_error='';$db->columns=array('id','public_id','updated_at');
  $db->indexes=array(array('Key_name'=>'PRIMARY','Non_unique'=>0,'Column_name'=>'id','Seq_in_index'=>1,'Sub_part'=>null),array('Key_name'=>'public_id','Non_unique'=>0,'Column_name'=>'public_id','Seq_in_index'=>1,'Sub_part'=>null));
  $db->columns_result=true;$db->indexes_result=true;
  $setup($db);
  ++$cases;
  $result=RSV_DB::assert_statements_applied(array($ddl));
  $actual=is_wp_error($result)?$result->get_error_code():'ready';
  if($actual!==$expected)$failed[]="$label: expected $expected, got $actual";
};
$probe('01 ready structure succeeds',static function($db){},'ready');
$probe('02 missing table fails',static function($db){$db->columns_result=false;},'rsv_schema_incomplete');
$probe('03 missing required id column',static function($db){$db->columns=array('public_id','updated_at');},'rsv_schema_incomplete');
$probe('04 missing required timestamp column',static function($db){$db->columns=array('id','public_id');},'rsv_schema_incomplete');
$probe('05 missing unique index',static function($db){$db->indexes=array(array('Key_name'=>'PRIMARY','Non_unique'=>0,'Column_name'=>'id','Seq_in_index'=>1,'Sub_part'=>null));},'rsv_schema_incomplete');
$probe('06 missing primary index',static function($db){$db->indexes=array(array('Key_name'=>'public_id','Non_unique'=>0,'Column_name'=>'public_id','Seq_in_index'=>1,'Sub_part'=>null));},'rsv_schema_incomplete');
$probe('07 nonunique index cannot masquerade as unique',static function($db){$db->indexes[1]['Non_unique']=1;},'rsv_schema_incomplete');
$probe('08 column query SQL error blocks activation',static function($db){$db->last_error='SQL unavailable';},'rsv_schema_incomplete');
$probe('09 index query unavailability blocks activation',static function($db){$db->indexes_result=false;},'rsv_schema_incomplete');
$probe('10 empty columns block activation',static function($db){$db->columns=array();},'rsv_schema_incomplete');
$probe('11 empty index list blocks activation',static function($db){$db->indexes=array();},'rsv_schema_incomplete');

++$cases;$missing=RSV_DB::assert_statements_applied(array('malformed sql'));
if(!is_wp_error($missing)||$missing->get_error_code()!=='rsv_schema_definition_invalid')$failed[]='12 invalid schema DDL must fail';
++$cases;$empty=RSV_DB::assert_statements_applied(array('CREATE TABLE wp_empty (PRIMARY KEY (id));'));
if(!is_wp_error($empty)||$empty->get_error_code()!=='rsv_schema_definition_invalid')$failed[]='13 DDL without columns must fail';
++$cases;$GLOBALS['wpdb']->indexes=array(array('Key_name'=>'PRIMARY','Non_unique'=>0,'Column_name'=>'id','Seq_in_index'=>1,'Sub_part'=>null),array('Key_name'=>'public_id','Non_unique'=>0,'Column_name'=>'public_id','Seq_in_index'=>1,'Sub_part'=>null));
$allowed=RSV_DB::assert_statements_applied(array($ddl,$ddl));
if(true!==$allowed)$failed[]='14 repeated DDL must remain deterministic';

foreach(array(
 '11-reels-foundation/includes/class-rsv-db.php',
 '11-reels-foundation/includes/class-rsv-top20.php',
 '11-reels-foundation/includes/class-rsv-future30.php'
) as $i=>$file){
  ++$cases;
  $src=file_get_contents(dirname(__DIR__).'/'.$file);
  if(!is_string($src)||!str_contains($src,'assert_statements_applied( $sql )'))$failed[]=(15+$i).' missing schema verifier in '.$file;
}
++$cases;$src=file_get_contents(dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-plugin.php');
if(!is_string($src)||!str_contains($src,'rsv_schema_repair_error')||!str_contains($src,'File 11 database migration incomplete'))$failed[]='18 activation/runtime fail-closed guard missing';

++$cases;$repair_source=file_get_contents(dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-diagnostics.php');
if(!is_string($repair_source)
   || !str_contains($repair_source,'RSV_Future30::install()')
   || !str_contains($repair_source,'if ( is_wp_error( $installed ) ) return $installed;'))$failed[]='19 System Repair must fail closed when Future30 schema cannot be verified';


$probe('20 same key name but wrong indexed column',static function($db){$db->indexes[1]['Column_name']='updated_at';},'rsv_schema_incomplete');
$probe('21 index sequence begins at two',static function($db){$db->indexes[1]['Seq_in_index']=2;},'rsv_schema_incomplete');
$probe('22 prefix-truncated unique index',static function($db){$db->indexes[1]['Sub_part']=4;},'rsv_schema_incomplete');
$probe('23 missing index column metadata',static function($db){unset($db->indexes[1]['Column_name']);},'rsv_schema_incomplete');
$probe('24 duplicated index sequence metadata',static function($db){$db->indexes[]=$db->indexes[1];},'rsv_schema_incomplete');
$probe('25 invalid zero index position',static function($db){$db->indexes[1]['Seq_in_index']=0;},'rsv_schema_incomplete');
$probe('26 index name changed to unrelated key',static function($db){$db->indexes[1]['Key_name']='other';},'rsv_schema_incomplete');
$probe('27 incorrectly declared primary indexed column',static function($db){$db->indexes[0]['Column_name']='updated_at';},'rsv_schema_incomplete');

echo 'RC19 schema verification: '.($cases-count($failed)).'/'.$cases.' checks passed'."\n";
foreach($failed as $error)fwrite(STDERR,'FAIL '.$error."\n");
exit($failed===array()?0:1);
