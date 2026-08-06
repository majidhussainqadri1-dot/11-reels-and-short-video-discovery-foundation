# Changelog

## 1.0.0-rc2 — 2026-08-06

- Fixed rc1 runtime defects in feed rendering and create-page semantics.
- Replaced unsigned/inconsistent cursors with signed sort-aware keyset cursors.
- Added access enforcement for public, unlisted-token, member and entitled Reels.
- Removed internal numeric user/video identifiers from public DTOs.
- Added authoritative cover, language, caption, File 10 rights/consent and duration publication gates.
- Added metadata update, public impression, report appeal and complete moderation routes.
- Made private progress concurrency-safe, bounded and event-throttled.
- Added report history, legal holds, dependency inbox and leased outbox delivery.
- Added nested canonical pages, rewrite routes, Safe Mode diagnostics and repair controls.
- Added load-more, lazy playback, swipe/pointer navigation, offline/data-saver, break cues and accessible reporting.
- Strengthened privacy exporter/eraser pagination and anonymized moderation retention.
- Expanded automated QA and deterministic clean-extract packaging.
