# File11 1.2.0-rc21 candidate

Date: 2026-10-21. Source branch audit/file11-rc21-index-prefix-parity-20261021. Based on RC20 342076369bbd4d873c395a27f9e35739c6373de1. Exact HEAD and artifact digest are bound to the final passing CI only.

Schema unchanged: File11 1.2.0, Top20 1.1.0, Future30 1.0.0. Canonical installable package reels-foundation-11-1.2.0-rc21.zip, root reels-foundation-11. Contract 19. No migrations added.

Corrected root cause: all DDL index prefix lengths are compared against SHOW INDEX Sub_part during migration postcondition checks; no erroneous completion for missing or mismatched prefixes. Automated tests: 48 distinct schema mutation fixtures; 40 source reviews; 40 central source probes; full PHP8.1/8.3/8.4 regression; double deterministic ZIP and source parity. Earlier releases preserved.

Deployed Version / Live DB Version / Migration State: UNVERIFIED. Staging and Live Verification: NOT PERFORMED. This source candidate is not an approved production release.
