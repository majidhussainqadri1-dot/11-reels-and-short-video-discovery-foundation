<?php
/** Top20 public/private archive privacy export truth, isolated WordPress-shaped DB fixture. */
require __DIR__.'/bootstrap.php';
if(!defined('ARRAY_A'))define('ARRAY_A','ARRAY_A');
function apply_filters($name,$v,...$args){return $v;}
function get_user_by($field,$email){return $email==='found@example.test'?(object)array('ID'=>7):false;}
final class RSV_Security {
    public static function claims($id=0){return array('is_minor'=>false,'age_band'=>'adult');}
}
final class RSV_Helpers {
    public static function table($key){return 'wp_rsv_'.$key;}
    public static function error($code,$message,$status=503){return new WP_Error($code,$message,array('status'=>$status));}
}
final class V123_Top20_Export_DB {
    public $last_error='';
    public $preferences=null;
    public $preferencesError='';
    public $queue=array();
    public $reads=0;
    public $writes=0;
    public function prepare($sql,...$args){return $sql;}
    public function get_row($sql,$output=ARRAY_A){++$this->reads;$this->last_error=$this->preferencesError;return $this->preferences;}
    public function get_results($sql,$output=ARRAY_A){
        ++$this->reads;
        $step=array_shift($this->queue);
        if($step===null){$this->last_error='unexpected extra query';return null;}
        $this->last_error=$step[1];
        return $step[0];
    }
    public function query(...$args){++$this->writes;return 1;}
}
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-top20-context.php';
require dirname(__DIR__).'/11-reels-foundation/includes/trait-rsv-top20-privacy-integration.php';
final class V123_Top20_Exporter {
    use RSV_Top20_Context_Trait;
    use RSV_Top20_Privacy_Integration_Trait;
}
$failed=[];$n=0;
$check=static function($ok,$label)use(&$failed,&$n){++$n;if(!$ok)$failed[]=$label;};
$stages=array(
    0=>'rsv_top20_stories_export_unavailable',
    1=>'rsv_top20_responses_export_unavailable',
    2=>'rsv_top20_highlights_export_unavailable',
    3=>'rsv_top20_context_export_unavailable'
);
foreach($stages as $stage=>$expected)foreach(array(
    'null'=>array(null,''),
    'false'=>array(false,''),
    'last-error'=>array(array(),'connection unavailable'),
    'bad-row'=>array(array('not-row'),'')
) as $name=>$case){
    $db=new V123_Top20_Export_DB();
    $db->queue=array_fill(0,$stage,array(array(),''));
    $db->queue[]=array($case[0],$case[1]);
    $GLOBALS['wpdb']=$db;
    $r=V123_Top20_Exporter::privacy_export('found@example.test',1);
    $check(is_wp_error($r)&&$r->get_error_code()===$expected&&
        ($r->get_error_data()['status']??0)===503&&$db->reads===$stage+2&&$db->writes===0,
        "export stage $stage/$name must stop on false SQL response with 503");
}
$db=new V123_Top20_Export_DB();
$db->queue=array_fill(0,4,array(array(),''));
$GLOBALS['wpdb']=$db;
$r=V123_Top20_Exporter::privacy_export('found@example.test',1);
$check(is_array($r)&&($r['done']??false)===true&&count($r['data']??array())===1&&$db->reads===5,
    'verified healthy empty export still exports only preferences and completes');
$db=new V123_Top20_Export_DB();
$db->queue=array_fill(0,4,array(array(),''));
$GLOBALS['wpdb']=$db;
$r=V123_Top20_Exporter::privacy_export('found@example.test',2);
$check(is_array($r)&&($r['done']??false)===true&&count($r['data']??array())===0&&$db->reads===4,
    'page 2 has no repeated private preference item');
$db=new V123_Top20_Export_DB();
$db->preferencesError='DB unable to load private settings';
$GLOBALS['wpdb']=$db;
$r=V123_Top20_Exporter::privacy_export('found@example.test',1);
$check(is_wp_error($r)&&$r->get_error_code()==='rsv_top20_preferences_export_unavailable'&&$db->reads===1,
    'failed private preferences cannot produce fake completed export');
$db=new V123_Top20_Export_DB();
$GLOBALS['wpdb']=$db;
$r=V123_Top20_Exporter::privacy_export('unknown@example.test',1);
$check(is_array($r)&&($r['done']??false)===true&&$db->reads===0,
    'unknown identity must not query private tables');
if($failed){fwrite(STDERR,"V12.3 Top20 export SQL integrity:\n".implode("\n",$failed)."\n");exit(1);}
echo "V12.3 Top20 privacy export SQL truth $n/$n synthetic scenarios PASS\n";
