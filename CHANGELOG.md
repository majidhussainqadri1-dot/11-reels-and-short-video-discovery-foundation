# Changelog

## 1.1.0-rc6 — 2026-08-07

### Forty consecutive review/fix rounds
- Completed 40/40 review → fix → fresh/adversarial retest rounds against the four governing plans.
- 25 rounds found defects; 15 rounds found no new defect.
- Opened and closed F11-D-601 through F11-D-625.

### Authorization, state and dependency hardening
- Guardian-required accounts fail closed unless verification is positively present.
- File 10 compatibility checks actual repository/video methods and declared provider/event contract.
- Normal publication is review-only; moderation restore revalidates File 10 readiness and publication safety.
- Protected owner/operator reads continue to require active, approved, non-suspended and guardian-valid identity state.
- Expired idempotency records are not reusable as live operation results.

### Integrity, ranking and telemetry
- START/COMMIT/ROLLBACK results are verified and rollback failure is surfaced.
- Ranking adds bounded freshness and creator-concentration signals while preserving File 26 cross-platform ownership.
- Feed and Story pagination no longer emits false next cursors at exact eligible boundaries.
- Provider/filter destinations are normalized to safe same-origin URLs.
- Creator-value signals require authenticated, rate-limited, unique viewer/Reel/signal/day receipts.
- Creator insight disclosure minimum is five distinct viewers.

### Privacy, accessibility and lifecycle
- Story auto-expiry is bounded, transactional and audit/outbox evidenced.
- Well-being timers count active visible Reel seconds and do not run on unrelated pages.
- JSON-LD uses script-safe JSON_HEX serialization.
- Dynamic enum/topic/story labels use translatable mappings.
- Privacy export isolates reporter/appellant data; erasure is transactional and includes telemetry receipts.
- Dependency/reconciliation/rollback transitions surface evidence failures.
- Activation and diagnostics fail clearly when managed pages or repair evidence cannot be completed.

### Release identity
- Plugin `1.1.0-rc6`; main schema `1.2.0`; Top-20 schema `1.1.0`; File 11 contract `5`; event contract `4`; provider contract `3`.
- Added `tests/rc6-forty-review-contracts.php`, RC6 exact-head packaging workflow and forty-round defect register.

## 1.1.0-rc5 — 2026-08-07

### Four fresh adversarial review/fix rounds
- Re-audited the repository against Definitive Master Plan v3.0, Recovered Directives v2.1, Continuous Value / Top-20 Superset v1.0 and File 11 Master Plan v1.0.
- Corrected authorization-order, atomicity, idempotency, privacy completeness, partial-update, localization and no-JavaScript experience defects that remained after RC4.

### Security, integrity and privacy
- Publication pre-dispatch now validates object readiness only after canonical authorization.
- Private REST responses receive no-store/noindex headers.
- Story, Highlight and Response writes now require idempotency keys, enforce bounded rates and complete atomically with audit/outbox evidence.
- Story and Response publication is restricted to the review state and rolls back if evidence cannot be written.
- Reel-create compensation now checks every rollback operation and surfaces compensation failure.
- Preference PATCH semantics preserve omitted fields and record the versioned audit atomically.
- Privacy export is paginated and covers preferences, Stories, Highlights, Responses and Reel source/safety context; erasure is batched and reports removed/retained data accurately.

### Experience and evidence
- Added no-JavaScript Story creation, Highlight addition and attributed Response submission forms.
- Completed client localization labels and external-link safety attributes.
- Added RC5 hardening contracts, four review records, a defect register and exact-head RC5 release workflow.

## 1.1.0-rc4 — 2026-08-06
- Implemented CV-119–CV-129, including Stories/Status, Highlights, attributed responses, source/safety context, youth-safe discovery, well-being controls and value insights.

## 1.0.0-rc3 — historical
RC3 established the canonical Reel foundation. RC6 supersedes RC5, RC4 and RC3.
