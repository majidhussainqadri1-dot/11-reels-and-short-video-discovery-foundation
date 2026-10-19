# File 11 Future30 — Implementation Defect Register

This register records defects found during the Future30 implementation cycle before the two required final post-code reviews.

| ID | Finding | Correction |
|---|---|---|
| F11-D-801 | Private/dedicated Future30 features could have been reachable through a generic public endpoint. | Added explicit public/write allowlists and dedicated private endpoints. |
| F11-D-802 | Public object projections could expose internal `owner_id`, pending coauthor state, reviewer notes/non-approved reviews, historical captions or quiz correct answers. | Added feature-aware public projection/sanitization. |
| F11-D-803 | External Reel/knowledge/question/graph/source references were initially accepted as unverified strings. | Added fail-closed canonical public-reference provider validation. |
| F11-D-804 | Citation/chapter/note timestamps were not initially bounded against File 10 authoritative duration. | Added authoritative 60–600 duration/range validation. |
| F11-D-805 | Coauthor/reviewer/voice-consent assertions could initially rely on client-provided values. | Added File 00/provider identity, consent and attestation validation. |
| F11-D-806 | Remix creation did not initially require a current remix/rights/patient-consent assertion and verified File 10 derivative. | Added fail-closed remix permission + derivative checks. |
| F11-D-807 | Ten-language linkage could initially accept duplicate source/target language semantics. | Canonical source language is derived from the Reel; only max nine non-source linked languages are accepted. |
| F11-D-808 | AI dubbing and searchable transcript projections did not initially require canonical current track/transcript verification. | Added verified File 10 track and reviewed public transcript contracts; no auto-publish. |
| F11-D-809 | Feed Control Center preferences were stored but not initially applied to the native logged-in Reel feed. | Added topic/doctor/keyword exclusion in `RSV_Repository::feed`. |
| F11-D-810 | Accessibility Plus preferences were initially persisted without native UI/runtime effect. | Added body preference classes, reduced-motion/keyboard/caption CSS and transcript-mode autoplay suppression. |
| F11-D-811 | Series/path/note/safety-scan duplicate-effect paths lacked complete idempotency coverage. | Added idempotency and bounded rate limits where duplicate effects matter. |
| F11-D-812 | Provider analytics/search/graph payloads could be passed through too broadly. | Added privacy-thresholded/allowlisted projections and public-ref validation. |
| F11-D-813 | Invalid persisted Future30 private-state JSON could fall back to the whole wrapper structure. | Corrected payload-specific default handling. |

All findings above must remain regression-protected before staging acceptance.
