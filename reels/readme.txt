=== Reels and Short Video Discovery Foundation ===
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later

Vertical American English educational Reels for the Sabri Social Homeopathy Platform.

== Corrective release 0.2.0 ==

* Enforces the final 60–600-second rule through authoritative media metadata.
* Supports local media duration inspection, Vimeo oEmbed duration, and YouTube Data API duration verification.
* Supports YouTube watch, embed, short-link, and Shorts URLs.
* Restores File 10 reactions, saves, and reports on the Reels feed.
* Records private watch progress, completion, and replay counts with rate limits and published-object validation.
* Adds WordPress personal-data export and erasure support for Reels history.
* Adds no-cache and noindex/noarchive/nofollow protection to private Reels pages.
* Requires server-side cover-image validation and cleans up failed uploads.
* Adds patient-case PII screening, separate consent/anonymization attestations, and moderated publishing.
* Adds moderation notes, audit history, author email notification, accessibility, keyboard navigation, reduced-motion behavior, bounded pagination, and safe page ownership.

== Dependency ==

Requires File 10 Video Wall and its compatible SVW_Helpers and SVW_Interactions contracts.

== YouTube duration verification ==

Define SRL_YOUTUBE_API_KEY securely in wp-config.php or supply a key through the srl_youtube_api_key filter. The plugin rejects YouTube submissions when authoritative duration verification is unavailable.

Developers may provide an authoritative duration through the srl_remote_duration_seconds filter.

== Data retention ==

Reel publications are File 10 content. Reels viewing history is private plugin-owned data and participates in WordPress personal-data export and erasure. Uninstall retains data by default; administrators may explicitly enable destructive cleanup through the srl_remove_data_on_uninstall option before uninstalling.
