<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function ( $path ) { $v = file_get_contents( $path ); if ( false === $v ) throw new RuntimeException( "Cannot read $path" ); return $v; };
$f = array(
    'bootstrap' => $read( $root . '/11-reels-foundation.php' ),
    'hardening' => $read( $root . '/includes/class-rsv-fresh20-hardening.php' ),
    'future'    => $read( $root . '/includes/class-rsv-future30.php' ),
);
$must = array(
    array( 'bootstrap', 'Version: 1.2.0-rc2' ),
    array( 'bootstrap', "define( 'RSV_CONTRACT_VERSION', 8 )" ),
    array( 'bootstrap', 'class-rsv-fresh20-hardening.php' ),
    array( 'hardening', 'rsv_series_invalid' ), array( 'hardening', 'rsv_learning_step_invalid' ),
    array( 'hardening', 'rsv_evidence_grade_invalid' ), array( 'hardening', 'rsv_remix_self_invalid' ),
    array( 'hardening', 'rsv_template_media_recipe_invalid' ), array( 'hardening', 'rsv_peer_review_conflict' ),
    array( 'hardening', 'rsv_translation_language_mismatch' ), array( 'hardening', 'future30_quiz_attempt_' ),
    array( 'hardening', 'ReelQuizAttemptUpdated' ), array( 'hardening', 'ReelStudyCollectionUpdated' ),
    array( 'hardening', 'FOR UPDATE' ), array( 'hardening', 'ReelPrivateNoteUpdated' ),
    array( 'hardening', 'ai-citation-current' ), array( 'hardening', "'transcript'" ),
    array( 'hardening', 'payment|donor' ), array( 'hardening', 'history_paused' ),
    array( 'hardening', 'apply_feed_runtime_preferences' ), array( 'hardening', 'ReelFeedPreferencesUpdated' ),
    array( 'hardening', 'ReelAccessibilityPreferencesUpdated' ), array( 'hardening', 'knowledge-graph-evidence-current' ),
    array( 'hardening', 'render_safe_tools' ), array( 'hardening', 'rest_post_dispatch' ),
    array( 'hardening', 'private, no-store' ),
);
foreach ( $must as $pair ) { list( $key, $needle ) = $pair; if ( false === strpos( $f[$key], $needle ) ) { fwrite( STDERR, "Missing fresh20 marker [$key]: $needle\n" ); exit( 1 ); } }
if ( 30 !== preg_match_all( "/'F11-FUT-[0-9]{3}'/", substr( $f['future'], strpos( $f['future'], 'const FEATURE_IDS' ), strpos( $f['future'], 'public static function definitions' ) - strpos( $f['future'], 'const FEATURE_IDS' ) ) ) ) { fwrite( STDERR, "Future30 manifest no longer contains exactly 30 stable IDs\n" ); exit( 1 ); }
foreach ( array( 'Version: 1.2.0-rc1', "define( 'RSV_CONTRACT_VERSION', 7 )" ) as $needle ) if ( false !== strpos( $f['bootstrap'], $needle ) ) { fwrite( STDERR, "Stale release identity remains: $needle\n" ); exit( 1 ); }
echo "fresh 20-round hardening contracts PASS\n";
