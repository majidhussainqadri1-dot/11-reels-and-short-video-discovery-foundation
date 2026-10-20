# File 11 — Fresh Forty-Round Contract and Cross-Module Audit (RC10)

Date: 2026-10-20, Pakistan.
Exact starting rc9 source HEAD: `270d89f8fc01f737f7f8f42f6d04596fbef34854`.
Corrective candidate: `1.2.0-rc10`, contract 16. Branch: `codex/file11-rc10-plan-audit-20261020`.

## Evidence / scope law

This register contains 40 distinct governing-plan review **scopes** and is accompanied by the executable `tests/fresh40-cycle2-sequential-audit.php` which, in 40 consecutive automated loops, re-reads all 49 plugin PHP/JS/CSS source files, checks 55 stable IDs (FR 15 + NFR 10 + FUT 30), checks earlier safety contracts plus rc10 source-revocation/PII guards, and inspects a different source marker per round. The accompanying behavioral tests are distinct. These scans do **not** constitute forty separate human semantic audits or full end-to-end WordPress integration tests; runtime and deployed states remain unverified. An ID appearing in code alone cannot prove a complete feature.

## Reviewed requirements and boundaries

| Round | Plan IDs | Focus | Evidence boundary |
|---:|---|---|---|
| 01 | F11-FR-001 | Creation with File10 asset | Source/ID inspection; real provider staging gate pending |
| 02 | F11-FR-002 | 60–600s server duration | Source/ID inspection; real provider staging gate pending |
| 03 | F11-FR-003 | Educational taxonomy | Source/ID inspection; real provider staging gate pending |
| 04 | F11-FR-004 | Medical/consent publish gates | Source/ID inspection; real provider staging gate pending |
| 05 | F11-FR-005 | Vertical focus/swipe/keyboard feed | Source/ID inspection; real provider staging gate pending |
| 06 | F11-FR-006 | Autoplay off/data saver | Source/ID inspection; real provider staging gate pending |
| 07 | F11-FR-007 | Relevance and source-quality rank | Source/ID inspection; real provider staging gate pending |
| 08 | F11-FR-008 | Owner interaction contracts | Source/ID inspection; real provider staging gate pending |
| 09 | F11-FR-009 | History and clear/reset | Source/ID inspection; real provider staging gate pending |
| 10 | F11-FR-010 | Canonical deep links | Source/ID inspection; real provider staging gate pending |
| 11 | F11-FR-011 | Moderation/report/appeal | Source/ID inspection; real provider staging gate pending |
| 12 | F11-FR-012 | Accessible captions/transcript | Source/ID inspection; real provider staging gate pending |
| 13 | F11-FR-013 | Offline/removed/processing states | Source/ID inspection; real provider staging gate pending |
| 14 | F11-FR-014 | Privacy-safe creator insights | Source/ID inspection; real provider staging gate pending |
| 15 | F11-FR-015 | Anti-spam/anti-scrape guards | Source/ID inspection; real provider staging gate pending |
| 16 | F11-NFR-001 | Object-level authorization/IDOR | Source/ID inspection; real provider staging gate pending |
| 17 | F11-NFR-002 | Privacy export/erasure/retention | Source/ID inspection; real provider staging gate pending |
| 18 | F11-NFR-003 | Retry/idempotency/outbox | Source/ID inspection; real provider staging gate pending |
| 19 | F11-NFR-004 | Bounded queries and performance | Source/ID inspection; real provider staging gate pending |
| 20 | F11-NFR-005 | RTL/keyboard/zoom/motion | Source/ID inspection; real provider staging gate pending |
| 21 | F11-NFR-006 | Audit logs/alerts/diagnostics | Source/ID inspection; real provider staging gate pending |
| 22 | F11-NFR-007 | Schema migration/rollback | Source/ID inspection; real provider staging gate pending |
| 23 | F11-NFR-008 | Safe admin repair/runbook | Source/ID inspection; real provider staging gate pending |
| 24 | F11-NFR-009 | WP/PHP compatibility | Source/ID inspection; real provider staging gate pending |
| 25 | F11-NFR-010 | US/Urdu/Arabic localization | Source/ID inspection; real provider staging gate pending |
| 26 | F11-FUT-001/002 | Series + File05 learning paths | Source/ID inspection; real provider staging gate pending |
| 27 | F11-FUT-003/004 | Related knowledge + timestamp sources | Source/ID inspection; real provider staging gate pending |
| 28 | F11-FUT-005/006 | File06 evidence + corrections | Source/ID inspection; real provider staging gate pending |
| 29 | F11-FUT-007/008 | Reel version + remix | Source/ID inspection; real provider staging gate pending |
| 30 | F11-FUT-009/010 | Remix policy + template ownership | Source/ID inspection; real provider staging gate pending |
| 31 | F11-FUT-011/012 | Question answers + coauthors | Source/ID inspection; real provider staging gate pending |
| 32 | F11-FUT-013/014 | Peer review + ten language links | Source/ID inspection; real provider staging gate pending |
| 33 | F11-FUT-015/016 | AI translation + transcript indexing | Source/ID inspection; real provider staging gate pending |
| 34 | F11-FUT-017/018 | Chapters + knowledge cards | Source/ID inspection; real provider staging gate pending |
| 35 | F11-FUT-019/020 | Quiz privacy + collections | Source/ID inspection; real provider staging gate pending |
| 36 | F11-FUT-021/022 | Notes + File16 grounded AI | Source/ID inspection; real provider staging gate pending |
| 37 | F11-FUT-023/024 | File26 search + explanation | Source/ID inspection; real provider staging gate pending |
| 38 | F11-FUT-025/026 | Feed controls + diversity | Source/ID inspection; real provider staging gate pending |
| 39 | F11-FUT-027/028 | Creator analytics + clinical scanner | Source/ID inspection; real provider staging gate pending |
| 40 | F11-FUT-029/030 | Accessibility+ + knowledge graph | Source/ID inspection; real provider staging gate pending |

## Defect ledger and corrective phase

- **RC10-D-001 (File 05)**: F11-FUT-002 learning paths had current public reference validation at write but not at public *read*. Withdrawn course/lesson records stayed present; public rows passed through undeclared private persisted metadata. **Corrected** with per-read public_ref provider revalidation on every course and step, strict 1–100 ref bounds, fail-closed omission of revoked paths, and public DTO allowlisting.
- **RC10-D-002 (File 06)**: F11-FUT-005 Evidence Layer and F11-FUT-018 Knowledge Card public projections checked source existence but returned arbitrary stored payload keys. Such historical fields can include clinical notes or personally identifying information. **Corrected** with a shared `public_sourced_payload()` allowlist invoked on both generic public projection and REST hardening paths.
- **New behavioral regression**: `tests/rc10-file05-file06-public-read.php` checks current/withdrawn path refs, current course refs, nested private fields, evidence private metadata and knowledge card PII exclusion.
- The forty-round static suite also checks all new rc10 guards, preventing their silent removal on subsequent edits.
- Report defect counts as distinct *application-code defect families*. Earlier CI cancellations due to sequential commits or old test-harness string checks are not counted as application defects.

## Non-coding blockers

The following are **not** removed by a repository patch: connect to an actual WordPress site; obtain deployed plugin version/source checksum; DB schema + relevant records; wp_options/migration statuses and logs; cross-file provider acceptance for File00/05/06/10/16/26; real medical-safety, browser, keyboard, Urdu/Arabic RTL and performance tests; verified backup restore and rollback; Founder acceptance, package deployment, live re-test, checksum parity.

WPVibe previously reported no registered WordPress site and Cloudflare HTTP 530 on connection attempts. No particular cause may be asserted from that alone. Do not deactivate safety controls on speculation.

## Mandatory final truth fields

Repository HEAD: final rc10 candidate commit, query GitHub after this register.
Deployed Version: UNVERIFIED.
DB Version: UNVERIFIED.
Migration State: UNVERIFIED.
Live Verification Status: UNVERIFIED.

Release gate remains DRAFT/UNMERGED; exact HEAD CI must pass after final commit.
