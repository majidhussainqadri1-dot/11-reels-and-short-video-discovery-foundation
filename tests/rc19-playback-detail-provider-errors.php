<?php
/** RC19: File10 playback is authoritative; REST cannot return false-success DTOs.
 * 12 isolated object/privacy/media scenarios; not a production WordPress browser test. */
require __DIR__ . '/bootstrap.php';
$GLOBALS['rc19_row']=array('id'=>3,'public_id'=>'reel_11111111-2222-4333-8444-555555555555','video_id'=>5,'status'=>'published');
$GLOBALS['rc19_allow']=true;
$GLOBALS['rc19_playback']=array('url'=>'https://media.example/reel.webm');
$GLOBALS['rc19_calls']=0;
function rest_ensure_response($data){return $data;}
final class RSV_Helpers {
    public static function error($code,$message,$status=400){return new WP_Error($code,$message,array('status'=>$status));}
}
final class RSV_Repository {
    public static function find($id,$public=false){return $GLOBALS['rc19_row'];}
    public static function public_dto($reel){return array('id'=>$reel['public_id'],'status'=>$reel['status']);}
}
final class RSV_Security {
    public static function can_view_reel($reel){return is_array($reel) && $GLOBALS['rc19_allow'];}
}
final class RSV_File10 {
    public static function playback($video_id){++$GLOBALS['rc19_calls'];return $GLOBALS['rc19_playback'];}
}
class RC19_Request implements ArrayAccess {
    public function offsetExists($o): bool {return 'id'===$o;}
    public function offsetGet($o): mixed {return $o==='id'?'reel_11111111-2222-4333-8444-555555555555':null;}
    public function offsetSet($o,$v): void {}
    public function offsetUnset($o): void {}
}
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-rest.php';
$api=new RSV_REST();$request=new RC19_Request();$failures=array();$count=0;
$run=static function($label,$playback,$status,$expected_code='')use(&$count,&$failures,$api,$request){
    ++$count;$GLOBALS['rc19_row']=array('id'=>3,'public_id'=>'reel_11111111-2222-4333-8444-555555555555','video_id'=>5,'status'=>'published');
    $GLOBALS['rc19_allow']=true;
    $GLOBALS['rc19_playback']=$playback;
    $GLOBALS['rc19_calls']=0;
    $result=$api->get($request);
    if('success'===$status) {
        if(is_wp_error($result)||!is_array($result)||!array_key_exists('playback',$result)||$result['playback']!==$playback)
            $failures[]="$label: expected typed successful playback";
    } else {
        if(!is_wp_error($result)||$result->get_error_code()!==$expected_code||
            ($result->get_error_data()['status']??null)!==$status)
            $failures[]="$label: playback outage was hidden in a success DTO";
    }
    if($GLOBALS['rc19_calls']!==1)$failures[]="$label: provider count should be exactly one";
};
$run('01 valid playback',array('url'=>'https://media.example/video.webm'),'success');
$run('02 valid empty owner response',array(),'success');
$run('03 File10 playback outage',new WP_Error('rsv_file10_unavailable','Media down',array('status'=>503)),503,'rsv_file10_unavailable');
$run('04 File10 playback forbidden',new WP_Error('rsv_media_forbidden','Media refused',array('status'=>403)),403,'rsv_media_forbidden');
$run('05 File10 revoked rights',new WP_Error('rsv_media_revoked','Revoked',array('status'=>422)),422,'rsv_media_revoked');
$run('06 File10 missing media',new WP_Error('rsv_media_gone','Gone',array('status'=>404)),404,'rsv_media_gone');
$run('07 malformed File10 scalar',false,503,'rsv_playback_malformed');
$run('08 malformed File10 string','unexpected',503,'rsv_playback_malformed');
$run('09 malformed File10 null',null,503,'rsv_playback_malformed');
$run('10 malformed File10 integer',123,503,'rsv_playback_malformed');
++$count;$GLOBALS['rc19_allow']=false;$GLOBALS['rc19_calls']=0;$denied=$api->get($request);
if(!is_wp_error($denied)||$denied->get_error_code()!=='rsv_not_found'||$GLOBALS['rc19_calls']!==0)$failures[]='11 access denial leaked playback or existence';
++$count;$GLOBALS['rc19_allow']=true;$GLOBALS['rc19_row']=null;$GLOBALS['rc19_calls']=0;$missing=$api->get($request);
if(!is_wp_error($missing)||$missing->get_error_code()!=='rsv_not_found'||$GLOBALS['rc19_calls']!==0)$failures[]='12 missing Reel leaked playback';
if($count!==12)$failures[]='incorrect fixture count';
if($failures){fwrite(STDERR,"RC19 REST playback failures:\n".implode("\n",$failures)."\n");exit(1);}
echo "RC19 Reel detail File10 playback truth 12/12 provider refusal, malformed response and privacy tests PASS\n";
