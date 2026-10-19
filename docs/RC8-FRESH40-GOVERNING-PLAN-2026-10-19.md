# File 11 — Fresh 40-Round Current-Plan Audit and rc8 Correction Register

**Date:** 2026-10-19 (Pakistan).  
**Source baseline:** `45976a2abc52ef6e55aeebca1f7cd0dd84020082` (rc7 Draft PR #10).  
**Corrective source:** `codex/file11-rc8-governing40-20261019` (`1.2.0-rc8`, File 11 contract `14`).  
**Plan evidence:** Consolidated Governing Master Plan v3.0, File 11 2026-08-12 amended Master Plan, companion File 10 Media, File 25 UI and File 26 Search/Graph plans.

## Scope limitations (mandatory truth boundary)

Each of the 40 consecutive rounds in `tests/fresh40-cycle2-sequential-audit.php` rereads every plugin PHP, JS and CSS source file, checks the 15 F11-FR, 10 F11-NFR and 30 F11-FUT named IDs and one distinct source/control marker. The automated suite then runs behavioral, syntax and package integrity checks on PHP 8.1, 8.3 and 8.4. These **are 40 automated frozen-source/contract reviews**; they do not mean 40 independent human whole-repository semantic reviews or 40 real WordPress integration passes.

## Round-focused checklist

| Round | Plan and cross-owner focus | Verified scope / limitation |
|---:|---|---|
| 01 | Runtime/bootstrap identity | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 02 | F11-FR-001 creation and ownership | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 03 | F11-FR-002 duration and File10 truth | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 04 | F11-FR-003 educational taxonomy | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 05 | F11-FR-004 publish and review authority | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 06 | F11-FR-005 vertical feed | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 07 | F11-FR-006 autoplay controls | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 08 | F11-FR-007 ranking safety | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 09 | F11-FR-008 canonical interaction bridge | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 10 | F11-FR-009 private progress | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 11 | F11-FR-010 canonical deep links | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 12 | F11-FR-011 reporting/moderation | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 13 | F11-FR-012 caption/keyboard | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 14 | F11-FR-013 degraded/offline states | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 15 | F11-FR-014 creator insights threshold | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 16 | F11-FR-015 anti-abuse | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 17 | F11-NFR-001 object-level permissions | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 18 | F11-NFR-002 erasure/export | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 19 | F11-NFR-003 outbox/retry | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 20 | F11-NFR-004 query bounds | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 21 | F11-NFR-005 RTL/accessibility | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 22 | F11-NFR-006 observability/audit | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 23 | F11-NFR-007 upgrade/rollback interfaces | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 24 | F11-NFR-008 diagnostics/operations | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 25 | F11-NFR-009 WordPress/PHP compatibility | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 26 | F11-NFR-010 localization | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 27 | F11-FUT-001 public Reel series | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 28 | F11-FUT-002 File05 learning steps | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 29 | F11-FUT-003..010 public source/media relationships | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 30 | F11-FUT-011..018 question/identity/media references | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 31 | F11-FUT-019 typed quiz and private result | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 32 | F11-FUT-020..021 private collections/notes | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 33 | F11-FUT-022 File16 AI context | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 34 | F11-FUT-023 File26 search opportunities | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 35 | F11-FUT-024 why/recommendation reason | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 36 | F11-FUT-025..026 feed preferences/diversity | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 37 | F11-FUT-027 creator research | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 38 | F11-FUT-028 clinical safety scan | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 39 | F11-FUT-029 accessibility preferences | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |
| 40 | F11-FUT-030 File26 graph edges | Automated frozen-tree recheck, source/contract probe; real provider acceptance pending |

## Four code findings and corrective actions in this batch

1. **RC8-01 — Object authorization defect (File 00 / File 11):** A Reel publisher could pass CAP_PUBLISH authorization on a different owner's Reel despite lacking the CAP_MANAGE privilege. **Fixed**: foreign-owned Reel publication now explicitly requires an independently verified File 11 manager capability (admin remains authorized). Source: `includes/class-rsv-security.php`.
2. **RC8-02 — File 05 learning-path provenance gap:** Future30 learning steps were stored as arbitrary client arrays; the response filter only hid invalid steps after storage. **Fixed**: require 1–100 typed steps, each with a currently public File 05 canonical reference, plus bounded verified course IDs, rejecting invalid input before persistence. Source: `includes/trait-rsv-future30-feature-write.php`.
3. **RC8-03 — File 16 AI grounded-context information minimization:** arbitrary keys introduced by filters or nested source objects could be copied to a logged-in user-facing context. **Fixed**: explicitly allowlist top-level context fields, regenerate the canonical public Reel DTO and whitelist all evidence, knowledge-card, citation and chapter payload keys. Source: `includes/class-rsv-future30-ai-context-safety.php`.
4. **RC8-04 — File 26 recommendation-explanation malformed provider output:** nested/non-scalar provider reason entries could cause PHP coercion warnings and unbounded responses. **Fixed**: scalar-only, sanitized, deduplicated maximum twelve reasons. Source: `includes/trait-rsv-future30-user-features.php`.

## Regression evidence

- `tests/rc8-governing-plan-guards.php`: behaviorally checks ordinary publisher ownership denial, manager override, File 05 valid/withdrawn/empty/oversize source steps and exclusion of unauthorized File 16 nested patient/other private metadata. Also guards the File 26 safe reasons contract.
- `tests/run-all.sh` retains earlier rc5/rc6/rc7 tests, 40 sequential source scans, PHP/JS syntax, deterministic ZIP, checksum, archive integrity and byte-for-byte package/source parity.
- A first new-test failure was caused by unescaped variable interpolation inside the **test harness**, fixed before the final run; it was not a production plugin defect.
- Final exact SHA and run numbers must come from **post-document GitHub Actions**, not historical CI.
- Two fresh release reviews remain needed on the immutable latest candidate.

## External obstacles not removable solely through GitHub

- WPVibe `list_sites` confirmed **no WordPress sites registered**. Therefore deployed source, checksum, database version, `wp_options`, migration state, runtime logs and live/staging provider behavior cannot be inspected from this connector.
- Release law still requires founder-approved Staging tests (real File 00/05/06/10/16/26 providers), verified restore/rollback, mobile/RTL/accessibility and deployment parity before merging or production deployment.
- Preserve Draft/unmerged status; a green release artifact is **repository QA only**.

## Mandatory incident-style truth fields

Repository HEAD: exact rc8 candidate HEAD from final CI.  
Deployed Version: **unverified**.  
DB Version: **unverified**.  
Migration State: **unverified**.  
Live Verification Status: **unverified**.
