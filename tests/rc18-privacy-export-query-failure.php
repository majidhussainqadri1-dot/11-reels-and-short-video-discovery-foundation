<?php
/** RC18: privacy exporter must never silently complete with incomplete SQL results.
 * Deterministic WordPress-shaped DB response fixtures; not live DB acceptance. */
require __DIR__ . '/bootstrap.php';
if ( ! defined('ARRAY_A') ) define('ARRAY_A','ARRAY_A');
function get_user_by($type,$email) { return $email === 'found@example.test' ? (object)array('ID'=>7) : false; }
function apply_filters($name,$value,...$args) { return $value; }
final class RC18_Privacy_DB {
    public $prefix='wp_';
    public $last_error='';
    public $steps=array();
    public $calls=0;
    public function prepare($sql,...$params){return $sql;}
    public function get_results($sql,$output){
        ++$this->calls;
        $step=array_shift($this->steps);
        if(!is_array($step)){$this->last_error='fixture exhausted';return null;}
        $this->last_error=$step['error']??'';
        return $step['result'];
    }
}
$GLOBALS['wpdb']=new RC18_Privacy_DB();
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-contracts.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-repository.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-privacy.php';
$e=new RSV_Privacy();
$errors=array();$count=0;
$fail=static function($stage,$position,$result,$message)use(&$errors,&$count,$e){
    $GLOBALS['wpdb']->calls=0;$GLOBALS['wpdb']->steps=array();
    for($i=0;$i<$position-1;$i++)$GLOBALS['wpdb']->steps[]=array('result'=>array(),'error'=>'');
    $GLOBALS['wpdb']->steps[]=array('result'=>$result,'error'=>$message);
    ++$count;
    $response=$e->export('found@example.test',1);
    $expected=array(1=>'rsv_privacy_history_export_unavailable',2=>'rsv_privacy_reports_export_unavailable',3=>'rsv_privacy_owned_export_unavailable')[$position];
    if(!is_wp_error($response)||$response->get_error_code()!==$expected||
      ($response->get_error_data()['status']??null)!==503) {
       $errors[]="$stage: expected $expected 503, got ".(is_wp_error($response)?$response->get_error_code():gettype($response));
    }
    if($GLOBALS['wpdb']->calls!==$position)$errors[]="$stage: unexpected later SQL query";
};
foreach(array(1=>'history',2=>'reports',3=>'authored-reels') as $pos=>$name) {
    $fail("$name SQL null",$pos,null,'');
    $fail("$name SQL false",$pos,false,'');
    $fail("$name SQL error-string",$pos,array(),'DB unavailable');
}
$GLOBALS['wpdb']->calls=0;
$GLOBALS['wpdb']->steps=array_fill(0,3,array('result'=>array(),'error'=>''));
++$count;$healthy=$e->export('found@example.test',1);
if(!is_array($healthy)||!($healthy['done']??false)||($healthy['data']??null)!==array()||$GLOBALS['wpdb']->calls!==3)$errors[]='Healthy empty export did not complete safely';
$GLOBALS['wpdb']->calls=0;$GLOBALS['wpdb']->steps=array();
++$count;$unknown=$e->export('unknown@example.test',1);
if(!is_array($unknown)||!($unknown['done']??false)||$GLOBALS['wpdb']->calls!==0)$errors[]='Unknown email attempted SQL queries';
$GLOBALS['wpdb']->calls=0;$GLOBALS['wpdb']->steps=array_fill(0,3,array('result'=>array(),'error'=>''));
++$count;$page=$e->export('found@example.test',2);
if(!is_array($page)||!($page['done']??false))$errors[]='Empty second page not complete';
if($count!==12)$errors[]="wrong fixture count $count";
if($errors){fwrite(STDERR,"RC18 privacy export failures:\n".implode("\n",$errors)."\n");exit(1);}
echo "RC18 privacy export 12/12 SQL failure/nonleak and clean empty pagination scenarios PASS\n";
