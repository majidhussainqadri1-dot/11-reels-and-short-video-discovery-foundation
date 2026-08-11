# Changelog

## 1.1.0-rc7 — 2026-08-11

### Rewritten governing-plan reconciliation
- Re-read the current consolidated central plan and rewritten File 11 plan as the governing baseline rather than assuming RC6 plan parity.
- Registered the current File 11 requirement manifest: F11-FR/NFR, CV-119–129, F11-CEN-01, CV-239–285 consumer obligations and current acceptance journeys.
- Preserved canonical ownership: File 10 remains raw-media/player/rights/caption-object owner; Files 00/19/20/24/25/26 retain their respective shared-domain authority.

### Safety, moderation and business-integrity corrections
- Replaced the legacy four-value report intake with the current harm / false-claim / impersonation / privacy / abuse / copyright / scam / child-safety taxonomy, with backward-compatible legacy mapping.
- Added low/medium/high/critical risk routing, SLA and specialist-route metadata to the moderation experience.
- Added a visible education-only medical safety charter: no autonomous diagnosis, prescription, dose selection or emergency replacement.
- Added a governed provider bridge for verified local emergency/qualified-care guidance; File 11 does not invent country emergency data.
- Made non-commercial recommendation behavior explicit: payment or donation status cannot purchase Reel ranking priority.

### Release identity
- Plugin `1.1.0-rc7`; main schema `1.2.0`; Top-20 schema `1.1.0`; File 11 contract `6`; event contract `4`; provider contract `3`.
- Added current-plan contract tests and RC7 exact-head packaging workflow while retaining all inherited RC5/RC6 corrective invariants.

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
- Re-audited the repository against the then-current governing plans and corrected authorization, atomicity, idempotency, privacy, localization and no-JavaScript defects.

## 1.1.0-rc4 — 2026-08-06
- Implemented CV-119–CV-129, including Stories/Status, Highlights, attributed responses, source/safety context, youth-safe discovery, well-being controls and value insights.

## 1.0.0-rc3 — historical
RC3 established the canonical Reel foundation. RC7 supersedes RC6, RC5, RC4 and RC3 as repository candidate.
