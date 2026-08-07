# Hostinger Staging Acceptance — File 11 1.1.0-rc6

This checklist is an external gate and must be completed against the exact final RC6 artifact. Repository QA does not substitute for these tests.

- [ ] Exact artifact digest/checksum matches the successful final RC6 workflow and source head.
- [ ] Fresh activation and supported RC5→RC6 upgrade complete without fatal/warning, false version promotion or foreign-data mutation.
- [ ] Managed Reels/Create page creation failure is simulated and activation fails/deactivates safely.
- [ ] File 00 Founder, Administrator, verified doctor, pending/suspended user, adult viewer and minor/guardian-required claims tested; missing guardian verification fails closed.
- [ ] File 10 real companion contract and required methods resolve; incompatible/missing methods fail closed.
- [ ] File 10 media at 59/60/600/601 seconds, rights, captions/transcript, patient consent and secure delivery tested.
- [ ] Publish from draft/restricted states is denied; review→published is allowed only after all readiness/safety gates.
- [ ] Moderation restore rechecks File 10 readiness, source/safety context and current consent before public visibility.
- [ ] Protected owner/operator reads remain denied when membership becomes suspended/inactive or guardian-required verification is withdrawn.
- [ ] Expired idempotency records permit a new operation only after safe expiry cleanup; live duplicate/replay remains one canonical result.
- [ ] START/COMMIT/ROLLBACK and evidence-write failure simulation proves no silent partial mutation or false success.
- [ ] Ranking fixtures verify quality/safety plus bounded freshness and creator-concentration signals; payment/donation is not a ranking signal.
- [ ] Feed and Story pagination at exact eligible page boundaries has no false next cursor and no hidden-state count/existence leak.
- [ ] Provider-returned profile/comments/follow/transcript/download destinations are constrained to allowed same-origin/owner destinations.
- [ ] Guest creator-value signal writes are denied; duplicate logged-in viewer/Reel/signal/day telemetry deduplicates.
- [ ] Creator insight rows below five distinct viewers suppress sensitive aggregate values.
- [ ] Story automatic expiry is bounded and creates auditable `StoryExpired` evidence without orphan state.
- [ ] Well-being session timing counts only active visible Reel seconds and never interrupts unrelated platform pages.
- [ ] JSON-LD with hostile title/caption strings remains valid and cannot break out of script context.
- [ ] English (US), Urdu and Arabic/RTL translatable enum/topic/story labels render correctly without raw machine labels.
- [ ] Privacy export never exposes another user's reporter/appellant text merely because the requester owns the Reel.
- [ ] Privacy erasure is transactional, handles telemetry receipts, preserves governed evidence where required and reports retained/removed data accurately.
- [ ] File 10 dependency transitions, reconciliation and rollback generate required audit/outbox evidence; failure is surfaced.
- [ ] Diagnostics repair returns a failure when reconciliation/audit evidence fails and Safe Mode remains truthful.
- [ ] File 20/21/22/23/24/25/26 provider versions/boundaries operate without duplicate owner, shell, feed, composer, assurance or ranking truth.
- [ ] No-JavaScript Story/Highlight/Response forms complete with nonce and idempotency.
- [ ] Keyboard, screen reader, focus order, 320–1920px, 400% zoom, contrast, RTL, reduced motion, data saver and current browsers/mobile pass.
- [ ] LiteSpeed/cache purge, search/index rebuild, backup restore, DB/schema compatibility and rollback rehearsal pass.
- [ ] Real load/provider-outage/retry/dead-letter behavior meets approved SLOs and no access broadening occurs in degraded mode.
- [ ] Founder signs dated acceptance before controlled live deployment.
