# Review/Fix Round 1 — RC3 Requirements and Architecture

A fresh requirements/architecture review was conducted after importing the third central plan. It found and corrected:

1. package-root mismatch against the File 11 plan;
2. no public approved-topic filter despite repository support;
3. pagination cursor not bound to topic context;
4. no recommendation explanation or explicit no-paid-placement disclosure;
5. module-level `:root` design-token ownership conflicting with File 20/25 boundaries;
6. emoji-only visual symbols instead of a consistent accessible icon component;
7. no read-only provider contract for File 21/25/26 consumers;
8. frontend errors did not expose the safe trace/reference ID.

All defects were corrected and regression/static contracts were added.
