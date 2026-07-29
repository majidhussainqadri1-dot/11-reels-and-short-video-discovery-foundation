# Release Manifest — File 11 Corrective Candidate 0.2.0

## Identity

- Plugin: Reels and Short Video Discovery Foundation
- Plugin directory: `reels/`
- Plugin version: `0.2.0`
- Database schema version: `2`
- Corrective branch: `fix/file-11-corrective-release-0.2.0`
- Baseline parent: `57f1c38ce9d775a24218950d6d1a3dddf71750af`
- Source files: **6**

## Corrective source inventory

| Path | SHA-256 |
|---|---|
| `reels/assets/reels.css` | `91404e5cda381de48d0e5a871376f05d9dec94338dd64de4e13164c53a9a9cc8` |
| `reels/assets/reels.js` | `179f359116244041623b16c98deff451407df1c8cde47b77ea2e65d82ab77f31` |
| `reels/includes/class-srl-plugin.php` | `09b871b212dd6f17ce41e70b1714df8b9d496973059bcb59ce4c1c71e0e7130e` |
| `reels/readme.txt` | `11d388677e352068cf1e30747ea7e5a8db7acd26517c529df2a7c879ba5668f6` |
| `reels/reels.php` | `b84c402e000bdc0fbfe9cfe0101706970f5aff6afbf719fc04974eb12baf7a4b` |
| `reels/uninstall.php` | `77fbc79897e8394e16f997c0a49af4d1613799c3cb34a88c923a7f22dbb82b10` |

The machine-readable copy is `RELEASE-CHECKSUMS.sha256` and is verified by Corrective QA.

## Package contract

Corrective QA builds:

`11-reels-and-short-video-discovery-foundation-0.2.0.zip`

The package must contain one top-level `reels/` directory and the exact six source files listed above. The workflow normalizes source timestamps and uses `zip -X` before testing archive readability and publishing the ZIP plus `package.sha256` as a workflow artifact.

## Evidence boundary

This manifest establishes source identity only. It does not establish WordPress runtime, File 10 integration, external-provider availability, staging acceptance, backup restoration, rollback, or production readiness.
