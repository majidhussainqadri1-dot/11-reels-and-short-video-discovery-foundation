# File 11 — Fresh 20-Round Corrective Review Register — 1.2.0-rc2

Date: 2026-08-12 (Pakistan Standard Time)

Baseline exact repository HEAD before this cycle: `26fb6594032129d40d4ab6fd470eb8b631dfffab` (`1.2.0-rc1`). Each substantive round started only after the preceding round's demonstrated repository defect had been corrected. A round is marked CLEAN only when no new repository/source/QA defect was demonstrated in that scope.

| Round | Result | Finding / correction |
|---|---|---|
| 01 | CLEAN | Revalidated the amended File 11 plan, all 30 stable `F11-FUT-001…030` IDs, governing revision `2026-08-12`, and current plan traceability. |
| 02 | CLEAN | Rechecked canonical ownership: File 11 Reel orchestration only; File 10 raw media, File 05 learning truth, File 06 knowledge/evidence, File 16 AI, File 26 global discovery remain authoritative. |
| 03 | DEFECT | **F11-D-896** — public Reel Series could be empty/stale and lacked final read-time current-Reel revalidation. **F11-D-897** — Learning Path steps were structurally loose and public reads could retain stale File 05 references. Added non-empty/bounded structured validation and current provider revalidation on write/read. |
| 04 | DEFECT | **F11-D-898** — Evidence Layer `grade` accepted arbitrary text despite the plan's bounded evidence taxonomy. Added approved Primary/Classical/Clinical/Research/Opinion/Disputed classifications and public filtering of legacy-invalid rows. |
| 05 | CLEAN | Correction/supersession cycle guards, immutable version snapshots and current replacement Reel validation rechecked. |
| 06 | DEFECT | **F11-D-899** — a Reel could be selected as its own remix source. **F11-D-900** — template type/sections were under-bounded and optional File 10 media-recipe references were not current-provider validated. Added self-remix denial, bounded template types/sections and File 10 recipe validation. |
| 07 | DEFECT | **F11-D-901** — an `approved` peer-review record could also declare reviewer conflict; legacy public projection could obscure the conflict. Conflicted approval is now rejected and legacy conflicted approvals are suppressed from public output. |
| 08 | DEFECT | **F11-D-902** — 10-language linked versions did not require a distinct linked Reel and did not prove the linked Reel's actual language matched the declared language. Added valid language-tag, distinct current public Reel and exact language-match gates on write/read. |
| 09 | CLEAN | Searchable Transcript and Smart Chapter current-review/timestamp bounds rechecked; no new repository defect demonstrated. |
| 10 | DEFECT | **F11-D-903** — multiple-choice quiz answers could be string-cast incorrectly and score arrays unsafely. **F11-D-904** — quiz attempts lacked a complete rate-limit/idempotency/audit/outbox governed result. Added type-aware scoring and atomic private-state + replay/audit/event evidence. |
| 11 | DEFECT | **F11-D-905** — single-Reel Future30 footer/shortcode tools read local tables directly and could render stale related/citation/knowledge/transcript references after external revocation. Replaced them with read-time revalidated safe rendering. |
| 12 | DEFECT | **F11-D-906** — Study Collection writes were concurrency-protected but not idempotent; omitted `reels` could unintentionally reset meaning. **F11-D-907** — private Notes could lose concurrent distinct appends because the list was assembled outside the row lock. Added idempotency, preserve-on-omission, row locking, bounded append and atomic evidence. |
| 13 | DEFECT | **F11-D-908** — grounded AI context did not revalidate all current citation/evidence references and could retain arbitrary provider-added top-level context. **F11-D-909** — reviewed File 10 transcript context was omitted from Ask-AI grounding. Added a strict context allowlist, current reference checks, reviewed transcript bridge and no-store/noindex response. |
| 14 | DEFECT | **F11-D-910** — recommendation explanation reasons from federated/global providers were insufficiently bounded/sanitized and did not fail closed on commercial/donor wording. Added scalar/length bounds, dedupe and explicit payment/donor/sponsor exclusion. |
| 15 | DEFECT | **F11-D-911** — Future30 `history_paused` preference was separate from the Top-20 canonical history switch and could be ineffective. **F11-D-912** — stored autoplay preference was not applied to the frontend runtime. Bridged history pause transactionally to canonical preferences and applied persisted autoplay through the existing guarded autoplay control. |
| 16 | CLEAN | Creator Research privacy threshold, identity-free aggregate output and minimum-cohort behavior rechecked. |
| 17 | CLEAN | Pre-publish Clinical Safety Scanner provider-outage, review-required, no-auto-publish and no-autonomous-clinical-authority behavior rechecked. |
| 18 | DEFECT | **F11-D-913** — Accessibility Plus writes could reset unspecified preferences on partial updates and lacked complete idempotency/audit/outbox evidence. Added merge-preserving partial writes with row locking and atomic replay/audit/event evidence. |
| 19 | DEFECT | **F11-D-914** — Knowledge Graph local/federated read output lacked a final current target/evidence revalidation layer. Added current canonical target and File 06 evidence checks with bounded safe projection. |
| 20 | DEFECT → FIX → RETEST | **F11-D-915** — the new Fresh-20 corrections lacked their own dedicated regression suite. **F11-D-916** — retaining `1.2.0-rc1` after substantive fixes would create mutable release identity, so the candidate advanced to `1.2.0-rc2` / File 11 contract `8`. The first rc2 exact-head run then exposed **F11-D-917**: the inherited RC5 regression fixture still hard-coded File 11 contract `7`. The fixture was corrected to contract `8`; Round 20 remains open until the corrected exact HEAD completes Green. |

## Accounting

- Requested rounds: **20/20 substantive rounds completed**; Round 20 exact-head retest is the final closure gate.
- Defect rounds: **03, 04, 06, 07, 08, 10, 11, 12, 13, 14, 15, 18, 19, 20**.
- Clean rounds: **01, 02, 05, 09, 16, 17**.
- New findings: **F11-D-896 through F11-D-917** (22 findings).
- Candidate after corrections: **1.2.0-rc2**, File 11 contract **8**; main schema `1.2.0`, Top-20 schema `1.1.0`, Future30 schema `1.0.0` unchanged.

## Claim boundary

This register closes only demonstrated repository/source/QA defects after exact-head automated verification succeeds. Hostinger staging, real companion/provider integrations, browser/device/accessibility measurements, backup/restore/rollback, Founder acceptance, live deployment and operational acceptance remain separate evidence gates.
