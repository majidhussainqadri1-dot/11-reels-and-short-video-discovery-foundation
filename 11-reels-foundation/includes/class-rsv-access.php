<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Access {
	public static function can_view( $reel, $purpose = 'view', $token = '' ) {
		if ( ! is_array( $reel ) ) return false;
		if ( RSV_Security::can( RSV_Contracts::CAP_MANAGE, $reel, 'preview_reel' ) || ( is_user_logged_in() && (int) $reel['owner_id'] === get_current_user_id() ) ) return true;
		if ( 'published' !== $reel['status'] ) return false;
		switch ( $reel['visibility'] ) {
			case 'public': return RSV_File10::eligible( (int) $reel['video_id'], false );
			case 'unlisted':
				if ( ! RSV_File10::eligible( (int) $reel['video_id'], true ) ) return false;
				$token = $token ?: ( isset( $_GET['rsv_access'] ) ? RSV_Helpers::text( wp_unslash( $_GET['rsv_access'] ), 120 ) : '' );
				return $token && ! empty( $reel['share_token_hash'] ) && hash_equals( $reel['share_token_hash'], hash_hmac( 'sha256', $token, wp_salt( 'auth' ) ) );
			case 'member':
				if ( ! RSV_File10::eligible( (int) $reel['video_id'], true ) ) return false;
				$claims = RSV_Security::claims();
				return is_user_logged_in() && empty( $claims['is_suspended'] ) && 'active' === ( $claims['status'] ?? '' );
			case 'entitled':
				if ( ! RSV_File10::eligible( (int) $reel['video_id'], true ) ) return false;
				if ( ! is_user_logged_in() ) return false;
				return (bool) apply_filters( 'rsv_entitlement_check', false, get_current_user_id(), $reel, $purpose );
		}
		return false;
	}

	public static function can_interact( $reel ) {
		$claims = RSV_Security::claims();
		return is_user_logged_in() && self::can_view( $reel, 'interact' ) && empty( $claims['is_suspended'] );
	}

	public static function public_feed_eligible( $reel ) {
		return is_array( $reel ) && 'published' === $reel['status'] && 'public' === $reel['visibility'] && RSV_File10::eligible( (int) $reel['video_id'], false );
	}

	public static function share_token() {
		return rtrim( strtr( base64_encode( random_bytes( 24 ) ), '+/', '-_' ), '=' );
	}
}
