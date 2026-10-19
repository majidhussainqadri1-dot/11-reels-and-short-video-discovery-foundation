# RC4 Defect Register

| ID | Severity | Requirement | Defect | Resolution |
|---|---|---|---|---|
| F11-D-401 | Blocker | CV-122 | Response/remix absent | Attributed Reel relationship + consent gate |
| F11-D-402 | Blocker | CV-123/124 | Stories/Highlights absent | 24-hour Story + consent-current Highlight schema/API/UI |
| F11-D-403 | High | CV-119/121 | Source/safety/caption not public gate | Context schema + publish/read fail-closed gate |
| F11-D-404 | Critical | F11-NFR-001 | Authorization filter could grant | Deny-only filter after canonical authority |
| F11-D-405 | High | F11-NFR-001 | Claims compatibility unspecified | Contract compatibility gate |
| F11-D-406 | High | CV-129 | Youth mode absent | Mandatory/optional youth-safe discovery and social limits |
| F11-D-407 | High | CV-128 | Well-being incomplete | Session limit, natural stop, late-night reminder |
| F11-D-408 | Medium | F11-NFR-004 | History OFFSET endpoint | Signed cursor interception |
| F11-D-409 | Medium | F11-FR-010 | Ineligible-neighbor dead end | Bounded eligibility scan |
| F11-D-410 | High | CV-127 | Local insight truth risk | Provider counts or explicit unavailable state |
| F11-D-411 | High | Privacy/consent | Highlight/remix consent drift | Snapshots and request-time recheck |
| F11-D-412 | Medium | Release integrity | Stale RC3 evidence | RC4-only manifests/reviews/workflow |
| F11-D-413 | High | Atomicity | Reel created but context failed | Compensating rollback of draft/audit/outbox/idempotency |
| F11-D-414 | Medium | UI/i18n | Client labels could be blank | Translation-ready client fallbacks |
| F11-D-415 | Medium | Design tokens | Context component lacked scoped variables | Token scope extended without global ownership |
| F11-D-416 | Critical | CV-122 / consent | Published patient response could survive later consent revocation | Revalidate canonical patient-reuse consent on every publish and public DTO read |
| F11-D-417 | High | CV-129 / pagination | Caller could request standard cursor context while mandatory youth mode filtered results | Cursor context now ORs requested mode with non-disableable current youth-safe mode |

No known repository-scope blocker remains after Round 4. External gates are not converted into code claims.
