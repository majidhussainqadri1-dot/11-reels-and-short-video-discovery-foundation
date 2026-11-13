# File 11 — Live-first read-only parity and staged acceptance handoff
Date: 2026-11-13 | Governing plan: Sabri Social Homeopathy Platform V12.1

## Why this document exists
The source candidate has passing synthetic CI, but the WordPress/Hostinger system is NOT connected. Earlier direct WPVibe connection returned HTTP 530, an inconclusive transport symptom. The connected WordPress.com view reports Jetpack site-scoped tools as disconnected. Neither result proves installed File11 code or live database truth.

**Mandatory incident sequence:** symptom → live evidence → exact deployed version → live DB/schema/options → source/deploy parity → root cause → code change → isolated QA → staging → approved deploy → live retest.

No database updates, admin role changes, WordPress plugin install/deactivation, Cloudflare rules, WAF edits, or migration steps are authorized by this handoff. Collect securely; do NOT paste credentials, raw patient records, auth tokens, WordPress salts, or private video URLs into chat, issue comments, or CI logs.

## Evidence owner / provenance form
| Environment | Site URL | UTC timestamp | Operator | Read-only method | Evidence ID / secure storage |
|---|---|---|---|---|---|
| Live | www.sabrihomeopathy.com | UNVERIFIED | Assign | WPVibe/Hostinger authenticated | Pending |
| Staging | Not identified | UNVERIFIED | Assign | Same approved read-only method | Pending |
| GitHub candidate | Draft PR #37, stacked on PR #36/PR #35 | Record CI UTC | Maintainer | Exact commit + deterministic artifact | Source only |

## 1. Runtime freeze — observe before changing
Record via an authenticated administrator interface or read-only WP-CLI session:
1. WordPress core version; PHP version; active theme/version; plugin inventory (name, slug, active state, version) and WordPress environment type.
2. File11 active plugin header/version and real installation directory; File10 and File00 provider versions, statuses and contract handshake results. Missing companion compatibility is a blocker, not permission to substitute a different owner.
3. Active/recent plugin PHP logs and relevant WordPress debug log entries with request IDs, times and bounded, redacted error text. Do not expose clinical or private-media data.
4. File11 configuration and dependent feature flags; cache/CDN route behavior; normal/reduced-mode user journeys; no settings mutation in the freeze.
5. Exact deployed plugin file-tree checksum manifest in a secure location. A single PHP header checksum is not whole-package parity.

Suggested read-only WP-CLI commands (use the actual server context; do not run on an inaccessible site or paste secrets):
```sh
wp core version
wp --info
wp plugin list --fields=name,status,version,update --format=table
wp theme list --fields=name,status,version --format=table
wp option get rsv_version
wp option get rsv_contract_version
wp option get rsv_schema_version
wp option get rsv_top20_schema_version
wp option get rsv_future30_schema_version
wp option get rsv_governing_plan_revision
wp cron event list --fields=hook,next_run_relative,recurrence --format=table
```
"Option not found" is meaningful evidence; never replace it with a planned version or infer migration success from an option that exists.

## 2. Actual database structure (read-only, privilege limited)
Obtain WordPress DB table prefix from the authorized environment. The prefix below is illustrative only — do not assume `wp_` in live. List all File11/Top20/Future30 tables and columns; compare with the exact candidate SQL DDL.
```sql
SHOW TABLES LIKE '<actual-prefix>rsv_%';
SHOW COLUMNS FROM <verified-rsv-reels-table>;
SHOW INDEX FROM <verified-rsv-reels-table>;
```
On every required table, record:
- Table existence, relevant column names/types/nullability, PRIMARY and UNIQUE keys, ordinary secondary indexes, composite ordered columns, and `SHOW INDEX.Sub_part` against every declared DDL prefix.
- Table charset/collation and engine for transaction safety; schema options must not be trusted without postcondition parity.
- An aggregate count `SELECT COUNT(*)` only when authorized; **never export rows, patient information or private note contents to GitHub**.
- Migration lock status, scheduled retries and relevant `wp_options` version values. If any index differs, STOP proposed application code debugging and conduct deployment/schema parity audit first.

## 3. Source-package-deployed parity
Pin the exact GitHub commit and its passing CI run; obtain its plugin ZIP and separate *internal plugin ZIP SHA256*. Record the build root `reels-foundation-11`.
Create a deterministic sorted file-checksum inventory of the installed plugin in a secure operator-only environment and compare every PHP/JS/CSS/language file against the extracted approved package.
Record mismatches by path and SHA256 only. Do not upload private WordPress files, `wp-config.php`, salts or logs to a public repository.

**Mandatory stop:** if deployed plugin differs from the candidate, do not diagnose a live bug using candidate code. Trigger Deployment-Parity Audit and document whether the live defect occurs in the actual deployed package.

## 4. Staging matrix after the freeze (not yet executed)
A. Backup verified; independent restore rehearsal and explicit rollback owner.
B. Clean File10-compatible install, RC21 upgrade from known actual schema, interrupted `dbDelta`, idempotent re-run and stale migration markers.
C. Real signed File10 media at durations 59/60/600/601 seconds, rights withdrawal, missing caption/transcript, private and unlisted clips.
D. File00 Founder/doctor/guardian eligibility, account suspension, publisher delegation, API token/permission revocation.
E. 15 native FR journeys; all 30 Future30 features (native and federated) and 10 NFR gates, each with negative cases.
F. WordPress privacy export and erase for real-test (nonpatient) accounts, malformed SQL simulated on staging with a reversible method, transaction rollback and cache/index downstream propagation.
G. File05/06/10/12/16/24/26 provider missing/malformed/revoked current refs; failure must not create false empty/success.
H. Accessibility: Urdu and Arabic RTL/LTR-mixed, captions, keyboard, screenreader, 200% font, 400% zoom, reduced motion; mobile and slow network.
I. Security: OWASP-style IDOR/CSRF/XSS, unauthorized downloads, hidden quiz answers, donation ranking neutrality, private graph edges.
J. Performance: measure real LCP/INP/CLS at p75 and REST latency p95 with real representative devices and traffic — do not substitute CI or Lighthouse lab scores.
K. Exact ZIP extraction parity, staging install logs, runtime signature, schema/options and independent test-reviewer's recorded approval.

Each test row must include `ID, actual deployed SHA, environment, data fixture, actor, expected, actual, evidence, reviewer, date, pass/fail, defect ID`. A pass from a synthetic PHP fixture is **not** a staging pass.

## 5. Release decision and rollback
Go only when (1) exact production candidate approved, (2) High/Critical defects resolved at staging, (3) legal/privacy review completed, (4) independently tested restore+rollback, (5) founder has explicitly accepted, and (6) release manifest and checksum pinned.

At deploy, verify installed plugin directory and version, true DB tables/indexes/rows/options and migration status *before* treating a success banner as a success. Run immediate public/private role, video, publishing and privacy smoke tests, preserve rollback option and monitor redacted incidents. Without this post-deploy live retest, a source fix is not Resolved.

## Current blockers
- Exact deployed code **unverified**; repository-based diagnosis provisional.
- Live/staging WordPress read-only access absent (HTTP 530 symptom, Jetpack disconnected).
- File00/File10/current other owner integrations and real MySQL version parity unverified.
- Production browser tests, clinical-safety review, privacy erasure, backup/restore, rollback rehearsal and founder sign-off not performed.
- Draft PR #35 (RC21), #36 (docs), #37 (source SQL corrections) not merged into `main`.

## Final mandatory status fields
Repository HEAD: `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (main, README-only at last read)
Deployed Version: UNVERIFIED
DB Version: UNVERIFIED
Migration State: UNVERIFIED
Live Verification Status: NOT PERFORMED
