<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Diagnostics {
	public static function summary() {
		global $wpdb;
		$reels = RSV_Helpers::table( 'reels' );
		$reports = RSV_Helpers::table( 'reports' );
		$outbox = RSV_Helpers::table( 'outbox' );
		$tables = array( 'reels','progress','reports','impressions','audit','outbox','idempotency' );
		$missing = array();
		foreach ( $tables as $table ) {
			$name = RSV_Helpers::table( $table );
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $name ) ) !== $name ) $missing[] = $table;
		}
		return array(
			'version' => RSV_VERSION,
			'schema_version' => get_option( 'rsv_schema_version' ),
			'contract_version' => RSV_CONTRACT_VERSION,
			'file10_ready' => RSV_File10::ready(),
			'schema_ok' => empty( $missing ),
			'missing_tables' => $missing,
			'published_reels' => empty( $missing ) ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reels WHERE status='published'" ) : 0,
			'open_reports' => empty( $missing ) ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reports WHERE status IN ('submitted','triaged','appealed')" ) : 0,
			'dead_events' => empty( $missing ) ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $outbox WHERE status='dead'" ) : 0,
			'requirements' => count( RSV_Contracts::REQUIREMENTS ),
			'safe_mode' => ! RSV_File10::ready() || ! empty( $missing ),
		);
	}
}
