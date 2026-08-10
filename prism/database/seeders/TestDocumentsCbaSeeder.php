<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\PrSignatureLog;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates two practice Purchase Requests for testing the mobile signatory
 * flow through the College of Business Administration, with Mayette Cananea
 * as the originating Office Head.
 *
 * PR-2026-CBA-001  at_end_user          → login as mcananea@bsu.edu.ph  to sign
 * PR-2026-CBA-002  at_vice_chancellor   → already signed by mcananea, now
 *                                          waiting on the Vice Chancellor
 *
 * Run: php artisan db:seed --class=TestDocumentsCbaSeeder
 * Safe to re-run (uses updateOrCreate on PR number).
 */
class TestDocumentsCbaSeeder extends Seeder
{
    public function run(): void
    {
        $cbaOffice = Office::where('code', 'CBA')->first();
        $mcananea  = User::where('email', 'mcananea@bsu.edu.ph')->first();

        if (! $cbaOffice || ! $mcananea) {
            $this->command->error('CBA office or mcananea user not found. Run DatabaseSeeder first.');
            return;
        }

        // ── PR 1 — waiting for Office Head (mcananea) signature ──────────────

        $pr1 = PurchaseRequest::updateOrCreate(
            ['number' => 'PR-2026-CBA-001'],
            [
                'office_id'          => $cbaOffice->id,
                'created_by_user_id' => $mcananea->id,
                'title'              => 'Classroom Furniture and Fixtures – Q3 2026',
                'description'        => 'Purchase of classroom furniture and fixtures for the College of Business Administration for the third quarter of fiscal year 2026.',
                'fiscal_year'        => 2026,
                'total_amount'       => 42500.00,
                'status'             => 'pending',
                'signatory_stage'    => 'at_end_user',
            ]
        );

        if ($pr1->items()->count() === 0) {
            $this->items($pr1->id, [
                ['Monoblock Chair (Armchair)',    'Stackable, writing-arm attached',        60, 'pcs',      650.00, 39000.00],
                ['Whiteboard 4x8ft',              'Melamine surface, aluminum frame',        3, 'pcs',      950.00,  2850.00],
                ['Whiteboard Marker (Black)',     'Bullet tip, board marker',                20, 'pcs',       35.00,   700.00],
            ]);
        }

        // ── PR 2 — end user (mcananea) signed, waiting for Vice Chancellor ───

        $pr2 = PurchaseRequest::updateOrCreate(
            ['number' => 'PR-2026-CBA-002'],
            [
                'office_id'          => $cbaOffice->id,
                'created_by_user_id' => $mcananea->id,
                'title'              => 'Business Lab Computers and Peripherals – 2nd Semester AY 2025–2026',
                'description'        => 'Procurement of desktop computers and peripherals for the College of Business Administration computer laboratory for the second semester of AY 2025–2026.',
                'fiscal_year'        => 2026,
                'total_amount'       => 275000.00,
                'status'             => 'pending',
                'signatory_stage'    => 'at_vice_chancellor',
            ]
        );

        if ($pr2->items()->count() === 0) {
            $this->items($pr2->id, [
                ['Desktop Computer (i5, 8GB, 256GB SSD)', 'Bundled with monitor, keyboard, mouse', 20, 'units', 13000.00, 260000.00],
                ['Surge Protector 6-outlet',               'With overload switch',                  20, 'pcs',     375.00,   7500.00],
                ['UTP Cable Cat6 (Boxed)',                 '305m box, solid copper',                  3, 'boxes',  2500.00,   7500.00],
            ]);
        }

        // Signature log: mcananea signed at_end_user for PR2
        if ($pr2->signatureLogs()->count() === 0) {
            PrSignatureLog::create([
                'purchase_request_id' => $pr2->id,
                'signatory_number'    => 1,   // index of 'at_end_user' in SIGNATORY_STAGES
                'signed_by_user_id'   => $mcananea->id,
                'action'              => 'signed',
                'remarks'             => 'Endorsed. Lab computers are overdue for replacement.',
                'signed_at'           => now()->subDays(2),
                'detection_status'    => 'detected',
                'photo_uploaded_at'   => now()->subDays(2),
            ]);
        }

        $this->command->info('✓ PR-2026-CBA-001  stage: at_end_user         → sign as mcananea@bsu.edu.ph');
        $this->command->info('✓ PR-2026-CBA-002  stage: at_vice_chancellor  → already signed by mcananea');
        $this->command->info('  Password: prism2025');
    }

    private function items(int $prId, array $rows): void
    {
        foreach ($rows as [$name, $desc, $qty, $unit, $unitCost, $total]) {
            PurchaseRequestItem::create([
                'purchase_request_id'  => $prId,
                'name'                 => $name,
                'description'          => $desc,
                'quantity'             => $qty,
                'unit'                 => $unit,
                'estimated_unit_cost'  => $unitCost,
                'estimated_total_cost' => $total,
            ]);
        }
    }
}
