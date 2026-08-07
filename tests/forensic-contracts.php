<?php
$root = dirname( __DIR__ );
$plugin = $root . '/11-reels-foundation';
$read = static function ( $path ) { $data = file_get_contents( $path ); if ( false === $data ) throw new RuntimeException( "Cannot read $path" ); return $data; };
$all = '';
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $plugin, FilesystemIterator::SKIP_DOTS ) );
foreach ( $iterator as $file ) if ( $file->isFile() ) $all .= "\n" . $read( $file->getPathname() );
$must = array(
	"Version: 1.1.0-rc6", "define( 'RSV_SCHEMA_VERSION', '1.2.0' )", "define( 'RSV_CONTRACT_VERSION', 5 )",
	"smc_membership_assertions", "rsv_identity_contract_compatible", "eligible_for_reel", "view_sessions", "actor_scope_window",
	"status IN ('pending','retry')", "migrate_history", "CHECKPOINT_OPTION", "rollback_legacy_cutover", "post_parent",
	"prefers-reduced-motion", "data-rsv-load-more", "bindVideos", "rsv_privacy_retain_report_evidence",
	"class RSV_Integrations", "class RSV_Top20", "sabri_platform_domain_provider_registered", "public_by_author", "search_public",
	"Why this Reel?", "data-rsv-topic", "body.data.trace_id", "--sabri-color-primary",
	"stories-status", "attributed-responses", "mandatory_youth_mode", "history_cursor", "patient_reuse_consent",
	"rsv_response_finalize_failed", "rsv_story_publish_evidence_failed", "rsv_highlight_evidence_failed", "rsv_privacy_erasure_evidence_failed",
	"value_signal_receipts", "StoryExpired", "JSON_HEX_TAG", "rsv_repair_evidence_failed",
);
foreach ( $must as $needle ) if ( false === strpos( $all, $needle ) ) { fwrite( STDERR, "Missing required contract: $needle\n" ); exit( 1 ); }
$forbidden = array(
	"Version: 1.1.0-rc5", "define( 'RSV_VERSION', '1.0.0-rc3' )", "Version: 1.0.0-rc2", "Version: 1.0.0-rc1",
	"if ( ! items )", "get_current_user_id() . '|' . gmdate( 'Y-m' )", ":root{--rsv-green",
);
foreach ( $forbidden as $needle ) if ( false !== strpos( $all, $needle ) ) { fwrite( STDERR, "Forbidden stale pattern: $needle\n" ); exit( 1 ); }
$contracts = $read( $plugin . '/includes/class-rsv-contracts.php' );
if ( 15 !== preg_match_all( "/'F11-FR-[0-9]{3}'/", $contracts ) ) exit( 1 );
if ( 10 !== preg_match_all( "/'F11-NFR-[0-9]{3}'/", $contracts ) ) exit( 1 );
if ( 11 !== preg_match_all( "/'CV-[0-9]{3}'/", $contracts ) ) exit( 1 );
$rest = $read( $plugin . '/includes/class-rsv-rest.php' );
if ( preg_match( "#/reels/\\(\\?P<id>\\\\d#", $rest ) ) exit( 1 );
if ( false === strpos( $rest, "'topic' => \$request->get_param( 'topic' )" ) ) exit( 1 );
$privacy = $read( $plugin . '/includes/class-rsv-privacy.php' );
if ( false === strpos( $privacy, "'idempotency'   => 'actor_id'" ) ) exit( 1 );
$frontend = $read( $plugin . '/includes/class-rsv-frontend.php' );
foreach ( array( 'data-rsv-report-form', '<textarea name="details"', 'rsv-discovery-controls', 'Recommended ordering is explainable', 'private function icon', 'private function error_trace', 'Next page of Reels', '<noscript>' ) as $needle ) if ( false === strpos( $frontend, $needle ) ) exit( 1 );
$reels = $read( $plugin . '/includes/class-rsv-reels.php' );
foreach ( array( 'rsv_submit_evidence_failed','rsv_publish_evidence_failed','rsv_progress_event_failed','rsv_appeal_evidence_failed','rsv_moderation_evidence_failed','rsv_restore_media_invalid' ) as $needle ) if ( false === strpos( $reels, $needle ) ) exit( 1 );
$security = $read( $plugin . '/includes/class-rsv-security.php' );
if ( false === strpos( $security, 'return $base_allowed && $filtered' ) ) exit( 1 );
$top = '';
foreach ( array( 'class-rsv-top20.php','trait-rsv-top20-context.php','trait-rsv-top20-stories.php','trait-rsv-top20-responses.php','trait-rsv-top20-experience.php','trait-rsv-top20-privacy-integration.php' ) as $top_file ) $top .= "\n" . $read( $plugin . '/includes/' . $top_file );
foreach ( array( 'MAX_STORY_HOURS = 24','publication_gate','consent_snapshot','public_highlights','create_response','history_cursor','value_insights','render_story_form','render_highlight_form' ) as $needle ) if ( false === strpos( $top, $needle ) ) exit( 1 );
$db = $read( $plugin . '/includes/class-rsv-db.php' );
if ( false === strpos( $db, 'appellant_id bigint unsigned' ) || false === strpos( $db, 'KEY appellant_created' ) ) exit( 1 );
$css = $read( $plugin . '/assets/css/rsv.css' ) . $read( $plugin . '/assets/css/rsv-top20.css' );
if ( false !== strpos( $css, ':root{' ) || false === strpos( $css, '--sabri-color-primary' ) ) exit( 1 );
echo "forensic contracts RC6 PASS\n";
