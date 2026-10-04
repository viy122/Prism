# Five-case rerun prepared October 4, 2026

User-executed evidence: `artifacts/20261004T020932.038561Z/results.json` records
30 collected, 25 PASS, 5 FAIL, 0 SKIP. No new Selenium execution has been performed.
The original artifacts and post-run records are preserved.

## Diagnosis

| Case | Classification | Evidence and correction |
|---|---|---|
| FC-03 | C — local runtime configuration issue | Apache logged `POST /logout` returning 500 at 10:09:55. Laravel's matching 10:09:56 exception says the session query used SQLite and a nonexistent `database/database.sqlite`. The screenshot is a Laravel error page, not a legitimate alternative login URL. The current `.env` and CLI configuration specify MySQL `prism_db`; configuration was uncached. Cached that verified local configuration with `php artisan config:cache`. Kept the logout/login/access assertions unchanged. The exact cause of the single request losing its environment configuration is not established by these artifacts; the rerun must confirm the remedy. |
| FC-07 | B — test interaction issue | `#proposalSummaryTotal` exists inside `#ppmpEditWrap`, initially `display:none`. The screenshot shows the default document view; proposal 72 still has one item and total 2,500, proving failure preceded CRUD. The test now clicks Edit Items before its first total assertion, as it already did after refresh. All add/edit/remove and persisted-total assertions remain. |
| FC-09 | B — selector issue | `myProposals()` maps its display `id` to the proposal **code**, with numeric `proposalId` separate. Blade builds the article ID/data attribute from that code. The test requested `proposal-row-75`; the actual key is `SEL-20261004-finance_return`. Proposal 75 is returned, and its review and screenshot show `UI finance return f7531962d2`. Select the exact configured code; additionally verify the timeline link points to the expected numeric proposal ID. |
| FC-23 | A — application history display defect | PR 81 advanced to `at_vice_chancellor`; signature log 115 contains `UI signature handoff c033444911`, action `signed`, signer 68. The controller mapped remarks to null unless action was `returned`, although the UI accepts signing remarks and the service saves them. Expose saved remarks for both signing and returning; preserve remarks in the immediate in-page activity entry too. No stage, permission, log-count or remark assertion was removed. |
| FC-29 | A — application year-switch handler defect | The screenshot shows dropdown FY 2025 but report banner FY 2026 and old 4/1/25% totals. Apache recorded no request for the target year in this case. The inline handler used `new URL(...)`; inline event-handler scope includes `document`, whose `URL` is a string. Use explicit `window.URL` and `window.location`. The test now waits for the target URL/selection and retains exact before/after totals plus refresh persistence. This is a real navigation, not an AJAX update or legitimate unchanged report. |

The report source records remain correct: SELRPT has four Q4 targets and one
matching paid PR in 2026, versus two targets and no matching PR in 2025.
There is no evidence requiring a different fiscal-year expectation.

## Rerun fixtures

Use **workflow.retest.local.json**, not the consumed full-suite configuration.

| Case | Prepared state |
|---|---|
| FC-03 | Same active Office Head, no Admin role; cached MySQL configuration |
| FC-07 | New proposal 80, code SEL-20261004-R1-FC07; draft, one item, total 2,500 |
| FC-09 | New proposal 81, code SEL-20261004-R1-FC09; submitted, no reviews or remarks |
| FC-23 | New PR 86, SEL-20261004-R1-PR-FC23; at_end_user, zero signature logs |
| FC-29 | Existing isolated SELRPT proposals 78/79 and paid PO 38, unchanged |

The three new mutating fixtures are independent. FC-03/29 are safe to rerun
individually. FC-07 is reusable after a completely successful CRUD cleanup;
if it fails mid-save, inspect/recreate it. FC-09 and FC-23 consume their fresh
states once, even if a later assertion fails. Do not run either twice on the
same fixture without preparation. The other 25 cases have not been reset.

Post-run database evidence is saved in
`fixtures/private/failure-state-20261004.json`; new IDs and preparation state are
in `fixtures/private/retest-manifest.json`. Preparation/check scripts issue no
browser requests. The original proposal 75 return and PR 81 signature log remain.

## Run only these five cases

Start PRISM, then enter in one PowerShell terminal:

```powershell
Set-Location C:\xampp\htdocs\Prism
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
. .\tests\selenium\set-test-credentials.ps1

$failedCases = @(
  'tests/selenium/test_access.py::test_fc03_password_login_role_access_and_logout'
  'tests/selenium/test_planning.py::test_fc07_budget_item_add_edit_remove_and_totals'
  'tests/selenium/test_planning.py::test_fc09_finance_return_reaches_office_with_remarks'
  'tests/selenium/test_routing.py::test_fc23_sign_and_verify_next_role_queue'
  'tests/selenium/test_reports.py::test_fc29_switch_fiscal_year_changes_report_to_expected_values'
)
& .\tests\selenium\.venv\Scripts\python.exe -m pytest @failedCases --config tests/selenium/workflow.retest.local.json --headed --allow-mutations
```

The suite sorts read-only cases first, so FC-03/29 precede the three mutations.
Results go to a new `artifacts/<UTC timestamp>/results.csv` and `results.json`.
These rerun outcomes are for you to collect; no rerun PASS is claimed here.

Local configuration is now cached at `prism/bootstrap/cache/config.php` (ignored
by Git). Future `.env` edits require rebuilding that cache; no `.env` values,
database defaults or authentication business rules were changed.

## Files changed for this diagnosis

- `prism/resources/views/prism/partials/fiscal-year.blade.php`: explicit browser globals in the year-change handler.
- `prism/app/Http/Controllers/Concerns/HandlesSignatureQueue.php`: include saved signing remarks in history.
- `prism/resources/views/prism/shared/for-my-signature.blade.php`: keep submitted remarks in the immediate activity entry.
- `tests/selenium/test_planning.py`: open Edit Items; select timeline by proposal code and verify its numeric link.
- `tests/selenium/test_reports.py`: wait for observable target year without changing expected totals.
- `tests/selenium/support/prepare-local.php`: emit the required proposal_code for future fixtures.
- `tests/selenium/workflow.local.json` and `workflow.example.json`: add the original Finance proposal code, preserving the original fixture references.
- `tests/selenium/workflow.retest.local.json`: separate configuration for fresh rerun records.
- `tests/selenium/support/inspect-failures.php`: preserve read-only database evidence.
- `tests/selenium/support/prepare-retest.php`: guarded fresh fixture creation and read-only `--check` mode.
- `tests/selenium/.gitignore`: exclude local rerun configurations.
- `tests/selenium/README.md`, `VERIFICATION.md`, and this file: current execution status and rerun instructions.
- Private local files: post-run snapshot, rerun manifest, and generated Laravel configuration cache.
