<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Jobs {
	public function register() {
		add_action( 'rsv_hourly', array( $this, 'hourly' ) );
		add_action( 'rsv_process_outbox', array( $this, 'outbox' ) );
		add_action( 'vwlb_event_VideoRestricted', array( $this, 'video_restricted' ), 10, 2 );
		add_action( 'vwlb_event_VideoAssetReady', array( $this, 'video_ready' ), 10, 2 );
	}

	public static function schedule() {
		if ( ! wp_next_scheduled( 'rsv_hourly' ) ) wp_schedule_event( time() + 300, 'hourly', 'rsv_hourly' );
		if ( ! wp_next_scheduled( 'rsv_process_outbox' ) ) wp_schedule_event( time() + 120, 'rsv_five_minutes', 'rsv_process_outbox' );
	}

	public static function unschedule() {
		wp_clear_scheduled_hook( 'rsv_hourly' );
		wp_clear_scheduled_hook( 'rsv_process_outbox' );
	}

	public static function intervals( $schedules ) {
		$schedules['rsv_five_minutes'] = array( 'interval' => 300, 'display' => 'Every five minutes' );
		return $schedules;
	}

	public function hourly() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'impressions' ) . ' WHERE created_at < %s', gmdate( 'Y-m-d H:i:s', time() - 90 * DAY_IN_SECONDS ) ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'idempotency' ) . ' WHERE expires_at < %s', RSV_Helpers::now() ) );
		RSV_Migration::reconcile( 100 );
	}

	public function outbox() {
		global $wpdb;
		$table = RSV_Helpers::table( 'outbox' );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE status IN ('pending','retry') AND available_at<=%s ORDER BY id ASC LIMIT 50", RSV_Helpers::now() ), ARRAY_A );
		foreach ( $rows as $row ) {
			try {
				do_action( 'rsv_event', $row['event_name'], RSV_Helpers::json_decode( $row['payload_json'] ), $row['event_id'] );
				$wpdb->update( $table, array( 'status' => 'delivered', 'updated_at' => RSV_Helpers::now() ), array( 'id' => $row['id'] ) );
			} catch ( Throwable $e ) {
				$attempts = (int) $row['attempts'] + 1;
				$status = $attempts >= 8 ? 'dead' : 'retry';
				$delay = min( DAY_IN_SECONDS, 60 * ( 2 ** min( 8, $attempts ) ) );
				$wpdb->update( $table, array( 'status' => $status, 'attempts' => $attempts, 'available_at' => gmdate( 'Y-m-d H:i:s', time() + $delay ), 'last_error' => RSV_Helpers::text( $e->getMessage(), 500 ), 'updated_at' => RSV_Helpers::now() ), array( 'id' => $row['id'] ) );
			}
		}
	}

	public function video_restricted( $video_id, $payload = array() ) {
		$this->set_by_video( $video_id, 'restricted', 'File 10 video restricted' );
	}

	public function video_ready( $video_id, $payload = array() ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE video_id=%d AND status='media_processing'", absint( $video_id ) ), ARRAY_A );
		foreach ( $rows as $row ) {
			if ( ! is_wp_error( RSV_File10::validate_for_reel( $video_id, $row['owner_id'] ) ) ) {
				RSV_Repository::update_versioned( $row['id'], $row['version'], array( 'status' => 'review' ) );
			}
		}
	}

	private function set_by_video( $video_id, $state, $reason ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE video_id=%d", absint( $video_id ) ), ARRAY_A );
		foreach ( $rows as $row ) {
			if ( RSV_State_Machine::allowed( $row['status'], $state ) ) {
				RSV_Repository::update_versioned( $row['id'], $row['version'], array( 'status' => $state ) );
				RSV_Helpers::audit( 'reel', $row['id'], 'dependency_state', $row['status'], $state, $reason );
			}
		}
	}
}
