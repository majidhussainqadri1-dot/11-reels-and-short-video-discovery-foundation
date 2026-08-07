<?php
$root = dirname( __DIR__ );
$p = $root . '/11-reels-foundation/includes';
$read = static function ( $path ) { $v = file_get_contents( $path ); if ( false === $v ) throw new RuntimeException( "Cannot read $path" ); return $v; };
$experience = $read( $p . '/trait-rsv-top20-experience.php' );
$responses  = $read( $p . '/trait-rsv-top20-responses.php' );
$stories    = $read( $p . '/trait-rsv-top20-stories.php' );
$context    = $read( $p . '/trait-rsv-top20-context.php' );
$privacy    = $read( $p . '/trait-rsv-top20-privacy-integration.php' );
$bootstrap  = $read( dirname( $p ) . '/11-reels-foundation.php' );
$must = array(
	array( $bootstrap, "define( 'RSV_CONTRACT_VERSION', 5 )" ),
	array( $experience, 'Do not run object-dependent publication validation before authorization' ),
	array( $experience, "RSV_Security::can( RSV_Contracts::CAP_PUBLISH, \$reel, 'publish_reel' )" ),
	array( $experience, "private, no-store, max-age=0" ),
	array( $experience, "'stories' => __( 'Stories / Status'" ),
	array( $experience, 'render_story_form' ), array( $experience, 'render_highlight_form' ),
	array( $experience, 'rsv_create_compensation_failed' ), array( $experience, 'rel="noopener noreferrer nofollow"' ),
	array( $responses, "rate_limit( 'create_response'" ), array( $responses, "idempotency_begin( 'create_response'" ),
	array( $responses, "'status' => 'review'" ), array( $responses, 'rsv_response_publish_evidence_failed' ),
	array( $stories, "idempotency_begin( 'create_story'" ), array( $stories, "idempotency_begin( 'add_highlight'" ),
	array( $stories, 'rsv_story_publish_evidence_failed' ), array( $stories, 'rsv_highlight_evidence_failed' ),
	array( $context, "array_key_exists( 'history_paused', \$data )" ), array( $context, 'rsv_preferences_evidence_failed' ),
	array( $privacy, 'LIMIT %d OFFSET %d' ), array( $privacy, "'rsv-source-context'" ),
	array( $privacy, 'rsv_privacy_erasure_evidence_failed' ), array( $privacy, "RSV_DB::transaction" ),
);
foreach ( $must as $pair ) if ( false === strpos( $pair[0], $pair[1] ) ) { fwrite( STDERR, "Missing inherited RC5 hardening marker: {$pair[1]}\n" ); exit( 1 ); }
$forbidden = array(
	array( $experience, "self::publication_gate(RSV_Repository::find" ),
	array( $responses, "public static function create_response( \$source_public_id, \$data )" ),
	array( $stories, "\$idempotency_key ?: wp_generate_uuid4()" ),
	array( $privacy, "LIMIT 500" ),
	array( $context, "'history_paused' => ! empty( \$data['history_paused'] ) ? 1 : 0" ),
);
foreach ( $forbidden as $pair ) if ( false !== strpos( $pair[0], $pair[1] ) ) { fwrite( STDERR, "Forbidden pre-RC5 pattern remains: {$pair[1]}\n" ); exit( 1 ); }
echo "inherited RC5 hardening contracts PASS\n";
