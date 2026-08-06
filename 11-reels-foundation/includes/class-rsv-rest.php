<?php
defined( 'ABSPATH' ) || exit;

final class RSV_REST {
	public function register() {
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'feed' ), 'permission_callback' => '__return_true' ),
			array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'create' ), 'permission_callback' => array( $this, 'can_submit' ) ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>[a-z0-9_-]+)', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'get' ), 'permission_callback' => '__return_true' ),
		) );
		foreach ( array( 'submit','publish','progress','report','interact' ) as $action ) {
			register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>[a-z0-9_-]+)/' . $action, array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, $action ),
				'permission_callback' => 'progress' === $action || 'report' === $action || 'interact' === $action ? 'is_user_logged_in' : array( $this, 'can_submit' ),
			) );
		}
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/history', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'history' ), 'permission_callback' => 'is_user_logged_in' ),
			array( 'methods' => WP_REST_Server::DELETABLE, 'callback' => array( $this, 'clear_history' ), 'permission_callback' => 'is_user_logged_in' ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/insights', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'insights' ),
			'permission_callback' => array( $this, 'can_insights' ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reports/(?P<id>\d+)/moderate', array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'moderate' ),
			'permission_callback' => array( $this, 'can_moderate' ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/health', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => static function () { return RSV_Diagnostics::summary(); },
			'permission_callback' => array( $this, 'can_manage' ),
		) );
	}

	public function can_submit() { return RSV_Security::can( RSV_Contracts::CAP_SUBMIT ); }
	public function can_moderate() { return RSV_Security::can( RSV_Contracts::CAP_MODERATE ); }
	public function can_manage() { return RSV_Security::can( RSV_Contracts::CAP_MANAGE ); }
	public function can_insights() { return RSV_Security::can( RSV_Contracts::CAP_INSIGHTS ) || RSV_Security::can( RSV_Contracts::CAP_SUBMIT ); }

	private function reel_from_request( $request ) {
		$id = sanitize_text_field( $request['id'] );
		return ctype_digit( $id ) ? RSV_Repository::find( (int) $id ) : RSV_Repository::find( $id, true );
	}

	public function feed( $request ) {
		return rest_ensure_response( RSV_Repository::feed( array(
			'limit' => $request->get_param( 'limit' ),
			'cursor' => $request->get_param( 'cursor' ),
			'topic' => sanitize_key( (string) $request->get_param( 'topic' ) ),
			'sort' => sanitize_key( (string) $request->get_param( 'sort' ) ),
		) ) );
	}

	public function get( $request ) {
		$reel = $this->reel_from_request( $request );
		if ( ! $reel || 'published' !== $reel['status'] || ! RSV_File10::publicly_eligible( $reel['video_id'] ) ) {
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		}
		$dto = RSV_Repository::public_dto( $reel );
		$dto['playback'] = RSV_File10::playback( $reel['video_id'] );
		return rest_ensure_response( $dto );
	}

	public function create( $request ) {
		return RSV_Reels::create( $request->get_json_params(), $request->get_header( 'Idempotency-Key' ) );
	}

	public function submit( $request ) {
		$reel = $this->reel_from_request( $request );
		return $reel ? RSV_Reels::submit( $reel['id'], absint( $request->get_param( 'version' ) ) ) : RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
	}

	public function publish( $request ) {
		$reel = $this->reel_from_request( $request );
		return $reel ? RSV_Reels::publish( $reel['id'], absint( $request->get_param( 'version' ) ) ) : RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
	}

	public function progress( $request ) {
		$reel = $this->reel_from_request( $request );
		return $reel ? RSV_Reels::progress( $reel['id'], $request->get_param( 'seconds' ), $request->get_param( 'duration' ), (bool) $request->get_param( 'completed' ), (bool) $request->get_param( 'rapid' ) ) : RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
	}

	public function report( $request ) {
		$reel = $this->reel_from_request( $request );
		return $reel ? RSV_Reels::report( $reel['id'], $request->get_param( 'reason' ), $request->get_param( 'details' ), $request->get_header( 'Idempotency-Key' ) ) : RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
	}

	public function interact( $request ) {
		$reel = $this->reel_from_request( $request );
		if ( ! $reel ) return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		return RSV_File10::interact( $reel['video_id'], $request->get_param( 'type' ) );
	}

	public function history() {
		RSV_Helpers::no_cache_private();
		return rest_ensure_response( RSV_Repository::history( get_current_user_id() ) );
	}

	public function clear_history() {
		global $wpdb;
		$removed = $wpdb->delete( RSV_Helpers::table( 'progress' ), array( 'user_id' => get_current_user_id() ), array( '%d' ) );
		RSV_Helpers::audit( 'history', get_current_user_id(), 'clear', '', 'complete' );
		return rest_ensure_response( array( 'removed' => (int) $removed ) );
	}

	public function insights() {
		RSV_Helpers::no_cache_private();
		return rest_ensure_response( RSV_Reels::insights( get_current_user_id() ) );
	}

	public function moderate( $request ) {
		return RSV_Reels::moderate(
			absint( $request['id'] ),
			$request->get_param( 'decision' ),
			$request->get_param( 'reason' ),
			absint( $request->get_param( 'version' ) )
		);
	}
}
