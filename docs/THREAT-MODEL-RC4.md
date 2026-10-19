# Threat Model — RC4

| Threat | Control |
|---|---|
| Forged publishing claim | File 00-compatible claims, deny-only filter, sensitive-action 2FA |
| Cross-topic/youth cursor replay | Signed cursor context includes topic and youth mode |
| Expired Story cache replay | Request-time expiry plus hourly transition |
| Rights/consent drift | Snapshot equality and current File 10 eligibility on every read |
| Patient media reused | Canonical consent-provider verification; fail closed |
| Raw media URL leakage | File 10 secure-delivery adapter only |
| Minor exposed | Minor claim forces youth mode; topics/actions/downloads restricted |
| Compulsive use | Autoplay choice, session limit, natural stop, one late-night reminder |
| Viewer identity in insights | Aggregate thresholds and no viewer list |
| Fabricated saves/comments | Owner provider or explicit unavailable state |
| Unsourced legacy Reel public | Public gate quarantines until repaired |
| Partial create | Compensating transaction removes orphan draft/evidence/idempotency |
| Destructive uninstall | Explicit wp-config purge flag and delete-plugin capability |
