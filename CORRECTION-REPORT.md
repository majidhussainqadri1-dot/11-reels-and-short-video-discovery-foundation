# File 11 Corrective Report — Version 0.2.0

## Governing result

The original `0.1.0` package was accepted only as an immutable evidentiary baseline. Independent review rejected it for staging or production because material functional, security, privacy, lifecycle, integration, and accessibility defects remained.

This document maps those findings to the `0.2.0` corrective implementation.

| Audit finding | Corrective implementation | Repository evidence |
|---|---|---|
| Reactions, saves, and reports were rendered but inert | Reels JavaScript now calls File 10's `svw_action` contract with its own localized interaction nonce and handles report forms | `reels/assets/reels.js`, `SRL_Plugin::assets()` |
| Progress/history/completion/replays were not written | Intersection tracking, five-second progress writes, page-exit beacon, completion calculation, and replay preservation added | `reels/assets/reels.js`, `SRL_Plugin::progress()` |
| Duration trusted a submitted text field | Duration input removed; local metadata, Vimeo oEmbed, or YouTube Data API is authoritative; unverifiable media is rejected | `local_duration()`, `remote_duration()` |
| Source and remote URL validation was weak | Exact allowlist, HTTPS requirement, exact provider hosts, and canonical video-ID parsing added | `valid_remote_url()`, `youtube_id()`, `vimeo_id()` |
| Local upload and cover enforcement were incomplete | Size/MIME checks, required cover validation, readable metadata, and post/attachment cleanup added | `submit()`, `cleanup_failed_submission()` |
| Private history had no data-subject rights | WordPress exporter and eraser callbacks added | `privacy_exporters()`, `privacy_erasers()` |
| Private pages were indexable/cacheable | Create, Saved, and History now emit no-cache and noindex/noarchive/nofollow controls | `private_response_headers()`, `robots()` |
| Progress endpoint lacked object/state/bound checks | Published post type, Reel marker, authoritative duration, progress bounds, nonce, login, and rate limit are enforced | `progress()` |
| Saved/history ordering was lost | `post__in` ordering now preserves the database result order | `list_page()` |
| Every non-Founder was labeled Verified Doctor | Founder, verified doctor, authorized administrator, and authorized publisher labels are distinguished | `author_badge()` |
| Moderation could target non-Reels and had no evidence trail | Reel validation, mandatory notes, bounded audit log, reviewer identity/time, and author email added | `review()`, `_srl_review_log` |
| Activation/page lifecycle could overwrite unrelated pages | Managed-page ownership is checked and unrelated slugs receive a safe alternate page | `page()` |
| No schema/version migration existed | Plugin and schema versions are stored; idempotent upgrade runs on load; indexes added | `maybe_upgrade()`, `install_schema()` |
| YouTube Shorts and remote playback control were incomplete | Shorts URL parsing and YouTube/Vimeo player API commands added | `youtube_id()`, `embed()`, `providerCommand()` |
| Accessibility, mobile, and query bounds were incomplete | Keyboard navigation, focus visibility, reduced motion, mobile layouts, 44-pixel targets, live feedback, pagination, and empty states added | `reels.js`, `reels.css`, `feed()` |
| Uninstall retention was undocumented and unconditional | Retention remains the safe default; destructive cleanup requires explicit administrator opt-in | `reels/uninstall.php`, `readme.txt` |

## Security and privacy boundary

The corrective code does not claim end-to-end encryption, diagnosis, prescription, or emergency-care capability. Public Patient Case submissions are forced into moderation for ordinary publishers and are screened for obvious identifiers, but automated screening is only an additional safeguard; authorized human review remains mandatory.

## External dependency boundary

The release requires the accepted File 10 Video Wall contracts. YouTube duration verification also requires a securely configured YouTube Data API key. No YouTube Reel is accepted when authoritative duration verification is unavailable. SMTP reliability, remote-provider availability, browser behavior, WordPress runtime, and server media metadata remain staging-test dependencies.

## Acceptance still required

The corrective implementation is not production-complete until all of the following pass with recorded evidence:

1. GitHub corrective QA and exact source checksums;
2. fresh WordPress installation and upgrade from `0.1.0`;
3. exact accepted File 10 integration;
4. Founder, trusted doctor, moderated doctor, patient, student, administrator, and anonymous-user permissions;
5. local, Vimeo, and configured YouTube submissions at boundary and invalid durations;
6. failed-upload cleanup and media-size/MIME tests;
7. privacy export/erasure, cache, indexing, IDOR, CSRF, and rate-limit tests;
8. desktop/mobile, keyboard, reduced-motion, and cross-browser acceptance;
9. backup restoration, rollback, and Hostinger staging acceptance;
10. Founder approval.

No merge or production claim is permitted while any discovered failure remains unresolved.
