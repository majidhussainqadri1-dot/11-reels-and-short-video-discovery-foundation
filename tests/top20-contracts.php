<?php
$root = dirname( __DIR__ );
$p = $root . '/11-reels-foundation';
$read = static function ( $path ) { $v = file_get_contents( $path ); if ( false === $v ) throw new RuntimeException( "Cannot read $path" ); return $v; };
$top = '';
foreach ( array( 'class-rsv-top20.php','trait-rsv-top20-context.php','trait-rsv-top20-stories.php','trait-rsv-top20-responses.php','trait-rsv-top20-experience.php','trait-rsv-top20-privacy-integration.php' ) as $top_file ) $top .= "\n" . $read( $p . '/includes/' . $top_file );
$contracts = $read( $p . '/includes/class-rsv-contracts.php' );
$security = $read( $p . '/includes/class-rsv-security.php' );
$repo = $read( $p . '/includes/class-rsv-repository.php' );
$js = $read( $p . '/assets/js/rsv-top20.js' );
$diagnostics = $read( $p . '/includes/class-rsv-diagnostics.php' );
$file10 = $read( $p . '/includes/class-rsv-file10.php' );
if ( 11 !== preg_match_all( "/'CV-[0-9]{3}'/", $contracts ) ) { fwrite( STDERR, "Top-20 requirement count mismatch\n" ); exit( 1 ); }
foreach ( array( 'stories','highlights','highlight_items','responses','preferences','value_signals','reel_context' ) as $table ) if ( false === strpos( $top, "table( '$table' )" ) ) { fwrite( STDERR, "Missing table $table\n" ); exit( 1 ); }
foreach ( array(
	'MAX_STORY_HOURS = 24','consent_snapshot','story_viewable','patient_reuse_consent','rsv_patient_reuse_consent_valid','rsv_patient_reuse_consent_still_valid',
	"empty( \$row['patient_reuse_consent'] )",'mandatory_youth_mode','natural_stop_every','history_paused','history_cursor','value_insights','meaningful_comments',
	'rsv_source_safety_required','rsv_caption_track_required','source-open','natural-stop','render_story_form','render_highlight_form','create_response( $source_public_id, $data, $idempotency_key',
) as $needle ) if ( false === strpos( $top, $needle ) ) { fwrite( STDERR, "Missing Top-20 contract: $needle\n" ); exit( 1 ); }
if ( false === strpos( $security, 'return $base_allowed && $filtered' ) || false === strpos( $security, 'null !== $user_id' ) ) exit( 1 );
if ( false !== strpos( $security, "return (bool) apply_filters( 'rsv_authorize', false" ) ) exit( 1 );
if ( false === strpos( $repo, "'youth_safe' => \$youth" ) || false === strpos( $repo, "LIMIT 50" ) ) exit( 1 );
if ( false === strpos( $repo, '$requested_youth' ) || false === strpos( $repo, 'RSV_Top20::youth_mode()' ) ) exit( 1 );
if ( false === strpos( $top, 'public static function required_tables' ) || false === strpos( $file10, 'public static function contract_compatible' ) || false === strpos( $diagnostics, 'published_without_context' ) ) exit( 1 );
foreach ( array( 'MutationObserver','naturalStopShown','sessionStopShown','lateNightShown','recordSignal','source-open' ) as $needle ) if ( false === strpos( $js, $needle ) ) exit( 1 );
echo "top20 contracts RC5 PASS\n";
