# File 11 — Fresh Forty-Round Source/Plan/Related Files Review (rc11)

Date: 2026-10-20 (Pakistan). Baseline source SHA: `324a5ff5e402032fd99cfe4a50985876ac339cb7`.
Branch: `codex/file11-rc11-integrated-file26-20261020` (source merged with proposed File26 connector upstream SHA `c89bf31dc081b8086d0290305944fd0687d4e26b`); current candidate `1.2.0-rc11`, File11 contract 17.

## Evidence and claim boundary
The forty rounds are **40 sequential automated frozen-tree/source/contract sweeps**, not 40 independent human semantic full-code reviews. `tests/fresh40-cycle2-sequential-audit.php` rereads all 49 PHP, JS and CSS plugin files each pass, checks 55 stable plan IDs and a different focused source marker; it now also verifies File05 revoked-learning-path, File06 public reference/PII, File10 template source/current recipe, File26 public reason controls, and File10 HTTPS transcript guards. `tests/run-all.sh` executes new and inherited behavioral tests under PHP 8.1, 8.3, 8.4 and checks reproducible ZIP/source checksum parity. A source marker alone does not prove runtime functionality or a completed planning requirement.

## Forty distinct governing-plan scopes

| Round | Requirement | Meaning | Verification / unresolved boundary |
|---:|---|---|---|
| 01 | F11-FR-001 | Create Reel/File10 selected media | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 02 | F11-FR-002 | 60–600 seconds authoritative File10 | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 03 | F11-FR-003 | Approved educational topic labels | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 04 | F11-FR-004 | Publish workflow/caption/rights/safety | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 05 | F11-FR-005 | Swipe/button/keyboard vertical feed | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 06 | F11-FR-006 | Autoplay opt-in/reduced motion | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 07 | F11-FR-007 | Educational ranking and fairness | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 08 | F11-FR-008 | Owner-mediated likes/comments/reactions | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 09 | F11-FR-009 | Private watch history and erasure | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 10 | F11-FR-010 | Canonical Reel/profile/video deep links | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 11 | F11-FR-011 | Medical safety/appeal/moderation | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 12 | F11-FR-012 | Captions/transcripts and accessible controls | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 13 | F11-FR-013 | Offline/removed/media-degraded states | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 14 | F11-FR-014 | Aggregate creator metrics and anonymity | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 15 | F11-FR-015 | Spam/report/scraping anti-abuse | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 16 | F11-NFR-001 | Object/field authorization File00 | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 17 | F11-NFR-002 | Purpose/privacy/export/erasure | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 18 | F11-NFR-003 | Retry/outbox/idempotency | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 19 | F11-NFR-004 | Pagination and measured performance | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 20 | F11-NFR-005 | Keyboard/RTL/zoom/motion | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 21 | F11-NFR-006 | Audit/alerts/diagnostics | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 22 | F11-NFR-007 | Migration, fresh install and rollback | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 23 | F11-NFR-008 | Safe system check and repair | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 24 | F11-NFR-009 | WordPress/PHP and provider parity | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 25 | F11-NFR-010 | English/Urdu/Arabic localization | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 26 | F11-FUT-001/002 | FUT-001/002 Series and File05 learning paths | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 27 | F11-FUT-003/004 | FUT-003/004 Related knowledge and timed sources | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 28 | F11-FUT-005/006 | FUT-005/006 File06 evidence and supersession | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 29 | F11-FUT-007/008 | FUT-007/008 Reel versions and approved remix | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 30 | F11-FUT-009/010 | FUT-009/010 Remix permission and File10 templates | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 31 | F11-FUT-011/012 | FUT-011/012 Question answer and coauthor | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 32 | F11-FUT-013/014 | FUT-013/014 Peer attestations and languages | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 33 | F11-FUT-015/016 | FUT-015/016 File16 AI translation and File10 transcripts | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 34 | F11-FUT-017/018 | FUT-017/018 Chapters and File06 knowledge cards | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 35 | F11-FUT-019/020 | FUT-019/020 Quiz privacy and collections | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 36 | F11-FUT-021/022 | FUT-021/022 Personal notes and grounded File16 AI | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 37 | F11-FUT-023/024 | FUT-023/024 File26 discovery and explanatory reasons | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 38 | F11-FUT-025/026 | FUT-025/026 Feed controls and knowledge diversity | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 39 | F11-FUT-027/028 | FUT-027/028 Creator research and clinical scan | Source/contract static pass; real WordPress provider/browser acceptance pending |
| 40 | F11-FUT-029/030 | FUT-029/030 Accessibility and File26 knowledge graph | Source/contract static pass; real WordPress provider/browser acceptance pending |

## Defect ledger

- **RC11-D-01 — F11-FUT-010, File10 template public fields:** unsafe arbitrary `sections` and extra persisted clinical/owner metadata were not explicitly allowlisted. Fixed at input/output on both public read paths, including legacy objects; limit 12 sections and bounded fields.
- **RC11-D-02 — F11-FUT-010 File10 media recipe:** optional recipe could be written before current File10 provider approval. Fixed with fail-closed write-time callback, retaining current provider validation during read.
- **RC11-D-03 — F11-FUT-024 File26 explanatory reasons:** arbitrary user-supplied or payment/donation reason codes were stored and publicly projected. Fixed approved noncommercial reason allowlist, deduplication, bounded public view, and reject-empty policy.
- **RC11-D-04 — CV-263/File10 secure transcript URL:** source accepted HTTP as well as HTTPS, permitting transport downgrade. Fixed HTTPS-only output.
- Separate test-harness syntax/error discovered by initial CI was corrected before final release run; it is not counted as a fifth application-code defect.

## Follow-up and blockers

- The affected behavioral test `tests/rc11-file10-file26-public-templates.php` covers valid/invalid File10 recipes, arbitrary nested template fields, malformed File26 reasons, payment reason rejection, and HTTP vs HTTPS transcript URLs.
- After code correction, all affected, inherited and forty source sweeps must pass at the **exact final HEAD**.
- GitHub mergeability state should be explicitly verified, not assumed from CI.
- WPVibe has no linked WordPress website. sabrihomeopathy.com previously returned Cloudflare HTTP 530 on connector attempts. This does not identify which upstream hop failed.
- Deployed source/checksum, actual active plugin, DB/schema, wp_options, migrations, runtime logs, backup restoration, File00/05/06/10/16/26 true providers, Staging and live retesting remain *UNVERIFIED*.
- No claim of production resolution, deployment, or staging approval is justified from the repository artifact alone.

Mandatory reality: Repository HEAD = final GitHub SHA; Deployed Version = UNVERIFIED; DB Version = UNVERIFIED; Migration State = UNVERIFIED; Live Verification Status = UNVERIFIED.

## File26 cross-branch parity audit (post-report addition)

A previously separate draft PR #17 contained a proposed native File11→File26 search connector and mandatory minimum sample threshold >=5 / opaque public source controls. This integrated branch is based on PR #17 exact code HEAD and deliberately preserves the entire File26 adapter, `trait-rsv-future30-user-features.php` k-anonymity improvements and its native File26, current-reference and additional forty-probe PHP tests. Only changed rc11 code paths were merged into that baseline; older File05 `public_learning_path_rows` safe helper was preserved and regression tests aligned with the current name.

These proposed connector bits MUST remain disabled/not asserted as live without matching real File26 staging provider, tombstone lifecycle, File00/10 rights/visibility and approval. This resolves the known **source-branch divergence** in the new candidate, but does not prove deployed parity or live safety.

Final exact HEAD and CI proof to be filled from the GitHub Actions run after this document commit.
