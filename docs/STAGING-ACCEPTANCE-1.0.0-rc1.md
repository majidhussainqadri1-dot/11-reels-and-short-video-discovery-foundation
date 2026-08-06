# Hostinger Staging Acceptance — File 11 1.0.0-rc1

No production-complete claim is permitted until every applicable item has dated evidence.

## Installation and migration
- [ ] Verified database/files backup and isolated restore proof.
- [ ] Fresh WordPress 7.0.1 / PHP 8.3 install.
- [ ] Upgrade from File 11 0.1.0 and 0.2.0 with dry-run report.
- [ ] Legacy File 10 mappings inventoried; unmapped/invalid Reels quarantined.
- [ ] Repeated activation, deactivation/reactivation and concurrent upgrade are idempotent.
- [ ] Non-destructive uninstall verified; destructive purge separately authorized and tested.

## Contracts and roles
- [ ] Exact accepted File 10 1.0.0-rc1+ installed; media, playback, captions, progress and interactions pass.
- [ ] File 00 real Founder, verified doctor, suspended doctor, ordinary member and guardian contexts pass.
- [ ] File 20/25 routes/layout/RTL visual contracts pass.
- [ ] File 19/21/24 event/notification/interaction/assurance adapters fail safely.

## Functional journeys
- [ ] Create → File 10 media processing → review → publish.
- [ ] 59, 60, 600 and 601 second boundaries.
- [ ] Rights, consent, caption, cover, topic and safety gates.
- [ ] Guest browse; authenticated Like/Dislike/Save/report/history.
- [ ] Restrict/remove/appeal/correction and cache purge.
- [ ] Deleted/restricted/processing/provider-outage/offline states.
- [ ] Creator insights reveal no viewer identity.

## Security and privacy
- [ ] IDOR, CSRF, nonce, replay, idempotency collision, rate, enumeration and privilege tests.
- [ ] No raw provider secrets, patient PII, SQL/path/stack details in UI/logs/repository.
- [ ] Private pages and APIs no-store/noindex; LiteSpeed cache verification.
- [ ] Privacy export, clear and erasure; retained moderation evidence anonymized.
- [ ] Abuse, rapid swipe, reaction spam, report spam and scraping controls.

## UX, accessibility and performance
- [ ] 320–1920px, mobile/desktop/tablet, Urdu/Arabic RTL and English LTR.
- [ ] Keyboard, focus, screen reader, 200/400% zoom, contrast, captions and reduced motion.
- [ ] Chrome, Firefox, Safari and Edge; Android and iOS.
- [ ] Weak network/data saver, memory cleanup and no uncontrolled preload.
- [ ] Realistic concurrent viewers, p75/p95 page/API budgets and DB query bounds.

## Resilience and release
- [ ] Outbox retry/dead-letter drill and operator recovery.
- [ ] File 10/provider outage and restoration reconciliation.
- [ ] Backup restore, cache/index rebuild and rollback rehearsal.
- [ ] Two fresh review/fix rounds after the final code change.
- [ ] Founder functional/visual/copy acceptance.
- [ ] Monitored production plan, rollback window and named operational owners.
