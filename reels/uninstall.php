<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

// Safe default: retain all data unless an administrator explicitly opted in.
if (!get_option('srl_remove_data_on_uninstall', false)) {
    return;
}

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}srl_history"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange

$map = (array) get_option('srl_page_map', array());
foreach ($map as $page_id) {
    $page_id = absint($page_id);
    if ($page_id && get_post_meta($page_id, '_srl_managed', true)) {
        wp_delete_post($page_id, true);
    }
}

delete_option('srl_page_map');
delete_option('srl_version');
delete_option('srl_db_version');
delete_option('srl_remove_data_on_uninstall');

$administrator = get_role('administrator');
if ($administrator) {
    $administrator->remove_cap('manage_reels');
}
