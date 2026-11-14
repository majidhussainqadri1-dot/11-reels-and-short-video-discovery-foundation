# File 11 V12.3 — P06 cross-repository source-contract parity (2026-11-14)

**Evidence tier: SOURCE ONLY (R1/R2 scoped); no staging or live parity inference.** Governing master plan: File11 V12.3 pages 166–171 and the cross-file ownership law. This is not a certification that any GitHub branch is deployed.

## Frozen provider and candidate refs

| Owner | GitHub main HEAD | Investigated unmerged candidate | Concrete source assessment | Operational gate |
|---|---|---|---|---|
| File 11 | `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (README-only) | PR #37 `e96e56636e0a5393e31e6fc022772e72242f08d0` + this draft P01 branch | RC21 requires `VWLB_VERSION >= 1.0.0-rc1`; Reels, Future30 and proposed File26 adapter are candidate-only | freeze actual deployed version and DB first |
| File 00 | `1fad051c86d530d558be3eb1890d3c6e67bce6e8` | Not pinned in this audit | identity authority: exact File00 assertion, guardian/consent and publisher permissions must be tested; no live claims | R3 identity/eligibility denied/withdrawn cases |
| File 05 | `e2667d69d43d655e3d86b0a358dec5f28b61dd96` (README-only) | PR #7 `a01d999abdbc9cb3651160e343555c1bec6961cf` (stacked onto PR #5) | learning paths require current public course/lesson owner refs; source candidate available, but main is not a provider | lock approved File05 branch/package and capability |
| File 06 | `c6f44e923f2a6f2ed82d967d18422624169b2770` (README-only) | PR #17 `136b067033d32962aab0c386e08a206c83f64a24` (stacked) | source-at-time, evidence card, knowledge graph and canonical source URL must come from File06/26 truth | obtain approved exact provider URL/visibility/withdrawal contract |
| File 10 | `4b8a3dda140186c01b05d998e729cb1aeeed2cf3`, legacy `SVW_VERSION 0.2.0` | PR #32 `6cc6e1c22cbac67e15421c33d6d461ff028eb0f9`, `VWLB_VERSION 1.2.25-rc1` | **Specific main source is API-incompatible with RC21** (SVW vs VWLB names). Latest draft declares VWLB APIs and at least the playback/progress/interact methods File11 expects. This is not proof of installed parity or full compatibility. | pin one authorized installed VWLB build, real duration/rights/caption/revocation tests |
| File 12 | `2b369ef2d609247896e1ab32118f1dc498407f51` | Not inspected for an approved candidate | PDF source routes and rights must be from File12 | R3 current public refs, revoke |
| File 16 | `0cea92273f9e716ba45e3606b3afaa9751c2d88d` | Not inspected for an approved candidate | File11 only provides context with `execute_ai=false`; never become diagnostic authority | signed File16 invocation/degraded tests |
| File 20 | `8a4dbcaf4fef8e926b9b834ecfde16c21a0f00ca` | Not inspected for an approved candidate | global shell and Home/Back routes remain File20-owned | real single-shell browser verification |
| File 24 | `a5b8d49968a7a5a7d6f3f4655bea541bf38a9acb` | Not inspected for an approved candidate | assurance, privacy and clinical moderation review authority | R3 clinical unsafe/appeal/scanner gate |
| File 25 | `347a4ff4d4c233c5ea6cd82c7786ee5398ea9d1e` | Not inspected for an approved candidate | documented design system `1.4.0`, visual acceptance `1.2.0`; `sabri-visual-design-system` and `--sabri-visual-*` tokens; shell stays File20 | real 320px/400%/RTL/keyboard/visual evidence |
| File 26 | `1b064c6ea9f81195611ed001d5457e8ced65460a` | no new candidate required to inspect documented contract | main `Connectors` requires slug/owner_file/contract_version/entity_types/privacy_classes/visibility_fields/deletion_semantics; `Owner_Contracts::collect` transforms File11's **`sabri_file26_owner_connector_adapters`** candidate into the required `sabri_file26_connector_manifests` shape, adding `owner_file='File 11'`. The missing direct owner_file in File11 adapter is **NOT a defect**. | adapter still `status=proposed`; requires schema/events/tombstones and explicit approval before active |

**Source compatibility inference:** File10 `main` and File11 Draft PR #37 are not compatible through the declared `RSV_File10::contract_compatible()` check. File10 PR #32 has the version/classes expected by that check, but neither PR is merged or proven installed. Do not infer that the Live site uses either codebase or that the File10 draft is otherwise acceptance-ready.

## Precise File26 handshake (confirmed at named source refs)

1. File11 `class-rsv-file26-connector.php` emits `$adapters['file11']` with `slug=file11-reels`, `entity_types=['reel']`, `privacy_classes=['public']`, `deletion_semantics=tombstone`, callable `list_batch/can_view/health`, and `status=proposed`.
2. File26 `includes/class-file26-owner-contracts.php`, lines 35–54, reads hook `sabri_file26_owner_connector_adapters`, verifies mandatory callbacks and types, **injects `owner_file='File 11'`** and forwards a normalized manifest.
3. File26 `includes/class-file26-plugin.php` registers that bridge on `sabri_file26_connector_manifests` before `Connectors::boot()`.
4. File26 `includes/class-file26-connectors.php`, lines 28–53, validates the resulting manifest; lines 78–89 enforce the approved/shadow/active index lifecycle. File11 is **proposed only**; do not activate from source existence.
5. File26 connector contract v1.0 requires same-origin canonical URLs, monotonic object versions, owner revalidation on click and replay-safe tombstones. File11 must independently prove all of them at R3.

## Critical incomplete acceptance

- Deployed plugin, exact package SHA256, DB schema versions and `wp_options` migration state: **UNVERIFIED**; WPVibe HTTP 530 (`cf_origin_error`), Jetpack `site_disconnected`.
- P06 provider branch locks, migration/rollback parity, File00 rights, File10 rendition/transcript/consent, File06/12/26 URLs, File16 execution, File25 real components: **R3 BLOCKED**.
- Issue #38 concurrent correction is still an **unconfirmed source risk**, not a proven live bug; requires isolated real MySQL two-writer acceptance.
- P07 owner/ref text is not a verified clickable citation: can only be fixed after canonical source URL contract and permission state are proven.
- File05/06 `main` is README-only although unmerged candidate branches contain implementation. Do not silently substitute a draft for an installed or approved provider.

## Next permitted actions

1. Run the read-only `tools/v123-readonly-freeze.php` using authorized WP-CLI, save redacted evidence; separately freeze staging and compare to exact chosen candidate manifests.
2. Decide and freeze provider candidate versions and approval status. Do not merge an arbitrary stack automatically.
3. Stage with disposable educational fixtures and zero patient data. Test File11/File10 actual source duration 59/60/600/601, revocation, owner permissions, deleted caches and no false success.
4. Stage File26 proposed adapter only after owner/event/index schema and tombstone integration proof; no active promotion without required evidence.
5. Obtain File06's published canonical source URL resolver and File25 UI components; implement only a provider-certified destination, not guessed links.
6. Repeat strict 55+15 ID R3 matrix, two reviews and founder R4 approval. Release only with R5 deployed parity/live retest.

**Status:** P06 source inventory partially completed; all deployment/integration gates remain blocked. No new code defect was claimed for the bridged File26 manifest.
