<?php
/**
 * File11 V12.3 P00 read-only WP-CLI evidence collector.
 * Run using: wp eval-file tools/v123-readonly-freeze.php
 * No inserts/updates, no patient/user rows, no secret or option values.
 */
declare(strict_types=1);
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI || !class_exists('WP_CLI')) {
    fwrite(STDERR, "WP-CLI only\n");
    exit(1);
}
global $wpdb;
$errors=[];
$pluginRoot=realpath(WP_PLUGIN_DIR);
$active=(array)get_option('active_plugins',[]);
if(is_multisite()) $active=array_merge($active,array_keys((array)get_site_option('active_sitewide_plugins',[])));
$active=array_values(array_unique($active));
sort($active,SORT_STRING);
$pluginDetails=[];
foreach($active as $pluginName) {
    if(!preg_match('/reel|rsv|short.video/i',(string)$pluginName)) continue;
    $main=realpath(WP_PLUGIN_DIR.'/'.(string)$pluginName);
    if(!$main || !is_file($main) || !$pluginRoot || strpos($main,$pluginRoot.DIRECTORY_SEPARATOR)!==0) {
        $errors[]='plugin-root-cannot-be-verified';
        continue;
    }
    if(!function_exists('get_plugin_data')) require_once ABSPATH.'wp-admin/includes/plugin.php';
    $info=get_plugin_data($main,false,false);
    $root=dirname($main);
    $files=[];$count=0;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS)) as $file) {
        if(++$count>1500) {$errors[]='manifest-over-limit';break;}
        if($file->isLink() || !$file->isFile() || $file->getSize()>10485760) continue;
        $real=$file->getRealPath();
        if(!$real || strpos($real,$root.DIRECTORY_SEPARATOR)!==0) continue;
        $rel=str_replace(DIRECTORY_SEPARATOR,'/',substr($real,strlen($root)+1));
        $hash=hash_file('sha256',$real);
        if($hash!==false) $files[$rel]=$hash;
    }
    ksort($files,SORT_STRING);
    $pluginDetails[]=[
        'plugin_basename'=>(string)$pluginName,
        'header_name'=>(string)($info['Name']??''),
        'header_version'=>(string)($info['Version']??''),
        'file_count'=>count($files),
        'file_sha256'=>$files,
        'manifest_sha256'=>hash('sha256',(string)wp_json_encode($files))
    ];
}
if(!$pluginDetails) $errors[]='no-matching-active-plugin';
$pattern=$wpdb->esc_like((string)$wpdb->prefix).'%';
$all=$wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s',$pattern));
if(!is_array($all) || (string)$wpdb->last_error!=='') {
    $errors[]='table-discovery-error';$all=[];
}
$tables=[];
foreach($all as $t) {
    $t=(string)$t;
    if(strpos($t,(string)$wpdb->prefix)!==0 || !preg_match('/rsv|reel|short_video/i',$t)) continue;
    if(!preg_match('/^[a-zA-Z0-9_]+$/D',$t)) {$errors[]='unusual-identifier';continue;}
    $quoted=chr(96).$t.chr(96);
    $columns=$wpdb->get_results('SHOW FULL COLUMNS FROM '.$quoted,ARRAY_A);
    $validCols=is_array($columns) && (string)$wpdb->last_error==='';
    $indexes=$wpdb->get_results('SHOW INDEX FROM '.$quoted,ARRAY_A);
    $validIdx=is_array($indexes) && (string)$wpdb->last_error==='';
    if(!$validCols || !$validIdx) $errors[]='schema-read-error:'.$t;
    $tables[$t]=[
       'columns'=>$validCols?array_map(static function($x){return [
           'name'=>(string)($x['Field']??''),
           'type'=>(string)($x['Type']??''),
           'null'=>(string)($x['Null']??'')
       ];},$columns):null,
       'indexes'=>$validIdx?array_map(static function($x){return [
           'key'=>(string)($x['Key_name']??''),
           'column'=>(string)($x['Column_name']??''),
           'seq'=>(int)($x['Seq_in_index']??0),
           'unique'=>isset($x['Non_unique']) && (int)$x['Non_unique']===0,
           'sub_part'=>isset($x['Sub_part'])?(int)$x['Sub_part']:null
       ];},$indexes):null
    ];
}
ksort($tables,SORT_STRING);
$names=$wpdb->get_col($wpdb->prepare('SELECT option_name FROM '.$wpdb->options.' WHERE option_name LIKE %s LIMIT 300',$wpdb->esc_like('rsv_').'%'));
if(!is_array($names) || (string)$wpdb->last_error!==''){$errors[]='migration-option-discovery-error';$names=[];}
$markers=array_values(array_filter(array_map('strval',$names),static function($v){
    return (bool)preg_match('/schema|version|migrat|upgrade|contract|lock/i',$v);
}));
sort($markers,SORT_STRING);
$report=[
 'mode'=>'READ_ONLY_METADATA_ONLY',
 'plan'=>'File11 V12.3 P00',
 'wp'=>(string)get_bloginfo('version'),
 'php'=>PHP_VERSION,
 'multisite'=>is_multisite(),
 'active_matching_plugins'=>$pluginDetails,
 'table_schemas'=>$tables,
 'migration_option_names_only'=>$markers,
 'errors'=>array_values(array_unique($errors)),
 'cannot_verify'=>['table row data','migration completion','runtime logs','staging/owner integration','source-to-deployed parity without comparison','R3/R4/R5']
];
WP_CLI::line((string)wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
