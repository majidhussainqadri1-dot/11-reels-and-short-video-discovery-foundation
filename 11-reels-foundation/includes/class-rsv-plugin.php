<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Plugin {
	private static $instance;

	public static function instance() {
		if ( ! self::$instance ) self::$instance = new self();
		return self::$instance;
	}

	public static function activate() {
		if ( ! RSV_File10::ready() ) {
			deactivate_plugins( plugin_basename( RSV_FILE ) );
			wp_die(
				esc_html__( 'Activate File 10 Video Wall and Live Broadcasting 1.0.0-rc1 or later before activating File 11.', RSV_TEXT_DOMAIN ),
				esc_html__( 'File 10 dependency required', RSV_TEXT_DOMAIN ),
				array( 'response' => 400, 'back_link' => true )
			);
		}
		RSV_DB::install();
		self::roles();
		self::pages();
		add_filter( 'cron_schedules', array( 'RSV_Jobs', 'intervals' ) );
		RSV_Jobs::schedule();
		update_option( 'rsv_version', RSV_VERSION, false );
		update_option( 'rsv_contract_version', RSV_CONTRACT_VERSION, false );
		set_transient( 'rsv_activation_notice', 1, 120 );
		flush_rewrite_rules();
	}

	public static function deactivate() {
		RSV_Jobs::unschedule();
		flush_rewrite_rules();
	}

	private static function roles() {
		$admin = get_role( 'administrator' );
		if ( $admin ) foreach ( array( RSV_Contracts::CAP_SUBMIT, RSV_Contracts::CAP_PUBLISH, RSV_Contracts::CAP_MODERATE, RSV_Contracts::CAP_MANAGE, RSV_Contracts::CAP_INSIGHTS ) as $cap ) $admin->add_cap( $cap );
	}

	private static function pages() {
		$map = (array) get_option( 'rsv_page_map', array() );
		$spec = array(
			'feed' => array( 'Reels', 'reels', '[rsv_reels]' ),
			'create' => array( 'Create Reel', 'reels/create', '[rsv_create]' ),
			'history' => array( 'Reel History', 'account/reels/history', '[rsv_history]' ),
			'insights' => array( 'Reel Insights', 'account/reels/insights', '[rsv_insights]' ),
		);
		foreach ( $spec as $key => $data ) {
			$map[ $key ] = self::ensure_page( $map[ $key ] ?? 0, $data[0], $data[1], $data[2] );
		}
		update_option( 'rsv_page_map', $map, false );
		do_action( 'rsv_routes_registered', $map, RSV_CONTRACT_VERSION );
	}

	private static function ensure_page( $id, $title, $path, $shortcode ) {
		$slug = basename( $path );
		$page = $id ? get_post( absint( $id ) ) : get_page_by_path( $path );
		if ( $page instanceof WP_Post && get_post_meta( $page->ID, '_rsv_managed', true ) ) {
			wp_update_post( array( 'ID' => $page->ID, 'post_title' => $title, 'post_content' => $shortcode, 'post_status' => 'publish' ) );
			return (int) $page->ID;
		}
		if ( $page instanceof WP_Post ) $slug .= '-sabri';
		$new = wp_insert_post( array( 'post_title' => $title, 'post_name' => $slug, 'post_content' => $shortcode, 'post_status' => 'publish', 'post_type' => 'page' ), true );
		if ( is_wp_error( $new ) ) return 0;
		update_post_meta( $new, '_rsv_managed', 1 );
		return (int) $new;
	}

	public function register() {
		if ( (string) get_option( 'rsv_schema_version' ) !== RSV_SCHEMA_VERSION ) RSV_DB::install();
		if ( (string) get_option( 'rsv_version' ) !== RSV_VERSION ) {
			self::pages();
			update_option( 'rsv_version', RSV_VERSION, false );
		}
		add_filter( 'cron_schedules', array( 'RSV_Jobs', 'intervals' ) );
		add_action( 'rest_api_init', array( new RSV_REST(), 'register' ) );
		( new RSV_Frontend() )->register();
		( new RSV_Admin() )->register();
		( new RSV_Privacy() )->register();
		( new RSV_Jobs() )->register();
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function notices() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( get_transient( 'rsv_activation_notice' ) ) {
			delete_transient( 'rsv_activation_notice' );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'File 11 Reels activated. Complete staging acceptance before production use.', RSV_TEXT_DOMAIN ) . '</p></div>';
		}
		if ( ! RSV_File10::ready() ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'File 11 is in Safe Mode because the canonical File 10 contract is unavailable.', RSV_TEXT_DOMAIN ) . '</p></div>';
		}
	}
}
