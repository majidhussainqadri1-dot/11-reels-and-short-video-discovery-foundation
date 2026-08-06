# Migration, Upgrade and Rollback

## Sources

RC2 recognizes approved legacy Reel posts and the legacy `srl_history` table. A Reel is migrated only when it has an explicit File 10 mapping and File 10 currently validates owner, duration, status, rights and consent. Invalid sources are quarantined rather than guessed.

## Controls

- non-overlapping migration lock with expiry;
- bounded batch size and resumable Reel/history checkpoints;
- dry-run before mutation;
- source snapshot and migration status markers;
- unique File 10 video ownership and duplicate suppression;
- audit evidence for migrated records;
- request/cron reconciliation after provider changes;
- rollback disables the new cutover while preserving newly created RC2 data.

## Staging sequence

1. clone production-like database and files to approved staging;
2. verify backup restoration before migration;
3. run dry-run and record counts/quarantines/errors;
4. run bounded batches until checkpoints stabilize;
5. reconcile File 10 states and compare source/target counts;
6. exercise rollback and confirm legacy reading resumes without deleting new data;
7. re-run migration idempotently and complete role/privacy/browser acceptance;
8. obtain Founder approval before production cutover.
