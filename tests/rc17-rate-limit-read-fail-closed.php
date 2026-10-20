<?php
/**
 * File 11 RC17: isolated fail-closed SQL counter regression.
 * No WordPress database, user state or production writes are made.
 */
require __DIR__ . '/bootstrap.php';
if (!defined('HOUR_IN_SECONDS')) define('HOUR_IN_SECONDS',3600);
final class RSV_Helpers {
    public static function text($value,$max){return substr((string)$value,0,$max);}
    public static function table($name){return 'wp_rsv_'.$name;}
    public static function now(){return '2026-10-21 00:00:00';}
    public static function error($code,$message,$status=400,$extra=array()) {
        return new WP_Error($code,$message,array_merge(array('status'=>$status),$extra));
    }
}
final class RC17_Rate_DB {
    public $count='1';
    public $write=1;
    public $read_count=0;
    public function prepare($query,...$args){return $query;}
    public function query($sql){return $this->write;}
    public function get_var($sql){$this->read_count++;return $this->count;}
}
$GLOBALS['wpdb']=new RC17_Rate_DB();
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-security.php';
$fail=array();$checks=0;
$case=static function($label,$count,$write,$expect)use(&$fail,&$checks){
    $db=$GLOBALS['wpdb'];$db->count=$count;$db->write=$write;$db->read_count=0;
    ++$checks;$result=RSV_Security::rate_limit('view_session',3,3600);
    $actual=is_wp_error($result)?$result->get_error_code():'allowed';
    if($actual!==$expect){$fail[]="$label: expected $expect, got $actual";}
    if($write===false&&$db->read_count!==0){$fail[]="$label: DB read attempted after insert failure";}
};
$case('01 first accepted',1,1,'allowed');
$case('02 threshold accepted','3',2,'allowed');
$case('03 above limit denied','4',2,'rsv_rate_limited');
$case('04 counter NULL fail closed',null,1,'rsv_rate_limit_unavailable');
$case('05 counter FALSE fail closed',false,1,'rsv_rate_limit_unavailable');
$case('06 counter zero fail closed','0',1,'rsv_rate_limit_unavailable');
$case('07 non-integer counter fail closed','not-a-count',1,'rsv_rate_limit_unavailable');
$case('08 database increment failure fail closed',1,false,'rsv_rate_limit_unavailable');
$case('09 oversized count safely denied','9999999999',2,'rsv_rate_limited');
printf("File 11 fresh SQL rate limit: %d/%d passed\n",$checks-count($fail),$checks);
foreach($fail as $e){fwrite(STDERR,'FAIL '.$e."\n");}
exit($fail===array()?0:1);
