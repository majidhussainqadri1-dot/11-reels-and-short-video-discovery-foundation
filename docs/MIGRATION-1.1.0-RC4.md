# Migration Plan — 1.1.0-rc4

Main schema `1.2.0`; Top-20 schema `1.0.0`. New tables: Reel context, Stories, Highlights, Highlight items, Responses, Preferences and aggregate Value signals.

Existing Reel/media IDs and URLs remain canonical. No File 10 data is copied. Existing published Reels lacking reviewed source label, safety summary and caption/transcript evidence remain available to authorized owner/operators for repair but are fail-closed publicly.

Required staging sequence: verified backup/restore; dry-run inventory; idempotent schema install; source/safety repair queue; cache purge/File 26 reindex/File 21/25 refresh; public/owner/minor/guest matrix; rollback preserving post-cutover data; explicit Founder acceptance. No dual-write or raw-media migration.
