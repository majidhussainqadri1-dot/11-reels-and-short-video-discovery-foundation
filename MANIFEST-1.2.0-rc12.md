# File 11 Integrated Release-Candidate Manifest — v1.2.0-rc12

Created: 2026-10-20, Asia/Karachi.

## Immutable candidate identity
- Branch: `codex/file11-rc12-cross-branch-40-20261020`.
- Starting exact PR #19 source HEAD: `4335bf6cd99f894d76302c5d847bdd69648cacfd`.
- Independently corrected PR #18 source HEAD: `add69953c0f5f7e9d7f9577d245a1a42a2ac2a0e`.
- Plugin version: `1.2.0-rc12`, File 11 contract version: `18`.
- Main schema `1.2.0`, Top20 schema `1.1.0`, Future30 schema `1.0.0`; **unchanged**.
- Event contract 4; provider contract 3; Future30 contract 1.
- Package root `reels-foundation-11` and ZIP `reels-foundation-11-1.2.0-rc12.zip`.

## Source changes
- PR #18 Study Collections: reject withdrawn/private Reel references on **read**, restrict private DTO, Unicode-safe deterministic keys, and transactional audit/outbox on write. No public owner/user IDs.
- PR #18 Version History: public list includes only Reel version and verified snapshot hash, never historical titles/captions/patient details.
- PR #19 File10 template and File26 reason allowlists and validated File10 media recipe refs, HTTPS-only transcripts under CV-263.
- PR #17 proposed native File26 search adapter and aggregate threshold retained; not production activated.
- Restored rc1–rc11 chronological readme/changelog identities lost on PR #19; new rc12 historical entry.
- New/regression tests: `rc11-revocation-and-version-privacy.php`, `rc11-study-collection-audit-utf8.php`, `rc11-study-collection-revocation.php`, `rc11-release-history-contract.php`, plus inherited RC10/RC11 tests and 40 sequential source checks.

## Verification gate
`bash tests/run-all.sh` (PHP 8.1/8.3/8.4, JS syntax, all historical/adversarial tests, forty sequential source/55-ID rechecks, deterministic ZIP and source/package parity); exact SHA, CI run and artifact/checksum only after latest commit CI.

40 rounds are **automated source/contract sweeps**, not forty independent full semantic code reviews nor staging acceptance.

## Deployment truth and stop rules
`main` and production are not touched. PR candidate remains DRAFT/unmerged.
No connected WordPress site in WPVibe; earlier `sabrihomeopathy.com` connection returned Cloudflare HTTP 530, origin root cause unknown. No security controls should be weakened as a workaround.
Repository HEAD: resolve final exact GitHub commit. Deployed Version: UNVERIFIED. DB Version: UNVERIFIED. Migration State: UNVERIFIED. Live Verification Status: UNVERIFIED.
Required before release: installed plugin checksum, exact DB/schema/options/migrations, File00/05/06/10/16/26 real staging providers and tombstone/outbox, founder-approved backup/restore/rollback, post-deploy live retest and parity confirmation.
