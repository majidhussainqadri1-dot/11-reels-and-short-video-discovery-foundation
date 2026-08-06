<?php
defined( 'ABSPATH' ) || exit;

final class RSV_REST {
	public function register() {
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'feed' ),
				'permission_callback' => '__return_true',
				'args' => array(
					'limit' => array( 'default' => 12, 'sanitize_callback' => 'absint', 'validate_callback' => static function ( $v ) { return absint( $v ) >= 1 && absint( $v ) <= 50; } ),
					'cursor' => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'topic' => array( 'sanitize_callback' => 'sanitize_key' ),
					'sort' => array( 'default' => 'recommended', 'sanitize_callback' => 'sanitize_key', 'validate_callback' => static function ( $v ) { return in_array( $v, array( 'recommended', 'latest' ), true ); } ),
					'format' => array( 'default' => 'json', 'sanitize_callback' => 'sanitize_key', 'validate_callback' => static function ( $v ) { return in_array( $v, array( 'json', 'html' ), true ); } ),
				),
			),
			array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'create' ), 'permission_callback' => array( $this, 'can_submit' ) ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'get' ), 'permission_callback' => '__return_true' ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/submit', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'submit' ), 'permission_callback' => array( $this, 'can_submit' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/publish', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'publish' ), 'permission_callback' => array( $this, 'can_publish' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/view-session', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'view_session' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/progress', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'progress' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/report', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'report' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/interact', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'interact' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/history', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'history' ), 'permission_callback' => 'is_user_logged_in' ),
			array( 'methods' => WP_REST_Server::DELETABLE, 'callback' => array( $this, 'clear_history' ), 'permission_callback' => 'is_user_logged_in' ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/insights', array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'insights' ), 'permission_callback' => array( $this, 'can_insights' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reports/(?P<id>rpt_[a-f0-9-]{36})/appeal', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'appeal' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reports/(?P<id>rpt_[a-f0-9-]{36})/moderate', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'moderate' ), 'permission_callback' => array( $this, 'can_moderate' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/health', array( 'methods' => WP_REST_Server::READABLE, 'callback' => static function () { return RSV_Diagnostics::summary(); }, 'permission_callback' => array( $this, 'can_manage' ) ) );
	}

	public function can_submit() { return RSV_Security::can( RSV_Contracts::CAP_SUBMIT ); }
	public function can_publish() { return RSV_Security::can( RSV_Contracts::CAP_PUBLISH ); }
	public function can_moderate() { return RSV_Security::can( RSV_Contracts::CAP_MODERATE ); }
	public function can_manage() { return RSV_Security::can( RSV_Contracts::CAP_MANAGE ); }
	public function can_insights() { return RSV_Security::can( RSV_Contracts::CAP_INSIGHTS ) || RSV_Security::can( RSV_Contracts::CAP_SUBMIT ); }

	private function reel_from_request( $request ) {
		return RSV_Repository::find( sanitize_text_field( (string) $request['id'] ), true );
	}

	public function feed( $request ) {
		$data = RSV_Repository::feed( array(
			'limit' => $request->get_param( 'limit' ),
			'cursor' => $request->get_param( 'cursor' ),
			'topic' => $request->get_param( 'topic' ),
			'sort' => $request->get_param( 'sort' ),
		) );
		if ( is_wp_error( $data ) ) return $data;
		if ( 'html' === $request->get_param( 'format' ) ) {
			$renderer = new RSV_Frontend();
			$html = array();
			foreach ( $data['items'] as $item ) {
				$row = RSV_Repository::find( $item['id'], true );
				if ( $row && RSV_Security::can_view_reel( $row, 0 ) ) $html[] = $renderer->render_reel( $row, true );
			}
			$data['html'] = $html;
		}
		return rest_ensure_response( $data );
	}

	public function get( $request ) {
		$reel = $this->reel_from_request( $request );
		if ( ! RSV_Security::can_view_reel( $reel ) ) return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		$dto = RSV_Repository::public_dto( $reel );
		$dto['playback'] = RSV_File10::playback( $reel['video_id'] );
		return rest_ensure_response( $dto );
	}

	public function create( $request ) { return RSV_Reels::create( (array) $request->get_json_params(), $request->get_header( 'Idempotency-Key' ) ); }
	public function submit( $request ) { $r=$this->reel_from_request($request); return $r ? RSV_Reels::submit($r['id'],absint($request->get_param('version'))) : RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404); }
	public function publish( $request ) { $r=$this->reel_from_request($request); return $r ? RSV_Reels::publish($r['id'],absint($request->get_param('version'))) : RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404); }
	public function view_session( $request ) { $r=$this->reel_from_request($request); return $r ? RSV_Reels::start_view_session($r['id']) : RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404); }
	public function progress( $request ) { $r=$this->reel_from_request($request); return $r ? RSV_Reels::progress($r['id'],$request->get_param('session_id'),$request->get_param('seconds')) : RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404); }
	public function report( $request ) { $r=$this->reel_from_request($request); return $r ? RSV_Reels::report($r['id'],$request->get_param('reason'),$request->get_param('details'),$request->get_header('Idempotency-Key')) : RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404); }
	public function interact( $request ) { $r=$this->reel_from_request($request); return $r ? RSV_Reels::interact($r['id'],$request->get_param('type')) : RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404); }
	public function appeal( $request ) { return RSV_Reels::appeal($request['id'],$request->get_param('text'),absint($request->get_param('version'))); }

	public function history( $request ) {
		RSV_Helpers::no_cache_private();
		return rest_ensure_response( RSV_Repository::history( get_current_user_id(), min(100,max(1,absint($request->get_param('limit')?:100))), absint($request->get_param('offset')) ) );
	}
	public function clear_history() {
		$result = RSV_Reels::clear_history( get_current_user_id() );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}
	public function insights() { RSV_Helpers::no_cache_private(); return rest_ensure_response( RSV_Reels::insights( get_current_user_id() ) ); }
	public function moderate( $request ) { return RSV_Reels::moderate( sanitize_text_field( (string) $request['id'] ), $request->get_param( 'decision' ), $request->get_param( 'reason' ), absint( $request->get_param( 'version' ) ), true ); }
}
