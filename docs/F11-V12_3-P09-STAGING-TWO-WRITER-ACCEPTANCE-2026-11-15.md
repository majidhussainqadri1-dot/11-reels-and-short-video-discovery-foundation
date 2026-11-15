# V12.3 / File 11 — P09 concurrent supersession **staging acceptance**, Issue #38

**Applies to:** `F11-FUT-006`, `F11-NFR-003`, `F11-NFR-007`, `F30-AT-11`, AJ-17, AJ-38, AJ-40.

**Status:** BLOCKED — no real WordPress/MySQL/MariaDB staging credentials or exact deployed package evidence was available on 2026-11-15. This is an **operator runbook**, not completed test evidence or release certification.

## 0. Non-negotiable environment authorization
1. Obtain authorization to inspect the exact active **Live** File11 plugin package, file SHA256, WordPress/PHP versions, `wp_options` migration markers, table columns/indexes and database engine **read-only** with P00 collectors. Do not publish patient records, tokens, usernames or SQL excerpts from production.
2. Separately freeze **Staging** exact PHP package SHA, schema version, `SHOW INDEX` results, `SHOW TABLE STATUS` storage engine and migration markers. Check the intended File10 `VWLB` provider is installed; File10 `main` legacy `SVW` is NOT assumed compatible.
3. `wp db query` and HTTP mutation testing below are **STAGING-ONLY** and use test-only Reels A/B/C. No production writes, migrations or mass cleanups.
4. Verify the relevant `future_edges` and `reels` tables are transactional InnoDB or document the real engine. Do not infer transaction safety from a green PHP mock.
5. Snapshot staging DB and files. Confirm restore and rollback behavior before testing.
6. Do not introduce a unique index or per-Reel lock until schema evidence, no-existing-ambiguity evidence and concurrency reproductions establish the correct approach.

## 1. Read-only baseline (on authorized environment)

Run `wp eval-file tools/v123-readonly-freeze.php` in the exact plugin checkout. Save its output **privately**. Compare with the approved ZIP's extracted plugin directory through:

```bash
php tools/v123-p00-offline-parity.php PRIVATE-P00-SNAPSHOT.json EXTRACTED-PLUGIN-ROOT EXACT-PLUGIN-BASENAME
```

`CODE_MATCH_ONLY` means the two **file byte manifests** match, and **does not** certify DB, Migration, provider state, accepted defects or Live resolution.

Execute the **read-only** duplicate-lineage snapshot:

```bash
wp eval-file tools/v123-p09-lineage-readonly.php
```

A zero-duplicate snapshot **does not** prove atomicity. If its result is `existing_ambiguous_lineage_detected`, do not automate a destructive repair: preserve evidence, determine ownership and approve a reconciliation strategy.

Staging SQL for investigation, substitute the **actual verified WordPress prefix** only:

```sql
SELECT source_reel_id, COUNT(*) AS active_edges,
       COUNT(DISTINCT target_ref) AS replacement_targets
FROM wp_rsv_future_edges
WHERE feature_id='F11-FUT-006'
  AND edge_type='supersedes'
  AND status='active'
GROUP BY source_reel_id
HAVING COUNT(*) > 1;
```

**Security:** this diagnostic returns internal IDs and must never be included in public issue comments/screenshots. The P09 WP-CLI tool prints aggregated counts instead.

## 2. Required sequential cases (STAGING)
- Create three **synthetic** currently public File10-backed 60–600-second educational Reels A/B/C (no personal health content).
- With creator/publisher permission, POST A→B with key K1. Verify one committed `supersedes` edge, audit event and durable outbox record.
- Replay **same request/key K1**. The same logical result must return; no new active edge, no new misattributed event.
- POST A→B with a **different** key K2. The V12.3 candidate from PR #55 must return **409** `rsv_supersession_already_active` without INSERT. This is a source fix, not yet stage-proven.
- POST A→C with K3. Must return 409 without INSERT. Restore fixture state via approved staging procedure.
- Inject failed `future_edges` SELECT and malformed rows: return **503** without false success or mutation.
- Self-cycle A→A must fail with 409 even when pre-dispatch is bypassed in isolated service test.

## 3. Required synchronized true two-writer cases (STAGING; Issue #38)

Use **two independent MySQL/MariaDB sessions and two genuine REST requests**, with a staging-only request barrier ensuring **both requests pass pre-dispatch read checks before either transaction tries INSERT**. A fast uncoordinated stress loop is **not** sufficient to claim the concurrency invariant.

| Case | Concurrent writers | Invariant |
|---|---|---|
| C1 | A→B K1 versus A→C K2 | **At most one** active A supersession edge; loser receives governed 409/503, no ghost audit/outbox |
| C2 | A→B K1 versus A→B K2 | **At most one** active A supersession edge even for same destination |
| C3 | A→B K1 versus B→A K2 | No correction cycle; one side blocked/rolled back |
| C4 | A→B versus in-flight rollback/retry | No resurrected or second edge; retries replay correctly |
| C5 | Writer crashes or database connection fails mid-transaction | Rollback/incomplete state visible; no false success |
| C6 | Two editors and one stale version | Correct authorization and optimistic conflict semantics |
| C7 | Revoke B or source A during a pending write | Current public and consent state revalidated on read and on commit |
| C8 | Failure/replay after Outbox insert | One logical event, no divergent index/cache projection |

For every case preserve: exact File11 ZIP SHA, WordPress version, PHP version, MySQL/MariaDB version, storage engine, `SHOW INDEX` columns and prefix lengths, migration markers, two request IDs (redacted), both status codes, transaction/lock evidence, `future_edges` aggregate, audit/outbox status, cache/index observation, and restoration report.

## 4. Explicit release decision
- **Current source-only protective change** (PR #55) blocks the easy *sequential* duplicate. It **does not** serialize concurrent writers or fix cycles across concurrent sessions. Do not close Issue #38 for a synthetic unit test.
- If true C1/C2/C3 fails, **R3 release remains blocked**. Design serialization under the actual schema, e.g. verified InnoDB row locks and ordered lock acquisition, with retry, deadlock and migration/rollback tests. No guessed unique index or MySQL advisory lock on production.
- Obtain **two independent** technical reviews with corrected findings, run 55+15+11 V12.3 acceptance matrix on Staging, complete R4 founder signoff, then approved package → backup → deploy → R5 live retest and parity confirmation.
- Do not use an unmerged GitHub PR, green CI, screenshot or old ZIP as the deployed build identity.

## Mandatory final facts
`Repository HEAD`: identify exact reviewed SHA, main separately. `Deployed Version`: UNVERIFIED until P00. `DB Version`: UNVERIFIED until P00. `Migration State`: UNVERIFIED until P00. `Live Verification Status`: NOT PERFORMED until R5.

**Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔**
