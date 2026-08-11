# File 11 — Hostinger Staging Acceptance — 1.1.0-rc7

Status: **External execution pending**. This file is a gate, not a claim of completion.

## Exact artifact and installation
- [ ] Deploy the exact RC7 workflow artifact and record SHA-256/deployed-file parity.
- [ ] Fresh install on production-like Hostinger staging.
- [ ] Upgrade from supported RC5/RC6 state with schema/version checks and no orphan/duplicate pages.
- [ ] Deactivate/reactivate and non-destructive uninstall behavior verified.

## Real dependencies and data
- [ ] Real File 00 identity/role/guardian/suspension claims, including minors, fail closed correctly.
- [ ] Real File 10 current contract/method/event compatibility passes.
- [ ] Real 59/60/600/601-second media boundaries pass from File 10 authoritative duration.
- [ ] Rights, captions/transcript, source/safety and patient-consent gates pass and later restriction removes public eligibility.
- [ ] File 19/20/24/25/26 consumer/provider integrations do not create duplicate truth owners.

## User journeys and safety
- [ ] Guest can browse eligible Reels without login; actions require valid login/capability.
- [ ] Founder/authorized doctor create→review→publish path passes with real media.
- [ ] Non-authorized/suspended/guardian-invalid users cannot mutate or learn hidden object existence.
- [ ] Current report taxonomy routes harm/false claim/impersonation/privacy/abuse/copyright/scam/child-safety correctly.
- [ ] Critical harm/child-safety cases surface immediate priority/expert route and audited due process.
- [ ] Education-only medical safety charter renders on feed/create/single Reel surfaces.
- [ ] Verified local emergency-guidance provider link is safe, current and jurisdiction-appropriate; absent provider does not fabricate guidance.
- [ ] Payment/donation status has no Reel ranking advantage.
- [ ] Private history clear/pause/export/erase and cache isolation pass.
- [ ] Youth-safe mode and guardian-related restrictions pass with real age/claim data.

## Accessibility / responsive / network
- [ ] Keyboard-only next/previous/swipe, focus order/restoration and no-JS fallback pass.
- [ ] Screen reader names/status/live regions pass.
- [ ] 320px through desktop widths and 200–400% zoom/reflow pass without blocking overflow.
- [ ] Urdu/Arabic RTL and long mixed-direction labels/URLs pass.
- [ ] Reduced motion and autoplay-off/default/save-data behavior pass.
- [ ] Weak/offline/provider-degraded network states are truthful and resumable where safe.

## Reliability / privacy / recovery
- [ ] Idempotency/replay/concurrent duplicate mutation tests produce one governed result.
- [ ] Cache/search/index purge after restriction/deletion/suspension is verified.
- [ ] Queue retry/dead-letter/provider outage and reconciliation behavior pass.
- [ ] Performance/load/API/DB-query budgets are measured and accepted.
- [ ] Backup restore, key/dependency recovery where relevant, cache/index rebuild and rollback rehearsal pass.
- [ ] Security/privacy/medical/Sharīʿah review evidence and Founder dated acceptance are recorded.

Only after all applicable checks pass may `Staging-Accepted` be claimed. Live deployment and Operational status remain later independent gates.
