# Changelog

## 1.0.0-rc2 — 2026-08-06

### Canonical architecture
- Removed the second legacy `reels/` runtime from the release branch.
- Enforced File 11 Reel ownership, File 10 media ownership and File 00 identity/publishing ownership.
- Added contract/schema/version separation and fail-closed dependency diagnostics.

### Security and integrity
- Added opaque Reel/report/view/event IDs and signed sort-bound cursors.
- Added current File 00 membership/publishing assertions, object ownership, guardian, suspension and step-up checks.
- Added optimistic concurrency, atomic rate limiting, idempotency and transactional audit/outbox evidence.
- Added server-issued view sessions and server-bounded progress/completion.

### Product and accessibility
- Added public vertical feed, permanent Reel route, previous/next, touch swipe and keyboard controls.
- Added explicit autoplay opt-in, reduced-motion/data-saver behavior, pause controls and no forced infinite scrolling.
- Added no-JavaScript next-page continuation, safe live regions and RTL/logical CSS.
- Added caption tracks, error/degraded states and File 10 secure download adapter.

### Moderation and privacy
- Added reports, restriction/removal/restoration, reporter/owner appeals and appellant attribution.
- Added private history reset/export/erasure, report holds/redaction and thresholded creator insights.
- Added bounded retention cleanup for impressions, sessions, rate limits and idempotency records.

### Migration and operations
- Added checkpointed legacy Reel/history migration, quarantine, reconciliation and rollback-preserving cutover.
- Added Safe Mode, diagnostics, repair, queue retry/backoff/dead-letter and operational health data.
- Added POT extraction, deterministic package, checksum, source parity and PHP 8.1/8.3/8.4 CI.

## 1.0.0-rc1
Initial plan-mapped release candidate. Superseded after fresh forensic review identified authorization, visibility, progress-integrity, moderation, privacy, migration, route, accessibility and QA gaps.
