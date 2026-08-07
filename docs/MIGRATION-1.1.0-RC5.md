# Migration — File 11 1.1.0-rc5

RC5 does not change the main (`1.2.0`) or Top-20 (`1.0.0`) database schemas. It advances the software version and integration contract to version 4 because mutation idempotency, transactional evidence, privacy pagination and REST cache behavior are strengthened.

Upgrade procedure: verified backup → install exact RC5 artifact on staging → activate/reactivate → verify options and routes → run diagnostics → execute replay/concurrency/privacy/role matrices → restore/rollback drill → Founder approval.

Rollback to RC4 is code-compatible at schema level, but any consumer using contract version 4 must be disabled or downgraded before rollback. Published data remains non-destructive; default uninstall never purges domain data.
