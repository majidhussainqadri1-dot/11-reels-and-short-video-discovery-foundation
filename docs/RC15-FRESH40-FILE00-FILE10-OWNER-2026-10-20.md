# File 11 RC15 — 40-round governing-plan/source + cross-owner security review
**Date:** 2026-10-20 (Pakistan); **candidate:** 1.2.0-rc15; **schema:** File11 1.2.0, Top20 1.1.0, Future30 1.0.0.
**Initial main HEAD:** `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` (README only).
**Audited working source parent:** RC14 `a83a878442cd914e278975adec30a6a0649b74f9` (PR #23; does **not** prove main/live deployment).
**Current corrective branch:** `audit/file11-40-crossfile-owner-security-20261020`.
**Evidence hierarchy:** File11 v1.1 approved plan and August 12 Future30 amendment; central plan harmonization in appended governing supplement; File00 and File10 current source contracts; File05/06/16/19/20/21/24/25/26 explicit canonical ownership boundaries. This is source evidence, not proof of staging.

## Confirmed new defects (six behavioral/code, two document/evidence)
| ID | Severity | Root cause | Correction | Direct evidence |
|---|---|---|---|---|
| F11-RC15-01 | High | File11 mapped approved but **ineligible** File00 membership as active, ignoring `eligible`, subject binding, and owner security modifiers. | Consume `smc_membership_assertions` first, current `smc_assertions_v1` security modifications, matching subject/version and both approval/eligibility. Fail closed on provider exception. | `class-rsv-security.php`, `tests/rc15-file00-file10-owner-boundary.php` |
| F11-RC15-02 | High | File00 age/guardian truth now lives in `age_context` yet File11 read legacy top-level fields; missing known-age and active guardian restrictions. | Use File00 age-context known/minor and verified guardian before protected actions; unknown age denies privileged action. | Same behavioral regression |
| F11-RC15-03 | High/availability | Blanket session_two_factor requirement contradicted File00's explicit 2026-08-10 retirement of mandatory MFA, denying legitimate verified doctors. | Require MFA *only when current canonical File00 policy says it is required*; no admin exception. | Positive and negative MFA behavior tests |
| F11-RC15-04 | High/authorization | `manage_options` was treated as verified Founder/doctor in submission/publication identity. WordPress admin is not File00 canonical identity. | Require File00 current Founder or professionally verified doctor for create/publish; admin can only perform separately allowed operational actions. | Positive Founder and negative admin tests |
| F11-RC15-05 | High/ownership | File10 `validate_for_reel` let arbitrary WP admin link another creator's media. | Enforce actual File10 media owner equality, fail closed absent a verified delegation contract. | Media ownership negative/positive tests |
| F11-RC15-06 | High/privacy | A public Reel could link an unlisted File10 asset and leak its existence through public feed and publication events. | Deny public Reel publication if source visibility is not public; recheck on each public read; unlisted Reel direct links remain distinct. | Read-time and publication behavioral tests |
| F11-RC15-07 | Documentation | `README.md` reported contract 16, while code/release manifest declare contract 19. | Current README synchronized to contract 19; historical versions preserved. | `tests/rc14-current-metadata-parity.php` (historical filename retained, verifies rc15). |
| F11-RC15-08 | Documentation | STATUS claimed package root `reels-foundation-11` contradicts plan although the approved File11 plan explicitly names that exact installable root. | Removed false blocker; clearly distinguished source directory `11-reels-foundation` from ZIP root `reels-foundation-11`. | Written plan p.5 / status / deterministic builder. |

## Fresh 40 review checks — explicit scope and evidence
The existing `tests/fresh40-cycle2-sequential-audit.php` performs **40 sequential, frozen-source source/contract sweeps**, each rereading all plugin PHP/JS/CSS sources, verifying 55 FR/NFR/FUT IDs plus shared negative guards and one named focus. This is **not** 40 independent professional/manual line-by-line code reviews, nor 40 Live/Staging runs.

| Rounds | Design/check dimensions | Mandatory evidence |
|---|---|---|
| 01–05 | Bootstrap, full FR/NFR, File10 owner, duration, publish restriction | 40-sweep source test; File00/10 behavior suite |
| 06–10 | File00 latest MFA/age/guardian, rights/consent, state machine, integrations | 40-sweep + File00/10 runtime-shaped tests |
| 11–15 | REST permissions, idempotency, SQL, optimistic locking, outbox | source/contract and inherited tests |
| 16–20 | Schema migration/locks, privacy export/erase, anonymous insights | source + inherited privacy regressions |
| 21–25 | Revocation-safe Series, File05, File06, File16 context, File26 owner | source + inherited cross-file behavior |
| 26–30 | Read-time public projections, private quiz grading, translations, creator research, bounded erasure | source + Future30 behavior |
| 31–35 | Autoplay off, reduced motion, keyboard, wellbeing, captions | code tests (real-browser staging still missing) |
| 36–40 | RTL CSS, patient-case policy, safe mode, moderation, complete CI wiring | code tests; real-user acceptance still missing |

Extra tests retained: RC14 SQL failure truth for five read families, RC13 feed outage, source-safe study collections and linked-language refs, deterministic dual build, ZIP integrity/extraction, PHP lint, JS checks, source/package parity. Re-run after every source correction at exact final commit.

## Unresolved external gates — do not call live fixed
- WPVibe site not connected; deployed code, active version, DB tables/schema and migration status **unverified**.
- Required File10 version/branch/deployed compatibility and authenticated File00/05/06/16/19/20/21/24/25/26 real provider claims/visibility semantics are untested on shared WordPress staging.
- Proposed File26 connector is not a production-adopted consumer; companion owner and staging approval mandatory.
- Hostinger staging backup/restore, supported upgrades, source checksum parity, rate/performance, cross-browser accessibility, privacy/deletion propagation, approval and rollback rehearsal are pending.
- GitHub main is README-only and several candidate PRs target development branches; a source PR must not be described as merged into main or deployed.
- Previously observed Cloudflare HTTP 530 does not by itself establish origin root cause; do not weaken security solely on that observation.

**Final incident truth fields:** Repository main HEAD: `c2df8e72bad76dc1a7fb83cbe039541f7aa1fd8b` at audit start; exact branch head: see PR/CI; Deployed Version: UNVERIFIED; DB Version: UNVERIFIED; Migration State: UNVERIFIED; Live Verification Status: NOT PERFORMED.
