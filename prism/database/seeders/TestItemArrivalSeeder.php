<?php

namespace Database\Seeders;

use App\Models\{AbstractOfCanvass, FiscalYear, PurchaseOrder, PurchaseRequest, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Local sample that leaves receiving blank for manual Office Head testing. */
class TestItemArrivalSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Arrival test data can only be created locally or in testing.');
        }

        $head = User::with('office', 'roles')->where('username', 'office_head')
            ->where('account_status', 'active')->firstOrFail();
        if (!$head->office || !$head->roles->contains('name', 'Office Head / Dean')) {
            throw new \RuntimeException('The office_head demo user must have an office and the Office Head role.');
        }

        $year = now()->year;
        FiscalYear::findOrFail($year);
        $suffix = 'TEST-ARRIVAL-'.$head->office->code.'-'.$year.'-Q'.now()->quarter;

        [$pr, $po, $item] = DB::transaction(function () use ($head, $year, $suffix) {
            // Re-running never resets dates or receipts entered during manual testing.
            $pr = PurchaseRequest::firstOrCreate(['number' => 'PR-'.$suffix], [
                'office_id' => $head->office_id,
                'created_by_user_id' => $head->id,
                'title' => '[TEST] Actual Arrival Date - Laptop Delivery',
                'description' => 'Local receiving demo only. Simulated approved procurement; enter actual arrival manually.',
                'fiscal_year' => $year,
                'total_amount' => 150000,
                'status' => 'approved',
                'signatory_stage' => 'fully_signed',
                'canvassing_stage' => 'completed',
                'remarks' => 'TEST DATA: Record 3 laptops first, then the remaining 2 to try partial and full receiving.',
            ]);
            if ((int) $pr->office_id !== (int) $head->office_id) {
                throw new \RuntimeException('Existing arrival demo belongs to another office.');
            }
            $item = $pr->items()->firstOrCreate(['name' => '[TEST] Laptop - Arrival Tracking'], [
                'description' => 'Five demo laptops for manual receipt testing.',
                'quantity' => 5, 'unit' => 'units',
                'estimated_unit_cost' => 30000, 'estimated_total_cost' => 150000,
            ]);
            $aoc = AbstractOfCanvass::firstOrCreate(['purchase_request_id' => $pr->id], [
                'code' => 'AOC-'.$suffix,
                'created_by_user_id' => $head->id,
                'signatory_stage' => 'fully_signed',
                'remarks' => 'TEST DATA: Simulated fully signed AOC for arrival testing.',
            ]);
            $po = PurchaseOrder::firstOrCreate(['abstract_of_canvass_id' => $aoc->id], [
                'po_number' => 'PO-'.$suffix,
                'created_by_user_id' => $head->id,
                'supplier_name' => '[TEST] Demo Laptop Supplier',
                'total_amount' => 150000,
                'status' => 'awaiting_delivery',
                'signatory_stage' => 'fully_signed',
                'issued_at' => now()->subDays(20),
                'procured_on' => now()->subDays(20)->toDateString(),
                'expected_delivery_date' => now()->subDays(15)->toDateString(),
                'remarks' => 'TEST DATA: Simulated fully signed PO. No actual receipt is pre-recorded.',
            ]);
            return [$pr, $po, $item];
        });

        $this->command->info('Office Head: '.$head->username.' ('.$head->office->code.')');
        $this->command->info('PR: '.$pr->number);
        $this->command->info('PO: '.$po->po_number);
        $this->command->info('Procured: '.$po->procured_on?->toDateString().' | Expected: '.$po->expected_delivery_date?->toDateString());
        $this->command->info('Item quantity: '.$item->quantity.' | Already received: '.$item->receipts()->sum('quantity'));
        $this->command->info('Open /office-head/purchase-requests?year='.$year.' and expand this PR.');
    }
}
