#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE="$ROOT/11-reels-foundation"
PACKAGE_ROOT="reels-foundation-11"
OUT="${1:-$ROOT/packages/reels-foundation-11-1.1.0-rc5.zip}"
case "$OUT" in /*) ;; *) OUT="$ROOT/$OUT";; esac
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/$PACKAGE_ROOT" "$(dirname "$OUT")"
cp -R "$SOURCE"/. "$TMP/$PACKAGE_ROOT/"
find "$TMP" -exec touch -t 202608070917.00 {} +
rm -f "$OUT" "$OUT.sha256"
( cd "$TMP"; LC_ALL=C find "$PACKAGE_ROOT" -type f -print | LC_ALL=C sort | zip -X -q "$OUT" -@ )
( cd "$(dirname "$OUT")"; sha256sum "$(basename "$OUT")" > "$(basename "$OUT").sha256" )
echo "$OUT"
