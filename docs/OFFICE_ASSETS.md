# Equipment allocation, use and warranty

## Pages and flow

1. Office Head → Purchase Requests → item **View Details** → **Receiving**: record the actual receipt.
2. Open **Office Assets** → **Received Items** (`/office-head/office-assets/received-items`). This dedicated page lists whole-unit receipts awaiting registration, with received, registered and remaining counts. Expand **Register for allocation**, confirm that these are equipment units, and register the quantity. Fully registered receipts leave this pending list. Registration redirects to **Asset Register**, filtered to the newly created batch. Registration is also available from the receiving item's **Allocation & Usage** tab.
3. Select units in Office Assets, then expand **Assign / update selected units** or **Set warranty for selected units**. An individual **View / Update** page supports serial/property numbers, allocation, usage, warranty proof and history.
4. **Asset Register** (`/office-head/office-assets`) contains registered units, search, filters, bulk updates and incoming transfers. It is a separate page from Received Items. Opening **View / Update** retains the full Office Head sidebar, keeps Asset Register highlighted, and shows an **Office Assets / Asset Register / [Asset]** breadcrumb. Search supports item/reference, serial/property number, room and accountable person. Filters cover office, acquisition FY, allocation, usage and warranty. Summary cards use the current filters; bulk updates apply only to selected units on the displayed page. Reset filters or use **View all registered units** to leave the new-registration batch filter.
5. Procurement and Chancellor → Reports → **Allocation & Warranty**; Vice Chancellor → Division Procurement Status → **Allocation & Warranty**. These are current asset views, separate from finalized procurement snapshots.

Each asset represents one physical equipment unit linked to its actual receipt, item and PO. Consumables and fractional quantities are excluded from equipment registration. Registration does not change the receipt, procurement status or payment status. Historical units are not created automatically.

## Allocation and access

- Only an Office Head for the current owning office can edit a unit. Procurement, Chancellor and System Administrator can inspect records; Vice Chancellors see offices within their configured division. Other roles have no asset access.
- Assigned units require location, accountable person's name and assignment date. `In Use` additionally requires an actual usage start date. Assignment and use cannot precede receipt (or acceptance into the current office); usage cannot precede the current assignment.
- Usage values: Not Yet In Use, In Use, Under Repair, Retired. Allocation is independent of usage. Serial number is optional; property number must be unique when supplied.
- Cross-office transfers require an active Office Head in the destination. A pending transfer freezes asset edits. The destination Office Head accepts/rejects; the originating Office Head can cancel. Acceptance changes the owner and clears assignment/use dates for reassignment, while preserving an Under Repair status. Original receipt, acquisition year, warranty and history remain attached.
- Asset updates require a reason and preserve actor, before/after values and timestamp. Version checks reject stale page submissions. Bulk updates are atomic: one unauthorized or stale record prevents the entire update.
- Registered units, including transferred/retired units, count against their original receipt. Receipt quantity cannot be reduced below this count or made fractional. Receipt arrival corrections cannot move after a current assignment/use date. There is no asset deletion endpoint.

## Warranty

Coverage is explicitly With Warranty, No Warranty or Not Yet Recorded. With Warranty requires a supplier-confirmed start and either an expiry date or duration in months. Explicit expiry takes precedence; month calculation clamps to the last day of shorter months. Receipt date is shown for reference but does not fabricate warranty coverage.

Statuses are derived as of the current application date: Not Yet Active before coverage starts; Under Warranty when more than 30 days remain; Expiring Soon for 0–30 days; Warranty Expired after expiry. Coverage includes the expiry date. No Warranty and Not Yet Recorded remain distinct. Supplier/service contact and coverage notes are optional.

PDF/JPG/PNG proof up to 10 MB is stored privately and served only after asset access checks. Bulk warranty entry can share one proof across selected units. Replacing proof preserves the earlier stored file for audit retention; the current asset page links the latest proof.

These changes provide warranty status and filtering, not automatic expiry notifications.

## Fiscal years

Office Assets includes all acquisition years by default and provides its own acquisition FY filter. The global report FY selector is hidden on asset pages. Registration and asset maintenance can continue after planning finalization, and do not rewrite finalized procurement report snapshots.

## Deployment and tests

Apply only the additive migration from `prism/`:

```powershell
php artisan migrate --path=database/migrations/2026_10_06_000001_create_office_assets.php --force
php artisan view:clear
php vendor/phpunit/phpunit/phpunit
```

The existing TestCase guard requires uncached SQLite `:memory:` configuration before RefreshDatabase can execute. Never run database-reset commands against the application database.

`OfficeAssetTest` covers received quantity limits, duplicate registration, discrete equipment confirmation, assignment/use dates, stale versions, atomic bulk changes, warranty boundaries/duration/proof, unique property numbers, private access, role/division visibility, older fiscal years, receipt corrections, transfer acceptance/rejection/cancellation, and preserved finalized reports.

Existing ExampleTest guest checks were corrected to expect login redirects for protected pages. The obsolete Finance APP URL correctly expects 404; authenticated role pages are covered by the fiscal-year/receiving/asset tests.

### Isolated browser verification

`tests/selenium/support/prepare-asset-ui.php` creates a new timestamped SQLite fixture under `tmp/` and refuses to overwrite an existing file. It boots with an explicit uncached SQLite configuration and verifies it before migrations. Test accounts and equipment are fictional and never written to the application database.

Start a separate PHP server on port 8092 using `APP_ENV=testing`, `APP_CONFIG_CACHE=<workspace>/tmp/no-asset-ui-config.php`, `DB_CONNECTION=sqlite`, `DB_DATABASE` from `tmp/office-assets-browser-fixture.json`, empty `DB_URL`, `CACHE_STORE=array`, `SESSION_DRIVER=file`, `SESSION_COOKIE=prism_asset_browser`, and `QUEUE_CONNECTION=sync`. Run `php artisan serve --host=127.0.0.1 --port=8092 --no-reload` from `prism/` with these environment variables; when using PowerShell Start-Process, use `-WindowStyle Hidden`.

```powershell
php tests/selenium/support/prepare-asset-ui.php
# Start the isolated server as described above, then:
tests/selenium/.venv/Scripts/python.exe tests/selenium/office_assets_smoke.py
tests/selenium/.venv/Scripts/python.exe tests/selenium/office_assets_smoke.py --verify-layout
```

The full browser flow consumes its fixture. Use a newly prepared database for another full run. `--resume-transfer` resumes after the transfer-request step, and `--verify-layout` is read-only after the full flow. Results and screenshots are saved under `tmp/office-assets-*`. Stop the isolated server after testing.
