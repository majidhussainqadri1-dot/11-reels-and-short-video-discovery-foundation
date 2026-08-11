# File 11 — Reels and Short Video Discovery

**Current repository candidate:** `1.2.0-rc1`  
**Future30 amendment:** `SSH-F11-FUTURE-REEL-KNOWLEDGE-INTELLIGENCE-30-2026`  
**Canonical package root:** `reels-foundation-11`

File 11 owns the vertical educational Reel entity, the authoritative 60–600-second Reel eligibility rule through File 10, Reel-native discovery, moderation, private history/progress and the approved Reel-domain Future30 orchestration layer. It does not duplicate File 10 raw media, File 05 learning truth, File 06 canonical knowledge, File 16 AI execution, File 00 identity, File 19 delivery, File 20 shell, File 24 assurance, File 25 visual system or File 26 cross-platform discovery/graph ownership.

## Future30 v1.2.0-rc1

The 2026-08-12 Founder-approved additive amendment introduces `F11-FUT-001` through `F11-FUT-030`:

1. Educational Reel Series / Playlists
2. Structured Learning Paths
3. Related Knowledge Button
4. Source-at-Time Citation Cards
5. Evidence Layer / Scientific Classification
6. Correction & Supersession System
7. Versioned Reel
8. Advanced Remix Studio
9. Remix Permission Matrix
10. Reel Templates Library
11. Question → Reel Answer
12. Collaborative Reel / Co-authoring
13. Expert Review Badge
14. 10-Language Reel System
15. AI Translation + Optional Dubbing
16. Searchable Transcript
17. Smart Chapters / Key Moments
18. Reel Knowledge Card
19. Micro-Quiz after Reel
20. Save to Study Collection
21. Reel → Personal Notes
22. Ask AI About This Reel
23. Creator Search Opportunity Intelligence
24. Expanded “Why am I seeing this Reel?”
25. Feed Control Center
26. Serendipity / Knowledge Diversity Slider
27. Creator Research Dashboard
28. Pre-Publish Clinical Safety Scanner
29. Accessibility Plus Mode
30. Reel Knowledge Graph

## Safety and ownership invariants

- External canonical references are provider-validated and fail closed if missing, stale or private.
- Patient-case remix defaults to deny and requires current canonical remix/rights/consent approval plus a verified File 10 derivative.
- Co-author acceptance, expert-review attribution and AI-dubbing voice consent require provider-verifiable assertions; client booleans are insufficient.
- The original Reel language remains canonical and at most nine linked language versions may be attached.
- AI execution remains File 16; File 11 exposes grounded context with `execute_ai=false`.
- Searchable transcript and generated audio tracks remain File 10 objects; File 11 stores references/projections only.
- Payment or donation status never purchases ranking priority.
- Private collections, notes and preferences are user-scoped, no-store/noindex, exportable and erasable.

## Release identity

- Plugin: `1.2.0-rc1`
- Main schema: `1.2.0`
- Top-20 schema: `1.1.0`
- Future30 schema: `1.0.0`
- File 11 contract: `7`
- Event contract: `4`
- Provider contract: `3`
- Future30 sub-contract: `1`
- Governing-plan revision: `2026-08-12`

## Verification

```bash
bash tests/run-all.sh
bash tools/build-package.sh packages/reels-foundation-11-1.2.0-rc1.zip
```

The release workflow must pass PHP 8.1/8.3/8.4 suites, JavaScript syntax, legacy corrective regressions, Future30 contracts, deterministic double build, checksum, ZIP integrity and exact source/package parity.

## Claim boundary

This repository may become a **Coded + Packaged + Automated-QA Green candidate** after the final exact-head workflow passes. Hostinger staging, real companion providers, real roles/media, browser/accessibility/load evidence, restore/rollback, Founder acceptance, live deployment and Operational status are later independent gates.
