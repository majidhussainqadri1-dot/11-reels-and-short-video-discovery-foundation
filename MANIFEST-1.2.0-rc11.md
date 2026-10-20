# File 11 Immutable Candidate Manifest — 1.2.0-rc11

Date 2026-10-20 (Asia/Karachi). Base corrective source exact SHA: `324a5ff5e402032fd99cfe4a50985876ac339cb7` (rc10).
Corrective branch: `codex/file11-rc11-fresh40-20261020`, DRAFT and unmerged.
Runtime: `1.2.0-rc11` | File 11 contract: `17`.
DB schemas unchanged: main `1.2.0`, Top20 `1.1.0`, Future30 `1.0.0`.
Event contract 4; File11 provider contract 3; Future30 contract 1.
Canonical root `reels-foundation-11`, ZIP `reels-foundation-11-1.2.0-rc11.zip`.
Governing source: consolidated master plans and File11 v1.1 amendment 2026-08-12; File10 Media and File26 Search/Ranking are canonical owners.

## Exact source findings and corrections
- RC11-D-01 — File10 templates F11-FUT-010: arbitrary client-defined nested sections and extra private/patient metadata could persist and be returned through public generic read. Fixed using bounded explicit public schema for input, public REST and native projection, including historic rows.
- RC11-D-02 — File10 media recipe input F11-FUT-010: missing write-time canonical provider validation of optional recipe ref. Added fail-closed current media provider verification, preserving read-time revocation checks.
- RC11-D-03 — File26 explanation policy F11-FUT-024: arbitrary or malformed reason codes persisted and were public. Fixed with approved noncommercial reason-code allowlist, deduplication and invalid-input handling; both public routes sanitized.
- RC11-D-04 — Central CV-263 TLS in transit: optional File10 transcript URL could return insecure HTTP. Restricted to HTTPS.
- Test: `tests/rc11-file10-file26-public-templates.php` (behavioral negatives and positives) in `tests/run-all.sh`.
- `tests/fresh40-cycle2-sequential-audit.php` rechecks every PHP/JS/CSS source and 55 named requirements in 40 consecutive rounds, with new File10/File26/TLS regressions. The result is an automated source/contract audit, not 40 human semantic whole-code reviews or a WordPress staging test.

## Acceptance
Final exact HEAD, workflow runs, artifact and SHA256 must be confirmed from GitHub after this manifest/report commit.
Do NOT merge or deploy to production without actual WordPress/Staging deployed artifact check, DB/schema/migration/rollback, current File00/05/06/10/16/26 integration and Founder acceptance.
WordPress connector unconnected; prior sabrihomeopathy.com access failed with Cloudflare HTTP 530. Cause of the 530 remains unverified; changing security configuration is not justified by this observation alone.
Mandatory reality fields: Repository HEAD = final corrective SHA; Deployed Version = UNVERIFIED; DB Version = UNVERIFIED; Migration State = UNVERIFIED; Live Verification Status = UNVERIFIED.
