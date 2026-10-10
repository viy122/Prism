<?php

namespace Tests\Feature;

use App\Models\{AbstractOfCanvass, AuditLog, Campus, FiscalYear, ItemReceipt, Office, OfficeAsset, OfficeAssetTransfer, PurchaseOrder, PurchaseRequest, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Tests\TestCase;

class OfficeAssetTest extends TestCase
{
    use RefreshDatabase;

    private Office $office;
    private User $head;
    private ItemReceipt $receipt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 6)->startOfDay());
        Storage::fake('local');
        DB::connection()->getPdo()->sqliteCreateFunction('MONTH', fn ($date) => $date ? (int) date('n', strtotime($date)) : null, 1);
        $campus = Campus::create(['code' => 'ASSET', 'name' => 'Asset Test']);
        $this->office = Office::create(['campus_id' => $campus->id, 'code' => 'CICS', 'name' => 'Computing', 'office_type' => 'academic']);
        $this->head = $this->user('Office Head / Dean');
        foreach ([2026, 2027] as $year) FiscalYear::firstOrCreate(['year' => $year]);
        $pr = PurchaseRequest::create(['office_id' => $this->office->id, 'number' => 'PR-ASSET-2026', 'title' => 'Equipment', 'fiscal_year' => 2026, 'status' => 'submitted', 'signatory_stage' => 'fully_signed']);
        $item = $pr->items()->create(['name' => 'Asset Test Laptop', 'quantity' => 5, 'unit' => 'unit', 'estimated_unit_cost' => 100, 'estimated_total_cost' => 500]);
        $aoc = AbstractOfCanvass::create(['purchase_request_id' => $pr->id, 'code' => 'AOC-ASSET', 'signatory_stage' => 'fully_signed']);
        $po = PurchaseOrder::create(['abstract_of_canvass_id' => $aoc->id, 'po_number' => 'PO-ASSET', 'supplier_name' => 'Supplier', 'signatory_stage' => 'fully_signed', 'status' => 'paid', 'procured_on' => '2026-09-10']);
        $this->receipt = ItemReceipt::create(['purchase_order_id' => $po->id, 'purchase_request_item_id' => $item->id, 'arrival_date' => '2026-09-18', 'quantity' => 3, 'received_by_name' => 'Receiver', 'recorded_by_user_id' => $this->head->id, 'submission_token' => (string) Str::uuid()]);
        $this->actingAs($this->head);
    }

    private function user(string $role, ?Office $office = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['office_id' => ($office ?? $this->office)->id, 'account_status' => 'active']);
        $user->roles()->attach(Role::firstOrCreate(['name' => $role], ['code' => Str::slug($role)]));
        return $user;
    }

    private function register(int $count = 1): OfficeAsset
    {
        $this->post('/office-assets/receipts/'.$this->receipt->id.'/register', ['quantity' => $count, 'equipment_confirmed' => 1, 'submission_token' => (string) Str::uuid()])->assertSessionHasNoErrors()->assertRedirect();
        return OfficeAsset::latest('id')->firstOrFail();
    }

    private function updateAsset(OfficeAsset $asset, array $data)
    {
        return $this->post('/office-assets/update', $data + ['asset_ids' => [$asset->id], 'versions' => [$asset->id => $asset->fresh()->version], 'reason' => 'Verified physical equipment']);
    }

    private function allocation(array $overrides = []): array
    {
        return $overrides + ['section' => 'allocation', 'allocation' => 'assigned', 'location' => "Dean's Office", 'accountable_person' => 'Employee A', 'assigned_on' => '2026-09-19', 'usage_status' => 'in_use', 'usage_started_on' => '2026-09-20'];
    }

    public function test_kpi_breakdowns_follow_category_and_current_filters(): void
    {
        $this->register(3);
        $units = OfficeAsset::orderBy('id')->get();
        $units[0]->update(['usage_status' => 'in_use', 'assigned_on' => '2026-09-19', 'location' => 'Lab A', 'accountable_person' => 'Employee A']);
        $units[1]->update(['usage_status' => 'under_repair', 'warranty_coverage' => 'covered', 'warranty_start' => '2026-09-18', 'warranty_end' => '2026-10-20']);
        $response = $this->get('/office-head/office-assets')->assertOk()->assertSee('View Registered Units breakdown');
        $details = $response->viewData('summaryDetails');
        $this->assertCount(3, $details['Registered Units']);
        $this->assertCount(2, $details['Unassigned']);
        $this->assertSame($units[0]->id, $details['In Use'][0]['id']);
        $this->assertSame('PR-ASSET-2026', $details['In Use'][0]['source']);
        $this->assertSame('Lab A', $details['In Use'][0]['location']);
        $this->assertSame($units[1]->id, $details['Under Repair'][0]['id']);
        $this->assertSame($units[1]->id, $details['Warranty Expiring Soon'][0]['id']);
        $response = $this->get('/office-head/office-assets?usage=in_use')->assertOk();
        $this->assertCount(1, $response->viewData('summaryDetails')['Registered Units']);
        $this->assertSame([], $response->viewData('summaryDetails')['Under Repair']);
        $this->get('/office-head/office-assets?search=no-match')->assertOk()
            ->assertViewHas('summaryDetails', fn ($groups) => collect($groups)->every(fn ($rows) => $rows === []));
    }

    public function test_office_assets_lists_received_items_before_registration_and_tracks_remaining_units(): void
    {
        $url = '/office-head/office-assets/received-items?year=2027';
        $response = $this->get($url)->assertOk()->assertSee('Received items ready for registration')
            ->assertSee('Asset Test Laptop')->assertSee('data-ready-receipt="'.$this->receipt->id.'"', false);
        $this->assertEquals(3, $response->viewData('readyUnitCount'));
        $this->assertDatabaseCount('office_assets', 0);
        $this->get('/office-head/office-assets')->assertOk()->assertSee('<h1>Asset Register</h1>', false)
            ->assertDontSee('data-ready-receipt=', false)->assertDontSee('Register for allocation');
        $response->assertDontSee('data-asset-select', false)->assertDontSee('Equipment register');
        $this->register(2);
        $response = $this->get($url)->assertOk();
        $this->assertEquals(1, $response->viewData('readyUnitCount'));
        $this->register(1);
        $response = $this->get($url)->assertOk()->assertDontSee('data-ready-receipt="'.$this->receipt->id.'"', false);
        $this->assertEquals(0, $response->viewData('readyUnitCount'));
        $this->assertEquals(3, $this->get('/office-head/office-assets')->assertOk()->viewData('assets')->total());
    }

    public function test_received_candidates_exclude_other_offices_inactive_orders_and_fractional_quantities(): void
    {
        $other = Office::create(['campus_id' => $this->office->campus_id, 'code' => 'OTHER', 'name' => 'Other', 'office_type' => 'academic']);
        $this->actingAs($this->user('Office Head / Dean', $other));
        $this->get('/office-head/office-assets/received-items')->assertOk()->assertDontSee('Asset Test Laptop');
        $this->actingAs($this->head);
        $this->receipt->update(['quantity' => 1.5]);
        $this->assertEquals(0, $this->get('/office-head/office-assets/received-items')->viewData('readyUnitCount'));
        $this->receipt->update(['quantity' => 3]);
        $this->receipt->purchaseOrder->update(['signatory_stage' => 'at_procurement']);
        $this->assertEquals(0, $this->get('/office-head/office-assets/received-items')->viewData('readyUnitCount'));
        $this->receipt->purchaseOrder->update(['signatory_stage' => 'fully_signed']);
        $this->receipt->item->purchaseRequest->update(['status' => 'cancelled']);
        $this->assertEquals(0, $this->get('/office-head/office-assets/received-items')->viewData('readyUnitCount'));
        $this->actingAs($this->user('Procurement Office'));
        $this->get('/office-head/office-assets/received-items')->assertRedirect(route('procurement-office.dashboard'));
        $this->get('/procurement-office/office-assets')->assertOk()->assertDontSee('Received items ready for registration');
    }

    public function test_new_registration_filter_and_detail_navigation_stay_in_asset_register(): void
    {
        $oldAsset = $this->register(2);
        $newAsset = $this->register(1);
        $url = route('office-head.office-assets', ['item' => $this->receipt->purchase_request_item_id, 'registration' => $newAsset->registration_token]);
        $response = $this->get($url)->assertOk()->assertSee($newAsset->reference)->assertDontSee($oldAsset->reference);
        $this->assertEquals(1, $response->viewData('assets')->total());
        $this->get('/office-assets/'.$newAsset->id)->assertOk()->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('data-asset-nav="register"  class="active" aria-current="page"', false)
            ->assertDontSee('data-asset-nav="detail"', false);
    }

    public function test_registers_only_received_units_and_prevents_duplicate_submission(): void
    {
        $token = (string) Str::uuid();
        $payload = ['quantity' => 3, 'equipment_confirmed' => 1, 'submission_token' => $token];
        $this->post('/office-assets/receipts/'.$this->receipt->id.'/register', $payload)->assertSessionHasNoErrors()->assertRedirect(route('office-head.office-assets', ['item' => $this->receipt->purchase_request_item_id, 'registration' => $token]));
        $this->assertDatabaseCount('office_assets', 3);
        $this->post('/office-assets/receipts/'.$this->receipt->id.'/register', $payload)->assertSessionHasErrors('submission_token');
        $this->post('/office-assets/receipts/'.$this->receipt->id.'/register', array_replace($payload, ['quantity' => 1, 'submission_token' => (string) Str::uuid()]))->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('office_assets', 3);
        $this->assertEquals(3, AuditLog::where('action', 'asset_registered')->count());
        $this->assertEquals(3, $this->receipt->fresh()->quantity);
        $this->assertEquals('paid', $this->receipt->purchaseOrder->status);
    }

    public function test_rejects_fractional_receipts_non_equipment_and_inactive_orders(): void
    {
        $payload = ['quantity' => 1, 'submission_token' => (string) Str::uuid()];
        $url = '/office-assets/receipts/'.$this->receipt->id.'/register';
        $this->post($url, $payload)->assertSessionHasErrors('equipment_confirmed');
        $this->receipt->update(['quantity' => 1.5]);
        $this->post($url, $payload + ['equipment_confirmed' => 1])->assertSessionHasErrors('quantity');
        $this->receipt->update(['quantity' => 3]);
        $this->receipt->purchaseOrder->update(['signatory_stage' => 'at_procurement']);
        $this->post($url, $payload + ['equipment_confirmed' => 1])->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('office_assets', 0);
    }

    public function test_allocation_usage_and_identity_are_independent_and_audited(): void
    {
        $asset = $this->register();
        $this->updateAsset($asset, $this->allocation(['usage_status' => 'not_in_use', 'usage_started_on' => null]))->assertSessionHasNoErrors();
        $this->assertEquals('not_in_use', $asset->fresh()->usage_status);
        $this->updateAsset($asset, $this->allocation())->assertSessionHasNoErrors();
        $this->updateAsset($asset, ['section' => 'identity', 'serial_number' => 'SN-TEST', 'property_number' => 'PROPERTY-TEST'])->assertSessionHasNoErrors();
        $this->assertEquals('in_use', $asset->fresh()->usage_status);
        $this->assertEquals('SN-TEST', $asset->fresh()->serial_number);
        $this->assertEquals(4, AuditLog::where('auditable_id', $asset->id)->where('auditable_type', OfficeAsset::class)->count());
        $this->get('/office-assets/'.$asset->id)->assertOk()->assertSee('Employee A')->assertSee('Asset history')->assertSee('Not Yet In Use', false);
    }

    public function test_dates_assignment_and_stale_versions_are_validated(): void
    {
        $asset = $this->register();
        $this->updateAsset($asset, $this->allocation(['allocation' => 'unassigned']))->assertSessionHasErrors('allocation');
        $this->updateAsset($asset, $this->allocation(['assigned_on' => '2026-09-17']))->assertSessionHasErrors('assigned_on');
        $this->updateAsset($asset, $this->allocation(['usage_started_on' => '2026-09-18']))->assertSessionHasErrors('usage_started_on');
        $this->updateAsset($asset, $this->allocation(['assigned_on' => '2026-10-07']))->assertSessionHasErrors('assigned_on');
        $this->updateAsset($asset, $this->allocation())->assertSessionHasNoErrors();
        $this->post('/office-assets/update', $this->allocation() + ['asset_ids' => [$asset->id], 'versions' => [$asset->id => 1], 'reason' => 'Stale tab'])->assertSessionHasErrors('versions');
    }

    public function test_warranty_status_boundaries_duration_and_private_proof(): void
    {
        $asset = $this->register();
        $this->assertEquals('Not Yet Recorded', $asset->warrantyStatus());
        $this->updateAsset($asset, ['section' => 'warranty', 'warranty_coverage' => 'covered', 'warranty_start' => '2026-09-18', 'warranty_months' => 12, 'warranty_proof' => UploadedFile::fake()->create('warranty.pdf', 10, 'application/pdf')])->assertSessionHasNoErrors();
        $asset->refresh();
        $this->assertEquals('2027-09-18', $asset->warranty_end->toDateString());
        $this->assertEquals('Under Warranty', $asset->warrantyStatus());
        Storage::disk('local')->assertExists($asset->warranty_path);
        $this->get('/office-assets/'.$asset->id.'/proof')->assertOk();
        foreach (['2026-11-06' => 'Under Warranty', '2026-11-05' => 'Expiring Soon', '2026-10-06' => 'Expiring Soon', '2026-10-05' => 'Warranty Expired'] as $end => $status) {
            $asset->update(['warranty_end' => $end]);
            $this->assertEquals($status, $asset->fresh()->warrantyStatus());
        }
        $asset->update(['warranty_start' => '2026-10-07', 'warranty_end' => '2027-10-07']);
        $this->assertEquals('Not Yet Active', $asset->fresh()->warrantyStatus());
        $this->updateAsset($asset, ['section' => 'warranty', 'warranty_coverage' => 'none'])->assertSessionHasNoErrors();
        $this->assertEquals('No Warranty', $asset->fresh()->warrantyStatus());
        $this->assertNull($asset->fresh()->warranty_end);
    }

    public function test_warranty_rejects_missing_or_reversed_dates_and_invalid_files(): void
    {
        $asset = $this->register();
        $this->updateAsset($asset, ['section' => 'warranty', 'warranty_coverage' => 'covered'])->assertSessionHasErrors('warranty_start');
        $this->updateAsset($asset, ['section' => 'warranty', 'warranty_coverage' => 'covered', 'warranty_start' => '2026-09-18'])->assertSessionHasErrors('warranty_end');
        $this->updateAsset($asset, ['section' => 'warranty', 'warranty_coverage' => 'covered', 'warranty_start' => '2026-09-18', 'warranty_end' => '2026-09-17'])->assertSessionHasErrors('warranty_end');
        $this->updateAsset($asset, ['section' => 'warranty', 'warranty_coverage' => 'none', 'warranty_proof' => UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream')])->assertSessionHasErrors('warranty_proof');
    }

    public function test_bulk_update_is_atomic_and_checks_every_office_and_version(): void
    {
        $this->register(3);
        $records = OfficeAsset::all();
        $payload = $this->allocation() + ['asset_ids' => $records->pluck('id')->all(), 'versions' => $records->pluck('version', 'id')->all(), 'reason' => 'Bulk assignment'];
        $this->post('/office-assets/update', $payload)->assertSessionHasNoErrors();
        $this->assertEquals(3, OfficeAsset::where('usage_status', 'in_use')->count());
        $other = Office::create(['campus_id' => $this->office->campus_id, 'code' => 'OTHER', 'name' => 'Other', 'office_type' => 'academic']);
        $records->last()->update(['office_id' => $other->id]);
        $payload['versions'] = OfficeAsset::pluck('version', 'id')->all();
        $payload['location'] = 'Unauthorized bulk change';
        $this->post('/office-assets/update', $payload)->assertForbidden();
        $this->assertEquals(0, OfficeAsset::where('location', 'Unauthorized bulk change')->count());
    }

    public function test_other_offices_and_read_only_roles_cannot_mutate_or_download_private_proof(): void
    {
        $asset = $this->register();
        $other = Office::create(['campus_id' => $this->office->campus_id, 'code' => 'OTHER', 'name' => 'Other', 'office_type' => 'academic']);
        $this->actingAs($this->user('Office Head / Dean', $other));
        $this->get('/office-assets/'.$asset->id)->assertNotFound();
        $this->get('/office-assets/'.$asset->id.'/proof')->assertForbidden();
        $this->updateAsset($asset, $this->allocation())->assertForbidden();
        $this->post('/office-assets/receipts/'.$this->receipt->id.'/register', ['quantity' => 1])->assertForbidden();
        foreach (['Procurement Office', 'Chancellor', 'Vice Chancellor', 'System Administrator', 'Accounting Office'] as $role) {
            $this->actingAs($this->user($role, null, ['vc_type' => 'vcaa']));
            $this->updateAsset($asset, $this->allocation())->assertForbidden();
        }
    }

    public function test_asset_pages_keep_prior_years_visible_and_filter_by_acquisition_year(): void
    {
        $asset = $this->register();
        $this->get('/office-head/office-assets?year=2027')->assertOk()->assertSee($asset->reference);
        $this->get('/office-head/office-assets?year=2027&acquisition_year=2027')->assertOk()->assertDontSee($asset->reference);
        $this->get('/office-head/office-assets?search=not-a-match')->assertOk()->assertDontSee($asset->reference);
        $this->get('/office-head/office-assets?warranty=Not+Yet+Recorded')->assertOk()->assertSee($asset->reference);
        $this->get('/office-head/purchase-requests?year=2026')->assertOk()->assertSee('Allocation &amp; Usage', false)->assertSee('Register Received Units');
        foreach (['Procurement Office' => 'procurement-office', 'Chancellor' => 'chancellor', 'Vice Chancellor' => 'vice-chancellor'] as $role => $prefix) {
            $this->actingAs($this->user($role, null, ['vc_type' => 'vcaa']));
            $this->get('/'.$prefix.'/office-assets?year=2027')->assertOk()->assertSee($asset->reference)->assertDontSee('<form data-asset-bulk', false);
            $this->get('/office-assets/'.$asset->id)->assertOk()->assertDontSee('Update this unit');
        }
        $this->actingAs($this->user('Vice Chancellor', null, ['vc_type' => 'vcaf']));
        $this->get('/vice-chancellor/office-assets')->assertOk()->assertDontSee($asset->reference);
        $this->get('/office-assets/'.$asset->id)->assertNotFound();
    }

    public function test_receipt_corrections_preserve_registered_quantities_and_dates(): void
    {
        $asset = $this->register(3);
        $this->updateAsset($asset, $this->allocation())->assertSessionHasNoErrors();
        $payload = ['arrival_date' => '2026-09-18', 'quantity' => 2, 'received_by_name' => 'Receiver', 'correction_reason' => 'Correction'];
        $this->put('/item-receiving/receipt/'.$this->receipt->id, $payload)->assertSessionHasErrors('quantity');
        $this->put('/item-receiving/receipt/'.$this->receipt->id, array_replace($payload, ['quantity' => 3, 'arrival_date' => '2026-09-20']))->assertSessionHasErrors('arrival_date');
        $this->put('/item-receiving/receipt/'.$this->receipt->id, array_replace($payload, ['quantity' => 3]))->assertSessionHasNoErrors();
    }

    public function test_cross_office_transfer_requires_acceptance_and_preserves_origin_and_warranty(): void
    {
        $asset = $this->register();
        $other = Office::create(['campus_id' => $this->office->campus_id, 'code' => 'COE', 'name' => 'Engineering', 'office_type' => 'academic']);
        $otherHead = $this->user('Office Head / Dean', $other);
        $this->updateAsset($asset, $this->allocation())->assertSessionHasNoErrors();
        $asset->refresh();
        $this->post('/office-assets/'.$asset->id.'/transfer', ['to_office_id' => $other->id, 'version' => $asset->version, 'reason' => 'Office reassignment'])->assertSessionHasNoErrors();
        $transfer = OfficeAssetTransfer::firstOrFail();
        $this->assertEquals($this->office->id, $asset->fresh()->office_id);
        $this->updateAsset($asset, $this->allocation())->assertSessionHasErrors('asset_ids');
        $this->post('/office-assets/transfers/'.$transfer->id.'/resolve', ['decision' => 'accepted', 'reason' => 'Wrong office'])->assertForbidden();
        $this->actingAs($otherHead);
        $this->get('/office-head/office-assets')->assertOk()->assertSee('Incoming office transfers')->assertSee($asset->reference);
        $this->post('/office-assets/transfers/'.$transfer->id.'/resolve', ['decision' => 'accepted', 'reason' => 'Physically received'])->assertSessionHasNoErrors();
        $this->assertEquals($other->id, $asset->fresh()->office_id);
        $this->assertNull($asset->fresh()->assigned_on);
        $this->assertEquals('not_in_use', $asset->fresh()->usage_status);
        $this->assertEquals($this->receipt->id, $asset->fresh()->item_receipt_id);
        $this->get('/office-assets/'.$asset->id)->assertOk()->assertSee('Physically received');
        $this->post('/office-assets/transfers/'.$transfer->id.'/resolve', ['decision' => 'accepted', 'reason' => 'Duplicate'])->assertSessionHasErrors('decision');
        $this->actingAs($this->head);
        $this->get('/office-assets/'.$asset->id)->assertNotFound();
    }

    public function test_bulk_warranty_and_unique_property_numbers(): void
    {
        $asset = $this->register(2);
        $records = OfficeAsset::all();
        $this->post('/office-assets/update', [
            'section' => 'warranty', 'asset_ids' => $records->pluck('id')->all(), 'versions' => $records->pluck('version', 'id')->all(),
            'reason' => 'Supplier certificate', 'warranty_coverage' => 'covered', 'warranty_start' => '2026-09-18', 'warranty_end' => '2027-09-18',
        ])->assertSessionHasNoErrors();
        $this->assertEquals(2, OfficeAsset::whereDate('warranty_end', '2027-09-18')->count());
        $this->updateAsset($asset, ['section' => 'identity', 'property_number' => 'PROPERTY-UNIQUE'])->assertSessionHasNoErrors();
        $this->updateAsset($records->first(), ['section' => 'identity', 'property_number' => 'PROPERTY-UNIQUE'])->assertSessionHasErrors('property_number');
        $this->get('/office-head/office-assets?warranty=Under+Warranty')->assertOk()->assertSee($asset->reference);
        $this->get('/office-head/office-assets?warranty=Warranty+Expired')->assertOk()->assertDontSee($asset->reference);
    }

    public function test_asset_updates_continue_in_locked_year_without_rewriting_report_snapshot(): void
    {
        $admin = $this->user('System Administrator');
        $this->actingAs($admin);
        $this->post('/fiscal-years/2026/finalize', ['reason' => 'Archive before asset allocation'])->assertSessionHasNoErrors();
        $before = $this->get('/procurement-office/procurement-reports/export?year=2026')->assertOk()->streamedContent();
        $this->actingAs($this->head);
        $asset = $this->register();
        $this->updateAsset($asset, $this->allocation())->assertSessionHasNoErrors();
        $this->get('/office-head/office-assets?year=2027')->assertOk()->assertSee($asset->reference);
        $this->actingAs($admin);
        $this->assertSame($before, $this->get('/procurement-office/procurement-reports/export?year=2026')->assertOk()->streamedContent());
    }

    public function test_reject_and_cancel_leave_ownership_unchanged(): void
    {
        $asset = $this->register();
        $other = Office::create(['campus_id' => $this->office->campus_id, 'code' => 'COE', 'name' => 'Engineering', 'office_type' => 'academic']);
        $otherHead = $this->user('Office Head / Dean', $other);
        foreach (['rejected', 'cancelled'] as $decision) {
            $this->actingAs($this->head);
            $this->post('/office-assets/'.$asset->id.'/transfer', ['to_office_id' => $other->id, 'version' => $asset->fresh()->version, 'reason' => 'Transfer request'])->assertSessionHasNoErrors();
            $transfer = OfficeAssetTransfer::latest('id')->first();
            $this->actingAs($decision === 'rejected' ? $otherHead : $this->head);
            $this->post('/office-assets/transfers/'.$transfer->id.'/resolve', ['decision' => $decision, 'reason' => 'Not proceeding'])->assertSessionHasNoErrors();
            $this->assertEquals($this->office->id, $asset->fresh()->office_id);
        }
    }
}
