<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\PrSignatureLog;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates two practice Purchase Requests for testing the mobile signatory flow.
 *
 * PR-2026-COE-001  at_end_user          → login as msantos@bsu.edu.ph  to sign
 * PR-2026-COE-002  at_vice_chancellor   → login as jdelacruz@bsu.edu.ph to sign
 *
 * Run: php artisan db:seed --class=TestDocumentsSeeder
 * Safe to re-run (uses updateOrCreate on PR number).
 */
class TestDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $coeOffice = Office::where('code', 'COE')->first();
        $msantos   = User::where('email', 'msantos@bsu.edu.ph')->first();
        $jdelacruz = User::where('email', 'jdelacruz@bsu.edu.ph')->first();
        $jreyes    = User::where('email', 'jreyes@bsu.edu.ph')->first();

        if (! $coeOffice || ! $msantos) {
            $this->command->error('COE office or msantos user not found. Run DatabaseSeeder first.');
            return;
        }

        // ── PR 1 — waiting for Office Head (msantos) signature ────────────────

        $pr1 = PurchaseRequest::updateOrCreate(
            ['number' => 'PR-2026-COE-001'],
            [
                'office_id'          => $coeOffice->id,
                'created_by_user_id' => $jreyes?->id,
                'title'              => 'Office Supplies and Materials – Q3 2026',
                'description'        => 'Purchase of office supplies and materials for the College of Engineering for the third quarter of fiscal year 2026.',
                'fiscal_year'        => 2026,
                'total_amount'       => 18625.00,
                'status'             => 'pending',
                'signatory_stage'    => 'at_end_user',
            ]
        );

        if ($pr1->items()->count() === 0) {
            $this->items($pr1->id, [
                ['Ballpen (Black, Pilot G2)',      'Box of 12 pcs',                         5, 'boxes',    120.00,   600.00],
                ['Bond Paper A4 80gsm',            'Substance 24, 500 sheets/ream',         20, 'reams',    250.00,  5000.00],
                ['Printer Ink Cartridge (Black)',  'Epson 003 Black',                        5, 'bottles',  350.00,  1750.00],
                ['Printer Ink Cartridge (Color)',  'Epson 003 Cyan/Magenta/Yellow set',      5, 'sets',     750.00,  3750.00],
                ['Folder Long (Pressboard)',       'Assorted colors',                       50, 'pcs',       25.00,  1250.00],
                ['Correction Tape 5mm×6m',         'White correction tape',                 25, 'pcs',       65.00,  1625.00],
                ['Sticky Notes 3×3',               'Post-it style, assorted colors',        25, 'pads',      45.00,  1125.00],
                ['Stapler No. 35 with Wires',      'Heavy-duty stapler, 1 box staples',      3, 'sets',     175.00,   525.00],
                ['Scissors (Medium)',              'Stainless steel blade',                 10, 'pcs',      120.00,  1200.00],
                ['Permanent Marker (Black)',       'Broad tip, Pentel or equivalent',       20, 'pcs',       55.00,  1100.00],
                ['Puncher Heavy Duty',             '2-hole, heavy-duty',                     3, 'pcs',      250.00,   750.00],
                ['Tape Dispenser (Desktop)',       'With one roll clear tape included',      5, 'pcs',      185.00,   950.00],
            ]);
        }

        // ── PR 2 — end user signed, waiting for Vice Chancellor ───────────────

        $pr2 = PurchaseRequest::updateOrCreate(
            ['number' => 'PR-2026-COE-002'],
            [
                'office_id'          => $coeOffice->id,
                'created_by_user_id' => $jreyes?->id,
                'title'              => 'Laboratory Equipment and Consumables – 2nd Semester AY 2025–2026',
                'description'        => 'Procurement of laboratory equipment and consumables required for Engineering laboratory subjects for the second semester of AY 2025–2026.',
                'fiscal_year'        => 2026,
                'total_amount'       => 85000.00,
                'status'             => 'pending',
                'signatory_stage'    => 'at_vice_chancellor',
            ]
        );

        if ($pr2->items()->count() === 0) {
            $this->items($pr2->id, [
                ['Digital Multimeter',          'Fluke 115 or equivalent, auto-ranging',  10, 'units',  3500.00, 35000.00],
                ['Soldering Iron Kit 40W',      'With stand, sponge, and solder wire',    10, 'sets',    850.00,  8500.00],
                ['Breadboard (Full-size)',       '830 tie-point breadboard',               20, 'pcs',     250.00,  5000.00],
                ['Jumper Wires Set',            '40 pcs M-M, 40 M-F, 40 F-F',            20, 'sets',    120.00,  2400.00],
                ['Resistor Kit 1/4W',           'Assorted values, 600 pcs/set',           10, 'sets',    450.00,  4500.00],
                ['Oscilloscope (Handheld)',      'DSO138, 200kHz bandwidth',                5, 'units',  3500.00, 17500.00],
                ['USB-C Charging Cable 1m',     'Fast charge, braided nylon',             30, 'pcs',      85.00,  2550.00],
                ['Extension Cord 5-outlet',     '3m cord, with individual switches',      10, 'pcs',     950.00,  9550.00],
            ]);
        }

        // Signature log: msantos signed at_end_user for PR2
        if ($pr2->signatureLogs()->count() === 0 && $msantos) {
            PrSignatureLog::create([
                'purchase_request_id' => $pr2->id,
                'signatory_number'    => 1,   // index of 'at_end_user' in SIGNATORY_STAGES
                'signed_by_user_id'   => $msantos->id,
                'action'              => 'signed',
                'remarks'             => 'Endorsed. Items are urgently needed for laboratory classes this semester.',
                'signed_at'           => now()->subDays(2),
                'detection_status'    => 'detected',
                'photo_uploaded_at'   => now()->subDays(2),
            ]);
        }

        $this->command->info('✓ PR-2026-COE-001  stage: at_end_user         → sign as msantos@bsu.edu.ph');
        $this->command->info('✓ PR-2026-COE-002  stage: at_vice_chancellor  → sign as jdelacruz@bsu.edu.ph');
        $this->command->info('  Password for both: prism2025');
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
