<?php

namespace Tests\Feature;

use App\Models\{AbstractOfCanvass, AuditLog, Campus, FiscalYear, ItemReceipt, Office, PurchaseOrder, PurchaseRequest, PurchaseRequestItem, Role, User};
use App\Services\ItemReceivingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Tests\TestCase;

class ItemReceivingTest extends TestCase
{
    use RefreshDatabase;

    private Office $office;
    private User $head;
    private User $procurement;
    private PurchaseRequest $pr;
    private PurchaseRequestItem $item;
    private PurchaseOrder $po;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 30)->startOfDay());
        DB::connection()->getPdo()->sqliteCreateFunction('MONTH', fn ($date) => $date ? (int) date('n', strtotime($date)) : null, 1);
        Storage::fake('local');
        $campus = Campus::create(['code' => 'ARR', 'name' => 'Receiving Test Campus']);
        $this->office = Office::create(['campus_id' => $campus->id, 'code' => 'CICS', 'name' => 'Computing', 'office_type' => 'academic']);
        FiscalYear::firstOrCreate(['year' => 2026]);
        FiscalYear::firstOrCreate(['year' => 2027]);
        $this->head = $this->user('Office Head / Dean');
        $this->procurement = $this->user('Procurement Office');
        $this->pr = PurchaseRequest::create(['office_id' => $this->office->id, 'number' => 'PR-2026-Q3-001', 'title' => 'Laptop request', 'fiscal_year' => 2026, 'status' => 'submitted', 'signatory_stage' => 'fully_signed', 'file_path' => 'test.pdf', 'canvassing_stage' => 'completed']);
        $this->item = $this->pr->items()->create(['name' => 'Laptop', 'quantity' => 5, 'unit' => 'unit', 'estimated_unit_cost' => 100, 'estimated_total_cost' => 500]);
        $aoc = AbstractOfCanvass::create(['purchase_request_id' => $this->pr->id, 'code' => 'AOC-001', 'signatory_stage' => 'fully_signed']);
        $this->po = PurchaseOrder::create(['abstract_of_canvass_id' => $aoc->id, 'po_number' => 'PO-001', 'supplier_name' => 'Test Supplier', 'signatory_stage' => 'fully_signed', 'status' => 'paid', 'procured_on' => '2026-09-10', 'expected_delivery_date' => '2026-09-15']);
        $this->actingAs($this->head);
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['office_id' => $this->office->id]);
        $user->roles()->attach(Role::firstOrCreate(['name' => $role], ['code' => Str::slug($role)]));
        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + ['arrival_date' => '2026-09-18', 'quantity' => 3, 'received_by_name' => 'Office Receiver', 'submission_token' => (string) Str::uuid()];
    }

    private function url(): string
    {
        return '/item-receiving/item/'.$this->item->id.'/receipts?year=2026';
    }

    private function row(): array
    {
        return app(ItemReceivingService::class)->row($this->item->fresh(), $this->head);
    }

    public function test_partial_and_full_receipts_continue_after_payment_and_calculate_dates(): void
    {
        $this->assertSame('Awaiting Receipt', $this->row()['receivingStatus']);
        $this->post($this->url(), $this->payload())->assertRedirect();
        $partial = $this->row();
        $this->assertSame('Partially Received', $partial['receivingStatus']);
        $this->assertNull($partial['arrivalDate']);
        $this->assertSame('2026-09-18', $partial['lastArrivalDate']);
        $this->assertSame(15, $partial['daysDelayed']);
        $this->post($this->url(), $this->payload(['quantity' => 2]))->assertRedirect();
        $full = $this->row();
        $this->assertSame('Fully Received', $full['receivingStatus']);
        $this->assertSame('2026-09-18', $full['arrivalDate']);
        $this->assertSame(8, $full['daysToReceive']);
        $this->assertSame(3, $full['daysDelayed']);
        $this->assertSame('paid', $this->po->fresh()->status);
        $this->assertDatabaseCount('item_receipts', 2);
        $this->assertSame(2, AuditLog::where('action', 'item_receipt_recorded')->count());
    }

    public function test_receiving_rejects_other_offices_and_read_only_roles(): void
    {
        $other = Office::create(['campus_id' => $this->office->campus_id, 'code' => 'OTHER', 'name' => 'Other', 'office_type' => 'academic']);
        $this->actingAs($this->user('Office Head / Dean', ['office_id' => $other->id]))->postJson($this->url(), $this->payload())->assertForbidden();
        foreach (['Procurement Office', 'Chancellor', 'Vice Chancellor', 'Accounting Office', 'Cashier'] as $role) {
            $this->actingAs($this->user($role))->postJson($this->url(), $this->payload())->assertForbidden();
        }
        $this->assertDatabaseCount('item_receipts', 0);
    }

    public function test_quantity_limits_precision_dates_and_duplicate_submission_are_enforced(): void
    {
        foreach ([['quantity' => 0], ['quantity' => -1], ['quantity' => 6], ['quantity' => 0.001], ['arrival_date' => '2026-10-01'], ['arrival_date' => '2026-09-09']] as $invalid) {
            $this->postJson($this->url(), $this->payload($invalid))->assertUnprocessable();
        }
        $payload = $this->payload(['quantity' => 3.25]);
        $this->post($this->url(), $payload)->assertRedirect();
        $this->postJson($this->url(), $payload)->assertUnprocessable();
        $this->postJson($this->url(), $this->payload(['quantity' => 2]))->assertUnprocessable();
        $this->post($this->url(), $this->payload(['quantity' => 1.75]))->assertRedirect();
        $this->assertSame('Fully Received', $this->row()['receivingStatus']);
    }

    public function test_draft_and_cancelled_orders_cannot_receive(): void
    {
        $this->po->update(['signatory_stage' => 'draft']);
        $this->postJson($this->url(), $this->payload())->assertUnprocessable();
        $this->po->update(['signatory_stage' => 'fully_signed']);
        $this->pr->update(['status' => 'cancelled']);
        $this->postJson($this->url(), $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('item_receipts', 0);
    }

    public function test_missing_historical_dates_are_not_invented(): void
    {
        $this->po->update(['procured_on' => null, 'expected_delivery_date' => null]);
        $this->assertSame('Arrival not recorded', $this->row()['receivingStatus']);
        $this->post($this->url(), $this->payload(['quantity' => 5]))->assertRedirect();
        $row = $this->row();
        $this->assertNull($row['procuredDate']);
        $this->assertNull($row['daysToReceive']);
        $this->assertNull($row['daysDelayed']);
        $this->assertSame('No target date', $row['delayLabel']);
    }

    public function test_only_procurement_updates_dates_and_cannot_move_procurement_after_receipt(): void
    {
        $url = '/item-receiving/purchase-order/'.$this->po->id.'/dates?year=2026';
        $data = ['procured_on' => '2026-09-11', 'expected_delivery_date' => '2026-09-16', 'correction_reason' => 'Supplier confirmation'];
        $this->postJson($url, $data)->assertForbidden();
        $this->post($this->url(), $this->payload())->assertRedirect();
        $this->actingAs($this->procurement)->post($url, $data)->assertRedirect();
        $this->assertSame('2026-09-11', $this->po->fresh()->procured_on->toDateString());
        $this->postJson($url, ['procured_on' => '2026-09-19', 'correction_reason' => 'Invalid change'])->assertUnprocessable();
        $this->postJson($url, ['procured_on' => '2026-09-10'])->assertUnprocessable();
        $this->postJson($url, ['procured_on' => '2026-09-10', 'expected_delivery_date' => '2026-09-09', 'correction_reason' => 'Invalid target'])->assertUnprocessable();
        $this->assertDatabaseHas('audit_logs', ['action' => 'procurement_dates_updated', 'user_id' => $this->procurement->id]);
    }

    public function test_corrections_preserve_actor_history_and_recalculate_completion(): void
    {
        $this->post($this->url(), $this->payload(['quantity' => 5]))->assertRedirect();
        $receipt = ItemReceipt::firstOrFail();
        $url = '/item-receiving/receipt/'.$receipt->id.'?year=2026';
        $correction = ['arrival_date' => '2026-09-19', 'quantity' => 4, 'received_by_name' => 'Corrected receiver'];
        $this->putJson($url, $correction)->assertUnprocessable();
        $this->put($url, $correction + ['correction_reason' => 'One unit was entered in error'])->assertRedirect();
        $this->assertSame('Partially Received', $this->row()['receivingStatus']);
        $log = AuditLog::where('action', 'item_receipt_corrected')->firstOrFail();
        $this->assertSame(5.0, (float) $log->old_values_json['quantity']);
        $this->assertSame(4.0, (float) $log->new_values_json['quantity']);
        $this->assertSame($this->head->id, $receipt->fresh()->recorded_by_user_id);
        $this->actingAs($this->procurement)->putJson($url, $correction + ['correction_reason' => 'Not allowed'])->assertForbidden();
    }

    public function test_private_attachments_obey_office_and_division_access(): void
    {
        $this->post($this->url(), $this->payload(['attachment' => UploadedFile::fake()->create('delivery.pdf', 12, 'application/pdf')]))->assertRedirect();
        $receipt = ItemReceipt::firstOrFail();
        Storage::disk('local')->assertExists($receipt->attachment_path);
        $url = '/item-receiving/receipt/'.$receipt->id.'/attachment?year=2026';
        $this->get($url)->assertOk();
        $this->actingAs($this->user('Vice Chancellor', ['vc_type' => 'vcaa']))->get($url)->assertOk();
        $this->actingAs($this->user('Vice Chancellor', ['vc_type' => 'vcaf']))->get($url)->assertForbidden();
        $this->actingAs($this->user('Office Head / Dean', ['office_id' => null]))->get($url)->assertForbidden();
    }

    public function test_receiving_continues_in_locked_fiscal_year(): void
    {
        FiscalYear::find(2026)->update(['status' => 'locked']);
        $this->post($this->url(), $this->payload())->assertRedirect();
        $this->assertDatabaseCount('item_receipts', 1);
    }

    public function test_all_role_views_render_receiving_with_correct_actions(): void
    {
        $this->post($this->url(), $this->payload())->assertRedirect();
        $this->get('/office-head/purchase-requests?year=2026')->assertOk()->assertSee('Arrival Date')->assertSee('Record Receipt')->assertSee('2026-09-18');
        $this->actingAs($this->procurement)->get('/procurement-office/purchase-orders?year=2026')->assertOk()->assertSee('Save PO Dates')->assertDontSee('Record Receipt');
        foreach (['Chancellor' => 'chancellor/procurement-reports', 'Vice Chancellor' => 'vice-chancellor/division-procurement-status', 'Accounting Office' => 'accounting-office', 'Cashier' => 'cashier'] as $role => $path) {
            $this->actingAs($this->user($role, ['vc_type' => 'vcaa']))->get('/'.$path.'?year=2026')->assertOk()->assertSee('Partially Received')->assertDontSee('Save PO Dates')->assertDontSee('Record Receipt');
        }
    }

    public function test_reports_and_csv_capture_arrival_and_keep_finalized_versions_immutable(): void
    {
        $this->post($this->url(), $this->payload(['quantity' => 5]))->assertRedirect();
        $admin = $this->user('System Administrator');
        $this->actingAs($admin);
        $url = '/procurement-office/procurement-reports/export?year=2026';
        $csv = $this->get($url)->assertOk()->streamedContent();
        $this->assertStringContainsString('2026-09-18', $csv);
        $this->assertStringContainsString('3 days late', $csv);
        $this->post('/fiscal-years/2026/finalize', ['reason' => 'Receiving report snapshot'])->assertRedirect();
        $before = $this->get($url)->assertOk()->streamedContent();
        $this->actingAs($this->head);
        $this->put('/item-receiving/receipt/'.ItemReceipt::first()->id.'?year=2026', ['arrival_date' => '2026-09-20', 'quantity' => 5, 'received_by_name' => 'Receiver', 'correction_reason' => 'Corrected delivery slip'])->assertRedirect();
        $this->actingAs($admin);
        $this->assertSame($before, $this->get($url)->assertOk()->streamedContent());
        $this->get('/chancellor/procurement-reports?year=2026')->assertOk()->assertSee('2026-09-18');
        $this->assertStringNotContainsString('Laptop', $this->get('/procurement-office/procurement-reports/export?year=2027')->assertOk()->streamedContent());
    }

    public function test_receiving_reports_honor_office_and_quarter_filters(): void
    {
        $this->actingAs($this->user('Chancellor'));
        $this->get('/chancellor/procurement-reports?year=2026&quarter=Q3&office=CICS')->assertOk()->assertViewHas('deliveryRows', fn ($rows) => count($rows) === 1);
        $this->get('/chancellor/procurement-reports?year=2026&quarter=Q1')->assertOk()->assertViewHas('deliveryRows', fn ($rows) => count($rows) === 0);
        $this->get('/chancellor/procurement-reports?year=2026&office=OTHER')->assertOk()->assertViewHas('deliveryRows', fn ($rows) => count($rows) === 0);
    }

    public function test_ajax_receipts_return_current_details_without_redirecting(): void
    {
        $first = $this->postJson($this->url(), $this->payload())
            ->assertOk()->assertJsonPath('message', 'Item receipt recorded.')
            ->assertJsonPath('delivery.receivingStatus', 'Partially Received')
            ->assertJsonPath('delivery.remainingQuantity', 2);
        $this->assertStringContainsString('data-receipt-form', $first->json('detailsHtml'));
        $this->assertStringContainsString('max="2"', $first->json('detailsHtml'));
        $this->postJson($this->url(), $this->payload(['quantity' => 2]))
            ->assertOk()->assertJsonPath('delivery.receivingStatus', 'Fully Received')
            ->assertJsonPath('delivery.arrivalDate', '2026-09-18');
        $receipt = ItemReceipt::firstOrFail();
        $this->putJson('/item-receiving/receipt/'.$receipt->id.'?year=2026', [
            'arrival_date' => '2026-09-19', 'quantity' => 2, 'received_by_name' => 'Receiver',
            'correction_reason' => 'Corrected count',
        ])->assertOk()->assertJsonPath('delivery.receivingStatus', 'Partially Received')
            ->assertJsonPath('delivery.remainingQuantity', 1);
    }
}
