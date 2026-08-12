<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function ( $path ) { $value = file_get_contents( $path ); if ( false === $value ) throw new RuntimeException( "Cannot read $path" ); return $value; };
$f = array(
	'bootstrap' => $read( $root . '/11-reels-foundation.php' ),
	'security' => $read( $root . '/includes/class-rsv-security.php' ),
	'feature' => $read( $root . '/includes/trait-rsv-future30-feature-write.php' ),
	'privacy' => $read( $root . '/includes/class-rsv-future30-privacy-integrity.php' ),
	'private_guard' => $read( $root . '/includes/class-rsv-future30-private-route-guard.php' ),
	'hardening' => $read( $root . '/includes/class-rsv-fresh-review-hardening.php' ),
	'public_min' => $read( $root . '/includes/class-rsv-fresh-review-public-minimization.php' ),
);
$markers = array(
	array( 'bootstrap', 'class-rsv-future30-private-route-guard.php' ),
	array( 'bootstrap', 'class-rsv-fresh-review-hardening.php' ),
	array( 'bootstrap', 'class-rsv-fresh-review-public-minimization.php' ),
	array( 'security', 'delete_idempotency_snapshot' ),
	array( 'security', 'AND status=%s AND payload_hash=%s AND updated_at=%s AND expires_at=%s' ),
	array( 'security', 'A zero-row CAS means another request changed the record after our read.' ),
	array( 'feature', 'ReelTranscriptProjectionReady' ),
	array( 'feature', 'rsv_transcript_event_failed' ),
	array( 'privacy', 'ReelFuturePrivateStateErased' ),
	array( 'privacy', "'reconcile' => array( 'cache', 'index', 'feed-preferences', 'accessibility-preferences' )" ),
	array( 'private_guard', 'class RSV_Future30_Private_Route_Guard' ),
	array( 'private_guard', 'rsv_private_state_ineligible' ),
	array( 'private_guard', "empty( \$claims['is_suspended'] )" ),
	array( 'private_guard', "! empty( \$claims['guardian_ok'] )" ),
	array( 'private_guard', 'future30/feed-preferences' ),
	array( 'private_guard', 'future30/accessibility' ),
	array( 'hardening', 'canonical_language_tag' ),
	array( 'hardening', 'rsv_language_tag_not_canonical' ),
	array( 'hardening', 'ReelLanguageLinksReconciled' ),
	array( 'hardening', 'RSV_Security::can_view_reel( $target, 0 )' ),
	array( 'hardening', 'rsv_future30_translation_request_public_valid' ),
	array( 'hardening', 'rsv_translation_source_language_invalid' ),
	array( 'hardening', 'rsv_quiz_unknown_field' ),
	array( 'hardening', 'rsv_ai_context_duration_unavailable' ),
	array( 'hardening', 'authoritative_duration_seconds' ),
	array( 'public_min', "'/rsv/v1/future30/learning-paths'" ),
	array( 'public_min', "array( 'file05_ref'=>\$ref, 'level'=>\$level )" ),
	array( 'public_min', 'rsv_future30_file05_public_ref_valid' ),
);
foreach ( $markers as $pair ) {
	list( $key, $needle ) = $pair;
	if ( false === strpos( $f[ $key ], $needle ) ) { fwrite( STDERR, "Missing second fresh20 invariant [$key]: $needle\n" ); exit( 1 ); }
}
if ( false !== strpos( $f['feature'], "do_action('rsv_transcript_ready_for_index'" ) || false !== strpos( $f['feature'], "do_action( 'rsv_transcript_ready_for_index'" ) ) {
	fwrite( STDERR, "Pre-commit transcript-ready action must not return\n" ); exit( 1 );
}
echo "second fresh 20-round corrective invariants PASS\n";
