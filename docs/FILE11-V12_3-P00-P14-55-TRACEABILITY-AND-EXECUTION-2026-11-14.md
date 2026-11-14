# File 11 — V12.3 P00–P14 execution, traceability and acceptance (source-only handoff)
**Date:** 2026-11-14. **Authority:** Sabri Website Master Plan V12.3, printed pages 40–41, 166–171 and 261; File 11 V12.3 Comprehensive Completion Plan dated 2026-11-14.
**Source plan SHA256:** `c061d9c9964d2434b040b814120d446e9004bd3b2f853eb355df1ccc4648e1cf`.
**Execution-plan SHA256:** `e0f10a8ceda1a16fa6210daee08b2ba038a784e97fea3f016551be67b1b8d8e0`.
**Candidate source baseline:** Draft PR #37 `e96e56636e0a5393e31e6fc022772e72242f08d0` (stacks on PR #36 and #35). This new branch is not main or production. Check current branch HEAD/CI afresh before review.
**Master-plan governance:** V12.3 supersedes the older V12.1 execution document. All inherited evidence is disclosed as historical; no R3–R5 claim transfers from CI.
**Release note:** This is an evidence register, NOT an installer, WordPress database dump, staging sign-off, Founder approval, or live-verification certificate.

## P00 — Exact-environment freeze and stop condition
| Evidence item | Actual observation / absence |
|---|---|
| GitHub main HEAD | `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (README-only at investigation) |
| Audited Draft source version | `1.2.0-rc21`; proposed schema declaration File11 `1.2.0`, Top20 `1.1.0`, Future30 `1.0.0` |
| GitHub approved production package | NONE demonstrated; Draft PR #35/#36/#37 not merged/deployed |
| WordPress read-only WPVibe | `www.sabrihomeopathy.com` returned HTTP 530 on fresh attempted connection; exact cause is UNVERIFIED |
| WordPress.com/Jetpack site-scoped access | Site registration visible but connection unavailable; no authenticated live inventory |
| Active File11 / File00 / File10 plugin versions | UNVERIFIED |
| Deployed PHP/JS/CSS plugin file hashes / folder | UNVERIFIED |
| Live DB tables, columns, rows, indexes, SHOW INDEX.Sub_part, wp_options, migration lock | UNVERIFIED |
| Runtime logs, PHP settings, File26 connector actual readiness | UNVERIFIED |
| Staging identity/package/checksum | UNVERIFIED |
| Live root cause | NOT ESTABLISHED; HTTP 530 is only transport symptom |
**Mandatory stop:** If exact deployed artifact is unavailable, do not call source fixes live resolutions. If staging/live differs from repository, halt live debugging and initiate Deployment-Parity Audit. No live schema/role/plugin changes authorized by this file.

## P01 — V12.3 requirement-level evidence matrix
R0 = master-plan rule. R1 = code/contract presence claimed in prior repository register. R2 = **specific** isolated regression where named. R3 = actual installed WordPress/companion journey. R4 = accepted staging & Founder. R5 = source/DB deployed parity and monitored live retest.
**Counts:** 15 FR, 10 NFR, 30 FUT = 55; 15 separate F30-AT tests; AJ-17 mandatory. All 55 have R0; none has confirmed R3/R4/R5 in this investigation. R1/R2 is not feature completion.

| ID | V12.3 requirement | Earlier RC21 source location | Historical narrowly scoped test | Required real environment acceptance | Evidence maturity |
|---|---|---|---|---|---|
| F11-FR-001 | Reel create | `class-rsv-reels.php` | Not specifically demonstrated in this register | Real File10 published video, permission, duplicate request, denied private media | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-002 | Duration enforcement | `class-rsv-file10.php` | Not specifically demonstrated in this register | Real video 59/60/600/601 seconds and forged duration | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-003 | Educational taxonomy | `class-rsv-contracts.php` | Not specifically demonstrated in this register | Allowed subjects; unknown labels denied | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-004 | Reel review/publish | `class-rsv-reels.php` | tests/rc17-restoration-gate-behavior.php | Rights, consent, current verified publisher and publication gate | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FR-005 | Vertical feed | `class-rsv-frontend.php; assets/js/rsv.js` | Not specifically demonstrated in this register | Mobile swipe/keyboard, single active player, focus and navigation | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-006 | Autoplay policy | `assets/js/rsv.js; assets/js/rsv-top20.js` | Not specifically demonstrated in this register | Opt-in off by default; pause on background/data saver/reduced motion | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-007 | Ranking | `class-rsv-reels.php; class-rsv-repository.php` | Not specifically demonstrated in this register | Safety/quality weighting, donor neutrality, withdrawn exclusion | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-008 | Interactions | `class-rsv-reels.php; class-rsv-file10.php` | Not specifically demonstrated in this register | Like/comment/report owner callbacks, duplicate and unauthorised writes | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-009 | Watch progress/history | `class-rsv-reels.php; class-rsv-repository.php` | Not specifically demonstrated in this register | Owned session progress, clear, export, erase and IDOR | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-010 | Deep links | `class-rsv-frontend.php` | Not specifically demonstrated in this register | Direct/public URL, back link, login destination and no open redirect | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-011 | Moderation | `class-rsv-reels.php` | tests/rc17-restoration-gate-behavior.php | Report, appeal, restoration rights/consent recheck and invalidation | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FR-012 | Captions/accessibility | `class-rsv-frontend.php; class-rsv-file10.php` | Not specifically demonstrated in this register | Captions/transcript, screenreader and keyboard | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-013 | Offline/error states | `class-rsv-rest.php; class-rsv-repository.php` | tests/rc19-playback-detail-provider-errors.php | Media/SQL 503 != healthy empty 200; retry/deleted states | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FR-014 | Creator insights | `class-rsv-reels.php` | Not specifically demonstrated in this register | Creator-only aggregate metrics, minimum sample suppression | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FR-015 | Anti-abuse | `class-rsv-security.php` | Not specifically demonstrated in this register | Rate limit, idempotency, throttling fail closed | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-001 | Object/field authorization | `class-rsv-security.php` | Not specifically demonstrated in this register | Role x object/field IDOR against actual File00 | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-002 | Privacy lifecycle | `class-rsv-privacy.php; class-rsv-future30-privacy-integrity.php` | tests/rc18-privacy-export-query-failure.php; tests/rc18-privacy-erase-query-failure.php; tests/rc21-future30-privacy-sql-failclosed.php (23) plus tests/rc21-private-write-readfail.php (10) | Real WP privacy export/erase, revoked consent, legal hold and cache purge | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-NFR-003 | Reliability | `class-rsv-jobs.php; class-rsv-db.php` | Not specifically demonstrated in this register | Retries, crash recovery, missing provider and no fabricated success | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-004 | Performance | `class-rsv-repository.php; assets/js/rsv.js` | Not specifically demonstrated in this register | Staging API p95, LCP/INP/CLS p75 field evidence | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-005 | Accessibility | `class-rsv-frontend.php; assets/css/rsv.css` | Not specifically demonstrated in this register | WCAG 2.2 AA, keyboard, 400% zoom, 200% text, captions | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-006 | Observability | `class-rsv-diagnostics.php; class-rsv-admin.php` | Not specifically demonstrated in this register | Safe logs, trace IDs, alert routing; no patient/secret leak | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-007 | Migration/rollback | `class-rsv-db.php; class-rsv-top20.php; class-rsv-future30.php` | tests/rc20-forty-schema-index-shape.php; tests/rc20-index-prefix-length.php | Real MySQL fresh/upgrade/partial/rollback, indexes, Sub_part | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-NFR-008 | Operability | `class-rsv-diagnostics.php; class-rsv-plugin.php` | Not specifically demonstrated in this register | System check, admin visibility, safe repair and runbooks | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-009 | Compatibility | `11-reels-foundation.php` | Not specifically demonstrated in this register | Actual WP/PHP/plugin dependency versions and contracts | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-NFR-010 | Localization | `languages/reels-short-video-discovery.pot; assets/css/rsv.css` | Not specifically demonstrated in this register | Urdu/Arabic RTL, en-US, dates, mixed LTR strings | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-001 | Educational Reel Series / Playlists | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File11 public Reel current-ref validation | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-002 | Structured Learning Paths | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File05 current course/lesson references | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-003 | Related Knowledge Button | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Current authoritative knowledge-owner link with actionable route | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-004 | Source-at-Time Citation Cards | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Evidence and time bound, source opens correctly | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-005 | Evidence Layer | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File06 evidence truth and withdrawn flag | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-006 | Correction & Supersession System | `class-rsv-future30.php; class-rsv-future30-public-safety.php` | tests/rc21-supersession-sql-failclosed.php (14 isolated scenarios) | Versioned lineage, stale previous citation | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FUT-007 | Versioned Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Reel version/history and private metadata nonleak | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-008 | Advanced Remix Studio | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File10 approved derivative, consent/rights and no patient-case default | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-009 | Remix Permission Matrix | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Denied permissions cannot be forged by client | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-010 | Reel Templates Library | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File10 current template media and rights | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-011 | Question → Reel Answer | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | External question owner stays canonical | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-012 | Collaborative Reel / Co-authoring | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File00 current coauthor consent and revoked account | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-013 | Expert Review Badge | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Reviewer attestation and withdrawn review | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-014 | 10-Language Reel System | `class-rsv-future30.php; trait-rsv-future30-*.php` | tests/rc9-language-quiz-behavior.php | Source +9 languages; mismatch/expired provider denied | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FUT-015 | AI Translation + Optional Dubbing | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File16 executes, File10 track and voice consent | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-016 | Searchable Transcript | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File10 reviewed track, File26 query handoff | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-017 | Smart Chapters / Key Moments | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Valid timestamps bounded by actual video duration | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-018 | Reel Knowledge Card | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File06 cited and current evidence | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-019 | Micro-Quiz after Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | tests/rc9-language-quiz-behavior.php | Private answer key and audited idempotent attempts | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FUT-020 | Save to Study Collection | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc11-study-collection-revocation.php; tests/rc11-study-collection-audit-utf8.php; tests/rc21-private-write-readfail.php | Private owner-only valid current Reel references | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FUT-021 | Reel → Personal Notes | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Private timestamp notes, secure clear/export | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FUT-022 | Ask AI About This Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Grounded context only; File16 execution; no patient data | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-023 | Creator Search Opportunity Intelligence | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File26 current privacy-safe aggregate | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-024 | Expanded Why This Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Explainability, mute and no donor weighting | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-025 | Feed Control Center | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Real owner controls, persisted reset and mute | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FUT-026 | Knowledge Diversity Slider | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | 0–100 preference, safety overrides | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-027 | Creator Research Dashboard | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Samples ≥5 and privacy-safe metrics, true UI | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-028 | Pre-Publish Clinical Safety Scanner | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Fail closed with provider missing/reject; human review | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
| F11-FUT-029 | Accessibility Plus Mode | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Language, transcript-only, reduced motion and audio description | R1; scoped R2 identified in prior CI; R3/R4/R5 unverified |
| F11-FUT-030 | Reel Knowledge Graph | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Current File06/File26 edges; no stale or private leakage | R1 listed; R2 not demonstrated for this ID; R3/R4/R5 unverified |
**Open scope:** Individual F11-FUT-001 requirements must be reconciled with the prior approved File11 Educational Reel Series/Playlists source; V12.3 visibly references FUT-001–030 but does not reproduce FUT-001 item-level prose in the checked pages. Do not quietly invent its specification.

## V12.3 F30-AT negative acceptance matrix
These are *File11* historical test IDs, **not a new File30 module**.
| ID | Scenario | Actual required behavior | Real R3 state |
|---|---|---|---|
| F30-AT-01 | Private generic endpoint | A guest or another account cannot enumerate collections, notes, preferences or creator-private state; 404/deny, no private metadata. | NOT RUN / BLOCKED |
| F30-AT-02 | Stale external references | Unpublished/revoked File05/06/10/12/26 source or course never appears in public projection; explicit degraded/deny. | NOT RUN / BLOCKED |
| F30-AT-03 | Timestamp bounds | Citation, chapter and note past File10-authoritative 60–600s duration returns 422. | NOT RUN / BLOCKED |
| F30-AT-04 | Language maximum | A 10th translation or source-language duplicate is rejected; source + at most nine links. | NOT RUN / BLOCKED |
| F30-AT-05 | Verified assertions | Forged coauthor/reviewer/voice consent is refused absent current canonical provider attestation. | NOT RUN / BLOCKED |
| F30-AT-06 | Clinical media rights | Patient-case remix is denied without current explicit reuse policy, derivative and consent. | NOT RUN / BLOCKED |
| F30-AT-07 | Transcript/AI review | Unreviewed or expired transcription, translation or AI media track cannot be published/discovered. | NOT RUN / BLOCKED |
| F30-AT-08 | Private answers | Correct micro-quiz answer is never returned in public payload or page markup. | NOT RUN / BLOCKED |
| F30-AT-09 | Muted preference | Muted topic, doctor or keyword does not reappear in authenticated recommendation feed. | NOT RUN / BLOCKED |
| F30-AT-10 | Provider outage truth | Failed AI/search/transcript/dubbing provider gives truthful 503/degraded, no fabricated success. | NOT RUN / BLOCKED |
| F30-AT-11 | Concurrent mutations | Distinct/replayed idempotency inputs produce one governed result with audit/outbox; no duplicate active lineage. | NOT RUN / BLOCKED |
| F30-AT-12 | Data lifecycle | Export/erasure includes collections, notes, preferences and cache/index reconciliation. | NOT RUN / BLOCKED |
| F30-AT-13 | Real accessibility | 320px/400% zoom, RTL, reduced motion, captions/transcript preference in real browser. | NOT RUN / BLOCKED |
| F30-AT-14 | No donor boost | Donation, payment or follower count cannot create paid discovery priority/explanation. | NOT RUN / BLOCKED |
| F30-AT-15 | Recoverable data | Backup, restore and rollback of additive Future30 tables without modifying other canonical owners. | NOT RUN / BLOCKED |

## AJ-17 unified journey
- File10-authoritative 59-second clip rejected; 60 and 600 seconds accepted; 601 seconds rejected, regardless of forged client metadata.
- Show visible source and clinical safety review label; autoplay default **off** and explicit pause; preserve user preference; history clear functions across sessions.
- A removed/restricted clip must disappear from cached feed; current captions, source and transcript must remain usable; errors must not fabricate success.
- Execute with guest, authorized member/doctor, moderator, suspended, guardian-minor roles as applicable; test Urdu RTL/mobile, screen-reader, reduced motion and slow network.
- **Status:** unverified actual WordPress journey.

## P00–P14 stage gate register
| Phase | Workstream | This turn status | Next proof required |
|---|---|---|---|
| P00 | Live freeze | PARTIAL / BLOCKED | GitHub/source pinned; live WPVibe 530 and Jetpack disconnected; no installed plugin, DB, options, deployed checksum |
| P01 | 55 IDs + 15 acceptance tests | SOURCE MATRIX CREATED / R3 BLOCKED | 55 source-only rows migrated from V12.1 and mapped to V12.3; old status never treated as final evidence |
| P02 | Exact-source root cause | 1 TARGETED SOURCE CORRECTION / CI PENDING | Current-reel Future30 read on failed SQL falsely returned 404; fix + 9 focused isolated cases on this draft |
| P03 | 15 core FRs | R3 BLOCKED | Live rights, playback, captions, feed, ranking, moderation and history journeys not run |
| P04 | 10 NFRs | R3 BLOCKED | Privacy/security/accessibility/performance/migration/compatibility not measured on real WordPress |
| P05 | 30 FUTs | R3 BLOCKED | API/code presence does not prove polished UX or companion integration |
| P06 | Provider contracts | BLOCKED | Require pinned File00/05/06/10/12/16/17/19/20/21/22/23/24/25/26 approved artifacts |
| P07 | Actual UI/RTL/UX | BLOCKED | Provider-signed navigable cited URLs, screens, keyboard/zoom/mobile still unverified |
| P08 | Real cross-repo integration | BLOCKED | No live/staging installed companion parity |
| P09 | DB migration/concurrency | BLOCKED | Issue #38, SHOW INDEX.Sub_part, transactional atomicity need real MySQL/MariaDB |
| P10 | E2E/privacy/security/perf | BLOCKED | No real browser, role/privacy, field Web Vitals/p95 or clinical review |
| P11 | Exact-final-HEAD CI | PENDING | Rerun all QA after last source/doc/test change; no earlier green may certify current HEAD |
| P12 | Two independent reviews/staging | NOT STARTED | No independent reviews, backup/restore or Founder sign-off |
| P13 | Approved production rollout | NOT STARTED | Draft only; no main merge or deploy |
| P14 | Live monitoring/closure | NOT STARTED | No deployed artifact/DB parity/live retest |

## P02 — Distinct defect and risk register
| ID | Nature | Evidence | Status |
|---|---|---|---|
| D-V123-01 | **Confirmed unreleased candidate source bug:** Future30 GET `rest_post_dispatch` current-Reel lookup used `RSV_Repository::find()` then treated SQL failure (null + `wpdb->last_error`) as 404 removed/private instead of unavailable 503 | `class-rsv-future30-public-safety.php::filter_response` baseline PR #37 exact source; new `tests/v123-future30-public-read-outage.php` covers 9 isolated cases | Proposed correction on current Draft branch; source CI still needs exact latest HEAD; LIVE NOT RESOLVED |
| D-V121-01…05 | Five previously confirmed source defects (private export, erase, SQL lineage read, private read-before-write, duplicated ambiguous edges) | PR #37 and inherited 50 focused checks | Source corrected in prior candidate; staging/live unverified; **do not double count** |
| R-V123-01 | Concurrent different-idempotency correction writes can create multiple active supersession edges | Existing Issue #38; pre-dispatch check outside transaction, no verified DB single-active constraint | Open source-level risk; needs actual synchronized MySQL staging reproducer, design decision and migration approval; not proven live |
| G-V123-UI01 | Source/citation text not yet canonical clickable current safe destination | Existing P07 source audit; provider URL/visibility contracts needed | Open UI completion gap, not a proven live defect |
| B-V123-01 | Live artifact/DB/migration unknown; connector returned HTTP 530 | P00 attempt above; WordPress.com site disconnected | Release blocker; needs authorized read-only operator collection |
| B-V123-02 | File11→File26 connector remains proposed; event/tombstone and File10 version parity needed | Existing Issue #16 | Release blocker; no unsanctioned activation |

## Release gates and acceptance responsibilities
- Only the Founder may grant final acceptance/release authorization; no reviewer/maintainer is assumed appointed by this register.
- Authorized Hostinger/WordPress operator must supply **redacted** read-only deployed manifest, WP options, schema/index Sub_part, migration and runtime facts; do not commit private logs, credentials, patients or signed media URLs.
- Repository implementer must supply exact SHA, reproducible ZIP internal checksum and full CI; independently checked release artifact must match installed candidate on staging.
- An independent reviewer must audit source/security/privacy/UX, then a second different adversarial review, both with defect-fix-retest counts and explicit failed/blocked test reporting.
- On staging test real File00/05/06/10/12/16/17/19/20/21/22/23/24/25/26 provider versions; fail closed on absent/revoked and never create local truth tables for foreign canonical owners.
- Use a real MySQL/MariaDB database for fresh/upgrade/partial schema/rollback and Issue #38 two synchronized correction requests. Require ≤1 active canonical replacement edge, no cross-cycle and a clear loser response.
- Go/No-Go: no outstanding known defects for active scope, no blocked mandatory test, rehearsed recovery, explicit Founder approval, then controlled deploy and **live** re-test/checksum/DB parity; only then mark resolved.

## Required defect report fields
For each phase track: found, previously known, distinct new, corrected in source, exact-HEAD regression passed, independent retest passed, open, blocked, and deployed/live resolved. Never equate CI success with live resolution.
**Mandatory last row:** Repository HEAD / Deployed Version / DB Version / Migration State / Live Verification Status.

**Repo truth** is source-only until deployment parity is established.
