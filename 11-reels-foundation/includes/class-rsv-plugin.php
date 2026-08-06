<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Plugin {
	private static $instance;
	public static function instance(){if(!self::$instance)self::$instance=new self();return self::$instance;}
	public function register(){
		(new RSV_REST())->register();(new RSV_Frontend())->register();(new RSV_Admin())->register();(new RSV_Privacy())->register();(new RSV_Jobs())->register();
		add_action('admin_init',array($this,'maybe_upgrade'));add_action('init',array($this,'register_capabilities'),5);add_action('init',array('RSV_Jobs','ensure'),20);
		add_filter('plugin_action_links_'.plugin_basename(RSV_FILE),array($this,'links'));
	}
	public function maybe_upgrade(){RSV_Migration::upgrade();}
	public function register_capabilities(){
		$admin=get_role('administrator');if($admin)foreach(array(RSV_Contracts::CAP_SUBMIT,RSV_Contracts::CAP_PUBLISH,RSV_Contracts::CAP_MODERATE,RSV_Contracts::CAP_MANAGE,RSV_Contracts::CAP_INSIGHTS) as $cap)$admin->add_cap($cap);
		do_action('rsv_register_capabilities',RSV_CONTRACT_VERSION);
	}
	public function links($links){array_unshift($links,'<a href="'.esc_url(admin_url('admin.php?page=rsv-diagnostics')).'">'.esc_html__('Diagnostics',RSV_TEXT_DOMAIN).'</a>');return $links;}
	public static function activate(){
		if(version_compare(PHP_VERSION,'8.1','<'))wp_die(esc_html__('File 11 requires PHP 8.1 or newer.',RSV_TEXT_DOMAIN));
		RSV_DB::install();RSV_Migration::ensure_pages();self::instance()->register_capabilities();RSV_Jobs::ensure();flush_rewrite_rules();RSV_Helpers::audit('system',0,'activate','',RSV_VERSION,'Activation completed; File 10 readiness is checked at use time');
	}
	public static function deactivate(){wp_clear_scheduled_hook(RSV_Jobs::CRON);flush_rewrite_rules();}
}
