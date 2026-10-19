# Review Round 2 — Mutation Atomicity, Idempotency and Evidence

## Defects found
1. Response creation had no rate limit, required idempotency or transaction; audit/outbox failure could leave an orphan relationship.
2. Response publication could be repeated from an invalid state and did not rollback when audit/outbox evidence failed.
3. Story publication and Highlight creation/addition were not evidence-atomic.
4. Story creation silently manufactured a server idempotency key when a REST client omitted one.
5. Reel-create compensation ignored individual rollback failures and returned the original validation error even when compensation failed.

## Corrections
- Story, Highlight and Response mutations now require client idempotency keys, use bounded rate limits where creation is exposed, optimistic versions and database transactions.
- Publication is restricted to the `review` state and update predicates include version/state.
- Audit and outbox writes are mandatory inside the same transaction.
- Compensation checks every delete, records rollback evidence and surfaces compensation failure.

## Result
No known RC5 repository mutation can report success while leaving incomplete canonical state or missing required evidence.
