# File11 RC21 — forty plan-to-code verification dimensions

Date: 2026-10-21 (Asia/Karachi). Current candidate 1.2.0-rc21, direct parent RC20 SHA 342076369bbd4d873c395a27f9e35739c6373de1. Governing sources: File11 v1.1 Future30 amended master plan, consolidated three central plans, companion File16/17 and relevant conditional CF plans. Conditional CF modules are not File11 live code.

## Proven defect and correction
One additional repository-level defect: RC20 `RSV_DB::assert_statements_applied()` confirmed installed index columns/order/uniqueness but ignored `SHOW INDEX.Sub_part` even when DDL specified `target_ref(120)`. Such a truncated/differently prefixed index could be certified as migrated. RC21 records expected prefix lengths and compares actual `Sub_part`, failing closed on missing, different, or unexpected prefix. Previous 40 schema cases now include eight independent prefix cases (48 total), plus the existing 40 sequential static rounds. No DB schema version changes.

## Forty source/owner/plan checks
| # | Dimension | Evidence / limit |
|---:|---|---|
| 1 | File11 canonical scope | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 2 | three central plans priority | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 3 | File00 member eligibility | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 4 | File00 Founder and doctor publisher | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 5 | File00 age/guardian safety | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 6 | single free education entitlement | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 7 | File10 raw media ownership | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 8 | File10 duration 60–600 | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 9 | File10 playback errors | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 10 | File10 signed delivery and media privacy | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 11 | public/unlisted/private visibility | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 12 | File05 learning projections | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 13 | File06 verifiable citations | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 14 | File12 reader/PDF boundary | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 15 | File16 AI execution owner | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 16 | File17 communication owner | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 17 | File19 notifications/outbox | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 18 | File20 single canonical shell | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 19 | File21 comments/interactions | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 20 | File22 composer contract | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 21 | File23 doctor dashboard | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 22 | File24 clinical/privacy assurance | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 23 | File25 RTL/accessibility | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 24 | File26 global discovery | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 25 | native create and publish gate | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 26 | moderation restore parity | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 27 | source/safety cards and captions | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 28 | watch history and consent | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 29 | privacy export SQL errors | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 30 | privacy erase rollback | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 31 | rate limiting SQL outage | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 32 | migration state versions | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 33 | core File11 columns | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 34 | Top20 and Future30 columns | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 35 | PRIMARY and UNIQUE indexes | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 36 | secondary and composite index order | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 37 | MySQL index prefix Sub_part | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 38 | PHP 8.1/8.3/8.4 behavior | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 39 | deterministic ZIP source parity | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |
| 40 | staging/live rollback approval | Existing source/contract + focused RC21 checks; actual WordPress acceptance pending |

**Automated evidence:** tests/rc20-forty-schema-index-shape.php (48 isolated cases), tests/fresh40-cycle2-sequential-audit.php (40 source rounds), tests/rc10-forty-governing-source-probes.php (40 central-plan static probes), inherited RC16–RC20 behavioral suite, CI PHP8.1/8.3/8.4, deterministic build/ZIP/parity. These are not forty independent live tests or proof of all planned features working in staging.

**Release blockers:** no connected WPVibe WordPress site; deployed plugin/source checksum, wp_options migration markers, real DB tables/indexes/rows, logs and dependencies UNVERIFIED. Repository main is README-only and RC20/RC21 are draft branches. Real Hostinger staging upgrade, File00/10/05/06/16/17/19/20/21/22/23/24/25/26 integration, data-export/erasure, accessibility, backup/restore, rollback, Founder approval, controlled production deployment and live retest remain open. Earlier Cloudflare 530 is symptom only. Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔

Repository candidate HEAD: use latest PR+CI. Deployed Version: UNVERIFIED. DB Version: UNVERIFIED. Migration State: UNVERIFIED. Live Verification Status: NOT PERFORMED.
