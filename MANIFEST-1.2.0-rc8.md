# File 11 Release-Candidate Manifest — 1.2.0-rc8

- Date: 2026-10-19 (Pakistan).
- Source baseline: `45976a2abc52ef6e55aeebca1f7cd0dd84020082` (rc7, draft PR #10).
- Candidate: `1.2.0-rc8`; File 11 contract: `14`.
- Main schema: `1.2.0`; Top20 schema: `1.1.0`; Future30 schema: `1.0.0`.
- Event contract: `4`; provider contract: `3`; Future30 contract: `1`.
- Governing plan revision: `2026-08-12`.
- Package root: `reels-foundation-11`; artifact: `reels-foundation-11-1.2.0-rc8.zip`.
- Source changes: File 11 publish requires Reel ownership or explicit manager capability; File 05 learning path validates every public step and bounds references; File 16 AI context passes allowlisted, sanitized provider/source metadata only; File 26 explanation reasons are bounded and scalar-only.
- Additional test: `tests/rc8-governing-plan-guards.php`.
- Full suite: `tests/run-all.sh`, including 40 sequential PHP/JS/CSS all-source rechecks and stable 15+10+30 File 11 requirement ID checks; these are automated static/contract audits, not forty human semantic, runtime or integration audits.
- Automated gate: PHP 8.1/8.3/8.4, JS syntax, all inherited and current regression tests, repeatable ZIP and SHA256, byte-for-byte package source parity. Exact HEAD/run/artifact IDs only after final manifest and audit report commit.
- Live Reality Freeze: no WordPress site connected for WPVibe inspection on 2026-10-19; deployed plugin files, checksums, DB/schema, wp_options and migration logs unverified.
- Staging, Founder approval, backup restore, rollback evidence, cross-file real providers and live smoke test: pending; do not merge/deploy.
