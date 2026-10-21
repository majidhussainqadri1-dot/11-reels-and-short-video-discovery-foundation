<?php
/** RC20 follow-up: isolated MySQL index Sub_part length truth; never accesses live DB. */
require __DIR__.'/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
final class RSV_Helpers {
    public static function error($code,$message,$status=503){return new WP_Error($code,$message,array('status'=>$status));}
}
final class RC20_Prefix_DB {
    public $last_error='';
    public $columns=array('id','target_ref');
    public $indexes=array();
    public function get_col($sql,$position=0){return $this->columns;}
    public function get_results($sql,$format){return $this->indexes;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-db.php';
$ddl="CREATE TABLE wp_rsv_prefix (
 id bigint unsigned NOT NULL AUTO_INCREMENT,
 target_ref varchar(255) NOT NULL,
 PRIMARY KEY (id),
 UNIQUE KEY target_ref (target_ref(120))
) ENGINE=InnoDB;";
$cases=array(
 array('01 exact prefix accepted',120,'ready',null),
 array('02 missing prefix fails',null,'rsv_schema_incomplete',null),
 array('03 shorter prefix fails',40,'rsv_schema_incomplete',null),
 array('04 longer prefix fails',180,'rsv_schema_incomplete',null),
 array('05 string prefix metadata accepted','120','ready',null),
 array('06 unrelated index column fails',120,'rsv_schema_incomplete','id'),
);
$failed=array();
foreach($cases as $case){
    $db=new RC20_Prefix_DB();
    $db->indexes=array(
      array('Key_name'=>'PRIMARY','Non_unique'=>0,'Seq_in_index'=>1,'Column_name'=>'id','Sub_part'=>null),
      array('Key_name'=>'target_ref','Non_unique'=>0,'Seq_in_index'=>1,'Column_name'=>$case[3]??'target_ref','Sub_part'=>$case[1])
    );
    $GLOBALS['wpdb']=$db;
    $actual=RSV_DB::assert_statements_applied(array($ddl));
    $code=is_wp_error($actual)?$actual->get_error_code():'ready';
    if($code!==$case[2])$failed[]=$case[0]." expected ".$case[2]." got ".$code;
}
if($failed){fwrite(STDERR,"Prefix parity failures:\n".implode("\n",$failed)."\n");exit(1);}
echo "RC20 index prefix lengths 6/6 scenarios PASS (synthetic)\n";
