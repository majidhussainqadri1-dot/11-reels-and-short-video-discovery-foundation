# File11 V12.3 P06/P08 — Issue #62 canonical File10 private-media authorization

**Scope:** F11-NFR-001/002/003, F11-FR-004/011/013, F11-FUT-008/009, AJ-17, AJ-34/35/38/40. **Status:** Candidate source fix only; R3/R4/R5 BLOCKED.

## Truth and root-cause dossier

- GitHub File11 `main`: README-only `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (repository truth only).
- Parent source for repro: Draft PR #61 exact `558674bff3a15348bceda0abfddcbf48377a6bdc`.
- File10 **candidate** inspected at `6cc6e1c22cbac67e15421c33d6d461ff028eb0f9` — `VWLB_Security::can_view($video,'playback')` applies File10's current delivery, rights, consent, deletion and viewer claims; **not confirmed deployed**.
- The Source bug: `RSV_Security::can_view_reel()` previously returned `true` for member/private/entitled Reel on Reel+File00 claims while never checking File10's **current viewer media authorization**. Primitive Reel `rights_status` and `consent_status` fields are not substitutes for File10's rights/consent replay guard.
- **Red source reproduction before the fix**: GitHub Actions `43672874302`, branch `fix/file11-v123-private-media-owner-guard-20261115`, test `tests/v123-private-file10-current-viewer-authority.php`. **11 assertions failed**, specifically File10 revoked member/private/entitled viewer and explicit different-actor requirements. They were *candidate-source* failures, **not observed Live**.

## Candidate fix and scope

- New File11 `RSV_File10::current_viewer_authorized(video_id,user_id)`: require user ID identical to current authenticated WordPress actor; File10 contract available; File10 `VWLB_Security::can_view` callable; current video valid/published, 60–600s, rights and consent flags; File10 current per-actor `can_view($video,'playback')` returns **strict true**. Malformed return, missing provider, failed owner read and provider exception all **DENY**.
- Existing `RSV_Security::can_view_reel()` retains File00/Reel private authorization and adds the new owner policy as **AND condition** for `member`, `private`, `entitled`. This narrows permission; does not grant new rights or alter canonical File00 claims. Explicit `viewer_id != get_current_user_id()` is denied for private verification (a separate owner-supported actor-check contract would be required for delegated administrative reads).
- Public and unlisted remain governed by separate `RSV_File10::publicly_eligible` and explicit anonymous projection rules. An unlisted direct link **cannot** be advertised as a public search/feed result.
- This candidate may introduce a second File10 read for nonpublic Reel decisions and has no measured P95 budget, so performance acceptance remains R3 blocked.
- Current File10 V12.3 candidate `VWLB_VERSION`, `VWLB_CONTRACT_VERSION`, `VWLB_Security`, rights/consent guard, and storage migration must be pinned and compared to exact installed package prior to any staging signoff.

## R2 synthetic evidence

The new dedicated `tests/v123-private-file10-current-viewer-authority.php` demonstrates File10 denial overrides Reel grants for `member`, `private` and `entitled`, correct allowed actor, foreign-subject denial, and pre-denial on ineligible media. `tests/v123-file10-owner-read-and-duration.php` tests the real File11 helper with revoked/withdrawn, soft deletion, absent media, provider exception and malformed response. Existing 6 File00 entitlement cases and 40 distinct role scenarios are required to remain green.

**Exact-final-HEAD CI must be re-run after every change. A green source CI never certifies installed File10 rights or live resolution.**

## R3 real staging acceptance matrix — synthetic cases only

Use fully synthetic adult study/demo identities. No patient media and no personal health details. Frozen File00, File10 and File11 exact Git commit/package SHA and active WordPress/PHP/MySQL versions, migrations, indexes, settings and consent storage must be recorded **separately from Live**.

| Test | Reel visibility / File10 visibility | Viewer | Expected result |
|---|---|---|---|
| A01 | public / public | guest | Public view only with File10 current rights/consent |
| A02 | public / unlisted | guest | Deny; do not disclose File10 media URL |
| A03 | unlisted / unlisted | explicit viewer 0 feed/search | Deny public listing |
| A04 | unlisted / unlisted | actual File10 authorized owner direct link | Allow if all owner policies agree |
| A05 | member / public | currently eligible File00 member | Allow with File10 current permission |
| A06 | member / private | File00 member without File10 access | Deny; no media, IDOR or thumbnail |
| A07 | entitled / member | entitled File00 user and File10 member | Allow if both current claims pass |
| A08 | entitled / entitled | File00 entitled but File10 entitlement denied | Deny |
| A09 | private / private | File11 creator and File10 media owner | Allow subject to current File10 `playback` |
| A10 | private / private | admin lacking File10 media rights | Deny; admin privilege alone not sufficient |
| A11 | member/private/entitled | suspended or guardian-ineligible account | Deny even if cached File10 permission |
| A12 | member/private/entitled | explicit user ID different from current WP actor | Deny: cannot borrow operator's rights |
| A13 | member/private/entitled | viewer File10 consent withdrawn after Reel publish | Immediate deny, reconcile caches/outbox/index |
| A14 | member/private/entitled | video soft-deleted/rights revoked | Immediate deny in public and direct endpoints |
| A15 | member/private/entitled | File10 database SELECT failure | No false success or visible private payload |
| A16 | member/private/entitled | absent `VWLB_Security`/wrong version | Safe denial; truthful unavailable message |
| A17 | member/private/entitled | slow File10 rights provider/exception | Deny with no unsafe public cache |
| A18 | member/private/entitled | File10 changed permissions between lookup and playback | Playback revalidation denies stale grant |
| A19 | all | File11 privacy export/history performed by an admin for another user | No unauthorized private DTO; preserve governed export process |
| A20 | public/unlisted/member/private/entitled | actual mobile 320px/RTL/400% zoom and keyboard | Same permit/deny, no private text in DOM or cache |

Required proof for every case: frozen package SHA, DB/version/migration state, REST status, role and owner assertion trace (redacted), observed File10 `can_view` decision, cache/CDN/index behavior, audit/outbox truth, fail-safe user message, and repeat after restore. A fake PHP fixture cannot satisfy any of these.

## Two independent reviews and release

**Review 1**: File00/11/10 identity, rights, subject matching, denial, provider exception and privacy/IDOR. Implement/fix/retest any finding.

**Review 2**: Independent adversarial audit of race/revocation, cache, owner SQL outage, performance, migration/rollback and cross-repository deployment parity. Implement/fix/retest all findings. Same author rerunning tests does not qualify as the second independent reviewer.

**Blocking issues retained:** #38 real two-writer supersession, #45 true WordPress/MySQL read-truth, #46 private-state atomicity, #62 this current File10 rights gate; current issue must remain OPEN until P00 + R3 + R4 + R5.

## Mandatory state
`Repository main HEAD`: `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b`.
`Candidate PR`: #63 Draft; exact head and CI to refresh at release.
`Deployed Version`: UNVERIFIED.
`DB Version`: UNVERIFIED.
`Migration State`: UNVERIFIED.
`Live Verification Status`: NOT PERFORMED.

**Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔**
