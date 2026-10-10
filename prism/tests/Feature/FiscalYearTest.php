<?php

namespace Tests\Feature;

use App\Models\{AbstractOfCanvass, BudgetProposal, Campus, DocumentUpload, FiscalYear, Office, PurchaseOrder, PurchaseRequest, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FiscalYearTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Office $office;

    protected function setUp(): void
    {
        parent::setUp();
        // Production dashboards use MySQL's MONTH(); keep the isolated SQLite
        // smoke tests compatible without changing those existing queries.
        DB::connection()->getPdo()->sqliteCreateFunction('MONTH', fn ($date) => $date ? (int) date('n', strtotime($date)) : null, 1);
        $campus = Campus::create(['code' => 'TEST', 'name' => 'Test Campus']);
        $this->office = Office::create(['campus_id' => $campus->id, 'code' => 'TEST', 'name' => 'Test Office', 'office_type' => 'academic']);
        $this->admin = User::factory()->create(['office_id' => $this->office->id]);
        $role = Role::create(['code' => 'system-administrator', 'name' => 'System Administrator']);
        $this->admin->roles()->attach($role);
        foreach ([2026, 2027] as $year) FiscalYear::firstOrCreate(['year' => $year]);
        $this->actingAs($this->admin);
    }

    private function proposal(int $year): BudgetProposal
    {
        $proposal = BudgetProposal::create(['office_id' => $this->office->id, 'code' => 'TEST-'.$year, 'title' => 'Planning '.$year, 'fiscal_year' => $year, 'status' => 'approved']);
        $proposal->items()->create(['name' => 'Unique item '.$year, 'quantity' => 1, 'unit' => 'unit', 'estimated_unit_cost' => 100, 'estimated_total_cost' => 100, 'target_quarter' => 'Q1', 'status' => 'approved']);
        return $proposal;
    }

    private function pr(BudgetProposal $proposal): PurchaseRequest
    {
        return PurchaseRequest::create(['office_id' => $this->office->id, 'budget_proposal_id' => $proposal->id, 'number' => 'PR-'.$proposal->fiscal_year, 'title' => 'Request '.$proposal->fiscal_year, 'fiscal_year' => $proposal->fiscal_year, 'status' => 'submitted', 'signatory_stage' => 'at_office_head']);
    }

    public function test_selector_separates_reports_and_csv_by_year(): void
    {
        $this->proposal(2026);
        $this->proposal(2027);
        foreach ([2026 => 2027, 2027 => 2026] as $year => $other) {
            $response = $this->get('/procurement-office/procurement-reports?year='.$year)->assertOk();
            $this->assertSame(['Unique item '.$year], array_column($response->viewData('ppmpValidationRows'), 'item'));
            $csv = $this->get('/procurement-office/procurement-reports/export?year='.$year)->assertOk()->streamedContent();
            $this->assertStringContainsString('Unique item '.$year, $csv);
            $this->assertStringNotContainsString('Unique item '.$other, $csv);
        }
        $this->get('/fiscal-years')->assertOk()->assertSee('2026');
        $this->get('/procurement-office/procurement-reports?year=9999')->assertStatus(422);
    }

    public function test_finalized_archive_is_immutable_and_reopening_preserves_versions(): void
    {
        $proposal = $this->proposal(2026);
        $pr = $this->pr($proposal);
        $this->post('/fiscal-years/2026/finalize', ['reason' => 'Test year close'])->assertRedirect();
        $this->assertDatabaseHas('fiscal_years', ['year' => 2026, 'status' => 'locked']);
        $url = '/procurement-office/procurement-reports/export?year=2026';
        $before = $this->get($url)->assertOk()->streamedContent();
        $this->get('/procurement-office/procurement-reports?year=2026')->assertOk();
        $this->get('/chancellor/procurement-reports?year=2026&quarter=Q1')->assertOk();
        $pr->update(['status' => 'approved']);
        $after = $this->get($url)->assertOk()->streamedContent();
        $this->assertSame($before, $after);
        $this->post('/fiscal-years/2026/reopen', ['reason' => 'Approved correction'])->assertRedirect();
        $proposal->items()->first()->update(['name' => 'Corrected item']);
        $this->assertSame($before, $this->get($url.'&version=1')->assertOk()->streamedContent());
        $this->post('/fiscal-years/2026/finalize', ['reason' => 'Finalize correction'])->assertRedirect();
        $this->assertDatabaseCount('procurement_report_snapshots', 2);
        $this->assertStringContainsString('Corrected item', $this->get($url)->assertOk()->streamedContent());
    }

    public function test_locked_planning_cannot_be_edited_deleted_or_moved(): void
    {
        $proposal = $this->proposal(2026);
        $item = $proposal->items()->first();
        FiscalYear::find(2026)->update(['status' => 'locked']);
        foreach ([fn () => $proposal->update(['title' => 'Changed']), fn () => $proposal->delete(), fn () => $item->update(['name' => 'Changed']), fn () => $item->delete()] as $mutation) {
            try { $mutation(); $this->fail('Locked planning mutation succeeded.'); } catch (ValidationException $e) { $this->assertArrayHasKey('fiscal_year', $e->errors()); }
        }
        $this->postJson('/office-head/budget-proposal/'.$proposal->id.'/title?year=2027', ['title' => 'Bypass'])->assertStatus(422);
        FiscalYear::find(2026)->update(['status' => 'open']);
        $this->postJson('/office-head/budget-proposal/'.$proposal->id.'/title?year=2027', ['title' => 'Wrong year'])->assertStatus(422);
        $this->expectException(ValidationException::class);
        $proposal->refresh()->update(['fiscal_year' => 2027]);
    }

    public function test_pending_signatures_remain_visible_across_years(): void
    {
        $this->pr($this->proposal(2026));
        $this->pr($this->proposal(2027));
        $this->get('/office-head/for-my-signature?year=2027')->assertOk()->assertSee('PR-2026')->assertSee('PR-2027')->assertSee('FY 2026');
    }

    public function test_only_admin_can_finalize_and_reason_is_required(): void
    {
        $this->postJson('/fiscal-years/2026/finalize', [])->assertUnprocessable();
        $this->actingAs(User::factory()->create());
        $this->post('/fiscal-years/2026/finalize', ['reason' => 'Not authorized'])->assertForbidden();
        $this->get('/fiscal-years')->assertForbidden();
        $this->assertDatabaseCount('procurement_report_snapshots', 0);
    }

    public function test_payments_and_operational_attachments_continue_in_locked_year(): void
    {
        $proposal = $this->proposal(2026);
        $pr = $this->pr($proposal);
        $aoc = AbstractOfCanvass::create(['purchase_request_id' => $pr->id, 'code' => 'AOC-2026']);
        $po = PurchaseOrder::create(['abstract_of_canvass_id' => $aoc->id, 'po_number' => 'PO-2026', 'supplier_name' => 'Test Supplier', 'status' => 'processing_payment', 'signatory_stage' => 'fully_signed']);
        FiscalYear::find(2026)->update(['status' => 'locked']);
        $this->get('/cashier?year=2027')->assertOk()->assertSee('PO-2026')->assertSee('FY 2026');
        $this->get('/accounting-office?year=2027')->assertOk()->assertSee('PO-2026')->assertSee('FY 2026');
        DocumentUpload::create(['attachable_type' => PurchaseRequest::class, 'attachable_id' => $pr->id, 'document_type' => 'canvass_quotation', 'original_filename' => 'test.pdf', 'file_path' => 'test.pdf']);
        $po->update(['status' => 'paid']);
        $this->assertSame('paid', $po->fresh()->status);
        $this->expectException(ValidationException::class);
        DocumentUpload::create(['attachable_type' => BudgetProposal::class, 'attachable_id' => $proposal->id, 'document_type' => 'planning', 'original_filename' => 'test.pdf', 'file_path' => 'test.pdf']);
    }

    public function test_role_pages_render_with_selected_year(): void
    {
        $this->proposal(2026);
        foreach (['office-head', 'office-head/budget-proposal', 'office-head/my-proposals', 'finance-office', 'finance-office/proposal-review', 'finance-office/budget-utilization-report', 'procurement-office', 'procurement-office/annual-procurement-plan', 'chancellor', 'chancellor/procurement-reports', 'vice-chancellor', 'vice-chancellor/division-performance-report'] as $path) {
            $this->get('/'.$path.'?year=2026')->assertOk()->assertSee($path === 'office-head' ? 'pdYearSelect' : 'globalFiscalYear');
        }
    }

    public function test_dashboard_has_one_picker_and_overall_combines_years(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::create(2027, 10, 10));
        $this->proposal(2026);
        $this->proposal(2027);
        FiscalYear::query()->update(['is_active' => false]);
        FiscalYear::find(2027)->update(['is_active' => true]);
        FiscalYear::find(2026)->update(['status' => 'locked']);
        FiscalYear::create(['year' => 2028]);

        $response = $this->get('/office-head?year=2026')->assertOk()
            ->assertSee('pdYearSelect')->assertDontSee('globalFiscalYear')
            ->assertSee('FY 2026 • Locked')->assertSee('FY 2027 • Active')
            ->assertSee('planningFiscalYear')
            ->assertSee(route('office-head.purchase-requests', ['year' => 2026]), false);
        $this->assertSame([2027, 2026], $response->viewData('availableYears')->pluck('year')->all());
        $this->assertSame(1, $response->viewData('summary')['totalProposedItems']);
        $this->assertSame(2026, $response->viewData('selectedYear'));
        $this->get('/office-head/budget-proposal')->assertOk()->assertSee('globalFiscalYear')
            ->assertViewHas('proposalForm', fn ($form) => $form['fiscalYear'] === 2026);

        $response = $this->get('/office-head?year=all')->assertOk();
        $this->assertNull($response->viewData('selectedYear'));
        $this->assertSame(2, $response->viewData('summary')['totalProposedItems']);
        $this->assertEquals(200, $response->viewData('summary')['totalProposedBudget']);
        $this->assertSame(2027, $response->viewData('planningYear'));
        $this->get('/office-head/budget-proposal')->assertOk()
            ->assertViewHas('proposalForm', fn ($form) => $form['fiscalYear'] === 2027);
    }

    public function test_advance_planning_draft_is_reachable_without_changing_current_year(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::create(2026, 10, 10));
        FiscalYear::query()->update(['is_active' => false]);
        FiscalYear::find(2026)->update(['is_active' => true]);
        $old = $this->proposal(2026);
        $response = $this->post('/office-head/budget-proposal/new', ['planning_year' => 2027])->assertRedirect();
        $draft = BudgetProposal::where('office_id', $this->office->id)->where('fiscal_year', 2027)->firstOrFail();
        $this->assertSame('draft', $draft->status);
        $this->get($response->headers->get('Location'))->assertOk()
            ->assertViewHas('selectedProposalId', $draft->id)
            ->assertViewHas('isReadOnly', false);
        $this->get('/office-head')->assertOk()->assertViewHas('selectedYear', 2026);
        $this->assertSame(2026, $old->fresh()->fiscal_year);
        $this->assertTrue(FiscalYear::find(2026)->is_active);
        $response = $this->get('/office-head/my-proposals?year=2026')->assertOk()
            ->assertSee('Advance Planning')->assertSee('Continue Draft')
            ->assertSee(route('office-head.budget-proposal', ['proposal' => $draft->id, 'year' => 2027]));
        $this->assertCount(2, $response->viewData('proposals'));
        $response = $this->get('/office-head?year=2026')->assertOk();
        $this->assertSame(1, $response->viewData('summary')['totalProposedItems']);
        $this->assertNotContains(2027, $response->viewData('availableYears')->pluck('year'));
        $this->get('/office-head/budget-proposal?year=2026&proposal='.$old->id)->assertOk()->assertSee('Add Supplemental PPMP');

        FiscalYear::find(2027)->update(['status' => 'locked']);
        $this->post('/office-head/budget-proposal/new', ['planning_year' => 2027])->assertSessionHasErrors('fiscal_year');
        FiscalYear::create(['year' => 2028]);
        $this->post('/office-head/budget-proposal/new', ['planning_year' => 2028])->assertSessionHasErrors('planning_year');
        $this->post('/office-head/budget-proposal/new', ['planning_year' => 9999])->assertSessionHasErrors('planning_year');
        $this->assertDatabaseCount('budget_proposals', 2);
    }

    public function test_new_planning_uses_selected_year_without_changing_prior_records(): void
    {
        $old = $this->proposal(2026);
        $this->get('/office-head/budget-proposal/new?year=2027')->assertRedirect();
        $this->assertDatabaseHas('budget_proposals', ['fiscal_year' => 2027, 'office_id' => $this->office->id, 'status' => 'draft']);
        $this->assertSame(2026, $old->fresh()->fiscal_year);
        $this->assertSame('Planning 2026', $old->fresh()->title);
    }

    public function test_year_administration_and_archive_integrity(): void
    {
        $this->post('/fiscal-years', ['year' => 2028])->assertRedirect();
        $this->post('/fiscal-years/2028/activate', ['reason' => 'Begin new planning cycle'])->assertRedirect();
        $this->assertSame([2028], FiscalYear::where('is_active', true)->pluck('year')->all());
        $this->proposal(2026);
        $this->post('/fiscal-years/2026/finalize', ['reason' => 'Archive integrity test'])->assertRedirect();
        DB::table('procurement_report_snapshots')->where('fiscal_year', 2026)->update(['payload' => '{}']);
        $this->get('/procurement-office/procurement-reports?year=2026')->assertStatus(409);
    }
}
