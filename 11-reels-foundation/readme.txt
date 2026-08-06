=== Reels and Short Video Discovery ===
Contributors: sabrihomeopathy
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0-rc1
License: GPLv2 or later

Canonical File 11 educational Reels implementation for the Sabri Social Homeopathy Platform.

== Description ==

File 11 owns Reel metadata, educational taxonomy, swipe discovery, ranking guardrails, private Reel progress/history, moderation and aggregate creator insights.

File 10 remains the sole owner of media upload, scanning, processing, captions, playback, video progress and Like/Dislike/Save interactions.

This release candidate includes:
* verified 60–600-second duration enforcement from File 10;
* fail-closed File 10 dependency;
* canonical Reel lifecycle and optimistic concurrency;
* public feed and permanent Reel URLs;
* keyboard, buttons, reduced-motion and data-conscious playback controls;
* private history/export/erasure;
* report/moderation workflow;
* privacy-safe aggregate insights;
* outbox/retry/dead-letter processing;
* legacy migration dry-run and quarantine;
* deterministic packaging and source/package parity QA.

== Installation ==

1. Install and activate File 10 Video Wall and Live Broadcasting 1.0.0-rc1 or later.
2. Upload this plugin ZIP.
3. Activate on approved staging only.
4. Complete the documented staging acceptance matrix before production deployment.

== Privacy ==

Viewing history is stored only for signed-in users and is private/no-store/noindex. WordPress privacy export and erasure are supported. Moderation evidence may be retained in anonymized form according to policy.

== Changelog ==

= 1.0.0-rc1 =
* Plan-mapped canonical implementation candidate.
