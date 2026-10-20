# File 11 — October 20, 2026 Fresh Forty-Scope Plan/Code Audit

**Baseline:** rc9 `270d89f8fc01f737f7f8f42f6d04596fbef34854`  
**Corrective candidate:** `1.2.0-rc10` / contract 16, branch `codex/file11-rc10-readtime-plan-review-20261020`  
**Governing plans:** uploaded File 11 v1.1 amended 2026-08-12 including tables 6/7/38/39/40; uploaded consolidated 3-master-plan document; File 10 Media and File 26 Search/Knowledge Graph companion docs, plus File 05/06/16/20/25 ownership links.

## Exactly what was checked

1. **Forty distinct** plan-to-source static probes in `tests/rc10-forty-governing-source-probes.php`. Each focuses on different code and requirement guards, rather than asserting all files are correct from a single marker.
2. Existing `tests/fresh40-cycle2-sequential-audit.php` performs **forty consecutive static iterations** over the current source tree (49 PHP/JS/CSS source files) and 55 declared plan IDs (15 F11-FR, 10 F11-NFR, 30 F11-FUT).
3. Targeted semantic inspection of File05 learning-path public-read and File26 search-opportunity public-read code against plan table 38 and F30-AT-02/12; both deficiencies were corrected and tested with real PHP fixture cases.

**Critical limitation:** These are source/control/static reviews and selective behavioral unit tests, **not forty separate exhaustive line-by-line semantic audits or live WordPress integration tests**. A code marker proves presence, not completeness. Nothing in this file establishes deployed state.

## Forty-scope findings

| Round/scope | Requirement or integration | Status |
|---:|---|---|
| 1 | Reel metadata created with File10 source | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 2 | 60–600s authoritative File10 duration | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 3 | Clinical education taxonomy and disclosure | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 4 | Draft/review/publish with rights/captions | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 5 | Vertical accessible Reel feed | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 6 | Autoplay default-off/reduced motion | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 7 | Quality/diversity recommendation ranking | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 8 | Canonical interaction bridge | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 9 | Private watch history/progress | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 10 | Stable permalink + accessibility | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 11 | Report/moderate/appeal workflow | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 12 | Caption and transcript review gate | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 13 | Degraded provider/safe mode | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 14 | Aggregate creator measurements | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 15 | Rate limit/anti-abuse/idempotency | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 16 | Object/field authorization and identity | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 17 | Privacy export/erase and retention | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 18 | Durable jobs/outbox and retry | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 19 | Bounded SQL, pagination and performance | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 20 | RTL/keyboard/focus/zoom behavior | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 21 | Audit/event/reconciliation evidence | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 22 | Fresh/upgrade migrations and rollback | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 23 | Health check/repair/queue diagnostics | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 24 | WP/PHP/plugin version compatibility | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 25 | US-English/Urdu/Arabic localization | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 26 | FUT-001 current public series | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 27 | FUT-002 File05 paths, current refs | DEFECT FOUND → CODE FIXED; BEHAVIOR REGRESSION ADDED |
| 28 | FUT-005 File06 evidence grade refs | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 29 | FUT-013 verified peer-review badge | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 30 | FUT-014 canonical translated Reel links | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 31 | FUT-015 AI dubbing/voice consent | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 32 | FUT-019 server-only quiz correct answer | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 33 | FUT-020 private study collections | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 34 | FUT-021 private timestamp notes | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 35 | FUT-022 File16 grounded AI context | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 36 | FUT-023 File26 aggregate search opportunities | DEFECT FOUND → CODE FIXED; BEHAVIOR REGRESSION ADDED |
| 37 | FUT-024 neutral Why This Reel reasons | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 38 | FUT-026 diversity controls and safety | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 39 | FUT-028 safe prepublish clinical scan | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |
| 40 | FUT-030 current knowledge graph refs | SOURCE/CONTRACT CONTROL CHECKED; RUNTIME NOT YET VERIFIED |

## New proven code defects and fixes

- **RC10-D-01 (F11-FUT-002)**: `path_list()` previously returned persisted learning paths after merely removing `owner_id`, without checking that each File05 lesson/course reference was still public and valid. It could also return other ungoverned payload keys. **Corrected**: provider-current source verification of every step/course, fail-closed per path, public DTO allowlist, 100-step and 100-course ceilings. Behavior tested against live, revoked, malformed, empty and oversized fixtures.
- **RC10-D-02 (F11-FUT-023)**: the Search Opportunity provider consumer used `if($count && $count<5)` so `count=0`, missing count or null could still surface a topic, with null reported count; it also forwarded an optional source ref without current File26 source verification. **Corrected**: reject absent/non-numeric/negative/under-five aggregate values; allow only current File26 references and a restricted set of public aggregate fields. Behavior tested for 5/pass, 0/1/4/-5/null/missing/fail, withdrawn/fail, unknown private keys removed.

## Reportable quantities

- New distinct application-code defect families found: **2**.
- Source fixes made: **2** (pending final exact-HEAD QA until run completed).
- New dedicated behavioral test: `tests/rc10-current-public-reference-guards.php`.
- New forty-scope source test: `tests/rc10-forty-governing-source-probes.php`.
- Plan-vs-build release discrepancy: **1 OPEN** — original File 11 plan table 2 says installed ZIP folder `11-reels-foundation`; current tested repo builder and WordPress internal assumptions use `reels-foundation-11`. Cannot change until the exact deployed install folder, updates and Founder-approved migration/compatibility requirement are established. It is not evidence the currently deployed site has a defect.
- Live WordPress staging/production integration: **BLOCKED** — WPVibe reports zero registered websites. Previous direct connect attempt to sabrihomeopathy.com reported Cloudflare HTTP 530, which by itself does not establish root cause. Do not disable security or silently redeploy.
- Required real acceptance: File00 identity, File05 public course truth, File06 evidence, File10 video, File16 AI, File26 search/graph, File20 shell, File25 UI; WordPress/MySQL fresh/upgrade migration, 59/60/600/601 duration, quota/caches, security/privacy, mobile RTL, backup/restore, rollback and Live QA.
- Exact-head CI/package verification: must be checked again on **last commit** of this register and release manifest.
- PR must remain Draft/unmerged; no production changes.

## Mandatory five-field deployment truth

Repository HEAD: final exact rc10 branch SHA, to verify after this file is committed.  
Deployed Version: **UNKNOWN / UNVERIFIED**.  
DB Version: **UNKNOWN / UNVERIFIED**.  
Migration State: **UNKNOWN / UNVERIFIED**.  
Live Verification Status: **NOT VERIFIED**.

**Rule:** Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔
