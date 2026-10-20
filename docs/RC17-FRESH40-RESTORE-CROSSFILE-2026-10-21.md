# RC17: Forty source-review rounds, 40 File00/10 behavior scenarios and moderation-restoration safety
Date: 2026-10-21, Asia/Karachi. Candidate: `1.2.0-rc17`, File11 contract `19`, schema `1.2.0` (unchanged), all other schemas unchanged. Parent RC16 PR27 SHA `afa1f79ebd6791b9384eef64bc94b508a9f0ff05`. Exact current HEAD is GitHub PR/CI, not this earlier parent.

## Governing sources and scoped owners
- Three Central Plans Consolidated Governing Master Plan 2026: File11 owns educational vertical Reels (60–600s), feed/history and its named Future30 opportunities; File10 owns video/media/rights/consent, File00 owns published medical privileges/member/guardian claims, File05 learning progress, File06 source/knowledge, File16 educational AI context, File17 private communication, File19 notification delivery, File20 sole shell, File21 public educational case publication, File24 security assurance, File25 visuals and File26 global search/discovery.
- File11 current approved/master implementation in inherited RC16 evidence: File11 FR/NFR/FUT, safety Source/Safety Card and mandatory caption/transcript, public restricted/removed state transitions. Never treat CF-01 clinical records and CF-02 support/appeal future modules as active File11 modules; their plans explicitly require separate extraction gates.
- Release discipline: source evidence must never stand in for deployed plugin, DB migrations or Hostinger staging.

## Confirmed code defect F11-RC17-01 (high — publication policy bypass on restore)
**Symptom proved in source:** `RSV_Reels::publish` required File10 public visibility, mandatory `RSV_Top20::publication_gate` returning explicit `true`, cover/captions. `RSV_Reels::moderate` restore branch only checked File10 `status=published` and called safety gate if class existed, rejecting WP_Error only. An absent/malformed/false gate could allow an action to restore a removed/restricted Reel; a source video could become unlisted/private while Reel restored `published`.
**Root cause:** two non-equivalent publication gates in distinct state-machine paths; prior patch guarded direct publish but failed to preserve invariants on rollback of moderation.
**Repair:** create one private `RSV_Reels::publication_eligibility($reel,$video)` used from initial publish and moderation restore. Conditions: File10 public visibility for a `public` Reel; callable Source/Safety Card/transcript provider with explicit `true`; required cover and text caption where policy enabled. Fail closed with typed errors before any database update/outbox.
**Behavior evidence:** `tests/rc17-restoration-gate-behavior.php`, 17 isolated positive/negative cases including valid restore, File10 processing, foreign owner, unlisted/private source, missing/false/null safety, cover, caption, changed state, closed report and stale report version; no write on rejection. These use mock WordPress DB/provider, so are not full integration acceptance.
**Regressions:** `tests/fresh40-cycle2-sequential-audit.php` now verifies identical gate use on both publish and restore during each of its 40 frozen-source source/contract sweeps. The existing `tests/rc16-forty-owner-behavior-scenarios.php` continues 40 distinct File00/10 rights checks. The 40 central source probes, SQL failure, privacy/export, version and Future30 tests are retained.

## Cross-file forty review lenses (40 rounds)
01 scope/owner; 02 video length; 03 File10 media ownership; 04 rights and consent; 05 visibility; 06 direct first publication; 07 restore publication; 08 caption/transcript; 09 cover; 10 review state; 11 restore allowed state; 12 report decision; 13 independent File00 identity; 14 eligibility/suspension; 15 age/guardian; 16 publisher authority; 17 admin cross-owner; 18 entitlement/free tier; 19 filter non-escalation; 20 public feed; 21 HTML feed; 22 direct Reel read; 23 learning File05; 24 source File06; 25 AI File16; 26 communication File17; 27 notifications File19; 28 File20 shell; 29 content File21; 30 File24 assurance; 31 File25 accessibility; 32 File26 search; 33 rate limit; 34 idempotency; 35 privacy/export; 36 no private logs; 37 SQL outage truth; 38 deterministic ZIP/source parity; 39 migration and version boundary; 40 failed restore no-write and release gates.
The existing 40-round script is automated and checks named static source scopes, not a substitute for 40 independent manual audits or live test cases.

## Root-cause/obstruction register
1. **Source defect:** 1 newly confirmed this round; 1 fixed in branch; release-dependent QA pending exact final SHA.
2. **Previous source defects:** PR25 and PR27 fixes retained; no claim about production.
3. **Repository head and merge:** GitHub `main` README-only SHA `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` at fresh review; RC16 PR27 draft. RC17 must target PR27 branch without skipping historic stacked changes. No merge before approval.
4. **Deployed version/package checksum:** UNVERIFIED; no connected WordPress site. Do not infer from GitHub.
5. **Database version/schema and migration options/rows:** UNVERIFIED; need active WP site inspect and deployed checksums to establish parity.
6. **Hostinger staging:** real File00/10/05/06/16/17/19/20/21/24/25/26 and genuine user/browser/accessibility/privacy/migration/rollback/backup evidence pending.
7. **Production:** Founder approval, actual deploy and post-deploy live retest pending. Previous Cloudflare 530 (if still present) is only a symptom, not root cause.
**Explicit status:** Repository QA candidate only. Do not announce resolved live defect.
