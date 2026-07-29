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

## Package evidence

- Package: `11-reels-and-short-video-discovery-foundation-0.2.0.zip`
- Package SHA-256: `502f2dc4448009b10803d6012e60659070af16fbdf65ae7f3ceeee57ae880798`
- Corrective QA run: `30484673037`
- QA commit: `c93b1209e09cbfab39539b9c6e1f9453f2729357`
- Workflow artifact: `file-11-corrective-package`
- Artifact ID: `8737116365`
- Artifact archive digest: `sha256:b0e8bce73c75bc412cadd0e3b364ea6d32c3cb580d3464a83821f5bfbd5c124a`

The package contains one top-level `reels/` directory and the exact six source files listed above. Corrective QA normalized source timestamps, built with `zip -X`, tested every archive entry, generated `package.sha256`, and uploaded the ZIP and checksum as a workflow artifact.

## Automated QA result

- PHP 7.4 syntax and corrective contracts: **PASS**
- PHP 8.0 syntax and corrective contracts: **PASS**
- PHP 8.3 syntax and corrective contracts: **PASS**
- PHP 8.4 syntax and corrective contracts: **PASS**
- JavaScript syntax: **PASS**
- Source checksums: **PASS**
- Reproducible archive construction and integrity: **PASS**
- Artifact upload: **PASS**

## Evidence boundary

This manifest establishes repository-level source and package identity. It does not establish WordPress runtime, exact File 10 integration, external-provider availability, staging acceptance, backup restoration, rollback, or production readiness.
