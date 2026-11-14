<?php
/**
 * P00 read-only freeze in a synthetic WP-CLI context (no real WP/MySQL).
 * Proves redaction, checksum fail-closed, index Sub_part and no DB writes.
 */
require __DIR__.'/bootstrap.php';
if (!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
if (!defined('WP_CLI')) define('WP_CLI',true);
$dir=sys_get_temp_dir().'/file11-p00-fixture-'.getmypid();
$plugindir=$dir.'/reels-foundation-11';
if(!is_dir($plugindir)&&!mkdir($plugindir,0700,true)){fwrite(STDERR,"Cannot build safe temp fixture\n");exit(1);}
define('WP_PLUGIN_DIR',$dir);
$file=$plugindir.'/reels-foundation-11.php';
file_put_contents($file,"<?php\n/* Plugin Name: File 11 */\n");
function get_option($key,$fallback=array()) {
    return $key==='active_plugins'?array('reels-foundation-11/reels-foundation-11.php'):$fallback;
}
function is_multisite() { return false; }
function get_plugin_data($path,$markup=false,$translate=false) {
    return array('Name'=>'File11 test plugin','Version'=>'1.2.0-rc21');
}
function get_bloginfo($key){return '7.0.1';}
function wp_json_encode($v,$options=0){return json_encode($v,$options);}
final class WP_CLI {
    public static $last='';
    public static function line($s){self::$last=$s;}
}
final class V123_ReadOnly_DB {
    public $prefix='wp_';
    public $options='wp_options';
    public $last_error='';
    public $operations=array();
    public function esc_like($value){return addcslashes((string)$value,'_%\\');}
    public function prepare($sql,...$args){
        $this->operations[]='PREPARED SELECT/SHOW';
        return $sql;
    }
    public function get_col($sql){
        if(strpos($sql,'SHOW TABLES')!==0)throw new RuntimeException('unexpected get_col');
        $this->operations[]='SHOW TABLES';
        return array('wp_rsv_reels');
    }
    public function get_results($sql,$format=ARRAY_A){
        if(strpos($sql,'SHOW FULL COLUMNS')===0){
            $this->operations[]='SHOW COLUMNS';
            return array(array('Field'=>'public_id','Type'=>'varchar(80)','Null'=>'NO'));
        }
        if(strpos($sql,'SHOW INDEX')===0){
            $this->operations[]='SHOW INDEX';
            return array(array('Key_name'=>'source_lookup','Column_name'=>'public_id','Seq_in_index'=>1,'Non_unique'=>0,'Sub_part'=>120));
        }
        if(strpos($sql,'SELECT option_name')===0){
            $this->operations[]='SELECT ALLOWLISTED MARKERS';
            return array(
                array('option_name'=>'rsv_schema_version','option_value'=>'1.2.0'),
                array('option_name'=>'rsv_migration_lock','option_value'=>'{"token":"SUPER_SECRET"}'),
                array('option_name'=>'rsv_migration_checkpoint','option_value'=>'v2-batch-3')
            );
        }
        throw new RuntimeException('unexpected query: '.substr($sql,0,70));
    }
    public function get_row($sql,$format=ARRAY_A){
        if(strpos($sql,'SHOW TABLE STATUS')!==0)throw new RuntimeException('unexpected row query');
        $this->operations[]='SHOW TABLE STATUS';
        return array('Engine'=>'InnoDB','Rows'=>'18');
    }
}
$GLOBALS['wpdb']=new V123_ReadOnly_DB();
$path=dirname(__DIR__).'/tools/v123-readonly-freeze.php';
$failures=[];$n=0;
$ok=static function($cond,$what)use(&$failures,&$n){++$n;if(!$cond)$failures[]=$what;};
require $path;
$report=json_decode(WP_CLI::$last,true);
$plugin=$report['active_relevant_plugins'][0]??array();
$cols=$report['relevant_table_schema_and_estimated_rows']['wp_rsv_reels']??array();
$markers=$report['allowlisted_migration_markers']??array();
$ok(($report['mode']??'')==='READ_ONLY_METADATA_ONLY','read-only mode must be explicit');
$ok(($plugin['manifest_complete']??false)===true
 && is_string($plugin['manifest_sha256']??null)
 && ($plugin['file_count']??0)===1,'complete source manifest with SHA256');
$ok(($plugin['header_version']??'')==='1.2.0-rc21','installed header version reported');
$ok(($cols['indexes'][0]['sub_part']??0)===120,'SHOW INDEX.Sub_part preserved');
$ok(($cols['row_count_estimate_not_exact']??null)===18 && ($cols['engine']??'')==='InnoDB',
    'InnoDB and approximate row count labeled as estimate');
$ok(($markers['rsv_schema_version']['value']??null)==='1.2.0','safe version marker retained');
$ok(($markers['rsv_migration_checkpoint']['value']??null)==='v2-batch-3','safe checkpoint marker retained');
$ok(($markers['rsv_migration_lock']['redacted_or_structured']??false)===true
 && ($markers['rsv_migration_lock']['value']??'X')===null,'structured lock marker fully redacted');
$ok(strpos(WP_CLI::$last,'SUPER_SECRET')===false,'no raw migration lock token in output');
$ok(!array_filter($GLOBALS['wpdb']->operations,static function($op){return !in_array($op,array('PREPARED SELECT/SHOW','SHOW TABLES','SHOW COLUMNS','SHOW INDEX','SELECT ALLOWLISTED MARKERS','SHOW TABLE STATUS'),true);}),
    'collector never executes a DB write');
if(function_exists('symlink')&&symlink($file,$plugindir.'/linked-source.php')){
    WP_CLI::$last='';
    require $path;
    $report=json_decode(WP_CLI::$last,true);
    $plugin=$report['active_relevant_plugins'][0]??array();
    $ok(($plugin['manifest_complete']??true)===false
        && ($plugin['manifest_sha256']??'nonempty')===null,
        'symlink omission invalidates manifest SHA instead of claiming parity');
    $ok(in_array('relevant-plugin-symlink-omitted:reels-foundation-11/reels-foundation-11.php',
        $report['errors']??array(),true),'symlink omission is explicitly reported');
    unlink($plugindir.'/linked-source.php');
}
unlink($file);
rmdir($plugindir);
rmdir($dir);
if($failures){fwrite(STDERR,"V12.3 P00 synthetic freeze failures:\n".implode("\n",$failures)."\n");exit(1);}
echo "V12.3 P00 read-only freeze: $n/$n synthetic safety assertions PASS (not live/site parity)\n";
