#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE="$ROOT/11-reels-foundation"
OUT="${1:-$ROOT/packages/11-reels-foundation-1.0.0-rc1.zip}"
case "$OUT" in /*) ;; *) OUT="$ROOT/$OUT";; esac
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/11-reels-foundation" "$(dirname "$OUT")"
cp -R "$SOURCE"/. "$TMP/11-reels-foundation/"
find "$TMP" -exec touch -t 202608060000.00 {} +
rm -f "$OUT" "$OUT.sha256"
( cd "$TMP"; LC_ALL=C find 11-reels-foundation -type f -print | LC_ALL=C sort | zip -X -q "$OUT" -@ )
( cd "$(dirname "$OUT")"; sha256sum "$(basename "$OUT")" > "$(basename "$OUT").sha256" )
echo "$OUT"
