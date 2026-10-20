# File 11 — Fresh 40-Scope Source/Plan Review, Corrective Batch rc11

Date: 2026-10-20, Pakistan.  
Frozen starting exact HEAD: `c89bf31dc081b8086d0290305944fd0687d4e26b` (rc10).  
Corrective branch: `codex/file11-rc11-revocation-audit-20261020`, candidate version `1.2.0-rc11`, File 11 contract `17`.  
Primary plans: uploaded amended File 11 Master Plan 2026-08-12, consolidated Three Central Plans 2026, File 10 media plan and File 26 global discovery plan. These plans distinguish approved specification from deployed software. Founder approval does not constitute code/staging/live acceptance.

## Audit discipline and scope

The source code was examined for the forty subjects below, and stronger adversarial PHP tests added where concrete deficiencies were demonstrated. The exact candidate's CI also runs **40 successive full-source static checks** across PHP/JS/CSS and 55 stable F11-FR/NFR/FUT IDs, and **40 separate plan-to-source probes**. These are automated source/control checks; no assertion of forty independent manual exhaustive code reviews, staging simulations or real device tests is made. A presence marker cannot prove full functional completeness.

## Forty plan-specific review areas

| # | Plan ID | Plan/owner subject | Demonstrated scope and limits |
|---:|---|---|---|
| 01 | F11-FR-001 | Reel creation / File10 media ownership | Source/contract checked; real staging acceptance not verified |
| 02 | F11-FR-002 | 60–600 seconds verified by File10 | Source/contract checked; real staging acceptance not verified |
| 03 | F11-FR-003 | Educational topic classification | Source/contract checked; real staging acceptance not verified |
| 04 | F11-FR-004 | Publish rights, patient consent and captions | Source/contract checked; real staging acceptance not verified |
| 05 | F11-FR-005 | Vertical one-item feed and keyboard | Source/contract checked; real staging acceptance not verified |
| 06 | F11-FR-006 | Autoplay/reduced motion/data saver | Source/contract checked; real staging acceptance not verified |
| 07 | F11-FR-007 | Quality/safety-first recommendation ranking | Source/contract checked; real staging acceptance not verified |
| 08 | F11-FR-008 | Interaction, report and follow owner bridges | Source/contract checked; real staging acceptance not verified |
| 09 | F11-FR-009 | Private watch history and reset | Source/contract checked; real staging acceptance not verified |
| 10 | F11-FR-010 | Canonical links and resume | Source/contract checked; real staging acceptance not verified |
| 11 | F11-FR-011 | Medical moderation and appeal | Source/contract checked; real staging acceptance not verified |
| 12 | F11-FR-012 | Caption/transcript accessibility | Source/contract checked; real staging acceptance not verified |
| 13 | F11-FR-013 | Provider offline/degraded states | Source/contract checked; real staging acceptance not verified |
| 14 | F11-FR-014 | Privacy-protected creator insight aggregates | Source/contract checked; real staging acceptance not verified |
| 15 | F11-FR-015 | Rate limit, view/interaction abuse | Source/contract checked; real staging acceptance not verified |
| 16 | F11-NFR-001 | File00 identity and object-level authorization | Source/contract checked; real staging acceptance not verified |
| 17 | F11-NFR-002 | Data lifecycle, export/erasure and minimization | Source/contract checked; real staging acceptance not verified |
| 18 | F11-NFR-003 | Transactional audit/outbox/retry | Repository defect corrected; behavioral checks added |
| 19 | F11-NFR-004 | Query bounds, paging and performance | Source/contract checked; real staging acceptance not verified |
| 20 | F11-NFR-005 | Keyboard, focus, RTL, accessibility | Source/contract checked; real staging acceptance not verified |
| 21 | F11-NFR-006 | Diagnostics, audit and trace ownership | Source/contract checked; real staging acceptance not verified |
| 22 | F11-NFR-007 | Idempotent migration/rollback | Source/contract checked; real staging acceptance not verified |
| 23 | F11-NFR-008 | System check and repair | Source/contract checked; real staging acceptance not verified |
| 24 | F11-NFR-009 | WordPress/PHP/version compatibility | Source/contract checked; real staging acceptance not verified |
| 25 | F11-NFR-010 | US English/Urdu/Arabic localization | Source/contract checked; real staging acceptance not verified |
| 26 | F11-FUT-001 | Revocation-safe Reel Series | Source/contract checked; real staging acceptance not verified |
| 27 | F11-FUT-002 | File05 public current Learning Paths | Source/contract checked; real staging acceptance not verified |
| 28 | F11-FUT-005 | File06 current evidence-source truth | Source/contract checked; real staging acceptance not verified |
| 29 | F11-FUT-006 | Correction and supersession safety | Source/contract checked; real staging acceptance not verified |
| 30 | F11-FUT-007 | Version history protected from historical caption/PII | Repository defect corrected; behavioral checks added |
| 31 | F11-FUT-008 | Remix current rights and File10 derivative | Source/contract checked; real staging acceptance not verified |
| 32 | F11-FUT-012 | File00 coauthor identity/acceptance | Source/contract checked; real staging acceptance not verified |
| 33 | F11-FUT-013 | Verified expert review badge | Source/contract checked; real staging acceptance not verified |
| 34 | F11-FUT-014 | Canonical translated language links | Source/contract checked; real staging acceptance not verified |
| 35 | F11-FUT-016 | File10 reviewed public transcript | Source/contract checked; real staging acceptance not verified |
| 36 | F11-FUT-019 | Private quiz answers and educational scoring | Source/contract checked; real staging acceptance not verified |
| 37 | F11-FUT-020 | Private Study Collections, revocation, Unicode, audit | Repository defect corrected; behavioral checks added |
| 38 | F11-FUT-022 | File16 grounded context and minimization | Source/contract checked; real staging acceptance not verified |
| 39 | F11-FUT-023 | File26 aggregate search, count ≥5 | Source/contract checked; real staging acceptance not verified |
| 40 | F11-FUT-030 | File26 knowledge graph current references | Source/contract checked; real staging acceptance not verified |

## Verified code defects discovered and corrected in this batch

1. **RC11-D-01 / F11-FUT-020 — Revoked private collection refs.** Previous `collections()` returned saved User State unchanged, even when linked Reels had later been removed, restricted or privately hidden. Corrected using current File 11 provider-visibility checks on every read, duplicate and malformed ref removal, and a minimal response DTO. Behavioral tests simulate public, revoked, private, repeated, malformed and withdrawn-only refs.
2. **RC11-D-02 / F11-FUT-007 — Historical Reel version leakage.** Previous version snapshot persistence included `title` and `caption`, and the generic public and REST safety readers did not both strip those historical fields or untrusted labels. Corrected: public version projections are restricted to safe version number and validated 64-hex SHA256; public label becomes generated `Version N`, with owner/reel IDs hidden. Original stored history is retained for authorized nonpublic audit as before. Behavioral tests reject historical medical/private text exposure.
3. **RC11-D-03 / F11-FUT-020; NFR-003 — Unproven private mutation audit.** Collection Update was written via `state_put()` without mandatory coupled audit/outbox evidence. Corrected with `RSV_DB::transaction` to commit state, privacy-safe Audit and an opaque-user-ref Outbox event atomically, rolling back on evidence failure. Existing array response semantics preserved with safe projections. Unit mock simulates audit failure and rollback.
4. **RC11-D-04 / F11-FUT-020; NFR-010 — Urdu-only keys.** `sanitize_key($name)` may return an empty key for Arabic-script-only titles, causing collisions. Corrected with stable deterministic `study_<SHA256prefix>` fallback, while retaining standard slugged keys for ASCII names. Tested with two different Urdu names.

**Additional documentation defect (separate from 4 code defects):** WordPress plugin `readme.txt` historical changelog headings previously all displayed the newest RC identity (rc11), destroying release provenance. Reconstructed rc11, rc10, ... rc1 ordering by comparison with the existing accurate `CHANGELOG.md`; new `tests/rc11-release-history-contract.php` prevents reoccurrence.

**Test-harness failures (separate from production code defects):** corrected inaccurate WordPress `sanitize_key` mock, missing `HOUR_IN_SECONDS` isolated test constant, and double-quoted `$duration` source markers. These failures were surfaced by CI; tests rerun after fixes.

## Evidence and residual gates

New behavioral tests: `tests/rc11-revocation-and-version-privacy.php`, `tests/rc11-study-collection-audit-utf8.php`, `tests/rc11-release-history-contract.php`. All rc5–rc10 source/behavior checks remain in `tests/run-all.sh`, including 40 source scans and 40 distinct plan probes. Last exact SHA and GitHub Action result must be checked after this document and release manifest are committed. Package and source parity required on the final SHA, not inherited from prior green runs.

### Blockers

- **Release folder discrepancy remains unverified:** File 11 original approved plan lists top-level folder `11-reels-foundation`, while currently tested ZIP uses `reels-foundation-11`. Changing installed plugin folder without freeze/parity/backup may duplicate a plugin or break updates; this is a **release compatibility blocker**, not proof of a live incident.
- WPVibe has no registered WordPress site; earlier attempted public site connection returned HTTP 530. Exact deployed plugin source checksum, DB/schema version, `wp_options`, migration lock/checkpoint state and live logs cannot be established. Do not remove Cloudflare security controls without diagnosis.
- Staging integration with File00 identity, File05 Learning, File06 evidence, File10 canonical media, File16 AI and File26 search/graph is required, plus File20/25 UI, File24 safety, keyboard/RTL/mobile, fresh/upgrade migration/rollback and backup restore, Founder acceptance, actual deploy and live re-test.
- Keep all source work on Draft PR. No changes have been merged into main or deployed to production.

### Mandatory five-field truth report

Repository HEAD: resolve rc11 branch exact final HEAD after last audit commit.  
Deployed Version: **UNVERIFIED**.  
DB Version: **UNVERIFIED**.  
Migration State: **UNVERIFIED**.  
Live Verification Status: **UNVERIFIED**.

**Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔**

This audit has no authoritative clock for minutes of active human-like semantic study. CI timings measure automation only; do not assert a false 760-minute manual review.
