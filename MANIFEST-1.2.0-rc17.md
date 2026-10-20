# File11 release candidate RC17 — source truth and external acceptance gates
Date 2026-10-21 (Asia/Karachi). Source branch `audit/file11-rc17-restore-parity-fresh40-20261021` based on PR27 `afa1f79ebd6791b9384eef64bc94b508a9f0ff05`.
Release `1.2.0-rc17`; schema File11 `1.2.0`; contract `19`, event `4`. No DB migration added.
Package `reels-foundation-11-1.2.0-rc17.zip` with installable root `reels-foundation-11`.
Fix: shared native Reel publication_eligibility used on first publish and moderation restore; fail closed on File10 public visibility, Source/Safety Card/captions/cover. Root cause recorded in `docs/RC17-FRESH40-RESTORE-CROSSFILE-2026-10-21.md`. Tests: 40 source rounds, 40 governing source probes, 40 File00/10 behavioral rights cases, 17 restore cases, retained suites, 3 supported PHP versions and deterministic artifact.
**Final exact SHA/checksum:** use passing CI action associated with final PR head; never borrow RC16 artifact.
**Repository main:** README only at review. **Deployed version:** unverified. **DB version:** unverified. **Migration state:** unverified. **Live verification:** not performed.
No staging/production approval, no deployment, no operational claim.
