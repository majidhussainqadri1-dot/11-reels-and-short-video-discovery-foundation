<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Diagnostics {
	private static function required_tables() {
		$tables = array( 'reels', 'progress', 'view_sessions', 'reports', 'impressions', 'audit', 'outbox', 'idempotency', 'rate_limits' );
		if ( class_exists( 'RSV_Top20' ) ) $tables = array_merge( $tables, RSV_Top20::required_tables() );
		return array_values( array_unique( $tables ) );
	}

	public static function summary() {
		global $wpdb;
		$missing = array();
		foreach ( self::required_tables() as $table ) {
			$name = RSV_Helpers::table( $table );
			if ( $name !== $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $name ) ) ) ) $missing[] = $table;
		}
		$schema_ok = empty( $missing );
		$reels = RSV_Helpers::table( 'reels' );
		$reports = RSV_Helpers::table( 'reports' );
		$outbox = RSV_Helpers::table( 'outbox' );
		$invalid_states = 0;
		$duplicate_videos = 0;
		$published_without_context = 0;
		if ( $schema_ok ) {
			$valid_states = "'" . implode( "','", array_map( 'esc_sql', RSV_Contracts::REEL_STATES ) ) . "'";
			$invalid_states = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reels WHERE status NOT IN ($valid_states)" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$duplicate_videos = (int) $wpdb->get_var( "SELECT COUNT(*) FROM (SELECT video_id FROM $reels GROUP BY video_id HAVING COUNT(*)>1) duplicate_rows" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$context = RSV_Helpers::table( 'reel_context' );
			$published_without_context = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reels r LEFT JOIN $context c ON c.reel_id=r.id WHERE r.status='published' AND (c.reel_id IS NULL OR c.source_title='' OR c.safety_summary='')" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$safe_mode_reasons = array();
		if ( ! RSV_File10::ready() ) $safe_mode_reasons[] = 'file10_contract_unavailable';
		if ( ! RSV_File10::contract_compatible() ) $safe_mode_reasons[] = 'file10_contract_incompatible';
		if ( ! $schema_ok ) $safe_mode_reasons[] = 'schema_incomplete';
		if ( $invalid_states ) $safe_mode_reasons[] = 'invalid_reel_states';
		if ( $duplicate_videos ) $safe_mode_reasons[] = 'duplicate_video_ownership';
		if ( $published_without_context ) $safe_mode_reasons[] = 'published_reel_missing_source_safety_context';

		$top20 = array(
			'context_required' => true,
			'published_without_context' => $published_without_context,
			'stories_active' => 0,
			'highlights_active' => 0,
			'responses_published' => 0,
			'top20_requirements' => count( RSV_Contracts::TOP20_REQUIREMENTS ),
		);
		if ( $schema_ok ) {
			$stories = RSV_Helpers::table( 'stories' );
			$highlights = RSV_Helpers::table( 'highlights' );
			$responses = RSV_Helpers::table( 'responses' );
			$now = RSV_Helpers::now();
			$top20['stories_active'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $stories WHERE status='published' AND expires_at>%s", $now ) );
			$top20['highlights_active'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $highlights WHERE status='published'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$top20['responses_published'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $responses WHERE status='published'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		return array(
			'version' => RSV_VERSION,
			'schema_version' => (string) get_option( 'rsv_schema_version', '' ),
			'contract_version' => RSV_CONTRACT_VERSION,
			'file10_ready' => RSV_File10::ready(),
			'file10_compatible' => RSV_File10::contract_compatible(),
			'file00_contract' => function_exists( 'smc_membership_assertions' ),
			'schema_ok' => $schema_ok,
			'missing_tables' => $missing,
			'invalid_states' => $invalid_states,
			'duplicate_videos' => $duplicate_videos,
			'published_reels' => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reels WHERE status='published'" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'open_reports' => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reports WHERE status IN ('submitted','triaged','appealed')" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'pending_events' => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $outbox WHERE status IN ('pending','retry','processing')" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'dead_events' => $schema_ok ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $outbox WHERE status='dead'" ) : 0, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'requirements' => count( RSV_Contracts::REQUIREMENTS ),
			'top20' => $top20,
			'migration_checkpoint' => (array) get_option( RSV_Migration::CHECKPOINT_OPTION, array() ),
			'safe_mode' => ! empty( $safe_mode_reasons ),
			'safe_mode_reasons' => $safe_mode_reasons,
			'checked_at' => RSV_Helpers::now(),
		);
	}

	public static function repair() {
		if ( ! current_user_can( 'manage_options' ) && ! RSV_Security::can( RSV_Contracts::CAP_MANAGE ) ) return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot repair File 11.', RSV_TEXT_DOMAIN ), 403 );
		$before = self::summary();
		RSV_DB::install();
		if ( class_exists( 'RSV_Top20' ) ) RSV_Top20::install();
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
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT event_id,event_name,status,attempts,available_at,last_error,created_at,updated_at FROM $table ORDER BY id DESC LIMIT %d", $limit ), ARRAY_A );
	}
}
