#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE="$ROOT/11-reels-foundation"
PACKAGE_ROOT="reels-foundation-11"
OUT="${1:-$ROOT/packages/reels-foundation-11-1.2.0-rc3.zip}"
case "$OUT" in /*) ;; *) OUT="$ROOT/$OUT";; esac
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/$PACKAGE_ROOT" "$(dirname "$OUT")"
cp -R "$SOURCE"/. "$TMP/$PACKAGE_ROOT/"
# Stable release timestamp keeps independent builds byte-reproducible.
find "$TMP" -exec touch -t 202608120000.00 {} +
rm -f "$OUT" "$OUT.sha256"
( cd "$TMP"; LC_ALL=C find "$PACKAGE_ROOT" -type f -print | LC_ALL=C sort | zip -X -q "$OUT" -@ )
( cd "$(dirname "$OUT")"; sha256sum "$(basename "$OUT")" > "$(basename "$OUT").sha256" )
echo "$OUT"
