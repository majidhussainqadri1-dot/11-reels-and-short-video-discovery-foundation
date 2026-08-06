<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Repository {
	private static $columns = array( 'video_id','owner_id','title','slug','topic','language','caption','disclosure','cover_id','visibility','share_token_hash','status','rights_status','consent_status','captions_status','safety_labels_json','review_note','restricted_reason','rank_score','published_by','published_at' );

	public static function find( $id, $public = false ) {
		global $wpdb; $table = RSV_Helpers::table( 'reels' );
		return $public ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE public_id=%s", (string)$id ), ARRAY_A ) : $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d", (int)$id ), ARRAY_A );
	}
	public static function public_dto( $row, $include_private = false ) {
		if ( ! $row ) return null;
		$video = RSV_File10::video( (int)$row['video_id'] );
		$user = get_userdata( (int)$row['owner_id'] );
		$user_public_id = apply_filters( 'rsv_user_public_id', '', (int)$row['owner_id'] );
		if ( ! $user_public_id ) $user_public_id = 'usr_' . substr( hash_hmac( 'sha256', (string)(int)$row['owner_id'], wp_salt('auth') ), 0, 20 );
		$owner = array(
			'id' => $user_public_id,
			'name' => $user ? $user->display_name : __( 'Publisher', RSV_TEXT_DOMAIN ),
			'profile_url' => apply_filters( 'rsv_profile_url', get_author_posts_url( (int)$row['owner_id'] ), (int)$row['owner_id'] ),
			'label' => RSV_Security::publisher_label( (int)$row['owner_id'] ),
			'follow_url' => apply_filters( 'rsv_follow_url', '', (int)$row['owner_id'] ),
		);
		$cover_url = $row['cover_id'] ? wp_get_attachment_image_url( (int)$row['cover_id'], 'large' ) : '';
		$data = array(
			'id'=>$row['public_id'], 'video_id'=>RSV_File10::public_id((int)$row['video_id']), 'title'=>$row['title'], 'slug'=>$row['slug'],
			'topic'=>$row['topic'], 'language'=>$row['language'], 'caption'=>$row['caption'], 'disclosure'=>$row['disclosure'],
			'cover_id'=>(int)$row['cover_id'], 'cover_url'=>$cover_url ?: '', 'visibility'=>$row['visibility'], 'status'=>$row['status'],
			'owner'=>$owner, 'duration_seconds'=>$video?(int)($video['duration_seconds']??0):0,
			'url'=>home_url('/reel/'.rawurlencode($row['public_id']).'/'.rawurlencode($row['slug']).'/'),
			'video_url'=>$video&&!empty($video['public_id'])?home_url('/video/'.rawurlencode($video['public_id']).'/'.rawurlencode($video['slug']??'').'/'):'',
			'comments_url'=>apply_filters('rsv_comments_url','',$row), 'share_url'=>home_url('/reel/'.rawurlencode($row['public_id']).'/'.rawurlencode($row['slug']).'/'),
			'published_at'=>$row['published_at'], 'updated_at'=>$row['updated_at'], 'version'=>(int)$row['version'],
		);
		if ( $include_private ) {
			$data['rights_status']=$row['rights_status']; $data['consent_status']=$row['consent_status']; $data['captions_status']=$row['captions_status'];
			$data['safety_labels']=RSV_Helpers::json_decode($row['safety_labels_json']); $data['review_note']=$row['review_note'];
			$data['restricted_reason']=$row['restricted_reason']; $data['rank_score']=(float)$row['rank_score'];
		}
		return $data;
	}

	public static function feed( $args = array() ) {
		global $wpdb; $table=RSV_Helpers::table('reels');
		$limit=min(30,max(1,absint($args['limit']??10))); $topic=RSV_Helpers::enum($args['topic']??'',RSV_Contracts::TOPICS,'');
		$sort='latest'===($args['sort']??'')?'latest':'rank'; $cursor=RSV_Helpers::cursor_decode($args['cursor']??'');
		if ( $cursor && ($cursor['sort']??'')!==$sort ) $cursor=null;
		$items=array(); $scanned=0; $has_more=false; $last_scanned=null;
		for($batch=0;$batch<5&&count($items)<$limit;$batch++){
			$where='status=%s AND visibility=%s'; $params=array('published','public');
			if($topic){$where.=' AND topic=%s';$params[]=$topic;}
			if($cursor){
				if('latest'===$sort){$where.=' AND (published_at<%s OR (published_at=%s AND id<%d))';$params[]=$cursor['time'];$params[]=$cursor['time'];$params[]=(int)$cursor['id'];}
				else{$where.=' AND (rank_score<%f OR (rank_score=%f AND (published_at<%s OR (published_at=%s AND id<%d))))';$params[]=(float)$cursor['score'];$params[]=(float)$cursor['score'];$params[]=$cursor['time'];$params[]=$cursor['time'];$params[]=(int)$cursor['id'];}
			}
			$order='latest'===$sort?'published_at DESC,id DESC':'rank_score DESC,published_at DESC,id DESC';
			$batch_limit=max(20,min(100,($limit-count($items))*4+1)); $params[]=$batch_limit;
			$rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE $where ORDER BY $order LIMIT %d",$params),ARRAY_A);
			if(!$rows){$has_more=false;break;}
			$has_more = count($rows) === $batch_limit;
			foreach($rows as $index=>$row){$last_scanned=$row;$scanned++;if(RSV_Access::public_feed_eligible($row)){$items[]=self::public_dto($row);if(count($items)>=$limit){$has_more=$has_more||$index<count($rows)-1;break;}}}
			if(!$has_more||!$last_scanned)break;
			$cursor=self::cursor_from_row($last_scanned,$sort);
		}
		$next=$has_more&&$last_scanned?RSV_Helpers::cursor_encode(self::cursor_from_row($last_scanned,$sort)):null;
		return array('items'=>$items,'next_cursor'=>$next,'has_more'=>(bool)$next,'scanned'=>$scanned,'sort'=>$sort);
	}
	private static function cursor_from_row($row,$sort){return array('sort'=>$sort,'score'=>(float)$row['rank_score'],'time'=>$row['published_at']?:$row['updated_at'],'id'=>(int)$row['id']);}

	public static function update_versioned( $id, $expected_version, $changes ) {
		global $wpdb; $table=RSV_Helpers::table('reels'); $sets=array(); $values=array();
		foreach($changes as $key=>$value){if(!in_array($key,self::$columns,true))continue;if(null===$value){$sets[]="$key=NULL";}elseif(in_array($key,array('video_id','owner_id','cover_id','published_by'),true)){$sets[]="$key=%d";$values[]=(int)$value;}elseif('rank_score'===$key){$sets[]="$key=%f";$values[]=(float)$value;}else{$sets[]="$key=%s";$values[]=(string)$value;}}
		$sets[]='version=version+1';$sets[]='updated_at=%s';$values[]=RSV_Helpers::now();$values[]=(int)$id;$values[]=(int)$expected_version;
		$updated=$wpdb->query($wpdb->prepare("UPDATE $table SET ".implode(',',$sets).' WHERE id=%d AND version=%d',$values));
		if(1!==$updated)return RSV_Helpers::error('rsv_version_conflict',__('This Reel changed. Refresh and try again.',RSV_TEXT_DOMAIN),409);
		return self::find($id);
	}

	public static function progress_row( $user_id, $reel_id, $lock=false ) {
		global $wpdb;$table=RSV_Helpers::table('progress');$suffix=$lock?' FOR UPDATE':'';
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND reel_id=%d$suffix",$user_id,$reel_id),ARRAY_A);
	}

	public static function history( $user_id, $limit=100, $offset=0 ) {
		global $wpdb;$p=RSV_Helpers::table('progress');$r=RSV_Helpers::table('reels');$limit=min(100,max(1,absint($limit)));$offset=max(0,absint($offset));
		$rows=$wpdb->get_results($wpdb->prepare("SELECT p.position_seconds,p.completed,p.replays,p.updated_at AS progress_updated_at,r.* FROM $p p JOIN $r r ON r.id=p.reel_id WHERE p.user_id=%d ORDER BY p.updated_at DESC,p.id DESC LIMIT %d OFFSET %d",$user_id,$limit,$offset),ARRAY_A);
		$out=array();foreach($rows as $row){$allowed=RSV_Access::can_view($row,'history');$dto=$allowed?self::public_dto($row):array('id'=>$row['public_id'],'title'=>__('Unavailable Reel',RSV_TEXT_DOMAIN),'status'=>'unavailable','url'=>'');$dto['progress']=array('position_seconds'=>(int)$row['position_seconds'],'completed'=>(bool)$row['completed'],'replays'=>(int)$row['replays'],'updated_at'=>$row['progress_updated_at']);$out[]=$dto;}
		return $out;
	}

	public static function report_find( $id, $public=false ) {
		global $wpdb;$table=RSV_Helpers::table('reports');return $public?$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE public_id=%s",(string)$id),ARRAY_A):$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",(int)$id),ARRAY_A);
	}
	public static function report_update_versioned( $id,$version,$changes ) {
		global $wpdb;$allowed=array('status','action_code','reviewer_id','moderator_note','appeal_text','triaged_at','decided_at','appealed_at','closed_at');$sets=array();$values=array();
		foreach($changes as $key=>$value){if(!in_array($key,$allowed,true))continue;if(null===$value)$sets[]="$key=NULL";elseif('reviewer_id'===$key){$sets[]="$key=%d";$values[]=(int)$value;}else{$sets[]="$key=%s";$values[]=(string)$value;}}
		$sets[]='version=version+1';$sets[]='updated_at=%s';$values[]=RSV_Helpers::now();$values[]=(int)$id;$values[]=(int)$version;
		$updated=$wpdb->query($wpdb->prepare('UPDATE '.RSV_Helpers::table('reports').' SET '.implode(',',$sets).' WHERE id=%d AND version=%d',$values));
		return 1===$updated?self::report_find($id):RSV_Helpers::error('rsv_report_version_conflict',__('This report changed. Refresh and retry.',RSV_TEXT_DOMAIN),409);
	}
}
