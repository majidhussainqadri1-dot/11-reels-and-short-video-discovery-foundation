<?php
defined( 'ABSPATH' ) || exit;

/** Transactional privacy erasure replacement for Future30 private state. */
final class RSV_Future30_Privacy_Integrity {
	public static function register() {
		// Override the earlier Future30 callbacks with SQL-error-aware privacy handlers.
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'exporters' ), 100 );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'erasers' ), 100 );
	}

	public static function exporters( $exporters ) {
		$exporters = is_array( $exporters ) ? $exporters : array();
		$exporters['rsv-future30'] = array(
			'exporter_friendly_name' => __( 'Reel study collections, notes and preferences', RSV_TEXT_DOMAIN ),
			'callback' => array( __CLASS__, 'export' ),
		);
		return $exporters;
	}

	/** A failed query must not certify a complete personal-data export. */
	public static function export( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) return array( 'data'=>array(), 'done'=>true );

		global $wpdb;
		$table = RSV_Helpers::table( 'future_user_state' );
		$limit = 100;
		$page = max( 1, absint( $page ) );
		$offset = ( $page - 1 ) * $limit;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT feature_id,object_ref,state_key,payload_json,updated_at FROM $table WHERE user_id=%d ORDER BY id ASC LIMIT %d OFFSET %d",
				absint( $user->ID ), $limit, $offset
			),
			ARRAY_A
		);
		if ( ! is_array( $rows ) || '' !== (string) $wpdb->last_error ) {
			return RSV_Helpers::error( 'rsv_future30_export_unavailable', __( 'Private Reel study data could not be exported safely. Please retry.', RSV_TEXT_DOMAIN ), 503 );
		}

		$data = array();
		foreach ( $rows as $i => $row ) {
			$data[] = array(
				'group_id' => 'rsv-future30',
				'group_label' => __( 'Reel learning and preferences', RSV_TEXT_DOMAIN ),
				'item_id' => 'rsv-f30-' . $page . '-' . $i,
				'data' => array(
					array( 'name'=>__( 'Feature', RSV_TEXT_DOMAIN ), 'value'=>$row['feature_id'] ),
					array( 'name'=>__( 'Reference', RSV_TEXT_DOMAIN ), 'value'=>$row['object_ref'] ),
					array( 'name'=>__( 'State', RSV_TEXT_DOMAIN ), 'value'=>$row['state_key'] ),
					array( 'name'=>__( 'Data', RSV_TEXT_DOMAIN ), 'value'=>$row['payload_json'] ),
					array( 'name'=>__( 'Updated', RSV_TEXT_DOMAIN ), 'value'=>$row['updated_at'] ),
				),
			);
		}
		return array( 'data'=>$data, 'done'=>count( $rows ) < $limit );
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
				$raw_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $table WHERE user_id=%d ORDER BY id ASC LIMIT 100 FOR UPDATE", $user_id ) );
				if ( ! is_array( $raw_ids ) || '' !== (string) $wpdb->last_error ) {
					return RSV_Helpers::error( 'rsv_future30_erase_lookup_unavailable', __( 'Private Reel data could not be checked safely. Please retry.', RSV_TEXT_DOMAIN ), 503 );
				}
				$ids = array();
				foreach ( $raw_ids as $raw_id ) {
					if ( ! is_numeric( $raw_id ) || absint( $raw_id ) < 1 ) {
						return RSV_Helpers::error( 'rsv_future30_erase_lookup_invalid', __( 'Private Reel data could not be verified safely. Please retry.', RSV_TEXT_DOMAIN ), 503 );
					}
					$ids[] = absint( $raw_id );
				}
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
				$remaining_raw = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE user_id=%d", $user_id ) );
				if ( null === $remaining_raw || false === $remaining_raw ||
					'' !== (string) $wpdb->last_error || ! is_numeric( $remaining_raw ) ||
					(float) $remaining_raw < 0 ) {
					return RSV_Helpers::error( 'rsv_future30_erase_count_unavailable', __( 'Private Reel data erasure could not be verified. Please retry.', RSV_TEXT_DOMAIN ), 503 );
				}
				return array( 'removed'=>absint($removed), 'done'=>0 === absint( $remaining_raw ) );
			}
		);
		if ( is_wp_error( $result ) ) return array( 'items_removed'=>false, 'items_retained'=>true, 'messages'=>array( $result->get_error_message() ), 'done'=>false );
		return array( 'items_removed'=>! empty($result['removed']), 'items_retained'=>false, 'messages'=>array(), 'done'=>! empty($result['done']) );
	}
}

RSV_Future30_Privacy_Integrity::register();
