# Review Round 2 — Fresh Adversarial Review after RC2 Coding

A second independent review was performed only after Round 1 corrections. It found and corrected further defects:

1. REST moderation did not capture the `$public_id` closure variable, breaking opaque report-ID moderation;
2. a global JavaScript announcement fallback could replace the entire document body;
3. the feed lacked a no-JavaScript continuation route;
4. submit, publish, progress, appeal and moderation could accept owner-state changes without verified audit/outbox evidence;
5. history clearing was not one transaction and could partially delete state;
6. appeals were reporter-only, did not support affected Reel owners, and did not record the appellant identity;
7. removed Reels had no validated restoration transition;
8. restoration did not re-check current File 10 duration/status/rights/consent eligibility;
9. localization source extraction was absent.

All defects above were fixed. New regression checks verify public-ID capture, evidence-failure codes, safe live-region creation, no-JavaScript pagination, appellant schema/privacy handling and File 10 revalidation before restoration.

Final local result: `all File 11 automated checks PASS` and two deterministic builds were byte-identical.
