<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Admin {
	public function register() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function menu() {
		if ( ! RSV_Security::can( RSV_Contracts::CAP_MANAGE ) && ! current_user_can( 'manage_options' ) ) return;
		add_menu_page( __( 'Reels', RSV_TEXT_DOMAIN ), __( 'Reels', RSV_TEXT_DOMAIN ), RSV_Contracts::CAP_MANAGE, 'rsv-reels', array( $this, 'dashboard' ), 'dashicons-video-alt3', 28 );
		add_submenu_page( 'rsv-reels', __( 'Moderation', RSV_TEXT_DOMAIN ), __( 'Moderation', RSV_TEXT_DOMAIN ), RSV_Contracts::CAP_MODERATE, 'rsv-moderation', array( $this, 'moderation' ) );
		add_submenu_page( 'rsv-reels', __( 'Diagnostics', RSV_TEXT_DOMAIN ), __( 'Diagnostics', RSV_TEXT_DOMAIN ), RSV_Contracts::CAP_MANAGE, 'rsv-diagnostics', array( $this, 'diagnostics' ) );
	}

	public function assets( $hook ) {
		if ( false === strpos( $hook, 'rsv-' ) ) return;
		wp_enqueue_style( 'rsv-admin', RSV_URL . 'assets/css/admin.css', array(), RSV_VERSION );
	}

	public function dashboard() {
		$summary = RSV_Diagnostics::summary();
		?><div class="wrap rsv-admin"><h1><?php esc_html_e( 'Reels Operations', RSV_TEXT_DOMAIN ); ?></h1>
		<div class="rsv-admin-grid">
			<div><strong><?php echo absint( $summary['published_reels'] ); ?></strong><span><?php esc_html_e( 'Published Reels', RSV_TEXT_DOMAIN ); ?></span></div>
			<div><strong><?php echo absint( $summary['open_reports'] ); ?></strong><span><?php esc_html_e( 'Open reports', RSV_TEXT_DOMAIN ); ?></span></div>
			<div><strong><?php echo esc_html( $summary['file10_ready'] ? __( 'Ready', RSV_TEXT_DOMAIN ) : __( 'Unavailable', RSV_TEXT_DOMAIN ) ); ?></strong><span><?php esc_html_e( 'File 10', RSV_TEXT_DOMAIN ); ?></span></div>
			<div><strong><?php echo esc_html( $summary['schema_ok'] ? __( 'Healthy', RSV_TEXT_DOMAIN ) : __( 'Repair required', RSV_TEXT_DOMAIN ) ); ?></strong><span><?php esc_html_e( 'Schema', RSV_TEXT_DOMAIN ); ?></span></div>
		</div></div><?php
	}

	public function moderation() {
		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT * FROM ' . RSV_Helpers::table( 'reports' ) . " WHERE status IN ('submitted','triaged','appealed') ORDER BY updated_at ASC LIMIT 100", ARRAY_A );
		?><div class="wrap rsv-admin"><h1><?php esc_html_e( 'Reel Moderation', RSV_TEXT_DOMAIN ); ?></h1>
		<?php if ( ! $rows ) : ?><p><?php esc_html_e( 'No open reports.', RSV_TEXT_DOMAIN ); ?></p><?php else : ?>
		<table class="widefat striped"><thead><tr><th>ID</th><th><?php esc_html_e( 'Reel', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Reason', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Status', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Created', RSV_TEXT_DOMAIN ); ?></th></tr></thead><tbody>
		<?php foreach ( $rows as $row ) : ?><tr><td><?php echo absint( $row['id'] ); ?></td><td><?php echo absint( $row['reel_id'] ); ?></td><td><?php echo esc_html( $row['reason_code'] ); ?></td><td><?php echo esc_html( $row['status'] ); ?></td><td><?php echo esc_html( $row['created_at'] ); ?></td></tr><?php endforeach; ?>
		</tbody></table><?php endif; ?></div><?php
	}

	public function diagnostics() {
		?><div class="wrap rsv-admin"><h1><?php esc_html_e( 'Reels Diagnostics', RSV_TEXT_DOMAIN ); ?></h1><pre><?php echo esc_html( wp_json_encode( RSV_Diagnostics::summary(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre></div><?php
	}
}
