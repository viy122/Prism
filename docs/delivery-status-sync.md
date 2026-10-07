# Delivery status from receipts

Recording or correcting an item receipt recalculates the delivery status for its active, fully signed PO in the same database transaction. A PO is complete only when every ordered item is fully received. Incomplete quantities produce partial delivery; a correction can reopen a previously complete delivery.

Accounting and Cashier retain control over payment processing and payment completion. Incomplete receipts on a processing/paid PO display a review flag in receiving views. Audit records capture delivery changes and transitions into/out of a receipt discrepancy. The receipt save response updates the PR header and PO labels without a page reload.

Procurement can still forward an issued PO to Supply. Partial/complete delivery now comes from receipts rather than manual status buttons.

## Existing records

Preview first:

```sh
php artisan procurement:sync-delivery-statuses --dry-run
```

Apply:

```sh
php artisan procurement:sync-delivery-statuses
```

The command is repeatable, preserves payment statuses, and skips inactive/unsigned POs and historical orders with no receipt evidence. It does not send notifications or alter finalized report snapshots.

## Tests

`phpunit.xml` forces an isolated, uncached SQLite in-memory configuration. `tests/TestCase.php` refuses persistent database configurations before the database-refresh test traits execute. The regular application's cached configuration is left intact.

Validation: ItemReceivingTest passed 19 tests / 193 assertions. The full suite passed 30 tests and failed 6 ExampleTest checks that expect HTTP 200 from authenticated pages without signing in (actual HTTP 302). A separate probe confirmed that cached local configuration is rejected by the test guard before database setup.

## Recovery incident — October 5, 2026

The initial test run, before the isolation safeguard was added, used the application's cached MySQL configuration instead of the SQLite settings in phpunit.xml. RefreshDatabase reset the local prism_db schema and data. Read-only verification subsequently found zero users and zero purchase requests; the target PR-TEST-ARRIVAL-CICS-2026-Q3 was absent.

The user was informed immediately after verification. The newest SQL export found was `C:/Users/khriz/Downloads/prism_db (11).sql`, last modified September 20, 2026 (dump header: September 19, 19:17). No newer export or Windows shadow copy was found in the locations checked.

The user authorized restoring this export. It was first imported into `prism_recovery_20261005`; four pending migrations were successfully applied there. The validated copy was then restored to `prism_db`, preserving 65 users, 9 roles, 44 purchase requests, and 15 purchase orders. All 14 demo-login routes were verified over HTTP to redirect to their dashboards with HTTP 200. All current migrations are applied.

Private recovery artifacts (source export, pre-restore dump, validated dump, and helper) are preserved in `prism/storage/app/private/recovery-20261005/`, which is ignored by Git. The recovery database is also retained. Changes after the backup remain unrecovered, including the September 30 receipt records and PR-TEST-ARRIVAL-CICS-2026-Q3. The restored database has zero item receipts, so delivery reconciliation has no receipt-backed changes to apply. No replacement receipt data was fabricated.
