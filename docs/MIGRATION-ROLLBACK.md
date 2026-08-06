# Migration and Rollback

1. Inventory legacy Reels, media IDs, durations, statuses, history, interactions and canonical URLs.
2. Run `RSV_Migration::legacy_dry_run()` and retain counts/checksums.
3. Resolve every File 10 mapping; quarantine missing, restricted, under-60 or over-600 media.
4. Back up database, media references, plugin configuration and accepted package checksum.
5. Run bounded idempotent migration under the schema lock; never dual-write without an approved time-bounded design.
6. Reconcile counts, sample rows, routes, privacy states, File 10 eligibility and downstream events.
7. Cut over only after staging acceptance. Keep the old source write-disabled.
8. Rollback restores schema/routes/read-write ownership without deleting post-cutover records; export and reapply new records if a code rollback is required.
