# PRISM Selenium Functional Correctness (UI)

**After the first full run:** your evidence records 25 PASS and 5 FAIL. Those
mutating fixtures are consumed. Use [RETEST.md](RETEST.md) and
`workflow.retest.local.json` to rerun only FC-03/07/09/23/29 on fresh fixtures.
The original full-run instructions below describe the initial preparation.

FC-01 through FC-30 are prepared for your execution in visible Chrome.
Preparation is not a PASS result. See [TEST_CASES.md](TEST_CASES.md) and
[VERIFICATION.md](VERIFICATION.md).

## Run the prepared suite

Start PRISM at http://prism.test with local MariaDB (prism_db). The root
start-all.bat starts the full stack. Python, Chrome and a matching cached
ChromeDriver are already installed.

In PowerShell:

```powershell
Set-Location C:\xampp\htdocs\Prism
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
. .\tests\selenium\set-test-credentials.ps1

# First safe test: FC-01 only, with visible Chrome.
& .\tests\selenium\.venv\Scripts\python.exe -m pytest tests/selenium/test_access.py::test_fc01_required_login_fields --headed

# Complete suite: all 30 cases, including disposable-record mutations.
.\tests\selenium\run.ps1 -Headed -AllowMutations
```

The credential loader reads the six generated passwords from Git-ignored
fixtures/private/credentials.json and sets PRISM_UI_OFFICE_PASSWORD,
PRISM_UI_FINANCE_PASSWORD, PRISM_UI_CHANCELLOR_PASSWORD,
PRISM_UI_PROCUREMENT_PASSWORD, PRISM_UI_ADMIN_PASSWORD and PRISM_UI_NEXT_PASSWORD
in the current terminal. It does not print passwords.

| Account | Role | Office |
|---|---|---|
| selenium.office@prism.test | Office Head / Dean | SELTEST |
| selenium.finance@prism.test | Budget Office | FIN |
| selenium.chancellor@prism.test | Chancellor | OC |
| selenium.procurement@prism.test | Procurement Office | PROC |
| selenium.admin@prism.test | System Administrator | ICTS |
| selenium.vcaa@prism.test | Vice Chancellor, VCAA | OVC |

Each account is active with exactly its intended role and office assignment.
The Office Head has no Admin role.

## Configuration and fixture evidence

The suite reads **tests/selenium/workflow.local.json**.
**tests/selenium/workflow.example.json** is a password-free copy of the prepared
local configuration; its IDs describe this database, not other installations.
The obsolete config.local.json is retained but is not used.

fixtures/private/prepared-manifest.json records every created fixture and ID.
support/prepare-local.php contains the independent fixture specification.
Records use TEST Selenium titles and the SEL-20261004 prefix. Existing business
records were not reset or edited.

- Proposals 71–77 separately cover draft validation, CRUD, missing sources,
  Finance return, Chancellor return, planning approval and PDF PR creation.
- PRs 77–83, AOCs 35–38 and POs 35–37 stage document, signature and receipt cases.
  FC-20 and FC-21 have separate parents. FC-24/25 use items 112/113.
  FC-22/23 use PRs 80/81.
- Report office SELRPT is separate from mutations. FY 2026 has four Q4 targets
  and one matching paid PR (25%); FY 2025 has two Q4 targets and none procured
  (0%). Expectations come from explicit fixture data, not browser output.
- Notification 664 is the Office Head's one prepared unread notification.
- FC-19 uploads fixtures/private/approved-test-pr.pdf: TEST Extraction Bond Paper A4, 10 reams
  at 250, total 2,500, FY 2026, office SELTEST, fictional test signatory labels.
  The app extracts PDF text in PHP. Item matching uses the local matcher or
  the application's existing PHP fallback.
- FC-27 creates a unique disposable user during execution and leaves it inactive;
  its role and office are preconfigured.

Read-only cases precede mutations. FC-30 runs first among mutations, before
workflows generate more notifications. The full suite consumes its starting
states; run it once on these fixtures. Repeating the complete suite requires
fresh preparation. No automatic reset occurs, and the preparer refuses to
overwrite existing accounts/fixtures.

## Observe and collect

Chrome opens and closes for each test. You will see real logins, navigation,
validation messages, document details, filters, refreshes, proposal decisions,
PDF extraction, document creation, a partial receipt and the VCAA handoff.
Brief pauses respect PRISM's five-logins-per-minute limit. Run sequentially
and avoid competing login attempts.

Actual PASS/FAIL/SKIP outcomes appear in the terminal. Each execution saves
results.csv, results.json and available failure screenshots under
tests/selenium/artifacts/<UTC timestamp>/. The exact directory is printed.
Keep each run's results separate; collection is not PASS evidence.

Optional commands:

```powershell
# Read-only/validation cases; mutations explicitly SKIP.
.\tests\selenium\run.ps1 -Headed

# Module or cross-role workflows.
.\tests\selenium\run.ps1 -Headed -Filter procurement
.\tests\selenium\run.ps1 -Headed -AllowMutations -Filter e2e

# Verification only: neither command runs functional cases or opens Chrome.
& C:\xampp\php\php.exe tests/selenium/support/verify-local.php
.\tests\selenium\run.ps1 -CollectOnly -AllowMutations
```

The current environment needs no installation. On a future environment,
-Install -CollectOnly installs dependencies without executing tests.
Authenticated read-only tests still create sessions/update last-login timestamps.
