# Migration and Rollback

## Migration
Legacy `svw_video` Reel posts are inventory records only. A legacy post must carry `_rsv_vwlb_video_id` pointing to the canonical File 10 video. The dry run reports found/created/quarantined/skipped counts. Missing, invalid-duration, restricted or unauthorized mappings are quarantined rather than guessed.

## Cutover
1. Freeze legacy Reel writes.
2. Back up database/files/configuration and prove restore.
3. Run dry-run and approve counts/samples.
4. Run bounded migration.
5. Reconcile canonical URLs, File 10 links and interactions.
6. Enable File 11 reads, then writes.
7. Keep legacy records read-only during the rollback window.

## Rollback
Disable File 11 writes, preserve post-cutover File 11 rows, restore prior plugin/routes, and reconcile any new File 10 media created during the window. Never drop File 11 tables during routine rollback.
