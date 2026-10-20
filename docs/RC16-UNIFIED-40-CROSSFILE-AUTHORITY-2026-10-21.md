# File 11 — RC16 unified forty-scope source audit and forty distinct behavior scenarios
**Date:** 21 October 2026, Asia/Karachi. **Release candidate:** 1.2.0-rc16. **Schema:** File11 1.2.0 (unchanged). **File11 contract:** 19 (unchanged).
**Authoritative source parents:** PR #25 SHA `36a811dfed3929542646a663f4c69e5ae0a67fe7`; separate PR #26 SHA `ef672de905e87a1b9d87c88b052bff9abf14b2ea`, itself forked from RC14. Both are unmerged draft source histories; neither is proof of main or live deployment.

## Plan and owner evidence
- Consolidated three governing plans: File 11 owns 60–600s educational Reels and their vertical/discovery domain; File 10 owns ingest/media/transcode/caption rights, File 20 owns shell, File 25 controls accessible presentation, File 00 owns identity, free education entitlements and professional publishing, File 26 owns global discovery. File 16 owns AI execution (not autonomous prescriptions).
- File11 inherited amended Future30 requirements: F11-FUT-001..030; 15 FR, 10 NFR, central CEN requirements, existing CV scopes remain unchanged.
- Additional related files: File05 education, File06 evidence, File17 communication, File19 notifications, File24 assurance; CF-01/CF-02 are conditional future plans and are **not** activated or fabricated by File11.
- File00 exact repository currently supplies `smc_membership_assertions`, nested `publishing.can_open_composer`, `publishing.can_direct_publish`, `entitlements.base_services.education`, `age_context`, and retired mandatory MFA. Reusing nested publishing from the same current security assertion avoids regranting rights already restricted.

## Six additional code defects independently demonstrated in candidate source
| Defect ID | Root cause | Source remedy | Validation |
|---|---|---|---|
| RC16-01 | PR26 entitlement grant is missing from PR25, so a consumer hook can override File00 `entitled` denial. | Merge necessary canonical entitlement conjunction from PR26. | `tests/rc16-entitlement-non-escalation.php` and forty-case suite. |
| RC16-02 | `base_services.reels` absent from File00's single free education tier; eligible learners could be denied. | Accept `base_services.education` (and legacy explicit reels) as necessary canonical condition. | Entitlement positive/negative scenarios. |
| RC16-03 | WordPress Administrator bypassed File00 approval/eligibility and entitlements to read protected member/entitled Reels. | Recheck current File00 eligibility/guardian for every protected viewer; no WP admin shortcut. | Admin denied scenarios. |
| RC16-04 | WordPress Administrator bypassed explicit cross-owner publication delegation. | Require dedicated File11 CAP_MANAGE when publishing another owner, even with WP admin. | Self/cross-owner and editor-grant scenarios. |
| RC16-05 | Native WordPress capability bypassed File00 `publishing.can_direct_publish` and composer denial. | File00-owned submit/direct-publish claim is necessary on each protected command. | Doctor, Founder, administrator and denied publisher tests. |
| RC16-06 | Independent `smc_publishing_assertions` fetched after File00's current/security-modified membership snapshot could restore an authority withdrawn by advanced-trust policy. | Read `publishing` from same versioned File00 membership assertion snapshot; missing field fails closed. | Snapshot-denial and absent-snapshot cases. |

## Forty distinct behavior checks and 40 sequential source scopes
`tests/rc16-forty-owner-behavior-scenarios.php` independently exercises **40 deterministic positive/negative behavior cases**:
1–8 Doctor and Founder submit vs direct-publication grants/denials; 9–18 eligibility/suspension/age/guardian/current MFA/contract subject/version; 19–28 admin identity, native capability, filter narrowing, cross-owner and explicit editor delegation, missing assertions; 29–40 free-tier/explicit entitlements, invented entitlement, administrator protected view, File10 unlisted/public and unpublished media.
`tests/fresh40-cycle2-sequential-audit.php` retains **40 consecutive frozen-source static/contract sweeps**, including per-round requirements IDs, File10/05/06/16/26, source safety, quiz, language, SQL integrity, accessibility and UI. `tests/rc10-forty-governing-source-probes.php` provides a further forty named governing source probes. None of these means forty independent human complete-code reviews or forty staging/live acceptance runs.

## Required next gates
- Run `tests/run-all.sh` at **exact RC16 SHA** on PHP 8.1, 8.3 and 8.4; PHP syntax, JS syntax, regressions, historical release records and 40 + 40 scoped tests.
- Build two identical reproducible ZIPs; compare extracted ZIP files byte-for-byte with candidate source; verify archive and checksum; publish artifact digest tied to exact same commit.
- Get **actual** File00/10/05/06/16/17/19/20/24/25/26 deployed provider contract versions and representative staging integration evidence, not repository assumptions.
- Confirm Hostinger actual installed plugins, wp_options migration checkpoints, DB tables/columns/rows, logs, checksum and live deployment parity; site access currently unavailable. Previous Cloudflare 530 is symptom evidence only, not proof of origin cause.
- Backup/restore and rollback rehearsal, browser/RTL/accessibility/medical safety privacy review, Founder approval, approved deployment, live smoke tests and monitored result are still mandatory.

## Last known reality (not cross-contaminated)
**GitHub main HEAD:** `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (README only at review).
**Working repository head:** read branch PR and exact final GitHub CI metadata.
**Deployed Version:** UNVERIFIED. **DB Version:** UNVERIFIED. **Migration State:** UNVERIFIED. **Live Verification:** NOT PERFORMED.
**Release claim:** SOURCE CANDIDATE ONLY; do not merge into main or deploy before dependency parity, qualified acceptance and Founder gate.
