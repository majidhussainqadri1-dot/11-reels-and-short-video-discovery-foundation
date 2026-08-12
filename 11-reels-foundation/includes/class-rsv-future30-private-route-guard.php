<?php
defined( 'ABSPATH' ) || exit;

/** Revalidate current File 00 membership state on private Future30 REST surfaces. */
final class RSV_Future30_Private_Route_Guard {
	public static function register() {
		add_filter( 'rest_request_before_callbacks', array( __CLASS__, 'guard' ), 5, 3 );
	}

	public static function guard( $response, $handler, $request ) {
		unset( $handler );
		if ( null !== $response || ! self::is_private_future30_route( $request->get_route() ) ) return $response;
		if ( ! is_user_logged_in() ) return $response;
		$claims = RSV_Security::claims();
		$allowed = 'active' === ( $claims['status'] ?? '' )
			&& empty( $claims['is_suspended'] )
			&& ! empty( $claims['membership_approved'] )
			&& ! empty( $claims['guardian_ok'] );
		if ( $allowed ) return $response;
		return RSV_Helpers::error( 'rsv_private_state_ineligible', __( 'Your current membership or guardian state does not allow this private Reel action.', RSV_TEXT_DOMAIN ), 403 );
	}

	private static function is_private_future30_route( $route ) {
		$patterns = array(
			'#^/rsv/v1/reels/reel_[a-f0-9-]{36}/quiz/attempt$#',
			'#^/rsv/v1/future30/collections$#',
			'#^/rsv/v1/reels/reel_[a-f0-9-]{36}/notes$#',
			'#^/rsv/v1/reels/reel_[a-f0-9-]{36}/ai-context$#',
			'#^/rsv/v1/future30/feed-preferences$#',
			'#^/rsv/v1/future30/accessibility$#',
		);
		foreach ( $patterns as $pattern ) if ( preg_match( $pattern, (string) $route ) ) return true;
		return false;
	}
}

RSV_Future30_Private_Route_Guard::register();
