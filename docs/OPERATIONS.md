# File 11 Operations

## Health
The restricted health endpoint and Reels Diagnostics report File 10 readiness/version, schema integrity, invalid states, duplicate media ownership, published count, open reports, pending/dead events and Safe Mode reasons.

## Background work
- outbox claims use lock tokens and stale-lock recovery;
- retry uses bounded attempts and backoff before dead-letter;
- reconciliation re-checks File 10 state and restricts or advances Reels safely;
- cleanup removes expired sessions, idempotency and rate-limit records and bounded analytics data.

## Safe repair
Repair is capability-protected, non-destructive and auditable. Destructive purge is disabled unless an explicit server constant authorizes it.

## Alerts
Production operators must connect privacy-safe health metrics to the approved File 24 assurance/incident layer. Raw patient, report or viewing data must not enter general logs/alerts.
