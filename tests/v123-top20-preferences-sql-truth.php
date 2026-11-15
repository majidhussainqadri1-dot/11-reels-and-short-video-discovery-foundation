<?php
/**
 * V12.3 Top20 privacy controls: failed DB read may NOT become watch-history enabled.
 * Mock SQL only; not installed WordPress, MySQL concurrency or Live acceptance.
 */
require __DIR__.'/bootstrap.php';
if(!defined('ARRAY_A'))define('ARRAY_A','ARRAY_A');
function apply_filters($name,$value,...$args){return $value;}
final class RSV_Security {
    public static function claims($user_id=0){return array('is_minor'=>false,'age_band'=>'adult');}
}
final class RSV_Helpers {
    public static function table($n){return 'wp_rsv_'.$n;}
    public static function error($code,$msg,$status=503){return new WP_Error($code,$msg,array('status'=>$status));}
    public static function now(){return '2026-11-15 09:00:00';}
    public static function audit(...$args){return true;}
}
final class RSV_DB {
    public static function transaction($f){return $f();}
}
final class V123_Top20_Pref_DB {
    public $last_error='';
    public $row=null;
    public $readError='';
    public $writes=0;
    public $reads=0;
    public function prepare($sql,...$args){return $sql;}
    public function get_row($sql,$format=ARRAY_A){++$this->reads;$this->last_error=$this->readError;return $this->row;}
    public function insert(...$args){++$this->writes;return 1;}
    public function update(...$args){++$this->writes;return 1;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-top20-context.php';
final class V123_Top20_Prefs {
    use RSV_Top20_Context_Trait;
}
$failed=[];$n=0;
$check=static function($ok,$label)use(&$failed,&$n){++$n;if(!$ok)$failed[]=$label;};
$reset=static function($row,$error=''){
    $GLOBALS['wpdb']=new V123_Top20_Pref_DB();
    $GLOBALS['wpdb']->row=$row;
    $GLOBALS['wpdb']->readError=$error;
    return $GLOBALS['wpdb'];
};
$db=$reset(null,'SQL connection unavailable');
$r=V123_Top20_Prefs::preferences();
$check(is_array($r)&&!empty($r['read_unavailable'])&&!empty($r['history_paused'])&&
       !empty($r['youth_safe'])&&!empty($r['mandatory_youth_safe'])===false,
    'SQL error returns conservative private/history/youth-safe fallback with unavailable marker');
$check($db->writes===0&&$db->reads===1,'failed preference SELECT cannot write');
$db=$reset(false,'');
$r=V123_Top20_Prefs::preferences();
$check(!empty($r['read_unavailable'])&&$r['history_paused']===true&&$r['youth_safe']===true,
    'malformed false result is not healthy missing row');
$db=$reset(null);
$r=V123_Top20_Prefs::preferences();
$check(empty($r['read_unavailable'])&&$r['history_paused']===false&&$r['youth_safe']===false&&$r['version']===0,
    'healthy first-time absent preferences remain normal default');
$db=$reset(array(
    'youth_safe'=>0,'history_paused'=>1,'session_limit_minutes'=>20,
    'natural_stop_every'=>6,'late_night_reminder'=>0,'version'=>9
));
$r=V123_Top20_Prefs::preferences();
$check(empty($r['read_unavailable'])&&$r['history_paused']===true&&$r['version']===9,
    'healthy existing preferences preserve pause and version');
$db=$reset(null,'SQL unavailable');
$r=V123_Top20_Prefs::save_preferences(array('history_paused'=>false));
$check(is_wp_error($r)&&$r->get_error_code()==='rsv_preferences_read_unavailable'&&
       ($r->get_error_data()['status']??0)===503&&$db->writes===0,
    'write aborts on failed current-preference SELECT');
$db=$reset(false);
$r=V123_Top20_Prefs::save_preferences(array('history_paused'=>false));
$check(is_wp_error($r)&&$r->get_error_code()==='rsv_preferences_read_unavailable'&&$db->writes===0,
    'write aborts on malformed current-preference SELECT');
if($failed){fwrite(STDERR,"Top20 preference read-truth failed:\n".implode("\n",$failed)."\n");exit(1);}
echo "V12.3 Top20 privacy preferences: $n/$n isolated SQL and fail-closed cases PASS\n";
