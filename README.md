# File 11 — Reels and Short Video Discovery

Canonical File 11 repository for the Sabri Social Homeopathy Platform.

## Current source candidate

- Version: `1.0.0-rc2`
- Governing plan: `SSH-F11-PLAN-2026-v1.0`
- Plugin folder: `11-reels-foundation`
- Text domain: `reels-short-video-discovery`
- PHP prefix: `RSV_`
- File 10 minimum candidate: `1.0.0-rc1`

File 10 owns raw media, playback, captions and core video interactions. File 11 owns the Reel entity, educational discovery, access policy, private Reel progress, ranking, report/moderation workflow and creator aggregates.

The repository includes deterministic packaging, signed keyset cursors, object/field authorization, unlisted-token/member/entitlement visibility, File 10 publication gates, report appeals, privacy export/erasure/legal holds, leased outbox processing, migration/rollback documentation and automated PHP 8.1/8.3 QA.

## Evidence boundary

`Specified`, `Coded`, `Packaged`, `Automated-QA Green`, `Staging-Accepted`, `Live-Deployed` and `Operational` are distinct statuses. Hostinger staging, real File 00/File 10/File 20/File 21/File 25 integration, browser/device/accessibility evidence, backup restoration, rollback rehearsal, Founder acceptance and live deployment remain external gates until documented.
