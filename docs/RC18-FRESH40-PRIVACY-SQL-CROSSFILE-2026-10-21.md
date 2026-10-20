# RC18 — File11 forty-dimension governing plan and privacy SQL corrective audit
**Date:** 21 Oct 2026 (Asia/Karachi). **Exact source candidate:** `1.2.0-rc18`, branch `audit/file11-rc18-privacy-export-sql-40-20261021`; parent `8018911c2d547263950c38048f154a6aae1ce3b2` (RC17 PR28). **Schema:** File11 1.2.0, Top20 1.1.0, Future30 1.0.0 (unchanged); contract File11 19, File26 provider 3.
**Evidence limitation:** This document lists forty source/plan verification lenses and the inherited 40-round automated frozen-tree sweeps. It does NOT represent 40 independent human line-by-line reviews, real WordPress journeys, or Hostinger live inspection.

## Approved scope and governing law
File11 August 12 amended v1.1 plan (48pp) contains native F11-FR-001–015; F11-NFR-001–010; F11-CEN-01, central CV/CEN/AJ cross-file requirements and additive Future30. Primary central plans govern free-tier policy, File26 discovery and one shell; no competing File10 media, File05 learning, File06 knowledge, File16 AI, File19 communications or File24 assurance truth tables. Other attached CF-01/CF-02/CF-03 plans remain conditional and must not be treated as current live File11 scope.

## Two new confirmed source-level defects, fixed in RC18
1. **RC18-P01 (privacy export fail-closed, high):** `RSV_Privacy::export` iterated over `RSV_Repository::history` without checking its `WP_Error`, which could fatal or misinterpret an SQL failure. It converted reports and owned-Reels `get_results` to `(array)`, causing null failures to look like no personal data. **Correction:** check typed history error; explicitly check null/non-array and `$wpdb->last_error` on each subsequent query and return WordPress-supported `WP_Error` 503 instead of finishing an incomplete archive. **Regression:** twelve isolated positive/negative SQL fixtures: 3 source stages × 3 failures (null, false, SQL last_error) plus healthy empty/no account/empty page.
2. **RC18-P02 (privacy erase retained-ownership confirmation, high):** `RSV_Privacy::erase` used `(int)$wpdb->get_var` for authored Reel count; an SQL fault could be read as zero and the erasure workflow wrongly reported `done=true`. **Correction:** require numeric, non-null count and no SQL error before committing; fail with typed `WP_Error`, transaction rollback and WordPress eraser `done=false`. **Regression:** five fault-state cases, each proving no COMMIT and confirmed ROLLBACK.

**Total newly confirmed this round:** **2 distinct code defects**; **2 repaired in candidate source**, and **17/17 new adversarial cases added**. Do not count repeated source sweeps as 40 different defects. All earlier RC17 safeguards retained.

## Forty-plan-domain inspection register
| Round | Requirement/contract lens | Source evidence scope | Confidence |
|---|---|---|---|
| 01 | Plan precedence / central governance | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 02 | File11 Reel 60–600s eligibility | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 03 | File10 canonical owner and rights | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 04 | File00 publisher role/guardian/revocation | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 05 | No paid educational/core tier or donor preference | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 06 | No unrestricted entertainment category | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 07 | Source/safety card and medical disclosures | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 08 | Native first-publish and restore safety parity | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 09 | Caption/transcript and cover | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 10 | Moderation/appeal state integrity | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 11 | WordPress protected REST object-level permissions | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 12 | Public Reel and private/unlisted visibility | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 13 | File05 learning truth nonduplication | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 14 | File06 verified knowledge/evidence links | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 15 | File12 PDF object/rights only | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 16 | File16 AI context-only; no autonomous prescribing | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 17 | File17 communication/links not duplicate messages | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 18 | File19 notification delivery not content truth | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 19 | File20 one canonical shell | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 20 | File21 post/comment engagement owner bridge | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 21 | File22 universal composer orchestration boundary | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 22 | File23 doctor/Founder dashboard projection boundary | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 23 | File24 security/privacy evidence owner | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 24 | File25 responsive RTL and accessibility owner | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 25 | File26 global search/index/ranking owner | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 26 | Future30 IDs F11-FUT-001…030 unchanged | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 27 | Future30 private note/study/quiz isolation | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 28 | Future30 language/dubbing privacy | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 29 | Future30 File26 creator research privacy thresholds | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 30 | Future30 clinical scanner human review gate | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 31 | Search/feed SQL outage non-silent | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 32 | History/review SQL errors non-silent | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 33 | Privacy exporter history SQL error/fatal guard | Inherited File11 static/contract source suite + plan traceability | Automated source/contract scope; not real staging |
| 34 | Privacy exporter report SQL error/no silent empty | RC18 explicit DB/Privacy fail-closed guard + regression fixture | Automated source/contract scope; not real staging |
| 35 | Privacy exporter authored Reel query SQL error | RC18 explicit DB/Privacy fail-closed guard + regression fixture | Automated source/contract scope; not real staging |
| 36 | Privacy erasure retained-owner SQL failure and rollback | RC18 explicit DB/Privacy fail-closed guard + regression fixture | Automated source/contract scope; not real staging |
| 37 | Privacy retention/export/erasure negative fixtures | RC18 explicit DB/Privacy fail-closed guard + regression fixture | Automated source/contract scope; not real staging |
| 38 | Migration/source/schema DB parity boundary | RC18 explicit DB/Privacy fail-closed guard + regression fixture | External acceptance remains pending (source assertions only) |
| 39 | Deterministic package/version/release-history parity | Inherited File11 static/contract source suite + plan traceability | External acceptance remains pending (source assertions only) |
| 40 | Staging Founder consent/live smoke and rollback external gate | Inherited File11 static/contract source suite + plan traceability | External acceptance remains pending (source assertions only) |

## Integrity and no-patch-stacking checkpoints
- Exact RC18 version propagated through plugin header/constants, readme, changelog, build script, static/release/manifest and CI. Historic RC1–17 records preserved.
- New privacy error codes are required by each of forty source sweeps in `tests/fresh40-cycle2-sequential-audit.php`; original 40 File00/10 behavioral scenarios, 17 restore scenarios, 40 source probes and Future30 tests retained.
- No schema, migration or canonical data-owner changes. No File16 AI runtime or File26 global ranking introduced. No paid/donor priority.
- PHP 8.1/8.3/8.4 exact-head CI, reproducible dual build, extracted source parity and ZIP/SHA-256 must pass on the final documentation commit; older builds do not certify this candidate.

## Deployment parity and unresolved gates
**Repository main HEAD:** `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (README only when reviewed).
**Source candidate HEAD:** GitHub PR/CI exact SHA, not parent RC17 SHA.
**Deployed version/source/checksum:** UNVERIFIED.
**Live DB/schema version/tables/rows:** UNVERIFIED.
**Migration/wp_options state:** UNVERIFIED.
**Live verification:** NOT PERFORMED.
No WPVibe site is registered. Until access and deployed artifact/checksum, wp_options, DB tables, logs, dependencies and configuration are examined, **exact deployed code remains unverified; repository-based diagnosis is provisional**. Consequently no claim of live resolution is permissible.
Required staging acceptance: actual File00/10/05/06/12/16/17/19/20/21/22/23/24/25/26 contract consumers, browser/RTL/keyboard/captions/low-bandwidth, clinical review/guardian tests, exporter/eraser real WordPress API, DB outage mocks vs server, backup restore, migration dry-run/rollback, Founder approved deployment, monitored production re-test and parity confirmation. Earlier HTTP 530 is symptom, not root cause.
