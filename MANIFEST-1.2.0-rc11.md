# File 11 Candidate Manifest — 1.2.0-rc11

- Review date: 2026-10-20 (Pakistan).
- Baseline GitHub corrective HEAD: `c89bf31dc081b8086d0290305944fd0687d4e26b`, previous candidate `1.2.0-rc10`.
- Branch: `codex/file11-rc11-revocation-audit-20261020`; Draft/unmerged, distinct from `main`.
- Plugin version: `1.2.0-rc11`; File 11 contract: `17`.
- Schemas unchanged: File 11 main `1.2.0`, Top20 `1.1.0`, Future30 `1.0.0`. Prior event and provider ownership unchanged.
- Governing sources: File 11 v1.1 amended 2026-08-12, Consolidated Three Central Plans (2026), File 10 Media owner, File 26 Search/Graph owner, File 00 identity, File 05 Learning, File 06 Knowledge, File 16 AI, File 20 shell and File 25 UI.
- Four code fixes:
  1. F11-FUT-020 saved private Study Collection reads revalidate every Reel reference against current File 11 publication/visibility; removed/restricted refs and unexpected keys no longer appear.
  2. F11-FUT-007 public version-history projections no longer emit historical Reel titles, captions, user-entered labels or undeclared private keys: only bounded version number and valid SHA256 are exposed in public payload.
  3. F11-FUT-020 collection mutation now commits stored state + audit + outbox evidence in one DB transaction; no success if audit/event evidence fails. Preserves the response structure with privacy-minimized DTO.
  4. F11-FUT-020 Unicode-only Study Collection titles now receive stable nonempty keys instead of collisions from an empty `sanitize_key`.
- Supporting test harness corrections: PHP WordPress `sanitize_key()` mock lowercase-first, WordPress `HOUR_IN_SECONDS` fixture constant, noninterpolating literal duration marker in 40-scope test. These are not application defects.
- Release record: WordPress `readme.txt` chronological headings rc11, rc10, ... rc1 restored without overwriting previous history; new regression guards them against recurrence (documentation defect, separate from four application-code findings).
- Tests: `tests/rc11-revocation-and-version-privacy.php`, `tests/rc11-study-collection-audit-utf8.php`, `tests/rc11-release-history-contract.php`, inherited rc5–rc10 behavioral/static/security suites, `tests/fresh40-cycle2-sequential-audit.php` (40 sequential source/55-ID checks) and `tests/rc10-forty-governing-source-probes.php` (40 distinct plan/source probes). CI must pass on PHP 8.1/8.3/8.4, JavaScript syntax, deterministic ZIP, hash and source/package parity at the exact final HEAD.
- IMPORTANT: These are automated static/source reviews, not forty independent complete human semantic reviews or actual WordPress Staging tests.
- Repository candidate's ZIP top-level folder is `reels-foundation-11`; original File 11 table lists `11-reels-foundation`. Do not rename until exact installed plugin identity, WordPress update continuity, backups and approved migration have been checked.
- Deployed Version: UNVERIFIED. DB Version: UNVERIFIED. Migration State: UNVERIFIED. Live Verification Status: UNVERIFIED. No exact deployed checksum, wp_options or runtime log available.
- WPVibe connection reported no registered WordPress sites; staging/production integration, real File00/05/06/10/16/26 provider tests, restore/rollback, security/a11y/RTL, Founder acceptance and live retest all remain gates. Keep Draft; do not merge or deploy.
- Exact candidate HEAD, GitHub Actions Run and artifact checksum: verify against GitHub after the last report commit; do not reuse a prior green CI run.
