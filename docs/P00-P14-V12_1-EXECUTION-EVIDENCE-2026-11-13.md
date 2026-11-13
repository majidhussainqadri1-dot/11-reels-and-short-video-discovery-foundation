# File 11 — V12.1 execution evidence register (2026-11-13)

**Authority:** Sabri Social Homeopathy Platform Master Plan V12.1; File11 completion plan dated 2026-11-13. This register is a source-limited handoff, not certification of Hostinger live status.

## Live-first freeze (P00)
- Repository `main` HEAD: `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (README-only).
- Parent RC21: `1df22845452419b3e67686d41b4ea178bfd9f484` (Draft PR #35).
- Corrected documentation: `9af8a4c30d4ebf6f1cf949c7ec710392365455ff` (Draft PR #36).
- Source correction branch: `fix/file11-rc21-future30-privacy-sql-20261113` (Draft PR #37 stacked on PR #36). Do not infer its latest HEAD from this frozen document; query GitHub and exact-HEAD CI at approval time.
- Source plugin version: `1.2.0-rc21`; schema declarations: File11 `1.2.0`, Top20 `1.1.0`, Future30 `1.0.0`. **All are repository declarations, not deployed state.**
- Actual active plugin/version, deployed artifact/hash, live database tables/rows/indexes/versions, `wp_options` migration markers, logs, configuration, companion versions: **UNVERIFIED**.
- WPVibe: no connected site; connection attempt to `www.sabrihomeopathy.com` received HTTP 530. Jetpack site-scoped MCP remains disconnected. HTTP 530 is connection symptom only; no root cause established.
- No production write, migration, deployment or live re-test authorized or undertaken.

## Maturity scale
R0 = requirement exists in plan. R1 = source/contract route exists. R2 = the **specified subset** has passed an isolated synthetic/automated test. R3 = actual WordPress/companion integration and user journeys. R4 = staging acceptance by Founder. R5 = deployed artifact, DB parity and monitored live retest.
**A scoped R2 test never certifies the entire requirement.** No requirement has verified R3, R4 or R5 evidence in this investigation.

## P00–P14 progress
| Phase | Evidence or remaining work | Gate |
|---|---|---|
| P00 | Source frozen at preceding RC21+docs SHA; main README-only. WordPress direct connection failed HTTP 530. | SOURCE DONE / LIVE BLOCKED |
| P01 | 55 canonical IDs entered in this matrix; each actual R3+ test remains unverified. | SOURCE-MATRIX DONE / R3+ BLOCKED |
| P02 | 4 confirmed source defects (2 privacy, 1 supersession, 1 private-state SQL failed-read write); 23 privacy + 14 supersession + 10 private-state isolated/static scenarios added; final exact-head CI required. | SCOPED SOURCE FIX; INDEPENDENT REVIEW PENDING |
| P03 | 15 fundamental FR journeys are source-present; real File10/File00 and browsers unavailable. | BLOCKED R3 |
| P04 | 10 NFR gates have source components; 3 new focused SQL-failure regression suites and inherited schema fixtures use synthetic data; actual staging gate remains unverified. | BLOCKED R3 |
| P05 | 30 Future30 IDs and native features present in candidate source; native browser acceptance not run. | SOURCE ONLY |
| P06 | Federated provider contracts are present; actual File05/06/10/16/26 providers not connected for test. | BLOCKED |
| P07 | UI code present, but full native Future30 interfaces/RTL/mobile WCAG audits not established. | BLOCKED |
| P08 | No live/staging credentials or verified companion builds; cannot establish provider version parity. | BLOCKED |
| P09 | MySQL/MariaDB fresh/upgrade/migration/restore not verified; 48+6 synthetic fixtures green. | BLOCKED |
| P10 | No actual browser/clinical/privacy/performance E2E test run. The new 47 isolated/static assertions cannot replace real WordPress tests. | BLOCKED |
| P11 | Passing PHP 8.1/8.3/8.4 source CI is confirmed on preceding code versions. Require a newly successful exact-final-HEAD CI for any further PR revision; Draft PR37 targets PR36, not main. | SOURCE GREEN / REVIEW REQUIRED |
| P12 | No staging evidence/Founder signoff. | BLOCKED |
| P13 | No production rollout or live re-test; do not deploy without parity. | BLOCKED |
| P14 | 7/30-day monitoring only after real production release. | NOT STARTED |

## All 55 requirements — traceability and next acceptance
Every row has at least R0; R1 means an existing partial implementation/contract in **candidate** code, not a claim of full code or approved delivery. Named tests establish **scoped R2** only where specified.

| ID | Requirement | Source/contract evidence (candidate) | Existing scoped isolated test | Next required real acceptance |
|---|---|---|---|---|
| F11-FR-001 | Reel create | `class-rsv-reels.php` | Not specifically demonstrated in this register | Real File10 published video, permission, duplicate request, denied private media |
| F11-FR-002 | Duration enforcement | `class-rsv-file10.php` | Not specifically demonstrated in this register | Real video 59/60/600/601 seconds and forged duration |
| F11-FR-003 | Educational taxonomy | `class-rsv-contracts.php` | Not specifically demonstrated in this register | Allowed subjects; unknown labels denied |
| F11-FR-004 | Reel review/publish | `class-rsv-reels.php` | tests/rc17-restoration-gate-behavior.php | Rights, consent, current verified publisher and publication gate |
| F11-FR-005 | Vertical feed | `class-rsv-frontend.php; assets/js/rsv.js` | Not specifically demonstrated in this register | Mobile swipe/keyboard, single active player, focus and navigation |
| F11-FR-006 | Autoplay policy | `assets/js/rsv.js; assets/js/rsv-top20.js` | Not specifically demonstrated in this register | Opt-in off by default; pause on background/data saver/reduced motion |
| F11-FR-007 | Ranking | `class-rsv-reels.php; class-rsv-repository.php` | Not specifically demonstrated in this register | Safety/quality weighting, donor neutrality, withdrawn exclusion |
| F11-FR-008 | Interactions | `class-rsv-reels.php; class-rsv-file10.php` | Not specifically demonstrated in this register | Like/comment/report owner callbacks, duplicate and unauthorised writes |
| F11-FR-009 | Watch progress/history | `class-rsv-reels.php; class-rsv-repository.php` | Not specifically demonstrated in this register | Owned session progress, clear, export, erase and IDOR |
| F11-FR-010 | Deep links | `class-rsv-frontend.php` | Not specifically demonstrated in this register | Direct/public URL, back link, login destination and no open redirect |
| F11-FR-011 | Moderation | `class-rsv-reels.php` | tests/rc17-restoration-gate-behavior.php | Report, appeal, restoration rights/consent recheck and invalidation |
| F11-FR-012 | Captions/accessibility | `class-rsv-frontend.php; class-rsv-file10.php` | Not specifically demonstrated in this register | Captions/transcript, screenreader and keyboard |
| F11-FR-013 | Offline/error states | `class-rsv-rest.php; class-rsv-repository.php` | tests/rc19-playback-detail-provider-errors.php | Media/SQL 503 != healthy empty 200; retry/deleted states |
| F11-FR-014 | Creator insights | `class-rsv-reels.php` | Not specifically demonstrated in this register | Creator-only aggregate metrics, minimum sample suppression |
| F11-FR-015 | Anti-abuse | `class-rsv-security.php` | Not specifically demonstrated in this register | Rate limit, idempotency, throttling fail closed |
| F11-NFR-001 | Object/field authorization | `class-rsv-security.php` | Not specifically demonstrated in this register | Role x object/field IDOR against actual File00 |
| F11-NFR-002 | Privacy lifecycle | `class-rsv-privacy.php; class-rsv-future30-privacy-integrity.php` | tests/rc18-privacy-export-query-failure.php; tests/rc18-privacy-erase-query-failure.php; tests/rc21-future30-privacy-sql-failclosed.php (23) plus tests/rc21-private-write-readfail.php (10) | Real WP privacy export/erase, revoked consent, legal hold and cache purge |
| F11-NFR-003 | Reliability | `class-rsv-jobs.php; class-rsv-db.php` | Not specifically demonstrated in this register | Retries, crash recovery, missing provider and no fabricated success |
| F11-NFR-004 | Performance | `class-rsv-repository.php; assets/js/rsv.js` | Not specifically demonstrated in this register | Staging API p95, LCP/INP/CLS p75 field evidence |
| F11-NFR-005 | Accessibility | `class-rsv-frontend.php; assets/css/rsv.css` | Not specifically demonstrated in this register | WCAG 2.2 AA, keyboard, 400% zoom, 200% text, captions |
| F11-NFR-006 | Observability | `class-rsv-diagnostics.php; class-rsv-admin.php` | Not specifically demonstrated in this register | Safe logs, trace IDs, alert routing; no patient/secret leak |
| F11-NFR-007 | Migration/rollback | `class-rsv-db.php; class-rsv-top20.php; class-rsv-future30.php` | tests/rc20-forty-schema-index-shape.php; tests/rc20-index-prefix-length.php | Real MySQL fresh/upgrade/partial/rollback, indexes, Sub_part |
| F11-NFR-008 | Operability | `class-rsv-diagnostics.php; class-rsv-plugin.php` | Not specifically demonstrated in this register | System check, admin visibility, safe repair and runbooks |
| F11-NFR-009 | Compatibility | `11-reels-foundation.php` | Not specifically demonstrated in this register | Actual WP/PHP/plugin dependency versions and contracts |
| F11-NFR-010 | Localization | `languages/reels-short-video-discovery.pot; assets/css/rsv.css` | Not specifically demonstrated in this register | Urdu/Arabic RTL, en-US, dates, mixed LTR strings |
| F11-FUT-001 | Educational Reel Series / Playlists | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File11 public Reel current-ref validation |
| F11-FUT-002 | Structured Learning Paths | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File05 current course/lesson references |
| F11-FUT-003 | Related Knowledge Button | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Current authoritative knowledge-owner link with actionable route |
| F11-FUT-004 | Source-at-Time Citation Cards | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Evidence and time bound, source opens correctly |
| F11-FUT-005 | Evidence Layer | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File06 evidence truth and withdrawn flag |
| F11-FUT-006 | Correction & Supersession System | `class-rsv-future30.php; class-rsv-future30-public-safety.php` | tests/rc21-supersession-sql-failclosed.php (14 isolated scenarios) | Versioned lineage, stale previous citation |
| F11-FUT-007 | Versioned Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Reel version/history and private metadata nonleak |
| F11-FUT-008 | Advanced Remix Studio | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File10 approved derivative, consent/rights and no patient-case default |
| F11-FUT-009 | Remix Permission Matrix | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Denied permissions cannot be forged by client |
| F11-FUT-010 | Reel Templates Library | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File10 current template media and rights |
| F11-FUT-011 | Question → Reel Answer | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | External question owner stays canonical |
| F11-FUT-012 | Collaborative Reel / Co-authoring | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File00 current coauthor consent and revoked account |
| F11-FUT-013 | Expert Review Badge | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Reviewer attestation and withdrawn review |
| F11-FUT-014 | 10-Language Reel System | `class-rsv-future30.php; trait-rsv-future30-*.php` | tests/rc9-language-quiz-behavior.php | Source +9 languages; mismatch/expired provider denied |
| F11-FUT-015 | AI Translation + Optional Dubbing | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File16 executes, File10 track and voice consent |
| F11-FUT-016 | Searchable Transcript | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File10 reviewed track, File26 query handoff |
| F11-FUT-017 | Smart Chapters / Key Moments | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Valid timestamps bounded by actual video duration |
| F11-FUT-018 | Reel Knowledge Card | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File06 cited and current evidence |
| F11-FUT-019 | Micro-Quiz after Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | tests/rc9-language-quiz-behavior.php | Private answer key and audited idempotent attempts |
| F11-FUT-020 | Save to Study Collection | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc11-study-collection-revocation.php; tests/rc11-study-collection-audit-utf8.php; tests/rc21-private-write-readfail.php | Private owner-only valid current Reel references |
| F11-FUT-021 | Reel → Personal Notes | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Private timestamp notes, secure clear/export |
| F11-FUT-022 | Ask AI About This Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Grounded context only; File16 execution; no patient data |
| F11-FUT-023 | Creator Search Opportunity Intelligence | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | File26 current privacy-safe aggregate |
| F11-FUT-024 | Expanded Why This Reel | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Explainability, mute and no donor weighting |
| F11-FUT-025 | Feed Control Center | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Real owner controls, persisted reset and mute |
| F11-FUT-026 | Knowledge Diversity Slider | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | 0–100 preference, safety overrides |
| F11-FUT-027 | Creator Research Dashboard | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Samples ≥5 and privacy-safe metrics, true UI |
| F11-FUT-028 | Pre-Publish Clinical Safety Scanner | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Fail closed with provider missing/reject; human review |
| F11-FUT-029 | Accessibility Plus Mode | `class-rsv-future30.php; class-rsv-fresh20-hardening.php` | tests/rc21-private-write-readfail.php | Language, transcript-only, reduced motion and audio description |
| F11-FUT-030 | Reel Knowledge Graph | `class-rsv-future30.php; trait-rsv-future30-*.php` | Not specifically demonstrated in this register | Current File06/File26 edges; no stale or private leakage |

## 25 full acceptance scenarios — not run on staging/live
- QA-01: 59/60/600/601s verified File10 media, forged duration — **Staging/Live: NOT VERIFIED**
- QA-02: Founder/verified doctor publish; suspended/admin impersonation denied — **Staging/Live: NOT VERIFIED**
- QA-03: File10 unlisted/private/withdrawn media and signed URL revocation — **Staging/Live: NOT VERIFIED**
- QA-04: Missing caption, cover, Source/Safety Card and review provider — **Staging/Live: NOT VERIFIED**
- QA-05: Mobile portrait swipe/keyboard, previous/next, one active player — **Staging/Live: NOT VERIFIED**
- QA-06: Autoplay opt-in, page hidden, low-data/reduced-motion/transcript-only — **Staging/Live: NOT VERIFIED**
- QA-07: SQL and File10 outage 503 vs healthy empty 200 — **Staging/Live: NOT VERIFIED**
- QA-08: Block/mute/not interested, logout-login persistence/reset — **Staging/Live: NOT VERIFIED**
- QA-09: Rate-limit SQL fail closed, duplicate impression/interactions — **Staging/Live: NOT VERIFIED**
- QA-10: Moderation restoration rechecks source/caption/rights/consent — **Staging/Live: NOT VERIFIED**
- QA-11: Three-family privacy exporter SQL failure and pagination — **Staging/Live: NOT VERIFIED**
- QA-12: Transactional privacy erasure/rollback SQL failure, Future30 included — **Staging/Live: NOT VERIFIED**
- QA-13: Private quiz answer key, 1–5 questions, idempotency — **Staging/Live: NOT VERIFIED**
- QA-14: Ten languages, wrong language/revoked provider — **Staging/Live: NOT VERIFIED**
- QA-15: AI dubbing with voice consent, expired track fail closed — **Staging/Live: NOT VERIFIED**
- QA-16: Patient-case remix default deny and rights evidence — **Staging/Live: NOT VERIFIED**
- QA-17: Revoked Reels removed from graph/series/search/collections — **Staging/Live: NOT VERIFIED**
- QA-18: Missing/wrong MySQL index, composite ordering and Sub_part — **Staging/Live: NOT VERIFIED**
- QA-19: Fresh install, interrupted upgrade, repeated migration, rollback — **Staging/Live: NOT VERIFIED**
- QA-20: IDOR/XSS/CSRF/SSRF applicable boundaries, data minimization — **Staging/Live: NOT VERIFIED**
- QA-21: English-US, Urdu/Arabic, RTL mixed-script/timezones — **Staging/Live: NOT VERIFIED**
- QA-22: WCAG 2.2 AA, keyboard, screenreader, 200% text/400% zoom — **Staging/Live: NOT VERIFIED**
- QA-23: Desktop/mobile low-bandwidth, buffering/errors/recovery — **Staging/Live: NOT VERIFIED**
- QA-24: Field LCP/INP/CLS p75, REST p95 measured on real site — **Staging/Live: NOT VERIFIED**
- QA-25: Source ZIP vs deployed plugin, schema, options/logs/hash parity — **Staging/Live: NOT VERIFIED**

## New defects and source correction status
| Defect | Root cause in exact repository candidate | Source correction | Regression evidence | Staging / Live |
|---|---|---|---|---|
| F11-D-20261113-01 | Future30 private-state export used unguarded `get_results`, allowing SQL failure to be confused with an empty/incomplete export | Override WP privacy exporter with typed 503 on bad SQL results/`last_error` | `tests/rc21-future30-privacy-sql-failclosed.php` (subset of 23 isolated assertions) | Not verified |
| F11-D-20261113-02 | Future30 private erase coerced failed `get_col` to empty and failed `get_var` to zero | Verify both read families; rollback and report incomplete | Same 23 isolated assertions | Not verified |
| F11-D-20261113-03 | Supersession history/cycle guard cast failed `get_col` into an empty active-target list; failed target/source lookup could look absent | Fail closed on SQL/invalid rows and propagate typed errors through cycle and ambiguity checks | `tests/rc21-supersession-sql-failclosed.php` (14 synthetic scenarios) | Not verified |
| F11-D-20261113-04 | Active Future30 private-state writes treated failed `get_row` as missing row, leading to unverified collection/note/preference INSERT/UPDATE | Five read points guarded before writes, including secondary history preference row; SQL failure produces WP_Error and transaction rollback | `tests/rc21-private-write-readfail.php` (6 behavioral, 4 static scenarios) | Not verified |

Existing historic RC21 `Sub_part` index prefix defect is already one prior defect, **not re-counted as new**. Existing 1 documentation consistency defect / 6 locations was corrected in predecessor PR36 and must remain separate from these two privacy defects.

## Mandatory production gate
`Freeze deployed state → compare exact source/DB/options → real staging integration → backup/restore/rollback → fix confirmed blockers → fresh exact-HEAD CI → Founder approval → signed package → controlled deployment → live retest → checksum/schema parity`.

**Repository HEAD / Deployed Version / DB Version / Migration State / Live Verification Status** must be individually recorded in the final release report. A passing GitHub action alone means **source CI green**, never Resolved.
