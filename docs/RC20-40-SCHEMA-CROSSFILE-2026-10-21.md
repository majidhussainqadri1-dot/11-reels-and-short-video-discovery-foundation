# File11 RC20 — Forty-dimension plan/repository cross-file review and migration parity
Date: 21 October 2026 (Pakistan).
**Audited source parent:** RC19 `8740bf18c8504e5cc4210272ea2a48a172b7c1ce` (Draft PR30).
**Separate competing source:** PR31 `bbf09aa5966d4a06710e0e266c0943033e12b9d4`, branched from RC18 and never contained RC19 File10 outage correction. Both PRs are **repository source realities** only. RC20 manually incorporates PR31 migration repair into RC19 before further hardening.
**Approved source documents:** `11-File-11-Reels-and-Short-Video-Discovery-Complete-Master-Plan-2026-v1.1-Future-Reel-Knowledge-Intelligence-30-Amended-2026-08-12.docx`, `Sabri-Social-Homeopathy-Platform-Three-Central-Plans-Consolidated-Governing-Master-Plan-2026۔۔۔4۔۔۔۔۔.docx`; relevant attached File16/17 and CF01/02/03 remain separate owners/conditional. File11 plan §§1.2, 2.4, 7.2, 11 and Central CV-283 require actual verified schema/data migrations and rollback acceptance, not simply successful code writing.

## Three proven code defects and source remediation
1. **Migration false completion (existing sibling PR31 finding).** RC19 called `dbDelta` but advanced `rsv_schema_version`, `rsv_top20_schema_version`, `rsv_future30_schema_version` without proving required tables/columns were installed. Exact PR31 source fixes carried forward: `RSV_DB::assert_statements_applied()`, fail-closed activation/runtime/repair, and 19 isolated DDL checks.
2. **Residual index-structure false success (new RC20 finding).** PR31 compared PRIMARY and named UNIQUE only. Missing secondary indexes, nonunique/unique confusion, reordered composite columns or incorrect prefix-index column positions could be certified as migrated. RC20 parses every declared PRIMARY/UNIQUE/KEY index and validates `SHOW INDEX` key name, `Non_unique`, `Seq_in_index`, `Column_name` against DDL, including prefixed `ref_code(120)`. **40 distinct positive/negative isolated tests** in `tests/rc20-forty-schema-index-shape.php` (not merely 40 repeats). No second data owner, no changed schema version.
3. **Invisible migration failure (new RC20 finding).** PR31 `RSV_Plugin::register()` returned early on SQL verification error before its `admin_notices` callback was registered and `notices()` never consumed `rsv_schema_repair_error`. RC20 registers admin notice before migration checks, presents escaped error to authorized administrator, skips diagnostics querying missing tables, and clears old warning only after successful verification. `tests/rc20-schema-admin-notice.php` contains nine isolated fail-close/no-REST/access-control behavior scenarios.

**Counts:** 3 confirmed source defects in the integrated work (1 inherited PR31 + 2 independently found RC20); 3 source corrections implemented. PR31 independent assertions retained. No live defect count or claim of production fix.

## Forty cross-plan inspection dimensions
| # | Source/owner/governance dimension | Evidence boundary |
|---:|---|---|
| 1 | Central governing ownership and no duplicated media source | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 2 | File11 60–600-second server-side validation | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 3 | File10 signed media, state and playback provider | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 4 | File00 current identity and suspended/guardian verification | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 5 | Free education vs donor/paid tier non-discrimination | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 6 | Founder/verified doctor publication rights | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 7 | File11 public content/source/safety/caption card | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 8 | Native publish/restore identical safety gates | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 9 | Public vs private/unlisted Reel/Video equivalence | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 10 | Clinical claim and consent/human review boundaries | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 11 | File21 canonical comments/reactions owner | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 12 | File22 composer and File23 doctor/Founder oversight | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 13 | File05 learning source-of-truth references | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 14 | File06 evidence and cited knowledge | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 15 | File16 grounded context/AI execution owner | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 16 | File17 communications and no direct-message clone | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 17 | File19 outbox delivery/notification ownership | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 18 | File20 single shell and navigation | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 19 | File24 assurance/audit boundaries | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 20 | File25 accessibility and RTL presentation | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 21 | File26 public-only connector and rank policy | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 22 | Future30 series and linked chapters references | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 23 | Future30 File10 remix/licensing boundaries | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 24 | Future30 translation/dubbing source privacy | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 25 | Future30 private notes and study collections | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 26 | Future30 quizzes and File05 score projection | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 27 | Future30 public provenance/reasons DTO allowlist | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 28 | Future30 personal controls and non-manipulative autoplay | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 29 | Future30 consent and per-object source ACL | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 30 | Future30 search graph edges owned by File26 | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 31 | File11 base migration primary and unique constraints | RC20 schema/adversarial behavior + inherited static scope |
| 32 | Top20 migration verification/registration | RC20 schema/adversarial behavior + inherited static scope |
| 33 | Future30 table and migration verification | RC20 schema/adversarial behavior + inherited static scope |
| 34 | Secondary index existence verification | RC20 schema/adversarial behavior + inherited static scope |
| 35 | Composite index exact ordered column shape | RC20 schema/adversarial behavior + inherited static scope |
| 36 | Column presence and SQL error fail-closed | RC20 schema/adversarial behavior + inherited static scope |
| 37 | Premature migration-option advancement blocked | RC20 schema/adversarial behavior + inherited static scope |
| 38 | Admin error visible on guarded early return | RC20 schema/adversarial behavior + inherited static scope |
| 39 | Coded package/manifest/source checksum parity | Inherited source/contract guard or plan boundary; live integration not yet proven |
| 40 | Real staging/live backup/migration/rollback acceptance | Inherited source/contract guard or plan boundary; live integration not yet proven |

## Actual executed automated checks
- **40 distinct migration mutation cases:** `tests/rc20-forty-schema-index-shape.php` manipulates columns, primary/unique/nonunique/composite index columns and sequences, SQL failures and invalid DDL. Each has independently named expected outcome.
- **19 PR31 schema assertions:** `tests/rc19-schema-failclosed-migration.php` — original mock migration verifier and plugin/Top20/Future30 integration marker checks.
- **9 admin negative/positive scenarios:** `tests/rc20-schema-admin-notice.php`.
- **40 consecutive source/frozen-tree checks:** `tests/fresh40-cycle2-sequential-audit.php` now requires the current all-index schema guard and early admin error hook.
- Additional 40 centrally governed source probes; inherited RC16 40 File00 authorization behaviors, RC17 17 restore, RC18 12 export +5 erase, RC19 12 playback and File10 feed outage regressions retained.
- CI PHP8.1/8.3/8.4, deterministic double build, ZIP extraction, exact tree/source parity and SHA-256 must pass for **final RC20 exact SHA**; earlier RC19/PR31 green runs do not certify this version.

## Honest blockers and real-world release gates
**Repository main** was README-only: `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` at audit. PR29/30/31 are unmerged sibling/stacked drafts. **RC20 must remain Draft** until controlled merge/release acceptance.
**Actual deployed code/version, DB version/tables/rows, wp_options migration checkpoint, checksum, runtime logs:** UNVERIFIED.
**WPVibe:** no site registered. Previous Cloudflare HTTP 530 from `sabrihomeopathy.com` shows failed connection only, not cause. Do not change Cloudflare, Hostinger or WP security blindly.
**Still pending:** staging fresh-install/upgrade with real MySQL SQL/Index results, provider compatibility on actual File00/05/06/10/16/17/19/20/21/22/23/24/25/26 deployments, browser/RTL/accessibility/guardian/medical safety, privacy lifecycle, backup/restore and rollback rehearsals, Founder approval, Live deployment, monitoring and post-deploy re-test and checksum parity.
**Incident truth fields:** Repository exact candidate HEAD = final PR/CI, Deployed Version = unverified, DB Version = unverified, Migration State = unverified, Live Verification Status = not performed.
**Rule:** “Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔”
