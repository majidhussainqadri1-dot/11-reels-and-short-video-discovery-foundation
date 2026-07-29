# File 11 — Reels and Short Video Discovery Foundation

Controlled repository for **File 11** of the **Sabri Social Homeopathy Platform**.

## Current development line

- Original evidentiary baseline: `0.1.0`
- Corrective release candidate: `0.2.0`
- Original plugin directory: `reels/`
- WordPress minimum: `6.0`
- PHP minimum: `7.4`
- Required upstream dependency: File 10 Video Wall through `SVW_Helpers` and `SVW_Interactions`

## 0.2.0 corrective scope

The corrective release addresses the independent audit blockers: inert interactions, missing progress/history/replays, non-authoritative duration, weak source/upload validation, incomplete thumbnail enforcement, missing privacy rights, weak moderation integrity, unsafe page mapping, absent schema upgrades, incorrect list ordering, inaccurate author labels, and accessibility/performance omissions.

Key controls include:

- authoritative 60–600-second duration validation;
- exact source/provider validation;
- failed-upload cleanup;
- File 10 reactions/saves/reports integration;
- private watch history with export/erasure;
- reviewer notes and audit history;
- patient-case PII screening and attestations;
- safe schema/page lifecycle;
- keyboard, reduced-motion, responsive, and pagination support.

See `CORRECTION-REPORT.md`, `CHANGELOG.md`, and `RELEASE-MANIFEST.md`.

## Evidence separation

The original 0.1.0 source evidence remains documented by:

- `SOURCE-PROVENANCE.md`
- `MANIFEST.md`
- `CHECKSUMS.sha256`
- `SOURCE-FILES.txt`

The current corrective source is documented by:

- `RELEASE-MANIFEST.md`
- `RELEASE-CHECKSUMS.sha256`
- `CORRECTION-REPORT.md`

## Quality gates

Corrective QA validates PHP 7.4/8.0/8.3/8.4 syntax, JavaScript syntax, release checksums, required security/privacy/functional contracts, and a deterministic installable ZIP artifact.

Passing repository QA is necessary but not sufficient. File 10 compatibility, WordPress runtime, upgrade/rollback, Hostinger staging, responsive/accessibility, caching, external-provider, privacy, and Founder acceptance remain mandatory before production deployment.
