<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Privacy {
	public function register() {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'erasers' ) );
	}

	public function exporters( $exporters ) {
		$exporters['rsv-history'] = array(
			'exporter_friendly_name' => __( 'Reel viewing history', RSV_TEXT_DOMAIN ),
			'callback' => array( $this, 'export' ),
		);
		return $exporters;
	}

	public function erasers( $erasers ) {
		$erasers['rsv-history'] = array(
			'eraser_friendly_name' => __( 'Reel viewing history', RSV_TEXT_DOMAIN ),
			'callback' => array( $this, 'erase' ),
		);
		return $erasers;
	}

	private function user_id( $email ) {
		$user = get_user_by( 'email', $email );
		return $user ? (int) $user->ID : 0;
	}

	public function export( $email, $page = 1 ) {
		$user_id = $this->user_id( $email );
		if ( ! $user_id ) return array( 'data' => array(), 'done' => true );
		$rows = RSV_Repository::history( $user_id, 200 );
		$data = array();
		foreach ( $rows as $row ) {
			$data[] = array(
				'group_id' => 'rsv-history',
				'group_label' => __( 'Reel viewing history', RSV_TEXT_DOMAIN ),
				'item_id' => 'rsv-history-' . $row['id'],
				'data' => array(
					array( 'name' => __( 'Reel', RSV_TEXT_DOMAIN ), 'value' => $row['title'] ),
					array( 'name' => __( 'Position', RSV_TEXT_DOMAIN ), 'value' => $row['progress']['position_seconds'] ),
					array( 'name' => __( 'Completed', RSV_TEXT_DOMAIN ), 'value' => $row['progress']['completed'] ? 'yes' : 'no' ),
					array( 'name' => __( 'Updated', RSV_TEXT_DOMAIN ), 'value' => $row['progress']['updated_at'] ),
				),
			);
		}
		return array( 'data' => $data, 'done' => true );
	}

	public function erase( $email, $page = 1 ) {
		$user_id = $this->user_id( $email );
		if ( ! $user_id ) return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true );
		global $wpdb;
		$removed = $wpdb->delete( RSV_Helpers::table( 'progress' ), array( 'user_id' => $user_id ), array( '%d' ) );
		// Reports are retained only as moderation evidence; reporter identity is anonymized.
		$wpdb->update( RSV_Helpers::table( 'reports' ), array( 'reporter_id' => 0 ), array( 'reporter_id' => $user_id ), array( '%d' ), array( '%d' ) );
		RSV_Helpers::audit( 'privacy', $user_id, 'erase', '', 'complete', 'User history erased; retained reports anonymized' );
		return array( 'items_removed' => (bool) $removed, 'items_retained' => true, 'messages' => array( __( 'Moderation evidence was retained in anonymized form.', RSV_TEXT_DOMAIN ) ), 'done' => true );
	}
}
