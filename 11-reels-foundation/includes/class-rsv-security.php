<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Security {
	public static function claims( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$claims = apply_filters( 'rsv_identity_claims', null, $user_id, RSV_CONTRACT_VERSION );
		if ( is_array( $claims ) ) return wp_parse_args( $claims, array( 'user_id'=>$user_id,'status'=>'unknown','is_founder'=>false,'is_verified_doctor'=>false,'is_suspended'=>true,'guardian_ok'=>false,'capabilities'=>array() ) );
		$admin = user_can( $user_id, 'manage_options' );
		return array( 'user_id'=>$user_id,'status'=>$admin?'active':'unavailable','is_founder'=>false,'is_verified_doctor'=>false,'is_suspended'=>!$admin,'guardian_ok'=>$admin,'capabilities'=>array() );
	}
	public static function can( $capability, $object = null, $purpose = '' ) {
		if ( ! is_user_logged_in() ) return false;
		$claims = self::claims();
		if ( ! empty( $claims['is_suspended'] ) || 'active' !== ( $claims['status'] ?? '' ) ) return false;
		$native = current_user_can( $capability ) || current_user_can( 'manage_options' );
		$asserted = in_array( $capability, (array) ( $claims['capabilities'] ?? array() ), true );
		if ( self::CAP_SUBMIT_ALIAS() === $capability ) {
			$identity = ! empty( $claims['is_founder'] ) || ! empty( $claims['is_verified_doctor'] ) || current_user_can( 'manage_options' );
			if ( ! $identity || ( ! empty( $claims['guardian_required'] ) && empty( $claims['guardian_ok'] ) ) ) return false;
		}
		if ( $object && ! current_user_can( 'manage_options' ) && (int) ( $object['owner_id'] ?? 0 ) !== get_current_user_id() && ! in_array( $capability, array( RSV_Contracts::CAP_PUBLISH, RSV_Contracts::CAP_MODERATE, RSV_Contracts::CAP_MANAGE ), true ) ) return false;
		return (bool) apply_filters( 'rsv_authorize', $native || $asserted, $capability, $object, $purpose, $claims );
	}
	private static function CAP_SUBMIT_ALIAS() { return RSV_Contracts::CAP_SUBMIT; }
	public static function publisher_label( $user_id ) {
		$claims = self::claims( $user_id );
		if ( ! empty( $claims['is_founder'] ) ) return __( 'Verified Founder', RSV_TEXT_DOMAIN );
		if ( ! empty( $claims['is_verified_doctor'] ) && empty( $claims['is_suspended'] ) ) return __( 'Verified Doctor', RSV_TEXT_DOMAIN );
		if ( user_can( $user_id, 'manage_options' ) ) return __( 'Authorized Administrator', RSV_TEXT_DOMAIN );
		return __( 'Authorized Publisher', RSV_TEXT_DOMAIN );
	}
	public static function rate_limit( $scope, $limit, $window ) {
		$actor = get_current_user_id();
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
		$key = 'rsv_rl_' . hash_hmac( 'sha256', $scope . '|' . $actor . '|' . $ip, wp_salt( 'auth' ) );
		$data = get_transient( $key );
		if ( ! is_array( $data ) || time() >= (int) ( $data['reset'] ?? 0 ) ) $data = array( 'count'=>0,'reset'=>time()+$window );
		$data['count']++;
		set_transient( $key, $data, max( 1, $data['reset'] - time() ) );
		if ( $data['count'] > $limit ) return RSV_Helpers::error( 'rsv_rate_limited', __( 'Too many requests. Please wait and try again.', RSV_TEXT_DOMAIN ), 429, array( 'retry_after'=>max(1,$data['reset']-time()) ) );
		return true;
	}
	public static function idempotency_begin( $scope, $key, $payload ) {
		$key = RSV_Helpers::text( $key, 120 );
		if ( ! $key ) return RSV_Helpers::error( 'rsv_idempotency_required', __( 'An idempotency key is required.', RSV_TEXT_DOMAIN ), 400 );
		global $wpdb; $table = RSV_Helpers::table( 'idempotency' ); $actor = get_current_user_id(); $hash = hash( 'sha256', RSV_Helpers::json_encode( $payload ) );
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE actor_id=%d AND scope_key=%s AND idem_key=%s", $actor, $scope, $key ), ARRAY_A );
		if ( $row && strtotime( $row['expires_at'] . ' UTC' ) <= time() ) { $wpdb->delete( $table, array( 'id'=>(int)$row['id'] ), array('%d') ); $row = null; }
		if ( $row ) {
			if ( ! hash_equals( $row['payload_hash'], $hash ) ) return RSV_Helpers::error( 'rsv_idempotency_conflict', __( 'The idempotency key was reused with different data.', RSV_TEXT_DOMAIN ), 409 );
			if ( 'complete' === $row['status'] ) return array( 'replay'=>true,'response'=>RSV_Helpers::json_decode( $row['response_json'] ) );
			if ( 'failed' === $row['status'] ) { $wpdb->delete( $table, array('id'=>(int)$row['id']), array('%d') ); }
			else return RSV_Helpers::error( 'rsv_request_in_progress', __( 'The request is already in progress.', RSV_TEXT_DOMAIN ), 409 );
		}
		$inserted = $wpdb->insert( $table, array( 'actor_id'=>$actor,'scope_key'=>$scope,'idem_key'=>$key,'payload_hash'=>$hash,'status'=>'started','response_json'=>'{}','expires_at'=>gmdate('Y-m-d H:i:s',time()+DAY_IN_SECONDS),'created_at'=>RSV_Helpers::now(),'updated_at'=>RSV_Helpers::now() ) );
		if ( ! $inserted ) return RSV_Helpers::error( 'rsv_idempotency_race', __( 'A concurrent request is already using this key.', RSV_TEXT_DOMAIN ), 409 );
		return array( 'replay'=>false );
	}
	public static function idempotency_finish( $scope, $key, $response ) { self::idempotency_set( $scope, $key, 'complete', $response ); }
	public static function idempotency_fail( $scope, $key, $response = array() ) { self::idempotency_set( $scope, $key, 'failed', $response ); }
	private static function idempotency_set( $scope, $key, $status, $response ) {
		global $wpdb;
		$wpdb->update( RSV_Helpers::table('idempotency'), array('status'=>$status,'response_json'=>RSV_Helpers::json_encode($response),'updated_at'=>RSV_Helpers::now()), array('actor_id'=>get_current_user_id(),'scope_key'=>$scope,'idem_key'=>RSV_Helpers::text($key,120)) );
	}
	public static function legal_hold( $subject_type, $subject_id ) {
		global $wpdb; $table=RSV_Helpers::table('legal_holds');
		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE subject_type=%s AND subject_id=%d AND status='active' AND (expires_at IS NULL OR expires_at>%s) LIMIT 1", $subject_type, absint($subject_id), RSV_Helpers::now() ) );
	}
}
