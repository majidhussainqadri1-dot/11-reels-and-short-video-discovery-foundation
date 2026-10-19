<?php
defined( 'ABSPATH' ) || exit;

/**
 * Founder-approved File 11 Future Superset 30 (2026-08-12).
 *
 * File 11 owns Reel-domain orchestration and native Reel metadata only. Raw media,
 * AI execution, global search/ranking and canonical knowledge remain with their
 * approved owners and are consumed through versioned filters/actions.
 */
final class RSV_Future30 {
	const SCHEMA_VERSION = '1.0.0';
	const CONTRACT_VERSION = 1;
	const PLAN_REVISION = '2026-08-12';

	const FEATURE_IDS = array(
		'F11-FUT-001','F11-FUT-002','F11-FUT-003','F11-FUT-004','F11-FUT-005',
		'F11-FUT-006','F11-FUT-007','F11-FUT-008','F11-FUT-009','F11-FUT-010',
		'F11-FUT-011','F11-FUT-012','F11-FUT-013','F11-FUT-014','F11-FUT-015',
		'F11-FUT-016','F11-FUT-017','F11-FUT-018','F11-FUT-019','F11-FUT-020',
		'F11-FUT-021','F11-FUT-022','F11-FUT-023','F11-FUT-024','F11-FUT-025',
		'F11-FUT-026','F11-FUT-027','F11-FUT-028','F11-FUT-029','F11-FUT-030',
	);

	use RSV_Future30_Storage_Trait;
	use RSV_Future30_Feature_Write_Trait;
	use RSV_Future30_User_Features_Trait;
	use RSV_Future30_Experience_Trait;

	public static function definitions() {
		return array(
			'F11-FUT-001' => array( 'slug'=>'series', 'title'=>'Educational Reel Series / Playlists', 'owner'=>'File 11', 'mode'=>'native' ),
			'F11-FUT-002' => array( 'slug'=>'learning-paths', 'title'=>'Structured Learning Paths', 'owner'=>'File 11 orchestration / File 05 learning truth', 'mode'=>'federated' ),
			'F11-FUT-003' => array( 'slug'=>'related-knowledge', 'title'=>'Related Knowledge Button', 'owner'=>'File 11 links / native content owners', 'mode'=>'federated' ),
			'F11-FUT-004' => array( 'slug'=>'timestamp-citations', 'title'=>'Source-at-Time Citation Cards', 'owner'=>'File 11 citation pointers / source owners', 'mode'=>'native' ),
			'F11-FUT-005' => array( 'slug'=>'evidence-layer', 'title'=>'Evidence Layer / Scientific Classification', 'owner'=>'File 11 presentation / File 06 evidence truth', 'mode'=>'federated' ),
			'F11-FUT-006' => array( 'slug'=>'corrections', 'title'=>'Correction & Supersession System', 'owner'=>'File 11', 'mode'=>'native' ),
			'F11-FUT-007' => array( 'slug'=>'versions', 'title'=>'Versioned Reel', 'owner'=>'File 11', 'mode'=>'native' ),
			'F11-FUT-008' => array( 'slug'=>'remix-studio', 'title'=>'Advanced Remix Studio', 'owner'=>'File 11 relation / File 10 media derivative', 'mode'=>'federated' ),
			'F11-FUT-009' => array( 'slug'=>'remix-policy', 'title'=>'Remix Permission Matrix', 'owner'=>'File 11', 'mode'=>'native' ),
			'F11-FUT-010' => array( 'slug'=>'templates', 'title'=>'Reel Templates Library', 'owner'=>'File 11 template metadata / File 10 raw media', 'mode'=>'native' ),
			'F11-FUT-011' => array( 'slug'=>'question-answer', 'title'=>'Question → Reel Answer', 'owner'=>'File 11 answer relation / interaction owner question truth', 'mode'=>'federated' ),
			'F11-FUT-012' => array( 'slug'=>'collaboration', 'title'=>'Collaborative Reel / Co-authoring', 'owner'=>'File 11 / File 00 identity', 'mode'=>'native' ),
			'F11-FUT-013' => array( 'slug'=>'peer-review', 'title'=>'Expert Review Badge', 'owner'=>'File 11 review record / File 00 reviewer identity', 'mode'=>'native' ),
			'F11-FUT-014' => array( 'slug'=>'ten-languages', 'title'=>'10-Language Reel System', 'owner'=>'File 11 linked language metadata / content providers', 'mode'=>'federated' ),
			'F11-FUT-015' => array( 'slug'=>'ai-dubbing', 'title'=>'AI Translation + Optional Dubbing', 'owner'=>'File 11 orchestration / File 10 tracks / File 16 AI', 'mode'=>'federated' ),
			'F11-FUT-016' => array( 'slug'=>'searchable-transcript', 'title'=>'Searchable Transcript', 'owner'=>'File 10 transcript / File 26 index / File 11 consumer', 'mode'=>'federated' ),
			'F11-FUT-017' => array( 'slug'=>'chapters', 'title'=>'Smart Chapters / Key Moments', 'owner'=>'File 11 chapter metadata / File 10 annotations', 'mode'=>'native' ),
			'F11-FUT-018' => array( 'slug'=>'knowledge-card', 'title'=>'Reel Knowledge Card', 'owner'=>'File 11 derived projection', 'mode'=>'native' ),
			'F11-FUT-019' => array( 'slug'=>'micro-quiz', 'title'=>'Micro-Quiz after Reel', 'owner'=>'File 11 assessment metadata / File 05 optional learning projection', 'mode'=>'native' ),
			'F11-FUT-020' => array( 'slug'=>'study-collections', 'title'=>'Save to Study Collection', 'owner'=>'File 11 private user state', 'mode'=>'native-private' ),
			'F11-FUT-021' => array( 'slug'=>'personal-notes', 'title'=>'Reel → Personal Notes', 'owner'=>'File 11 private timestamp note / notes editor bridge', 'mode'=>'native-private' ),
			'F11-FUT-022' => array( 'slug'=>'ask-ai', 'title'=>'Ask AI About This Reel', 'owner'=>'File 16 AI / File 11 grounded context', 'mode'=>'federated' ),
			'F11-FUT-023' => array( 'slug'=>'search-opportunities', 'title'=>'Creator Search Opportunity Intelligence', 'owner'=>'File 26 search intelligence / File 11 consumer', 'mode'=>'federated' ),
			'F11-FUT-024' => array( 'slug'=>'why-this-reel', 'title'=>'Expanded “Why am I seeing this Reel?”', 'owner'=>'File 11 native explanation / File 26 global reason', 'mode'=>'federated' ),
			'F11-FUT-025' => array( 'slug'=>'feed-controls', 'title'=>'Feed Control Center', 'owner'=>'File 11 private preferences / File 26 cross-platform consumer', 'mode'=>'native-private' ),
			'F11-FUT-026' => array( 'slug'=>'diversity-slider', 'title'=>'Serendipity / Knowledge Diversity Slider', 'owner'=>'File 11 preference / File 26 ranking consumer', 'mode'=>'native-private' ),
			'F11-FUT-027' => array( 'slug'=>'creator-research', 'title'=>'Creator Research Dashboard', 'owner'=>'File 11 aggregate Reel metrics / shared analytics', 'mode'=>'derived' ),
			'F11-FUT-028' => array( 'slug'=>'safety-scanner', 'title'=>'Pre-Publish Clinical Safety Scanner', 'owner'=>'File 11 gate / File 24 assurance / File 16 optional classifier', 'mode'=>'federated' ),
			'F11-FUT-029' => array( 'slug'=>'accessibility-plus', 'title'=>'Accessibility Plus Mode', 'owner'=>'File 11 user preference / File 10 accessible media tracks', 'mode'=>'native-private' ),
			'F11-FUT-030' => array( 'slug'=>'knowledge-graph', 'title'=>'Reel Knowledge Graph', 'owner'=>'File 06 knowledge truth / File 26 graph / File 11 edges', 'mode'=>'federated' ),
		);
	}

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$c = $wpdb->get_charset_collate();
		$sql = array();
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'future_objects' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			public_id varchar(80) NOT NULL,
			feature_id varchar(20) NOT NULL,
			object_type varchar(40) NOT NULL,
			reel_id bigint unsigned NOT NULL DEFAULT 0,
			owner_id bigint unsigned NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'active',
			title varchar(255) NOT NULL DEFAULT '',
			payload_json longtext NOT NULL,
			version bigint unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY public_id (public_id),
			KEY feature_reel (feature_id,reel_id,status,id),
			KEY owner_feature (owner_id,feature_id,status,id),
			KEY object_type (object_type,status,id)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'future_edges' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			public_id varchar(80) NOT NULL,
			feature_id varchar(20) NOT NULL,
			source_reel_id bigint unsigned NOT NULL DEFAULT 0,
			edge_type varchar(50) NOT NULL,
			target_owner varchar(30) NOT NULL DEFAULT '',
			target_ref varchar(255) NOT NULL DEFAULT '',
			start_second int unsigned NOT NULL DEFAULT 0,
			end_second int unsigned NOT NULL DEFAULT 0,
			payload_json longtext NOT NULL,
			status varchar(30) NOT NULL DEFAULT 'active',
			version bigint unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY public_id (public_id),
			KEY reel_feature (source_reel_id,feature_id,status,id),
			KEY target_ref (target_owner,target_ref(120),status)
		) $c;";
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'future_user_state' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint unsigned NOT NULL,
			feature_id varchar(20) NOT NULL,
			object_ref varchar(100) NOT NULL DEFAULT '',
			state_key varchar(60) NOT NULL,
			payload_json longtext NOT NULL,
			version bigint unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_feature_state (user_id,feature_id,object_ref,state_key),
			KEY feature_updated (feature_id,updated_at,id),
			KEY user_updated (user_id,updated_at,id)
		) $c;";
		foreach ( $sql as $statement ) dbDelta( $statement );
		update_option( 'rsv_future30_schema_version', self::SCHEMA_VERSION, false );
		update_option( 'rsv_future30_contract_version', self::CONTRACT_VERSION, false );
		update_option( 'rsv_future30_plan_revision', self::PLAN_REVISION, false );
	}

	public function register() {
		if ( (string) get_option( 'rsv_future30_schema_version' ) !== self::SCHEMA_VERSION ) self::install();
		add_action( 'rest_api_init', array( $this, 'routes' ) );
		add_filter( 'rsv_provider_manifest', array( __CLASS__, 'provider_manifest' ), 55 );
		add_filter( 'rsv_current_plan_requirements', array( __CLASS__, 'requirements' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'privacy_exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'privacy_erasers' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
		add_action( 'get_footer', array( __CLASS__, 'single_reel_tools' ), 3 );
		add_shortcode( 'rsv_future30', array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'announce' ), 98 );
	}

	public static function requirements( $manifest ) {
		$manifest = is_array( $manifest ) ? $manifest : array();
		$manifest['future30'] = array(
			'plan_revision' => self::PLAN_REVISION,
			'schema_version' => self::SCHEMA_VERSION,
			'contract_version' => self::CONTRACT_VERSION,
			'requirements' => self::FEATURE_IDS,
			'definitions' => self::definitions(),
			'owner_law' => 'File 11 owns Reel-domain orchestration only; foreign canonical truth is referenced, never duplicated.',
		);
		return $manifest;
	}

	public static function provider_manifest( $manifest ) {
		$payload = array(
			'contract_version'=>self::CONTRACT_VERSION,
			'plan_revision'=>self::PLAN_REVISION,
			'features'=>self::definitions(),
			'capabilities'=>array( 'series','learning-paths','citations','evidence-layer','corrections','version-history','remix-policy','templates','question-answers','collaborators','peer-review','language-links','chapters','knowledge-cards','micro-quizzes','study-collections','timestamp-notes','grounded-ai-context','search-opportunity-consumer','feed-controls','diversity-control','creator-research','clinical-safety-scan','accessibility-plus','knowledge-graph-edges' ),
			'raw_media_owner'=>'File 10', 'ai_owner'=>'File 16', 'knowledge_owner'=>'File 06', 'global_discovery_owner'=>'File 26',
		);
		if ( is_array( $manifest ) && isset( $manifest['provider_id'] ) && 'file11-reels' === ( $manifest['provider_id'] ?? '' ) ) {
			$manifest['future30'] = $payload;
			return $manifest;
		}
		$manifest = is_array( $manifest ) ? $manifest : array();
		$manifest['file11-future30'] = $payload;
		return $manifest;
	}

	public static function announce() {
		do_action( 'rsv_future30_registered', self::PLAN_REVISION, self::FEATURE_IDS, self::CONTRACT_VERSION );
		do_action( 'sabri_platform_capability_registered', 'file11-future30', self::provider_manifest( array() )['file11-future30'] );
	}

	public static function assets() {
		$css = '.rsv-a11y-high-contrast .rsv-reel-card,.rsv-a11y-high-contrast .rsv-future30-tools{outline:2px solid currentColor}.rsv-a11y-keyboard-first .rsv-reel-card :focus-visible,.rsv-a11y-keyboard-first .rsv-future30-tools :focus-visible{outline:3px solid currentColor;outline-offset:3px}.rsv-a11y-reduced-motion .rsv-reels-feed *,.rsv-a11y-reduced-motion .rsv-future30-tools *{scroll-behavior:auto!important;transition-duration:.001ms!important;animation-duration:.001ms!important;animation-iteration-count:1!important}.rsv-captions-large video::cue{font-size:125%}.rsv-captions-extra-large video::cue{font-size:150%}';
		wp_add_inline_style( 'rsv', $css );
		$js = "document.addEventListener('DOMContentLoaded',function(){if(!document.body.classList.contains('rsv-a11y-transcript-only'))return;document.querySelectorAll('video').forEach(function(v){v.pause();v.autoplay=false;});document.querySelectorAll('.rsv-future30-tools').forEach(function(d){d.open=true;});});";
		wp_add_inline_script( 'rsv', $js, 'after' );
	}

	public static function single_reel_tools() {
		$public_id = get_query_var( 'rsv_reel' );
		if ( ! $public_id ) return;
		$reel = RSV_Repository::find( $public_id, true );
		if ( ! $reel || ! RSV_Security::can_view_reel( $reel ) ) return;
		echo wp_kses_post( self::render_tools( $reel ) );
	}

	public function routes() {
		$ns = RSV_Contracts::API_NAMESPACE;
		register_rest_route( $ns, '/future30/manifest', array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'manifest'), 'permission_callback'=>'__return_true' ) );
		register_rest_route( $ns, '/future30/series', array(
			array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'series_list'), 'permission_callback'=>'__return_true' ),
			array( 'methods'=>WP_REST_Server::CREATABLE, 'callback'=>array($this,'series_create'), 'permission_callback'=>array($this,'can_publish') ),
		) );
		register_rest_route( $ns, '/future30/learning-paths', array(
			array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'path_list'), 'permission_callback'=>'__return_true' ),
			array( 'methods'=>WP_REST_Server::CREATABLE, 'callback'=>array($this,'path_create'), 'permission_callback'=>array($this,'can_publish') ),
		) );
		register_rest_route( $ns, '/reels/(?P<id>reel_[a-f0-9-]{36})/future30/(?P<feature>F11-FUT-[0-9]{3})', array(
			array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'reel_feature_get'), 'permission_callback'=>'__return_true' ),
			array( 'methods'=>WP_REST_Server::CREATABLE, 'callback'=>array($this,'reel_feature_write'), 'permission_callback'=>array($this,'can_publish') ),
		) );
		register_rest_route( $ns, '/reels/(?P<id>reel_[a-f0-9-]{36})/quiz/attempt', array( 'methods'=>WP_REST_Server::CREATABLE, 'callback'=>array($this,'quiz_attempt'), 'permission_callback'=>'is_user_logged_in' ) );
		register_rest_route( $ns, '/future30/collections', array(
			array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'collections'), 'permission_callback'=>'is_user_logged_in' ),
			array( 'methods'=>WP_REST_Server::CREATABLE, 'callback'=>array($this,'collection_write'), 'permission_callback'=>'is_user_logged_in' ),
		) );
		register_rest_route( $ns, '/reels/(?P<id>reel_[a-f0-9-]{36})/notes', array(
			array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'notes'), 'permission_callback'=>'is_user_logged_in' ),
			array( 'methods'=>WP_REST_Server::CREATABLE, 'callback'=>array($this,'note_write'), 'permission_callback'=>'is_user_logged_in' ),
		) );
		register_rest_route( $ns, '/reels/(?P<id>reel_[a-f0-9-]{36})/ai-context', array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'ai_context'), 'permission_callback'=>'is_user_logged_in' ) );
		register_rest_route( $ns, '/future30/search-opportunities', array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'search_opportunities'), 'permission_callback'=>array($this,'can_creator') ) );
		register_rest_route( $ns, '/reels/(?P<id>reel_[a-f0-9-]{36})/why', array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'why'), 'permission_callback'=>'__return_true' ) );
		register_rest_route( $ns, '/future30/feed-preferences', array(
			array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'feed_preferences'), 'permission_callback'=>'is_user_logged_in' ),
			array( 'methods'=>WP_REST_Server::EDITABLE, 'callback'=>array($this,'feed_preferences_write'), 'permission_callback'=>'is_user_logged_in' ),
		) );
		register_rest_route( $ns, '/future30/creator-research', array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'creator_research'), 'permission_callback'=>array($this,'can_creator') ) );
		register_rest_route( $ns, '/reels/(?P<id>reel_[a-f0-9-]{36})/safety-scan', array( 'methods'=>WP_REST_Server::CREATABLE, 'callback'=>array($this,'safety_scan'), 'permission_callback'=>array($this,'can_publish') ) );
		register_rest_route( $ns, '/future30/accessibility', array(
			array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'accessibility'), 'permission_callback'=>'is_user_logged_in' ),
			array( 'methods'=>WP_REST_Server::EDITABLE, 'callback'=>array($this,'accessibility_write'), 'permission_callback'=>'is_user_logged_in' ),
		) );
		register_rest_route( $ns, '/reels/(?P<id>reel_[a-f0-9-]{36})/knowledge-graph', array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'knowledge_graph'), 'permission_callback'=>'__return_true' ) );
	}

	public function can_publish() { return RSV_Security::can( RSV_Contracts::CAP_PUBLISH ) || RSV_Security::can( RSV_Contracts::CAP_MANAGE ); }
	public function can_creator() { return RSV_Security::can( RSV_Contracts::CAP_INSIGHTS ) || RSV_Security::can( RSV_Contracts::CAP_SUBMIT ); }

	public function manifest() { return rest_ensure_response( self::requirements( array() )['future30'] ); }

}
