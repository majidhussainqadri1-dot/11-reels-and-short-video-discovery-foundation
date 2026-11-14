<?php
/** Synthetic safe WP-CLI test for File11 V12.3 Issue38; not actual MySQL concurrency. */
require __DIR__.'/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
if (!defined('WP_CLI')) define('WP_CLI',true);
final class WP_CLI {
    public static $last='';
    public static function line($value){self::$last=$value;}
}
final class V123_P09_DB {
    public $prefix='wp_';
    public $last_error='';
    public $exists=true;
    public $columns=array('feature_id','source_reel_id','edge_type','status','id');
    public $aggregate=array('active_edges'=>'2','distinct_sources'=>'1');
    public $duplicate='1';
    public $summaryError='';
    public $duplicateError='';
    public $operations=array();
    public function esc_like($value){return addcslashes((string)$value,'_%\\');}
    public function prepare($sql,...$args){return $sql;}
    public function get_var($sql){
        if(strpos($sql,'SHOW TABLES')===0){
            $this->operations[]='SHOW TABLES';
            $this->last_error='';
            return $this->exists?'wp_rsv_future_edges':null;
        }
        if(strpos($sql,'SELECT COUNT(*) FROM (SELECT source_reel_id')===0){
            $this->operations[]='SELECT GROUP COUNT';
            $this->last_error=$this->duplicateError;
            return $this->duplicate;
        }
        throw new RuntimeException('unexpected get_var');
    }
    public function get_col($sql){
        if(strpos($sql,'SHOW COLUMNS')!==0)throw new RuntimeException('unexpected get_col');
        $this->operations[]='SHOW COLUMNS';
        $this->last_error='';
        return $this->columns;
    }
    public function get_row($sql,$format=ARRAY_A){
        if(strpos($sql,'SHOW TABLE STATUS')===0){
            $this->operations[]='SHOW TABLE STATUS';
            $this->last_error='';
            return array('Engine'=>'InnoDB','Rows'=>75);
        }
        if(strpos($sql,'SELECT COUNT(*) AS active_edges')===0){
            $this->operations[]='SELECT SUMMARY';
            $this->last_error=$this->summaryError;
            return $this->aggregate;
        }
        throw new RuntimeException('unexpected get_row');
    }
}
$script=dirname(__DIR__).'/tools/v123-p09-lineage-readonly.php';
$fail=[];$n=0;
$assert=static function($valid,$reason)use(&$fail,&$n){++$n;if(!$valid)$fail[]=$reason;};
$run=static function(V123_P09_DB $db)use($script){
    $GLOBALS['wpdb']=$db;
    WP_CLI::$last='';
    require $script;
    return json_decode(WP_CLI::$last,true);
};
$db=new V123_P09_DB();
$r=$run($db);
$assert(($r['status']??null)==='existing_ambiguous_lineage_detected'
 && ($r['active_supersession_edges']??0)===2
 && ($r['distinct_source_reels']??0)===1
 && ($r['ambiguous_source_groups']??0)===1,
 'existing ambiguous lineage detected by read-only aggregate');
$assert(($r['concurrency_atomicity_proven']??true)===false
 && ($r['live_resolved']??true)===false,
 'duplicate read never claims atomicity or live resolution');
$assert(!preg_match('/source_ref|target_ref|patient|token/i',WP_CLI::$last),
 'no private Reel refs or patient identifiers printed');
$assert(count($db->operations)===5,'P09 collector uses only 5 read-only queries');
$db=new V123_P09_DB();$db->aggregate=array('active_edges'=>'0','distinct_sources'=>'0');$db->duplicate='0';
$r=$run($db);
$assert(($r['status']??'')==='no_existing_ambiguity_in_this_snapshot'
 && ($r['concurrency_atomicity_proven']??true)===false,
 'empty snapshot still cannot claim concurrency atomicity');
$db=new V123_P09_DB();$db->exists=false;
$r=$run($db);
$assert(($r['status']??'')==='required_table_missing'
 && ($r['ambiguous_source_groups']??'unset')===null,
 'missing schema not misclassified as empty healthy DB');
$db=new V123_P09_DB();$db->columns=array('feature_id','source_reel_id');
$r=$run($db);
$assert(($r['status']??'')==='incomplete_schema',
 'partial schema not treated as healthy');
$db=new V123_P09_DB();$db->summaryError='test SELECT interrupted';
$r=$run($db);
$assert(($r['status']??'')==='aggregate_read_unavailable'
 && ($r['active_supersession_edges']??'unset')===null,
 'summary SQL error stays unverified');
$db=new V123_P09_DB();$db->duplicateError='test SQL interrupted';
$r=$run($db);
$assert(($r['status']??'')==='duplicate_group_read_unavailable'
 && ($r['active_supersession_edges']??'unset')===null,
 'duplicate SQL error does not fabricate successful zero');
$db=new V123_P09_DB();$db->aggregate=array('active_edges'=>'2','distinct_sources'=>'2');$db->duplicate='1';
$r=$run($db);
$assert(($r['status']??'')==='inconsistent_aggregate_reads',
 'inconsistent reads do not certify lineage');
if($fail){fwrite(STDERR,"P09 read-only fixture failures:\n".implode("\n",$fail)."\n");exit(1);}
echo "V12.3 P09 aggregate inspection: $n/$n synthetic read-only assertions PASS (not MySQL concurrency)\n";
