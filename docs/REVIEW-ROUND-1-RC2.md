# Review Round 1 — RC1 Forensic Reconciliation and Correction

A fresh source-level review compared RC1 against the File 11 master plan, File 00 claims and File 10 ownership boundary. It found and corrected these material defects:

1. hidden/restricted history leakage and inconsistent visibility checks;
2. numeric internal identifiers exposed in public routes/actions;
3. unsigned or sort-ambiguous pagination cursor behavior;
4. client-trusted watch progress/completion and weak replay manipulation controls;
5. incomplete report, moderation and appeal workflow;
6. race-prone idempotency, rate limiting and event delivery;
7. incorrect page hierarchy, dead load-more behavior and missing touch/autoplay/well-being controls;
8. wildcard remote-player control and unverified remote progress assumptions;
9. incomplete WordPress privacy exporter/eraser and unstable viewer erasure hash;
10. incomplete legacy history migration, checkpoints, reconciliation and rollback;
11. shallow diagnostics and no coherent Safe Mode/repair path;
12. generic identity adapter instead of canonical File 00 membership/publishing assertions;
13. duplicate legacy and canonical plugin runtimes in one branch.

Corrections were implemented in RC2 and guarded by unit/static/forensic/package-parity tests.
