<?php
/** RC17: Moderation restore must not bypass the *native* publication gate.
 * Runtime-shaped isolated fixtures (not staging/live proof).
 */
require __DIR__ . '/bootstrap.php';
$GLOBALS['rc17']=array('video_status'=>'published','video_visibility'=>'public','owner_ok'=>true,'reel_visibility'=>'public','cover_id'=>12,'caption'=>'Human reviewed caption','require_caption'=>false,'gate'=>true,'reel_status'=>'removed','report_status'=>'submitted','version'=>1,'write_count'=>0);
function apply_filters($name,$default,...$args) {
    return 'rsv_caption_text_required'===$name ? $GLOBALS['rc17']['require_caption'] : $default;
}
final class RSV_Contracts {
    const CAP_MODERATE='rsv_moderate_reels';
    const MODERATION_DECISIONS=array('restrict','remove','restore','close','no_action');
}
final class RSV_Security { public static function can($capability,$object=null,$purpose=''){return true;} }
final class RSV_Helpers {
    public static function error($code,$message,$status=400){return new WP_Error($code,$message,array('status'=>$status));}
    public static function enum($value,$options,$default=''){return in_array($value,$options,true)?$value:$default;}
    public static function textarea($value,$max){return substr((string)$value,0,$max);}
    public static function now(){return '2026-10-21 00:00:00';}
    public static function table($name){return 'wp_rsv_'.$name;}
    public static function outbox(...$args){return true;}
    public static function audit(...$args){return true;}
}
final class RSV_DB { public static function transaction($callback){return $callback();} }
final class RSV_Repository {
    public static function report($id,$public=false,$lock=false) {
        return array('id'=>9,'reel_id'=>3,'reason_code'=>'unsafe','status'=>$GLOBALS['rc17']['report_status'],'version'=>$GLOBALS['rc17']['version']);
    }
    public static function find($id) {
        return array('id'=>3,'public_id'=>'reel_test','video_id'=>5,'owner_id'=>7,'status'=>$GLOBALS['rc17']['reel_status'],'version'=>4,'visibility'=>$GLOBALS['rc17']['reel_visibility'],'cover_id'=>$GLOBALS['rc17']['cover_id'],'caption'=>$GLOBALS['rc17']['caption']);
    }
    public static function update_versioned($id,$expected,$changes){$GLOBALS['rc17']['write_count']++;return array_merge(self::find($id),$changes);}
}
final class RSV_File10 {
    public static function validate_for_reel($id,$owner) {
        if(!$GLOBALS['rc17']['owner_ok'])return new WP_Error('rsv_video_forbidden','File10 owner mismatch');
        return array('id'=>5,'owner_id'=>7,'status'=>$GLOBALS['rc17']['video_status'],'visibility'=>$GLOBALS['rc17']['video_visibility']);
    }
}
final class RSV_Top20 {
    public static function publication_gate($reel){return $GLOBALS['rc17']['gate'];}
}
final class RSV_State_Machine {
    public static function assert_report($from,$to){return in_array($from,array('submitted','triaged','appealed'),true)?true:new WP_Error('rsv_invalid_report_transition','Invalid transition');}
    public static function assert($from,$to){return in_array($from,array('removed','restricted'),true)&&'published'===$to?true:new WP_Error('rsv_invalid_transition','Invalid transition');}
}
final class RC17_DB {
    public function update($table,$data,$where,$data_formats=null,$where_formats=null){$GLOBALS['rc17']['write_count']++;return 1;}
}
$GLOBALS['wpdb']=new RC17_DB();
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-reels.php';
$fail=array();$count=0;
$reset=static function(){
  $GLOBALS['rc17']=array('video_status'=>'published','video_visibility'=>'public','owner_ok'=>true,'reel_visibility'=>'public','cover_id'=>12,'caption'=>'Human reviewed caption','require_caption'=>false,'gate'=>true,'reel_status'=>'removed','report_status'=>'submitted','version'=>1,'write_count'=>0);
};
$case=static function($label,$changes,$error)use(&$fail,&$count,$reset){
 $reset();foreach($changes as $key=>$value)$GLOBALS['rc17'][$key]=$value;
 ++$count;
 $got=RSV_Reels::moderate(9,'restore','Evidence-based restoration reviewed',1,false);
 if($error) {
   if(!is_wp_error($got)||$got->get_error_code()!==$error)$fail[]="$label expected $error; got ".(is_wp_error($got)?$got->get_error_code():gettype($got));
   if($GLOBALS['rc17']['write_count']!==0)$fail[]="$label mutated database on rejection";
 }elseif(is_wp_error($got)||$GLOBALS['rc17']['write_count']!==2){
   $fail[]="$label expected restored transaction, got ".(is_wp_error($got)?$got->get_error_code():json_encode($got))." writes=".$GLOBALS['rc17']['write_count'];
 }
};
$case('01 approved public restoration',array(),null);
$case('02 unavailable File10 media',array('video_status'=>'processing'),'rsv_restore_media_invalid');
$case('03 foreign File10 media ownership',array('owner_ok'=>false),'rsv_video_forbidden');
$case('04 public Reel cannot revive unlisted File10 video',array('video_visibility'=>'unlisted'),'rsv_public_media_visibility_mismatch');
$case('05 public Reel cannot revive private File10 video',array('video_visibility'=>'private'),'rsv_public_media_visibility_mismatch');
$case('06 missing source and safety review',array('gate'=>new WP_Error('rsv_source_safety_required','Review required')),'rsv_source_safety_required');
$case('07 malformed boolean safety denial',array('gate'=>false),'rsv_publication_safety_unavailable');
$case('08 null safety response fails closed',array('gate'=>null),'rsv_publication_safety_unavailable');
$case('09 missing cover cannot restore',array('cover_id'=>0),'rsv_cover_required');
$case('10 caption policy blocks missing caption',array('caption'=>'','require_caption'=>true),'rsv_caption_required');
$case('11 human-reviewed caption satisfies requested caption policy',array('require_caption'=>true),null);
$case('12 unlisted Reel may restore with unlisted video',array('reel_visibility'=>'unlisted','video_visibility'=>'unlisted'),null);
$case('13 private Reel may restore without public index',array('reel_visibility'=>'member','video_visibility'=>'private'),null);
$case('14 already published Reel cannot be restored',array('reel_status'=>'published'),'rsv_restore_state_invalid');
$case('15 draft Reel cannot be restored',array('reel_status'=>'draft'),'rsv_restore_state_invalid');
$case('16 closed report not eligible for another moderation decision',array('report_status'=>'closed'),'rsv_report_closed');
$case('17 report version conflict before restore',array('version'=>2),'rsv_report_conflict');
if($count!==17)$fail[]='Wrong assertion count';
if($fail){fwrite(STDERR,"RC17 restore parity failures:\n".implode("\n",$fail)."\n");exit(1);}
echo "RC17 moderation restore 17/17 permission, File10, source, caption and state behavioral scenarios PASS\n";
