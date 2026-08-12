<?php
$root = dirname( __DIR__ ) . '/11-reels-foundation';
$read = static function ( $path ) { $v = file_get_contents( $path ); if ( false === $v ) throw new RuntimeException( "Cannot read $path" ); return $v; };
$f = array(
 'bootstrap'=>$read($root.'/11-reels-foundation.php'),
 'future'=>$read($root.'/includes/class-rsv-future30.php').$read($root.'/includes/trait-rsv-future30-storage.php').$read($root.'/includes/trait-rsv-future30-feature-write.php').$read($root.'/includes/trait-rsv-future30-user-features.php').$read($root.'/includes/trait-rsv-future30-experience.php').$read($root.'/includes/class-rsv-fresh20-hardening.php').$read($root.'/includes/class-rsv-fresh-review-hardening.php'),
 'contracts'=>$read($root.'/includes/class-rsv-contracts.php'),
 'plugin'=>$read($root.'/includes/class-rsv-plugin.php'),
 'frontend'=>$read($root.'/includes/class-rsv-frontend.php'),
 'uninstall'=>$read($root.'/uninstall.php'),
);
$must = array(
 ['bootstrap','Version: 1.2.0-rc3'], ['bootstrap',"define( 'RSV_CONTRACT_VERSION', 9 )"], ['bootstrap',"'class-rsv-future30.php'"], ['bootstrap',"'class-rsv-fresh20-hardening.php'"], ['bootstrap',"'class-rsv-fresh-review-hardening.php'"],
 ['future',"const SCHEMA_VERSION = '1.0.0'"], ['future',"const PLAN_REVISION = '2026-08-12'"],
 ['future','Educational Reel Series / Playlists'], ['future','Structured Learning Paths'], ['future','Source-at-Time Citation Cards'],
 ['future','Correction & Supersession System'], ['future','Advanced Remix Studio'], ['future','Remix Permission Matrix'],
 ['future','Question → Reel Answer'], ['future','Expert Review Badge'], ['future','10-Language Reel System'], ['future','AI Translation + Optional Dubbing'],
 ['future','Searchable Transcript'], ['future','Smart Chapters / Key Moments'], ['future','Micro-Quiz after Reel'], ['future','Save to Study Collection'],
 ['future','Reel → Personal Notes'], ['future','Ask AI About This Reel'], ['future','Creator Search Opportunity Intelligence'],
 ['future','Feed Control Center'], ['future','Serendipity / Knowledge Diversity Slider'], ['future','Creator Research Dashboard'],
 ['future','Pre-Publish Clinical Safety Scanner'], ['future','Accessibility Plus Mode'], ['future','Reel Knowledge Graph'],
 ['future',"'raw_media_owner'=>'File 10'"], ['future',"'ai_owner'=>'File 16'"], ['future',"'knowledge_owner'=>'File 06'"], ['future',"'global_discovery_owner'=>'File 26'"],
 ['future','future_objects'], ['future','future_edges'], ['future','future_user_state'], ['future','Idempotency-Key'], ['future','idempotency_begin'], ['future','rate_limit'],
 ['future','wp_privacy_personal_data_exporters'], ['future','wp_privacy_personal_data_erasers'], ['future',"'auto_publish'=>false"], ['future',"'ranking_effect'=>false"], ['future',"'patient_case_default'=>'deny'"],
 ['future',"'payment_or_donation_reason'=>false"], ['future',"'viewer_identity_exposed'=>false"], ['future','generic_public_allowed'], ['future','generic_write_allowed'], ['future','public_projection'],
 ['future','rsv_future30_public_ref_valid'], ['future','rsv_future30_remix_allowed'], ['future','rsv_future30_file10_derivative_valid'], ['future','rsv_future30_coauthor_consent_valid'], ['future','rsv_future30_peer_review_attestation_valid'], ['future','rsv_future30_voice_consent_valid'],
 ['future','rsv_future30_transcript_ref_public_valid'], ['future','at most nine linked language versions'], ['future','feed_allows_row'], ['future','body_classes'], ['future','rsv-a11y-'], ['future','unset( $question[\'correct\']'], ['future','Authoritative Reel duration'],
 ['plugin','RSV_Future30::install'], ['plugin','new RSV_Future30'], ['future','single_reel_tools'], ['future','wp_add_inline_style'], ['future','wp_add_inline_script'], ['uninstall','future_user_state'], ['contracts','F11-FUT-030'],
 ['future','rsv_translation_language_mismatch'], ['future','ReelQuizAttemptUpdated'], ['future','render_safe_tools'], ['future','ReelTranscriptProjectionReady'], ['future','ReelLanguageLinksReconciled'], ['future','authoritative_duration_seconds'],
);
foreach ($must as [$key,$needle]) if (false === strpos($f[$key], $needle)) { fwrite(STDERR,"Missing Future30 marker [$key]: $needle\n"); exit(1); }
if (30 !== preg_match_all("/'F11-FUT-[0-9]{3}'/", $f['contracts'])) { fwrite(STDERR,"Expected exactly 30 Future30 requirement IDs in contracts\n"); exit(1); }
if (30 !== preg_match_all("/'F11-FUT-[0-9]{3}'/", substr($f['future'], strpos($f['future'],'const FEATURE_IDS'), strpos($f['future'],'public static function definitions')-strpos($f['future'],'const FEATURE_IDS')))) { fwrite(STDERR,"Expected exactly 30 Feature IDs in Future30 manifest\n"); exit(1); }
$forbidden = array('raw media owner File 11','autonomous diagnosis','paid ranking advantage');
foreach($forbidden as $needle) if(false!==stripos($f['future'],$needle)){fwrite(STDERR,"Forbidden Future30 claim: $needle\n");exit(1);}
echo "File 11 Future30 rc3 contracts PASS\n";
