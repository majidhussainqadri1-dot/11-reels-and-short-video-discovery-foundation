# RC5 Threat-Model Delta

| Threat | Control |
|---|---|
| Unauthorized object/readiness probing | Permission granted before object-dependent readiness response |
| Duplicate/replayed Story, Highlight or Response | mandatory idempotency, bounded rates, unique constraints, optimistic versions |
| Partial canonical write without evidence | database transaction containing state + audit + outbox |
| Compensation failure hidden by validation error | every rollback operation checked; explicit 500 compensation failure |
| Cross-user private REST cache/index leakage | private no-store/no-cache/noindex headers |
| Consent revoked after response publication | canonical consent revalidation on publish and every public DTO read |
| Privacy export truncation | bounded pagination and complete owned-domain coverage |
| Privacy erasure without evidence | batched transaction and governed-retention disclosure |
| Silent preference loss | presence-aware PATCH and version conflict |
| No-JS exclusion | nonce/idempotency server-rendered forms |

Residual external risks: real WordPress/MySQL transaction behavior, provider failures, browser/device accessibility, load and operator error require staging and operational evidence.
