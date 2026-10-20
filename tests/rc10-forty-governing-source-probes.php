<?php
/**
 * 40 independent governing-plan SOURCE checks, paired with real behavioral
 * regression tests elsewhere in run-all.sh. Not staging or 40 human reviews.
 */
$root=dirname(__DIR__);
$checks=[
  ["FR01 Reel creation bound to File10","11-reels-foundation/includes/class-rsv-reels.php",["function create(","validate_for_reel"]],
  ["FR02 Authoritative duration","11-reels-foundation/includes/class-rsv-file10.php",['$duration >= 60','$duration <= 600']],
  ["FR03 Educational taxonomy","11-reels-foundation/includes/class-rsv-reels.php",["topic"]],
  ["FR04 Publish and review state","11-reels-foundation/includes/class-rsv-reels.php",["function publish(","publication_gate"]],
  ["FR05 Public vertical feed","11-reels-foundation/includes/class-rsv-frontend.php",["function feed(","render_reel"]],
  ["FR06 Autoplay opt-in + reduced motion","11-reels-foundation/assets/js/rsv.js",["autoplay = false","prefers-reduced-motion"]],
  ["FR07 Quality ranking","11-reels-foundation/includes/class-rsv-reels.php",["base_rank_score","recalculate_rank"]],
  ["FR08 Canonical interaction bridge","11-reels-foundation/includes/class-rsv-file10.php",["function interact("]],
  ["FR09 Private watch progress/history","11-reels-foundation/includes/class-rsv-reels.php",["function progress(","clear_history"]],
  ["FR10 Canonical deep links","11-reels-foundation/includes/class-rsv-frontend.php",["function rewrites(","rsv_reel"]],
  ["FR11 Reporting and moderation","11-reels-foundation/includes/class-rsv-reels.php",["function report(","function appeal(","function moderate("]],
  ["FR12 Caption publication gate","11-reels-foundation/includes/trait-rsv-top20-context.php",["caption_track_required","publication_gate"]],
  ["FR13 Degraded dependency diagnostics","11-reels-foundation/includes/class-rsv-diagnostics.php",["safe_mode","queue_snapshot"]],
  ["FR14 Aggregate creator insights","11-reels-foundation/includes/class-rsv-reels.php",["function insights("]],
  ["FR15 Abuse and replay limits","11-reels-foundation/includes/class-rsv-security.php",["rate_limit","idempotency_begin"]],
  ["NFR01 Field/object level authorization","11-reels-foundation/includes/class-rsv-security.php",["can_view_reel","owner_id","CAP_PUBLISH"]],
  ["NFR02 Export and privacy erase","11-reels-foundation/includes/class-rsv-privacy.php",["wp_privacy_personal_data_exporters","wp_privacy_personal_data_erasers"]],
  ["NFR03 Background outbox/retries","11-reels-foundation/includes/class-rsv-jobs.php",["outbox"]],
  ["NFR04 Bounded queries","11-reels-foundation/includes/class-rsv-repository.php",["LIMIT","prepare"]],
  ["NFR05 CSS RTL and focus","11-reels-foundation/assets/css/rsv.css",["rtl","focus"]],
  ["NFR06 Audit and event evidence","11-reels-foundation/includes/class-rsv-helpers.php",["function audit(","function outbox("]],
  ["NFR07 Migration/rollback","11-reels-foundation/includes/class-rsv-migration.php",["rollback"]],
  ["NFR08 Repair/diagnostics","11-reels-foundation/includes/class-rsv-diagnostics.php",["function repair(","function summary("]],
  ["NFR09 Versioned WordPress plugin","11-reels-foundation/11-reels-foundation.php",["RSV_VERSION","RSV_SCHEMA_VERSION"]],
  ["NFR10 Extractable localization","11-reels-foundation/languages/reels-short-video-discovery.pot",["msgid"]],
  ["FUT01 Series read revocation","11-reels-foundation/includes/trait-rsv-future30-feature-write.php",["public_series_rows","series-public-read"]],
  ["FUT02 Learning path read revocation","11-reels-foundation/includes/trait-rsv-future30-feature-write.php",["public_learning_path_rows","rsv_future30_file05_public_ref_valid"]],
  ["FUT05 Evidence references","11-reels-foundation/includes/trait-rsv-future30-storage.php",["evidence-layer-current","public_projection"]],
  ["FUT13 Approved peer review","11-reels-foundation/includes/trait-rsv-future30-storage.php",["peer-reviewer-public-current","peer_review_attestation_valid"]],
  ["FUT14 Translation source uniqueness","11-reels-foundation/includes/trait-rsv-future30-feature-write.php",["rsv_translation_language_mismatch","translation_count>=9"]],
  ["FUT15 AI dubbing consent","11-reels-foundation/includes/trait-rsv-future30-feature-write.php",["rsv_future30_voice_consent_valid","auto_publish'=>false"]],
  ["FUT19 Quiz answer non-disclosure","11-reels-foundation/includes/class-rsv-future30-public-safety.php",["public_quiz_questions"]],
  ["FUT20 Private study collection","11-reels-foundation/includes/trait-rsv-future30-user-features.php",["function collections(","collection_write"]],
  ["FUT21 Private timestamp notes","11-reels-foundation/includes/trait-rsv-future30-user-features.php",["function notes(","note_write"]],
  ["FUT22 File16 grounded context","11-reels-foundation/includes/class-rsv-future30-ai-context-safety.php",["safe_context_row","execute_ai"]],
  ["FUT23 k-anonymous File26 demand","11-reels-foundation/includes/trait-rsv-future30-user-features.php",["public_search_opportunity_rows","aggregate_count"]],
  ["FUT24 Why recommendation reason","11-reels-foundation/includes/trait-rsv-future30-user-features.php",["function why(","payment_or_donation_reason"]],
  ["FUT26 Diversity feed prefs","11-reels-foundation/includes/trait-rsv-future30-user-features.php",["function feed_preferences_write(","diversity"]],
  ["FUT28 Human clinical safety scan","11-reels-foundation/includes/trait-rsv-future30-experience.php",["run_safety_scan","review-required"]],
  ["FUT30 Source-current knowledge graph","11-reels-foundation/includes/trait-rsv-future30-storage.php",["knowledge-graph-evidence-current","public_projection"]],
];
if(count($checks)!==40)throw new RuntimeException("Forty requirements were expected.");
$tested=0;$errors=[];
foreach($checks as $item){
    [$id,$path,$needles]=$item;
    $f=$root.'/'.$path;
    if(!is_file($f)){$errors[]="$id missing file $path";continue;}
    $source=file_get_contents($f);
    if(false===$source){$errors[]="$id unreadable $path";continue;}
    foreach($needles as $needle){
        if(false===strpos($source,$needle))$errors[]="$id missing control marker: $needle";
    }
    $tested++;
    echo "SCOPE $id " . (count(array_filter($errors,static function($e) use($id){return 0===strpos($e,$id.' ');})) ? "CHECK-NEEDED" : "SOURCE-CONTROL-PRESENT") . "\n";
}
foreach($errors as $error)fwrite(STDERR,$error."\n");
if($errors)exit(1);
echo "$tested / 40 distinct governing-plan source probes PASS (not runtime acceptance)\n";
