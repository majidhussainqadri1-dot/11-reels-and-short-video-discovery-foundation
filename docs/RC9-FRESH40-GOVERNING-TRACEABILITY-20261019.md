# File 11 — Fresh Forty-Round Audit Register (rc9)

**Date:** 2026-10-19 (Pakistan). **Source baseline:** `cbfee6e2b5edf4206f2834d0ffd3534c1c9b5c86` (rc8, Draft PR #11).  
**Corrective branch:** `codex/file11-rc9-fresh40-20261019`. **Candidate:** `1.2.0-rc9` / contract 15.

## Governing sources
1. Sabri Social Homeopathy Platform Three Central Plans Consolidated Governing Master Plan (2026), File 11 ownership.
2. File 11 Reels and Short Video Discovery Master Plan v1.1 / Future Reel Knowledge Intelligence 30, amendment 2026-08-12, specifically tables 6, 7, 38 and 40.
3. File 10 canonical video, File 25 public UI, and File 26 search/knowledge graph plans supplied in the current project, plus ownership boundaries File 00/05/06/16/19/20/21/24.

## Forty separate mandatory review scopes

The automated `tests/fresh40-cycle2-sequential-audit.php` loops **40 times**, rereading all source PHP/JS/CSS and rechecking all 55 identifiers (15 FR, 10 NFR, 30 FUT), checking an additional specialized source marker in each round. The following matrix identifies distinct governing-plan scopes which must **also** be verified in staging, rather than claiming those 40 source scans are 40 human semantic reviews.

| No. | Governing requirement and integration | Repository source/contract gate | Staging / live gate |
|---:|---|---|---|
| 01 | F11-FR-001: create/metadata File10 | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 02 | F11-FR-002: authoritative duration 60–600 | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 03 | F11-FR-003: educational taxonomy | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 04 | F11-FR-004: publish/caption/consent gates | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 05 | F11-FR-005: swipe/keyboard feed | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 06 | F11-FR-006: opt-in autoplay | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 07 | F11-FR-007: education-first ranking | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 08 | F11-FR-008: canonical reactions/comments | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 09 | F11-FR-009: watch privacy and clearing | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 10 | F11-FR-010: canonical URLs/resume | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 11 | F11-FR-011: report/moderate/appeal | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 12 | F11-FR-012: caption/accessibility exception | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 13 | F11-FR-013: offline/degraded/removed states | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 14 | F11-FR-014: aggregate creator insights | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 15 | F11-FR-015: scraping/view abuse | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 16 | F11-NFR-001: identity/object/field authorization | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 17 | F11-NFR-002: privacy/retention/export/erasure | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 18 | F11-NFR-003: retries/outbox/idempotency | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 19 | F11-NFR-004: bounded queries/p95 | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 20 | F11-NFR-005: RTL/WCAG/keyboard/zoom | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 21 | F11-NFR-006: audit/observability | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 22 | F11-NFR-007: migration/rollback | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 23 | F11-NFR-008: operations/repair/diagnostics | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 24 | F11-NFR-009: WP7/PHP compatibility | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 25 | F11-NFR-010: localization/number/date | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 26 | F11-FUT-001/002: series and File05 pathways | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 27 | F11-FUT-003/004: related knowledge and timed citations | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 28 | F11-FUT-005/006: evidence sources and supersession | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 29 | F11-FUT-007/008: immutable versions and safe remix | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 30 | F11-FUT-009/010: remix permissions and File10 templates | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 31 | F11-FUT-011/012: question answer/coauthor identity | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 32 | F11-FUT-013/014: peer attestation / correct linked languages | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 33 | F11-FUT-015/016: AI translation and File10 transcripts | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 34 | F11-FUT-017/018: chapters and File06 knowledge cards | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 35 | F11-FUT-019/020: quiz answer privacy/collections | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 36 | F11-FUT-021/022: private notes/File16 grounded context | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 37 | F11-FUT-023/024: File26 search and reason transparency | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 38 | F11-FUT-025/026: feed controls and safety-first diversity | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 39 | F11-FUT-027/028: creator analytics/clinical scan | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |
| 40 | F11-FUT-029/030: accessible preferences and knowledge graph | Source and ID scan, PHP/JS tests | Not verified; requires actual provider/DB/UI evidence |

## Exact source defects found after rc8

- **RC9-D-01** — `F11-FUT-014`: write command accepted a self-linked translated Reel, a link declaring a different language from the Reel's canonical `language`, or repeated target. Corrected in `trait-rsv-future30-feature-write.php` through current canonical File 11 target lookup, identity/foreign-link check, locale match, and existing-target deduplication.
- **RC9-D-02** — `F11-FUT-019`: generic/public quiz projection passed through arbitrary question metadata after removing only three named answer fields; client could persist extra private metadata and have it returned publicly. Corrected using a strict quiz public DTO allowlist applied to both the generic and hardened REST paths, and the private quiz write schema is restricted at storage.
- **Test migration:** inherited test expected the old literal `unset(correct)` source marker, replaced with a stronger current public allowlist contract and new adversarial PHP behavioral test. This was a test fixture, not an application defect.

## New regression and closure
- `tests/rc9-language-quiz-behavior.php` includes self link, wrong locale, duplicate target/language, ninth-linked ceiling, valid target, and hostile quiz metadata checks.
- `tests/run-all.sh` retains inherited safety/migration/privacy/source-contract suites, including 40 consecutive source sweeps, with PHP 8.1/8.3/8.4, reproducible package and source parity required.
- Two post-code read-only reviews and latest exact-HEAD CI must be recorded from actual GitHub results after this document is committed.
- **Claims limited to repository source QA:** live DB, deployed files/package, schema version, wp_options, migration logs, Staging integrations and restore/rollback proof are not available. No live-resolved statement is justified.

## Five-field truth report
Repository HEAD: resolve the corrective branch's **final** commit SHA.  
Deployed Version: UNVERIFIED.  
DB Version: UNVERIFIED.  
Migration State: UNVERIFIED.  
Live Verification Status: UNVERIFIED.

**Release decision:** Draft/unmerged pending verified backup/restore, staging tests, two fresh release reviews, Founder acceptance and deployment parity. Static identity markers alone are not implementation acceptance.
