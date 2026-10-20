# File 11 Immutable Candidate Manifest — 1.2.0-rc12

Date: 2026-10-20 (Pakistan); base HEAD: `add69953c0f5f7e9d7f9577d245a1a42a2ac2a0e` (rc11 candidate / Draft PR #18).
Corrective branch: `codex/file11-rc12-feed-query-failclosed-20261020`, Draft/unmerged.
Plugin version: `1.2.0-rc12`. File 11 contract: `18`.
Main schema: `1.2.0`; Top20 schema: `1.1.0`; Future30 schema: `1.0.0`; unchanged.
Event contract: 4; provider contract: 3; Future30 contract: 1; unchanged.
Canonical repository package directory: `reels-foundation-11`; candidate artifact `reels-foundation-11-1.2.0-rc12.zip`. **This is repo-only packaging truth, not installed plugin identity.**

## Corrective source change
F11-FR-005 + F30-AT-10 + CEN-SEARCH-001: previously `RSV_Repository::feed()` cast the result of `$wpdb->get_results()` to an array, turning SQL failure/null into a successful zero-result response. It now checks the query outcome and `$wpdb->last_error`, returning typed `rsv_feed_query_unavailable` (HTTP 503) on failure and a normal empty feed only on a successful zero-row query.

## Evidence
- `tests/rc12-feed-sql-failure-behavior.php`: native PHP behavioral tests for SQL null, SQL error, and valid empty results.
- `tests/rc10-forty-governing-source-probes.php`: forty distinct plan/source controls plus explicit F30-AT-10 SQL outage marker.
- `tests/fresh40-cycle2-sequential-audit.php`: forty consecutive static checks of available PHP/JS/CSS source and all 55 File 11 FR/NFR/FUT IDs; **not 40 human semantic reviews**.
- All previous rc5–rc11 tests, syntax, PHP 8.1/8.3/8.4, deterministic ZIP, checksum and package/source parity remain mandatory.
- Historical release changelog remains intact and WordPress readme now includes rc12; immutable history test extended to 12 entries.
- Final exact-HEAD SHA, Actions result and artifact must be checked **after** committing this manifest and audit register.

## Release / live boundary
Original approved File 11 plan top-level folder: `11-reels-foundation`; current tested ZIP root: `reels-foundation-11`. Do not rename or deploy without exact installed plugin, WP upgrade path and Founder-approved parity/migration assessment.
WPVibe site connector list: no registered WordPress site. Connection request to `https://sabrihomeopathy.com` returned Cloudflare HTTP 530 with no 1xxx diagnostic; this does not prove root cause. No security bypass or host modifications performed.
Deployed Version: UNVERIFIED; DB Version: UNVERIFIED; Migration State: UNVERIFIED; Live Verification Status: UNVERIFIED. Staging, File00/05/06/10/16/26 real provider integration, backups/restore/rollback, browser/mobile/RTL accessibility, security and Founder acceptance remain pending.
No merge or production deployment approved. Green GitHub CI is NOT live defect resolution.
