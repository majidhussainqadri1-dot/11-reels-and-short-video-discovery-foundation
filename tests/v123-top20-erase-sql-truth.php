<?php
/** Top20 privacy erasure SQL integrity; isolated fake DB, no real data erased. */
require __DIR__.'/bootstrap.php';
if(!defined('ARRAY_A'))define('ARRAY_A','ARRAY_A');
function get_user_by($by,$email){return $email==='found@example.test'?(object)array('ID'=>7):false;}
final class RSV_Helpers {
    public static function table($name){return 'wp_rsv_'.$name;}
    public static function error($code,$msg,$status=503){return new WP_Error($code,$msg,array('status'=>$status));}
    public static function now(){return '2026-11-15 09:00:00';}
    public static function audit(...$args){return true;}
}
final class RSV_DB {
    public static function transaction($callback){
        $GLOBALS['wpdb']->tx[]='BEGIN';
        $result=$callback();
        $GLOBALS['wpdb']->tx[]=is_wp_error($result)?'ROLLBACK':'COMMIT';
        return $result;
    }
}
final class V123_Top20_Erase_DB {
    public $last_error='';
    public $queue=array();
    public $count='0';
    public $countError='';
    public $updates=0;
    public $deletes=0;
    public $updateResult=1;
    public $tx=array();
    public $readCount=0;
    public function prepare($q,...$args){return $q;}
    public function get_results($q,$format=ARRAY_A){
        ++$this->readCount;
        $s=array_shift($this->queue);
        if($s===null){$this->last_error='Unexpected extra read';return null;}
        $this->last_error=$s[1];
        return $s[0];
    }
    public function get_var($sql){++$this->readCount;$this->last_error=$this->countError;return $this->count;}
    public function delete(...$args){++$this->deletes;return 0;}
    public function update(...$args){++$this->updates;return $this->updateResult;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-top20-privacy-integration.php';
final class V123_Top20_Eraser {
    use RSV_Top20_Privacy_Integration_Trait;
}
$errors=[];$n=0;
$check=static function($ok,$label)use(&$errors,&$n){++$n;if(!$ok)$errors[]=$label;};
$start=static function($steps,$count='0',$countError=''){
    $db=new V123_Top20_Erase_DB();
    $db->queue=$steps;
    $db->count=$count;
    $db->countError=$countError;
    $GLOBALS['wpdb']=$db;
    return $db;
};
foreach(array(
 'null'=>array(null,''),
 'false'=>array(false,''),
 'sql-failure'=>array(array(),'SQL gone'),
 'malformed-row'=>array(array('invalid'),'')
) as $name=>$case){
    $db=$start(array($case));
    $r=V123_Top20_Eraser::privacy_erase('found@example.test');
    $check(is_array($r)&&($r['done']??true)===false&&
       ($r['items_removed']??true)===false&&($r['items_retained']??false)===true&&
       $db->deletes===0&&$db->updates===0&&
       in_array('ROLLBACK',$db->tx,true)&&!in_array('COMMIT',$db->tx,true),
       "first erasure SELECT $name must rollback and never claim finished");
    $db=$start(array(array(array(),'') ,$case));
    $r=V123_Top20_Eraser::privacy_erase('found@example.test');
    $check(is_array($r)&&($r['done']??true)===false&&
       $db->deletes===0&&$db->updates===0&&
       in_array('ROLLBACK',$db->tx,true),
       "second erasure SELECT $name must rollback before mutation");
}
foreach(array(
    'null'=>array(null,''),
    'false'=>array(false,''),
    'sql-error'=>array('0','SQL lost'),
    'nonnumeric'=>array('bad',''),
    'empty'=>array('','')
) as $name=>$countCase){
    $db=$start(array(array(array(),'') ,array(array(),'')),$countCase[0],$countCase[1]);
    $r=V123_Top20_Eraser::privacy_erase('found@example.test');
    $check(($r['done']??true)===false&&($r['items_retained']??false)===true&&
        in_array('ROLLBACK',$db->tx,true)&&!in_array('COMMIT',$db->tx,true),
        "retained count $name must not falsely certify privacy erasure");
}
$db=$start(array(array(array(),''),array(array(),'')),'0');
$r=V123_Top20_Eraser::privacy_erase('found@example.test');
$check(($r['done']??false)===true&&in_array('COMMIT',$db->tx,true)&&
    ($r['items_removed']??true)===false,'healthy empty erasure completes exactly');
$db=$start(array(array(array(array('id'=>101,'version'=>1)),''),array(array(),'')),'1');
$r=V123_Top20_Eraser::privacy_erase('found@example.test');
$check(($r['done']??false)===true&&($r['items_removed']??false)===true&&
    ($r['items_retained']??false)===true&&$db->updates===1&&in_array('COMMIT',$db->tx,true),
    'healthy existing draft minimized and retained published data acknowledged');
$db=$start(array(array(array(array('id'=>101,'version'=>1)),''),array(array(),'')),'0');
$db->updateResult=0;
$r=V123_Top20_Eraser::privacy_erase('found@example.test');
$check(($r['done']??true)===false&&in_array('ROLLBACK',$db->tx,true),
    'optimistic stale-row zero update rolls back as conflict');
$db=$start(array(array(array(),''),array(array(),'')));
$r=V123_Top20_Eraser::privacy_erase('unknown@example.test');
$check(($r['done']??false)===true&&$db->readCount===0&&$db->deletes===0,
    'unknown identity has no SQL or deletion');
if($errors){fwrite(STDERR,"V12.3 Top20 privacy erase SQL failures:\n".implode("\n",$errors)."\n");exit(1);}
echo "V12.3 Top20 privacy erase SQL truth $n/$n synthetic scenarios PASS\n";
