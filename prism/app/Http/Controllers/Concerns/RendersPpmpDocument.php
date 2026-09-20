<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BudgetProposal;
use App\Models\PurchaseRequestItem;
use App\Services\ProcurementModeService;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * The exact templated PPMP form (letterhead, INDICATIVE/FINAL, the 12-col
 * official table, Prepared/Reviewed/Approved by) an Office Head sees on their
 * own Print/Export — read-only, for any reviewer stage (Budget Office,
 * Chancellor, ...) that needs to check the actual submitted form rather than
 * just a review page's own item table. Shared so every such role renders the
 * identical document instead of each maintaining its own copy.
 */
trait RendersPpmpDocument
{
    protected function ppmpDocumentView(BudgetProposal $proposal): View
    {
        abort_if($proposal->status === 'draft', 404);

        $proposal->load(['office', 'items.marketReferences', 'items.sourceFiles', 'createdBy', 'reviewedBy', 'approvedBy']);

        $proposalForm = [
            'officeName'      => $proposal->office?->name ?? '—',
            'title'           => $proposal->title,
            'code'            => $proposal->code ?: '',
            'fiscalYear'      => $proposal->fiscal_year,
            'isFinal'         => $proposal->status === 'approved',
            'preparedByName'  => $proposal->createdBy?->name ?? '',
            'preparedByTitle' => $proposal->createdBy?->position_title ?? '',
            'preparedDate'    => $proposal->created_at?->format('M d, Y') ?? '',
            'reviewedByName'  => $proposal->reviewedBy?->name ?? '',
            'reviewedByTitle' => $proposal->reviewedBy?->position_title ?? '',
            'reviewedDate'    => $proposal->reviewed_at?->format('M d, Y') ?? '',
            'approvedByName'  => $proposal->approvedBy?->name ?? '',
            'approvedByTitle' => $proposal->approvedBy?->position_title ?? '',
            'approvedDate'    => $proposal->approved_at?->format('M d, Y') ?? '',
        ];

        // Official PPMP form column 8 "Expected Delivery/Implementation" — the
        // ACTUAL date Procurement Office set when it issued the Purchase
        // Order for this item (PurchaseOrder::expected_delivery_date),
        // reported back here rather than guessed up front by the office
        // head, since it isn't real until a supplier's been picked and a PO
        // issued. No stored link exists between a PPMP item and the PR item
        // eventually raised from it, so this matches on item name.
        $poDeliveryDateByItemName = PurchaseRequestItem::with('purchaseRequest.abstractOfCanvass.purchaseOrder')
            ->whereHas('purchaseRequest', fn ($q) => $q->where('budget_proposal_id', $proposal->id))
            ->get()
            ->keyBy(fn ($pri) => strtolower(trim($pri->name)))
            ->map(fn ($pri) => $pri->purchaseRequest?->abstractOfCanvass?->purchaseOrder?->expected_delivery_date);

        $items = $proposal->items->map(fn ($item) => [
            'description'      => $item->name,
            'projectType'       => $item->project_type ?? 'Goods',
            'quantity'          => (float) $item->quantity,
            'unit'              => $item->unit,
            'procurementMode'   => $item->procurement_mode ?: ProcurementModeService::recommend((float) $item->estimated_total_cost),
            'preProcurementConference' => (bool) $item->pre_procurement_conference,
            'procurementStartDate' => $item->procurement_start_date?->format('M d, Y'),
            'dateNeeded'        => $item->date_needed?->format('M d, Y'),
            'expectedDeliveryDate' => $poDeliveryDateByItemName->get(strtolower(trim($item->name)))?->format('M d, Y'),
            'sourceOfFund'      => $item->source_of_fund,
            'totalCost'         => (float) $item->estimated_total_cost,
            'justification'     => $item->remarks ?? '',
            'attachments'       => $item->sourceFiles->map(fn ($doc) => [
                'name' => $doc->original_filename ?? $doc->title,
                'url'  => str_starts_with($doc->file_path, 'http') ? $doc->file_path : Storage::url($doc->file_path),
            ])->values()->all(),
            'scoping'           => $item->marketReferences->where('is_selected', true)
                ->map(fn ($ref) => [
                    'supplierName' => $ref->supplier_name,
                    'sourceLink'   => $ref->source_url ?? '',
                ])->values()->all(),
        ]);

        return view('prism.shared.ppmp-document', [
            'pageTitle'     => 'PPMP Document',
            'proposalForm'  => $proposalForm,
            'items'         => $items,
            'proposalTotal' => (float) $items->sum('totalCost'),
        ]);
    }
}
