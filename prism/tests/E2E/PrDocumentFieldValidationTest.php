<?php

namespace Tests\E2E;

use App\Models\BudgetProposal;
use App\Models\Office;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\DocumentValidationService;
use App\Services\ItemMatchingService;
use Tests\TestCase;

/**
 * Header-field cross-checks (Office / Fiscal Year / Total Cost / PR Number)
 * that DocumentValidationService::validatePrAgainstPpmp() now runs alongside
 * the existing item-vs-PPMP check — catching "wrong file, wrong PPMP, or
 * already uploaded" rather than a planning disagreement.
 */
class PrDocumentFieldValidationTest extends TestCase
{
    private function step(string $msg): void
    {
        fwrite(STDERR, $msg . PHP_EOL);
    }

    /** An approved PPMP for CICS, FY of "next year" — office/fiscal_year known for comparison. */
    private function approvedPpmp(): BudgetProposal
    {
        $officeHead = User::where('email', 'office.head@prism.test')->firstOrFail();
        $finance    = User::where('email', 'finance.office@prism.test')->firstOrFail();
        $chancellor = User::where('email', 'chancellor@prism.test')->firstOrFail();

        $this->actingAs($officeHead)->postJson(route('office-head.budget-proposal.store-item'), [
            'description' => 'Bond Paper A4', 'unit' => 'ream', 'quantity' => 50,
            'estimatedUnitCost' => 250, 'targetQuarter' => 'Q1',
        ])->assertOk();

        $proposal = BudgetProposal::whereIn('status', ['draft', 'returned'])->latest('id')->firstOrFail();
        $item = $proposal->items()->firstOrFail();

        $this->actingAs($officeHead)->postJson(
            route('office-head.budget-proposal.item-attachment', $item->id),
            ['file' => \Illuminate\Http\UploadedFile::fake()->create('src.pdf', 20, 'application/pdf')]
        )->assertOk();

        $this->actingAs($officeHead)
            ->postJson(route('office-head.budget-proposal.submit'), ['proposal_id' => $proposal->id])->assertOk();
        $this->actingAs($finance)
            ->post(route('finance-office.proposal-review.endorse', $proposal->id), ['remarks' => 'ok']);
        $this->actingAs($chancellor)
            ->postJson(route('chancellor.budget-approval.approve', $proposal->id), ['remarks' => 'ok'])->assertOk();

        return $proposal->fresh(['office']);
    }

    private function service(): DocumentValidationService
    {
        return new DocumentValidationService(app(ItemMatchingService::class));
    }

    public function test_office_mismatch_blocks_even_when_items_match(): void
    {
        $ppmp = $this->approvedPpmp();
        $items = [['name' => 'Bond Paper A4', 'quantity' => 10, 'unit' => 'ream', 'unitCost' => 250]];

        $result = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'officeCode' => 'WRONGOFFICE',
        ]);

        $this->assertSame('failed', $result['verdict']);
        $officeCheck = collect($result['fieldChecks'])->firstWhere('field', 'Office');
        $this->assertFalse($officeCheck['ok']);
        $this->step('FIELD 1  OK - office mismatch blocks even with matching items: ' . $officeCheck['reason']);
    }

    public function test_office_match_is_case_insensitive_and_passes(): void
    {
        $ppmp = $this->approvedPpmp();
        $items = [['name' => 'Bond Paper A4', 'quantity' => 10, 'unit' => 'ream', 'unitCost' => 250]];

        $result = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'officeCode' => strtolower($ppmp->office->code),
        ]);

        $officeCheck = collect($result['fieldChecks'])->firstWhere('field', 'Office');
        $this->assertTrue($officeCheck['ok']);
        $this->assertSame('passed', $result['verdict']);
        $this->step('FIELD 2  OK - office match is case-insensitive (' . strtolower($ppmp->office->code) . ' vs ' . $ppmp->office->code . ')');
    }

    public function test_fiscal_year_mismatch_blocks(): void
    {
        $ppmp = $this->approvedPpmp();
        $items = [['name' => 'Bond Paper A4', 'quantity' => 10, 'unit' => 'ream', 'unitCost' => 250]];

        $result = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'fiscalYear' => $ppmp->fiscal_year + 5,
        ]);

        $this->assertSame('failed', $result['verdict']);
        $fyCheck = collect($result['fieldChecks'])->firstWhere('field', 'Fiscal Year');
        $this->assertFalse($fyCheck['ok']);
        $this->step('FIELD 3  OK - fiscal year mismatch blocks: ' . $fyCheck['reason']);
    }

    public function test_total_cost_is_checked_against_its_own_items_not_the_ppmp_total(): void
    {
        $ppmp = $this->approvedPpmp(); // PPMP total is 50 * 250 = 12,500
        // This PR only draws 10 of the 50 reams — a legitimate partial PR
        // (split-PR workflow) whose own total (2,500) is far below the
        // PPMP's total. That must NOT be flagged.
        $items = [['name' => 'Bond Paper A4', 'quantity' => 10, 'unit' => 'ream', 'unitCost' => 250]];

        $result = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'totalCost' => 2500.00, // matches this PR's own 10 * 250, not the PPMP's 12,500
        ]);

        $totalCheck = collect($result['fieldChecks'])->firstWhere('field', 'Total Cost');
        $this->assertTrue($totalCheck['ok'], 'A partial PR whose own total matches its own items must pass, even though it is far below the PPMP total');
        $this->step('FIELD 4  OK - partial-PR total (2,500) correctly checked against its OWN items, not the PPMP total (12,500)');

        // Now corrupt the printed total so it disagrees with the item rows.
        $bad = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'totalCost' => 9999.00,
        ]);
        $badCheck = collect($bad['fieldChecks'])->firstWhere('field', 'Total Cost');
        $this->assertFalse($badCheck['ok']);
        $this->assertSame('failed', $bad['verdict']);
        $this->step('FIELD 5  OK - printed total disagreeing with its own item rows is caught: ' . $badCheck['reason']);
    }

    public function test_pr_number_already_used_is_flagged_at_review_time(): void
    {
        $ppmp = $this->approvedPpmp();
        $items = [['name' => 'Bond Paper A4', 'quantity' => 10, 'unit' => 'ream', 'unitCost' => 250]];

        $office = Office::first();
        $taken  = PurchaseRequest::create([
            'budget_proposal_id' => $ppmp->id,
            'office_id'          => $office->id,
            'number'             => 'PR-DUPTEST-' . now()->format('His'),
            'title'              => 'Existing PR',
            'fiscal_year'        => $ppmp->fiscal_year,
            'status'             => 'new',
            'signatory_stage'    => 'draft',
            'canvassing_stage'   => 'not_started',
            'uploaded_at'        => now(),
        ]);

        $result = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'prNumber' => $taken->number,
        ]);

        $this->assertSame('failed', $result['verdict']);
        $numCheck = collect($result['fieldChecks'])->firstWhere('field', 'PR Number');
        $this->assertFalse($numCheck['ok']);
        $this->step('FIELD 6  OK - reusing an existing PR number is caught at review time (Step 2), not only at final Create: ' . $numCheck['reason']);

        // A number nobody has used yet must pass.
        $free = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'prNumber' => 'PR-DEFINITELY-UNUSED-' . now()->format('His'),
        ]);
        $freeCheck = collect($free['fieldChecks'])->firstWhere('field', 'PR Number');
        $this->assertTrue($freeCheck['ok']);
        $this->step('FIELD 7  OK - a genuinely unused PR number passes');
    }

    public function test_all_fields_matching_and_items_matching_passes_cleanly(): void
    {
        $ppmp = $this->approvedPpmp();
        $items = [['name' => 'Bond Paper A4', 'quantity' => 50, 'unit' => 'ream', 'unitCost' => 250]];

        $result = $this->service()->validatePrAgainstPpmp($items, $ppmp, null, null, [
            'officeCode' => $ppmp->office->code,
            'fiscalYear' => $ppmp->fiscal_year,
            'totalCost'  => 12500.00,
            'prNumber'   => 'PR-CLEAN-' . now()->format('His'),
        ]);

        $this->assertSame('passed', $result['verdict']);
        $this->assertTrue(collect($result['fieldChecks'])->every(fn ($c) => $c['ok']));
        $this->step('FIELD 8  OK - everything matching (office, FY, total, fresh PR number) + matching items -> clean pass');
    }
}
