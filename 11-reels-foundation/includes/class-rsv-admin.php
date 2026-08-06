<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Admin {
	public function register() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_rsv_moderate_report', array( $this, 'handle_moderation' ) );
		add_action( 'admin_post_rsv_retry_dead_events', array( $this, 'retry_dead' ) );
	}
	public function menu() {
		$manage = RSV_Contracts::CAP_MANAGE;
		$moderate = RSV_Contracts::CAP_MODERATE;
		if ( RSV_Security::can( $manage ) || current_user_can( 'manage_options' ) ) {
			add_menu_page( __( 'Reels', RSV_TEXT_DOMAIN ), __( 'Reels', RSV_TEXT_DOMAIN ), $manage, 'rsv-reels', array( $this, 'dashboard' ), 'dashicons-video-alt3', 28 );
		}
		if ( RSV_Security::can( $moderate ) || current_user_can( 'manage_options' ) ) {
			if ( ! RSV_Security::can( $manage ) && ! current_user_can( 'manage_options' ) ) {
				add_menu_page( __( 'Reels', RSV_TEXT_DOMAIN ), __( 'Reels', RSV_TEXT_DOMAIN ), $moderate, 'rsv-reels', array( $this, 'moderation' ), 'dashicons-video-alt3', 28 );
			}
			add_submenu_page( 'rsv-reels', __( 'Moderation', RSV_TEXT_DOMAIN ), __( 'Moderation', RSV_TEXT_DOMAIN ), $moderate, 'rsv-moderation', array( $this, 'moderation' ) );
		}
		if ( RSV_Security::can( $manage ) || current_user_can( 'manage_options' ) ) {
			add_submenu_page( 'rsv-reels', __( 'Diagnostics', RSV_TEXT_DOMAIN ), __( 'Diagnostics', RSV_TEXT_DOMAIN ), $manage, 'rsv-diagnostics', array( $this, 'diagnostics' ) );
		}
	}
	public function assets( $hook ) {
		if ( false === strpos( $hook, 'rsv-' ) ) return;
		wp_enqueue_style( 'rsv-admin', RSV_URL . 'assets/css/admin.css', array(), RSV_VERSION );
	}
	public function dashboard() {
		$s = RSV_Diagnostics::summary();
		?>
		<div class="wrap rsv-admin">
			<h1><?php esc_html_e( 'Reels Operations', RSV_TEXT_DOMAIN ); ?></h1>
			<div class="rsv-admin-grid">
			<?php foreach ( array( 'published_reels' => __( 'Published Reels', RSV_TEXT_DOMAIN ), 'open_reports' => __( 'Open reports', RSV_TEXT_DOMAIN ), 'pending_events' => __( 'Pending events', RSV_TEXT_DOMAIN ), 'dead_events' => __( 'Dead events', RSV_TEXT_DOMAIN ) ) as $key => $label ) : ?>
				<div><strong><?php echo absint( $s[ $key ] ?? 0 ); ?></strong><span><?php echo esc_html( $label ); ?></span></div>
			<?php endforeach; ?>
				<div><strong><?php echo esc_html( $s['file10_ready'] ? __( 'Ready', RSV_TEXT_DOMAIN ) : __( 'Unavailable', RSV_TEXT_DOMAIN ) ); ?></strong><span><?php esc_html_e( 'File 10', RSV_TEXT_DOMAIN ); ?></span></div>
				<div><strong><?php echo esc_html( $s['schema_ok'] ? __( 'Healthy', RSV_TEXT_DOMAIN ) : __( 'Repair required', RSV_TEXT_DOMAIN ) ); ?></strong><span><?php esc_html_e( 'Schema', RSV_TEXT_DOMAIN ); ?></span></div>
			</div>
		</div>
		<?php
	}
	public function moderation() {
		global $wpdb;
		$page = max( 1, absint( $_GET['paged'] ?? 1 ) );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . RSV_Helpers::table( 'reports' ) . " WHERE status IN ('submitted','triaged','appealed','action','no_action') ORDER BY updated_at ASC LIMIT 50 OFFSET %d", ( $page - 1 ) * 50 ), ARRAY_A );
		?>
		<div class="wrap rsv-admin"><h1><?php esc_html_e( 'Reel Moderation', RSV_TEXT_DOMAIN ); ?></h1>
		<?php if ( isset( $_GET['rsv_notice'] ) ) : ?><div class="notice notice-success"><p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['rsv_notice'] ) ) ); ?></p></div><?php endif; ?>
		<?php if ( ! $rows ) : ?><p><?php esc_html_e( 'No open reports.', RSV_TEXT_DOMAIN ); ?></p>
		<?php else : ?><table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Report', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Reel', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Reason', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Status', RSV_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Decision', RSV_TEXT_DOMAIN ); ?></th></tr></thead><tbody>
		<?php foreach ( $rows as $row ) : ?><tr><td><?php echo esc_html( $row['public_id'] ); ?></td><td><?php echo absint( $row['reel_id'] ); ?></td><td><strong><?php echo esc_html( $row['reason_code'] ); ?></strong><br><?php echo esc_html( wp_trim_words( $row['details'], 30 ) ); ?></td><td><?php echo esc_html( $row['status'] ); ?> (v<?php echo absint( $row['version'] ); ?>)</td><td>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'rsv_moderate_' . $row['id'] ); ?><input type="hidden" name="action" value="rsv_moderate_report"><input type="hidden" name="report_id" value="<?php echo absint( $row['id'] ); ?>"><input type="hidden" name="version" value="<?php echo absint( $row['version'] ); ?>"><select name="decision"><?php foreach ( RSV_Contracts::MODERATION_DECISIONS as $decision ) : ?><option value="<?php echo esc_attr( $decision ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $decision ) ) ); ?></option><?php endforeach; ?></select><input name="reason" maxlength="2000" required placeholder="<?php esc_attr_e( 'Evidence-based reason', RSV_TEXT_DOMAIN ); ?>"><button class="button button-primary"><?php esc_html_e( 'Apply', RSV_TEXT_DOMAIN ); ?></button></form>
		</td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div>
		<?php
	}
	public function handle_moderation() {
		if ( ! RSV_Security::can( RSV_Contracts::CAP_MODERATE, null, 'admin_moderation' ) ) wp_die( esc_html__( 'Forbidden', RSV_TEXT_DOMAIN ), esc_html__( 'Forbidden', RSV_TEXT_DOMAIN ), array( 'response' => 403 ) );
		$id = absint( $_POST['report_id'] ?? 0 );
		check_admin_referer( 'rsv_moderate_' . $id );
		$result = RSV_Reels::moderate( $id, sanitize_key( $_POST['decision'] ?? '' ), sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) ), absint( $_POST['version'] ?? 0 ) );
		$url = admin_url( 'admin.php?page=rsv-moderation' );
		$url = add_query_arg( 'rsv_notice', is_wp_error( $result ) ? $result->get_error_message() : __( 'Moderation decision recorded.', RSV_TEXT_DOMAIN ), $url );
		wp_safe_redirect( $url ); exit;
	}
	public function diagnostics() {
		$s = RSV_Diagnostics::summary();
		?><div class="wrap rsv-admin"><h1><?php esc_html_e( 'Reels Diagnostics', RSV_TEXT_DOMAIN ); ?></h1><pre><?php echo esc_html( wp_json_encode( $s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre><?php if ( ! empty( $s['dead_events'] ) ) : ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'rsv_retry_dead_events' ); ?><input type="hidden" name="action" value="rsv_retry_dead_events"><button class="button"><?php esc_html_e( 'Retry dead events safely', RSV_TEXT_DOMAIN ); ?></button></form><?php endif; ?></div><?php
	}
	public function retry_dead() {
		if ( ! RSV_Security::can( RSV_Contracts::CAP_MANAGE, null, 'retry_events' ) ) wp_die( esc_html__( 'Forbidden', RSV_TEXT_DOMAIN ), esc_html__( 'Forbidden', RSV_TEXT_DOMAIN ), array( 'response' => 403 ) );
		check_admin_referer( 'rsv_retry_dead_events' );
		global $wpdb;
		$wpdb->query( "UPDATE " . RSV_Helpers::table( 'outbox' ) . " SET status='retry',attempts=0,available_at=UTC_TIMESTAMP(),lease_token='',lease_expires_at=NULL,last_error='' WHERE status='dead'" );
		RSV_Helpers::audit( 'system', 0, 'retry_dead_events', '', 'retry' );
		wp_safe_redirect( admin_url( 'admin.php?page=rsv-diagnostics' ) ); exit;
	}
}
