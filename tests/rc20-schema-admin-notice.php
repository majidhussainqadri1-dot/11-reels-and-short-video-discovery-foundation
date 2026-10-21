<?php
/** Test schema migration failure is actionable: admin diagnostics appears even
 * when service registration stops early. No active WordPress site is used. */
require __DIR__.'/bootstrap.php';
$GLOBALS['rc20_hooks']=array();$GLOBALS['rc20_options']=array();$GLOBALS['rc20_transients']=array();
$GLOBALS['rc20_fail']='';$GLOBALS['rc20_admin']=true;$GLOBALS['rc20_queries']=0;
if(!defined('RSV_SCHEMA_VERSION'))define('RSV_SCHEMA_VERSION','1.2.0');
if(!defined('RSV_VERSION'))define('RSV_VERSION','1.2.0-rc20');
final class RSV_DB { public static function install(){return $GLOBALS['rc20_fail']==='core'?new WP_Error('rsv_schema_incomplete','Schema unavailable'):true;} }
final class RSV_Top20 {
  const SCHEMA_VERSION='1.1.0';
  public static function install(){return $GLOBALS['rc20_fail']==='top20'?new WP_Error('rsv_schema_incomplete','Schema unavailable'):true;}
  public function register(){}
}
final class RSV_Future30 {
  const SCHEMA_VERSION='1.0.0';
  public static function install(){return $GLOBALS['rc20_fail']==='future30'?new WP_Error('rsv_schema_incomplete','Schema unavailable'):true;}
  public function register(){}
}
final class RSV_Current_Plan { const REVISION='2026-08-12'; public function register(){} }
final class RSV_Jobs { public function register(){} }
final class RSV_REST { public function register(){} }
final class RSV_Frontend { public function register(){} }
final class RSV_Admin { public function register(){} }
final class RSV_Privacy { public function register(){} }
final class RSV_Integrations { public function register(){} }
final class RSV_Diagnostics { public static function summary(){++$GLOBALS['rc20_queries'];return array();} }
function get_option($name,$fallback=''){return $GLOBALS['rc20_options'][$name]??$fallback;}
function set_transient($name,$value,$expire=0){$GLOBALS['rc20_transients'][$name]=$value;}
function get_transient($name){return $GLOBALS['rc20_transients'][$name]??false;}
function delete_transient($name){unset($GLOBALS['rc20_transients'][$name]);}
function current_user_can($permission){return $GLOBALS['rc20_admin'];}
function add_action($name,$callback,$priority=10,$args=1){$GLOBALS['rc20_hooks'][]=$name;}
function add_filter($name,$callback,$priority=10,$args=1){$GLOBALS['rc20_hooks'][]=$name;}
function esc_html($v){return htmlspecialchars((string)$v, ENT_QUOTES);}
function esc_html__($v,$domain=''){return $v;}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-plugin.php';
$fail=array();$n=0;
foreach(array('core','top20','future30') as $target){
  $GLOBALS['rc20_options']=array(
    'rsv_schema_version'=>$target==='core'?'old':RSV_SCHEMA_VERSION,
    'rsv_top20_schema_version'=>in_array($target,array('core','top20'),true)?'old':RSV_Top20::SCHEMA_VERSION,
    'rsv_future30_schema_version'=>'old'
  );
  $GLOBALS['rc20_hooks']=array();$GLOBALS['rc20_transients']=array();$GLOBALS['rc20_fail']=$target;
  $plugin=new RSV_Plugin();$plugin->register();++$n;
  if(!in_array('admin_notices',$GLOBALS['rc20_hooks'],true))$fail[]="$target has no admin notice hook";
  if(in_array('rest_api_init',$GLOBALS['rc20_hooks'],true))$fail[]="$target began REST operations with broken schema";
  if(get_transient('rsv_schema_repair_error')!=='Schema unavailable')$fail[]="$target dropped original error";
  $GLOBALS['rc20_queries']=0;ob_start();$plugin->notices();$out=ob_get_clean();++$n;
  if(!str_contains($out,'Schema unavailable') || $GLOBALS['rc20_queries']!==0)$fail[]="$target failed safe visible diagnosis";
  $GLOBALS['rc20_admin']=false;ob_start();$plugin->notices();$out=ob_get_clean();++$n;
  if(''!==$out)$fail[]="$target leaked admin diagnostic to non-admin";
  $GLOBALS['rc20_admin']=true;
}
if($n!==9)$fail[]="expected 9 checks, got $n";
if($fail){fwrite(STDERR,"RC20 early return admin evidence FAILED:\n".implode("\n",$fail)."\n");exit(1);}
echo "RC20 9/9 schema failed-start/visible admin/no unauthorised REST scenarios PASS\n";
