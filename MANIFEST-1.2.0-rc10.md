# File 11 — Immutable Candidate Manifest — 1.2.0-rc10

- Audit / correction date: 2026-10-20 (Pakistan)
- Source baseline exact rc9 HEAD: `270d89f8fc01f737f7f8f42f6d04596fbef34854`.
- Candidate branch: `codex/file11-rc10-readtime-plan-review-20261020`.
- Plugin runtime: `1.2.0-rc10`; File 11 contract: `16`.
- Main schema `1.2.0`; Top20 schema `1.1.0`; Future30 schema `1.0.0`; unchanged.
- Event contract `4`; provider contract `3`; Future30 contract `1`; unchanged.
- Provider duties: File 10 video owner, File 05 course/lesson owner, File 06 knowledge owner, File 16 AI owner, File 26 search/graph owner; File 11 consumes current, approved refs.
- Upstream rc9 owner connector synchronized (five later commits) into candidate: public-only File 26 adapter remains status `proposed` and is never auto-activated; public author keys are opaque/HMAC, not raw WP IDs. Corresponding File26 connector source-contract test added.
- Corrections: F11-FUT-002 public learning paths now revalidate all File05 steps and course refs and emit only a minimal public DTO; F11-FUT-023 File26 aggregates enforce measured count >=5 and optional source-ref recency while removing unsafe provider keys.
- QA: `tests/rc10-current-public-reference-guards.php` (behavioral), `tests/rc10-forty-governing-source-probes.php` (40 independent plan/source static guards), plus inherited 40 consecutive full-source and 55-ID checks, unit/static/Node/PHP 8.1/8.3/8.4, reproducible ZIP SHA256 and source parity.
- Historical package-root discrepancy: File 11 plan table 2 says `11-reels-foundation`; existing tested package builder outputs `reels-foundation-11`. This is a **release gate pending verified installed plugin directory / approved migration**; silently changing it risks duplicate activation/data. Do not claim parity.
- Candidate currently tested package root: `reels-foundation-11`; intended file: `reels-foundation-11-1.2.0-rc10.zip`. This is repository package identity, **NOT** a statement of deployed package identity.
- WPVibe site list 2026-10-20: zero connected WordPress sites. Deployed artifact checksum, DB/schema, wp_options, migration state, staging tests and logs not retrievable.
- Exact final SHA, GitHub Actions run, artifact and checksum must be entered only after the final audit/report commit is verified; GitHub Actions is authoritative.
- Current status: Draft/unmerged, no live deployment. Security/privacy/medical/integration staging acceptance and Founder approval pending.
