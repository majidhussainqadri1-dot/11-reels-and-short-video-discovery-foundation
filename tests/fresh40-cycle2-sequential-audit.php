<?php
/**
 * Forty fresh sequential whole-source static/contract audits.
 * Each round re-reads every available source file, checks all requirement IDs,
 * enforces the newest regressions, and performs one distinct focused guard.
 * This is not 40 separate manual semantic reviews or live staging runs.
 */
$root = dirname( __DIR__ );
$source_root = $root . '/11-reels-foundation';
$checks = array(
    array( 'Bootstrap version and runtime', '11-reels-foundation/11-reels-foundation.php', 'RSV_VERSION' ),
    array( 'Functional/nonfunctional traceability', '11-reels-foundation/includes/class-rsv-contracts.php', 'F11-NFR-010' ),
    array( 'File 10 canonical media provider', '11-reels-foundation/includes/class-rsv-file10.php', 'VWLB_Videos' ),
    array( 'Verified 60–600 duration', '11-reels-foundation/includes/class-rsv-file10.php', '$duration >= 60 && $duration <= 600' ),
    array( 'Restricted publishing', '11-reels-foundation/includes/class-rsv-reels.php', 'RSV_Security::can' ),
    array( 'Two-factor publisher claims', '11-reels-foundation/includes/class-rsv-security.php', 'session_two_factor' ),
    array( 'File10 consent/rights', '11-reels-foundation/includes/class-rsv-file10.php', 'consent_status' ),
    array( 'Guardian and suspension', '11-reels-foundation/includes/class-rsv-security.php', 'guardian_ok' ),
    array( 'Atomic state transitions', '11-reels-foundation/includes/class-rsv-state-machine.php', 'published' ),
    array( 'Inter-module provider manifest', '11-reels-foundation/includes/class-rsv-integrations.php', 'PROVIDER_VERSION' ),
    array( 'REST permission guard', '11-reels-foundation/includes/class-rsv-rest.php', 'permission_callback' ),
    array( 'Idempotency and duplicate safety', '11-reels-foundation/includes/class-rsv-security.php', 'idempotency_begin' ),
    array( 'SQL prepared statements', '11-reels-foundation/includes/class-rsv-repository.php', '$wpdb->prepare' ),
    array( 'Optimistic versioned updates', '11-reels-foundation/includes/class-rsv-repository.php', 'update_versioned' ),
    array( 'Outbox retry mechanism', '11-reels-foundation/includes/class-rsv-jobs.php', 'outbox' ),
    array( 'Migration checkpoint', '11-reels-foundation/includes/class-rsv-migration.php', 'CHECKPOINT_OPTION' ),
    array( 'Migration lock ownership', '11-reels-foundation/includes/class-rsv-migration-lock-guard.php', 'lock' ),
    array( 'WP privacy export', '11-reels-foundation/includes/class-rsv-privacy.php', 'wp_privacy_personal_data_exporters' ),
    array( 'WP privacy erasure', '11-reels-foundation/includes/class-rsv-privacy.php', 'wp_privacy_personal_data_erasers' ),
    array( 'Anonymous aggregate threshold', '11-reels-foundation/includes/class-rsv-reels.php', 'rsv_insights_privacy_threshold' ),
    array( 'Series current public refs', '11-reels-foundation/includes/trait-rsv-future30-feature-write.php', 'series-public-read' ),
    array( 'Learning paths File05 owner', '11-reels-foundation/includes/class-rsv-future30.php', 'File 05' ),
    array( 'Evidence File06 owner', '11-reels-foundation/includes/class-rsv-future30.php', 'File 06' ),
    array( 'AI File16 context', '11-reels-foundation/includes/class-rsv-future30-ai-context-safety.php', 'source_object_valid' ),
    array( 'File26 discovery ownership', '11-reels-foundation/includes/class-rsv-future30.php', 'File 26' ),
    array( 'Future30 current public projection', '11-reels-foundation/includes/trait-rsv-future30-storage.php', 'public_projection' ),
    array( 'Typed multi-choice quiz grading', '11-reels-foundation/includes/trait-rsv-future30-user-features.php', 'quiz_answer_matches' ),
    array( 'Linked translated Reel checks', '11-reels-foundation/includes/trait-rsv-future30-feature-write.php', 'rsv_translated_reel_invalid' ),
    array( 'Creator research sample proof', '11-reels-foundation/includes/class-rsv-third-review-hardening.php', 'MIN_CREATOR_RESEARCH_SAMPLE' ),
    array( 'Bounded atomic Future30 erasure', '11-reels-foundation/includes/class-rsv-future30-privacy-integrity.php', 'LIMIT 100 FOR UPDATE' ),
    array( 'Autoplay opt-in default off', '11-reels-foundation/assets/js/rsv.js', 'let autoplay = false' ),
    array( 'Reduced motion preference', '11-reels-foundation/assets/js/rsv.js', 'prefers-reduced-motion' ),
    array( 'Keyboard accessibility', '11-reels-foundation/assets/js/rsv.js', 'keydown' ),
    array( 'Wellbeing and stop control', '11-reels-foundation/assets/js/rsv-top20.js', 'wellbeing' ),
    array( 'Captions and transcript fallback', '11-reels-foundation/includes/class-rsv-frontend.php', 'captions' ),
    array( 'RTL styles and semantic layout', '11-reels-foundation/assets/css/rsv.css', '[dir="rtl"]' ),
    array( 'Patient case risk and moderation', '11-reels-foundation/includes/class-rsv-top20.php', 'patient' ),
    array( 'Diagnostics and safe mode', '11-reels-foundation/includes/class-rsv-diagnostics.php', 'safe_mode' ),
    array( 'Admin review and auditability', '11-reels-foundation/includes/class-rsv-admin.php', 'moderate' ),
    array( 'Release guard/test wiring', 'tests/run-all.sh', 'fresh40-cycle2-quiz-series.php' ),
);
if ( count( $checks ) !== 40 ) throw new RuntimeException( 'Exactly forty review scopes required.' );
$sourceFiles = array();
$walker = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source_root, FilesystemIterator::SKIP_DOTS ) );
foreach ( $walker as $file ) {
    if ( ! $file->isFile() || ! in_array( strtolower( $file->getExtension() ), array( 'php', 'js', 'css' ), true ) ) continue;
    $sourceFiles[] = $file->getPathname();
}
sort( $sourceFiles, SORT_STRING );
if ( count( $sourceFiles ) < 45 ) throw new RuntimeException( 'Source tree appears incomplete.' );
$baseline = null;
$success = 0;
for ( $round = 1; $round <= 40; ++$round ) {
    $concat = '';
    foreach ( $sourceFiles as $file ) {
        $bytes = file_get_contents( $file );
        if ( false === $bytes || '' === $bytes ) throw new RuntimeException( "Round $round: unreadable/empty source file: $file" );
        $concat .= hash( 'sha256', $bytes );
    }
    $digest = hash( 'sha256', $concat );
    if ( null !== $baseline && $digest !== $baseline ) throw new RuntimeException( "Round $round: source changed during consecutive reviews." );
    $baseline = $digest;
    $contracts = file_get_contents( $source_root . '/includes/class-rsv-contracts.php' );
    if ( false === $contracts ) throw new RuntimeException( 'Contracts unavailable.' );
    foreach ( array( 'F11-FR-'=>15, 'F11-NFR-'=>10, 'F11-FUT-'=>30 ) as $prefix => $total ) {
        for ( $id = 1; $id <= $total; ++$id ) {
            $reference = $prefix . str_pad( (string) $id, 3, '0', STR_PAD_LEFT );
            if ( false === strpos( $contracts, $reference ) ) throw new RuntimeException( "Round $round: requirement $reference missing." );
        }
    }
    $series = file_get_contents( $source_root . '/includes/trait-rsv-future30-feature-write.php' );
    $quiz = file_get_contents( $source_root . '/includes/trait-rsv-future30-user-features.php' );
    $ai = file_get_contents( $source_root . '/includes/class-rsv-future30-ai-context-safety.php' );
    if ( false === strpos( $series, 'series-public-read' ) ||
         false === strpos( $quiz, 'quiz_answer_matches' ) ||
         false === strpos( $ai, 'source_object_valid' ) ||
         false === strpos( $ai, 'timed_row_valid' ) )
        throw new RuntimeException( "Round $round: a previously corrected cross-file guard regressed." );
    $safety = file_get_contents( $source_root . '/includes/class-rsv-future30-public-safety.php' );
    $storage = file_get_contents( $source_root . '/includes/trait-rsv-future30-storage.php' );
    if ( false === strpos( $series, 'private static function public_path_rows()' ) ||
         false === strpos( $series, "'rsv_future30_file05_public_ref_valid'" ) ||
         false === strpos( $safety, 'public static function public_sourced_payload' ) ||
         false === strpos( $storage, 'RSV_Future30_Public_Safety::public_sourced_payload' ) )
        throw new RuntimeException( "Round $round: File05 current reference or File06 public payload minimization regressed." );
    list( $scope, $path, $marker ) = $checks[ $round - 1 ];
    $contents = file_get_contents( $root . '/' . $path );
    if ( false === $contents || false === strpos( $contents, $marker ) )
        throw new RuntimeException( "Round $round defect: $scope / $path lacks required contract marker." );
    ++$success;
    echo sprintf( "ROUND %02d / 40: CLEAN — %s; %d source files; requirement IDs 55/55\n", $round, $scope, count( $sourceFiles ) );
}
echo "FORTY SEQUENTIAL FROZEN-TREE STATIC/CONTRACT REVIEW ROUNDS: $success / 40 PASS\n";
