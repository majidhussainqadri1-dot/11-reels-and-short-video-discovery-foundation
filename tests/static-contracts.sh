#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
P="$ROOT/11-reels-foundation"
grep -F "Version: 1.1.0-rc4" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_VERSION', '1.1.0-rc4' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_SCHEMA_VERSION', '1.2.0' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_CONTRACT_VERSION', 3 )" "$P/11-reels-foundation.php" >/dev/null
grep -F "class-rsv-top20.php" "$P/11-reels-foundation.php" >/dev/null
grep -F "new RSV_Top20" "$P/includes/class-rsv-plugin.php" >/dev/null
grep -F "RSV_Top20::install" "$P/includes/class-rsv-plugin.php" >/dev/null
grep -F "class RSV_Top20" "$P/includes/class-rsv-top20.php" >/dev/null
grep -RF "MAX_STORY_HOURS = 24" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "reel_context" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "highlight_items" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "patient_reuse_consent" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "rsv_patient_reuse_consent_valid" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "natural_stop_every" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "mandatory_youth_mode" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "history_cursor" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "source_opens" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "meaningful_comments" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "rsv_source_safety_required" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -RF "rsv_caption_track_required" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -F 'return $base_allowed && $filtered' "$P/includes/class-rsv-security.php" >/dev/null
grep -F "rsv_identity_contract_compatible" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "rsv_can_view_reel" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "stories-status" "$P/includes/class-rsv-integrations.php" >/dev/null
grep -F "attributed-responses" "$P/includes/class-rsv-integrations.php" >/dev/null
grep -F "VWLB_Videos', 'download_url" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "interaction_aggregate" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "youth_safe" "$P/includes/class-rsv-repository.php" >/dev/null
grep -F "LIMIT 50" "$P/includes/class-rsv-repository.php" >/dev/null
grep -F "data-rsv-wellbeing-choice" "$P/assets/js/rsv-top20.js" >/dev/null
grep -F "natural-stop" "$P/assets/js/rsv-top20.js" >/dev/null
grep -F -- "--sabri-color-primary" "$P/assets/css/rsv-top20.css" >/dev/null
grep -F "Why this Reel?" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "data-rsv-topic" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "trace_id" "$P/assets/js/rsv.js" >/dev/null
grep -F "rsv-trace" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F -- "--sabri-color-primary" "$P/assets/css/rsv.css" >/dev/null
! grep -F ":root{" "$P/assets/css/rsv.css"
! grep -F ":root" "$P/assets/css/rsv-top20.css"
grep -F "smc_membership_assertions" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "VWLB_Videos::interact" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "duration >= 60" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "duration <= 600" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "wp_privacy_personal_data_exporters" "$P/includes/class-rsv-privacy.php" >/dev/null
grep -RF "wp_privacy_personal_data_exporters" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -F "Cache-Control: private, no-store" "$P/includes/class-rsv-helpers.php" >/dev/null
grep -F "Idempotency-Key" "$P/assets/js/rsv.js" >/dev/null
grep -F "actor_scope_window" "$P/includes/class-rsv-db.php" >/dev/null
grep -F "RSV_State_Machine" "$P/includes/class-rsv-reels.php" >/dev/null
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
grep -RF "public static function required_tables" "$P/includes" --include='class-rsv-top20.php' --include='trait-rsv-top20-*.php' >/dev/null
grep -F "public static function contract_compatible" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F 'null !== $user_id' "$P/includes/class-rsv-security.php" >/dev/null
grep -F "published_without_context" "$P/includes/class-rsv-diagnostics.php" >/dev/null
grep -F "Project-Id-Version: Reels and Short Video Discovery 1.1.0-rc4" "$P/languages/reels-short-video-discovery-1.1.0-rc4.pot" >/dev/null
echo "static contracts RC4 PASS"
