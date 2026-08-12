# Changelog

## 1.2.0-rc3 — 2026-08-12

### Second fresh 20-round corrective hardening
- Began from exact rc2 HEAD `2817f12586efa3b40aaa3cc2ef75871cb45097da` and performed a new sequential review → fix → next-review cycle against the amended File 11 plan.
- Added CAS-safe idempotency renewal so stale failed/expired reads cannot delete newer completed evidence.
- Canonicalized linked-language and AI target language tags, reconciled stale/revoked/mismatched linked-language edges with audit/outbox evidence, and prevented source-language duplication.
- Prevented unreviewed AI translation/dubbing metadata from becoming a public-safe projection without explicit current attestation.
- Replaced pre-commit transcript indexing notification with durable transactional `ReelTranscriptProjectionReady` evidence.
- Added strict governed quiz question schema so undeclared/private answer fields cannot leak through public projections.
- Added `ReelFuturePrivateStateErased` downstream cache/index/preference reconciliation evidence to transactional privacy erasure.
- Revalidated private Future30 access against current File 00 membership/suspension/guardian state.
- Revalidated Ask-AI citations/chapters against current authoritative File 10 duration.
- Minimized public Learning Path step DTOs to current File 05 references and governed level data.
- Added a dedicated second fresh20 regression-contract suite.
- Advanced immutable release identity to plugin `1.2.0-rc3`, File 11 contract `9`; main/Top-20/Future30 schemas remain unchanged.

## 1.2.0-rc2 — 2026-08-12

### Fresh 20-round corrective hardening
- Completed the prior 20 sequential review → fix → next-review rounds.
- Corrected `F11-D-896` through `F11-D-917`; see `docs/FRESH-20-REVIEW-REGISTER-1.2.0-rc2.md`.
- Added final read-time current-reference validation for Series, Learning Paths, related/citation/knowledge/transcript tools, AI context and Knowledge Graph.
- Added bounded evidence grades, template types/sections, recommendation reasons and strict linked-language Reel truth.
- Denied self-remix and conflicted peer-review approval; preserved current external owner attestations.
- Added type-aware quiz scoring and atomic rate/idempotency/audit/outbox evidence for quiz attempts, collections, notes, feed preferences and Accessibility Plus writes.
- Advanced immutable release identity to plugin `1.2.0-rc2`, File 11 contract `8`.

## 1.2.0-rc1 — 2026-08-12

### Future Reel Knowledge & Learning Intelligence — 30 Enhancements
- Added `F11-FUT-001` through `F11-FUT-030` as an additive Founder-approved File 11 scope.
- Added Reel Series, learning paths, related knowledge, timestamp citations, evidence projection, correction/supersession, safe version history, remix modes/policy, templates, question answers, co-authors and verified expert-review attribution.
- Added original-language + nine linked-language enforcement, governed AI translation/dubbing requests, reviewed transcript projection, chapters, knowledge cards and micro-quizzes.
- Added private study collections, timestamp notes, File 16 grounded AI context, File 26 search opportunity consumer, expanded recommendation explanations, feed controls/diversity preferences and privacy-thresholded creator research metrics.
- Added pre-publish clinical safety scanning, Accessibility Plus preferences and Reel knowledge-graph edges.

### Release identity
- Plugin `1.2.0-rc1`; main schema `1.2.0`; Top-20 schema `1.1.0`; Future30 schema `1.0.0`; File 11 contract `7`; event `4`; provider `3`; Future30 contract `1`.

## 1.1.0-rc7 — 2026-08-11
- Re-harmonized the RC6 candidate with the rewritten central and File 11 plans, including current report taxonomy/risk routing, education-only medical safety, donation-neutral ranking and current-plan requirement registration.

## 1.1.0-rc6 — 2026-08-07
- Forty corrective review/fix rounds over authorization, media contracts, moderation, privacy, reliability, accessibility, migration and release integrity.
