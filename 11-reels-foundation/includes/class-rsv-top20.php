<?php
defined( 'ABSPATH' ) || exit;

/**
 * Continuous Value / Top-20 Superset implementation owned by File 11.
 * File 10 remains the sole raw-media, player, caption-object, rights and secure-delivery owner.
 */
final class RSV_Top20 {
	const SCHEMA_VERSION = '1.0.0';
	const MAX_STORY_HOURS = 24;
	const INSIGHT_MINIMUM = 5;

	use RSV_Top20_Context_Trait;
	use RSV_Top20_Stories_Trait;
	use RSV_Top20_Responses_Trait;
	use RSV_Top20_Experience_Trait;
	use RSV_Top20_Privacy_Integration_Trait;

	public static function required_tables() {
		return array( 'reel_context', 'stories', 'highlights', 'highlight_items', 'responses', 'preferences', 'value_signals' );
	}

	public function register() {
		add_action( 'init', array( __CLASS__, 'maybe_install' ), 5 );
		add_action( 'init', array( __CLASS__, 'rewrites' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'route_request' ), 2 );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'rest_pre_dispatch' ), 9, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'rest_post_dispatch' ), 20, 3 );
		add_filter( 'rsv_authorize', array( __CLASS__, 'authorization_gate' ), 20, 6 );
		add_filter( 'rsv_can_view_reel', array( __CLASS__, 'can_view_reel_filter' ), 20, 3 );
		add_filter( 'rsv_comments_url', array( __CLASS__, 'filter_youth_social_url' ), 90 );
		add_filter( 'rsv_follow_url', array( __CLASS__, 'filter_youth_social_url' ), 90 );
		add_filter( 'rsv_file10_download_url', array( __CLASS__, 'filter_youth_download' ), 90 );
		add_filter( 'do_shortcode_tag', array( __CLASS__, 'enhance_shortcode' ), 20, 4 );
		add_action( 'get_footer', array( __CLASS__, 'single_context_before_footer' ), 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ), 30 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_action( 'rsv_hourly', array( __CLASS__, 'maintenance' ), 20 );
		add_action( 'admin_post_rsv_top20_story', array( __CLASS__, 'admin_story' ) );
		add_action( 'admin_post_rsv_top20_response', array( __CLASS__, 'admin_response' ) );
		add_action( 'admin_post_rsv_top20_highlight', array( __CLASS__, 'admin_highlight' ) );
		add_action( 'admin_post_rsv_top20_preferences', array( __CLASS__, 'admin_preferences' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'privacy_exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'privacy_erasers' ) );
		add_filter( 'rsv_provider_manifest', array( __CLASS__, 'provider_manifest' ), 30 );
		add_filter( 'rsv_public_search_documents', array( __CLASS__, 'search_documents' ), 30, 3 );
		add_filter( 'rsv_public_author_items', array( __CLASS__, 'author_items' ), 30, 3 );
		add_filter( 'rsv_story_cards', array( __CLASS__, 'story_cards' ), 10, 2 );
		add_filter( 'rsv_highlight_collections', array( __CLASS__, 'highlight_collections' ), 10, 3 );
		add_filter( 'sabri_composer_type_contracts', array( __CLASS__, 'composer_contracts' ) );
	}

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$c = $wpdb->get_charset_collate();
		$sql = array();
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'reel_context' ) . " (
			reel_id bigint unsigned NOT NULL,
			source_title varchar(255) NOT NULL DEFAULT '',
			source_url varchar(500) NOT NULL DEFAULT '',
			transcript_url varchar(500) NOT NULL DEFAULT '',
			safety_summary text NOT NULL,
			allow_response tinyint unsigned NOT NULL DEFAULT 1,
			patient_reuse_policy varchar(40) NOT NULL DEFAULT 'explicit-consent-required',
			version bigint unsigned NOT NULL DEFAULT 1,
			updated_at datetime NOT NULL,
			PRIMARY KEY (reel_id), KEY updated_at (updated_at)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'stories' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			public_id varchar(80) NOT NULL,
			reel_id bigint unsigned NOT NULL,
			owner_id bigint unsigned NOT NULL,
			story_type varchar(40) NOT NULL,
			title varchar(255) NOT NULL,
			body text NOT NULL,
			destination_url varchar(500) NOT NULL DEFAULT '',
			status varchar(30) NOT NULL DEFAULT 'draft',
			visibility varchar(20) NOT NULL DEFAULT 'public',
			duration_hours smallint unsigned NOT NULL DEFAULT 24,
			expires_at datetime NOT NULL,
			highlight_eligible tinyint unsigned NOT NULL DEFAULT 1,
			consent_snapshot char(64) NOT NULL,
			version bigint unsigned NOT NULL DEFAULT 1,
			published_at datetime NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id), UNIQUE KEY public_id (public_id),
			KEY public_feed (status,visibility,expires_at,published_at,id),
			KEY owner_status (owner_id,status,updated_at), KEY reel_status (reel_id,status,updated_at)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'highlights' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			public_id varchar(80) NOT NULL,
			owner_id bigint unsigned NOT NULL,
			title varchar(160) NOT NULL,
			slug varchar(180) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'published',
			version bigint unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id), UNIQUE KEY public_id (public_id), UNIQUE KEY owner_slug (owner_id,slug), KEY owner_status (owner_id,status,updated_at)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'highlight_items' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			highlight_id bigint unsigned NOT NULL,
			story_id bigint unsigned NOT NULL,
			sort_order int unsigned NOT NULL DEFAULT 0,
			consent_rechecked_at datetime NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id), UNIQUE KEY highlight_story (highlight_id,story_id), KEY highlight_order (highlight_id,sort_order,id), KEY story_id (story_id)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'responses' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			public_id varchar(80) NOT NULL,
			source_reel_id bigint unsigned NOT NULL,
			response_reel_id bigint unsigned NOT NULL,
			owner_id bigint unsigned NOT NULL,
			attribution_text varchar(500) NOT NULL,
			patient_reuse_consent tinyint unsigned NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'review',
			version bigint unsigned NOT NULL DEFAULT 1,
			published_at datetime NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id), UNIQUE KEY public_id (public_id), UNIQUE KEY response_reel_id (response_reel_id), KEY source_status (source_reel_id,status,published_at,id), KEY owner_status (owner_id,status,updated_at)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'preferences' ) . " (
			user_id bigint unsigned NOT NULL,
			youth_safe tinyint unsigned NOT NULL DEFAULT 0,
			history_paused tinyint unsigned NOT NULL DEFAULT 0,
			session_limit_minutes smallint unsigned NOT NULL DEFAULT 15,
			natural_stop_every smallint unsigned NOT NULL DEFAULT 10,
			late_night_reminder tinyint unsigned NOT NULL DEFAULT 1,
			version bigint unsigned NOT NULL DEFAULT 1,
			updated_at datetime NOT NULL,
			PRIMARY KEY (user_id), KEY updated_at (updated_at)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'value_signals' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			reel_id bigint unsigned NOT NULL,
			day_key date NOT NULL,
			source_opens int unsigned NOT NULL DEFAULT 0,
			shares int unsigned NOT NULL DEFAULT 0,
			natural_stops int unsigned NOT NULL DEFAULT 0,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id), UNIQUE KEY reel_day (reel_id,day_key), KEY updated_at (updated_at)
		) $c;";
		foreach ( $sql as $statement ) dbDelta( $statement );
		update_option( 'rsv_top20_schema_version', self::SCHEMA_VERSION, false );
	}

	public static function maybe_install() {
		if ( (string) get_option( 'rsv_top20_schema_version' ) !== self::SCHEMA_VERSION ) {
			self::install();
			update_option( 'rsv_top20_rewrite_flush', 1, false );
		}
		if ( get_option( 'rsv_top20_rewrite_flush' ) ) {
			delete_option( 'rsv_top20_rewrite_flush' );
			flush_rewrite_rules( false );
		}
	}

	public static function query_vars( $vars ) {
		foreach ( array( 'rsv_stories', 'rsv_story', 'rsv_highlights', 'rsv_preferences', 'rsv_value_insights', 'rsv_respond' ) as $var ) $vars[] = $var;
		return $vars;
	}

	public static function rewrites() {
		add_rewrite_rule( '^reels/stories/?$', 'index.php?rsv_stories=1', 'top' );
		add_rewrite_rule( '^story/(story_[a-f0-9-]{36})/?$', 'index.php?rsv_story=$matches[1]', 'top' );
		add_rewrite_rule( '^reels/highlights/([0-9]+)/?$', 'index.php?rsv_highlights=$matches[1]', 'top' );
		add_rewrite_rule( '^account/reels/preferences/?$', 'index.php?rsv_preferences=1', 'top' );
		add_rewrite_rule( '^account/reels/value-insights/?$', 'index.php?rsv_value_insights=1', 'top' );
		add_rewrite_rule( '^reels/respond/?$', 'index.php?rsv_respond=1', 'top' );
	}

	public static function register_routes() {
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/context', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_context' ), 'permission_callback' => '__return_true' ),
			array( 'methods' => WP_REST_Server::EDITABLE, 'callback' => array( __CLASS__, 'rest_context_update' ), 'permission_callback' => array( __CLASS__, 'can_submit' ) ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/stories', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_stories' ), 'permission_callback' => '__return_true' ),
			array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( __CLASS__, 'rest_story_create' ), 'permission_callback' => array( __CLASS__, 'can_submit' ) ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/stories/(?P<id>story_[a-f0-9-]{36})', array( array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_story' ), 'permission_callback' => '__return_true' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/stories/(?P<id>story_[a-f0-9-]{36})/publish', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( __CLASS__, 'rest_story_publish' ), 'permission_callback' => array( __CLASS__, 'can_publish' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/stories/(?P<id>story_[a-f0-9-]{36})/highlight', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( __CLASS__, 'rest_highlight_add' ), 'permission_callback' => array( __CLASS__, 'can_submit' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/highlights/(?P<owner_id>[0-9]+)', array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_highlights' ), 'permission_callback' => '__return_true' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/responses', array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_responses' ), 'permission_callback' => '__return_true' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/response', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( __CLASS__, 'rest_response_create' ), 'permission_callback' => array( __CLASS__, 'can_submit' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/responses/(?P<id>response_[a-f0-9-]{36})/publish', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( __CLASS__, 'rest_response_publish' ), 'permission_callback' => array( __CLASS__, 'can_publish' ) ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/preferences', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_preferences' ), 'permission_callback' => 'is_user_logged_in' ),
			array( 'methods' => WP_REST_Server::EDITABLE, 'callback' => array( __CLASS__, 'rest_preferences_update' ), 'permission_callback' => 'is_user_logged_in' ),
		) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/reels/(?P<id>reel_[a-f0-9-]{36})/value-signal', array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( __CLASS__, 'rest_signal' ), 'permission_callback' => '__return_true' ) );
		register_rest_route( RSV_Contracts::API_NAMESPACE, '/value-insights', array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_value_insights' ), 'permission_callback' => array( __CLASS__, 'can_insights' ) ) );
	}

	public static function can_submit() { return RSV_Security::can( RSV_Contracts::CAP_SUBMIT ); }
	public static function can_publish() { return RSV_Security::can( RSV_Contracts::CAP_PUBLISH ); }
	public static function can_insights() { return RSV_Security::can( RSV_Contracts::CAP_INSIGHTS ) || RSV_Security::can( RSV_Contracts::CAP_SUBMIT ); }
}
