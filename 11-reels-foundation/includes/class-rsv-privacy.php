<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Privacy {
	public function register() {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'erasers' ) );
	}

	public function exporters( $exporters ) {
		$exporters['rsv-data'] = array(
			'exporter_friendly_name' => __( 'Reels data', RSV_TEXT_DOMAIN ),
			'callback'               => array( $this, 'export' ),
		);
		return $exporters;
	}

	public function erasers( $erasers ) {
		$erasers['rsv-data'] = array(
			'eraser_friendly_name' => __( 'Reels data', RSV_TEXT_DOMAIN ),
			'callback'             => array( $this, 'erase' ),
		);
		return $erasers;
	}

	private function user_id( $email ) {
		$user = get_user_by( 'email', $email );
		return $user ? absint( $user->ID ) : 0;
	}

	public function export( $email, $page = 1 ) {
		$user_id = $this->user_id( $email );
		if ( ! $user_id ) {
			return array( 'data' => array(), 'done' => true );
		}
		$page   = max( 1, absint( $page ) );
		$limit  = 50;
		$offset = ( $page - 1 ) * $limit;
		$data   = array();

		$history = RSV_Repository::history( $user_id, $limit, $offset );
		foreach ( $history as $row ) {
			$data[] = array(
				'group_id'    => 'rsv-history',
				'group_label' => __( 'Reel viewing history', RSV_TEXT_DOMAIN ),
				'item_id'     => 'rsv-history-' . $row['id'],
				'data'        => array(
					array( 'name' => __( 'Reel', RSV_TEXT_DOMAIN ), 'value' => $row['title'] ),
					array( 'name' => __( 'Available', RSV_TEXT_DOMAIN ), 'value' => $row['available'] ? 'yes' : 'no' ),
					array( 'name' => __( 'Position', RSV_TEXT_DOMAIN ), 'value' => $row['progress']['position_seconds'] ),
					array( 'name' => __( 'Completed', RSV_TEXT_DOMAIN ), 'value' => $row['progress']['completed'] ? 'yes' : 'no' ),
					array( 'name' => __( 'Updated', RSV_TEXT_DOMAIN ), 'value' => $row['progress']['updated_at'] ),
				),
			);
		}

		global $wpdb;
		$reports_table = RSV_Helpers::table( 'reports' );
		$reports = (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT rp.* FROM $reports_table rp WHERE rp.reporter_id=%d OR rp.appellant_id=%d ORDER BY rp.id ASC LIMIT %d OFFSET %d",
				$user_id,
				$user_id,
				$limit,
				$offset
			),
			ARRAY_A
		);
		foreach ( $reports as $report ) {
			$data[] = array(
				'group_id'    => 'rsv-reports',
				'group_label' => __( 'Reel reports and appeals', RSV_TEXT_DOMAIN ),
				'item_id'     => 'rsv-report-' . $report['public_id'],
				'data'        => array(
					array( 'name' => __( 'Reason', RSV_TEXT_DOMAIN ), 'value' => $report['reason_code'] ),
					array( 'name' => __( 'Details', RSV_TEXT_DOMAIN ), 'value' => $report['details'] ),
					array( 'name' => __( 'Status', RSV_TEXT_DOMAIN ), 'value' => $report['status'] ),
					array( 'name' => __( 'Appeal', RSV_TEXT_DOMAIN ), 'value' => $report['appeal_text'] ),
					array( 'name' => __( 'Relationship', RSV_TEXT_DOMAIN ), 'value' => absint( $report['reporter_id'] ) === $user_id ? 'reporter' : 'appellant' ),
					array( 'name' => __( 'Created', RSV_TEXT_DOMAIN ), 'value' => $report['created_at'] ),
				),
			);
		}

		$reels = (array) $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . RSV_Helpers::table( 'reels' ) . ' WHERE owner_id=%d ORDER BY id ASC LIMIT %d OFFSET %d', $user_id, $limit, $offset ),
			ARRAY_A
		);
		foreach ( $reels as $reel ) {
			$data[] = array(
				'group_id'    => 'rsv-owned-reels',
				'group_label' => __( 'Owned Reels', RSV_TEXT_DOMAIN ),
				'item_id'     => 'rsv-reel-' . $reel['public_id'],
				'data'        => array(
					array( 'name' => __( 'Title', RSV_TEXT_DOMAIN ), 'value' => $reel['title'] ),
					array( 'name' => __( 'Topic', RSV_TEXT_DOMAIN ), 'value' => $reel['topic'] ),
					array( 'name' => __( 'Status', RSV_TEXT_DOMAIN ), 'value' => $reel['status'] ),
					array( 'name' => __( 'Visibility', RSV_TEXT_DOMAIN ), 'value' => $reel['visibility'] ),
					array( 'name' => __( 'Created', RSV_TEXT_DOMAIN ), 'value' => $reel['created_at'] ),
				),
			);
		}

		return array(
			'data' => $data,
			'done' => count( $history ) < $limit && count( $reports ) < $limit && count( $reels ) < $limit,
		);
	}

	public function erase( $email, $page = 1 ) {
		unset( $page );
		$user_id = $this->user_id( $email );
		if ( ! $user_id ) {
			return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true );
		}
		$result = RSV_DB::transaction(
			static function () use ( $user_id ) {
				global $wpdb;
				$removed  = false;
				$retained = false;
				$messages = array();
				$tables = array(
					'progress'      => 'user_id',
					'view_sessions' => 'user_id',
					'idempotency'   => 'actor_id',
				);
				foreach ( $tables as $table => $column ) {
					$deleted = $wpdb->delete( RSV_Helpers::table( $table ), array( $column => $user_id ), array( '%d' ) );
					if ( false === $deleted ) return RSV_Helpers::error( 'rsv_privacy_delete_failed', __( 'Private Reel data could not be erased safely.', RSV_TEXT_DOMAIN ), 500 );
					$removed = $removed || $deleted > 0;
				}
				$viewer = hash_hmac( 'sha256', (string) $user_id, wp_salt( 'auth' ) );
				foreach ( array( 'impressions', 'value_signal_receipts' ) as $viewer_table ) {
					$deleted = $wpdb->delete( RSV_Helpers::table( $viewer_table ), array( 'viewer_hash' => $viewer ), array( '%s' ) );
					if ( false === $deleted ) return RSV_Helpers::error( 'rsv_privacy_viewer_delete_failed', __( 'Private Reel audience data could not be erased safely.', RSV_TEXT_DOMAIN ), 500 );
					$removed = $removed || $deleted > 0;
				}

				$retention = (bool) apply_filters( 'rsv_privacy_retain_report_evidence', false, $user_id );
				$reports_table = RSV_Helpers::table( 'reports' );
				if ( $retention ) {
					$deidentified = $wpdb->query( $wpdb->prepare( "UPDATE $reports_table SET reporter_id=IF(reporter_id=%d,0,reporter_id),appellant_id=IF(appellant_id=%d,0,appellant_id) WHERE reporter_id=%d OR appellant_id=%d", $user_id, $user_id, $user_id, $user_id ) );
					if ( false === $deidentified ) return RSV_Helpers::error( 'rsv_privacy_report_deidentify_failed', __( 'Moderation evidence could not be de-identified safely.', RSV_TEXT_DOMAIN ), 500 );
					$retained = $retained || $deidentified > 0;
					$messages[] = __( 'Moderation evidence was retained under an approved hold and de-identified.', RSV_TEXT_DOMAIN );
				} else {
					$redacted = $wpdb->query(
						$wpdb->prepare(
							"UPDATE $reports_table SET details=IF(reporter_id=%d,'[redacted by privacy erasure]',details),appeal_text=IF(appellant_id=%d,'[redacted by privacy erasure]',appeal_text),reporter_id=IF(reporter_id=%d,0,reporter_id),appellant_id=IF(appellant_id=%d,0,appellant_id) WHERE reporter_id=%d OR appellant_id=%d",
							$user_id, $user_id, $user_id, $user_id, $user_id, $user_id
						)
					);
					if ( false === $redacted ) return RSV_Helpers::error( 'rsv_privacy_report_redact_failed', __( 'Moderation data could not be minimized safely.', RSV_TEXT_DOMAIN ), 500 );
					$removed = $removed || $redacted > 0;
				}

				$audit_redaction = $wpdb->update( RSV_Helpers::table( 'audit' ), array( 'actor_id' => 0, 'context_json' => '{}' ), array( 'actor_id' => $user_id ), array( '%d', '%s' ), array( '%d' ) );
				if ( false === $audit_redaction ) return RSV_Helpers::error( 'rsv_privacy_audit_redact_failed', __( 'Audit identifiers could not be minimized safely.', RSV_TEXT_DOMAIN ), 500 );
				$removed = $removed || $audit_redaction > 0;

				$owned = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . RSV_Helpers::table( 'reels' ) . ' WHERE owner_id=%d', $user_id ) );
				if ( $owned ) {
					$retained = true;
					$messages[] = __( 'Published or governed Reel records were retained for editorial, rights and accountability review; request correction or withdrawal through the content workflow.', RSV_TEXT_DOMAIN );
				}
				if ( ! RSV_Helpers::audit( 'privacy', 0, 'erase', '', 'complete', 'Private history and identifiers erased or de-identified', array( 'subject_ref' => RSV_Helpers::opaque_user_ref( $user_id ) ), 0 ) ) {
					return RSV_Helpers::error( 'rsv_privacy_evidence_failed', __( 'Privacy erasure could not be completed with audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return array( 'removed' => $removed, 'retained' => $retained, 'messages' => $messages );
			}
		);
		if ( is_wp_error( $result ) ) {
			return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array( $result->get_error_message() ), 'done' => false );
		}
		return array( 'items_removed' => (bool) $result['removed'], 'items_retained' => (bool) $result['retained'], 'messages' => $result['messages'], 'done' => true );
	}}
