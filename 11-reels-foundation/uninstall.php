<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
if ( ! defined( 'RSV_ALLOW_DESTRUCTIVE_PURGE' ) || true !== RSV_ALLOW_DESTRUCTIVE_PURGE ) return;
if ( ! current_user_can( 'delete_plugins' ) ) return;
global $wpdb;
foreach ( array( 'reels','progress','reports','report_events','impressions','audit','outbox','inbox','legal_holds','idempotency' ) as $name ) {
	$table = $wpdb->prefix . 'rsv_' . $name;
	$wpdb->query( "DROP TABLE IF EXISTS `$table`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
foreach ( array( 'rsv_schema_version','rsv_pages' ) as $option ) delete_option( $option );
foreach ( array( 'administrator' ) as $role_name ) { $role=get_role($role_name); if($role) foreach(array('rsv_submit_reel','rsv_publish_reel','rsv_moderate_reel','rsv_manage_reels','rsv_view_reel_insights') as $cap)$role->remove_cap($cap); }
