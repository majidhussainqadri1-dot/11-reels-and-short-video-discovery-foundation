<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Top20_Stories_Trait {
	private static function consent_snapshot( $reel ) {
		$video = RSV_File10::video( absint( $reel['video_id'] ?? 0 ) );
		return hash( 'sha256', RSV_Helpers::json_encode( array( 'video'=>absint($reel['video_id']??0),'rights'=>(string)($video['rights_status']??''),'consent'=>(string)($video['consent_status']??''),'visibility'=>(string)($video['visibility']??''),'status'=>(string)($video['status']??'') ) ) );
	}

	private static function story_row( $id, $public = true ) {
		global $wpdb; $table=RSV_Helpers::table('stories');
		return $public ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE public_id=%s LIMIT 1",(string)$id),ARRAY_A) : $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d LIMIT 1",absint($id)),ARRAY_A);
	}

	private static function story_viewable( $story, $allow_highlight = false ) {
		if ( ! is_array($story) || ! in_array($story['status'],$allow_highlight?array('published','expired'):array('published'),true) ) return false;
		if ( ! $allow_highlight && strtotime($story['expires_at'].' UTC')<=time() ) return false;
		$reel=RSV_Repository::find(absint($story['reel_id']));
		return RSV_Security::can_view_reel($reel,0) && hash_equals((string)$story['consent_snapshot'],self::consent_snapshot($reel));
	}

	private static function story_dto( $story, $allow_highlight = false ) {
		if ( ! self::story_viewable($story,$allow_highlight) ) return null;
		$reel=RSV_Repository::find(absint($story['reel_id']));
		return array('id'=>(string)$story['public_id'],'type'=>(string)$story['story_type'],'title'=>(string)$story['title'],'body'=>(string)$story['body'],'destination_url'=>(string)$story['destination_url'],'expires_at'=>(string)$story['expires_at'],'published_at'=>(string)$story['published_at'],'url'=>home_url('/story/'.rawurlencode($story['public_id']).'/'),'reel'=>RSV_Repository::public_dto($reel));
	}

	public static function create_story( $data, $idempotency_key = '' ) {
		if ( ! RSV_Security::can(RSV_Contracts::CAP_SUBMIT,null,'create_story') ) return RSV_Helpers::error('rsv_forbidden',__('You cannot create Stories.',RSV_TEXT_DOMAIN),403);
		$rate=RSV_Security::rate_limit('create_story',30,HOUR_IN_SECONDS); if(is_wp_error($rate))return $rate;
		$data=is_array($data)?$data:array();
		$reel=RSV_Repository::find(RSV_Helpers::text($data['reel_id']??'',80),true);
		if(!$reel||!RSV_Security::can(RSV_Contracts::CAP_SUBMIT,$reel,'create_story'))return RSV_Helpers::error('rsv_reel_not_found',__('The source Reel is unavailable.',RSV_TEXT_DOMAIN),404);
		$type=RSV_Helpers::enum($data['story_type']??'status',RSV_Contracts::STORY_TYPES,'');
		$title=RSV_Helpers::text($data['title']??$reel['title'],255); $body=RSV_Helpers::textarea($data['body']??'',2000);
		$hours=min(self::MAX_STORY_HOURS,max(1,absint($data['expires_in_hours']??self::MAX_STORY_HOURS)));
		if(!$type||!$title)return RSV_Helpers::error('rsv_story_metadata_required',__('A valid Story type and title are required.',RSV_TEXT_DOMAIN),422);
		$payload=array('reel_id'=>$reel['public_id'],'story_type'=>$type,'title'=>$title,'body'=>$body,'hours'=>$hours); $idem_key=$idempotency_key?:wp_generate_uuid4();
		$idem=RSV_Security::idempotency_begin('create_story',$idem_key,$payload); if(is_wp_error($idem)||!empty($idem['replay']))return is_wp_error($idem)?$idem:$idem['response'];
		$result=RSV_DB::transaction(static function()use($data,$reel,$type,$title,$body,$hours,$idem_key){global $wpdb;$now=RSV_Helpers::now();$public_id=RSV_Helpers::public_id('story');
			$insert=$wpdb->insert(RSV_Helpers::table('stories'),array('public_id'=>$public_id,'reel_id'=>absint($reel['id']),'owner_id'=>get_current_user_id(),'story_type'=>$type,'title'=>$title,'body'=>$body,'destination_url'=>self::safe_destination_url($data['destination_url']??''),'status'=>'review','visibility'=>'public','duration_hours'=>$hours,'expires_at'=>gmdate('Y-m-d H:i:s',time()+$hours*HOUR_IN_SECONDS),'highlight_eligible'=>1,'consent_snapshot'=>self::consent_snapshot($reel),'version'=>1,'published_at'=>null,'created_at'=>$now,'updated_at'=>$now),array('%s','%d','%d','%s','%s','%s','%s','%s','%s','%d','%s','%d','%s','%d','%s','%s','%s'));
			if(1!==$insert)return RSV_Helpers::error('rsv_story_write_failed',__('The Story could not be created.',RSV_TEXT_DOMAIN),500);$id=absint($wpdb->insert_id);
			if(!RSV_Helpers::audit('story',$id,'create','','review',$type)||!RSV_Helpers::outbox('StoryCreated','story',$id,array('public_id'=>$public_id,'reel_id'=>$reel['public_id'])))return RSV_Helpers::error('rsv_story_evidence_failed',__('The Story could not be created with complete evidence.',RSV_TEXT_DOMAIN),500);
			$response=array('id'=>$public_id,'status'=>'review','duration_hours'=>$hours);if(!RSV_Security::idempotency_finish('create_story',$idem_key,$response))return RSV_Helpers::error('rsv_story_finalize_failed',__('The Story could not be finalized.',RSV_TEXT_DOMAIN),500);return $response;});
		if(is_wp_error($result))RSV_Security::idempotency_fail('create_story',$idem_key);return $result;
	}

	public static function publish_story( $public_id, $expected_version ) {
		$story=self::story_row($public_id,true);if(!$story||!RSV_Security::can(RSV_Contracts::CAP_PUBLISH,$story,'publish_story'))return RSV_Helpers::error('rsv_story_not_found',__('Story not found.',RSV_TEXT_DOMAIN),404);
		$reel=RSV_Repository::find(absint($story['reel_id']));if(!RSV_Security::can_view_reel($reel,0)||!hash_equals((string)$story['consent_snapshot'],self::consent_snapshot($reel)))return RSV_Helpers::error('rsv_story_consent_changed',__('Rights, consent or visibility changed. Review the Story again.',RSV_TEXT_DOMAIN),409);
		if(absint($story['version'])!==absint($expected_version))return RSV_Helpers::error('rsv_version_conflict',__('This Story changed. Refresh and try again.',RSV_TEXT_DOMAIN),409);
		global $wpdb;$published_at=RSV_Helpers::now();$expires_at=gmdate('Y-m-d H:i:s',time()+min(self::MAX_STORY_HOURS,max(1,absint($story['duration_hours']??self::MAX_STORY_HOURS)))*HOUR_IN_SECONDS);
		$ok=$wpdb->update(RSV_Helpers::table('stories'),array('status'=>'published','published_at'=>$published_at,'expires_at'=>$expires_at,'version'=>absint($story['version'])+1,'updated_at'=>$published_at),array('id'=>absint($story['id']),'version'=>absint($story['version'])),array('%s','%s','%s','%d','%s'),array('%d','%d'));
		if(1!==$ok)return RSV_Helpers::error('rsv_story_publish_failed',__('The Story could not be published.',RSV_TEXT_DOMAIN),500);RSV_Helpers::audit('story',absint($story['id']),'publish',$story['status'],'published');RSV_Helpers::outbox('StoryPublished','story',absint($story['id']),array('public_id'=>$story['public_id']));return self::story_dto(self::story_row($story['id'],false));
	}

	public static function public_stories( $limit = 20, $cursor = '' ) {
		self::expire_stories();global $wpdb;$limit=min(50,max(1,absint($limit)));$context='stories-'.(self::youth_mode()?'youth':'standard');$decoded=RSV_Helpers::cursor_decode($cursor,'latest',$context);if(is_wp_error($decoded))return $decoded;
		$table=RSV_Helpers::table('stories');$where="status='published' AND visibility='public' AND expires_at>%s";$args=array(RSV_Helpers::now());if($decoded){$where.=' AND (published_at<%s OR (published_at=%s AND id<%d))';$args[]=$decoded['primary'];$args[]=$decoded['primary'];$args[]=$decoded['id'];}$args[]=min(200,$limit*4);
		$rows=(array)$wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE $where ORDER BY published_at DESC,id DESC LIMIT %d",$args),ARRAY_A);$items=array();$last=null;foreach($rows as $row){$last=$row;$dto=self::story_dto($row);if($dto){$items[]=$dto;if(count($items)>=$limit)break;}}$next=null;if($last&&(count($rows)===min(200,$limit*4)||count($items)>=$limit))$next=RSV_Helpers::cursor_encode('latest',$last['published_at'],'',$last['id'],$context);return array('items'=>$items,'next_cursor'=>$next);
	}

	public static function expire_stories(){global $wpdb;$table=RSV_Helpers::table('stories');return false!==$wpdb->query($wpdb->prepare("UPDATE $table SET status='expired',version=version+1,updated_at=%s WHERE status='published' AND expires_at<=%s",RSV_Helpers::now(),RSV_Helpers::now()));}

	private static function highlight_row($id,$public=true){global $wpdb;$table=RSV_Helpers::table('highlights');return $public?$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE public_id=%s LIMIT 1",(string)$id),ARRAY_A):$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d LIMIT 1",absint($id)),ARRAY_A);}

	public static function add_highlight($story_public_id,$title,$highlight_public_id=''){
		$story=self::story_row($story_public_id,true);if(!$story||absint($story['owner_id'])!==get_current_user_id()||!RSV_Security::can(RSV_Contracts::CAP_SUBMIT,null,'highlight_story'))return RSV_Helpers::error('rsv_story_not_found',__('Story not found.',RSV_TEXT_DOMAIN),404);if(!self::story_viewable($story,true)||empty($story['highlight_eligible']))return RSV_Helpers::error('rsv_story_highlight_ineligible',__('This Story cannot be highlighted until rights and consent are rechecked.',RSV_TEXT_DOMAIN),409);
		global $wpdb;$highlight=$highlight_public_id?self::highlight_row($highlight_public_id,true):null;if($highlight&&absint($highlight['owner_id'])!==get_current_user_id())return RSV_Helpers::error('rsv_highlight_not_found',__('Highlight not found.',RSV_TEXT_DOMAIN),404);
		if(!$highlight){$title=RSV_Helpers::text($title?:__('Highlights',RSV_TEXT_DOMAIN),160);$public_id=RSV_Helpers::public_id('highlight');$insert=$wpdb->insert(RSV_Helpers::table('highlights'),array('public_id'=>$public_id,'owner_id'=>get_current_user_id(),'title'=>$title,'slug'=>sanitize_title($title.'-'.substr($public_id,-6)),'status'=>'published','version'=>1,'created_at'=>RSV_Helpers::now(),'updated_at'=>RSV_Helpers::now()),array('%s','%d','%s','%s','%s','%d','%s','%s'));if(1!==$insert)return RSV_Helpers::error('rsv_highlight_write_failed',__('The Highlight could not be created.',RSV_TEXT_DOMAIN),500);$highlight=self::highlight_row(absint($wpdb->insert_id),false);}
		$max=(int)$wpdb->get_var($wpdb->prepare('SELECT COALESCE(MAX(sort_order),0) FROM '.RSV_Helpers::table('highlight_items').' WHERE highlight_id=%d',absint($highlight['id'])));$sql=$wpdb->prepare('INSERT IGNORE INTO '.RSV_Helpers::table('highlight_items').' (highlight_id,story_id,sort_order,consent_rechecked_at,created_at) VALUES (%d,%d,%d,%s,%s)',absint($highlight['id']),absint($story['id']),$max+10,RSV_Helpers::now(),RSV_Helpers::now());if(false===$wpdb->query($sql))return RSV_Helpers::error('rsv_highlight_item_failed',__('The Story could not be added to the Highlight.',RSV_TEXT_DOMAIN),500);RSV_Helpers::audit('highlight',absint($highlight['id']),'add_story','','','',array('story_id'=>$story['public_id']));return self::highlight_dto($highlight);
	}

	private static function highlight_dto($highlight){global $wpdb;$items_table=RSV_Helpers::table('highlight_items');$stories_table=RSV_Helpers::table('stories');$rows=(array)$wpdb->get_results($wpdb->prepare("SELECT s.* FROM $items_table i JOIN $stories_table s ON s.id=i.story_id WHERE i.highlight_id=%d ORDER BY i.sort_order ASC,i.id ASC LIMIT 100",absint($highlight['id'])),ARRAY_A);$items=array();foreach($rows as $row){$dto=self::story_dto($row,true);if($dto)$items[]=$dto;}return array('id'=>$highlight['public_id'],'owner_id'=>absint($highlight['owner_id']),'title'=>$highlight['title'],'slug'=>$highlight['slug'],'items'=>$items,'url'=>home_url('/reels/highlights/'.absint($highlight['owner_id']).'/'));}
	public static function public_highlights($owner_id,$limit=20){global $wpdb;$rows=(array)$wpdb->get_results($wpdb->prepare("SELECT * FROM ".RSV_Helpers::table('highlights')." WHERE owner_id=%d AND status='published' ORDER BY updated_at DESC,id DESC LIMIT %d",absint($owner_id),min(50,max(1,absint($limit)))),ARRAY_A);$out=array();foreach($rows as $row){$dto=self::highlight_dto($row);if($dto['items'])$out[]=$dto;}return $out;}
}
