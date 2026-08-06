#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
P="$ROOT/11-reels-foundation"
grep -R "Version: 1.0.0-rc1" "$P/11-reels-foundation.php" >/dev/null
grep -R "define( 'RSV_VERSION', '1.0.0-rc1' )" "$P/11-reels-foundation.php" >/dev/null
grep -R "class RSV_File10" "$P/includes" >/dev/null
grep -R "VWLB_Videos::interact" "$P/includes/class-rsv-file10.php" >/dev/null
grep -R "VWLB_Videos::progress" "$P/includes/class-rsv-file10.php" >/dev/null
grep -R "duration_seconds" "$P/includes/class-rsv-file10.php" >/dev/null
grep -R "60" "$P/includes/class-rsv-file10.php" >/dev/null
grep -R "600" "$P/includes/class-rsv-file10.php" >/dev/null
grep -R "wp_privacy_personal_data_exporters" "$P/includes" >/dev/null
grep -R "Cache-Control: private, no-store" "$P/includes" >/dev/null
grep -R "Idempotency-Key" "$P/assets/js/rsv.js" >/dev/null
grep -R "rsv_rate_limited" "$P/includes" >/dev/null
grep -R "RSV_State_Machine" "$P/includes" >/dev/null
grep -R "RSV_Contracts::REQUIREMENTS" "$P/includes/class-rsv-diagnostics.php" >/dev/null
grep -R "RSV_ALLOW_DESTRUCTIVE_PURGE" "$P/uninstall.php" >/dev/null
grep -R "prefers-reduced-motion" "$P/assets" >/dev/null
grep -R "data-rsv-prev" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -R "data-rsv-next" "$P/includes/class-rsv-frontend.php" >/dev/null
grep -R "data-rsv-pause" "$P/includes/class-rsv-frontend.php" >/dev/null
test "$(grep -o "'F11-FR-[0-9][0-9][0-9]'" "$P/includes/class-rsv-contracts.php" | sort -u | wc -l)" -eq 15
test "$(grep -o "'F11-NFR-[0-9][0-9][0-9]'" "$P/includes/class-rsv-contracts.php" | sort -u | wc -l)" -eq 10
! grep -RIE "(api[_-]?key|secret|token)[[:space:]]*=[[:space:]]*['\"][A-Za-z0-9_\-]{16,}" "$P" --include='*.php' --include='*.js'
echo "static contracts PASS"
