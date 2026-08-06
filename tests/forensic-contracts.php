<?php
$root = dirname( __DIR__ );
$plugin = $root . '/11-reels-foundation';
$read = static function ( $path ) { $data = file_get_contents( $path ); if ( false === $data ) throw new RuntimeException( "Cannot read $path" ); return $data; };
$all = '';
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $plugin, FilesystemIterator::SKIP_DOTS ) );
foreach ( $iterator as $file ) { if ( $file->isFile() ) $all .= "\n" . $read( $file->getPathname() ); }
$must = array(
	"Version: 1.0.0-rc2",
	"define( 'RSV_SCHEMA_VERSION', '1.1.0' )",
	"smc_membership_assertions",
	"smc_publishing_assertions",
	"eligible_for_reel",
	"hash_hmac( 'sha256', (string) get_current_user_id()",
	"view_sessions",
	"actor_scope_window",
	"status IN ('pending','retry')",
	"rpt_[a-f0-9-]{36}",
	"migrate_history",
	"CHECKPOINT_OPTION",
	"rollback_legacy_cutover",
	"post_parent",
	"prefers-reduced-motion",
	"data-rsv-load-more",
	"bindVideos",
	"rsv_privacy_retain_report_evidence",
);
foreach ( $must as $needle ) { if ( false === strpos( $all, $needle ) ) { fwrite( STDERR, "Missing required contract: $needle\n" ); exit( 1 ); } }
$forbidden = array(
	"Version: 1.0.0-rc1",
	"define( 'RSV_VERSION', '1.0.0-rc1' )",
	"postMessage('\\\"{\\\"event\\\"', '*')",
	"if ( ! items )",
	"WHERE id=\\d+/moderate",
	"get_current_user_id() . '|' . gmdate( 'Y-m' )",
);
foreach ( $forbidden as $needle ) { if ( false !== strpos( $all, $needle ) ) { fwrite( STDERR, "Forbidden stale pattern: $needle\n" ); exit( 1 ); } }
$contracts = $read( $plugin . '/includes/class-rsv-contracts.php' );
if ( 15 !== preg_match_all( "/'F11-FR-[0-9]{3}'/", $contracts ) ) exit( 1 );
if ( 10 !== preg_match_all( "/'F11-NFR-[0-9]{3}'/", $contracts ) ) exit( 1 );
$rest = $read( $plugin . '/includes/class-rsv-rest.php' );
if ( preg_match( "#/reels/\\(\\?P<id>\\\\d#", $rest ) ) exit( 1 );
$privacy = $read( $plugin . '/includes/class-rsv-privacy.php' );
if ( false === strpos( $privacy, "'idempotency'   => 'actor_id'" ) ) exit( 1 );
$frontend = $read( $plugin . '/includes/class-rsv-frontend.php' );
if ( false === strpos( $frontend, 'data-rsv-report-form' ) || false === strpos( $frontend, '<textarea name="details"' ) ) exit( 1 );

$reels = $read( $plugin . '/includes/class-rsv-reels.php' );
if ( false === strpos( $reels, 'use ( $report_id, $decision, $reason, $expected_version, $public_id )' ) ) exit( 1 );
foreach ( array( 'rsv_submit_evidence_failed', 'rsv_publish_evidence_failed', 'rsv_progress_event_failed', 'rsv_appeal_evidence_failed', 'rsv_moderation_evidence_failed' ) as $evidence_code ) {
	if ( false === strpos( $reels, $evidence_code ) ) exit( 1 );
}
$js = $read( $plugin . '/assets/js/rsv.js' );
if ( false !== strpos( $js, 'status || document.body' ) || false === strpos( $js, 'data-rsv-global-status' ) ) exit( 1 );
if ( false === strpos( $frontend, 'Next page of Reels' ) || false === strpos( $frontend, '<noscript>' ) ) exit( 1 );

$db = $read( $plugin . '/includes/class-rsv-db.php' );
if ( false === strpos( $db, 'appellant_id bigint unsigned' ) || false === strpos( $db, 'KEY appellant_created' ) ) exit( 1 );
if ( false === strpos( $reels, "'appellant_id'     => 0" ) || false === strpos( $reels, "'appellant_id' => get_current_user_id()" ) ) exit( 1 );
if ( false === strpos( $reels, "'restore' === \$decision" ) || false === strpos( $reels, 'rsv_restore_media_invalid' ) ) exit( 1 );
$privacy = $read( $plugin . '/includes/class-rsv-privacy.php' );
if ( false === strpos( $privacy, 'rp.appellant_id=%d' ) || false === strpos( $privacy, 'appellant_id=IF' ) ) exit( 1 );
echo "forensic contracts PASS\n";
