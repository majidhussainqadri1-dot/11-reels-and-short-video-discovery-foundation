# File 11 — Fresh 40-Round Corrective Review Register — 1.2.0-rc1

This register records the new sequential review → correction → next-review cycle performed against the current File 11 Future30 candidate. A finding is recorded only where a repository/source/QA defect was demonstrated. Later discovery that an earlier correction itself regressed the tree reopened that correction immediately before substantive review continued.

| Round | Result | Finding / correction |
|---|---|---|
| 01 | DEFECT | **F11-D-870** — `tests/future30-contracts.php` contained a PHP quoting/parse defect. Corrected before continuing. |
| 02 | DEFECT | **F11-D-871** stale RC5 package-builder identity and **F11-D-872** stale RC7/release-record identity. Builder and PR release identity aligned to `1.2.0-rc1`. |
| 03 | CLEAN | Re-traced F11-FR-001–015, F11-NFR-001–010, CV-119–129, CV-239–285, F11-CEN-01 and F11-FUT-001–030. |
| 04 | CLEAN | Canonical ownership rechecked: File 10 raw media, File 16 AI, File 06 knowledge and File 26 global discovery remain external owners. |
| 05 | CLEAN | File 00 identity/claims, suspension, guardian, verified-doctor and 2FA authorization paths rechecked. |
| 06 | CLEAN | File 10 authoritative 60–600 duration, ownership, rights, consent and fail-closed provider behavior rechecked. |
| 07 | CLEAN | Reel creation idempotency, rate limits, transaction, audit and outbox rechecked. |
| 08 | CLEAN | Submit/review/publish state machine and publication gate rechecked. |
| 09 | CLEAN | Moderation taxonomy, risk tiers, harm/child-safety priority and appeal state rechecked. |
| 10 | CLEAN | Restore only from restricted/removed with fresh File 10 + publication-safety validation rechecked. |
| 11 | CLEAN | Core public Reel DTO and opaque public identities rechecked. |
| 12 | CLEAN | Private history/progress/view-session cache and privacy behavior rechecked. |
| 13 | DEFECT | **F11-D-873** — provider manifest still declared governing revision `2026-08-11`; corrected to `RSV_Current_Plan::REVISION` (`2026-08-12`). |
| 14 | CLEAN | Feed/Story cursor context, ordering and bounded pagination rechecked. |
| 15 | CLEAN | Story expiry lifecycle, audit and `StoryExpired` outbox evidence rechecked. |
| 16 | DEFECT | **F11-D-874** — generic Future30 mutations could commit before idempotency/audit/outbox evidence. Mutation + replay evidence + audit + event were made one DB transaction. |
| 17 | DEFECT | **F11-D-875** — dedicated Series/Learning Path/private Note/Safety Scan writes did not share the same atomic evidence discipline. Converted to callable transactional operations with fail-closed evidence. |
| 18 | DEFECT | **F11-D-876** — Future30 generic public projections could expose internal `reel_id`/owner storage identities. Public-response stripping/revalidation was required. |
| 19 | DEFECT | **F11-D-877** — the first Round-18 storage correction itself produced a malformed exact tree/CI regression. The bad patch was removed and the known-good storage implementation restored before continuing. |
| 20 | DEFECT | **F11-D-878** — public Future30 reads relied too heavily on write-time external-reference assertions; current references/remix/reviewer attestations were not always rechecked. **F11-D-879** — candidate/unreviewed translation/transcript projections could reach generic public output. Added a final public read-time safety guard and stripped internal IDs. |
| 21 | DEFECT | **F11-D-880** — correction/supersession could create self/cyclic or multiple ambiguous active replacement chains. Added pre-mutation cycle and lineage guards. |
| 22 | DEFECT | **F11-D-881** — caller-supplied Future30 public IDs could be reassigned across feature/Reel boundaries; **F11-D-882** — historical version snapshots could be overwritten through a reused public ID. Added identity-boundary and immutable-snapshot guards. |
| 23 | DEFECT | **F11-D-883** — micro-quiz structure/correct-answer integrity was under-validated; **F11-D-884** — knowledge-card/graph evidence source refs were not all fail-closed. Added bounded quiz validation and current public source validation. |
| 24 | DEFECT | **F11-D-885** — sanitized collection names/keys could collide; **F11-D-886** — read/merge/upsert collection writes could lose concurrent updates. Added collision-resistant keys and transaction + `FOR UPDATE` versioned writes with audit/outbox evidence. |
| 25 | DEFECT | **F11-D-887** — File 16 grounded AI context could contain internal Future30 DB identities or stale evidence rows. Added AI-context sanitization/current-source checks and private no-store headers. |
| 26 | DEFECT | **F11-D-888** — search-opportunity privacy threshold was fail-open when `aggregate_count` was absent/zero. Public output now requires aggregate count ≥ 5; creator research remains identity-free. |
| 27 | CLEAN | Feed-control/diversity boundary rechecked: File 11 owns user preference/exclusion state while global ranking remains File 26-owned. |
| 28 | DEFECT | **F11-D-889** — Accessibility Plus caption-position/audio-description preferences were persisted but lacked a complete media-runtime bridge. Added caption cue positioning and a fail-closed File 10 audio-description provider bridge. |
| 29 | CLEAN | Pre-publish clinical safety scanner rechecked: provider outage remains `review-required`; no auto-publish/diagnosis/emergency substitution. |
| 30 | DEFECT | **F11-D-890** — Future30 privacy erasure deleted rows individually and could partially succeed. Replaced with bounded transactional batch erase + audit evidence. |
| 31 | CLEAN | REST permission callbacks, login/nonce model, object authorization and private no-store/noindex behavior rechecked. |
| 32 | DEFECT | **F11-D-891** — stale migration-lock takeover used a read→delete window that could delete a newly acquired fresh lock. Added a compatibility guard that blocks fresh-lock deletion in the takeover path while preserving legitimate release. |
| 33 | CLEAN | Outbox claim/retry/dead-letter/stale-processing recovery rechecked. |
| 34 | CLEAN | Diagnostics/repair and Future30 schema/table safe-mode reporting rechecked. |
| 35 | CLEAN | Redirect/URL/provider-reference/SSRF-oriented boundaries rechecked; no new repository defect proved. |
| 36 | CLEAN | Query bounds, indexes, transaction/CAS paths and no unbounded Future30 public query regression rechecked. |
| 37 | CLEAN | Translatable UI strings, RTL/reduced-motion/accessibility integration and package text-domain consistency rechecked. |
| 38 | DEFECT | **F11-D-892** — new hardening paths lacked a dedicated regression-contract suite. Added `tests/fresh40-hardening-contracts.php` to `run-all.sh`. **F11-D-893** — an unsafe test marker quoting form was found during the fixture review and corrected. |
| 39 | DEFECT | **F11-D-894** — Future30 `rest_pre_dispatch` integrity validation could run external/provider checks before the canonical permission callback. Guard validation is now itself gated by the equivalent publish/manage + ownership authorization, so it cannot become an unauthorized oracle/provider-work trigger. |
| 40 | DEFECT → FIX → CLEAN REPEAT | First exact-head pass exposed **F11-D-895**: PHP-incompatible `foreach ( ... as array(...) )` syntax in the new regression fixture. Replaced with compatible list unpacking; Round 40 was restarted on corrected state and exact-head workflow run #208 completed Green. |

## Final round accounting

- Requested rounds: **40/40 completed**.
- Defect rounds: **01, 02, 13, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 28, 30, 32, 38, 39, 40**.
- Clean rounds: **03–12 except 13, 14, 15, 27, 29, 31, 33, 34, 35, 36, 37** (20 clean rounds total).
- Recorded findings: **F11-D-870 through F11-D-895**.
- Final repository candidate remains **1.2.0-rc1** and **Draft + unmerged**.

## Claim boundary

Repository source/package/automated-QA evidence remains separate from Hostinger staging, production deployment and operational acceptance. No staging/live/operational claim is made by this register.
