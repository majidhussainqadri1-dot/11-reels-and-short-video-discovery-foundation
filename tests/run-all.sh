#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
find "$ROOT/11-reels-foundation" -type f -name '*.php' -print0 | sort -z | xargs -0 -n1 php -l >/dev/null
node --check "$ROOT/11-reels-foundation/assets/js/rsv.js"
node --check "$ROOT/11-reels-foundation/assets/js/rsv-top20.js"
php "$ROOT/tests/unit-state-machine.php"
php "$ROOT/tests/unit-helpers.php"
php "$ROOT/tests/forensic-contracts.php"
php "$ROOT/tests/top20-contracts.php"
php "$ROOT/tests/rc5-hardening-contracts.php"
bash "$ROOT/tests/static-contracts.sh"
A="$(mktemp -d)"; B="$(mktemp -d)"; trap 'rm -rf "$A" "$B"' EXIT
bash "$ROOT/tools/build-package.sh" "$A/file11.zip" >/dev/null
bash "$ROOT/tools/build-package.sh" "$B/file11.zip" >/dev/null
cmp "$A/file11.zip" "$B/file11.zip"
unzip -t "$A/file11.zip" >/dev/null
test "$(unzip -Z1 "$A/file11.zip" | cut -d/ -f1 | sort -u)" = "reels-foundation-11"
rm -rf "$A/extract"; mkdir -p "$A/extract"; unzip -q "$A/file11.zip" -d "$A/extract"
diff -ru "$ROOT/11-reels-foundation" "$A/extract/reels-foundation-11"
echo "all File 11 RC5 automated checks PASS"
