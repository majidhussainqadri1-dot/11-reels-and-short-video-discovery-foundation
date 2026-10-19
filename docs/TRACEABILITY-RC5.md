# RC5 Corrective Traceability

| Requirement family | Code/evidence |
|---|---|
| Authorization and privacy cache | `trait-rsv-top20-experience.php`; F11-D-501/502; RC5 hardening tests |
| Story/Highlight atomicity | `trait-rsv-top20-stories.php`; F11-D-506–508 |
| Response consent/idempotency/evidence | `trait-rsv-top20-responses.php`; F11-D-503–505 |
| Preference integrity | `trait-rsv-top20-context.php`; F11-D-513 |
| Privacy export/erase | `trait-rsv-top20-privacy-integration.php`; F11-D-510–512 |
| No-JS/i18n/link safety | `trait-rsv-top20-experience.php`; F11-D-514–516 |
| Release integrity | bootstrap/readme/workflow/tests/manifests; F11-D-517 |

All corrective source markers are enforced by `tests/rc5-hardening-contracts.php`, the static and forensic suites, and exact source/package parity.
