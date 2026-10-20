# Changelog

## 1.2.0-rc18 — 2026-10-21

- Corrected personal-data exporter to surface SQL failures from the private Reel history, reports, and authored Reel collections as WordPress privacy exporter errors; no incomplete or silently successful export.
- Corrected erasure transaction to fail closed and roll back when the retained-owned-Reels count cannot be read or validated.
- Added 12 isolated export cases and 5 erasure/rollback cases, plus new fail-closed markers in all 40 existing source review rounds.
- Release is a source candidate. Hostinger live plugin and schema/migration parity remain unverified.


## 1.2.0-rc17 — 2026-10-21

- Eliminated a moderation-restore publication bypass: restoring a removed/restricted Reel now runs the same mandatory File10 public visibility, Source/Safety Card, caption/transcript and cover conditions required for an initial publish.
- Introduced one shared native `publication_eligibility` validation function for initial publish and restore so future changes cannot silently diverge.
- Added 17 isolated positive/negative restore scenarios and registered them in the complete test suite; enhanced all 40 repeated frozen-source verification rounds.
- No schema changes; source QA and CI package are not deployed-state evidence.


## 1.2.0-rc16 — 2026-10-21

- Integrated RC15 corrective PR #25 and separate File00 entitlement safety PR #26 without discarding either branch; prior isolated CI results did not establish combined build parity.
- Current File00 `publishing` assertions now remain in the same eligibility/security snapshot. A second provider call no longer re-grants rights removed by security containment.
- File00 `can_open_composer` and `can_direct_publish` must be true for their respective Reel commands, regardless of WordPress administrator native capabilities.
- Removed WordPress manage_options bypass of private/member/entitled views and cross-owner publication; explicit File11 editorial delegation is required.
- Preserved canonical single-free-tier File00 `base_services.education` Reel entitlement, and rejected any policy filter attempt to grant missing eligibility.
- Added 40 distinct behavioral cases on publishing, subject/guardian/MFA, owner, entitlements and File10 public/unlisted media; inherited forty frozen-source sweeps retained.
- No database schema change; release remains a source candidate, not production accepted or deployed.


## 1.2.0-rc15 — 2026-10-20

- Corrected canonical File00 membership subject, eligibility, guardian and age assertions including current File00 safety modifiers; previously active users could be misclassified when eligible=false or age unknown.
- Removed administrator role substitution for canonical Founder/verified doctor Reel submission and publication, removed stale mandatory MFA re-enforcement when File00 policy retired MFA, and retained policy-required step-up when current File00 requires it.
- Restricted File10 media linkage to the actual media owner even for a WordPress administrator; public Reels cannot publish unlisted File10 media and read-time visibility changes remove public eligibility.
- Made File 11's reviewed source/safety/caption publication gate mandatory in the native publish command; absent or malformed provider fails closed rather than depending solely on filter registration.
- Added runtime-shaped File00/10 adversarial regression and stronger 40-source review guards. Source CI success is not staging/live proof.
- Corrected Release Manifest/status/history version metadata and clarified that the File11 plan specifies canonical ZIP root `reels-foundation-11`.


## 1.2.0-rc14 — 2026-10-20

- Fixed five additional native SQL-read families that previously coerced failed database reads into valid empty results: private watch history, Reel neighbors, public author timeline, public search and creator insights.
- Added safe REST and public/private UI error handling; read-only cross-file providers emit a degradation signal and preserve external contract types.
- New RC14 behavioral regression distinguishes null SQL failure, explicit database error, healthy empty reads and a second-neighbor-query failure. All 40 source/contract rounds and PHP 8.1/8.3/8.4 suites are mandatory at exact HEAD.
- Corrected stale default build-package naming (rc3) and assigned RC14 a distinct runtime, workflow, artifact, docs and manifest identity. Schema 1.2.0, File 11 contract 19 and canonical boundaries unchanged.
- Deployed artifact, DB/migration state, WordPress staging, real providers, origin Cloudflare 530 diagnosis, backup/restore, Founder approval and live re-test remain unverified.

## 1.2.0-rc13 — 2026-10-20

- Corrected F11-FR-005 / CEN-SEARCH-001: an unsuccessful Reel Feed database read must return a typed HTTP 503 rather than a fabricated successful empty feed. The REST and UI error routes already propagate that error.
- Brought SQL outage tests into the integrated rc12 privacy/ownership suite and all 40 sequential source checks.
- Corrected stale current release metadata while preserving chronological rc1–rc12 entries. Plugin 1.2.0-rc13, File11 contract 19; no schema change.
- Production deployed version and live verification remain unverified.


## 1.2.0-rc12 — 2026-10-20

- Unified PR #18 Study Collection revocation-safe reads, Unicode-safe keys, transactional audit/outbox and private-safe version history with PR #19 File10 Template/Media Recipe, File26 reason policy and HTTPS transcript protections.
- Preserved proposed native File26 search connector and its k-anonymity/source checks; staging is required before activation.
- Corrected accidentally overwritten WordPress readme release identities, restoring historical rc1–rc11 from verified source.
- Forty automated code/contract sweeps, PHP 8.1/8.3/8.4 and deterministic package acceptance are required at the exact final rc12 HEAD. No live-deployed status claimed.


## 1.2.0-rc11 — 2026-10-20

### Privacy-safe historical Reels and private study mutations
- F11-FUT-020: revalidate every saved Study Collection Reel on each private read; suppress withdrawn, deleted, non-public, malformed and duplicate refs and minimize returned payload.
- F11-FUT-007: public Version History now includes only version number and verified SHA256 metadata; old caption/title, submitted labels and extra PII cannot appear via generic or filtered REST projections.
- F11-FUT-020: atomic study collection update now commits private state with mandatory Audit and Outbox event evidence, and returns the currently safe DTO.
- F11-FUT-020: Unicode/Urdu-only collection names receive stable nonempty deterministic keys instead of colliding in an empty sanitized key.
- Added adversarial PHP behavior tests for revoked read paths, historical snapshots, Urdu identifiers, audit/outbox success and simulated rollback failure.
- Aligned PHP unit's WordPress sanitize_key with lowercase-before-filter behavior and fixed interpolated duration source markers in the 40-scope test.
- Immutable runtime 1.2.0-rc11 / File 11 contract 17, no schema-version changes. Repository CI does not establish live/staging parity.


## 1.2.0-rc10 — 2026-10-20

### Provider-current public read integrity
- Fixed F11-FUT-002 public Learning Paths: verify every File 05 lesson/course source remains public/current on each read; suppress withdrawn paths and remove undeclared private payload and owner fields.
- Fixed F11-FUT-023 File 26 aggregate search opportunities: never expose missing, zero, negative or below-five audience aggregates; reject stale declared File 26 sources and unknown personal fields; allow only governed public aggregate fields.
- Added live-PHP behavior tests for revoked File 05 paths and File 26 k>=5 limits, as well as forty distinct governing-plan source probes.
- Existing 40 sequential full-source static/55-ID scans remain, alongside all prior behavioral tests, syntax and deterministic release/package parity gates.
- Reconciled newly added rc9 File26 proposed connector without activating it and removed raw WordPress author IDs from the public search connector; retained upstream regression.
- New immutable candidate: plugin 1.2.0-rc10 / contract 16; database schema versions unchanged.
- Package-root disagreement with early File 11 plan is a pending deployment-parity decision: do not silently rename an existing WordPress plugin.
- Not staged or deployed; DB and runtime migration state are unverified.


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
