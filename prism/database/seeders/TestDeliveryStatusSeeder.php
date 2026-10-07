<?php

namespace Database\Seeders;

use App\Models\{AbstractOfCanvass, FiscalYear, PurchaseOrder, PurchaseRequest, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Add one manual receiving scenario without resetting any existing test progress. */
class TestDeliveryStatusSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Delivery test data is only available locally or in testing.');
        }

        $head = User::with(['office', 'roles'])->where('username', 'office_head')
            ->where('account_status', 'active')->firstOrFail();
        if (!$head->office || !$head->roles->contains('name', 'Office Head / Dean')) {
            throw new \RuntimeException('An active Office Head demo account with an office is required.');
        }
        $today = now()->startOfDay();
        $year = $today->year;
        FiscalYear::findOrFail($year);
        $suffix = 'TEST-DELIVERY-SYNC-'.$head->office->code.'-'.$year.'-Q'.$today->quarter;

        $pr = DB::transaction(function () use ($head, $year, $suffix, $today) {
            $existing = PurchaseRequest::withTrashed()->where('number', 'PR-'.$suffix)->first();
            if ($existing) {
                if ($existing->trashed() || (int) $existing->office_id !== (int) $head->office_id) {
                    throw new \RuntimeException('Existing demo cannot be reused. No records were changed.');
                }
                return $existing;
            }

            $pr = PurchaseRequest::create([
                'number' => 'PR-'.$suffix,
                'office_id' => $head->office_id,
                'created_by_user_id' => $head->id,
                'title' => '[TEST] Automatic Delivery Status - Laptops and Monitors',
                'description' => 'Manual test only: record receipts to try automatic PO delivery status and corrections.',
                'fiscal_year' => $year,
                'total_amount' => 170000,
                'status' => 'approved',
                'signatory_stage' => 'fully_signed',
                'canvassing_stage' => 'completed',
                'remarks' => 'TEST DATA: Simulated approvals. Receive 3 laptops, then 2 laptops, then 2 monitors. Only the last step completes the PO. Correct a receipt to reopen delivery.',
            ]);
            $pr->items()->createMany([
                [
                    'name' => '[TEST] Laptop - Delivery Status',
                    'description' => 'Receive 3 first, then the remaining 2.',
                    'quantity' => 5, 'unit' => 'units',
                    'estimated_unit_cost' => 30000, 'estimated_total_cost' => 150000,
                ],
                [
                    'name' => '[TEST] Monitor - Delivery Status',
                    'description' => 'Receive both after the laptops to complete all items on this PO.',
                    'quantity' => 2, 'unit' => 'units',
                    'estimated_unit_cost' => 10000, 'estimated_total_cost' => 20000,
                ],
            ]);
            $aoc = AbstractOfCanvass::create([
                'purchase_request_id' => $pr->id,
                'code' => 'AOC-'.$suffix,
                'created_by_user_id' => $head->id,
                'signatory_stage' => 'fully_signed',
                'remarks' => 'TEST DATA: Simulated fully signed AOC for manual delivery testing.',
            ]);
            PurchaseOrder::create([
                'abstract_of_canvass_id' => $aoc->id,
                'po_number' => 'PO-'.$suffix,
                'created_by_user_id' => $head->id,
                'supplier_name' => '[TEST] Delivery Demo Supplier',
                'total_amount' => 170000,
                'status' => 'awaiting_delivery',
                'signatory_stage' => 'fully_signed',
                'issued_at' => $today->copy()->subDays(4),
                'procured_on' => $today->copy()->subDays(4)->toDateString(),
                'expected_delivery_date' => $today->copy()->subDays(2)->toDateString(),
                'remarks' => 'TEST DATA: No receipts or payment pre-recorded. Delivery status follows Office Head receipts.',
            ]);
            return $pr;
        });

        $po = $pr->abstractOfCanvass?->purchaseOrder;
        $this->command->info('PR: '.$pr->number);
        $this->command->info('PO: '.$po?->po_number.' | Status: '.$po?->status);
        $this->command->info('Office Head: office_head | Office: '.$head->office->code);
        $this->command->info('Items: 5 laptops and 2 monitors. Existing progress is preserved on re-run.');
        $this->command->info('Open /office-head/purchase-requests?year='.$year.'#pr-card-'.$pr->number);
    }
}
