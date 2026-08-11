# File 11 — Reels and Short Video Discovery

**Release candidate:** `1.1.0-rc7`  
**Working branch:** `codex/file-11-40-review-v1.1.0-rc6` (historical branch name retained for PR continuity)  
**Canonical package root:** `reels-foundation-11`

File 11 is the canonical owner of vertical educational Reel entities, the 60–600-second rule, swipe discovery, ranking guardrails, moderation, private progress/history, maximum-24-hour Stories/Status, durable consent-current Highlights, attributed Reel-to-Reel Response/Remix relationships, source/safety context, user-controlled well-being settings, youth-safe discovery and privacy-minimized creator value insights.

File 10 remains the sole owner of raw upload, transcoding, storage, playback, caption objects, media rights, patient-media consent and secure delivery. File 11 does not duplicate that backend.

## Current governing baseline

RC7 re-harmonizes the repository against the Founder-supplied rewritten governing documents current on 2026-08-11:

1. the consolidated central governing master plan;
2. the rewritten File 11 complete master plan.

Historical RC4–RC6 review evidence remains useful regression evidence, but the rewritten documents are the current specification baseline for this release candidate.

## RC7 rewritten-plan completion

RC7 preserves all RC6 corrective invariants and closes the additional plan-to-code gaps identified when the rewritten plans were re-read requirement-by-requirement:

- registers the rewritten File 11 requirement/acceptance manifest, including `F11-CEN-01`, `CV-239–CV-285` consumer obligations and current acceptance journeys;
- adopts the current report taxonomy: harm, false claim, impersonation, privacy, abuse, copyright, scam and child safety, while safely mapping legacy RC6 report values;
- exposes content-risk tier, SLA and expert-route metadata for moderation without creating a second moderation owner;
- adds a visible education-only medical safety charter and a verified-provider bridge for local emergency/qualified-care diversion;
- makes ranking/provider metadata explicit that payment, donation status and follower count alone do not buy recommendation priority;
- records the exact current governing-plan revision in runtime/provider contracts.

## Release identity

- Plugin: `1.1.0-rc7`
- Main schema: `1.2.0`
- Top-20 schema: `1.1.0`
- File 11 contract: `6`
- File 11 event contract: `4`
- Provider contract: `3`
- Governing-plan revision: `2026-08-11`

## Verification

```bash
bash tests/run-all.sh
bash tools/build-package.sh packages/reels-foundation-11-1.1.0-rc7.zip
```

The complete suite covers PHP 8.1/8.3/8.4, PHP/JavaScript syntax, state/helper tests, forensic/static/Top-20/inherited-RC5/inherited-RC6/current-plan contracts, deterministic double build, SHA-256, ZIP integrity and exact source/package parity.

## Claim boundary

RC7 is a **repository release candidate**, not production completion. Hostinger staging, real companion contracts, real roles/media, browsers/devices/RTL/accessibility, migration/restore/rollback rehearsal, Founder acceptance, controlled deployment and operational monitoring remain separate mandatory gates.
