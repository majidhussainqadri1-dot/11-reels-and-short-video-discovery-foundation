# File 11 — Reels and Short Video Discovery

Canonical repository for **File 11** of the Sabri Social Homeopathy Platform.

## Current release candidate

- Software: `1.0.0-rc2`
- Schema: `1.1.0`
- Contract: `2`
- Canonical plugin folder: `11-reels-foundation/`
- Canonical package: `11-reels-foundation-1.0.0-rc2.zip`
- Package SHA-256: `9fc1a3f34e2a275c0755bf06de5900ebd86d9d2a6d9f358f5eeb6cd5a43325ed`

RC2 supersedes RC1 after two fresh review-and-correction rounds. The legacy `reels/` implementation is removed from the RC2 branch to preserve one canonical runtime.

## Ownership boundary

File 11 owns Reel metadata/state, educational taxonomy, discovery/ranking, Reel progress/history, Reel moderation/appeals and privacy-safe aggregate insights. File 10 remains the sole owner of upload, scanning, processing, captions, playback, media rights/consent truth, secure delivery and video-level interactions. File 00 remains the sole identity, membership, suspension, guardian and publishing-assertion authority.

## Automated verification

```bash
bash tests/run-all.sh
bash tools/build-package.sh packages/11-reels-foundation-1.0.0-rc2.zip
```

The suite performs PHP lint, JavaScript syntax validation, state-machine tests, signed-cursor/tamper tests, forensic contract checks, secret-pattern checks, deterministic double build, archive integrity, one-folder verification and source/package byte parity.

## Release truth

Repository-controlled stages are complete for this release candidate: **Specified, Coded, Packaged and Automated-QA Green**. Hostinger staging acceptance, real File 00/File 10 integration, browser/device and accessibility evidence, backup/restore and rollback rehearsal, Founder sign-off, controlled live deployment and operational monitoring remain separate external gates. See `STATUS.md` and `docs/STAGING-ACCEPTANCE-1.0.0-rc2.md`.
