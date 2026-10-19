<?php
defined( 'ABSPATH' ) || exit;

/** Concurrency-safe protected mutations for private Future30 user state. */
final class RSV_Future30_Private_State_Integrity {
	public static function register() {
		add_filter( 'rest_request_before_callbacks', array( __CLASS__, 'before_callbacks' ), 20, 3 );
	}

	public static function before_callbacks( $response, $handler, $request ) {
		unset( $handler );
		if ( null !== $response || 'POST' !== $request->get_method() || '/rsv/v1/future30/collections' !== $request->get_route() ) return $response;
		if ( ! is_user_logged_in() ) return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to manage study collections.', RSV_TEXT_DOMAIN ), 401 );
		$rate = RSV_Security::rate_limit( 'future30_collections', 60, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) return $rate;
		$p = (array) $request->get_json_params();
		$name = RSV_Helpers::text( $p['name'] ?? '', 80 );
		if ( '' === trim( $name ) ) return RSV_Helpers::error( 'rsv_collection_name_required', __( 'Collection name is required.', RSV_TEXT_DOMAIN ), 422 );
		$key = self::collection_key( $p['key'] ?? '', $name );
		$refs = array_values( array_unique( array_map( 'sanitize_text_field', (array) ( $p['reels'] ?? array() ) ) ) );
		if ( count( $refs ) > 500 ) return RSV_Helpers::error( 'rsv_collection_too_large', __( 'A study collection may contain at most 500 Reels.', RSV_TEXT_DOMAIN ), 422 );
		foreach ( $refs as $ref ) {
			$reel = RSV_Repository::find( $ref, true );
			if ( ! $reel || ! RSV_Security::can_view_reel( $reel, 0 ) ) return RSV_Helpers::error( 'rsv_collection_reel_invalid', __( 'Every study-collection item must be a current public Reel.', RSV_TEXT_DOMAIN ), 422 );
		}
		$user_id = get_current_user_id();
		$result = RSV_DB::transaction(
			static function () use ( $user_id, $name, $key, $refs ) {
				global $wpdb;
				$table = RSV_Helpers::table( 'future_user_state' );
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT id,payload_json,version FROM $table WHERE user_id=%d AND feature_id=%s AND object_ref=%s AND state_key=%s LIMIT 1 FOR UPDATE", $user_id, 'F11-FUT-020', 'user', 'collections' ), ARRAY_A );
				$payload = $row ? (array) RSV_Helpers::json_decode( $row['payload_json'], array( 'collections'=>array() ) ) : array( 'collections'=>array() );
				$collections = (array) ( $payload['collections'] ?? array() );
				if ( isset( $collections[ $key ] ) && ! hash_equals( (string) ( $collections[ $key ]['name'] ?? '' ), $name ) ) return RSV_Helpers::error( 'rsv_collection_key_conflict', __( 'This collection identity is already assigned to a different collection.', RSV_TEXT_DOMAIN ), 409 );
				$collections[ $key ] = array( 'name'=>$name, 'reels'=>$refs, 'private'=>true, 'updated_at'=>RSV_Helpers::now() );
				$json = RSV_Helpers::json_encode( array( 'collections'=>$collections ) );
				$now = RSV_Helpers::now();
				if ( $row ) {
					$version = absint( $row['version'] );
					$ok = $wpdb->update( $table, array( 'payload_json'=>$json, 'version'=>$version+1, 'updated_at'=>$now ), array( 'id'=>absint($row['id']), 'version'=>$version ), array('%s','%d','%s'), array('%d','%d') );
					if ( 1 !== $ok ) return RSV_Helpers::error( 'rsv_collection_conflict', __( 'Study collections changed. Refresh and retry.', RSV_TEXT_DOMAIN ), 409 );
					$new_version = $version + 1;
				} else {
					$ok = $wpdb->insert( $table, array( 'user_id'=>$user_id,'feature_id'=>'F11-FUT-020','object_ref'=>'user','state_key'=>'collections','payload_json'=>$json,'version'=>1,'created_at'=>$now,'updated_at'=>$now ), array('%d','%s','%s','%s','%s','%d','%s','%s') );
					if ( 1 !== $ok ) return RSV_Helpers::error( 'rsv_collection_conflict', __( 'Study collections changed. Refresh and retry.', RSV_TEXT_DOMAIN ), 409 );
					$new_version = 1;
				}
				if ( ! RSV_Helpers::audit( 'study_collection', $user_id, 'update', '', (string)$new_version, 'F11-FUT-020', array( 'collection_ref'=>$key ), $user_id ) || ! RSV_Helpers::outbox( 'ReelStudyCollectionUpdated', 'study_collection', $user_id, array( 'user_ref'=>RSV_Helpers::opaque_user_ref( $user_id, 'future30-collection' ), 'collection_ref'=>$key ) ) ) return RSV_Helpers::error( 'rsv_collection_evidence_failed', __( 'The study collection could not be committed with complete evidence.', RSV_TEXT_DOMAIN ), 500 );
				return array( 'payload'=>array( 'collections'=>$collections ), 'version'=>$new_version, 'updated_at'=>$now );
			}
		);
		if ( is_wp_error( $result ) ) return $result;
		RSV_Helpers::no_cache_private();
		return rest_ensure_response( $result );
	}

	private static function collection_key( $supplied, $name ) {
		$supplied = trim( (string) $supplied );
		if ( $supplied && preg_match( '/^[a-z0-9][a-z0-9_-]{0,59}$/', $supplied ) ) return $supplied;
		return 'collection_' . substr( hash( 'sha256', strtolower( $name ) ), 0, 24 );
	}
}

RSV_Future30_Private_State_Integrity::register();
