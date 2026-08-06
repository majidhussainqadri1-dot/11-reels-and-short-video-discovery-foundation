#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="${1:-$ROOT/packages/11-reels-foundation-1.0.0-rc2.zip}"
if [[ "$OUT" != /* ]]; then OUT="$PWD/$OUT"; fi
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
mkdir -p "$(dirname "$OUT")" "$TMP/11-reels-foundation"
cp -a "$ROOT/11-reels-foundation/." "$TMP/11-reels-foundation/"
find "$TMP/11-reels-foundation" -type f -exec touch -t 202608060000.00 {} +
(
  cd "$TMP"
  find 11-reels-foundation -type f ! -name 'MANIFEST.sha256' -print0 | LC_ALL=C sort -z | xargs -0 sha256sum > 11-reels-foundation/MANIFEST.sha256
  LC_ALL=C find 11-reels-foundation -type f -print | sort | zip -X -q "$OUT" -@
)
unzip -t "$OUT" >/dev/null
sha256sum "$OUT" > "$OUT.sha256"
printf '%s\n' "$OUT"
