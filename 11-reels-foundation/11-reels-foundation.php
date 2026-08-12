<?php
/**
 * Plugin Name: Reels and Short Video Discovery
 * Description: Canonical educational Reels, Stories/Status, Highlights and attributed Response/Remix domain for the Sabri Social Homeopathy Platform. File 10 remains the sole raw-media owner.
 * Version: 1.2.0-rc1
 * Requires at least: 6.7
 * Requires PHP: 8.1
 * Author: Dr. Allamah Majid Hussain Sabri
 * License: GPL-2.0-or-later
 * Text Domain: reels-short-video-discovery
 */
defined( 'ABSPATH' ) || exit;

define( 'RSV_VERSION', '1.2.0-rc1' );
define( 'RSV_SCHEMA_VERSION', '1.2.0' );
define( 'RSV_CONTRACT_VERSION', 7 );
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
	'class-rsv-file10.php',
	'class-rsv-reels.php',
	'class-rsv-rest.php',
	'class-rsv-frontend.php',
	'class-rsv-admin.php',
	'class-rsv-privacy.php',
	'class-rsv-jobs.php',
	'class-rsv-migration.php',
	'class-rsv-diagnostics.php',
	'class-rsv-integrations.php',
	'class-rsv-current-plan.php',
	'trait-rsv-future30-storage.php',
	'trait-rsv-future30-feature-write.php',
	'trait-rsv-future30-user-features.php',
	'trait-rsv-future30-experience.php',
	'class-rsv-future30.php',
	'class-rsv-future30-public-safety.php',
	'class-rsv-future30-write-integrity.php',
	'class-rsv-future30-private-state-integrity.php',
	'class-rsv-future30-ai-context-safety.php',
	'class-rsv-future30-analytics-safety.php',
	'class-rsv-future30-accessibility-runtime.php',
	'trait-rsv-top20-context.php',
	'trait-rsv-top20-stories.php',
	'trait-rsv-top20-responses.php',
	'trait-rsv-top20-experience.php',
	'trait-rsv-top20-privacy-integration.php',
	'class-rsv-top20.php',
	'class-rsv-plugin.php',
);
foreach ( $files as $file ) require_once RSV_DIR . 'includes/' . $file;

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
