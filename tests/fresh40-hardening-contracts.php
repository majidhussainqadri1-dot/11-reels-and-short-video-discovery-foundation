<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function( $path ) { $value = file_get_contents( $path ); if ( false === $value ) throw new RuntimeException( "Cannot read $path" ); return $value; };
$files = array(
	'bootstrap' => $read( $root . '/11-reels-foundation.php' ),
	'public' => $read( $root . '/includes/class-rsv-future30-public-safety.php' ),
	'write' => $read( $root . '/includes/class-rsv-future30-write-integrity.php' ),
	'private' => $read( $root . '/includes/class-rsv-future30-private-state-integrity.php' ),
	'ai' => $read( $root . '/includes/class-rsv-future30-ai-context-safety.php' ),
	'analytics' => $read( $root . '/includes/class-rsv-future30-analytics-safety.php' ),
	'a11y' => $read( $root . '/includes/class-rsv-future30-accessibility-runtime.php' ),
	'privacy' => $read( $root . '/includes/class-rsv-future30-privacy-integrity.php' ),
	'lock' => $read( $root . '/includes/class-rsv-migration-lock-guard.php' ),
	'feature_write' => $read( $root . '/includes/trait-rsv-future30-feature-write.php' ),
	'storage' => $read( $root . '/includes/trait-rsv-future30-storage.php' ),
);
$markers = array(
	array('bootstrap','class-rsv-future30-public-safety.php'), array('bootstrap','class-rsv-future30-write-integrity.php'), array('bootstrap','class-rsv-future30-private-state-integrity.php'),
	array('bootstrap','class-rsv-future30-ai-context-safety.php'), array('bootstrap','class-rsv-future30-analytics-safety.php'), array('bootstrap','class-rsv-future30-accessibility-runtime.php'), array('bootstrap','class-rsv-future30-privacy-integrity.php'), array('bootstrap','class-rsv-migration-lock-guard.php'),
	array('public','rest_post_dispatch'), array('public','unset( $row[\'owner_id\'], $row[\'reel_id\']'), array('public','human_reviewed'), array('public','rsv_future30_peer_review_attestation_valid'), array('public','rsv_future30_transcript_ref_public_valid'), array('public','rsv_supersession_cycle'),
	array('write','rsv_version_snapshot_immutable'), array('write','rsv_future_public_id_conflict'), array('write','validate_quiz'), array('write','rsv_knowledge_source_invalid'), array('write','rsv_graph_source_invalid'),
	array('private','rest_request_before_callbacks'), array('private','FOR UPDATE'), array('private','rsv_collection_key_conflict'), array('private','ReelStudyCollectionUpdated'),
	array('ai','owner_id'), array('ai','private, no-store'), array('analytics','MIN_AGGREGATE = 5'), array('analytics','aggregate-only-minimum-5'),
	array('a11y','rsv_file10_accessibility_track'), array('a11y','captionPosition'), array('a11y',"track.kind='descriptions'"),
	array('privacy','FOR UPDATE'), array('privacy','rsv_future30_erase_incomplete'), array('privacy','future30_erase'),
	array('lock','pre_delete_option_'), array('lock','acquire_lock'), array('lock','release_lock'),
	array('feature_write','rsv_future30_evidence_failed'), array('feature_write','RSV_DB::transaction'), array('storage','idempotent_finish_result'), array('storage','rsv_future_evidence_failed'),
);
foreach ( $markers as array($key,$needle) ) {
	if ( false === strpos( $files[$key], $needle ) ) { fwrite( STDERR, "Missing fresh40 hardening marker [$key]: $needle\n" ); exit( 1 ); }
}
if ( false !== strpos( $files['public'], "'translation_status'=>'candidate'" ) ) { fwrite( STDERR, "Unsafe candidate translation marker found in public guard\n" ); exit( 1 ); }
echo "fresh 40-round hardening contracts PASS\n";
