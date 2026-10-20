# Changelog

## 1.2.0-rc9 — 2026-10-19

### Corrected File 11 Future30 read/write canonical source integrity
- F11-FUT-014: a translated Reel must be a *different* currently public Reel whose canonical language matches the linked version declaration; duplicate linked targets are rejected. Existing source language and 9-linked-language ceilings remain enforced.
- F11-FUT-019: public Micro-Quiz DTOs allowlist only type, prompt, public options and learning-only flags. Arbitrary hidden/nested answer and patient metadata can no longer be returned through generic public or hardened REST projections.
- F11-FUT-019: private quiz write storage accepts only the defined question/option/answer/explanation schema, preventing persistence of extraneous client-supplied private keys.
- Added adversarial typed PHP tests for seven language-link cases and five public quiz privacy cases; all inherited tests/40 static-contract rounds retained.
- Advances runtime to 1.2.0-rc9, File 11 contract 15; DB schemas unchanged. Production and staging acceptance remain separate, unverified gates.


## 1.2.0-rc8 — 2026-10-19

### Current-plan ownership and external-provider hardening
- Restricted cross-owner publication to a fully authorized File 11 manager; an ordinary doctor/publisher may publish only their own Reel.
- Validated each File 05 learning-path step against the current canonical public provider, requiring 1–100 steps and bounding courses to 100.
- Applied an explicit allowlist to the File 16 grounded AI context and nested evidence/knowledge-card fields, preventing arbitrary provider-added private keys.
- Bounded and sanitized File 26 global recommendation reasons, ignoring malformed nested provider values.
- Added executable regression fixtures for object ownership, provider validation, context minimization and recommendation reasons.
- Kept all DB schema versions and File 10/05/06/16/26 ownership unchanged; runtime 1.2.0-rc8, File 11 contract 14.
- Staging, live code, migration state and deployment parity remain independent unverified acceptance gates.


## 1.2.0-rc7 — 2026-10-19

### Forty sequential source reviews and corrective closure
- Fixed typed Micro-Quiz scoring for single/multiple choice and true-false; removed PHP array-to-string answer ambiguity and replayable fake answers.
- Applied rate-limited, private/no-store transactional persistence with audit and outbox evidence for quiz attempts.
- Revalidated every Reel Series public reference on read, rejected empty series, and removed raw series reuse in public Study Tools.
- Removed withdrawn File 06 citations, source-free evidence/knowledge cards and expired/out-of-range timestamp rows from the File 16 grounded AI context.
- Added behavioral quiz tests, AI provider/temporal boundary tests and 40 sequential whole-source static/plan-contract inspection rounds.
- Assigned distinct immutable runtime 1.2.0-rc7 and File 11 contract 13; main/Top20/Future30 schema, event and provider versions unchanged.
- Repository QA is not staging or live acceptance.


## 1.2.0-rc6 — 2026-10-19

### Public knowledge / canonical reference integrity
- Corrected direct Knowledge Graph public endpoint to use owner-validated, source-current File 11 projections; verified optional provider evidence against File 06.
- Corrected Public Study Tools to use the same current source-safe projections for related links, citations, evidence, chapters, knowledge cards, transcripts and graph.
- Rejected source-free historical public evidence/knowledge-card projections; validated File 06 source references on write and current read.
- Added October regression suite and retained existing PHP 8.1/8.3/8.4, source/package parity and all prior suites.
- Advanced candidate runtime to immutable 1.2.0-rc6 / File 11 contract 12. Staging, deployment, database state and live parity still require separate proof.


## 1.2.0-rc5 — 2026-08-12

### Third fresh 20-round corrective closure
- Corrected `F11-FUT-014` canonical ownership: linked translated Reel edges now target `File 11`, matching the amended plan's File 11 linked-version ownership.
- Removed the invalid language-as-target fallback; every additional language link must point to a different current public File 11 Reel.
- Preserved legacy rc1–rc4 language-edge compatibility by validating legacy target refs as current public File 11 Reels at read time even if their stored target owner used the older translation-provider label.
- Retained third-cycle current-reference public projection hardening, Creator Research minimum-sample proof and identifier-minimized Future30 erasure evidence.
- Advanced immutable release identity to plugin `1.2.0-rc5`, File 11 contract `11`, with a distinct rc5 workflow/package identity. Main/Top-20/Future30 schemas, event/provider contracts and Future30 sub-contract remain unchanged.

## 1.2.0-rc4 — 2026-08-12

### Third fresh 20-round corrective hardening
- Began from exact rc3 HEAD `ec600e97a007f1c06038ff6b42465bab8f5a98a5` and performed a new sequential review → fix → next-review cycle against the amended File 11 plan.
- Revalidated generic public Future30 projections at read time against current authoritative external/public references, current File 10 duration and current File 11 linked-Reel state where required.
- Required every exposed Creator Research metric to carry provider-verifiable aggregate sample-size proof meeting the minimum privacy threshold; unproved/low-sample metrics fail closed.
- Removed raw erased WordPress user IDs from new Future30 erasure audit/outbox evidence; opaque subject references now carry downstream cache/index/preference reconciliation.
- Added a dedicated third fresh20 regression-contract suite and kept all prior corrective suites in the complete runner.
- Advanced immutable release identity to plugin `1.2.0-rc4`, File 11 contract `10`; main/Top-20/Future30 schemas, event/provider contracts and Future30 sub-contract remain unchanged.

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
