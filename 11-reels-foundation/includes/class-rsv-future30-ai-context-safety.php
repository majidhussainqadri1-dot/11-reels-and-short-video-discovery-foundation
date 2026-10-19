<?php
defined( 'ABSPATH' ) || exit;

/** Prevent internal database identities or stale source assertions entering File 16 context. */
final class RSV_Future30_AI_Context_Safety {
	public static function register() {
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_response' ), 95, 3 );
	}

	public static function filter_response( $response, $server, $request ) {
		unset( $server );
		if ( ! ( $response instanceof WP_REST_Response ) || 'GET' !== $request->get_method() || ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/ai-context$#', $request->get_route(), $m ) ) return $response;
		$data = $response->get_data();
		if ( ! is_array( $data ) || ! isset( $data['context'] ) ) return $response;
		$reel = RSV_Repository::find( $m[1], true );
		if ( ! $reel || ! RSV_Security::can_view_reel( $reel ) ) return $response;
		$context = (array) $data['context'];
		foreach ( array( 'evidence','knowledge_card' ) as $bucket ) {
			$clean = array();
			foreach ( (array) ( $context[ $bucket ] ?? array() ) as $row ) {
				if ( ! is_array( $row ) ) continue;
				unset( $row['id'], $row['owner_id'], $row['reel_id'], $row['payload_json'] );
				$p = (array) ( $row['payload'] ?? array() );
				$owner = RSV_Helpers::text( $p['source_owner'] ?? 'File 06', 30 );
				$refs = array_values( array_filter( array_map( static function( $ref ) { return RSV_Helpers::text( $ref, 255 ); }, (array) ( $p['source_refs'] ?? array() ) ) ) );
				if ( $refs ) {
					$valid = true;
					foreach ( $refs as $ref ) if ( true !== apply_filters( 'rsv_future30_public_ref_valid', false, $owner, $ref, 'ai-grounded-context' ) ) { $valid = false; break; }
					if ( ! $valid ) continue;
				}
				$clean[] = $row;
			}
			$context[ $bucket ] = $clean;
		}
		foreach ( array( 'citations','chapters' ) as $bucket ) {
			foreach ( (array) ( $context[ $bucket ] ?? array() ) as &$row ) if ( is_array( $row ) ) unset( $row['id'], $row['owner_id'], $row['reel_id'], $row['source_reel_id'] );
			unset( $row );
		}
		$context['medical_authority'] = 'education-only-no-diagnosis-prescription-dose-or-emergency-replacement';
		$data['context'] = $context;
		$data['execute_ai'] = false;
		$response->set_data( $data );
		$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
		$response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
		return $response;
	}
}

RSV_Future30_AI_Context_Safety::register();
