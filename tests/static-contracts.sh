#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
P="$ROOT/11-reels-foundation"
grep -Fq 'Version: 1.0.0-rc2' "$P/11-reels-foundation.php"
grep -Fq "define( 'RSV_SCHEMA_VERSION', '1.1.0' )" "$P/11-reels-foundation.php"
grep -Fq 'class RSV_Access' "$P/includes/class-rsv-access.php"
grep -Fq 'class RSV_Ranking' "$P/includes/class-rsv-ranking.php"
grep -Fq "share_token_hash" "$P/includes/class-rsv-db.php"
grep -Fq "report_events" "$P/includes/class-rsv-db.php"
grep -Fq "legal_holds" "$P/includes/class-rsv-db.php"
grep -Fq "lease_token" "$P/includes/class-rsv-jobs.php"
grep -Fq "ON DUPLICATE KEY UPDATE" "$P/includes/class-rsv-reels.php"
grep -Fq "RSV_State_Machine::assert" "$P/includes/class-rsv-reels.php"
grep -Fq "rsv_entitlement_check" "$P/includes/class-rsv-access.php"
grep -Fq "wp_privacy_personal_data_exporters" "$P/includes/class-rsv-privacy.php"
grep -Fq "Cache-Control: private, no-store" "$P/includes/class-rsv-helpers.php"
grep -Fq "Idempotency-Key" "$P/assets/js/rsv.js"
grep -Fq "data-rsv-load-more" "$P/assets/js/rsv.js"
grep -Fq "pointerdown" "$P/assets/js/rsv.js"
grep -Fq "navigator.connection" "$P/assets/js/rsv.js"
grep -Fq "pagehide" "$P/assets/js/rsv.js"
grep -Fq "safeOrigin" "$P/assets/js/rsv.js"
grep -Fq "prefers-reduced-motion" "$P/assets/css/rsv.css"
grep -Fq "RSV_ALLOW_DESTRUCTIVE_PURGE" "$P/uninstall.php"
test "$(grep -o "'F11-FR-[0-9][0-9][0-9]'" "$P/includes/class-rsv-contracts.php" | sort -u | wc -l)" -eq 15
test "$(grep -o "'F11-NFR-[0-9][0-9][0-9]'" "$P/includes/class-rsv-contracts.php" | sort -u | wc -l)" -eq 10
! grep -R "if ( ! items )" "$P"
! grep -R "location.href = '/'" "$P/assets/js/rsv.js"
! grep -R "postMessage.*'\*'" "$P/assets/js/rsv.js"
! grep -RIE "(api[_-]?key|secret|token)[[:space:]]*=[[:space:]]*['\"][A-Za-z0-9_\-]{16,}" "$P" --include='*.php' --include='*.js'
! find "$P" -type l -print -quit | grep -q .
echo "static contracts PASS"
