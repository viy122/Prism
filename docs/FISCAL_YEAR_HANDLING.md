# Fiscal-year handling

The fiscal-year selector is shared by the web role pages. Reports and planning queries use the selected year; selecting a year never copies, renumbers, deletes, or moves existing records. New Office Head drafts use the selected year. Existing PPMP and PR fiscal years cannot be changed.

## Role behavior

| Area | Behavior |
| --- | --- |
| Office Head, Budget Office, Procurement, Chancellor, Vice Chancellor | Planning, procurement lists and report views use the selected FY. Existing office/role permissions still apply. |
| For My Signature (all roles), BAC, Accounting, Cashier | Queues include all years, so switching to a new year does not hide older pending work. Signature and payment rows display their originating FY. |
| System Administrator | Manage fiscal years: create, activate, finalize, or reopen. Actions are audited. |

The active year is the default for sessions without a selection. There is no automatic January rollover or automatic lock. Years found in existing records, the current year, and the next year are registered as open during migration. Legacy PRs without a fiscal year are not silently reassigned; they remain in all-year task queues and need data review before appearing in a year-specific report.

## Finalization and reopening

1. As System Administrator, choose **Manage fiscal years** in the selector bar.
2. Review that year's data, enter a reason, then choose **Finalize report and lock planning**.
3. The system saves versioned data for Procurement Office and Chancellor procurement reports, then locks that year's planning. Locked years show their saved report, not recalculated transaction values. CSV exports preserve the same snapshot and identify the year/version.
4. Existing PRs, signatures, canvassing and payments can continue under their original FY. Later transaction changes do not rewrite finalized report versions.
5. Corrections require an administrator to reopen the year with a reason. Earlier snapshots remain available. Finalizing again creates the next version.

Other dashboards remain operational views; they are filtered by FY but are not archived procurement-report snapshots. Snapshots store report data, not copies of every uploaded PDF. Include both the database and uploaded files in normal backups. A checksum detects changes to a saved report payload; it is not a substitute for database access controls or backups.

## Deployment and tests

Back up the database before deployment. From `prism/`, run:

```powershell
php artisan migrate --force
php artisan view:clear
php artisan test --filter=FiscalYearTest
```

The test suite uses an isolated in-memory SQLite database. It checks FY separation, locked planning, immutable report versions, reopen/refinalize, access control, cross-year queues, operational attachments, new drafts, role pages, and snapshot integrity. It does not lock or populate the actual application database.

Verified locally: 9 fiscal-year tests pass. The full suite also has six existing `ExampleTest` failures: those tests request authenticated pages as guests and expect HTTP 200 instead of the login redirect (302).

Do not roll back the fiscal-year migration on a deployed system with archived reports: its `down()` removes the archive tables. Preserve a database backup when reversing this feature.
