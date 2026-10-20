# RC18 immutable source candidate — File 11
Date: 2026-10-21 Asia/Karachi; working branch `audit/file11-rc18-privacy-export-sql-40-20261021`.
Source base RC17 `8018911c2d547263950c38048f154a6aae1ce3b2`. Exact current release SHA is reported in the final PR and GitHub Actions run.
Version `1.2.0-rc18`. Canonical File11 schema `1.2.0`, contract `19`; no DB migration. Installable package root `reels-foundation-11` and ZIP `reels-foundation-11-1.2.0-rc18.zip`.
Root causes/changes: WordPress privacy exporter treats real SQL failure from history, reports and authored Reels as typed error, not empty success or non-array iteration; privacy eraser transactional count verification errors fail closed and roll back.
QA: `tests/rc18-privacy-export-query-failure.php` 12 cases; `tests/rc18-privacy-erase-query-failure.php` 5 cases; old 40 source sweeps now explicitly require 4 new privacy guards; prior 40 File00/10 cases and 17 restore cases retained; exact-HEAD PHP 8.1/8.3/8.4, deterministic ZIP/manifest/source-extract parity required.
Companion governance: File00 identity; 05 learning; 06 knowledge; 10 media; 12 PDF; 16 AI; 17 communication; 19 delivery; 20 shell; 21 interactions; 22 universal composer; 23 publisher dashboard; 24 assurance; 25 UI; 26 discovery. Conditional CF01/02/03 not activated.
**Deployed version:** unverified. **DB version:** unverified. **Migration state:** unverified. **Live verification:** not performed. **Site staging/Founder acceptance:** pending. A successful CI is not a WordPress deployment.
