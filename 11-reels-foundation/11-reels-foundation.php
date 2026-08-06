<?php
/**
 * Plugin Name: Reels and Short Video Discovery
 * Description: Canonical educational Reels domain for the Sabri Social Homeopathy Platform. File 10 owns media; File 11 owns Reel metadata, discovery, progress and moderation.
 * Version: 1.0.0-rc2
 * Requires at least: 6.7
 * Requires PHP: 8.1
 * Author: Dr. Allamah Majid Hussain Sabri Muhaddith Mursheed
 * License: GPL-2.0-or-later
 * Text Domain: reels-short-video-discovery
 * Domain Path: /languages
 */
defined( 'ABSPATH' ) || exit;

define( 'RSV_VERSION', '1.0.0-rc2' );
define( 'RSV_SCHEMA_VERSION', '1.1.0' );
define( 'RSV_CONTRACT_VERSION', 2 );
define( 'RSV_FILE', __FILE__ );
define( 'RSV_DIR', plugin_dir_path( __FILE__ ) );
define( 'RSV_URL', plugin_dir_url( __FILE__ ) );
define( 'RSV_TEXT_DOMAIN', 'reels-short-video-discovery' );

$files = array(
	'class-rsv-contracts.php',
	'class-rsv-helpers.php',
	'class-rsv-db.php',
	'class-rsv-repository.php',
	'class-rsv-state-machine.php',
	'class-rsv-security.php',
	'class-rsv-access.php',
	'class-rsv-ranking.php',
	'class-rsv-file10.php',
	'class-rsv-reels.php',
	'class-rsv-rest.php',
	'class-rsv-frontend.php',
	'class-rsv-admin.php',
	'class-rsv-privacy.php',
	'class-rsv-jobs.php',
	'class-rsv-migration.php',
	'class-rsv-diagnostics.php',
	'class-rsv-plugin.php',
);
foreach ( $files as $file ) {
	require_once RSV_DIR . 'includes/' . $file;
}

register_activation_hook( RSV_FILE, array( 'RSV_Plugin', 'activate' ) );
register_deactivation_hook( RSV_FILE, array( 'RSV_Plugin', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( RSV_TEXT_DOMAIN, false, dirname( plugin_basename( RSV_FILE ) ) . '/languages' );
		RSV_Plugin::instance()->register();
	},
	70
);
