#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
P="$ROOT/11-reels-foundation"
find "$P" -type f -name '*.php' -print0 | sort -z | xargs -0 -n1 php -l >/dev/null
node --check "$P/assets/js/rsv.js"
node --check "$P/assets/js/rsv-top20.js"
php "$ROOT/tests/unit-state-machine.php"
php "$ROOT/tests/unit-helpers.php"
php "$ROOT/tests/forensic-contracts.php"
php "$ROOT/tests/top20-contracts.php"
php "$ROOT/tests/rc5-hardening-contracts.php"
php "$ROOT/tests/rc6-forty-review-contracts.php"
php "$ROOT/tests/current-plan-contracts.php"
php "$ROOT/tests/future30-contracts.php"
php "$ROOT/tests/fresh40-hardening-contracts.php"
php "$ROOT/tests/fresh20-hardening-contracts.php"
php "$ROOT/tests/fresh20-second-cycle-contracts.php"
php "$ROOT/tests/fresh20-third-cycle-contracts.php"
php "$ROOT/tests/fresh40-oct2026-contracts.php"
php "$ROOT/tests/fresh40-cycle2-quiz-series.php"
php "$ROOT/tests/fresh40-cycle2-ai-context.php"
php "$ROOT/tests/fresh40-cycle2-sequential-audit.php"
php "$ROOT/tests/rc8-governing-plan-guards.php"
php "$ROOT/tests/rc9-language-quiz-behavior.php"
php "$ROOT/tests/rc10-current-public-reference-guards.php"
php "$ROOT/tests/rc10-forty-governing-source-probes.php"
bash "$ROOT/tests/static-contracts.sh"
A="$(mktemp -d)"; B="$(mktemp -d)"; trap 'rm -rf "$A" "$B"' EXIT
bash "$ROOT/tools/build-package.sh" "$A/file11.zip" >/dev/null
bash "$ROOT/tools/build-package.sh" "$B/file11.zip" >/dev/null
cmp "$A/file11.zip" "$B/file11.zip"
unzip -t "$A/file11.zip" >/dev/null
test "$(unzip -Z1 "$A/file11.zip" | cut -d/ -f1 | sort -u)" = "reels-foundation-11"
rm -rf "$A/extract"; mkdir -p "$A/extract"; unzip -q "$A/file11.zip" -d "$A/extract"
diff -ru "$P" "$A/extract/reels-foundation-11"
echo "all File 11 Future30 1.2.0-rc9 automated checks PASS"
