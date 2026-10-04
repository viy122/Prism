# Item delivery and receiving

Arrival means receipt by the requesting office. It is independent of the existing Supply Office delivery and accounting/payment workflow. A paid PO can still have outstanding office receipts.

## Where to use it

- **Office Head → Purchase Requests:** expand a PR to see Arrival Date and Receiving Status on each item. Open **View Details / Record Receipt** to enter actual arrival, quantity, receiver, remarks, and an optional PDF/JPG/PNG delivery slip (10 MB maximum). Only the requesting office's Office Head can record or correct receipts.
- **Procurement Office → Purchase Orders:** use **Item Delivery & Receiving** and open the item's details to save **Procured Date** and **Expected Delivery Date**. These dates apply to the PO's items. Procured Date is actual supplier order confirmation after approvals, not the PO draft creation timestamp or payment date.
- **Procurement Reports**, **Chancellor Procurement Reports**, **Vice Chancellor Division Procurement Status**, **Accounting**, and **Cashier:** receiving tables show actual progress. Vice Chancellors see their configured division. Accounting/Cashier can reference older fiscal-year transactions in their queues.

The existing payment KPIs remain payment measures; the Office Head page labels them **Paid**. Receiving is shown separately per item. The PPMP planning template is unchanged by this feature.

## Dates and quantities

- Multiple receipts support partial deliveries. The row shows `Latest partial: YYYY-MM-DD` until all ordered quantities have arrived. On completion, Arrival Date is the latest actual receipt date, including receipts entered out of order.
- Delivery Duration = full receipt date minus Procured Date. It is unavailable until both are known.
- Delay = days beyond Expected Delivery Date. For outstanding quantities it is measured through today; after full receipt it stops at the arrival date. Without an expected date, the indicator says **No target date**.
- Existing records are not backfilled from status, payment, or last-update timestamps. They retain **Arrival not recorded** until a receipt is entered.
- Arrivals cannot be future-dated or precede a known Procured Date. Combined received quantities cannot exceed the item quantity. Corrections require a reason and retain before/after values and the acting user in `audit_logs`.
- Fully signed, active POs are required. Receiving and date updates do not advance Supply Office delivery or payment statuses.
- Receipt writes and PO date updates lock the PO row in a transaction. Submission tokens prevent duplicate receipt creation. Files are stored privately and downloaded through an authorized endpoint.

## Fiscal years and reports

Transactions may continue after planning finalization. Select the PR's fiscal year on the Office Head or Procurement page to work with it. Finalized reports preserve their receiving snapshot; later receipts/corrections appear in operational views, not in an already finalized report version. Older snapshots without receiving data display that it was not captured. The procurement CSV includes dates, quantities, status, duration, and delay. Quarterly reporting uses the linked APP item's quarter, falling back to the quarter in the PR number when no APP item is linked.

## Setup and verification

Apply `2026_09_30_000001_add_item_receiving_records.php` with Laravel migrations. It adds nullable `purchase_orders.procured_on` and the `item_receipts` table; no historical business dates are fabricated.

Run `php artisan test --filter="ItemReceivingTest|FiscalYearTest"` against the configured isolated SQLite test database. The receiving tests cover partial/full receipt, permissions, quantities, dates, corrections, private attachments, finalized years, report filters, and immutable snapshots.
