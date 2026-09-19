<?php

namespace Tests\E2E;

use App\Models\AbstractOfCanvass;
use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end walk of the full PRISM procurement chain for the scenario the
 * office actually hits: ONE approved PPMP with many items, split into TWO
 * separate Purchase Requests, each carrying its own canvassing, its own
 * Abstract of Canvass and its own Purchase Order.
 *
 * Runs against the disposable `prism_e2e` database (phpunit.e2e.xml) and the
 * live matcher microservice on :5001 - nothing here touches prism_db.
 */
class SplitPrFlowTest extends TestCase
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

    public function test_one_ppmp_splits_into_two_prs_two_aocs_two_pos(): void
    {
        $stamp = now()->format('His');

        // 1. Office Head builds the PPMP with many items
        $items = [
            ['description' => 'Bond Paper A4 substance 20',   'unit' => 'ream', 'quantity' => 100, 'estimatedUnitCost' => 250,   'targetQuarter' => 'Q1'],
            ['description' => 'Ballpen black fine tip',       'unit' => 'pc',   'quantity' => 200, 'estimatedUnitCost' => 12,    'targetQuarter' => 'Q1'],
            ['description' => 'Stapler heavy duty',           'unit' => 'pc',   'quantity' => 20,  'estimatedUnitCost' => 350,   'targetQuarter' => 'Q1'],
            ['description' => 'Whiteboard marker black',      'unit' => 'pc',   'quantity' => 100, 'estimatedUnitCost' => 35,    'targetQuarter' => 'Q1'],
            ['description' => 'Laptop computer core i5',      'unit' => 'unit', 'quantity' => 5,   'estimatedUnitCost' => 45000, 'targetQuarter' => 'Q2'],
            ['description' => 'Printer ink cartridge black',  'unit' => 'pc',   'quantity' => 30,  'estimatedUnitCost' => 850,   'targetQuarter' => 'Q2'],
            ['description' => 'Office chair ergonomic',       'unit' => 'pc',   'quantity' => 15,  'estimatedUnitCost' => 4500,  'targetQuarter' => 'Q2'],
            ['description' => 'Filing cabinet steel 4-layer', 'unit' => 'unit', 'quantity' => 10,  'estimatedUnitCost' => 8500,  'targetQuarter' => 'Q2'],
        ];

        $proposalId = null;
        foreach ($items as $item) {
            $res = $this->actingAs($this->officeHead)
                ->postJson(route('office-head.budget-proposal.store-item'), $item + ['proposal_id' => $proposalId]);

            if ($res->status() !== 200) {
                $this->fail('storeItem failed (' . $res->status() . '): ' . $res->getContent());
            }
            $proposalId ??= BudgetProposal::whereIn('status', ['draft', 'returned'])->latest('id')->firstOrFail()->id;
        }

        $proposal = BudgetProposal::findOrFail($proposalId);
        $this->assertSame(8, $proposal->items()->count(), 'PPMP should hold all 8 encoded items');
        $this->step("STEP 1  PPMP #{$proposal->id} ({$proposal->code}) created with " . $proposal->items()->count() . ' items');

        // Every item needs a market-study source before the PPMP may be submitted.
        foreach ($proposal->items as $item) {
            $this->actingAs($this->officeHead)->postJson(
                route('office-head.budget-proposal.item-attachment', $item->id),
                ['file' => UploadedFile::fake()->create("source-{$item->id}.pdf", 40, 'application/pdf')]
            )->assertOk();
        }
        $this->step('STEP 2  Attached ' . $proposal->items()->count() . ' market-study source files (one per item)');

        // 2. Submit -> endorse -> approve
        $submit = $this->actingAs($this->officeHead)
            ->postJson(route('office-head.budget-proposal.submit'), ['proposal_id' => $proposal->id]);
        if ($submit->status() !== 200) {
            $this->fail('submitProposal failed (' . $submit->status() . '): ' . $submit->getContent());
        }
        $this->assertSame('submitted', $proposal->fresh()->status);

        $this->actingAs($this->finance)
            ->post(route('finance-office.proposal-review.endorse', $proposal->id), ['remarks' => 'E2E endorse']);
        $this->assertSame('endorsed', $proposal->fresh()->status, 'Budget Office endorsement did not take');

        $this->actingAs($this->chancellor)
            ->postJson(route('chancellor.budget-approval.approve', $proposal->id), ['remarks' => 'E2E approve'])
            ->assertOk();
        $this->assertSame('approved', $proposal->fresh()->status);
        $this->step('STEP 3  PPMP submitted -> endorsed (Budget) -> approved (Chancellor)');

        // 3. Split the approved PPMP into TWO separate PRs
        $all      = $proposal->items()->orderBy('id')->get();
        $supplies = $all->slice(0, 4)->values();   // Q1 consumables
        $equip    = $all->slice(4, 4)->values();   // Q2 equipment

        $prA = $this->createPr($proposal, "PR-E2E-A-{$stamp}", 'Office supplies - 1st batch', 'Q1', $supplies);
        $prB = $this->createPr($proposal, "PR-E2E-B-{$stamp}", 'Equipment - 2nd batch',       'Q2', $equip);

        $this->assertNotSame($prA->id, $prB->id);
        $this->assertSame($proposal->id, $prA->budget_proposal_id);
        $this->assertSame($proposal->id, $prB->budget_proposal_id);
        $this->assertSame(4, $prA->items()->count());
        $this->assertSame(4, $prB->items()->count());
        $this->assertEqualsWithDelta(37900.0, (float) $prA->total_amount, 0.01);
        $this->assertEqualsWithDelta(403000.0, (float) $prB->total_amount, 0.01);
        $this->step("STEP 4  Split into 2 PRs from the SAME PPMP: {$prA->number} (4 items, " . number_format((float) $prA->total_amount, 2) . ") + {$prB->number} (4 items, " . number_format((float) $prB->total_amount, 2) . ')');

        // Items must not bleed across the two PRs.
        $namesA = $prA->items()->pluck('name')->all();
        $namesB = $prB->items()->pluck('name')->all();
        $this->assertEmpty(array_intersect($namesA, $namesB), 'The two PRs must not share items');
        $this->step('STEP 5  Item isolation OK - no item appears in both PRs');

        // 4. Route both PRs through the full signature chain
        $this->signPrChain($prA);
        $this->signPrChain($prB);
        $this->assertSame('fully_signed', $prA->fresh()->signatory_stage);
        $this->assertSame('fully_signed', $prB->fresh()->signatory_stage);
        $this->step('STEP 6  Both PRs fully signed through End User -> VCAA -> 3rd -> 4th -> Chancellor');

        // 5. Bulk canvass uploads, per PR
        $suppliersA = ['Nasugbu Office Supplies Inc.', 'Batangas Trading Corp.', 'Lipa Paper House'];
        $suppliersB = ['TechnoWorld Computer Center', 'Metro Equipment Supply', 'Southern Furniture Depot', 'PrimeTech Solutions'];

        $this->bulkUpload($prA, $suppliersA);
        $this->bulkUpload($prB, $suppliersB);

        $countA = $prA->documents()->where('document_type', 'canvass_quotation')->count();
        $countB = $prB->documents()->where('document_type', 'canvass_quotation')->count();
        $this->assertSame(3, $countA);
        $this->assertSame(4, $countB);
        $this->step("STEP 7  Bulk quotation upload OK - {$countA} attached to {$prA->number}, {$countB} to {$prB->number}, none cross-attached");

        // Every stored file must actually exist and be tied to the right PR.
        foreach ([$prA, $prB] as $pr) {
            foreach ($pr->documents()->where('document_type', 'canvass_quotation')->get() as $doc) {
                $this->assertTrue(Storage::disk('public')->exists($doc->file_path), "Missing stored file {$doc->file_path}");
                $this->assertSame(PurchaseRequest::class, $doc->attachable_type);
                $this->assertSame($pr->id, $doc->attachable_id);
            }
        }
        $this->step('STEP 8  All ' . ($countA + $countB) . ' uploaded files persisted on disk and correctly owned');

        // Finalize canvassing separately per PR.
        foreach ([$prA, $prB] as $pr) {
            $fin = $this->actingAs($this->procurement)
                ->postJson(route('procurement-office.purchase-request.canvassing-finalize', $pr->id));
            if ($fin->status() !== 200) {
                $this->fail("finalizeCanvassing for {$pr->number} failed (" . $fin->status() . '): ' . $fin->getContent());
            }
            $this->assertSame('completed', $pr->fresh()->canvassing_stage);
            $this->assertTrue($pr->fresh()->isReadyForAoc());
        }
        $this->step('STEP 9  Canvassing finalized independently on both PRs');

        // 6. Two separate AOCs
        $aocA = $this->createAoc($prA);
        $aocB = $this->createAoc($prB);

        $this->assertNotSame($aocA->id, $aocB->id);
        $this->assertNotSame($aocA->code, $aocB->code, 'Each PR must get its own distinct AOC code');
        $this->assertSame($prA->id, $aocA->purchase_request_id);
        $this->assertSame($prB->id, $aocB->purchase_request_id);
        $this->assertSame(2, AbstractOfCanvass::whereIn('purchase_request_id', [$prA->id, $prB->id])->count());
        $this->step("STEP 10 Two separate AOCs created: {$aocA->code} (for {$prA->number}) and {$aocB->code} (for {$prB->number})");

        // Quotations lock once an AOC exists.
        $locked = $this->actingAs($this->procurement)->postJson(
            route('procurement-office.purchase-request.canvass-document', $prA->id),
            ['supplier_name' => 'Late Supplier', 'document' => UploadedFile::fake()->create('late.pdf', 30, 'application/pdf')]
        );
        $locked->assertStatus(422);
        $this->assertSame(3, $prA->fresh()->documents()->where('document_type', 'canvass_quotation')->count());
        $this->step('STEP 11 Quotation lock OK - upload refused after AOC exists (' . $locked->json('error') . ')');

        // A second AOC on the same PR must be refused.
        $dupe = $this->actingAs($this->procurement)->postJson(route('procurement-office.aoc.create', $prA->id));
        $dupe->assertStatus(422);
        $this->step('STEP 12 Duplicate-AOC guard OK - ' . $dupe->json('error'));

        // 7. Route both AOCs through their own chain
        $this->signAocChain($aocA);
        $this->signAocChain($aocB);
        $this->assertSame('fully_signed', $aocA->fresh()->signatory_stage);
        $this->assertSame('fully_signed', $aocB->fresh()->signatory_stage);
        $this->step('STEP 13 Both AOCs fully signed through End User -> BAC x3 -> VCAF -> Chancellor');

        // 8. One PO per AOC
        $poA = $this->issuePo($aocA, 'Nasugbu Office Supplies Inc.', (float) $prA->total_amount);
        $poB = $this->issuePo($aocB, 'TechnoWorld Computer Center',  (float) $prB->total_amount);

        $this->assertNotSame($poA->id, $poB->id);
        // po_number is deliberately left unset at issuance now — it's only
        // known once the actual signed PO is uploaded and read (see
        // PrismProcurementOfficeController::uploadPurchaseOrder()) — so
        // there's nothing to compare here yet; the id/AOC-link checks above
        // and below already prove these are two genuinely separate records.
        $this->assertNull($poA->po_number);
        $this->assertNull($poB->po_number);
        $this->assertSame($aocA->id, $poA->abstract_of_canvass_id);
        $this->assertSame($aocB->id, $poB->abstract_of_canvass_id);
        $this->step("STEP 14 Two separate POs issued for PO-{$poA->id} (" . number_format((float) $poA->total_amount, 2) . ") and PO-{$poB->id} (" . number_format((float) $poB->total_amount, 2) . ')');

        $this->signPoChain($poA);
        $this->signPoChain($poB);
        $this->assertSame('fully_signed', $poA->fresh()->signatory_stage);
        $this->assertSame('fully_signed', $poB->fresh()->signatory_stage);
        $this->step('STEP 15 Both POs fully signed through Accounting -> Chancellor -> Supplier');

        // 9. The two branches stayed independent end to end
        $this->assertSame(2, PurchaseRequest::where('budget_proposal_id', $proposal->id)->count());
        $this->assertSame(2, PurchaseOrder::whereIn('abstract_of_canvass_id', [$aocA->id, $aocB->id])->count());
        $this->step('STEP 16 One PPMP -> 2 PRs -> 2 AOCs -> 2 POs, fully independent. FLOW COMPLETE.');
    }

    // helpers

    private function createPr(BudgetProposal $proposal, string $number, string $title, string $quarter, $items): PurchaseRequest
    {
        $payload = [
            'budget_proposal_id' => $proposal->id,
            'pr_number'          => $number,
            'title'              => $title,
            'quarter'            => $quarter,
            'file'               => UploadedFile::fake()->create(strtolower($number) . '.pdf', 60, 'application/pdf'),
            'items'              => $items->map(fn (BudgetProposalItem $i) => [
                'name'      => $i->name,
                'unit'      => $i->unit,
                'quantity'  => (float) $i->quantity,
                'unit_cost' => (float) $i->estimated_unit_cost,
            ])->all(),
        ];

        $res = $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.purchase-request-management.create-pr'), $payload);

        if ($res->status() !== 200) {
            $this->fail("PR creation for {$number} failed (" . $res->status() . '): ' . $res->getContent());
        }

        return PurchaseRequest::findOrFail($res->json('prId'));
    }

    private function bulkUpload(PurchaseRequest $pr, array $suppliers): void
    {
        foreach ($suppliers as $n => $supplier) {
            $res = $this->actingAs($this->procurement)->postJson(
                route('procurement-office.purchase-request.canvass-document', $pr->id),
                [
                    'supplier_name' => $supplier,
                    'document'      => UploadedFile::fake()->create("quote-{$pr->id}-{$n}.pdf", 45, 'application/pdf'),
                ]
            );
            if ($res->status() !== 200) {
                $this->fail("Quotation upload #{$n} for PR {$pr->number} failed (" . $res->status() . '): ' . $res->getContent());
            }
        }
    }

    private function createAoc(PurchaseRequest $pr): AbstractOfCanvass
    {
        $res = $this->actingAs($this->procurement)->postJson(route('procurement-office.aoc.create', $pr->id));
        if ($res->status() !== 200) {
            $this->fail("AOC creation for PR {$pr->number} failed (" . $res->status() . '): ' . $res->getContent());
        }

        return AbstractOfCanvass::findOrFail($res->json('aoc.id'));
    }

    private function issuePo(AbstractOfCanvass $aoc, string $supplier, float $amount): PurchaseOrder
    {
        $res = $this->actingAs($this->procurement)->postJson(route('procurement-office.po.issue', $aoc->id), [
            'supplier_name' => $supplier,
            'total_amount'  => $amount,
        ]);
        if ($res->status() !== 200) {
            $this->fail("PO issue for AOC {$aoc->code} failed (" . $res->status() . '): ' . $res->getContent());
        }

        return PurchaseOrder::findOrFail($res->json('po.id'));
    }

    /** draft -> at_end_user -> at_vice_chancellor -> 3rd -> 4th -> at_chancellor -> fully_signed */
    private function signPrChain(PurchaseRequest $pr): void
    {
        // draft is a routing stage - procurement forwards it.
        $adv = $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.purchase-request.advance', $pr->id));
        if ($adv->status() !== 200) {
            $this->fail("advance draft for {$pr->number} failed (" . $adv->status() . '): ' . $adv->getContent());
        }

        $this->confirm($this->officeHead, 'office-head', 'pr', $pr->id);
        // The VCAA's confirmation also nominates who takes the flexible 3rd slot.
        $this->confirm($this->vcaa, 'vice-chancellor', 'pr', $pr->id, ['third_signer' => 'accounting']);
        $this->confirm($this->accounting, 'accounting-office', 'pr', $pr->id);
        $this->confirm($this->vcaf, 'vice-chancellor', 'pr', $pr->id);
        $this->confirm($this->chancellor, 'chancellor', 'pr', $pr->id);
    }

    /** draft -> at_end_user -> BAC member -> BAC vice chair -> BAC chair -> VCAF -> Chancellor */
    private function signAocChain(AbstractOfCanvass $aoc): void
    {
        $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.aoc.advance', $aoc->id))->assertOk();

        $this->confirm($this->officeHead, 'office-head', 'aoc', $aoc->id);
        $this->confirm($this->bac, 'bac', 'aoc', $aoc->id);
        $this->confirm($this->bac, 'bac', 'aoc', $aoc->id);
        $this->confirm($this->bac, 'bac', 'aoc', $aoc->id);
        $this->confirm($this->vcaf, 'vice-chancellor', 'aoc', $aoc->id);
        $this->confirm($this->chancellor, 'chancellor', 'aoc', $aoc->id);
    }

    /** draft -> at_accounting -> at_chancellor -> at_supplier -> fully_signed */
    private function signPoChain(PurchaseOrder $po): void
    {
        $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.po.advance', $po->id))->assertOk();

        $this->confirm($this->accounting, 'accounting-office', 'po', $po->id);
        $this->confirm($this->chancellor, 'chancellor', 'po', $po->id);

        // at_supplier has no in-system role - procurement records it.
        $this->actingAs($this->procurement)
            ->postJson(route('procurement-office.po.advance', $po->id))->assertOk();
    }

    /** Sign (no photo) then confirm, as the role that owns the current stage. */
    private function confirm(User $user, string $prefix, string $docType, int $id, array $extra = []): void
    {
        $sign = $this->actingAs($user)->postJson(route("{$prefix}.sign", [$docType, $id]));
        if ($sign->status() !== 200) {
            $this->fail("sign {$docType}#{$id} as {$user->email} failed (" . $sign->status() . '): ' . $sign->getContent());
        }

        $confirm = $this->actingAs($user)->postJson(route("{$prefix}.sign.confirm", [$docType, $id]), $extra);
        if ($confirm->status() !== 200) {
            $this->fail("confirm {$docType}#{$id} as {$user->email} failed (" . $confirm->status() . '): ' . $confirm->getContent());
        }
    }
}
