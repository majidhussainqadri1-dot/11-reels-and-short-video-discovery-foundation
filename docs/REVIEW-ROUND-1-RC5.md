# Review Round 1 — Authority, Scope and REST Boundary

## Governing plans
Definitive Master Plan v3.0; Recovered Directives v2.1; Continuous Value / Top-20 Superset v1.0; File 11 Master Plan v1.0.

## Defects found
1. Object-dependent publication readiness ran in `rest_pre_dispatch` before canonical permission resolution, creating an authorization-order oracle.
2. Private history/preferences/value-insights responses did not consistently carry explicit REST no-store/noindex headers.
3. RC4 documentation asserted exact-head completion while no exact-head RC4 run existed.

## Corrections
- Publication readiness now runs in pre-dispatch only after `RSV_Security::can()` grants canonical publish authority; the route permission callback remains authoritative.
- Private REST endpoints add private/no-store/no-cache and noindex headers.
- RC5 is a new candidate with a new exact-head workflow; no RC4 CI result is reused.

## Result
Repository authorization and cache boundaries are corrected. Staging and real-provider validation remain external gates.
