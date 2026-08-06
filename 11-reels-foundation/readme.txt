=== Reels and Short Video Discovery ===
Contributors: sabrihomeopathy
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0-rc2
License: GPLv2 or later

Canonical File 11 educational Reels implementation for the Sabri Social Homeopathy Platform.

== Description ==

File 11 owns the vertical educational Reel entity, approved educational taxonomy, publication state, swipe discovery and ranking guardrails, private Reel progress/history, Reel moderation, appeals and privacy-safe creator insights.

File 10 remains the sole owner of media upload, scanning, processing, captions, playback, source rights, secure delivery, video progress and Like/Dislike/Save interactions. File 11 fails closed when the compatible File 10 contract is unavailable.

This release candidate includes:

* File 00 canonical membership and publishing assertions with current suspension, guardian and two-factor checks;
* File 10 verified 60–600-second duration, rights, consent and publication gates;
* opaque public Reel/report identifiers and tamper-evident sort-bound cursors;
* optimistic concurrency, idempotency, atomic rate limits and transactional audit/outbox evidence;
* public discovery, permanent Reel URLs, Previous/Next, touch swipe, keyboard controls and no-JavaScript pagination;
* autoplay off by default, explicit consent, reduced-motion and data-saver behavior, mindful-pause controls and no forced infinite scroll;
* server-issued view sessions and server-bounded progress/completion evidence;
* Like, Dislike and Save bridges to File 10, plus shared-contract comments/follow/download destinations;
* structured reporting, moderation, restriction/removal/restoration and reporter appeal;
* private no-store/noindex history, privacy export/erasure and minimum-threshold creator insights;
* retry/backoff/dead-letter outbox processing, diagnostics, Safe Mode and reversible repair;
* checkpointed legacy migration, quarantine, reconciliation and rollback-preserving cutover;
* deterministic packaging, source/package parity and PHP 8.1/8.3/8.4 automated QA.

== Installation ==

1. Install and activate File 10 Video Wall and Live Broadcasting 1.0.0-rc1 or later.
2. Confirm File 00 membership/publishing contracts are active for protected actions.
3. Upload this plugin ZIP to approved staging.
4. Activate and run Reels Diagnostics.
5. Complete the documented fresh-install, upgrade, migration, browser, role, privacy, performance, backup/restore and rollback acceptance matrix.
6. Deploy to production only after Founder approval.

== Privacy ==

Signed-in viewing progress is private, no-store and noindex. Viewer analytics use stable pseudonymous hashes, are thresholded before creator display, and are covered by WordPress personal-data export and erasure. Moderation evidence may be retained or redacted only under the configured lawful policy/hold. File 11 does not expose viewer identities to creators.

== Frequently Asked Questions ==

= Does this plugin upload or transcode video? =

No. File 10 is the canonical media owner. File 11 only links an eligible File 10 video to a Reel record.

= Does the feed autoplay automatically? =

No. Autoplay begins only after explicit user choice and remains disabled for reduced-motion and data-saver contexts.

= Is this production-complete after installation? =

No. This package is a repository release candidate. Hostinger staging acceptance, real cross-file integration, browser/device evidence, restore/rollback rehearsal, Founder sign-off and controlled live deployment remain separate evidence gates.

== Changelog ==

= 1.0.0-rc2 =
* Corrected the RC1 forensic defects in authorization, visibility, progress integrity, moderation, privacy, migration, routes, accessibility and deterministic release evidence.
* Added atomic and transactional integrity around sensitive writes and evidence.
* Added no-JavaScript feed continuation and safe live-region fallback.

= 1.0.0-rc1 =
* Initial plan-mapped implementation candidate; superseded by RC2 after fresh adversarial review.
