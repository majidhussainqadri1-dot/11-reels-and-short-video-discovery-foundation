# Changelog

## 0.2.0 — Corrective release candidate

### Functional

- Restored File 10 Like, Dislike, Save, and Report actions on Reels pages.
- Implemented private watch history, bounded progress, completion, and replay persistence.
- Preserved saved/history ordering and added bounded Reels pagination.
- Added permanent empty, loading-feedback, and media-unavailable states.

### Media integrity

- Removed trust in a submitted duration field.
- Added authoritative local duration inspection with WordPress video metadata.
- Added Vimeo duration verification through Vimeo oEmbed.
- Added YouTube duration verification through the YouTube Data API.
- Added support for YouTube watch, embed, short-link, and Shorts URLs.
- Added exact HTTPS provider validation and fail-closed behavior.
- Added server-side cover-image validation and complete failed-upload cleanup.

### Security, privacy, and moderation

- Added nonce, object-type, publication-state, duration-bound, and rate-limit controls to progress writes.
- Added WordPress personal-data export and erasure for Reels history.
- Added no-cache and noindex/noarchive/nofollow protection for private Reels pages.
- Added patient-case anonymization and consent attestations with obvious-PII screening.
- Added moderation object validation, mandatory reviewer notes, bounded audit history, and author notification.
- Prevented unrelated-page overwrite and added explicit opt-in destructive uninstall behavior.

### Lifecycle and compatibility

- Bumped plugin version to `0.2.0` and database schema to `2`.
- Added idempotent schema/version upgrades and useful history-table indexes.
- Added strict File 10 dependency-contract checks.
- Added PHP 7.4, 8.0, 8.3, and 8.4 CI; JavaScript syntax validation; static corrective contracts; and deterministic ZIP packaging.

### Accessibility and responsive behavior

- Added keyboard feed navigation, visible focus, 44-pixel controls, reduced-motion behavior, accessible status announcements, responsive forms, and mobile-safe overlays.
