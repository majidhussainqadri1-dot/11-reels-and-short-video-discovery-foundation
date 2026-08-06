<?php
defined( 'ABSPATH' ) || exit;

final class RSV_DB {
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$c = $wpdb->get_charset_collate();

		$sql = array();
		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'reels' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			public_id varchar(80) NOT NULL,
			video_id bigint unsigned NOT NULL,
			owner_id bigint unsigned NOT NULL,
			title varchar(255) NOT NULL,
			slug varchar(200) NOT NULL,
			topic varchar(80) NOT NULL,
			language varchar(20) NOT NULL DEFAULT 'en-US',
			caption text NOT NULL,
			disclosure varchar(500) NOT NULL DEFAULT '',
			cover_id bigint unsigned NOT NULL DEFAULT 0,
			visibility varchar(20) NOT NULL DEFAULT 'public',
			status varchar(30) NOT NULL DEFAULT 'draft',
			rights_status varchar(30) NOT NULL DEFAULT 'declared',
			consent_status varchar(30) NOT NULL DEFAULT 'not_patient_case',
			safety_labels_json longtext NOT NULL,
			rank_score decimal(12,4) NOT NULL DEFAULT 0,
			version bigint unsigned NOT NULL DEFAULT 1,
			published_at datetime NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY public_id (public_id),
			UNIQUE KEY video_id (video_id),
			UNIQUE KEY slug (slug),
			KEY feed_status (status,visibility,rank_score,id),
			KEY owner_status (owner_id,status,updated_at)
		) $c;";

		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'progress' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint unsigned NOT NULL,
			reel_id bigint unsigned NOT NULL,
			position_seconds int unsigned NOT NULL DEFAULT 0,
			completed tinyint unsigned NOT NULL DEFAULT 0,
			replays int unsigned NOT NULL DEFAULT 0,
			version bigint unsigned NOT NULL DEFAULT 1,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_reel (user_id,reel_id),
			KEY reel_updated (reel_id,updated_at),
			KEY user_updated (user_id,updated_at)
		) $c;";

		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'reports' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			public_id varchar(80) NOT NULL,
			reel_id bigint unsigned NOT NULL,
			reporter_id bigint unsigned NOT NULL,
			reason_code varchar(60) NOT NULL,
			details text NOT NULL,
			status varchar(30) NOT NULL DEFAULT 'submitted',
			action_code varchar(60) NOT NULL DEFAULT '',
			reviewer_id bigint unsigned NOT NULL DEFAULT 0,
			appeal_text text NOT NULL,
			version bigint unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY public_id (public_id),
			KEY reel_status (reel_id,status,updated_at),
			KEY reporter_created (reporter_id,created_at)
		) $c;";

		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'impressions' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			reel_id bigint unsigned NOT NULL,
			viewer_hash char(64) NOT NULL,
			viewed_seconds int unsigned NOT NULL DEFAULT 0,
			completed tinyint unsigned NOT NULL DEFAULT 0,
			swiped_rapidly tinyint unsigned NOT NULL DEFAULT 0,
			day_key date NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY reel_viewer_day (reel_id,viewer_hash,day_key),
			KEY expiry (created_at),
			KEY reel_day (reel_id,day_key)
		) $c;";

		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'audit' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			trace_id varchar(40) NOT NULL,
			actor_id bigint unsigned NOT NULL DEFAULT 0,
			object_type varchar(40) NOT NULL,
			object_id bigint unsigned NOT NULL DEFAULT 0,
			action_name varchar(80) NOT NULL,
			from_state varchar(40) NOT NULL DEFAULT '',
			to_state varchar(40) NOT NULL DEFAULT '',
			reason text NOT NULL,
			context_json longtext NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY trace_id (trace_id),
			KEY object_history (object_type,object_id,created_at),
			KEY actor_created (actor_id,created_at)
		) $c;";

		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'outbox' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			event_id varchar(80) NOT NULL,
			event_name varchar(100) NOT NULL,
			object_type varchar(40) NOT NULL,
			object_id bigint unsigned NOT NULL DEFAULT 0,
			payload_json longtext NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			attempts smallint unsigned NOT NULL DEFAULT 0,
			available_at datetime NOT NULL,
			last_error varchar(500) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY event_id (event_id),
			KEY delivery (status,available_at,id)
		) $c;";

		$sql[] = 'CREATE TABLE ' . RSV_Helpers::table( 'idempotency' ) . " (
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			actor_id bigint unsigned NOT NULL,
			scope_key varchar(80) NOT NULL,
			idem_key varchar(120) NOT NULL,
			payload_hash char(64) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'started',
			response_json longtext NOT NULL,
			expires_at datetime NOT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY actor_scope_key (actor_id,scope_key,idem_key),
			KEY expiry (expires_at)
		) $c;";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}

		update_option( 'rsv_schema_version', RSV_SCHEMA_VERSION, false );
	}

	public static function transaction( $callback ) {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		try {
			$result = $callback();
			if ( is_wp_error( $result ) ) {
				$wpdb->query( 'ROLLBACK' );
				return $result;
			}
			$wpdb->query( 'COMMIT' );
			return $result;
		} catch ( Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			return RSV_Helpers::error( 'rsv_transaction_failed', __( 'The operation could not be completed safely.', RSV_TEXT_DOMAIN ), 500 );
		}
	}
}
