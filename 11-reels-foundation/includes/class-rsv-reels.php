<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Reels {
	public static function create( $data, $idempotency_key ) {
		if(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT,null,'create_reel'))return RSV_Helpers::error('rsv_forbidden',__('You cannot create Reels.',RSV_TEXT_DOMAIN),403);
		$rate=RSV_Security::rate_limit('create',20,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;
		$video_id=absint($data['video_id']??0);$video=RSV_File10::validate_for_reel($video_id,get_current_user_id());if(is_wp_error($video))return $video;
		$title=RSV_Helpers::text($data['title']??($video['title']??''),255);$topic=RSV_Helpers::enum($data['topic']??'',RSV_Contracts::TOPICS,'');
		$caption=RSV_Helpers::textarea($data['caption']??'',4000);$language=RSV_Helpers::bcp47($data['language']??'en-US');
		if(!$title||!$topic||!$caption)return RSV_Helpers::error('rsv_metadata_required',__('Title, caption and an approved educational topic are required.',RSV_TEXT_DOMAIN),422);
		$cover_id=absint($data['cover_id']??($video['thumbnail_id']??0));if(!$cover_id||!wp_attachment_is_image($cover_id))return RSV_Helpers::error('rsv_cover_required',__('A valid cover image is required.',RSV_TEXT_DOMAIN),422);
		$labels=array_values(array_intersect(array_unique(array_map('sanitize_key',(array)($data['safety_labels']??array()))),RSV_Contracts::SAFETY_LABELS));
		$idem=RSV_Security::idempotency_begin('create_reel',$idempotency_key,$data);if(is_wp_error($idem)||!empty($idem['replay']))return is_wp_error($idem)?$idem:$idem['response'];
		$share_token='';$visibility=RSV_Helpers::enum($data['visibility']??'public',RSV_Contracts::VISIBILITIES,'public');if('unlisted'===$visibility)$share_token=RSV_Access::share_token();
		$status='published'===($video['status']??'')?'review':'media_processing';
		global $wpdb;$now=RSV_Helpers::now();$public=RSV_Helpers::public_id('reel');$slug=sanitize_title($data['slug']??$title.'-'.substr($public,-6));
		$inserted=$wpdb->insert(RSV_Helpers::table('reels'),array(
			'public_id'=>$public,'video_id'=>$video_id,'owner_id'=>get_current_user_id(),'title'=>$title,'slug'=>$slug,'topic'=>$topic,'language'=>$language,
			'caption'=>$caption,'disclosure'=>RSV_Helpers::text($data['disclosure']??'',500),'cover_id'=>$cover_id,'visibility'=>$visibility,
			'share_token_hash'=>$share_token?hash_hmac('sha256',$share_token,wp_salt('auth')):'','status'=>$status,'rights_status'=>RSV_Helpers::enum($video['rights_status']??'declared',array('declared','verified','disputed','restricted'),'declared'),
			'consent_status'=>RSV_Helpers::enum($video['consent_status']??'not_patient_case',array('not_patient_case','documented','anonymized','approved','missing'),'missing'),
			'captions_status'=>RSV_File10::captions_ready($video_id)?'ready':'missing','safety_labels_json'=>RSV_Helpers::json_encode($labels),'review_note'=>'','restricted_reason'=>'','rank_score'=>0,
			'version'=>1,'published_by'=>0,'created_at'=>$now,'updated_at'=>$now,
		));
		if(!$inserted){RSV_Security::idempotency_fail('create_reel',$idempotency_key,array('code'=>'database_error'));return RSV_Helpers::error('rsv_database_error',__('The Reel could not be created.',RSV_TEXT_DOMAIN),500);}
		$id=(int)$wpdb->insert_id;RSV_Helpers::audit('reel',$id,'create','',$status,'',array('video_public_id'=>RSV_File10::public_id($video_id)));RSV_Helpers::outbox('ReelCreated','reel',$id,array('public_id'=>$public,'video_public_id'=>RSV_File10::public_id($video_id)));
		$response=RSV_Repository::public_dto(RSV_Repository::find($id),true);if($share_token){$response['unlisted_access_token']=$share_token;$response['url']=add_query_arg('rsv_access',$share_token,$response['url']);}
		RSV_Security::idempotency_finish('create_reel',$idempotency_key,$response);return $response;
	}

	public static function update( $id,$version,$data ) {
		$reel=RSV_Repository::find($id);if(!$reel)return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);
		if(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT,$reel,'update_reel'))return RSV_Helpers::error('rsv_forbidden',__('You cannot edit this Reel.',RSV_TEXT_DOMAIN),403);
		if(!in_array($reel['status'],array('draft','media_processing','review','restricted'),true))return RSV_Helpers::error('rsv_edit_state',__('This Reel cannot be edited in its current state.',RSV_TEXT_DOMAIN),409);
		$changes=array();
		if(isset($data['title'])){$v=RSV_Helpers::text($data['title'],255);if(!$v)return RSV_Helpers::error('rsv_title_required',__('Title is required.',RSV_TEXT_DOMAIN),422);$changes['title']=$v;}
		if(isset($data['caption'])){$v=RSV_Helpers::textarea($data['caption'],4000);if(!$v)return RSV_Helpers::error('rsv_caption_required',__('Caption is required.',RSV_TEXT_DOMAIN),422);$changes['caption']=$v;}
		if(isset($data['topic'])){$v=RSV_Helpers::enum($data['topic'],RSV_Contracts::TOPICS,'');if(!$v)return RSV_Helpers::error('rsv_topic_invalid',__('Choose an approved educational topic.',RSV_TEXT_DOMAIN),422);$changes['topic']=$v;}
		if(isset($data['language']))$changes['language']=RSV_Helpers::bcp47($data['language']);
		if(isset($data['disclosure']))$changes['disclosure']=RSV_Helpers::text($data['disclosure'],500);
		if(isset($data['cover_id'])){$cover=absint($data['cover_id']);if(!$cover||!wp_attachment_is_image($cover))return RSV_Helpers::error('rsv_cover_invalid',__('A valid cover image is required.',RSV_TEXT_DOMAIN),422);$changes['cover_id']=$cover;}
		if(isset($data['safety_labels']))$changes['safety_labels_json']=RSV_Helpers::json_encode(array_values(array_intersect(array_unique(array_map('sanitize_key',(array)$data['safety_labels'])),RSV_Contracts::SAFETY_LABELS)));
		$updated=RSV_Repository::update_versioned($id,$version,$changes);if(!is_wp_error($updated))RSV_Helpers::audit('reel',$id,'update',$reel['status'],$updated['status']);return $updated;
	}

	public static function submit( $id,$version ) {
		$reel=RSV_Repository::find($id);if(!$reel)return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);
		if(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT,$reel,'submit_reel'))return RSV_Helpers::error('rsv_forbidden',__('You cannot submit this Reel.',RSV_TEXT_DOMAIN),403);
		$video=RSV_File10::validate_for_reel((int)$reel['video_id'],(int)$reel['owner_id']);if(is_wp_error($video))return $video;
		$to='published'===($video['status']??'')?'review':'media_processing';if($to===$reel['status'])return $reel;
		$gate=RSV_State_Machine::assert($reel['status'],$to);if(is_wp_error($gate))return $gate;
		$updated=RSV_Repository::update_versioned($id,$version,array('status'=>$to,'captions_status'=>RSV_File10::captions_ready($reel['video_id'])?'ready':'missing'));
		if(!is_wp_error($updated)){RSV_Helpers::audit('reel',$id,'submit',$reel['status'],$to);RSV_Helpers::outbox('ReelSubmitted','reel',$id,array('public_id'=>$reel['public_id'],'state'=>$to));}return $updated;
	}

	private static function publication_gate( $reel ) {
		$video=RSV_File10::validate_for_reel((int)$reel['video_id'],(int)$reel['owner_id']);if(is_wp_error($video))return $video;
		$gate=RSV_File10::publication_gate($video);if(is_wp_error($gate))return $gate;
		if(!trim($reel['title'])||!trim($reel['caption'])||!in_array($reel['topic'],RSV_Contracts::TOPICS,true))return RSV_Helpers::error('rsv_metadata_gate',__('Required Reel metadata is incomplete.',RSV_TEXT_DOMAIN),422);
		if(!$reel['cover_id']||!wp_attachment_is_image((int)$reel['cover_id']))return RSV_Helpers::error('rsv_cover_gate',__('A valid Reel cover is required.',RSV_TEXT_DOMAIN),422);
		if(!RSV_Helpers::bcp47($reel['language'],''))return RSV_Helpers::error('rsv_language_gate',__('The language code is invalid.',RSV_TEXT_DOMAIN),422);
		if('entitled'===$reel['visibility']&&!has_filter('rsv_entitlement_check'))return RSV_Helpers::error('rsv_entitlement_gate',__('Entitled visibility requires an active entitlement provider.',RSV_TEXT_DOMAIN),503);
		if(!RSV_Security::can(RSV_Contracts::CAP_PUBLISH,$reel,'publish_reel'))return RSV_Helpers::error('rsv_publish_forbidden',__('Current publishing authorization is required.',RSV_TEXT_DOMAIN),403);
		return $video;
	}

	public static function publish( $id,$version,$review_note='' ) {
		$reel=RSV_Repository::find($id);if(!$reel)return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);
		$video=self::publication_gate($reel);if(is_wp_error($video))return $video;
		$gate=RSV_State_Machine::assert($reel['status'],'published');if(is_wp_error($gate))return $gate;
		$reel['captions_status']='ready';$score=RSV_Ranking::score($reel,$video,array());
		$updated=RSV_Repository::update_versioned($id,$version,array('status'=>'published','captions_status'=>'ready','review_note'=>RSV_Helpers::textarea($review_note,2000),'rank_score'=>$score,'restricted_reason'=>'','published_by'=>get_current_user_id(),'published_at'=>RSV_Helpers::now()));
		if(!is_wp_error($updated)){RSV_Helpers::audit('reel',$id,'publish',$reel['status'],'published',$review_note);RSV_Helpers::outbox('ReelPublished','reel',$id,array('public_id'=>$reel['public_id'],'topic'=>$reel['topic']));}return $updated;
	}

	public static function progress( $id,$seconds,$completed=false,$rapid=false,$session_id='' ) {
		if(!is_user_logged_in())return RSV_Helpers::error('rsv_login_required',__('Sign in to save progress.',RSV_TEXT_DOMAIN),401);
		$reel=RSV_Repository::find($id);if(!$reel||!RSV_Access::can_view($reel,'progress'))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);
		$rate=RSV_Security::rate_limit('progress_'.$id,120,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;
		$video=RSV_File10::video($reel['video_id']);$duration=(int)($video['duration_seconds']??0);$seconds=max(0,min(absint($seconds),$duration));$is_complete=$duration>0&&$seconds>=(int)floor($duration*.9);
		$session_hash=$session_id?hash_hmac('sha256',RSV_Helpers::text($session_id,100),wp_salt('auth')):'';$bucket=$duration?min(10,(int)floor(($seconds/$duration)*10)):0;
		$result=RSV_DB::transaction(function()use($reel,$seconds,$is_complete,$rapid,$session_hash,$bucket){global $wpdb;$table=RSV_Helpers::table('progress');$user=get_current_user_id();$old=RSV_Repository::progress_row($user,$reel['id'],true);$now=RSV_Helpers::now();$emit=false;
			if($old){$replays=(int)$old['replays'];if($is_complete&&!empty($old['completed'])&&$session_hash&&$session_hash!==$old['last_session_hash'])$replays++;$emit=$bucket>(int)$old['event_bucket']||($is_complete&&!$old['completed']);$updated=$wpdb->update($table,array('position_seconds'=>$seconds,'completed'=>$is_complete?1:0,'replays'=>$replays,'last_session_hash'=>$session_hash,'event_bucket'=>max($bucket,(int)$old['event_bucket']),'version'=>(int)$old['version']+1,'updated_at'=>$now),array('id'=>(int)$old['id'],'version'=>(int)$old['version']));if(1!==$updated)return RSV_Helpers::error('rsv_progress_conflict',__('Progress changed concurrently. Retry.',RSV_TEXT_DOMAIN),409);}
			else{$inserted=$wpdb->insert($table,array('user_id'=>$user,'reel_id'=>$reel['id'],'position_seconds'=>$seconds,'completed'=>$is_complete?1:0,'replays'=>0,'last_session_hash'=>$session_hash,'event_bucket'=>$bucket,'version'=>1,'created_at'=>$now,'updated_at'=>$now));if(!$inserted)return RSV_Helpers::error('rsv_progress_error',__('Progress could not be saved.',RSV_TEXT_DOMAIN),500);$emit=true;}
			self::record_impression($reel['id'],$seconds,$is_complete,$rapid);
			if($emit)RSV_Helpers::outbox($is_complete?'ReelCompleted':'ReelViewed','reel',$reel['id'],array('public_id'=>$reel['public_id'],'progress_bucket'=>$bucket));
			return array('saved'=>true,'position_seconds'=>$seconds,'completed'=>$is_complete);
		});
		if(!is_wp_error($result))RSV_File10::progress($reel['video_id'],$seconds,$duration);return $result;
	}

	public static function impression( $id,$seconds=0,$completed=false,$rapid=false ) {
		$reel=RSV_Repository::find($id);if(!$reel||!RSV_Access::public_feed_eligible($reel))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);
		$rate=RSV_Security::rate_limit('impression_'.$id,80,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;
		self::record_impression($id,absint($seconds),RSV_Helpers::bool($completed),RSV_Helpers::bool($rapid));return array('recorded'=>true);
	}
	private static function record_impression($reel_id,$seconds,$completed,$rapid){global $wpdb;$table=RSV_Helpers::table('impressions');$now=RSV_Helpers::now();$hash=RSV_Helpers::daily_viewer_hash();$day=gmdate('Y-m-d');$sql=$wpdb->prepare("INSERT INTO $table (reel_id,viewer_hash,viewed_seconds,completed,swiped_rapidly,day_key,created_at,updated_at) VALUES (%d,%s,%d,%d,%d,%s,%s,%s) ON DUPLICATE KEY UPDATE viewed_seconds=GREATEST(viewed_seconds,VALUES(viewed_seconds)),completed=GREATEST(completed,VALUES(completed)),swiped_rapidly=GREATEST(swiped_rapidly,VALUES(swiped_rapidly)),updated_at=VALUES(updated_at)",$reel_id,$hash,absint($seconds),$completed?1:0,$rapid?1:0,$day,$now,$now);$wpdb->query($sql);}

	public static function report( $id,$reason,$details,$key ) {
		if(!is_user_logged_in())return RSV_Helpers::error('rsv_login_required',__('Sign in to submit a report.',RSV_TEXT_DOMAIN),401);
		$reel=RSV_Repository::find($id);if(!$reel||!RSV_Access::can_view($reel,'report'))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);
		$reason=RSV_Helpers::enum($reason,RSV_Contracts::REPORT_REASONS,'');if(!$reason)return RSV_Helpers::error('rsv_report_reason',__('Choose a valid report reason.',RSV_TEXT_DOMAIN),422);
		$rate=RSV_Security::rate_limit('report',10,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;$payload=array('reel'=>$reel['public_id'],'reason'=>$reason,'details'=>$details);
		$idem=RSV_Security::idempotency_begin('report_reel',$key,$payload);if(is_wp_error($idem)||!empty($idem['replay']))return is_wp_error($idem)?$idem:$idem['response'];
		global $wpdb;$now=RSV_Helpers::now();$public=RSV_Helpers::public_id('report');$inserted=$wpdb->insert(RSV_Helpers::table('reports'),array('public_id'=>$public,'reel_id'=>$id,'reporter_id'=>get_current_user_id(),'reason_code'=>$reason,'details'=>RSV_Helpers::textarea($details,4000),'status'=>'submitted','action_code'=>'','reviewer_id'=>0,'moderator_note'=>'','appeal_text'=>'','version'=>1,'created_at'=>$now,'updated_at'=>$now));
		if(!$inserted){RSV_Security::idempotency_fail('report_reel',$key);return RSV_Helpers::error('rsv_report_error',__('The report could not be submitted.',RSV_TEXT_DOMAIN),500);}$report_id=(int)$wpdb->insert_id;self::report_event($report_id,'submit','','submitted',$reason);RSV_Helpers::audit('report',$report_id,'submit','','submitted',$reason);RSV_Helpers::outbox('ReelReportSubmitted','report',$report_id,array('report_id'=>$public,'reel_id'=>$reel['public_id'],'reason'=>$reason));$response=array('id'=>$public,'status'=>'submitted','version'=>1);RSV_Security::idempotency_finish('report_reel',$key,$response);return $response;
	}

	public static function moderate( $report_id,$decision,$reason,$version ) {
		if(!RSV_Security::can(RSV_Contracts::CAP_MODERATE,null,'moderate_reel'))return RSV_Helpers::error('rsv_forbidden',__('You cannot moderate Reel reports.',RSV_TEXT_DOMAIN),403);
		$decision=RSV_Helpers::enum($decision,RSV_Contracts::MODERATION_DECISIONS,'');if(!$decision)return RSV_Helpers::error('rsv_decision_invalid',__('Choose a valid moderation decision.',RSV_TEXT_DOMAIN),422);
		$report=RSV_Repository::report_find($report_id);if(!$report)return RSV_Helpers::error('rsv_report_missing',__('Report not found.',RSV_TEXT_DOMAIN),404);$reel=RSV_Repository::find((int)$report['reel_id']);if(!$reel)return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);
		return RSV_DB::transaction(function()use($report,$reel,$decision,$reason,$version){$now=RSV_Helpers::now();$from=$report['status'];$to=$from;$changes=array('reviewer_id'=>get_current_user_id(),'moderator_note'=>RSV_Helpers::textarea($reason,2000));
			if('triage'===$decision){$to='triaged';$changes['triaged_at']=$now;}
			elseif('no_action'===$decision){$to='no_action';$changes['action_code']='no_action';$changes['decided_at']=$now;}
			elseif('close'===$decision){$to='closed';$changes['closed_at']=$now;}
			else{$to='action';$changes['action_code']=$decision;$changes['decided_at']=$now;}
			$gate=RSV_State_Machine::assert($from,$to,'report');if(is_wp_error($gate))return $gate;$changes['status']=$to;$updated=RSV_Repository::report_update_versioned($report['id'],$version,$changes);if(is_wp_error($updated))return $updated;
			if(in_array($decision,array('restrict','remove','restore'),true)){$target='restore'===$decision?'published':('remove'===$decision?'removed':'restricted');$gate=RSV_State_Machine::assert($reel['status'],$target);if(is_wp_error($gate))return $gate;$r=RSV_Repository::update_versioned($reel['id'],$reel['version'],array('status'=>$target,'restricted_reason'=>'restore'===$decision?'':RSV_Helpers::text($reason,120)));if(is_wp_error($r))return $r;RSV_Helpers::outbox('ReelRestricted','reel',$reel['id'],array('public_id'=>$reel['public_id'],'state'=>$target));}
			self::report_event($report['id'],$decision,$from,$to,$reason);RSV_Helpers::audit('report',$report['id'],$decision,$from,$to,$reason);return $updated;
		});
	}

	public static function appeal( $report_id,$text,$version ) {
		if(!is_user_logged_in())return RSV_Helpers::error('rsv_login_required',__('Sign in to appeal.',RSV_TEXT_DOMAIN),401);$report=RSV_Repository::report_find($report_id);if(!$report)return RSV_Helpers::error('rsv_report_missing',__('Report not found.',RSV_TEXT_DOMAIN),404);$reel=RSV_Repository::find((int)$report['reel_id']);$participant=(int)$report['reporter_id']===get_current_user_id()||($reel&&(int)$reel['owner_id']===get_current_user_id());if(!$participant&&!RSV_Security::can(RSV_Contracts::CAP_MODERATE,null,'appeal_report'))return RSV_Helpers::error('rsv_forbidden',__('You cannot appeal this decision.',RSV_TEXT_DOMAIN),403);$gate=RSV_State_Machine::assert($report['status'],'appealed','report');if(is_wp_error($gate))return $gate;$updated=RSV_Repository::report_update_versioned($report['id'],$version,array('status'=>'appealed','appeal_text'=>RSV_Helpers::textarea($text,4000),'appealed_at'=>RSV_Helpers::now()));if(!is_wp_error($updated)){self::report_event($report['id'],'appeal',$report['status'],'appealed',$text);RSV_Helpers::outbox('ReelReportAppealed','report',$report['id'],array('report_id'=>$report['public_id']));}return $updated;
	}
	private static function report_event($report_id,$event,$from,$to,$reason){global $wpdb;$wpdb->insert(RSV_Helpers::table('report_events'),array('report_id'=>$report_id,'actor_id'=>get_current_user_id(),'event_name'=>$event,'from_state'=>$from,'to_state'=>$to,'reason'=>RSV_Helpers::textarea($reason,2000),'created_at'=>RSV_Helpers::now()));}

	public static function insights($owner_id){global $wpdb;$reels=RSV_Helpers::table('reels');$imp=RSV_Helpers::table('impressions');$rows=$wpdb->get_results($wpdb->prepare("SELECT r.public_id,r.title,COUNT(i.id) views,SUM(i.completed) completions,AVG(i.viewed_seconds) avg_seconds FROM $reels r LEFT JOIN $imp i ON i.reel_id=r.id WHERE r.owner_id=%d GROUP BY r.id ORDER BY r.updated_at DESC LIMIT 100",$owner_id),ARRAY_A);return array_map(function($row){return array('id'=>$row['public_id'],'title'=>$row['title'],'views'=>(int)$row['views'],'completions'=>(int)$row['completions'],'average_seconds'=>round((float)$row['avg_seconds'],1));},$rows);}
}
