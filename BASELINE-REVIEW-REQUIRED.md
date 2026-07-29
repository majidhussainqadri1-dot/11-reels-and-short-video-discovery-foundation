# Mandatory Post-Import Review — File 11

The baseline import is not completion. The following review is mandatory before merge, release, staging activation, or deployment.

## 1. Snapshot integrity

- Confirm all six original source files are present.
- Confirm `CHECKSUMS.sha256` passes.
- Confirm `SOURCE-FILES.txt` matches the exact `reels/` inventory.
- Confirm no source file was normalized or rewritten during import.

## 2. Dependency audit

- Identify the exact File 10 repository, version, classes, constants, post types, taxonomies, metadata keys, tables, and shortcodes required by File 11.
- Verify `SVW_Helpers`, `SVW_Interactions`, and all referenced methods exist with compatible signatures.
- Verify the dependency failure path is safe during activation and ordinary runtime.
- Verify File 11 does not assume an obsolete File 10 architecture.

## 3. Security and privacy audit

- Review every form, AJAX action, admin-post action, upload, redirect, query, and metadata operation.
- Test authorization, nonces, object ownership, IDOR, CSRF, MIME validation, file-size limits, failed-upload cleanup, and privilege boundaries.
- Verify patient-case anonymity and consent are enforced rather than merely asserted by a checkbox.
- Verify private history and saved data cannot appear in public search, REST, feeds, caches, or indexing.

## 4. Data and lifecycle audit

- Review custom table schema, indexes, timestamps, migration versioning, duplicate handling, replay counting, progress bounds, and data ownership.
- Test activation twice, upgrade, deactivation, reactivation, uninstall, backup restoration, and rollback.
- Document whether retained history on uninstall is an approved policy.

## 5. Functional audit

- Test remote video sources and local uploads.
- Enforce the 60–600-second rule from authoritative media metadata, not only a submitted text field.
- Test thumbnail requirements and failure cleanup.
- Test Founder, trusted verified doctor, verified doctor, pending doctor, patient, student, anonymous visitor, and administrator permissions.
- Test moderation transitions, public visibility, saved reels, recently watched reels, watch progress, completion, replays, reactions, comments, reports, and permanent reel pages.

## 6. UI, accessibility, and performance audit

- Test desktop and mobile swipe behavior, autoplay rules, reduced motion, captions/transcripts, focus visibility, keyboard access, touch targets, contrast, overflow, loading, empty, and error states.
- Test bounded queries, pagination, cache behavior, media loading, and performance with realistic content volume.

## 7. Acceptance evidence

The corrective release must include exact branch and commit SHA, tests and logs, source/package parity, reproducible ZIP, SHA-256 checksum, manifest, migration and rollback evidence, staging screenshots, and Founder acceptance.

Any discovered defect must be corrected, retested, and accepted before work proceeds to the next phase.
