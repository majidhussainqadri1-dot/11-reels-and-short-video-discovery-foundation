# File 11 Immutable Candidate Manifest — 1.2.0-rc13

Date: 2026-10-20 (Asia/Karachi).
Corrective source baseline: `0cbb29e9d04f27cc7319f0a723e33acf820c95c9` (PR #20, rc12, File11 contract 18).
Supplementary SQL source correction from PR #21 (`a3597b29f8f1a806cb95a5a89d5c0eb1e0a213e1`), integrated onto latest unified candidate, not merged as an older package.
Corrective branch: `codex/file11-rc13-fresh40-integrated-20261020` — Draft, unmerged, undeployed.

Plugin: `1.2.0-rc13` | File11 contract `19` | Main DB schema `1.2.0`, Top20 `1.1.0`, Future30 `1.0.0` (unchanged).
Event contract `4`; provider contract `3`; Future30 sub-contract `1` (unchanged).
Canonical package root `reels-foundation-11`; expected ZIP `reels-foundation-11-1.2.0-rc13.zip`.

## Correction evidence

1. F11-FR-005 / F11-NFR-003 / CEN-SEARCH-001: the native public Reel Feed must not convert a failed SQL read to a fabricated successful empty result. Source now returns a typed HTTP 503 when no array arrives or `$wpdb->last_error` is non-empty. Existing REST/UI call sites propagate failure safely.
2. The README current release candidate, STATUS current contract and RELEASE-MANIFEST were stale despite prior green source CI. Current rc13 version/contract/package/workflow labels are now explicitly aligned, while old immutable rc1–rc12 changelog/readme identities remain unchanged.

Behavioral/contract regression: `tests/rc12-feed-sql-failure-behavior.php`, `tests/rc13-current-metadata-parity.php`, `tests/rc11-release-history-contract.php`.
The 40-round `tests/fresh40-cycle2-sequential-audit.php` now verifies source and caller SQL outage guards on each round. Separate forty-scoped `tests/rc10-forty-governing-source-probes.php` checks named source areas. Both are static/source-contract, **not 40 independent human whole-code or real WordPress tests**.

## Required immutable release test
- Exact HEAD GitHub Actions PHP 8.1, 8.3, 8.4 all pass.
- All inherited/privacy/provider/behavioral tests and two deterministic ZIPs identical, ZIP integrity and byte-for-byte source-to-package parity.
- Workflow artifact version `1.2.0-rc13`, SHA256 and PR status shall be obtained from the final *post-manifest* CI run.

## Required Live-First stop conditions
GitHub source is not the deployed plugin. WPVibe WordPress site remains unconnected and `sabrihomeopathy.com` previously returned Cloudflare HTTP 530. Exact origin root cause unavailable without Ray ID + underlying 1xxx error + origin DNS/hosting logs.
- Repository HEAD: final GitHub corrective SHA pending last exact-head CI.
- Deployed Version: UNVERIFIED.
- DB Version: UNVERIFIED.
- Migration State: UNVERIFIED.
- Live Verification Status: UNVERIFIED.

Do not merge to main, deploy or call production resolved until freeze of exact installed source SHA256, runtime, relevant DB/wp_options/migration/logs, live-vs-source deployment parity, real File00/05/06/10/16/26 staging providers, negative tests, backup/restore/rollback, Founder approval, live re-test and parity confirmation.
