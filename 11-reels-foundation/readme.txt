=== Reels and Short Video Discovery ===
Contributors: sabrihomeopathy
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0-rc2
License: GPLv2 or later

Canonical educational Reels for the Sabri Social Homeopathy Platform. File 10 owns media; File 11 owns Reel metadata, discovery, private progress, ranking and moderation.

== Description ==

Version 1.0.0-rc2 implements the approved File 11 source scope:

* authoritative File 10 video linkage and 60–600 second enforcement;
* educational taxonomy, rights, consent, cover and captions publication gates;
* accessible vertical feed, keyboard/pointer navigation, reduced-motion and data-saver behavior;
* public viewing with authenticated Like, Dislike, Save, reports and private history;
* public, unlisted-token, member and entitlement-aware visibility;
* versioned Reel and report state machines, optimistic concurrency and idempotency;
* report triage, moderation decisions, appeals and append-only evidence;
* privacy export/erasure, legal-hold support and daily pseudonymous impressions;
* reliable outbox leases, retries, dead-letter repair, dependency reconciliation and ranking jobs;
* deterministic package, tests, migration/rollback and staging acceptance documentation.

This release candidate is not a claim of Hostinger staging, live deployment or operational acceptance.

== Installation ==

1. Install and activate the compatible File 10 release candidate.
2. Upload and activate this plugin on staging.
3. Confirm File 00 identity-claim integration and File 20/25 shell/design contracts.
4. Run Diagnostics and the complete staging acceptance matrix before production.

== Changelog ==

= 1.0.0-rc2 =
* Corrected runtime, pagination, visibility, progress, moderation, queue, privacy, route and accessibility defects found in the rc1 audit.
* Expanded automated tests and release evidence.
