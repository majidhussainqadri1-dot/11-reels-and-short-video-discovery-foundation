#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
P="$ROOT/11-reels-foundation"
grep -F "Version: 1.0.0-rc2" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_VERSION', '1.0.0-rc2' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "define( 'RSV_SCHEMA_VERSION', '1.1.0' )" "$P/11-reels-foundation.php" >/dev/null
grep -F "smc_membership_assertions" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "smc_publishing_assertions" "$P/includes/class-rsv-security.php" >/dev/null
grep -F "VWLB_Videos::interact" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "VWLB_Videos::progress" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "duration >= 60" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "duration <= 600" "$P/includes/class-rsv-file10.php" >/dev/null
grep -F "wp_privacy_personal_data_exporters" "$P/includes/class-rsv-privacy.php" >/dev/null
grep -F "Cache-Control: private, no-store" "$P/includes/class-rsv-helpers.php" >/dev/null
grep -F "Idempotency-Key" "$P/assets/js/rsv.js" >/dev/null
grep -F "actor_scope_window" "$P/includes/class-rsv-db.php" >/dev/null
grep -F "RSV_State_Machine" "$P/includes/class-rsv-reels.php" >/dev/null
grep -F "RSV_ALLOW_DESTRUCTIVE_PURGE" "$P/uninstall.php" >/dev/null
grep -RF "prefers-reduced-motion" "$P/assets" >/dev/null
grep -F "data-rsv-prev" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "data-rsv-next" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "data-rsv-pause" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "migrate_history" "$P/includes/class-rsv-migration.php" >/dev/null
grep -F "status IN ('pending','retry')" "$P/includes/class-rsv-jobs.php" >/dev/null
! grep -RIE "(api[_-]?key|secret|token)[[:space:]]*=[[:space:]]*['\"][A-Za-z0-9_\-]{16,}" "$P" --include='*.php' --include='*.js'
! grep -R "postMessage" "$P/assets/js/rsv.js" | grep -F ", '*')"
! grep -R "if ( ! items )" "$P"
grep -F 'use ( $report_id, $decision, $reason, $expected_version, $public_id )' "$P/includes/class-rsv-reels.php" >/dev/null
grep -F "data-rsv-global-status" "$P/assets/js/rsv.js" >/dev/null
grep -F "Next page of Reels" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -F "appellant_id bigint unsigned" "$P/includes/class-rsv-db.php" >/dev/null
grep -F "rsv_restore_media_invalid" "$P/includes/class-rsv-reels.php" >/dev/null
grep -F "rp.appellant_id=%d" "$P/includes/class-rsv-privacy.php" >/dev/null
! grep -F "status || document.body" "$P/assets/js/rsv.js"
echo "static contracts PASS"
