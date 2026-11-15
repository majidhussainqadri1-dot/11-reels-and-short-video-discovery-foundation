# File 11 V12.3 P06/P07 — Owner-verified canonical source links

**Date:** 2026-11-15. **Evidence tier:** Draft source and synthetic tests ONLY. This is an integration handoff, **not** a claim that any File05/06/10 provider is deployed or has approved a URL contract.

## Governing requirements

- `F11-FR-010`: canonical Reel/profile/topic/video deep links; `F11-FUT-003`: current public related educational records; `F11-FUT-004`: timestamp source citations; `F11-FUT-016`: reviewed current File10 transcript; `F30-AT-02`: revoked/unpublished foreign source denies public display; `AJ-17`: accessible source/safety controls.
- Source of truth: **File 05** owns current course/lesson status and canonical WordPress permalink; **File 06** owns encyclopedia evidence/version/publication state; **File 10** owns verified video/transcript rights, current track and revocation. File11 may consume a verified reference and render an owner URL, but cannot invent or persist foreign publication truth.
- Actual GitHub `main` for File05 and File06 is README-only while Draft owner candidates have code; File10 `main` is older SVW, newer VWLB candidate not proven installed. A candidate's function existence is **not** live parity.

## Proposed File11 consumer contract (Draft PR #59)

The current File11 consumer calls the public-reference verifier `rsv_future30_public_ref_valid` on each render. Only if it returns exactly `true`, File11 calls the new **consumer** filter:

`apply_filters('rsv_future30_verified_public_url', null, $owner, $ref, sanitize_key($purpose))`.

An **owner-authored**, approved adapter would return this exact shape, not a string:

```php
array(
  'owner'          => 'File 06',              // exact requested owner
  'ref'            => $canonical_public_ref, // exact requested ref
  'visibility'     => 'public',
  'public_current' => true,
  'verified'       => true,
  'url'            => $owner_current_public_url
)
```

**The example is a contract illustration, not an implemented File06 callback or a real URL.** Fields must be returned from the owner’s current published/rights/visibility check. Any absent, outdated, malformed, wrong owner/ref, private/revoked or cross-origin value yields a **non-clickable label**, never a guessed URL or fabricated successful source opening. URLs must use exactly the WordPress site scheme, host and port, with no userinfo or CR/LF. User-facing links remain accessible native anchors when verified.

File11 source locations: `includes/class-rsv-fresh20-hardening.php::verified_reference_href`, `::verified_reference_markup`, `::render_safe_tools`. The companion native Future30 renderer is protected from SQL read errors separately in Draft PR #60. Neither File11 method installs the File05/06/10 provider callbacks.

### Owner-by-owner handoff

| Owner | Input domain | Owner must verify | Revoke/deny test | Current real status |
|---|---|---|---|---|
| File 05 | Public course/lesson `post_id` or approved owner ref | Published post; current curriculum/role/entitlement; canonical `get_permalink` from File05 policy | Draft, removed or restricted lesson => no URL | **No approved installed provider adapter verified** |
| File 06 | Published entry, evidence source, document/edition ref | Exact canonical public entry/version and public-safe source; current citation rights and knowledge moderation | Removed evidence, superseded/unpublished entry => no URL | **No approved installed provider adapter verified** |
| File 10 | Reviewed public transcript media/track ref | Current caption/transcript track, linked Reel media ownership, rights/consent, playable current source and version | Unreviewed/withdrawn transcript or case-video => no URL | **No approved installed provider adapter verified** |

**No guessed route** from numeric post IDs, UUIDs, React slugs, source `target_ref` text or generic `/learn`, `/encyclopedia`, `/video` prefixes is allowed. Do not add a fallback external search URL. A provider can return a URL only if its own independent read check supports that exact destination.

### Source-level tests and real R3 gate

Source fixtures now cover forged owner binding, stale/private source, wrong ref, scheme downgrade, cross-origin, credentialed URL, malicious scheme, CR/LF, oversized URL and missing provider; valid provider output renders a same-origin anchor; missing provider renders plain reference text. `tests/v123-owner-verified-canonical-links.php` passed 23 isolated assertions on Draft PR #59 exact CI run #43669781590.

New operator-only `tools/v123-p07-owner-link-readiness.php` reports whether provider hooks are *registered*, without executing callbacks or reading source or user rows. Its result always says actual canonical navigation and revocation are **unverified**, even when all hooks are registered.

Required real WordPress/MySQL/Staging matrix:
1. Freeze exact installed File11/File05/File06/File10 package SHA256 and migration/schema/options, plus File20/25 browser shell.
2. For each owner, resolve at least one published, unlisted/unpublished, withdrawn and permission-denied object; verify object read, link binding, HTTP destination, expiry/revocation and no stale CDN return.
3. Ensure no File11 invented URL when owner is absent, SQL unavailable or rejects a ref; use the public plain-text label.
4. Test current link click at 320px, 390px, 768px, 1024px and 1440px, English/Urdu/Arabic, RTL/LTR, 200% and 400% zoom, keyboard and screen-reader. Confirm Back/Home and focus restoration using File20.
5. Test cache/tombstone and File26 index consumers after revoke: inaccessible source must not remain advertised.
6. Repeat two independent reviews and all V12.3 gates, obtain explicit Founder signoff; only then stage package/deploy/live R5.

### Open blocker and non-claims

- WPVibe site connection on `sabrihomeopathy.com` remains HTTP 530; WPVibe lists **no registered site**. The response does not establish host, DNS, WordPress or CDN root cause. No live WordPress, active plugin, database or migration state inspected.
- New File11 hook is a **proposed consumer interface**, not an approved inter-repository integration contract. Owner adapters must be reviewed by each owner repository maintainers. File06 URL formats are **not inferred** from the repository search.
- This source work is not P07 closure, staging acceptance, proof of linked content retrieval, deployment parity, or live issue resolution.

**Production verdict: BLOCKED. Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔**
