# File 11 — Reels and Short Video Discovery

**Release candidate:** `1.1.0-rc4`  
**Branch:** `codex/file-11-four-plan-complete-v1.1.0-rc4`  
**Canonical package root:** `reels-foundation-11`

File 11 is the canonical owner of vertical educational Reel entities, the 60–600-second rule, swipe discovery, ranking guardrails, moderation, private progress/history, maximum-24-hour Stories/Status, durable consent-current Highlights, attributed Reel-to-Reel Response/Remix relationships, source/safety context, user-controlled well-being settings, youth-safe discovery and privacy-minimized creator value insights.

File 10 remains the sole owner of raw upload, transcoding, storage, playback, caption objects, media rights, patient-media consent and secure delivery. File 11 does not duplicate that backend.

## Governing plans

1. Definitive Integrated Master Plan v3.0;
2. Consolidated All-Chats Recovered Directives v2.1;
3. Continuous Value / Top-20 Superset Master Plan v1.0;
4. File 11 Complete Master Plan v1.0.

## RC4 corrections

- implements CV-119 through CV-129: source/safety, transcripts, Stories/Status, Highlights, attributed responses, creator value insights, well-being controls and youth-safe mode;
- makes File 00 claims contract-aware and keeps authorization filters deny-only;
- binds cursors to topic and youth-safety context and provides signed private-history cursors;
- consumes versioned File 10 secure-download, transcript and interaction adapters without manufacturing media URLs or counts;
- fail-closes public visibility for legacy Reels missing reviewed source/safety and caption/transcript evidence while preserving owner/operator repair access;
- adds four review records, migration, threat model, traceability, deterministic packaging and exact-head CI.

## Verification

```bash
bash tests/run-all.sh
bash tools/build-package.sh
```

The suite covers PHP/JavaScript syntax, state/helper tests, forensic/static/Top-20 contracts, deterministic double build, ZIP integrity and source/package byte parity.

## Claim boundary

RC4 is a **repository release candidate**, not production completion. Hostinger staging, real companion contracts, real roles/media, browsers/devices/RTL/accessibility, migration/restore/rollback rehearsal, Founder acceptance, controlled deployment and operational monitoring remain separate mandatory gates.
