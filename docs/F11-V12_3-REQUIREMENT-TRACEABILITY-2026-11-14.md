# File 11 — Master Plan V12.3 traceability and release-gate register

**Date:** 2026-11-14. **Candidate:** Draft PR #37 exact SHA `e96e56636e0a5393e31e6fc022772e72242f08d0`, plugin source `1.2.0-rc21`.
**Governing authority:** user-approved `Sabri-Website-Master-Plan-V12.3.docx`, particularly File11 at printed pages 40–41, 166–171 and AJ-17 at 261; operational completion plan of 2026-11-14.
**Maturity:** R0 plan; R1 candidate source/route claim; R2 scoped isolated CI; R3 actual WordPress+owners integration; R4 staging/founder acceptance; R5 exact-deployed parity + live retest.
**Release judgment:** NOT RELEASE READY. None of the 55 IDs has verified R3/R4/R5. The table is a requirements/evidence control, NOT a claimed implementation pass.

## P00 — three separate realities

| Reality | Exact state | Decision |
|---|---|---|
| GitHub `main` | `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` README-only | not production code |
| Candidate | `e96e56636e0a5393e31e6fc022772e72242f08d0`; Draft PR #37, stack #36/#35; RC21 source | source-only R1/scoped R2 |
| Live plugin/checksum | UNVERIFIED; no deployed package read | BLOCKED P00 |
| Live DB/columns/index/rows | UNVERIFIED; `SHOW INDEX.Sub_part` not read | BLOCKED P00 |
| `wp_options`/migration | UNVERIFIED | BLOCKED P00 |
| WordPress connection | WPVibe no linked site / HTTP 530 `cf_origin_error`; Jetpack `site_disconnected` | transport symptom, root cause NOT established |
| Staging providers | UNVERIFIED/UNCONNECTED | R3 and R4 BLOCKED |

**Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔** No production changes, no DB migration, no merge or deploy from source CI alone.

## P01 — all 55 mandatory IDs

Each row has: exact ID, V12.3 normative acceptance scope (short paraphrase), *previous source-register pointer only*, scoped R2 candidate test if listed, and required R3 scenario. A named test does not cover the entire requirement.

| ID | V12.3 acceptance scope | Candidate source pointer (unverified completeness) | Scoped candidate R2 check | R3 requirement / current decision |
|---|---|---|---|---|
| F11-FR-001 | File10-origin Reel create: title/topic/language/caption/cover/disclosure, approved media and publisher permission | `class-rsv-reels.php` | NOT DEMONSTRATED | Real File10 published video, permission, duplicate request, denied private media ; R3 BLOCKED |
| F11-FR-002 | Server-verified File10 60–600 seconds; reject 59/601 and forged client metadata | `class-rsv-file10.php` | NOT DEMONSTRATED | Real video 59/60/600/601 seconds and forged duration ; R3 BLOCKED |
| F11-FR-003 | Approved educational topics/types and safety labels; no open entertainment | `class-rsv-contracts.php` | NOT DEMONSTRATED | Allowed subjects; unknown labels denied ; R3 BLOCKED |
| F11-FR-004 | Current publisher role, ready media, captions, rights, consent, clinical review and publish gate | `class-rsv-reels.php` | tests/rc17-restoration-gate-behavior.php | Rights, consent, current verified publisher and publication gate ; R3 BLOCKED |
| F11-FR-005 | Exactly one active Reel; swipe/button/keyboard; focus and Back/forward predictability | `class-rsv-frontend.php; assets/js/rsv.js` | NOT DEMONSTRATED | Mobile swipe/keyboard, single active player, focus and navigation ; R3 BLOCKED |
| F11-FR-006 | Explicit user control; autoplay off by default, visibility/data-saver/reduced-motion adaptation | `assets/js/rsv.js; assets/js/rsv-top20.js` | NOT DEMONSTRATED | Opt-in off by default; pause on background/data saver/reduced motion ; R3 BLOCKED |
| F11-FR-007 | Educational relevance, source quality, completion, freshness, diversity and safety-weighted ranking | `class-rsv-reels.php; class-rsv-repository.php` | NOT DEMONSTRATED | Safety/quality weighting, donor neutrality, withdrawn exclusion ; R3 BLOCKED |
| F11-FR-008 | Owner-governed like/dislike/reactions/comments/share/save/follow/report and versioned counts | `class-rsv-reels.php; class-rsv-file10.php` | NOT DEMONSTRATED | Like/comment/report owner callbacks, duplicate and unauthorised writes ; R3 BLOCKED |
| F11-FR-009 | Private last position/completion plus clear/reset/export without IDOR | `class-rsv-reels.php; class-rsv-repository.php` | NOT DEMONSTRATED | Owned session progress, clear, export, erase and IDOR ; R3 BLOCKED |
| F11-FR-010 | Canonical Reel/profile/topic/video destinations with return/resume | `class-rsv-frontend.php` | NOT DEMONSTRATED | Direct/public URL, back link, login destination and no open redirect ; R3 BLOCKED |
| F11-FR-011 | Claims, patient privacy, harassment/copyright report, restrict/remove/appeal/revoke | `class-rsv-reels.php` | tests/rc17-restoration-gate-behavior.php | Report, appeal, restoration rights/consent recheck and invalidation ; R3 BLOCKED |
| F11-FR-012 | Caption exception governance, transcript access and non-gesture controls | `class-rsv-frontend.php; class-rsv-file10.php` | NOT DEMONSTRATED | Captions/transcript, screenreader and keyboard ; R3 BLOCKED |
| F11-FR-013 | Network loss, deleted/restricted/processing/unsupported media explicit error/skip | `class-rsv-rest.php; class-rsv-repository.php` | tests/rc19-playback-detail-provider-errors.php | Media/SQL 503 != healthy empty 200; retry/deleted states ; R3 BLOCKED |
| F11-FR-014 | Aggregated privacy-safe creator views/completion/engagement; no viewer identity | `class-rsv-reels.php` | NOT DEMONSTRATED | Creator-only aggregate metrics, minimum sample suppression ; R3 BLOCKED |
| F11-FR-015 | Anti-spam view/reactions, scraping, fast swipe/report abuse protections | `class-rsv-security.php` | NOT DEMONSTRATED | Rate limit, idempotency, throttling fail closed ; R3 BLOCKED |
| F11-NFR-001 | Every read/write: File00 claims plus object/field and current owner/state authorization; IDOR | `class-rsv-security.php` | NOT DEMONSTRATED | Role x object/field IDOR against actual File00 ; R3 BLOCKED |
| F11-NFR-002 | Consent/retention/export/erasure/legal hold and cache/index reconciliation | `class-rsv-privacy.php; class-rsv-future30-privacy-integrity.php` | tests/rc18-privacy-export-query-failure.php; tests/rc18-privacy-erase-query-failure.php; tests/rc21-future30-privacy-sql-failclosed.php (23) plus tests/rc21-private-write-readfail.php (10) | Real WP privacy export/erase, revoked consent, legal hold and cache purge ; R3 BLOCKED |
| F11-NFR-003 | Idempotent events/jobs, bounded retry, dead letters and truthful degraded state | `class-rsv-jobs.php; class-rsv-db.php` | NOT DEMONSTRATED | Retries, crash recovery, missing provider and no fabricated success ; R3 BLOCKED |
| F11-NFR-004 | Bounded queries/pagination/cache/background work; measured p75/p95 budgets | `class-rsv-repository.php; assets/js/rsv.js` | NOT DEMONSTRATED | Staging API p95, LCP/INP/CLS p75 field evidence ; R3 BLOCKED |
| F11-NFR-005 | WCAG 2.2 AA: keyboard, focus, labels, zoom, contrast, motion, captions, RTL | `class-rsv-frontend.php; assets/css/rsv.css` | NOT DEMONSTRATED | WCAG 2.2 AA, keyboard, 400% zoom, 200% text, captions ; R3 BLOCKED |
| F11-NFR-006 | Redacted structured logs, metrics, trace IDs and owned alerts | `class-rsv-diagnostics.php; class-rsv-admin.php` | NOT DEMONSTRATED | Safe logs, trace IDs, alert routing; no patient/secret leak ; R3 BLOCKED |
| F11-NFR-007 | Fresh/upgrade/partial/concurrent migration, reconciliation/rollback, no data loss | `class-rsv-db.php; class-rsv-top20.php; class-rsv-future30.php` | tests/rc20-forty-schema-index-shape.php; tests/rc20-index-prefix-length.php | Real MySQL fresh/upgrade/partial/rollback, indexes, Sub_part ; R3 BLOCKED |
| F11-NFR-008 | System Check, queue/cache/index inspection and safe repair with runbook | `class-rsv-diagnostics.php; class-rsv-plugin.php` | NOT DEMONSTRATED | System check, admin visibility, safe repair and runbooks ; R3 BLOCKED |
| F11-NFR-009 | Reverify V12.3 WP 7.0.1/PHP 8.3 baseline and companion contract versions | `11-reels-foundation.php` | NOT DEMONSTRATED | Actual WP/PHP/plugin dependency versions and contracts ; R3 BLOCKED |
| F11-NFR-010 | English US base; Urdu/Arabic RTL, mixed scripts, locale times/numbers | `languages/reels-short-video-discovery.pot; assets/css/rsv.css` | NOT DEMONSTRATED | Urdu/Arabic RTL, en-US, dates, mixed LTR strings ; R3 BLOCKED |
| F11-FUT-001 | Educational Reel Series / Playlists (carried from candidate/earlier plan; V12.3 individual normative text MISSING: scope resolution required) | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File11 public Reel current-ref validation ; R3 BLOCKED |
| F11-FUT-002 | Structured Learning Paths via File05 current course/lesson authority | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File05 current course/lesson references ; R3 BLOCKED |
| F11-FUT-003 | Related Knowledge Button via current public canonical owner URLs | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Current authoritative knowledge-owner link with actionable route ; R3 BLOCKED |
| F11-FUT-004 | Source-at-Time Citation Cards with File10 duration/current owner source | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Evidence and time bound, source opens correctly ; R3 BLOCKED |
| F11-FUT-005 | Evidence Layer from File06 without inventing grades | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File06 evidence truth and withdrawn flag ; R3 BLOCKED |
| F11-FUT-006 | Correction/supersession lineage with atomic single-active relation (Issue #38) | `class-rsv-future30.php; class-rsv-future30-public-safety.php` | tests/rc21-supersession-sql-failclosed.php (14 isolated scenarios) | Versioned lineage, stale previous citation ; R3 BLOCKED |
| F11-FUT-007 | Versioned Reel snapshots/hash, no private history leak | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Reel version/history and private metadata nonleak ; R3 BLOCKED |
| F11-FUT-008 | Advanced Remix Studio via File10 verified derivative and current consent | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File10 approved derivative, consent/rights and no patient-case default ; R3 BLOCKED |
| F11-FUT-009 | Remix Permission Matrix with patient default deny/revocation | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Denied permissions cannot be forged by client ; R3 BLOCKED |
| F11-FUT-010 | Reel Templates, recipe pointer only, File10 media authority | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File10 current template media and rights ; R3 BLOCKED |
| F11-FUT-011 | Question -> Reel Answer with external question truth retained | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | External question owner stays canonical ; R3 BLOCKED |
| F11-FUT-012 | Co-authoring with File00 verified consent | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File00 current coauthor consent and revoked account ; R3 BLOCKED |
| F11-FUT-013 | Expert Review Badge from genuine current attestation | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Reviewer attestation and withdrawn review ; R3 BLOCKED |
| F11-FUT-014 | Original + up to 9 approved translation Reel versions; no source duplicate | `class-rsv-future30.php; trait-rsv-future30-*.php` | tests/rc9-language-quiz-behavior.php | Source +9 languages; mismatch/expired provider denied ; R3 BLOCKED |
| F11-FUT-015 | Human-reviewed translation/dub, original audio, current voice consent and File10 track | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File16 executes, File10 track and voice consent ; R3 BLOCKED |
| F11-FUT-016 | Searchable reviewed File10 transcript via File26, no local corpus | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File10 reviewed track, File26 query handoff ; R3 BLOCKED |
| F11-FUT-017 | Bounded smart chapters/key moments and actual seek | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Valid timestamps bounded by actual video duration ; R3 BLOCKED |
| F11-FUT-018 | File06-backed Reel Knowledge Card, no fabricated cure/superiority | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File06 cited and current evidence ; R3 BLOCKED |
| F11-FUT-019 | 1–5 learning questions, server-side answer key, no public leak or ranking reward | `class-rsv-future30.php; trait-rsv-future30-*.php` | tests/rc9-language-quiz-behavior.php | Private answer key and audited idempotent attempts ; R3 BLOCKED |
| F11-FUT-020 | Private Study Collections, current public Reel refs, export/erase | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc11-study-collection-revocation.php; tests/rc11-study-collection-audit-utf8.php; tests/rc21-private-write-readfail.php | Private owner-only valid current Reel references ; R3 BLOCKED |
| F11-FUT-021 | Private timestamp Notes, bounded duration, no-store, export/erase | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Private timestamp notes, secure clear/export ; R3 BLOCKED |
| F11-FUT-022 | Grounded AI context handoff to File16; execute_ai=false and no diagnosis/dose | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Grounded context only; File16 execution; no patient data ; R3 BLOCKED |
| F11-FUT-023 | Creator search opportunity via privacy-safe File26 aggregates | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | File26 current privacy-safe aggregate ; R3 BLOCKED |
| F11-FUT-024 | Explainable why-this-Reel reasons; no donation/payment cause | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Explainability, mute and no donor weighting ; R3 BLOCKED |
| F11-FUT-025 | Feed control: more/less/mute/not interested/keywords/reset alters retrieval | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Real owner controls, persisted reset and mute ; R3 BLOCKED |
| F11-FUT-026 | 0–100 learning diversity preference, safety rules override | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | 0–100 preference, safety overrides ; R3 BLOCKED |
| F11-FUT-027 | Privacy-thresholded Creator Research Dashboard, no individual viewers | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Samples ≥5 and privacy-safe metrics, true UI ; R3 BLOCKED |
| F11-FUT-028 | Pre-publish clinical scan + mandatory human review; failure never auto-approves | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Fail closed with provider missing/reject; human review ; R3 BLOCKED |
| F11-FUT-029 | Accessibility Plus preferences: captions/transcript, contrast, motion, RTL | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Language, transcript-only, reduced motion and audio description ; R3 BLOCKED |
| F11-FUT-030 | Current File06/26-validated Reel Knowledge Graph, no stale/private nodes | `class-rsv-future30.php; trait-rsv-future30-*.php` | NOT DEMONSTRATED | Current File06/File26 edges; no stale or private leakage ; R3 BLOCKED |

## V12.3 source reconciliation notes

1. **F11-FUT-001 ambiguity:** the V12.3 File11 block says `F11-FUT-001` through `F11-FUT-030` are additive approved scope but gives no separate itemized F11-FUT-001 paragraph; RC21 calls it `Educational Reel Series / Playlists`. Keep the ID, but obtain the approved earlier-item statement before coding/acceptance. Do not silently invent missing normative detail.
2. V12.1 execution register is inherited *evidence only* and cannot be relabeled as V12.3 R3/R4/R5. Need cross-check safety, usability, roles and revised provider acceptance for **every** row.
3. Candidate `RSV_Future30::PLAN_REVISION` is `2026-08-12`, an earlier lineage marker; no inference that V12.3 was compiled into source. A V12.3 scope/revision decision and versioned contract must be explicit.
4. P07 source UI `render_safe_tools` exposes plain owner/ref for citation and related knowledge without verified navigable canonical target. Provider destination contract needed first; do not guess URLs.
5. Issue #38: concurrency atomicity unverified. Pre-transaction reads and unique `public_id` do not prove a single active supersession edge for a given source. Actual MySQL two-session fixture and rollback required before code fix or resolution.
6. Issue #45: Future30 generic read helpers may substitute empty/default state when MySQL SELECT fails; distinct from previously fixed export/erase and private POST SQL paths. Needs staging reproduction, complete caller audit and typed 503 before repair.
7. V12.3 references WordPress 7.0.1/PHP 8.3 as project compatibility baseline. Candidate declares lower minimum support. This is a verification differential, not evidence of incompatibility.

## P09/P10 — 15 negative acceptance tests

| ID | Actual isolated staging / browser acceptance | State |
|---|---|---|
| F30-AT-01 | Private generic route cannot leak collection/note/preference/creator state; deny/404 | R3/R4 BLOCKED |
| F30-AT-02 | Stale/unpublished knowledge, source, lesson, Reel external ref fails closed | R3/R4 BLOCKED |
| F30-AT-03 | Timestamp exceeds canonical File10 duration => 422 | R3/R4 BLOCKED |
| F30-AT-04 | More than 9 translations/source-language duplicate rejected | R3/R4 BLOCKED |
| F30-AT-05 | Forged coauthor, reviewer attestation and voice consent rejected | R3/R4 BLOCKED |
| F30-AT-06 | Patient-case remix without current consent denied; no derivative edge | R3/R4 BLOCKED |
| F30-AT-07 | Unreviewed transcript/AI track absent from public discovery | R3/R4 BLOCKED |
| F30-AT-08 | Quiz answer key never exposed in public payload | R3/R4 BLOCKED |
| F30-AT-09 | Muted topic/doctor/keyword absent from actual logged-in retrieval | R3/R4 BLOCKED |
| F30-AT-10 | AI/search/transcript/dub outage yields truthful 503/degraded, never false success | R3/R4 BLOCKED |
| F30-AT-11 | Two concurrent idempotency-distinct supersession writes yield one governed result; audit/outbox | R3/R4 BLOCKED |
| F30-AT-12 | Collections/notes/preferences export/erase plus index/cache reconciliation | R3/R4 BLOCKED |
| F30-AT-13 | Real browsers 320px, 400% zoom, RTL, reduced motion, transcript preference | R3/R4 BLOCKED |
| F30-AT-14 | Donor/payment/follower-only signals not prioritized in explanations/metrics | R3/R4 BLOCKED |
| F30-AT-15 | Backup/restore/rollback of Future30 additive tables; no foreign-owner mutation | R3/R4 BLOCKED |

## P00–P14 execution gates / evidence handoff

| Phase | Required artifact | Current gate |
|---|---|---|
| P00 | read-only live+staging version, code SHA256, tables+indexes+Sub_part, migration markers, logs | BLOCKED site access |
| P01 | this V12.3 matrix, FUT001 written scope decision, versioned decisions | SOURCE DOCUMENTED; scope pending |
| P02 | reproducible exact-build defect register, review of five inherited fixes and Issue #38 | IN PROGRESS; no verified live defects |
| P03 | all 15 FR actual role/device journeys and denied cases | R3 BLOCKED |
| P04 | all 10 NFR real WP, privacy, accessibility, performance, operability and rollback | R3 BLOCKED |
| P05 | all 30 Future real UI + backend owner-boundary behavior | R3 BLOCKED |
| P06 | version-locked File00/05/06/10/12/16/17/19/20/21/22/23/24/25/26 providers and contracts | BLOCKED |
| P07 | actual responsive/RTL/keyboard source actions and verified canonical URL destination | BLOCKED |
| P08 | cross-repo installed WordPress integrated test environment | BLOCKED |
| P09 | real MariaDB/MySQL concurrency+idempotency+backup/restore+schema parity | BLOCKED |
| P10 | AJ-17, AT01–15, security, privacy, browser/performance evidence | BLOCKED |
| P11 | exact final HEAD PHP 8.1/8.3/8.4 CI, deterministic ZIP + extracted parity | prior candidate SHA CI passed; new HEAD must rerun |
| P12 | two independent review cycles; defect=0 gate, founder staging approval | NOT DONE |
| P13 | authorized staged production deploy, parity, canary, rollback and live retest | NOT AUTHORIZED / NOT DONE |
| P14 | postrelease anomaly monitoring and incident closure with versioned evidence | NOT STARTED |

## Defect and blocker ledger at this source-only snapshot

| Ledger item | Count | Evidence | Status |
|---|---:|---|---|
| Distinct preexisting source defects described as fixed in PR #37 | 5 | SQL/privacy/correction/private-read fixes; 50 scoped regressions | source corrected, no live retest |
| Open tracked concurrency risk | 1 | Issue #38 | NOT RESOLVED, needs MySQL reproduction |
| New suspected read-SQL error/false-empty risk | 1 | Issue #45, `object_rows`/`edge_rows`/`state_get` source | UNRESOLVED, requires isolated real-DB reproduction; not a verified live defect |
| Known source-level P07 UI gap group | 1 | canonical source/knowledge links not actually navigable | NOT FIXED; owner contract required |
| V12.3 scoping ambiguity | 1 | FUT001 missing separate itemized text | UNRESOLVED decision |
| Runtime/staging untested FR+NFR+FUT requirements | 55 | no R3 evidence | BLOCKED / not necessarily defects |
| Runtime/staging negative AT tests unverified | 15 | no R3/R4 evidence | BLOCKED / not necessarily defects |

**Do not sum the last two rows as defects.** Actual defect counts change only after reproducible evidence. Every new defect log must capture ID, environment, SHA, db/migration state, steps, expected/actual, owner, reproduction, correction, retest and closure.

## Release blocker and decision

- STOP if site plugin, DB or migration state cannot be frozen, or if repository-deploy parity differs.
- STOP if Issue #38, citation link, revoked media cache, privacy export/erase, IDOR, moderation or clinical safety remains unverified.
- STOP if any active 55-ID/15-AT R3/R4 acceptance is missing; isolated source green is insufficient.
- Founder written R4 approval, safe backup/rollback and monitored R5 after release are indispensable.

## Source provenance

- V12.3 user-supplied Word plan, printed pp. 40–41, 166–171, 261.
- GitHub Draft PR #37 exact candidate `e96e56636e0a5393e31e6fc022772e72242f08d0` (not merged/deployed).
- Inherited `docs/P00-P14-V12_1-EXECUTION-EVIDENCE-2026-11-13.md` and `docs/P07-UI-UX-SOURCE-DELTA-2026-11-13.md` (reused as explicit historical source pointers only).
- GitHub Issue #38 (staging-only hypothesis; not a proven live defect).
