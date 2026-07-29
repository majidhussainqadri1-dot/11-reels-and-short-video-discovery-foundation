<?php
/**
 * Plugin Name: Reels and Short Video Discovery Foundation
 * Description: American English vertical educational Reels with authoritative 60–600-second duration validation, moderated publishing, interactions, private history, and accessible discovery.
 * Version: 0.2.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Dr. Allama Majid Hussain Sabri
 * License: GPL-2.0-or-later
 * Text Domain: sabri-reels
 */
defined('ABSPATH') || exit;

define('SRL_VERSION', '0.2.0');
define('SRL_DB_VERSION', '2');
define('SRL_FILE', __FILE__);
define('SRL_DIR', plugin_dir_path(__FILE__));
define('SRL_URL', plugin_dir_url(__FILE__));

require_once SRL_DIR . 'includes/class-srl-plugin.php';

register_activation_hook(SRL_FILE, array('SRL_Plugin', 'activate'));
register_deactivation_hook(SRL_FILE, array('SRL_Plugin', 'deactivate'));

add_action('plugins_loaded', function () {
    if (SRL_Plugin::dependency_ready()) {
        (new SRL_Plugin())->run();
        return;
    }

    add_action('admin_notices', function () {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-error"><p><strong>Reels:</strong> File 10 (Video Wall) must be active and compatible. Required contracts: <code>SVW_Helpers</code>, <code>SVW_Interactions</code>, <code>SVW_Helpers::TYPE</code>, and <code>SVW_Helpers::TAX</code>.</p></div>';
    });
}, 60);
