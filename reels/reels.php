<?php
/** Plugin Name: Reels and Short Video Discovery Foundation
 * Description: American English vertical educational Reels with a mandatory duration of 60 to 600 seconds.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Dr. Allama Majid Hussain Sabri
 * License: GPL-2.0-or-later
 */
defined('ABSPATH')||exit;define('SRL_VERSION','0.1.0');define('SRL_FILE',__FILE__);define('SRL_DIR',plugin_dir_path(__FILE__));define('SRL_URL',plugin_dir_url(__FILE__));require_once SRL_DIR.'includes/class-srl-plugin.php';register_activation_hook(SRL_FILE,array('SRL_Plugin','activate'));register_deactivation_hook(SRL_FILE,array('SRL_Plugin','deactivate'));add_action('plugins_loaded',function(){if(class_exists('SVW_Helpers')){(new SRL_Plugin())->run();}else{add_action('admin_notices',function(){echo'<div class="notice notice-error"><p><strong>Reels:</strong> Activate File 10 first.</p></div>';});}},60);

