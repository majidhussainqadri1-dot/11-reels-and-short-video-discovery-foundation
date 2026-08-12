<?php
defined( 'ABSPATH' ) || exit;

/** Additional fail-closed guards around Future30 write identities. */
final class RSV_Future30_Write_Integrity {
	public static function register() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'pre_dispatch' ), 15, 3 );
	}

	public static function pre_dispatch( $result, $server, $request ) {
		unset( $server );
		if ( null !== $result || 'POST' !== $request->get_method() ) return $result;
		$route = $request->get_route();
		$feature = '';
		$reel_id = 0;
		if ( preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/(F11-FUT-[0-9]{3})$#', $route, $m ) ) {
			$feature = $m[2];
			$reel = RSV_Repository::find( $m[1], true );
			$reel_id = $reel ? absint( $reel['id'] ) : 0;
		} elseif ( '/rsv/v1/future30/series' === $route ) {
			$feature = 'F11-FUT-001';
		} elseif ( '/rsv/v1/future30/learning-paths' === $route ) {
			$feature = 'F11-FUT-002';
		} else {
			return $result;
		}
		$params = (array) $request->get_json_params();
		$public_id = RSV_Helpers::text( $params['public_id'] ?? '', 80 );
		if ( ! $public_id ) return $result;
		global $wpdb;
		$table = RSV_Helpers::table( 'future_objects' );
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT feature_id,reel_id,owner_id,version FROM $table WHERE public_id=%s LIMIT 1", $public_id ), ARRAY_A );
		if ( ! $existing ) return $result;
		if ( 'F11-FUT-007' === $feature ) {
			return RSV_Helpers::error( 'rsv_version_snapshot_immutable', __( 'A historical Reel version snapshot is immutable and cannot be overwritten.', RSV_TEXT_DOMAIN ), 409 );
		}
		if ( $feature !== (string) $existing['feature_id'] || $reel_id !== absint( $existing['reel_id'] ) ) {
			return RSV_Helpers::error( 'rsv_future_public_id_conflict', __( 'This Future30 public identity belongs to a different feature or Reel and cannot be reassigned.', RSV_TEXT_DOMAIN ), 409 );
		}
		return $result;
	}
}

RSV_Future30_Write_Integrity::register();
