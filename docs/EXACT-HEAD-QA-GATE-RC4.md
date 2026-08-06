# Exact-Head QA Gate — RC4

The merge gate applies to the final pull-request head, not an earlier RC3/RC4 commit.

Required hosted evidence:
- PHP 8.1, 8.3 and 8.4 complete suite;
- PHP and JavaScript syntax;
- unit, forensic, static and Top-20 contracts;
- deterministic double build;
- ZIP integrity and canonical root;
- byte-for-byte source/package parity;
- exact-head artifact and SHA-256.

A queued, cancelled, skipped, stale-head or failed run is not Green evidence. Hostinger staging and Founder acceptance remain separate gates after hosted CI.