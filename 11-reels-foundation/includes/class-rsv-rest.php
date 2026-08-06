<?php
defined( 'ABSPATH' ) || exit;

final class RSV_REST {
	public function register() { add_action( 'rest_api_init', array( $this, 'routes' ) ); }
	private function common_id_args(){return array('id'=>array('required'=>true,'type'=>'string','sanitize_callback'=>array($this,'sanitize_id')));}
	public function sanitize_id($value){$value=RSV_Helpers::text($value,80);return preg_match('/^(?:\d+|reel_[a-f0-9-]{36})$/',$value)?$value:'';}
	public function routes() {
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reels',array(
			array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'feed'),'permission_callback'=>'__return_true','args'=>array('limit'=>array('type'=>'integer','minimum'=>1,'maximum'=>30,'default'=>10),'cursor'=>array('type'=>'string','sanitize_callback'=>'sanitize_text_field'),'topic'=>array('type'=>'string','sanitize_callback'=>'sanitize_key'),'sort'=>array('type'=>'string','enum'=>array('rank','latest'),'default'=>'rank'))),
			array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'create'),'permission_callback'=>array($this,'can_submit')),
		));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reels/(?P<id>[A-Za-z0-9_-]+)',array(
			array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'get'),'permission_callback'=>'__return_true','args'=>$this->common_id_args()),
			array('methods'=>'PATCH','callback'=>array($this,'update'),'permission_callback'=>array($this,'can_submit'),'args'=>$this->common_id_args()),
		));
		foreach(array('submit','publish') as $action)register_rest_route(RSV_Contracts::API_NAMESPACE,'/reels/(?P<id>[A-Za-z0-9_-]+)/'.$action,array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,$action),'permission_callback'=>array($this,$action==='publish'?'can_publish':'can_submit'),'args'=>array_merge($this->common_id_args(),array('version'=>array('required'=>true,'type'=>'integer','minimum'=>1),'review_note'=>array('type'=>'string','sanitize_callback'=>'sanitize_textarea_field')))));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reels/(?P<id>[A-Za-z0-9_-]+)/progress',array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'progress'),'permission_callback'=>array($this,'logged_in'),'args'=>array_merge($this->common_id_args(),array('seconds'=>array('type'=>'integer','minimum'=>0,'required'=>true),'completed'=>array('type'=>'boolean','default'=>false),'rapid'=>array('type'=>'boolean','default'=>false),'session_id'=>array('type'=>'string','sanitize_callback'=>'sanitize_text_field')))));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reels/(?P<id>[A-Za-z0-9_-]+)/impression',array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'impression'),'permission_callback'=>'__return_true','args'=>array_merge($this->common_id_args(),array('seconds'=>array('type'=>'integer','minimum'=>0),'completed'=>array('type'=>'boolean'),'rapid'=>array('type'=>'boolean')))));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reels/(?P<id>[A-Za-z0-9_-]+)/report',array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'report'),'permission_callback'=>array($this,'logged_in'),'args'=>array_merge($this->common_id_args(),array('reason'=>array('required'=>true,'type'=>'string','enum'=>RSV_Contracts::REPORT_REASONS),'details'=>array('type'=>'string','sanitize_callback'=>'sanitize_textarea_field')))));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reels/(?P<id>[A-Za-z0-9_-]+)/interact',array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'interact'),'permission_callback'=>array($this,'logged_in'),'args'=>array_merge($this->common_id_args(),array('type'=>array('required'=>true,'type'=>'string','enum'=>RSV_Contracts::INTERACTIONS)))));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/history',array(array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'history'),'permission_callback'=>array($this,'logged_in'),'args'=>array('page'=>array('type'=>'integer','minimum'=>1,'default'=>1))),array('methods'=>WP_REST_Server::DELETABLE,'callback'=>array($this,'clear_history'),'permission_callback'=>array($this,'logged_in'))));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/insights',array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'insights'),'permission_callback'=>array($this,'can_insights')));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reports/(?P<id>\d+)/moderate',array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'moderate'),'permission_callback'=>array($this,'can_moderate'),'args'=>array('id'=>array('type'=>'integer','minimum'=>1),'decision'=>array('required'=>true,'type'=>'string','enum'=>RSV_Contracts::MODERATION_DECISIONS),'reason'=>array('type'=>'string','sanitize_callback'=>'sanitize_textarea_field'),'version'=>array('required'=>true,'type'=>'integer','minimum'=>1))));
		register_rest_route(RSV_Contracts::API_NAMESPACE,'/reports/(?P<id>\d+)/appeal',array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'appeal'),'permission_callback'=>array($this,'logged_in'),'args'=>array('id'=>array('type'=>'integer','minimum'=>1),'text'=>array('required'=>true,'type'=>'string','sanitize_callback'=>'sanitize_textarea_field'),'version'=>array('required'=>true,'type'=>'integer','minimum'=>1))));
	}
	public function logged_in(){return is_user_logged_in();}
	public function can_submit(){return RSV_Security::can(RSV_Contracts::CAP_SUBMIT,null,'rest');}
	public function can_publish(){return RSV_Security::can(RSV_Contracts::CAP_PUBLISH,null,'rest');}
	public function can_moderate(){return RSV_Security::can(RSV_Contracts::CAP_MODERATE,null,'rest');}
	public function can_insights(){return RSV_Security::can(RSV_Contracts::CAP_INSIGHTS,null,'rest')||RSV_Security::can(RSV_Contracts::CAP_SUBMIT,null,'rest');}
	private function reel($request){$id=(string)$request['id'];return ctype_digit($id)?RSV_Repository::find((int)$id):RSV_Repository::find($id,true);}
	public function feed($r){return rest_ensure_response(RSV_Repository::feed(array('limit'=>$r['limit'],'cursor'=>$r['cursor'],'topic'=>$r['topic'],'sort'=>$r['sort'])));}
	public function get($r){$reel=$this->reel($r);$token=$r->get_param('access')?:$r->get_header('X-RSV-Access');if(!$reel||!RSV_Access::can_view($reel,'read',$token))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);$dto=RSV_Repository::public_dto($reel,RSV_Security::can(RSV_Contracts::CAP_MANAGE,$reel,'read_private'));$playback=RSV_File10::safe_playback($reel['video_id']);if(is_wp_error($playback))return $playback;$dto['playback']=$playback;return rest_ensure_response($dto);}
	public function create($r){return RSV_Reels::create($r->get_json_params(),$r->get_header('Idempotency-Key'));}
	public function update($r){$reel=$this->reel($r);return $reel?RSV_Reels::update($reel['id'],absint($r->get_param('version')),$r->get_json_params()):RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);}
	public function submit($r){$reel=$this->reel($r);return $reel?RSV_Reels::submit($reel['id'],absint($r['version'])):RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);}
	public function publish($r){$reel=$this->reel($r);return $reel?RSV_Reels::publish($reel['id'],absint($r['version']),$r->get_param('review_note')):RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);}
	public function progress($r){$reel=$this->reel($r);return $reel?RSV_Reels::progress($reel['id'],$r['seconds'],$r['completed'],$r['rapid'],$r['session_id']):RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);}
	public function impression($r){$reel=$this->reel($r);return $reel?RSV_Reels::impression($reel['id'],$r['seconds'],$r['completed'],$r['rapid']):RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);}
	public function report($r){$reel=$this->reel($r);return $reel?RSV_Reels::report($reel['id'],$r['reason'],$r['details'],$r->get_header('Idempotency-Key')):RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);}
	public function interact($r){$reel=$this->reel($r);if(!$reel||!RSV_Access::can_interact($reel))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);$rate=RSV_Security::rate_limit('interact_'.$reel['id'],100,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;return RSV_File10::interact($reel['video_id'],$r['type']);}
	public function history($r){RSV_Helpers::no_cache_private();$page=max(1,absint($r['page']));return rest_ensure_response(RSV_Repository::history(get_current_user_id(),50,($page-1)*50));}
	public function clear_history(){if(RSV_Security::legal_hold('user',get_current_user_id()))return RSV_Helpers::error('rsv_legal_hold',__('History is subject to an active legal hold.',RSV_TEXT_DOMAIN),409);global $wpdb;$removed=$wpdb->delete(RSV_Helpers::table('progress'),array('user_id'=>get_current_user_id()),array('%d'));RSV_Helpers::audit('history',get_current_user_id(),'clear','','complete');return rest_ensure_response(array('removed'=>(int)$removed));}
	public function insights(){RSV_Helpers::no_cache_private();return rest_ensure_response(RSV_Reels::insights(get_current_user_id()));}
	public function moderate($r){return RSV_Reels::moderate(absint($r['id']),$r['decision'],$r['reason'],absint($r['version']));}
	public function appeal($r){return RSV_Reels::appeal(absint($r['id']),$r['text'],absint($r['version']));}
}
