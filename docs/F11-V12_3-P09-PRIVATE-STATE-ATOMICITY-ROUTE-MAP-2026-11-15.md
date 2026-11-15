# File 11 V12.3 — Issue #46: exact candidate-source private-state write routing and R3 atomicity gate

**Evidence tier:** read-only source review at draft PR #55 SHA `06184de7904712e7fdd946418cfc5addb255fa60`. **Neither deployed code nor installed hooks are verified**. This document does not request or implement a live patch.

**Governing:** `F11-NFR-002` private data lifecycle, `F11-NFR-003` error truth, `F11-NFR-007` atomic migration/rollback, `F11-FUT-019/020/021/025/029`, `F30-AT` privacy tests; incident Issue #46 OPEN.

## Candidate-source facts (not installed-runtime facts)

- `trait-rsv-future30-storage.php:state_put()` calls `INSERT ... ON DUPLICATE KEY UPDATE`, then `state_get()`. The readback returns typed SQL 503 after the recent P02 patch, but an `INSERT` outside a transaction may have already committed. An HTTP 503 alone is not evidence of rollback.
- `class-rsv-future30.php:routes()` registers native REST POST `/quiz/attempt`, POST `/future30/collections`, POST `/notes`, PUT/PATCH `/future30/feed-preferences`, PUT/PATCH `/future30/accessibility`.
- `class-rsv-fresh20-hardening.php:register()` adds `rest_request_before_callbacks` at priority **5** and its `before_callbacks()` intercepts those mutation routes. Its replacements use `RSV_DB::transaction()`, version-aware `SELECT ... FOR UPDATE`, `audit`, `outbox` and idempotency.
- `class-rsv-future30-private-state-integrity.php` also intercepts collection POST at priority **20**, but the earlier Fresh20 handler ordinarily supplies a response; which callback is actually active must be captured from the installed WordPress runtime.
- Native `RSV_Future30::quiz_attempt()` uses `RSV_DB::transaction()`; native `collection_write()` uses `RSV_DB::transaction()`; native `note_write()` uses `idempotent_finish_result()`, which wraps `RSV_DB::transaction()`; native `feed_preferences_write()` and `accessibility_write()` call `state_put()` directly **without their own transaction**.
- On the candidate source, the **registered REST route** should ordinarily run the Fresh20 before-callback replacement, so **source reading alone does not prove** direct methods are exercised by actual normal REST requests. Conversely it does not prove that all extension/internal callers use REST or that a stale deployed build installed the hook.

## Source-level route coverage matrix

| Mutation | Native call | Candidate Fresh20 REST override | Native transaction | Risk if native path reached |
|---|---|---|---|---|
| Quiz `POST /reels/{id}/quiz/attempt` | `quiz_attempt` | Yes | Yes | transaction protects write/readback if DB rollback succeeds |
| Collections `POST /future30/collections` | `collection_write` | Yes; separate priority-20 guard also | Yes | transaction protects write/readback if DB rollback succeeds |
| Notes `POST /reels/{id}/notes` | `note_write` | Yes | Yes through idempotent helper | transaction protects write/readback if DB rollback succeeds |
| Feed controls `PUT/PATCH/POST /future30/feed-preferences` | `feed_preferences_write` | Yes | **No, native path** | post-UPsert SELECT 503 can follow a committed write; audited evidence may be absent |
| Accessibility `PUT/PATCH/POST /future30/accessibility` | `accessibility_write` | Yes | **No, native path** | post-UPsert SELECT 503 can follow a committed write; audited evidence may be absent |

Note REST route declaration is `WP_REST_Server::EDITABLE`; check actual method handling and hook ordering in exact runtime, not assumptions about methods.

## Mandatory staging reproduction (no real patients or live writes)

1. Operator freezes **Live source and DB** read-only (P00), the exact staging package and its SHA-256, File00 identity, PHP/WordPress/DB versions, migration options, relevant table engines/indexes, and `$wp_filter['rest_request_before_callbacks']` effective callbacks with priorities/active plugin state.
2. With disposable fake users and synthetic Reel, capture REST route collection and method maps. Confirm permission checks are applied. Confirm Fresh20 priority-5 intercept executes **before** native callback for every method and all appropriate authenticated users. Capture only route names/priorities/class names; no personal data.
3. Induce failpoint **only on the `state_get` SELECT following a successful UPSERT**, with full redacted query trace. Test native methods directly only in isolated staging fixtures and test effective REST entrypoints separately.
4. For each case compare BEFORE/AFTER: private state payload hash (never print payload), monotonically changing version, transaction state, audit/outbox event count, idempotency retry record, HTTP response and non-cache headers. If error occurs, assert rolled-back row and evidence consistency; do not accept a mock transaction log as a real DB rollback.
5. Repeat a request with its original key, with a different key, across network reset/connection loss, after a revoked File00 entitlement, and a known empty first-save state.
6. Verify no phantom success in WordPress privacy exporter, erasure, restored backups or File26 index after errors.
7. Only after an actual exposed path is reproduced should an architectural correction be designed. A proposed solution must preserve File00/10/24/26 ownership, use the real DB's InnoDB semantics, and be tested against 2 independent transactions with retry/rollback/commit-failure handling.

## Release exit status

| Gate | State |
|---|---|
| Static native-route ownership map | Observed on candidate source |
| Installed `rest_request_before_callbacks` evidence | BLOCKED |
| Actual WordPress REST + real MySQL post-UPSERT SQL failure | BLOCKED |
| Transaction rollback, audit/outbox reconciliation | BLOCKED |
| Production exact package/deployed parity | UNVERIFIED |
| Independent second review, founder acceptance | BLOCKED |
| Live re-test | NOT PERFORMED |

**No code patch is authorized yet for Issue #46** because its precise effective runtime/write path and real transaction behavior have not been demonstrated. Keep OPEN; do not call it Resolved based on green PHP mock tests.

**Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔**
