# File 11 RC5 — Four-Review Defect Register

| ID | Severity | Requirement | Defect | Resolution |
|---|---|---|---|---|
| F11-D-501 | Critical | Authorization/IDOR | Publication readiness evaluated before canonical permission | Authorization-first pre-dispatch gate |
| F11-D-502 | High | Privacy/cache | Private REST responses lacked explicit no-store/noindex headers | REST response headers enforced |
| F11-D-503 | Critical | Atomicity | Response create could orphan row after evidence failure | Transaction + mandatory audit/outbox |
| F11-D-504 | High | Abuse/replay | Response create lacked rate/idempotency controls | Bounded rate + client idempotency |
| F11-D-505 | High | State machine | Response could be republished from wrong state | Review-only versioned publication |
| F11-D-506 | Critical | Evidence | Story publication could succeed without audit/outbox | Transactional publication evidence |
| F11-D-507 | Critical | Evidence/atomicity | Highlight could be empty/orphaned after partial failure | Transactional create/add/evidence |
| F11-D-508 | High | Replay | Story REST silently generated idempotency key | Client key required; no-JS key embedded |
| F11-D-509 | Critical | Recovery | Reel-create compensation failures were ignored | Checked rollback + explicit failure response |
| F11-D-510 | High | Privacy export | Pagination ignored; export incomplete but marked done | Bounded paginated complete export |
| F11-D-511 | High | Privacy erasure | Removed/retained reporting inaccurate | Batched versioned accurate result |
| F11-D-512 | Critical | Privacy evidence | Erasure and audit not atomic | Transactional erasure/evidence |
| F11-D-513 | High | User choice | Partial preference update reset omitted fields | Presence-aware PATCH semantics |
| F11-D-514 | Medium | No-JS/accessibility | Story/Highlight handlers had no forms | Accessible server-rendered forms |
| F11-D-515 | Medium | Localization | JavaScript labels could be blank | All used i18n keys supplied |
| F11-D-516 | Medium | Link safety | Source/transcript links lacked rel protections | `noopener noreferrer nofollow` |
| F11-D-517 | High | Release integrity | RC4 identifiers/evidence could be reused | RC5 version/contract/workflow/tests/manifests |

**Verdict:** zero known unresolved repository-scope blocker or critical defect after Round 4. This is not a claim of staging, live or operational completion.
