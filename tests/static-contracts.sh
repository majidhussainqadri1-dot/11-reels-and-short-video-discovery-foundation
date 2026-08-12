#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
P="$ROOT/11-reels-foundation"

grep -F "Version: 1.2.0-rc3" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_VERSION', '1.2.0-rc3' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_SCHEMA_VERSION', '1.2.0' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_CONTRACT_VERSION', 9 )" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-current-plan.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "trait-rsv-future30-storage.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "trait-rsv-future30-feature-write.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "trait-rsv-future30-user-features.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "trait-rsv-future30-experience.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-future30.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-fresh20-hardening.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-fresh-review-hardening.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-fresh-review-public-minimization.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "new RSV_Future30" "$P/includes/class-rsv-plugin.php" >/dev/null
grep -F "RSV_Future30::install" "$P/includes/class-rsv-plugin.php" >/dev/null

grep -F "smc_membership_assertions" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "rsv_identity_contract_compatible" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "duration >= 60" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "duration <= 600" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "rsv_patient_reuse_consent_still_valid" "$P/includes/trait-rsv-top20-responses.php" >/dev/null
grep -F "mandatory_youth_mode" "$P/includes/trait-rsv-top20-context.php" >/dev/null
grep -F "rsv_caption_track_required" "$P/includes/trait-rsv-top20-context.php" >/dev/null
grep -F "value_signal_receipts" "$P/includes/class-rsv-top20.php" >/dev/null
grep -F "const INSIGHT_MINIMUM = 5" "$P/includes/class-rsv-top20.php" >/dev/null
grep -F "StoryExpired" "$P/includes/trait-rsv-top20-stories.php" >/dev/null
grep -F "JSON_HEX_TAG" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "public static function contract_compatible" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "wp_privacy_personal_data_exporters" "$P/includes/class-rsv-privacy.php" >/dev/null
grep -F "Cache-Control: private, no-store" "$P/includes/class-rsv-helpers.php" >/dev/null
grep -F "RSV_ALLOW_DESTRUCTIVE_PURGE" "$P/uninstall.php" >/dev/null
grep -RF "prefers-reduced-motion" "$P/assets" >/dev/null

grep -F "const SCHEMA_VERSION = '1.0.0'" "$P/includes/class-rsv-future30.php" >/dev/null
grep -F "const CONTRACT_VERSION = 1" "$P/includes/class-rsv-future30.php" >/dev/null
grep -F "const PLAN_REVISION = '2026-08-12'" "$P/includes/class-rsv-future30.php" >/dev/null
grep -F "F11-FUT-001" "$P/includes/class-rsv-contracts.php" >/dev/null
grep -F "F11-FUT-030" "$P/includes/class-rsv-contracts.php" >/dev/null
grep -RF "future_objects" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "future_edges" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "future_user_state" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "generic_public_allowed" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "public_projection" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_public_ref_valid" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_remix_allowed" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_file10_derivative_valid" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_coauthor_consent_valid" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_peer_review_attestation_valid" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_voice_consent_valid" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_file10_track_ref_valid" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv_future30_transcript_ref_public_valid" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "at most nine linked language versions" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "Authoritative Reel duration" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "execute_ai'=>false" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "auto_publish'=>false" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "viewer_identity_exposed'=>false" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "payment_or_donation_reason'=>false" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "feed_allows_row" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -F "RSV_Future30::feed_allows_row" "$P/includes/class-rsv-repository.php" >/dev/null
grep -RF "rsv-a11y-" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -RF "rsv-a11y-reduced-motion" "$P/includes" --include='class-rsv-future30.php' --include='trait-rsv-future30-*.php' >/dev/null
grep -F "wp_add_inline_script" "$P/includes/class-rsv-future30.php" >/dev/null
grep -F "future_user_state" "$P/uninstall.php" >/dev/null

H="$P/includes/class-rsv-fresh20-hardening.php"
grep -F "class RSV_Fresh20_Hardening" "$H" >/dev/null
grep -F "rsv_series_invalid" "$H" >/dev/null
grep -F "rsv_learning_step_invalid" "$H" >/dev/null
grep -F "rsv_evidence_grade_invalid" "$H" >/dev/null
grep -F "rsv_remix_self_invalid" "$H" >/dev/null
grep -F "rsv_template_media_recipe_invalid" "$H" >/dev/null
grep -F "rsv_peer_review_conflict" "$H" >/dev/null
grep -F "rsv_translation_language_mismatch" "$H" >/dev/null
grep -F "ReelQuizAttemptUpdated" "$H" >/dev/null
grep -F "ReelStudyCollectionUpdated" "$H" >/dev/null
grep -F "ReelPrivateNoteUpdated" "$H" >/dev/null
grep -F "ai-citation-current" "$H" >/dev/null
grep -F "knowledge-graph-evidence-current" "$H" >/dev/null
grep -F "ReelFeedPreferencesUpdated" "$H" >/dev/null
grep -F "ReelAccessibilityPreferencesUpdated" "$H" >/dev/null
grep -F "render_safe_tools" "$H" >/dev/null
grep -F "FOR UPDATE" "$H" >/dev/null

R="$P/includes/class-rsv-fresh-review-hardening.php"
grep -F "delete_idempotency_snapshot" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "ReelTranscriptProjectionReady" "$P/includes/trait-rsv-future30-feature-write.php" >/dev/null
grep -F "ReelFuturePrivateStateErased" "$P/includes/class-rsv-future30-privacy-integrity.php" >/dev/null
grep -F "ReelLanguageLinksReconciled" "$R" >/dev/null
grep -F "rsv_quiz_unknown_field" "$R" >/dev/null
grep -F "authoritative_duration_seconds" "$R" >/dev/null
grep -F "class RSV_Fresh_Review_Public_Minimization" "$P/includes/class-rsv-fresh-review-public-minimization.php" >/dev/null

! grep -RIE "(api[_-]?key|secret|token)[[:space:]]*=[[:space:]]*['\"][A-Za-z0-9_\-]{16,}" "$P" --include='*.php' --include='*.js'
! grep -R "postMessage" "$P/assets/js/rsv.js" | grep -F ", '*')"
! grep -R "if ( ! items )" "$P"
! grep -F "status || document.body" "$P/assets/js/rsv.js"
! grep -F ":root{" "$P/assets/css/rsv.css"
! grep -F ":root" "$P/assets/css/rsv-top20.css"

echo "static contracts Future30 rc3 PASS"
