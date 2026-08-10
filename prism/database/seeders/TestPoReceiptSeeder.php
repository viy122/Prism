<?php

namespace Database\Seeders;

use App\Models\AbstractOfCanvass;
use App\Models\DocumentUpload;
use App\Models\Office;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Builds a full demo chain — PR (fully signed) → AOC (fully signed) → PO
 * (fully signed, delivery complete) — sitting right at the point where the
 * Cashier needs to upload the official receipt to mark it Paid.
 *
 * PO-2026-CBA-003  status: processing_payment → log in as any Cashier
 *                          account to upload the receipt.
 *
 * Run: php artisan db:seed --class=TestPoReceiptSeeder
 * Safe to re-run (uses updateOrCreate on PR/AOC/PO number).
 */
class TestPoReceiptSeeder extends Seeder
{
    public function run(): void
    {
        $cbaOffice = Office::where('code', 'CBA')->first();
        $mcananea  = User::where('email', 'mcananea@bsu.edu.ph')->first();

        if (! $cbaOffice || ! $mcananea) {
            $this->command->error('CBA office or mcananea user not found. Run DatabaseSeeder first.');
            return;
        }

        // ── PR — fully signed, canvassing completed ───────────────────────────

        $pr = PurchaseRequest::updateOrCreate(
            ['number' => 'PR-2026-CBA-003'],
            [
                'office_id'          => $cbaOffice->id,
                'created_by_user_id' => $mcananea->id,
                'title'              => 'Office Supplies and Consumables – Q3 2026',
                'description'        => 'Procurement of office supplies and consumables for the College of Business Administration for the third quarter of fiscal year 2026.',
                'fiscal_year'        => 2026,
                'total_amount'       => 68400.00,
                'status'             => 'approved',
                'signatory_stage'    => 'fully_signed',
                'canvassing_stage'   => 'completed',
            ]
        );

        if ($pr->items()->count() === 0) {
            PurchaseRequestItem::create([
                'purchase_request_id'  => $pr->id,
                'name'                 => 'Bond Paper A4 (Substance 20)',
                'description'          => '10 reams per box',
                'quantity'             => 40,
                'unit'                 => 'reams',
                'estimated_unit_cost'  => 210.00,
                'estimated_total_cost' => 8400.00,
            ]);
            PurchaseRequestItem::create([
                'purchase_request_id'  => $pr->id,
                'name'                 => 'Ink Cartridge Set (4-color)',
                'description'          => 'Compatible with office printers',
                'quantity'             => 20,
                'unit'                 => 'sets',
                'estimated_unit_cost'  => 3000.00,
                'estimated_total_cost' => 60000.00,
            ]);
        }

        $supplierName = 'Nasugbu Office Supplies Center';

        if (! $pr->documents()->where('document_type', 'canvass_quotation')->exists()) {
            $quotePath = 'canvass/' . now()->year . '/demo-quotation-cba-003.pdf';
            Storage::disk('public')->put($quotePath, '%PDF-1.4 demo quotation placeholder');

            DocumentUpload::create([
                'uploaded_by_user_id' => $mcananea->id,
                'attachable_type'     => PurchaseRequest::class,
                'attachable_id'       => $pr->id,
                'document_type'       => 'canvass_quotation',
                'title'               => $supplierName,
                'original_filename'   => 'demo-quotation-cba-003.pdf',
                'file_path'           => $quotePath,
                'mime_type'           => 'application/pdf',
                'file_size'           => 27,
                'status'              => 'uploaded',
                'uploaded_at'         => now()->subDays(6),
            ]);
        }

        // ── AOC — fully signed ─────────────────────────────────────────────────

        $aoc = AbstractOfCanvass::updateOrCreate(
            ['code' => 'AOC-2026-CBA-003'],
            [
                'purchase_request_id' => $pr->id,
                'created_by_user_id'  => $mcananea->id,
                'signatory_stage'     => 'fully_signed',
            ]
        );

        // ── PO — fully signed, delivery complete, awaiting Cashier receipt ────

        $po = PurchaseOrder::updateOrCreate(
            ['po_number' => 'PO-2026-CBA-003'],
            [
                'abstract_of_canvass_id' => $aoc->id,
                'created_by_user_id'     => $mcananea->id,
                'supplier_name'          => $supplierName,
                'supplier_address'       => 'J.P. Laurel St., Nasugbu, Batangas',
                'total_amount'           => 68400.00,
                'status'                 => 'processing_payment',
                'signatory_stage'        => 'fully_signed',
                'issued_at'              => now()->subDays(5),
                'expected_delivery_date' => now()->subDays(1),
                'payment_processing_at'  => now(),
            ]
        );

        $this->command->info('✓ PO-2026-CBA-003  status: processing_payment  → log in as any Cashier account to upload the receipt');
    }
}
