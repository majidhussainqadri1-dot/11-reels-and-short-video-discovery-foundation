#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
find "$ROOT/11-reels-foundation" -type f -name '*.php' -print0 | sort -z | xargs -0 -n1 php -l >/dev/null
node --check "$ROOT/11-reels-foundation/assets/js/rsv.js"
php "$ROOT/tests/unit-core.php"
php "$ROOT/tests/unit-access.php"
bash "$ROOT/tests/static-contracts.sh"
A="$(mktemp -d)";B="$(mktemp -d)";C="$(mktemp -d)";trap 'rm -rf "$A" "$B" "$C"' EXIT
bash "$ROOT/tools/build-package.sh" "$A/file11.zip" >/dev/null
bash "$ROOT/tools/build-package.sh" "$B/file11.zip" >/dev/null
cmp "$A/file11.zip" "$B/file11.zip"
unzip -t "$A/file11.zip" >/dev/null
test "$(unzip -Z1 "$A/file11.zip" | cut -d/ -f1 | sort -u)" = "11-reels-foundation"
unzip -q "$A/file11.zip" -d "$C"
find "$C/11-reels-foundation" -type f -name '*.php' -print0 | sort -z | xargs -0 -n1 php -l >/dev/null
node --check "$C/11-reels-foundation/assets/js/rsv.js"
(cd "$C" && sha256sum --check 11-reels-foundation/MANIFEST.sha256 >/dev/null)
printf 'all File 11 automated checks PASS\n'
