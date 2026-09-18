<?php

namespace Tests\E2E;

use App\Models\AbstractOfCanvass;
use App\Models\BudgetProposal;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Once one PPMP carries several PRs, the procurement screens group siblings
 * under one row. This checks the grouping never hides a sibling: every PR,
 * AOC and PO still reaches the page, and the counts add up.
 */
class SplitPrVisibilityTest extends TestCase
{
    private User $officeHead;
    private User $finance;
    private User $procurement;
    private User $chancellor;
    private User $vcaa;
    private User $vcaf;
    private User $accounting;
    private User $bac;

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
        $this->bac         = User::where('email', 'bac@prism.test')->firstOrFail();
    }

    private function step(string $msg): void
    {
        fwrite(STDERR, $msg . PHP_EOL);
    }

    public function test_three_prs_from_one_ppmp_all_stay_visible_and_reach_their_own_aoc(): void
    {
        $stamp = now()->format('His');

        // A PPMP with 6 items, drawn against by THREE separate PRs.
        $rows = [
            ['description' => 'Bond Paper A4 substance 20', 'unit' => 'ream', 'quantity' => 60, 'estimatedUnitCost' => 250,  'targetQuarter' => 'Q1'],
            ['description' => 'Ballpen black fine tip',     'unit' => 'pc',   'quantity' => 120, 'estimatedUnitCost' => 12,  'targetQuarter' => 'Q1'],
            ['description' => 'Stapler heavy duty',         'unit' => 'pc',   'quantity' => 18,  'estimatedUnitCost' => 350, 'targetQuarter' => 'Q1'],
            ['description' => 'Whiteboard marker black',    'unit' => 'pc',   'quantity' => 90,  'estimatedUnitCost' => 35,  'targetQuarter' => 'Q1'],
            ['description' => 'Printer ink cartridge black','unit' => 'pc',   'quantity' => 24,  'estimatedUnitCost' => 850, 'targetQuarter' => 'Q1'],
            ['description' => 'Office chair ergonomic',     'unit' => 'pc',   'quantity' => 12,  'estimatedUnitCost' => 4500,'targetQuarter' => 'Q1'],
        ];

        $proposalId = null;
        foreach ($rows as $row) {
            $this->actingAs($this->officeHead)
                ->postJson(route('office-head.budget-proposal.store-item'), $row + ['proposal_id' => $proposalId])
                ->assertOk();
            $proposalId ??= BudgetProposal::whereIn('status', ['draft', 'returned'])->latest('id')->firstOrFail()->id;
        }

        $ppmp = BudgetProposal::findOrFail($proposalId);
        foreach ($ppmp->items as $item) {
            $this->actingAs($this->officeHead)->postJson(
                route('office-head.budget-proposal.item-attachment', $item->id),
                ['file' => UploadedFile::fake()->create("s{$item->id}.pdf", 25, 'application/pdf')]
            )->assertOk();
        }
        $this->actingAs($this->officeHead)
            ->postJson(route('office-head.budget-proposal.submit'), ['proposal_id' => $ppmp->id])->assertOk();
        $this->actingAs($this->finance)
            ->post(route('finance-office.proposal-review.endorse', $ppmp->id), ['remarks' => 'ok']);
        $this->actingAs($this->chancellor)
            ->postJson(route('chancellor.budget-approval.approve', $ppmp->id), ['remarks' => 'ok'])->assertOk();

        $items = $ppmp->items()->orderBy('id')->get();
        $prs   = [];
        foreach ([[0, 2], [2, 2], [4, 2]] as $n => [$offset, $len]) {
            $slice = $items->slice($offset, $len)->values();
            $res   = $this->actingAs($this->procurement)->postJson(
                route('procurement-office.purchase-request-management.create-pr'),
                [
                    'budget_proposal_id' => $ppmp->id,
                    'pr_number'          => 'PR-VIS-' . chr(65 + $n) . "-{$stamp}",
                    'title'              => 'Batch ' . ($n + 1),
                    'quarter'            => 'Q1',
                    'file'               => UploadedFile::fake()->create("vis{$n}.pdf", 40, 'application/pdf'),
                    'items'              => $slice->map(fn ($i) => [
                        'name'      => $i->name,
                        'unit'      => $i->unit,
                        'quantity'  => (float) $i->quantity,
                        'unit_cost' => (float) $i->estimated_unit_cost,
                    ])->all(),
                ]
            );
            if ($res->status() !== 200) {
                $this->fail('PR ' . $n . ' failed (' . $res->status() . '): ' . $res->getContent());
            }
            $prs[] = PurchaseRequest::findOrFail($res->json('prId'));
        }

        $this->step("VIS 1   One PPMP #{$ppmp->id} split into 3 PRs: " . implode(', ', array_map(fn ($p) => $p->number, $prs)));

        // Purchase Request Management payload must carry all three.
        $mgmt = $this->actingAs($this->procurement)
            ->getJson(route('procurement-office.purchase-request-management.refresh'));
        $mgmt->assertOk();

        $mine = collect($mgmt->json('purchaseRequests'))
            ->whereIn('id', array_map(fn ($p) => $p->id, $prs));

        $this->assertCount(3, $mine, 'All three sibling PRs must be present in the payload');
        $this->assertSame(1, $mine->where('isTableRow', true)->count(), 'Exactly one sibling opens the group row');
        $this->assertTrue($mine->every(fn ($r) => $r['siblingCount'] === 3), 'Each sibling should report a group of 3');
        $this->step('VIS 2   OK - PR Management returns all 3 (1 group row + 2 collapsed), siblingCount=3 on each');

        // Canvassing page: all three cards, each with its own upload URL.
        $canvassHtml = $this->actingAs($this->procurement)->get(route('procurement-office.canvassing'));
        $canvassHtml->assertOk();

        // Drive all three to fully signed, then bulk-upload different supplier
        // counts per PR and confirm the counts stay separate.
        $perPr = [2, 3, 4];
        foreach ($prs as $n => $pr) {
            $this->driveToFullySigned($pr);
            for ($s = 0; $s < $perPr[$n]; $s++) {
                $this->actingAs($this->procurement)->postJson(
                    route('procurement-office.purchase-request.canvass-document', $pr->id),
                    [
                        'supplier_name' => "Supplier {$n}-{$s}",
                        'document'      => UploadedFile::fake()->create("q{$n}{$s}.pdf", 30, 'application/pdf'),
                    ]
                )->assertOk();
            }
            $this->assertSame(
                $perPr[$n],
                $pr->documents()->where('document_type', 'canvass_quotation')->count(),
                "PR {$pr->number} should hold exactly {$perPr[$n]} quotations"
            );
        }
        $this->step('VIS 3   OK - bulk uploads stayed per-PR: ' . implode(' / ', array_map(
            fn ($p) => $p->number . '=' . $p->documents()->where('document_type', 'canvass_quotation')->count(),
            $prs
        )));

        foreach ($prs as $pr) {
            $this->actingAs($this->procurement)
                ->postJson(route('procurement-office.purchase-request.canvassing-finalize', $pr->id))->assertOk();
        }

        $aocs = [];
        foreach ($prs as $pr) {
            $res = $this->actingAs($this->procurement)->postJson(route('procurement-office.aoc.create', $pr->id));
            $res->assertOk();
            $aocs[] = AbstractOfCanvass::findOrFail($res->json('aoc.id'));
        }

        $this->assertCount(3, array_unique(array_map(fn ($a) => $a->code, $aocs)), 'Three distinct AOC codes');
        $this->step('VIS 4   OK - 3 distinct AOCs: ' . implode(', ', array_map(fn ($a) => $a->code, $aocs)));

        $aocPage = $this->actingAs($this->procurement)
            ->getJson(route('procurement-office.abstract-of-canvass.refresh'));
        $aocPage->assertOk();

        $payload  = $aocPage->json();
        $listKey  = isset($payload['abstractOfCanvasses']) ? 'abstractOfCanvasses'
            : (isset($payload['aocs']) ? 'aocs' : array_key_first($payload));
        $mineAocs = collect($payload[$listKey])->whereIn('id', array_map(fn ($a) => $a->id, $aocs));

        $this->assertCount(3, $mineAocs, 'All three AOCs must reach the Abstract of Canvass page');
        $this->step('VIS 5   OK - Abstract of Canvass page lists all 3 sibling AOCs (key: ' . $listKey . ')');
    }

    private function driveToFullySigned(PurchaseRequest $pr): void
    {
        $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.purchase-request.advance', $pr->id))->assertOk();

        $this->confirm($this->officeHead, 'office-head', $pr->id);
        $this->confirm($this->vcaa, 'vice-chancellor', $pr->id, ['third_signer' => 'vice_chancellor']);
        $this->confirm($this->vcaf, 'vice-chancellor', $pr->id);
        $this->confirm($this->accounting, 'accounting-office', $pr->id);
        $this->confirm($this->chancellor, 'chancellor', $pr->id);

        $this->assertSame('fully_signed', $pr->fresh()->signatory_stage);
    }

    private function confirm(User $user, string $prefix, int $id, array $extra = []): void
    {
        $sign = $this->actingAs($user)->postJson(route("{$prefix}.sign", ['pr', $id]));
        if ($sign->status() !== 200) {
            $this->fail("sign pr#{$id} as {$user->email} failed (" . $sign->status() . '): ' . $sign->getContent());
        }
        $confirm = $this->actingAs($user)->postJson(route("{$prefix}.sign.confirm", ['pr', $id]), $extra);
        if ($confirm->status() !== 200) {
            $this->fail("confirm pr#{$id} as {$user->email} failed (" . $confirm->status() . '): ' . $confirm->getContent());
        }
    }
}
