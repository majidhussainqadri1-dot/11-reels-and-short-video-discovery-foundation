# File 11 Requirements Traceability — RC2

| Requirement | Implementation evidence | Automated evidence |
|---|---|---|
| F11-FR-001 Reel create | `RSV_Reels::create`, File 10 adapter, create route/form | forensic/static/package parity |
| F11-FR-002 Duration enforcement | `RSV_File10::validate_for_reel`, 60–600 verified seconds | static contract |
| F11-FR-003 Educational taxonomy | `RSV_Contracts::TOPICS`, server enum validation | forensic requirement count |
| F11-FR-004 Review/publish | Reel state machine, transactional submit/publish, File 10 gates | state-machine + forensic |
| F11-FR-005 Vertical feed | repository feed, swipe/buttons/keyboard/focus/history | JS syntax + static |
| F11-FR-006 Autoplay policy | explicit opt-in, muted, visibility, reduced-motion, data-saver | static + JS syntax |
| F11-FR-007 Ranking | source/rights/consent/safety base score + bounded completion/rapid-swipe signals | code/static review |
| F11-FR-008 Interactions | File 10 Like/Dislike/Save; shared comments/follow/download adapters; reports native | static File 10 bridge checks |
| F11-FR-009 Progress/history | server view sessions, bounded progress, private history/reset/export/erasure | forensic/privacy/static |
| F11-FR-010 Deep links | opaque `/reel/{id}/{slug}`, video/profile/topic context, previous/next | static/forensic |
| F11-FR-011 Moderation | structured reports, decisions, restrict/remove/restore, reporter/owner appeals | state machine + forensic |
| F11-FR-012 Captions/accessibility | File 10 tracks, keyboard/non-gesture controls, labels/focus/RTL | static and staging matrix |
| F11-FR-013 Offline/error states | media unavailable state, offline pause, restricted 404, processing/Safe Mode | JS/static and staging matrix |
| F11-FR-014 Creator insights | aggregate-only metrics, privacy threshold, no viewer IDs | forensic/privacy review |
| F11-FR-015 Anti-abuse | atomic rate limits, idempotency, signed cursors, plausible progress, rapid-swipe signal | unit/helper/forensic |
| F11-NFR-001 Authorization | current File 00 claims + object/visibility checks + opaque IDs | forensic IDOR contracts; staging roles |
| F11-NFR-002 Privacy | minimization, bounded retention, exporter/eraser, hold/redaction | forensic + staging privacy |
| F11-NFR-003 Reliability | transactions, idempotency, outbox retry/backoff/dead-letter, reconciliation | static + staging failure injection |
| F11-NFR-004 Performance | indexed normalized tables, bounded cursor queries, lazy media, background jobs | static; staging p75/p95 required |
| F11-NFR-005 Accessibility | keyboard, focus, 44px controls, reduced motion, captions, RTL, no-JS | static; manual staging required |
| F11-NFR-006 Observability | trace IDs, audit, diagnostics, queue/health metrics and Safe Mode | forensic + operations matrix |
| F11-NFR-007 Migration/rollback | lock, checkpoints, dry-run, quarantine, history migration, reconciliation, reversible cutover | static; staging copy rehearsal |
| F11-NFR-008 Operability | diagnostics, repair, queue inspection, Safe Mode and cleanup | static; operator staging runbook |
| F11-NFR-009 Compatibility | WordPress 7 baseline, PHP >=8.1, CI PHP 8.1/8.3/8.4, versioned contracts | CI matrix/package tests |
| F11-NFR-010 Localization | English-US base, text domain/POT, logical RTL CSS, locale-safe labels | POT/static; Urdu/Arabic linguistic acceptance in staging |
