<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Non-destructive by default. A deliberate, separately authorized purge must set
// RSV_ALLOW_DESTRUCTIVE_PURGE to true in wp-config.php.
if ( ! defined( 'RSV_ALLOW_DESTRUCTIVE_PURGE' ) || true !== RSV_ALLOW_DESTRUCTIVE_PURGE ) {
	return;
}
if ( ! current_user_can( 'delete_plugins' ) ) {
	return;
}
global $wpdb;
foreach ( array( 'reels','progress','reports','impressions','audit','outbox','idempotency' ) as $table ) {
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsv_' . $table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
delete_option( 'rsv_schema_version' );
delete_option( 'rsv_version' );
delete_option( 'rsv_contract_version' );
delete_option( 'rsv_page_map' );
