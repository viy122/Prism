# Preparation verification — October 4, 2026 (Asia/Manila)

> Update after user execution: artifacts/20261004T020932.038561Z records
> **25 PASS, 5 FAIL, 0 SKIP**. Those are the user's original results.
> See [RETEST.md](RETEST.md) for the five diagnoses, corrections and fresh
> fixtures. No Selenium rerun was performed by the assistant. The preparation
> record below describes the earlier, pre-execution session.


This session did **not** execute Selenium functional tests, launch Chrome, or
dispatch authenticated application requests. Execution is reserved for the user.
No current functional PASS/FAIL/SKIP results are claimed.

Prepared in local prism_db:

- Six dedicated active accounts, generated password hashes, exact roles,
  office assignments and VCAA subtype.
- Nine proposals, eight PRs, five AOCs, four POs, separate receipt items, two
  test offices, one unread notification, PDF/source files and credential loader.
- FC-01 through FC-30 configuration with actual IDs and independently specified
  expected values. Existing business records were not edited.

Preparation checks:

- Read-only PHP preflight: 166 checks for account hashes/roles/offices, years,
  routes, records, file existence, unused PR number, AOC/PO eligibility, receipts,
  report source data, notification count, filter ID sets and PDF text contents.
- pytest collection with mutation option: **30 cases collected; zero executed**.
- Python syntax compilation, dependency consistency, JSON parsing and PowerShell
  runner/credential-loader syntax.
- Python 3.10.11, pytest 9.1.1, Selenium 4.50.0; Chrome 154.0.8037.93 and
  cached ChromeDriver 154.0.8037.92 (same Chrome build).

Local preparation evidence: fixtures/private/prepared-manifest.json and
fixtures/private/preflight.json. These describe preparation, not UI results.
Collection-only mode creates no results.csv/results.json.

No PRISM business logic was changed in this session. Suite assertions were
strengthened for CRUD totals, receiving limits, signature history count and
exact report rows. Expected values were not copied from a rendered page.

Older artifacts remain. The previous verification document referenced an earlier
FC-01/FC-02 smoke run at artifacts/20261004T003755.345691Z/. That historical run
does not represent execution against the newly prepared fixtures.
