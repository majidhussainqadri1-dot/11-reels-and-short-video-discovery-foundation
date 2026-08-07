# Changelog

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
RC3 established the canonical Reel foundation. RC5 supersedes RC4 and RC3.
