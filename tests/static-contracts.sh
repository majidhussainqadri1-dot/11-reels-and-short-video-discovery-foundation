#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
P="$ROOT/11-reels-foundation"
grep -F "Version: 1.1.0-rc7" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_VERSION', '1.1.0-rc7' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_SCHEMA_VERSION', '1.2.0' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_CONTRACT_VERSION', 6 )" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-top20.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-current-plan.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "new RSV_Top20" "$P/includes/class-rsv-plugin.php" >/dev/null
grep -F "new RSV_Current_Plan" "$P/includes/class-rsv-plugin.php" >/dev/null
grep -F "RSV_Top20::install" "$P/includes/class-rsv-plugin.php" >/dev/null
grep -F "class RSV_Top20" "$P/includes/class-rsv-top20.php" >/dev/null
grep -F "const SCHEMA_VERSION = '1.1.0'" "$P/includes/class-rsv-top20.php" >/dev/null
grep -RF "MAX_STORY_HOURS = 24" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "reel_context" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "highlight_items" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "rsv_patient_reuse_consent_still_valid" "$P/includes" --include='trait-rsv-top20-responses.php' >/dev/null
grep -RF "mandatory_youth_mode" "$P/includes" --include='trait-rsv-top20-context.php' >/dev/null
grep -RF "history_cursor" "$P/includes" --include='trait-rsv-top20-responses.php' >/dev/null
grep -RF "rsv_caption_track_required" "$P/includes" --include='trait-rsv-top20-context.php' >/dev/null
grep -F 'return $base_allowed && $filtered' "$P/includes/class-rsv-security.php" >/dev/null
grep -F "rsv_identity_contract_compatible" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "stories-status" "$P/includes/class-rsv-integrations.php" >/dev/null
grep -F "attributed-responses" "$P/includes/class-rsv-integrations.php" >/dev/null
grep -F "duration >= 60" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "duration <= 600" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "youth_safe" "$P/includes/class-rsv-repository.php" >/dev/null
grep -F "LIMIT 50" "$P/includes/class-rsv-repository.php" >/dev/null
grep -F "data-rsv-wellbeing-choice" "$P/assets/js/rsv-top20.js" >/dev/null
grep -F "natural-stop" "$P/assets/js/rsv-top20.js" >/dev/null
grep -F -- "--sabri-color-primary" "$P/assets/css/rsv-top20.css" >/dev/null
grep -F "Why this Reel?" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "trace_id" "$P/assets/js/rsv.js" >/dev/null
! grep -F ":root{" "$P/assets/css/rsv.css"
! grep -F ":root" "$P/assets/css/rsv-top20.css"
grep -F "smc_membership_assertions" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "wp_privacy_personal_data_exporters" "$P/includes/class-rsv-privacy.php" >/dev/null
grep -F "public static function privacy_exporters" "$P/includes/trait-rsv-top20-privacy-integration.php" >/dev/null
grep -F "Cache-Control: private, no-store" "$P/includes/class-rsv-helpers.php" >/dev/null
grep -F "Idempotency-Key" "$P/assets/js/rsv.js" >/dev/null
grep -F "actor_scope_window" "$P/includes/class-rsv-db.php" >/dev/null
grep -F "RSV_ALLOW_DESTRUCTIVE_PURGE" "$P/uninstall.php" >/dev/null
grep -RF "prefers-reduced-motion" "$P/assets" >/dev/null
grep -F "migrate_history" "$P/includes/class-rsv-migration.php" >/dev/null
grep -F "status IN ('pending','retry')" "$P/includes/class-rsv-jobs.php" >/dev/null
! grep -RIE "(api[_-]?key|secret|token)[[:space:]]*=[[:space:]]*['\"][A-Za-z0-9_\-]{16,}" "$P" --include='*.php' --include='*.js'
! grep -R "postMessage" "$P/assets/js/rsv.js" | grep -F ", '*')"
! grep -R "if ( ! items )" "$P"
! grep -F "status || document.body" "$P/assets/js/rsv.js"
grep -F "data-rsv-global-status" "$P/assets/js/rsv.js" >/dev/null
grep -F "Next page of Reels" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "appellant_id bigint unsigned" "$P/includes/class-rsv-db.php" >/dev/null
grep -F "rsv_restore_media_invalid" "$P/includes/class-rsv-reels.php" >/dev/null
grep -F "rp.appellant_id=%d" "$P/includes/class-rsv-privacy.php" >/dev/null
grep -RF "public static function required_tables" "$P/includes" --include='class-rsv-top20.php' >/dev/null
grep -F "public static function contract_compatible" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F 'null !== $user_id' "$P/includes/class-rsv-security.php" >/dev/null
grep -F "published_without_context" "$P/includes/class-rsv-diagnostics.php" >/dev/null
# Inherited RC5 hardening contracts.
grep -F "Do not run object-dependent publication validation before authorization" "$P/includes/trait-rsv-top20-experience.php" >/dev/null
grep -F "rsv_response_finalize_failed" "$P/includes/trait-rsv-top20-responses.php" >/dev/null
grep -F "rsv_story_publish_evidence_failed" "$P/includes/trait-rsv-top20-stories.php" >/dev/null
grep -F "rsv_highlight_evidence_failed" "$P/includes/trait-rsv-top20-stories.php" >/dev/null
grep -F "array_key_exists( 'history_paused', \$data )" "$P/includes/trait-rsv-top20-context.php" >/dev/null
grep -F "LIMIT %d OFFSET %d" "$P/includes/trait-rsv-top20-privacy-integration.php" >/dev/null
grep -F "render_story_form" "$P/includes/trait-rsv-top20-experience.php" >/dev/null
grep -F "render_highlight_form" "$P/includes/trait-rsv-top20-experience.php" >/dev/null
grep -F "rel=\"noopener noreferrer nofollow\"" "$P/includes/trait-rsv-top20-experience.php" >/dev/null
# Inherited RC6 forty-review corrective contracts.
grep -F "'guardian_ok'          => ! \$guardian_required || \$guardian_verified" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "is_callable( array( 'VWLB_Videos', 'progress' ) )" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "defined( 'VWLB_Contracts::EVENT_VERSION' )" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "const INSIGHT_MINIMUM = 5" "$P/includes/class-rsv-top20.php" >/dev/null
grep -F "value_signal_receipts" "$P/includes/class-rsv-top20.php" >/dev/null
grep -F "StoryExpired" "$P/includes/trait-rsv-top20-stories.php" >/dev/null
grep -F "JSON_HEX_TAG" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "const PROVIDER_VERSION = 3" "$P/includes/class-rsv-integrations.php" >/dev/null
# RC7 rewritten-plan alignment.
grep -F "const REVISION = '2026-08-11'" "$P/includes/class-rsv-current-plan.php" >/dev/null
grep -F "F11-CEN-01" "$P/includes/class-rsv-contracts.php" >/dev/null
grep -F "CV-285" "$P/includes/class-rsv-contracts.php" >/dev/null
grep -F "child-safety" "$P/includes/class-rsv-contracts.php" >/dev/null
grep -F "Educational content only." "$P/includes/class-rsv-current-plan.php" >/dev/null
grep -F "normalize_report_reason" "$P/includes/class-rsv-rest.php" >/dev/null
grep -F "donation status" "$P/includes/class-rsv-integrations.php" >/dev/null
echo "static contracts RC7 PASS"
