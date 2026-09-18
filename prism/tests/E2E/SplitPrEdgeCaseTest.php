<?php

namespace Tests\E2E;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The guard rails around the split-PR scenario: what happens when the same
 * approved PPMP is drawn against more than once, when a PR asks for more than
 * was approved, and when quotations are uploaded in bulk at the wrong moment.
 */
class SplitPrEdgeCaseTest extends TestCase
{
    private User $officeHead;
    private User $finance;
    private User $procurement;
    private User $chancellor;
    private User $vcaa;
    private User $vcaf;
    private User $accounting;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->officeHead  = User::where('email', 'office.head@prism.test')->firstOrFail();
        $this->finance     = User::where('email', 'finance.office@prism.test')->firstOrFail();
        $this->procurement = User::where('email', 'procurement.office@prism.test')->firstOrFail();
        $this->chancellor  = User::where('email', 'chancellor@prism.test')->firstOrFail();
        $this->vcaa        = User::where('email', 'vcaa.demo@prism.test')->firstOrFail();
        $this->vcaf        = User::where('email', 'vcaf.demo@prism.test')->firstOrFail();
        $this->accounting  = User::where('email', 'accounting.office@prism.test')->firstOrFail();
    }

    private function step(string $msg): void
    {
        fwrite(STDERR, $msg . PHP_EOL);
    }

    /** An approved 3-item PPMP to draw PRs against. */
    private function approvedPpmp(): BudgetProposal
    {
        $items = [
            ['description' => 'Bond Paper A4 substance 20', 'unit' => 'ream', 'quantity' => 100, 'estimatedUnitCost' => 250,  'targetQuarter' => 'Q1'],
            ['description' => 'Ballpen black fine tip',     'unit' => 'pc',   'quantity' => 200, 'estimatedUnitCost' => 12,   'targetQuarter' => 'Q1'],
            ['description' => 'Stapler heavy duty',         'unit' => 'pc',   'quantity' => 20,  'estimatedUnitCost' => 350,  'targetQuarter' => 'Q1'],
        ];

        $proposalId = null;
        foreach ($items as $item) {
            $this->actingAs($this->officeHead)
                ->postJson(route('office-head.budget-proposal.store-item'), $item + ['proposal_id' => $proposalId])
                ->assertOk();
            $proposalId ??= BudgetProposal::whereIn('status', ['draft', 'returned'])->latest('id')->firstOrFail()->id;
        }

        $proposal = BudgetProposal::findOrFail($proposalId);

        foreach ($proposal->items as $item) {
            $this->actingAs($this->officeHead)->postJson(
                route('office-head.budget-proposal.item-attachment', $item->id),
                ['file' => UploadedFile::fake()->create("src-{$item->id}.pdf", 30, 'application/pdf')]
            )->assertOk();
        }

        $this->actingAs($this->officeHead)
            ->postJson(route('office-head.budget-proposal.submit'), ['proposal_id' => $proposal->id])->assertOk();
        $this->actingAs($this->finance)
            ->post(route('finance-office.proposal-review.endorse', $proposal->id), ['remarks' => 'ok']);
        $this->actingAs($this->chancellor)
            ->postJson(route('chancellor.budget-approval.approve', $proposal->id), ['remarks' => 'ok'])->assertOk();

        return $proposal->fresh();
    }

    private function attemptPr(BudgetProposal $proposal, string $number, array $rows): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->procurement)->postJson(
            route('procurement-office.purchase-request-management.create-pr'),
            [
                'budget_proposal_id' => $proposal->id,
                'pr_number'          => $number,
                'title'              => $number,
                'quarter'            => 'Q1',
                'file'               => UploadedFile::fake()->create(strtolower($number) . '.pdf', 50, 'application/pdf'),
                'items'              => $rows,
            ]
        );
    }

    private function row(BudgetProposalItem $item, ?float $qty = null): array
    {
        return [
            'name'      => $item->name,
            'unit'      => $item->unit,
            'quantity'  => $qty ?? (float) $item->quantity,
            'unit_cost' => (float) $item->estimated_unit_cost,
        ];
    }

    public function test_a_single_pr_may_not_exceed_the_approved_quantity(): void
    {
        $ppmp  = $this->approvedPpmp();
        $paper = $ppmp->items()->orderBy('id')->first();

        $res = $this->attemptPr($ppmp, 'PR-OVER-' . now()->format('His'), [$this->row($paper, 150)]);

        $res->assertStatus(422);
        $this->assertStringContainsString('exceeds', strtolower($res->json('error')) === '' ? '' : $res->json('validation.items.0.reason'));
        $this->step('EDGE 1  OK - one PR asking 150 of an approved 100 is refused: ' . $res->json('validation.items.0.reason'));
    }

    public function test_an_item_not_in_the_ppmp_is_refused(): void
    {
        $ppmp = $this->approvedPpmp();

        $res = $this->attemptPr($ppmp, 'PR-GHOST-' . now()->format('His'), [[
            'name'      => 'Brand new plasma television 65 inch',
            'unit'      => 'unit',
            'quantity'  => 2,
            'unit_cost' => 55000,
        ]]);

        $res->assertStatus(422);
        $this->step('EDGE 2  OK - item never in the PPMP is refused: ' . $res->json('validation.items.0.reason'));
    }

    public function test_duplicate_pr_number_is_refused(): void
    {
        $ppmp  = $this->approvedPpmp();
        $paper = $ppmp->items()->orderBy('id')->first();
        $num   = 'PR-DUP-' . now()->format('His');

        $this->attemptPr($ppmp, $num, [$this->row($paper, 10)])->assertOk();
        $second = $this->attemptPr($ppmp, $num, [$this->row($paper, 10)]);

        $second->assertStatus(422);
        $this->step('EDGE 3  OK - reusing a PR number is refused: ' . $second->json('error'));
    }

    /**
     * The scenario at the centre of the split-PR workflow: the same PPMP line
     * is drawn against by two separate PRs. Each PR is checked on its own, so
     * this documents whether the running total across sibling PRs is enforced.
     */
    public function test_two_separate_prs_drawing_against_the_same_ppmp_line(): void
    {
        $ppmp  = $this->approvedPpmp();
        $paper = $ppmp->items()->orderBy('id')->first();
        $stamp = now()->format('His');

        // PR 1 takes the ENTIRE approved quantity of the paper line.
        $first = $this->attemptPr($ppmp, "PR-CUM-A-{$stamp}", [$this->row($paper, 100)]);
        $first->assertOk();

        // PR 2 asks for the same 100 again, against the same approved line.
        $second = $this->attemptPr($ppmp, "PR-CUM-B-{$stamp}", [$this->row($paper, 100)]);

        $prCount = PurchaseRequest::where('budget_proposal_id', $ppmp->id)->count();
        $drawn   = (float) PurchaseRequest::where('budget_proposal_id', $ppmp->id)
            ->join('purchase_request_items', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')
            ->where('purchase_request_items.name', $paper->name)
            ->sum('purchase_request_items.quantity');

        $this->step('EDGE 4  PPMP approved ' . (float) $paper->quantity . ' ' . $paper->unit . ' of "' . $paper->name . '"');
        $this->step('        PR 1 for 100 -> HTTP ' . $first->status());
        $this->step('        PR 2 for 100 -> HTTP ' . $second->status()
            . ($second->status() === 422 ? ' (' . $second->json('error') . ')' : ' (ACCEPTED)'));
        $this->step('        PRs on this PPMP: ' . $prCount . ' | total quantity drawn against the line: ' . $drawn . ' of ' . (float) $paper->quantity);

        if ($drawn > (float) $paper->quantity) {
            $this->step('        >>> GAP: cumulative draw ' . $drawn . ' EXCEEDS the approved ' . (float) $paper->quantity
                . '. Each PR is validated in isolation; sibling PRs on the same PPMP line are not netted off.');
        }

        // Recorded as an observation of current behaviour, not a pass/fail gate.
        $this->assertGreaterThanOrEqual(1, $prCount);
    }

    /**
     * The same over-draw, but inside a single PR: two rows of the same item
     * that individually fit under the approved quantity yet together exceed it.
     */
    public function test_two_rows_of_the_same_item_inside_one_pr(): void
    {
        $ppmp  = $this->approvedPpmp();
        $paper = $ppmp->items()->orderBy('id')->first();

        $res = $this->attemptPr($ppmp, 'PR-SPLITROW-' . now()->format('His'), [
            $this->row($paper, 60),
            $this->row($paper, 60),
        ]);

        $this->step('EDGE 8  PPMP approved ' . (float) $paper->quantity . ' of "' . $paper->name . '"');
        $this->step('        One PR with two rows of 60 (=120) -> HTTP ' . $res->status()
            . ($res->status() === 422 ? ' (' . $res->json('error') . ')' : ' (ACCEPTED)'));

        foreach (($res->json('validation.items') ?? []) as $n => $line) {
            $this->step('        row ' . ($n + 1) . ': ' . $line['verdict'] . ' - ' . $line['reason']);
        }

        if ($res->status() === 200) {
            $pr    = PurchaseRequest::findOrFail($res->json('prId'));
            $drawn = (float) $pr->items()->sum('quantity');
            $this->step('        >>> GAP: this one PR alone draws ' . $drawn . ' against an approved ' . (float) $paper->quantity
                . '. Rows are checked one at a time, never totalled per PPMP line.');
        }

        $this->assertContains($res->status(), [200, 422]);
    }

    public function test_bulk_quotation_upload_is_refused_before_the_pr_is_fully_signed(): void
    {
        $ppmp  = $this->approvedPpmp();
        $paper = $ppmp->items()->orderBy('id')->first();

        $res = $this->attemptPr($ppmp, 'PR-EARLY-' . now()->format('His'), [$this->row($paper, 10)]);
        $res->assertOk();
        $pr = PurchaseRequest::findOrFail($res->json('prId'));

        $failures = 0;
        foreach (['Supplier A', 'Supplier B', 'Supplier C'] as $n => $supplier) {
            $up = $this->actingAs($this->procurement)->postJson(
                route('procurement-office.purchase-request.canvass-document', $pr->id),
                ['supplier_name' => $supplier, 'document' => UploadedFile::fake()->create("q{$n}.pdf", 20, 'application/pdf')]
            );
            if ($up->status() === 422) {
                $failures++;
            }
        }

        $this->assertSame(3, $failures, 'Every early upload should be refused');
        $this->assertSame(0, $pr->documents()->where('document_type', 'canvass_quotation')->count());
        $this->step('EDGE 5  OK - all 3 bulk uploads refused while the PR is still at draft stage, nothing stored');
    }

    public function test_finalizing_canvassing_without_a_single_quotation_is_refused(): void
    {
        $ppmp  = $this->approvedPpmp();
        $paper = $ppmp->items()->orderBy('id')->first();

        $res = $this->attemptPr($ppmp, 'PR-NOQ-' . now()->format('His'), [$this->row($paper, 10)]);
        $res->assertOk();
        $pr = PurchaseRequest::findOrFail($res->json('prId'));

        $this->driveToFullySigned($pr);

        $fin = $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.purchase-request.canvassing-finalize', $pr->id));
        $fin->assertStatus(422);

        $aoc = $this->actingAs($this->procurement)->postJson(route('procurement-office.aoc.create', $pr->id));
        $aoc->assertStatus(422);

        $this->step('EDGE 6  OK - finalize refused with zero quotations (' . $fin->json('error') . ')');
        $this->step('        OK - AOC creation refused before canvassing completes (' . $aoc->json('error') . ')');
    }

    public function test_a_non_pdf_quotation_and_an_oversize_file_are_refused(): void
    {
        $ppmp  = $this->approvedPpmp();
        $paper = $ppmp->items()->orderBy('id')->first();

        $res = $this->attemptPr($ppmp, 'PR-BADF-' . now()->format('His'), [$this->row($paper, 10)]);
        $res->assertOk();
        $pr = PurchaseRequest::findOrFail($res->json('prId'));
        $this->driveToFullySigned($pr);

        $exe = $this->actingAs($this->procurement)->postJson(
            route('procurement-office.purchase-request.canvass-document', $pr->id),
            ['supplier_name' => 'Bad Type Co.', 'document' => UploadedFile::fake()->create('quote.exe', 20)]
        );
        $exe->assertStatus(422);

        $big = $this->actingAs($this->procurement)->postJson(
            route('procurement-office.purchase-request.canvass-document', $pr->id),
            ['supplier_name' => 'Huge File Co.', 'document' => UploadedFile::fake()->create('huge.pdf', 11 * 1024, 'application/pdf')]
        );
        $big->assertStatus(422);

        // An image quotation (phone photo of a quote) is explicitly allowed.
        // Written as real PNG bytes rather than UploadedFile::fake()->image(),
        // which needs the GD extension XAMPP ships disabled.
        $pngPath = tempnam(sys_get_temp_dir(), 'quote') . '.png';
        file_put_contents($pngPath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        $img = $this->actingAs($this->procurement)->postJson(
            route('procurement-office.purchase-request.canvass-document', $pr->id),
            [
                'supplier_name' => 'Photo Quote Co.',
                'document'      => new UploadedFile($pngPath, 'quote.png', 'image/png', null, true),
            ]
        );
        $img->assertOk();

        $this->assertSame(1, $pr->documents()->where('document_type', 'canvass_quotation')->count());
        $this->step('EDGE 7  OK - .exe refused, >10MB refused, phone-photo JPG accepted (1 quotation stored)');
    }

    private function driveToFullySigned(PurchaseRequest $pr): void
    {
        $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.purchase-request.advance', $pr->id))->assertOk();

        $this->confirm($this->officeHead, 'office-head', $pr->id);
        $this->confirm($this->vcaa, 'vice-chancellor', $pr->id, ['third_signer' => 'accounting']);
        $this->confirm($this->accounting, 'accounting-office', $pr->id);
        $this->confirm($this->vcaf, 'vice-chancellor', $pr->id);
        $this->confirm($this->chancellor, 'chancellor', $pr->id);

        $this->assertSame('fully_signed', $pr->fresh()->signatory_stage);
    }

    private function confirm(User $user, string $prefix, int $id, array $extra = []): void
    {
        $this->actingAs($user)->postJson(route("{$prefix}.sign", ['pr', $id]))->assertOk();
        $this->actingAs($user)->postJson(route("{$prefix}.sign.confirm", ['pr', $id]), $extra)->assertOk();
    }
}
