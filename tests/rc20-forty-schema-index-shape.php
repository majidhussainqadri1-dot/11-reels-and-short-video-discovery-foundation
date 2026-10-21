<?php
/** Forty distinct post-dbDelta mutation checks, using isolated MySQL SHOW fixtures.
 * The live WordPress database has NOT been inspected. */
require __DIR__.'/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
final class RSV_Helpers {
    public static function error($code,$message,$status=503){ return new WP_Error($code,$message,array('status'=>$status)); }
}
final class RC20_Indexed_DB {
    public $last_error='';
    public $columns_result=true;
    public $indexes_result=true;
    public $columns=array();
    public $indexes=array();
    public function get_col($sql,$column=0){ return $this->columns_result ? $this->columns : null; }
    public function get_results($sql,$output){ return $this->indexes_result ? $this->indexes : null; }
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-db.php';
$ddl="CREATE TABLE wp_rsv_verify (
 id bigint unsigned NOT NULL AUTO_INCREMENT,
 public_id varchar(80) NOT NULL,
 owner_id bigint unsigned NOT NULL,
 status varchar(30) NOT NULL,
 created_at datetime NOT NULL,
 ref_code varchar(255) NOT NULL,
 PRIMARY KEY (id),
 UNIQUE KEY public_id (public_id),
 KEY owner_status (owner_id,status,created_at),
 KEY target_ref (owner_id,ref_code(120),status)
) ENGINE=InnoDB;";
$make=static function(){
    $db=new RC20_Indexed_DB();
    $db->columns=array('id','public_id','owner_id','status','created_at','ref_code');
    $db->indexes=array(
        array('Key_name'=>'PRIMARY','Non_unique'=>0,'Seq_in_index'=>1,'Column_name'=>'id'),
        array('Key_name'=>'public_id','Non_unique'=>0,'Seq_in_index'=>1,'Column_name'=>'public_id'),
        array('Key_name'=>'owner_status','Non_unique'=>1,'Seq_in_index'=>1,'Column_name'=>'owner_id'),
        array('Key_name'=>'owner_status','Non_unique'=>1,'Seq_in_index'=>2,'Column_name'=>'status'),
        array('Key_name'=>'owner_status','Non_unique'=>1,'Seq_in_index'=>3,'Column_name'=>'created_at'),
        array('Key_name'=>'target_ref','Non_unique'=>1,'Seq_in_index'=>1,'Column_name'=>'owner_id'),
        array('Key_name'=>'target_ref','Non_unique'=>1,'Seq_in_index'=>2,'Column_name'=>'ref_code'),
        array('Key_name'=>'target_ref','Non_unique'=>1,'Seq_in_index'=>3,'Column_name'=>'status')
    );
    return $db;
};
$tests=array(
    array('01 full composite schema','ready',static function($db,&$statements){}),
    array('02 shuffled SHOW INDEX rows','ready',static function($db,&$statements){$db->indexes = array_reverse($db->indexes);}),
    array('03 additional nonrequired index','ready',static function($db,&$statements){$db->indexes[] = array('Key_name'=>'auxiliary','Non_unique'=>1,'Column_name'=>'status','Seq_in_index'=>1);}),
    array('04 additional harmless table column','ready',static function($db,&$statements){$db->columns[] = 'extra_local';}),
    array('05 case-insensitive index names','ready',static function($db,&$statements){foreach ($db->indexes as &$r) $r['Key_name'] = strtoupper($r['Key_name']); unset($r);}),
    array('06 two verified create table statements','ready',static function($db,&$statements){$statements[] = $statements[0];}),
    array('07 missing columns result','rsv_schema_incomplete',static function($db,&$statements){$db->columns_result = false;}),
    array('08 missing index result','rsv_schema_incomplete',static function($db,&$statements){$db->indexes_result = false;}),
    array('09 SQL error during column check','rsv_schema_incomplete',static function($db,&$statements){$db->last_error = 'Database offline';}),
    array('10 no columns at all','rsv_schema_incomplete',static function($db,&$statements){$db->columns = array();}),
    array('11 missing primary column','rsv_schema_incomplete',static function($db,&$statements){$db->columns = array_values(array_diff($db->columns,array('id')));}),
    array('12 missing public identifier column','rsv_schema_incomplete',static function($db,&$statements){$db->columns = array_values(array_diff($db->columns,array('public_id')));}),
    array('13 missing owner column','rsv_schema_incomplete',static function($db,&$statements){$db->columns = array_values(array_diff($db->columns,array('owner_id')));}),
    array('14 missing visibility state column','rsv_schema_incomplete',static function($db,&$statements){$db->columns = array_values(array_diff($db->columns,array('status')));}),
    array('15 missing ordering time column','rsv_schema_incomplete',static function($db,&$statements){$db->columns = array_values(array_diff($db->columns,array('created_at')));}),
    array('16 missing prefixed reference column','rsv_schema_incomplete',static function($db,&$statements){$db->columns = array_values(array_diff($db->columns,array('ref_code')));}),
    array('17 empty index rows','rsv_schema_incomplete',static function($db,&$statements){$db->indexes = array();}),
    array('18 missing PRIMARY index','rsv_schema_incomplete',static function($db,&$statements){$db->indexes = array_values(array_filter($db->indexes,static fn($x)=>$x['Key_name']!=='PRIMARY'));}),
    array('19 PRIMARY accidentally nonunique','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[0]['Non_unique']=1;}),
    array('20 PRIMARY wrong column','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[0]['Column_name']='owner_id';}),
    array('21 missing unique public ID index','rsv_schema_incomplete',static function($db,&$statements){$db->indexes = array_values(array_filter($db->indexes,static fn($x)=>$x['Key_name']!=='public_id'));}),
    array('22 public ID index no longer unique','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[1]['Non_unique']=1;}),
    array('23 public ID index wrong key column','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[1]['Column_name']='id';}),
    array('24 secondary owner index absent','rsv_schema_incomplete',static function($db,&$statements){$db->indexes = array_values(array_filter($db->indexes,static fn($x)=>$x['Key_name']!=='owner_status'));}),
    array('25 secondary owner index accidentally unique','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[2]['Non_unique']=0;}),
    array('26 secondary owner index wrong first column','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[2]['Column_name']='status';}),
    array('27 secondary owner index wrong middle column','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[3]['Column_name']='owner_id';}),
    array('28 secondary owner index wrong last column','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[4]['Column_name']='ref_code';}),
    array('29 secondary owner index missing second column','rsv_schema_incomplete',static function($db,&$statements){array_splice($db->indexes,3,1);}),
    array('30 secondary owner index missing last column','rsv_schema_incomplete',static function($db,&$statements){array_splice($db->indexes,4,1);}),
    array('31 secondary owner index reversed sequence','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[2]['Seq_in_index']=3; $db->indexes[4]['Seq_in_index']=1;}),
    array('32 duplicate secondary sequence','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[3]['Seq_in_index']=1;}),
    array('33 missing sequence metadata','rsv_schema_incomplete',static function($db,&$statements){unset($db->indexes[3]['Seq_in_index']);}),
    array('34 missing column metadata','rsv_schema_incomplete',static function($db,&$statements){unset($db->indexes[3]['Column_name']);}),
    array('35 missing uniqueness metadata','rsv_schema_incomplete',static function($db,&$statements){unset($db->indexes[3]['Non_unique']);}),
    array('36 prefixed reference index absent','rsv_schema_incomplete',static function($db,&$statements){$db->indexes = array_values(array_filter($db->indexes,static fn($x)=>$x['Key_name']!=='target_ref'));}),
    array('37 prefixed reference index wrong second column','rsv_schema_incomplete',static function($db,&$statements){$db->indexes[6]['Column_name']='status';}),
    array('38 missing primary DDL definition','rsv_schema_definition_invalid',static function($db,&$statements){$statements[0] = str_replace('PRIMARY KEY (id),','KEY id (id),',$statements[0]);}),
    array('39 duplicate declared index names','rsv_schema_definition_invalid',static function($db,&$statements){$statements[0] = str_replace('KEY owner_status (owner_id,status,created_at),','KEY owner_status (owner_id,status,created_at), KEY owner_status (status,created_at),',$statements[0]);}),
    array('40 malformed prefix index column','rsv_schema_definition_invalid',static function($db,&$statements){$statements[0] = str_replace('ref_code(120)','ref_code(abc)',$statements[0]);}),
);
$fail=array();
foreach($tests as $test){
    $db=$make();
    $GLOBALS['wpdb']=$db;
    $statements=array($ddl);
    $test[2]($db,$statements);
    try {$response=RSV_DB::assert_statements_applied($statements);}
    catch(Throwable $e){$fail[]=$test[0].' threw '.$e->getMessage();continue;}
    $code=is_wp_error($response)?$response->get_error_code():'ready';
    if($code!==$test[1]) $fail[]=$test[0].": expected ".$test[1].", got ".$code;
}
if(count($tests)!==40)$fail[]='The required forty distinct scenarios were not defined.';
if($fail){fwrite(STDERR,"RC20 INDEX SCHEMA FAILURES:\n".implode("\n",$fail)."\n");exit(1);}
echo "RC20 40/40 distinct Table/Column/Index/DDL postcondition cases PASS (isolated fixtures)\n";
