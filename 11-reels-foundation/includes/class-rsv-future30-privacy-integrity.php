<?php
defined( 'ABSPATH' ) || exit;

/** Transactional privacy erasure replacement for Future30 private state. */
final class RSV_Future30_Privacy_Integrity {
	public static function register() {
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'erasers' ), 100 );
	}

	public static function erasers( $erasers ) {
		$erasers = is_array( $erasers ) ? $erasers : array();
		$erasers['rsv-future30'] = array(
			'eraser_friendly_name' => __( 'Reel study collections, notes and preferences', RSV_TEXT_DOMAIN ),
			'callback' => array( __CLASS__, 'erase' ),
		);
		return $erasers;
	}

	public static function erase( $email, $page = 1 ) {
		unset( $page );
		$user = get_user_by( 'email', $email );
		if ( ! $user ) return array( 'items_removed'=>false, 'items_retained'=>false, 'messages'=>array(), 'done'=>true );
		$user_id = absint( $user->ID );
		$result = RSV_DB::transaction(
			static function () use ( $user_id ) {
				global $wpdb;
				$table = RSV_Helpers::table( 'future_user_state' );
				$ids = array_map( 'absint', (array) $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $table WHERE user_id=%d ORDER BY id ASC LIMIT 100 FOR UPDATE", $user_id ) ) );
				if ( ! $ids ) return array( 'removed'=>0, 'done'=>true );
				$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
				$args = array_merge( array( $user_id ), $ids );
				$sql = $wpdb->prepare( "DELETE FROM $table WHERE user_id=%d AND id IN ($placeholders)", $args );
				$removed = $wpdb->query( $sql );
				if ( false === $removed || absint( $removed ) !== count( $ids ) ) return RSV_Helpers::error( 'rsv_future30_erase_incomplete', __( 'Private Reel data could not be erased atomically.', RSV_TEXT_DOMAIN ), 500 );
				$opaque_user_ref = RSV_Helpers::opaque_user_ref( $user_id, 'future30-erasure' );
				if ( ! RSV_Helpers::audit( 'privacy', 0, 'future30_erase', '', '', 'Private Future30 state erased', array( 'count'=>absint($removed), 'subject_ref'=>$opaque_user_ref ), 0 ) ) return RSV_Helpers::error( 'rsv_future30_erase_evidence_failed', __( 'Private Reel data erasure could not be committed with complete evidence.', RSV_TEXT_DOMAIN ), 500 );
				if ( ! RSV_Helpers::outbox(
					'ReelFuturePrivateStateErased',
					'privacy',
					0,
					array(
						'user_ref' => $opaque_user_ref,
						'features' => array( 'F11-FUT-019','F11-FUT-020','F11-FUT-021','F11-FUT-025','F11-FUT-026','F11-FUT-029' ),
						'reconcile' => array( 'cache', 'index', 'feed-preferences', 'accessibility-preferences' ),
						'count' => absint( $removed ),
					)
				) ) return RSV_Helpers::error( 'rsv_future30_erase_reconciliation_failed', __( 'Private Reel data erasure could not be committed with downstream reconciliation evidence.', RSV_TEXT_DOMAIN ), 500 );
				$remaining = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE user_id=%d", $user_id ) );
				return array( 'removed'=>absint($removed), 'done'=>0 === $remaining );
			}
		);
		if ( is_wp_error( $result ) ) return array( 'items_removed'=>false, 'items_retained'=>true, 'messages'=>array( $result->get_error_message() ), 'done'=>false );
		return array( 'items_removed'=>! empty($result['removed']), 'items_retained'=>false, 'messages'=>array(), 'done'=>! empty($result['done']) );
	}
}

RSV_Future30_Privacy_Integrity::register();
