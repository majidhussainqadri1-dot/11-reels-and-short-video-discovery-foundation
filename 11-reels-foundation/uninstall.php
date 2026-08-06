<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Non-destructive by default. A deliberate, separately authorized purge must
// set RSV_ALLOW_DESTRUCTIVE_PURGE to true in wp-config.php.
if ( ! defined( 'RSV_ALLOW_DESTRUCTIVE_PURGE' ) || true !== RSV_ALLOW_DESTRUCTIVE_PURGE ) {
	return;
}
if ( ! current_user_can( 'delete_plugins' ) ) {
	return;
}
global $wpdb;
foreach ( array( 'reels', 'progress', 'view_sessions', 'reports', 'impressions', 'audit', 'outbox', 'idempotency', 'rate_limits' ) as $table ) {
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsv_' . $table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
foreach ( array( 'rsv_schema_version', 'rsv_version', 'rsv_contract_version', 'rsv_page_map', 'rsv_migration_lock', 'rsv_migration_checkpoint', 'rsv_pre_migration_snapshot', 'rsv_legacy_cutover_enabled' ) as $option ) {
	delete_option( $option );
}
