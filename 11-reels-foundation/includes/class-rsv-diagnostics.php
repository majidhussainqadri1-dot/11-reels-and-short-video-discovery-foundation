<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Diagnostics {
	private static function required_tables() {
		return array( 'reels', 'progress', 'view_sessions', 'reports', 'impressions', 'audit', 'outbox', 'idempotency', 'rate_limits' );
	}

	public static function summary() {
		global $wpdb;
		$missing = array();
		foreach ( self::required_tables() as $table ) {
			$name = RSV_Helpers::table( $table );
			if ( $name !== $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $name ) ) ) ) {
				$missing[] = $table;
			}
		}
		$schema_ok = empty( $missing );
		$reels     = RSV_Helpers::table( 'reels' );
		$reports   = RSV_Helpers::table( 'reports' );
		$outbox    = RSV_Helpers::table( 'outbox' );
		$invalid_states = 0;
		$duplicate_videos = 0;
		if ( $schema_ok ) {
			$valid_states = "'" . implode( "','", array_map( 'esc_sql', RSV_Contracts::REEL_STATES ) ) . "'";
			$invalid_states = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reels WHERE status NOT IN ($valid_states)" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$duplicate_videos = (int) $wpdb->get_var( "SELECT COUNT(*) FROM (SELECT video_id FROM $reels GROUP BY video_id HAVING COUNT(*)>1) duplicate_rows" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$safe_mode_reasons = array();
		if ( ! RSV_File10::ready() ) {
			$safe_mode_reasons[] = 'file10_contract_unavailable';
		}
		if ( ! $schema_ok ) {
			$safe_mode_reasons[] = 'schema_incomplete';
		}
		if ( $invalid_states ) {
			$safe_mode_reasons[] = 'invalid_reel_states';
		}
		if ( $duplicate_videos ) {
			$safe_mode_reasons[] = 'duplicate_video_ownership';
		}
		return array(
			'version'             => RSV_VERSION,
			'schema_version'      => (string) get_option( 'rsv_schema_version', '' ),
			'contract_version'    => RSV_CONTRACT_VERSION,
			'file10_ready'        => RSV_File10::ready(),
			'file00_contract'     => function_exists( 'smc_membership_assertions' ),
			'schema_ok'           => $schema_ok,
			'missing_tables'      => $missing,
			'invalid_states'      => $invalid_states,
			'duplicate_videos'    => $duplicate_videos,
			'published_reels'     => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reels WHERE status='published'" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'open_reports'        => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reports WHERE status IN ('submitted','triaged','appealed')" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'pending_events'      => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $outbox WHERE status IN ('pending','retry','processing')" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'dead_events'         => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $outbox WHERE status='dead'" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'requirements'        => count( RSV_Contracts::REQUIREMENTS ),
			'migration_checkpoint'=> (array) get_option( RSV_Migration::CHECKPOINT_OPTION, array() ),
			'safe_mode'           => ! empty( $safe_mode_reasons ),
			'safe_mode_reasons'   => $safe_mode_reasons,
			'checked_at'          => RSV_Helpers::now(),
		);
	}

	public static function repair() {
		if ( ! current_user_can( 'manage_options' ) && ! RSV_Security::can( RSV_Contracts::CAP_MANAGE ) ) {
			return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot repair File 11.', RSV_TEXT_DOMAIN ), 403 );
		}
		$before = self::summary();
		RSV_DB::install();
		RSV_Jobs::schedule();
		$reconciled = RSV_Migration::reconcile( 200 );
		$after = self::summary();
		RSV_Helpers::audit( 'diagnostics', 0, 'repair', $before['safe_mode'] ? 'degraded' : 'healthy', $after['safe_mode'] ? 'degraded' : 'healthy', '', array( 'reconciled' => $reconciled ) );
		return array( 'before' => $before, 'after' => $after, 'reconciled' => $reconciled );
	}

	public static function queue_snapshot( $limit = 100 ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'outbox' );
		$limit = min( 500, max( 1, absint( $limit ) ) );
		return (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT event_id,event_name,status,attempts,available_at,last_error,created_at,updated_at FROM $table ORDER BY id DESC LIMIT %d", $limit ),
			ARRAY_A
		);
	}
}
