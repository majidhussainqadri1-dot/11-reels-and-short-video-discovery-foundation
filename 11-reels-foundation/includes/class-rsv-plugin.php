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
		RSV_Top20::install();
		self::roles();
		$pages = self::pages();
		if ( is_wp_error( $pages ) ) {
			deactivate_plugins( plugin_basename( RSV_FILE ) );
			wp_die( esc_html( $pages->get_error_message() ), esc_html__( 'File 11 activation failed', RSV_TEXT_DOMAIN ), array( 'response' => 500, 'back_link' => true ) );
		}
		add_filter( 'cron_schedules', array( 'RSV_Jobs', 'intervals' ) );
		RSV_Jobs::schedule();
		update_option( 'rsv_version', RSV_VERSION, false );
		update_option( 'rsv_contract_version', RSV_CONTRACT_VERSION, false );
		update_option( 'rsv_governing_plan_revision', RSV_Current_Plan::REVISION, false );
		set_transient( 'rsv_activation_notice', 1, 120 );
		flush_rewrite_rules();
	}

	public static function deactivate() {
		RSV_Jobs::unschedule();
		flush_rewrite_rules();
	}

	private static function roles() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( array( RSV_Contracts::CAP_SUBMIT, RSV_Contracts::CAP_PUBLISH, RSV_Contracts::CAP_MODERATE, RSV_Contracts::CAP_MANAGE, RSV_Contracts::CAP_INSIGHTS ) as $cap ) $admin->add_cap( $cap );
		}
	}

	private static function pages() {
		$map = (array) get_option( 'rsv_page_map', array() );
		$feed = self::ensure_page( $map['feed'] ?? 0, 'Reels', 'reels', '[rsv_reels]', 0 );
		$map['feed'] = $feed;
		if ( ! $feed ) return RSV_Helpers::error( 'rsv_feed_page_failed', __( 'The managed Reels page could not be created or repaired.', RSV_TEXT_DOMAIN ), 500 );
		$map['create'] = self::ensure_page( $map['create'] ?? 0, 'Create Reel', 'create', '[rsv_create]', $feed );
		if ( ! $map['create'] ) return RSV_Helpers::error( 'rsv_create_page_failed', __( 'The managed Create Reel page could not be created or repaired.', RSV_TEXT_DOMAIN ), 500 );
		$map['history_url'] = home_url( '/account/reels/history/' );
		$map['insights_url'] = home_url( '/account/reels/insights/' );
		$map['stories_url'] = home_url( '/reels/stories/' );
		$map['preferences_url'] = home_url( '/account/reels/preferences/' );
		$map['value_insights_url'] = home_url( '/account/reels/value-insights/' );
		update_option( 'rsv_page_map', $map, false );
		$routes = array(
			'feed' => home_url( '/reels/' ), 'create' => home_url( '/reels/create/' ),
			'history' => $map['history_url'], 'insights' => $map['insights_url'],
			'stories' => $map['stories_url'], 'preferences' => $map['preferences_url'],
			'value_insights' => $map['value_insights_url'],
		);
		do_action( 'rsv_routes_registered', $routes, RSV_CONTRACT_VERSION );
		do_action( 'sabri_platform_routes_registered', 'file11', $routes, RSV_CONTRACT_VERSION );
		return $map;
	}

	private static function ensure_page( $id, $title, $slug, $shortcode, $parent_id = 0 ) {
		$parent_id = absint( $parent_id );
		$path = $parent_id ? trim( get_page_uri( $parent_id ), '/' ) . '/' . $slug : $slug;
		$page = $id ? get_post( absint( $id ) ) : get_page_by_path( $path );
		if ( $page instanceof WP_Post && get_post_meta( $page->ID, '_rsv_managed', true ) ) {
			$result = wp_update_post( array( 'ID' => $page->ID, 'post_title' => $title, 'post_name' => $slug, 'post_parent' => $parent_id, 'post_content' => $shortcode, 'post_status' => 'publish' ), true );
			return is_wp_error( $result ) ? 0 : absint( $page->ID );
		}
		if ( $page instanceof WP_Post ) $slug .= '-sabri';
		$new_id = wp_insert_post( array( 'post_title' => $title, 'post_name' => $slug, 'post_parent' => $parent_id, 'post_content' => $shortcode, 'post_status' => 'publish', 'post_type' => 'page' ), true );
		if ( is_wp_error( $new_id ) ) return 0;
		update_post_meta( $new_id, '_rsv_managed', 1 );
		return absint( $new_id );
	}

	public function register() {
		if ( (string) get_option( 'rsv_schema_version' ) !== RSV_SCHEMA_VERSION ) RSV_DB::install();
		if ( (string) get_option( 'rsv_top20_schema_version' ) !== RSV_Top20::SCHEMA_VERSION ) RSV_Top20::install();
		if ( (string) get_option( 'rsv_version' ) !== RSV_VERSION || (string) get_option( 'rsv_governing_plan_revision' ) !== RSV_Current_Plan::REVISION ) {
			self::roles();
			$pages = self::pages();
			if ( ! is_wp_error( $pages ) ) {
				update_option( 'rsv_version', RSV_VERSION, false );
				update_option( 'rsv_contract_version', RSV_CONTRACT_VERSION, false );
				update_option( 'rsv_governing_plan_revision', RSV_Current_Plan::REVISION, false );
			} else {
				set_transient( 'rsv_page_repair_error', $pages->get_error_message(), 300 );
			}
		}
		add_filter( 'cron_schedules', array( 'RSV_Jobs', 'intervals' ) );
		add_action( 'rest_api_init', array( new RSV_REST(), 'register' ) );
		( new RSV_Frontend() )->register();
		( new RSV_Admin() )->register();
		( new RSV_Privacy() )->register();
		( new RSV_Jobs() )->register();
		( new RSV_Integrations() )->register();
		( new RSV_Top20() )->register();
		( new RSV_Current_Plan() )->register();
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function notices() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( get_transient( 'rsv_activation_notice' ) ) {
			delete_transient( 'rsv_activation_notice' );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'File 11 Reels activated. Complete staging acceptance before production use.', RSV_TEXT_DOMAIN ) . '</p></div>';
		}
		if ( get_transient( 'rsv_page_repair_error' ) ) {
			$message = (string) get_transient( 'rsv_page_repair_error' );
			delete_transient( 'rsv_page_repair_error' );
			echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
		}
		$health = RSV_Diagnostics::summary();
		if ( ! empty( $health['safe_mode'] ) ) echo '<div class="notice notice-error"><p>' . esc_html__( 'File 11 is in Safe Mode. Open Reels Diagnostics for the exact dependency or data-integrity reason.', RSV_TEXT_DOMAIN ) . '</p></div>';
	}
}
