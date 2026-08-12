<?php
defined( 'ABSPATH' ) || exit;

/** Privacy-threshold enforcement for derived Future30 analytics responses. */
final class RSV_Future30_Analytics_Safety {
	const MIN_AGGREGATE = 5;

	public static function register() {
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_response' ), 96, 3 );
	}

	public static function filter_response( $response, $server, $request ) {
		unset( $server );
		if ( ! ( $response instanceof WP_REST_Response ) || 'GET' !== $request->get_method() ) return $response;
		$route = $request->get_route();
		if ( '/rsv/v1/future30/search-opportunities' === $route ) {
			$data = $response->get_data();
			if ( is_array( $data ) && isset( $data['items'] ) ) {
				$data['items'] = array_values( array_filter( (array) $data['items'], static function( $row ) {
					return is_array( $row ) && absint( $row['aggregate_count'] ?? 0 ) >= self::MIN_AGGREGATE;
				} ) );
				$data['privacy'] = 'aggregate-only-minimum-5';
				$response->set_data( $data );
			}
			$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
			$response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
		}
		if ( '/rsv/v1/future30/creator-research' === $route ) {
			$data = $response->get_data();
			if ( is_array( $data ) ) {
				$data['minimum_viewers'] = max( self::MIN_AGGREGATE, absint( $data['minimum_viewers'] ?? 0 ) );
				$data['viewer_identity_exposed'] = false;
				$response->set_data( $data );
			}
			$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
			$response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
		}
		return $response;
	}
}

RSV_Future30_Analytics_Safety::register();
