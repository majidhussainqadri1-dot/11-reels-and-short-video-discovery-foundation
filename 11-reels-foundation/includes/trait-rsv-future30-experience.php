<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Future30_Experience_Trait {
	public static function body_classes( $classes ) {
		if ( ! is_user_logged_in() ) return $classes;
		$state = self::state_get( 'F11-FUT-029', 'user', 'accessibility', array( 'payload'=>self::default_accessibility(), 'version'=>0 ) );
		$prefs = array_merge( self::default_accessibility(), (array) ( $state['payload'] ?? array() ) );
		foreach ( array( 'transcript_only','high_contrast','reduced_motion','audio_description_preferred','keyboard_first' ) as $key ) if ( ! empty( $prefs[$key] ) ) $classes[] = 'rsv-a11y-' . str_replace( '_', '-', $key );
		$classes[] = 'rsv-captions-' . sanitize_html_class( (string) $prefs['captions_size'] );
		$classes[] = 'rsv-captions-' . sanitize_html_class( (string) $prefs['captions_position'] );
		return array_values( array_unique( $classes ) );
	}

	public function creator_research(){RSV_Helpers::no_cache_private();$base=RSV_Reels::insights(get_current_user_id());$allowed=array('completion-by-topic','source-opens','save-to-study','related-knowledge-opens','quiz-completion','language-demand','search-to-reel-conversion');$extra=apply_filters('rsv_future30_creator_research_aggregates',array(),get_current_user_id(),array('minimum_viewers'=>5,'identities'=>false,'allowed_metrics'=>$allowed));$safe=array();if(is_array($extra))foreach($allowed as $key)if(isset($extra[$key])&&is_numeric($extra[$key]))$safe[$key]=0+$extra[$key];return rest_ensure_response(array('reel_metrics'=>$base,'educational_value_metrics'=>$safe,'allowed_metrics'=>$allowed,'minimum_viewers'=>5,'viewer_identity_exposed'=>false,'follower_count_primary'=>false));}

	private static function run_safety_scan($reel,$p){$text=strtolower(wp_strip_all_tags(($p['title']??$reel['title']).' '.($p['caption']??$reel['caption']).' '.($p['transcript_excerpt']??'')));$findings=array();$rules=array('guaranteed cure'=>'guaranteed-cure','100% cure'=>'guaranteed-cure','emergency replacement'=>'emergency-replacement','do not seek emergency'=>'emergency-replacement','prescription'=>'prescription-like','dosage'=>'dose-like','dose'=>'dose-like');foreach($rules as $needle=>$code)if(false!==strpos($text,$needle))$findings[]=$code;$provider=apply_filters('rsv_pre_publish_safety_scan',null,array('reel_public_id'=>$reel['public_id'],'title'=>$p['title']??$reel['title'],'caption'=>$p['caption']??$reel['caption'],'transcript_excerpt'=>$p['transcript_excerpt']??'','clinical_authority'=>false,'auto_publish'=>false));$provider_available=is_array($provider);if($provider_available&&!empty($provider['findings']))$findings=array_merge($findings,(array)$provider['findings']);$findings=array_values(array_unique(array_map('sanitize_key',$findings)));$decision=$findings?'review-required':($provider_available?'pass':'review-required');$result=self::put_object('F11-FUT-028','safety_scan',$reel['id'],'Pre-publish safety scan',array('decision'=>$decision,'findings'=>$findings,'provider_available'=>$provider_available,'human_review_required'=>('pass'!==$decision),'auto_publish'=>false));if(is_wp_error($result))return $result;return array('decision'=>$decision,'findings'=>$findings,'provider_available'=>$provider_available,'human_review_required'=>('pass'!==$decision),'record'=>$result);}
	public function safety_scan($request){$rate=RSV_Security::rate_limit('future30_safety_scan',60,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;$reel=self::reel($request['id'],true);if(is_wp_error($reel))return $reel;$p=(array)$request->get_json_params();$idem=$request->get_header('Idempotency-Key');$scope='future30_safety_scan_'.$reel['public_id'];$begin=RSV_Security::idempotency_begin($scope,$idem,$p);if(is_wp_error($begin))return $begin;if(!empty($begin['replay']))return rest_ensure_response($begin['response']);return self::idempotent_finish_result($scope,$idem,self::run_safety_scan($reel,$p));}

	private static function default_accessibility(){return array('transcript_only'=>false,'captions_size'=>'medium','captions_position'=>'bottom','high_contrast'=>false,'reduced_motion'=>false,'audio_description_preferred'=>false,'keyboard_first'=>false);}
	public function accessibility(){RSV_Helpers::no_cache_private();return rest_ensure_response(self::state_get('F11-FUT-029','user','accessibility',array('payload'=>self::default_accessibility(),'version'=>0)));}
	public function accessibility_write($request){RSV_Helpers::no_cache_private();$rate=RSV_Security::rate_limit('future30_accessibility',60,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;$p=(array)$request->get_json_params();$data=self::default_accessibility();foreach(array('transcript_only','high_contrast','reduced_motion','audio_description_preferred','keyboard_first') as $k)if(array_key_exists($k,$p))$data[$k]=!empty($p[$k]);$data['captions_size']=RSV_Helpers::enum($p['captions_size']??'medium',array('small','medium','large','extra-large'),'medium');$data['captions_position']=RSV_Helpers::enum($p['captions_position']??'bottom',array('top','bottom'),'bottom');$saved=self::state_put('F11-FUT-029','user','accessibility',$data);if(!is_wp_error($saved))do_action('rsv_future30_accessibility_preferences_changed',get_current_user_id(),$data);return $saved;}

	public function knowledge_graph($request){$reel=self::reel($request['id']);if(is_wp_error($reel))return $reel;if(!RSV_Security::can_view_reel($reel))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);$local=self::edge_rows('F11-FUT-030',$reel['id'],100);$provider=apply_filters('rsv_future30_knowledge_graph',array(),array('reel_public_id'=>$reel['public_id'],'topic'=>$reel['topic'],'language'=>$reel['language'],'limit'=>100,'public_safe'=>true));$safe=array();if(is_array($provider))foreach(array_slice($provider,0,100) as $row){if(!is_array($row))continue;$owner=RSV_Helpers::text($row['target_owner']??'File 06',30);$ref=RSV_Helpers::text($row['target_ref']??'',255);if(!self::canonical_ref_public_valid($owner,$ref,'knowledge-graph-provider'))continue;$safe[]=array('edge_type'=>sanitize_key($row['edge_type']??'related-to'),'target_owner'=>$owner,'target_ref'=>$ref,'label'=>RSV_Helpers::text($row['label']??'',160),'evidence_ref'=>RSV_Helpers::text($row['evidence_ref']??'',255));}return rest_ensure_response(array('local_edges'=>$local,'federated_edges'=>$safe,'canonical_knowledge_owner'=>'File 06','cross_platform_graph_owner'=>'File 26','file11_role'=>'Reel relationship projection only'));}

	public static function public_tools( $reel ) {
		if(!$reel||!is_array($reel))return array();
		return array(
			'series'=>self::object_rows('F11-FUT-001',0,0,20),
			'related'=>self::edge_rows('F11-FUT-003',$reel['id'],12),
			'citations'=>self::edge_rows('F11-FUT-004',$reel['id'],20),
			'evidence'=>self::object_rows('F11-FUT-005',$reel['id'],0,10),
			'chapters'=>self::edge_rows('F11-FUT-017',$reel['id'],30),
			'knowledge_card'=>self::object_rows('F11-FUT-018',$reel['id'],0,1),
			'transcript'=>self::object_rows('F11-FUT-016',$reel['id'],0,1),
			'graph'=>self::edge_rows('F11-FUT-030',$reel['id'],30),
		);
	}

	public static function render_tools( $reel ) {
		$tools=self::public_tools($reel);$has=false;foreach($tools as $items)if(!empty($items)){$has=true;break;}if(!$has)return '';
		$html='<details class="rsv-future30-tools"><summary>'.esc_html__('Study & knowledge tools',RSV_TEXT_DOMAIN).'</summary>';
		if(!empty($tools['citations'])){$html.='<h3>'.esc_html__('Timestamp sources',RSV_TEXT_DOMAIN).'</h3><ul>';foreach($tools['citations'] as $row)$html.='<li>'.esc_html(gmdate('i:s',absint($row['start_second']))).' — '.esc_html($row['target_owner'].' · '.$row['target_ref']).'</li>';$html.='</ul>';}
		if(!empty($tools['chapters'])){$html.='<h3>'.esc_html__('Key moments',RSV_TEXT_DOMAIN).'</h3><ol>';foreach($tools['chapters'] as $row)$html.='<li>'.esc_html(gmdate('i:s',absint($row['start_second']))).' — '.esc_html($row['payload']['title']??__('Chapter',RSV_TEXT_DOMAIN)).'</li>';$html.='</ol>';}
		if(!empty($tools['related'])){$html.='<h3>'.esc_html__('Related knowledge',RSV_TEXT_DOMAIN).'</h3><ul>';foreach($tools['related'] as $row)$html.='<li>'.esc_html($row['target_owner'].' · '.$row['target_ref']).'</li>';$html.='</ul>';}
		if(!empty($tools['knowledge_card'][0]['payload']['summary']))$html.='<p class="rsv-knowledge-summary">'.esc_html($tools['knowledge_card'][0]['payload']['summary']).'</p>';if(!empty($tools['transcript'][0]['payload']['transcript_ref']))$html.='<p class="rsv-transcript-reference"><strong>'.esc_html__('Reviewed transcript:',RSV_TEXT_DOMAIN).'</strong> '.esc_html($tools['transcript'][0]['payload']['transcript_ref']).'</p>';
		return $html.'</details>';
	}

	public static function shortcode( $atts ) {
		$atts=shortcode_atts(array('reel'=>''),$atts,'rsv_future30');$reel=self::reel($atts['reel']);if(is_wp_error($reel)||!RSV_Security::can_view_reel($reel))return '';return self::render_tools($reel);
	}

	public static function privacy_exporters( $exporters ) {
		$exporters['rsv-future30'] = array( 'exporter_friendly_name'=>__('Reel study collections, notes and preferences',RSV_TEXT_DOMAIN), 'callback'=>array(__CLASS__,'privacy_export') );
		return $exporters;
	}
	public static function privacy_erasers( $erasers ) {
		$erasers['rsv-future30'] = array( 'eraser_friendly_name'=>__('Reel study collections, notes and preferences',RSV_TEXT_DOMAIN), 'callback'=>array(__CLASS__,'privacy_erase') );
		return $erasers;
	}
	public static function privacy_export( $email, $page = 1 ) {
		$user=get_user_by('email',$email);if(!$user)return array('data'=>array(),'done'=>true);global $wpdb;$table=RSV_Helpers::table('future_user_state');$limit=100;$offset=(max(1,absint($page))-1)*$limit;$rows=$wpdb->get_results($wpdb->prepare("SELECT feature_id,object_ref,state_key,payload_json,updated_at FROM $table WHERE user_id=%d ORDER BY id ASC LIMIT %d OFFSET %d",$user->ID,$limit,$offset),ARRAY_A);$data=array();foreach($rows as $i=>$row)$data[]=array('group_id'=>'rsv-future30','group_label'=>__('Reel learning and preferences',RSV_TEXT_DOMAIN),'item_id'=>'rsv-f30-'.$page.'-'.$i,'data'=>array(array('name'=>__('Feature',RSV_TEXT_DOMAIN),'value'=>$row['feature_id']),array('name'=>__('Reference',RSV_TEXT_DOMAIN),'value'=>$row['object_ref']),array('name'=>__('State',RSV_TEXT_DOMAIN),'value'=>$row['state_key']),array('name'=>__('Data',RSV_TEXT_DOMAIN),'value'=>$row['payload_json']),array('name'=>__('Updated',RSV_TEXT_DOMAIN),'value'=>$row['updated_at'])));return array('data'=>$data,'done'=>count($rows)<$limit);}
	public static function privacy_erase( $email, $page = 1 ) {
		$user=get_user_by('email',$email);if(!$user)return array('items_removed'=>false,'items_retained'=>false,'messages'=>array(),'done'=>true);global $wpdb;$table=RSV_Helpers::table('future_user_state');$ids=$wpdb->get_col($wpdb->prepare("SELECT id FROM $table WHERE user_id=%d ORDER BY id ASC LIMIT 100",$user->ID));if(!$ids)return array('items_removed'=>false,'items_retained'=>false,'messages'=>array(),'done'=>true);$removed=0;foreach($ids as $id)if(1===$wpdb->delete($table,array('id'=>absint($id),'user_id'=>$user->ID),array('%d','%d')))$removed++;return array('items_removed'=>$removed>0,'items_retained'=>false,'messages'=>array(),'done'=>count($ids)<100);}
}
