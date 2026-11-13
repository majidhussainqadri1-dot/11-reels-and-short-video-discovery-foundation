# File 11 — P07 UI/UX source-delta and real-acceptance matrix (2026-11-13)

**Status:** SOURCE-REVIEW ONLY. This is not a browser screenshot audit, a WCAG 2.2 AA certificate, a production bug report or an authority to create a new owner table. The companion modules remain separate. Refer to Master Plan V12.1 Chapter 2/3/8, the File11 24-page completion plan, and PR #37's P00–P14 evidence register.

## Proven code-shape observations (candidate RC21)
- `RSV_Frontend::feed`, `render_reel`, `player`, `create`, `history`, `insights` exist; `assets/js/rsv.js` includes playback view-session, active media handling and accessibility-related controls. This is **R1 source presence**, not R3 browser pass.
- `RSV_Future30::routes` exposes 15 generic REST route patterns for all 30 feature IDs; `RSV_Fresh20_Hardening::render_safe_tools` renders a narrow `<details>` panel with source timestamps, related items, chapter labels, knowledge summary and transcript reference. It currently renders a source owner/reference as escaped text, not a currently validated navigable citation/source button. This is a **specific source-observable UI limitation** for `F11-FUT-003` and `F11-FUT-004`; do not count it as a verified Live defect.
- Creator research/AI context/knowledge graph/quiz/collections/feed preferences have source APIs and data protection paths; a complete end-user interaction design, empty/loading/error/denied states and discoverability for all roles remain unverified.
- `RSV_Future30_Experience_Trait` and `RSV_Fresh20_Hardening` provide some UI projections; native PHP methods must **not** be equated with a polished visual Studio, 10-language selector, full transcript reader, source-seeking controls, or analytics dashboard without browser proof.
- `File10` owns original media, caption tracks and derivative assets; `File05` education, `File06` evidence, `File16` AI execution, `File20` shell, `File25` design tokens, `File26` cross-platform search. File11 must request approved outward destinations and never invent source links.

## UI acceptance surfaces (gates R1→R3→R4)
| Feature scope | Candidate source entry | Required interactive behavior and negative evidence |
|---|---|---|
| Vertical feed + playback (15 FR) | `RSV_Frontend`; `rsv.js` | Scroll/swipe/keyboard, single active video, no hidden-tab autoplay, slow-network fallbacks, true 503 vs healthy empty, media revoked/refused |
| Source-at-time & related knowledge FUT-003/004 | `render_safe_tools` | Current File06 canonical source URL from approved provider; linked action/seek to timestamp; withdrawn source has no clickable dead/unsafe destination; source owner/ref is not sole UX |
| Series/learning FUT-001/002 | Future30 endpoints | Discoverable next lesson/series card, File05 current prerequisites, paused/resume state, keyboard/SR |
| AI and translations FUT-014/015/022 | Future30 endpoints + File16/10 | Language selection, voice consent and missing-provider state; no invented AI completed answer or clinical advice |
| Quizzes FUT-019 | Quiz endpoints + private answer key | 1–5 questions, correct/incorrect/retake state, private answers never in HTML/public DTO, accessible controls |
| Collections/notes FUT-020/021 | Private endpoint + hardening | Owner-only create/edit/save/time marker/clear; SQL 503 and rollback; no hidden success or private cache leak |
| Remix/coauthors/review FUT-008/009/012/013 | Future30 edge/policy metadata | File10 approved derivative, File00 signature, consent and reviewer verified; deny unauthorized, patient case defaults deny |
| Feed diversity and “why” FUT-024/025/026 | Future30 preferences + `rsv-top20.js` | On-device/off-device setting persistence, reset, deny muted topics, no donor preference |
| Research dashboard FUT-027 | Creator research REST | True privacy threshold >=5 sample proof, metric definitions/time window, empty/insufficient data explicitly distinguished from SQL failure |
| Clinical scan FUT-028 | Scan command + File24/16 assurance | Human review status and reason; absent provider never displays a safety approval |
| Accessibility FUT-029 / NFR | CSS + accessibility route | Captions, 200% text, 400% reflow, screen reader, keyboard only, reduced motion, RTL mixed-script no clipping |
| Graph FUT-030 | Knowledge graph endpoint | Only current canonical File06/26 edges, clickable verified destinations, no private/retracted leak |

## Visual review protocol: one approved design system
1. Check screen sketches against V12.1 Chapter 2 and File25: Sabri Green `#087A4E`, semantic error colors, one global File20 navigation, Urdu RTL logical order, max reading width, accessible focus and tap targets.
2. Test the *actual* environments and audiences: guest, member, verified doctor, Founder, moderator, suspended, denied link, no File10, no File26, no transcript, slow mobile.
3. Capture screenshot, viewport, route, CSS/JS bundle hashes, keyboard/focus path, screenreader transcript, expected/actual and reproducible issue ID. No screenshot is evidence of deployed PHP/DB parity.
4. Use the existing central `File25` tokens instead of adding a competing File11 design system; use File20 navigation instead of another bar.
5. Gate every UI acceptance on an installed *staging* package/version, the same package's SHA and read-only database source-of-truth verification. No real site-connected test has yet run.

## Current decision
Source code offers partial UI and substantial backend, but a defensible '100% UI complete' percentage cannot be assigned without the above tests. The limited plain-reference rendering should be tracked as a **P07 completion item** rather than patched by guessing File06/26 URL contracts. Do not merge a UI change until the canonical target provider's URL/visibility contract and a safe fallback are verified.

**Repository candidate:** Draft PR #37 (plus #36/#35 ancestry). **Deployed version, DB, migration, live UX:** all unverified.
