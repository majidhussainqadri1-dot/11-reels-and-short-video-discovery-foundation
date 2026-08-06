# Review Round 4 — Fresh Adversarial Release Audit

Defects: RC3 evidence could be mistaken for RC4; legacy unsourced Reels could remain public; saves/comments might be fabricated locally; create-context failure could orphan a draft; client labels and scoped tokens had gaps.

Corrections: RC4-only evidence; legacy public fail-close with repair access; owner metrics or unavailable state; compensating create rollback; translation-ready client fallbacks; corrected scoped token inheritance; one exact-head PHP 8.1/8.3/8.4 workflow with deterministic artifact.

Result: traceability, forbidden stale patterns, double-build and source parity are gates. External staging remains pending by design.
