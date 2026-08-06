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
		if ( ! wp_next_scheduled( 'rsv_hourly' ) ) {
			wp_schedule_event( time() + 300, 'hourly', 'rsv_hourly' );
		}
		if ( ! wp_next_scheduled( 'rsv_process_outbox' ) ) {
			wp_schedule_event( time() + 120, 'rsv_five_minutes', 'rsv_process_outbox' );
		}
	}

	public static function unschedule() {
		wp_clear_scheduled_hook( 'rsv_hourly' );
		wp_clear_scheduled_hook( 'rsv_process_outbox' );
	}

	public static function intervals( $schedules ) {
		$schedules['rsv_five_minutes'] = array( 'interval' => 300, 'display' => __( 'Every five minutes', RSV_TEXT_DOMAIN ) );
		return $schedules;
	}

	public function hourly() {
		global $wpdb;
		$now = RSV_Helpers::now();
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'impressions' ) . ' WHERE created_at<%s', gmdate( 'Y-m-d H:i:s', time() - 90 * DAY_IN_SECONDS ) ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'idempotency' ) . ' WHERE expires_at<%s', $now ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'rate_limits' ) . ' WHERE expires_at<%s', $now ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . RSV_Helpers::table( 'view_sessions' ) . ' WHERE expires_at<%s', $now ) );
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE " . RSV_Helpers::table( 'outbox' ) . " SET status='retry',lock_token='',locked_at=NULL,updated_at=%s WHERE status='processing' AND locked_at<%s",
				$now,
				gmdate( 'Y-m-d H:i:s', time() - 15 * MINUTE_IN_SECONDS )
			)
		);
		RSV_Migration::reconcile( 100 );
		$this->refresh_ranks( 100 );
	}

	private function refresh_ranks( $limit ) {
		global $wpdb;
		$ids = (array) $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . RSV_Helpers::table( 'reels' ) . " WHERE status='published' ORDER BY updated_at ASC LIMIT %d", absint( $limit ) ) );
		foreach ( $ids as $id ) {
			RSV_Reels::recalculate_rank( absint( $id ) );
		}
	}

	public function outbox() {
		global $wpdb;
		$table = RSV_Helpers::table( 'outbox' );
		$ids = (array) $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $table WHERE status IN ('pending','retry') AND available_at<=%s ORDER BY id ASC LIMIT 50", RSV_Helpers::now() ) );
		foreach ( $ids as $id ) {
			$token = RSV_Helpers::public_id( 'lock' );
			$now   = RSV_Helpers::now();
			$claimed = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $table SET status='processing',lock_token=%s,locked_at=%s,updated_at=%s WHERE id=%d AND status IN ('pending','retry') AND available_at<=%s",
					$token,
					$now,
					$now,
					absint( $id ),
					$now
				)
			);
			if ( 1 !== $claimed ) {
				continue;
			}
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d AND lock_token=%s", absint( $id ), $token ), ARRAY_A );
			if ( ! $row ) {
				continue;
			}
			try {
				$payload = RSV_Helpers::json_decode( $row['payload_json'] );
				do_action( 'rsv_event', $row['event_name'], $payload, $row['event_id'] );
				$delivered = (bool) apply_filters( 'rsv_event_delivery_result', true, $row['event_name'], $payload, $row['event_id'] );
				if ( ! $delivered ) {
					throw new RuntimeException( 'Consumer reported delivery failure.' );
				}
				$wpdb->update(
					$table,
					array( 'status' => 'delivered', 'lock_token' => '', 'locked_at' => null, 'updated_at' => RSV_Helpers::now() ),
					array( 'id' => $row['id'], 'lock_token' => $token ),
					array( '%s', '%s', '%s', '%s' ),
					array( '%d', '%s' )
				);
			} catch ( Throwable $exception ) {
				$attempts = absint( $row['attempts'] ) + 1;
				$status   = $attempts >= 8 ? 'dead' : 'retry';
				$delay    = min( DAY_IN_SECONDS, 60 * ( 2 ** min( 8, $attempts ) ) );
				$wpdb->update(
					$table,
					array(
						'status'       => $status,
						'attempts'     => $attempts,
						'available_at' => gmdate( 'Y-m-d H:i:s', time() + $delay ),
						'lock_token'   => '',
						'locked_at'    => null,
						'last_error'   => RSV_Helpers::text( $exception->getMessage(), 500 ),
						'updated_at'   => RSV_Helpers::now(),
					),
					array( 'id' => $row['id'], 'lock_token' => $token ),
					array( '%s', '%d', '%s', '%s', '%s', '%s', '%s' ),
					array( '%d', '%s' )
				);
				if ( 'dead' === $status ) {
					RSV_Helpers::audit( 'outbox', $row['id'], 'dead_letter', 'processing', 'dead', $exception->getMessage(), array(), 0 );
				}
			}
		}
	}

	public function video_restricted( $video_id, $payload = array() ) {
		unset( $payload );
		$this->set_by_video( $video_id, 'restricted', 'File 10 video restricted' );
	}

	public function video_ready( $video_id, $payload = array() ) {
		unset( $payload );
		global $wpdb;
		$rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . RSV_Helpers::table( 'reels' ) . " WHERE video_id=%d AND status='media_processing'", absint( $video_id ) ), ARRAY_A );
		foreach ( $rows as $row ) {
			if ( is_wp_error( RSV_File10::validate_for_reel( $video_id, $row['owner_id'] ) ) ) {
				continue;
			}
			$updated = RSV_Repository::update_versioned( $row['id'], $row['version'], array( 'status' => 'review' ) );
			if ( ! is_wp_error( $updated ) ) {
				RSV_Helpers::audit( 'reel', $row['id'], 'dependency_state', 'media_processing', 'review', 'File 10 asset ready', array(), 0 );
			}
		}
	}

	private function set_by_video( $video_id, $state, $reason ) {
		global $wpdb;
		$rows = (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . RSV_Helpers::table( 'reels' ) . ' WHERE video_id=%d', absint( $video_id ) ), ARRAY_A );
		foreach ( $rows as $row ) {
			if ( ! RSV_State_Machine::allowed( $row['status'], $state ) ) {
				continue;
			}
			$updated = RSV_Repository::update_versioned( $row['id'], $row['version'], array( 'status' => $state ) );
			if ( ! is_wp_error( $updated ) ) {
				RSV_Helpers::audit( 'reel', $row['id'], 'dependency_state', $row['status'], $state, $reason, array(), 0 );
			}
		}
	}
}
