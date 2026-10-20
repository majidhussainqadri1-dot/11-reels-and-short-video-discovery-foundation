# File 11 — Immutable Release Candidate 1.2.0-rc10

Date: 2026-10-20 (Asia/Karachi).
Baseline source exact SHA: `270d89f8fc01f737f7f8f42f6d04596fbef34854` (rc9, Draft PR #13).
Corrective branch: `codex/file11-rc10-plan-audit-20261020`.
Plugin: `1.2.0-rc10` | File11 contract: `16`.
Main schema: `1.2.0` | Top20 schema: `1.1.0` | Future30 schema: `1.0.0`. No schema changes.
Event contract: 4 | provider contract: 3 | Future30 contract: 1.

Plans: Central consolidated governing Master Plan v3.0, File11 v1.1 amended 2026-08-12, File10 2026-08-11 and File26 plan; File00/05/06/16/25 boundaries.

- RC10-D-001: Future30 public learning path listing did not revalidate stored File05 course and learning-step references after revocation, nor remove undeclared private stored row fields. Fixed at read with current canonical refs, full fail-closed per-path behavior and strict public DTO.
- RC10-D-002: Future30 public Evidence Layer and Knowledge Card could relay arbitrary historical stored nested payload fields, including sensitive clinical/patient material, despite reference checks. Fixed using source-specific allowlisted payload projections for both the generic public read and public REST response.
- Behavioral tests: `tests/rc10-file05-file06-public-read.php`.
- Forty sequential full-source static/contract rounds now include all current File05 and File06 regression gates. These are automated static/contract passes, not forty human semantic/WordPress/staging or live operational passes.
- Package: `reels-foundation-11-1.2.0-rc10.zip`; build by GitHub Actions and verify SHA256, reproducibility and exact source/package parity at final HEAD.
- File11 code remains on Draft/unmerged branch. Do NOT merge, deploy or declare production resolved until real-site freeze and exact deployed plugin/source+DB+wp_options+migrations/logs, provider integration tests, founder-approved staging, backup/restore and live retest.
- WPVibe site connection is not established. Previous attempts for https://sabrihomeopathy.com failed with Cloudflare HTTP 530; do not assume the cause without source/origin diagnostics.
- Mandatory truth fields: Repository HEAD = final corrective commit (check GitHub); Deployed Version = UNVERIFIED; DB Version = UNVERIFIED; Migration State = UNVERIFIED; Live Verification Status = UNVERIFIED.
