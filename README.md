# File 11 — Reels and Short Video Discovery

Canonical repository for **File 11** of the Sabri Social Homeopathy Platform.

## Current release candidate

- Software: `1.0.0-rc3`
- Schema: `1.1.0`
- Contract: `2`
- Source folder: `11-reels-foundation/`
- Canonical installable package root: `reels-foundation-11/`
- Canonical package: `reels-foundation-11-1.0.0-rc3.zip`
- Package SHA-256: `3b0064d032224a29231afaf98d92ff69cfffd03eac2c60dba24cbb75195d0438`

RC3 supersedes RC2 after reconciliation with the Definitive Master Plan v3.0, the recovered directives and the third Continuous-Value/Top-20 Superset plan.

## Ownership boundary

File 11 owns Reel metadata/state, educational taxonomy, vertical discovery, bounded ranking signals, private Reel progress/history, Reel moderation/appeals and privacy-safe aggregate insights. File 10 remains the sole owner of upload, scanning, processing, captions, playback, media rights/consent truth and secure delivery. File 00 remains the identity, membership, suspension, guardian and publishing-assertion authority. File 21, File 25 and File 26 consume versioned read-only provider contracts; they do not become parallel Reel owners.

## RC3 harmonization

- canonical package root aligned to `reels-foundation-11`;
- approved-topic filtering with cursor/filter binding and no-JavaScript continuity;
- explainable “Why this Reel?” reasons and explicit Recommended/Latest user control;
- no paid-placement claim and no follower-count-only ranking;
- scoped CSS tokens that consume File 20/25 platform tokens with safe green fallbacks;
- accessible inline SVG icons with visible text labels;
- safe trace/reference IDs in frontend error states;
- File 21 Home cards, File 25 public timeline and File 26 search/recommendation read-only provider contracts;
- post-load keyboard/inert-state correction for dynamically appended Reels.

## Automated verification

```bash
bash tests/run-all.sh
bash tools/build-package.sh packages/reels-foundation-11-1.0.0-rc3.zip
```

The suite performs PHP lint, JavaScript syntax validation, state-machine tests, context-bound signed-cursor tests, forensic contract checks, secret-pattern checks, deterministic double build, archive integrity, canonical-root verification and source/package byte parity.

## Release truth

Repository-controlled statuses are complete for this candidate: **Specified, Coded, Packaged and Automated-QA Green**. Hostinger staging acceptance, real companion-module integration, browser/device/accessibility evidence, restore/rollback rehearsal, Founder acceptance, controlled live deployment and operational monitoring remain separate external gates.
