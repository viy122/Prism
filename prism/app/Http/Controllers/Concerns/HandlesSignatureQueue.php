<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AbstractOfCanvass;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\SignatoryActionService;
use App\Services\SignatoryQueueService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared handlers for a role's "For My Signature" queue. The consuming
 * controller defines which role's queue it serves and under which route
 * name prefix its sign endpoints live.
 */
trait HandlesSignatureQueue
{
    /** roles.code whose stages this controller signs (e.g. 'bac') */
    abstract protected function queueRoleCode(): string;

    /** route name prefix (e.g. 'bac' → routes bac.sign / bac.sign.confirm) */
    abstract protected function queueRoutePrefix(): string;

    /** Office IDs to scope the queue to, or null for no office filter. */
    protected function queueOfficeIds(): ?array
    {
        return null;
    }

    /**
     * Step 1 of 2: sign. Routing stages are still a one-click forward (they
     * were never signature-based). Signature stages never advance here —
     * a photo is optional, but either way this only processes/detects the
     * photo (if any) and hands back "ready" so a distinct "Next" action
     * (confirmSignDocument) is what actually sends it to the next signatory.
     */
    public function signDocument(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc = $this->resolveSignableDoc($docType, $id);
        $this->authorizeStageOwnership($doc);

        if (($doc->stageMetaFor($doc->signatory_stage)['type'] ?? 'signature') === 'routing') {
            $result = $signatory->advance($doc, $request->input('remarks'));
            return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
        }

        $request->validate(['photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240']);

        if (!$request->hasFile('photo')) {
            return response()->json(['success' => true, 'needsConfirmation' => true, 'detection' => 'none', 'photoPath' => null]);
        }

        $result = $signatory->signWithPhoto($doc, $request->file('photo'));

        return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
    }

    /**
     * Step 2 of 2 ("Next"): the sole point that actually advances a
     * signature-type stage. `photo_path` is optional — null means no photo
     * was ever attached (signing without one is allowed).
     */
    public function confirmSignDocument(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc = $this->resolveSignableDoc($docType, $id);
        $this->authorizeStageOwnership($doc);

        $rules = [
            'photo_path'   => 'nullable|string|max:500',
            'blurred_path' => 'nullable|string|max:500',
            'boxes'        => 'nullable|array|max:6',
            'boxes.*.x'    => 'required|numeric|min:0|max:1',
            'boxes.*.y'    => 'required|numeric|min:0|max:1',
            'boxes.*.w'    => 'required|numeric|min:0|max:1',
            'boxes.*.h'    => 'required|numeric|min:0|max:1',
            'remarks'      => 'nullable|string|max:1000',
        ];
        if ($doc instanceof PurchaseRequest && $doc->nextSignatoryStage() === 'at_third_sign') {
            $rules['third_signer'] = 'required|in:accounting,vice_chancellor';
        }
        $request->validate($rules);

        $result = $signatory->confirmSign(
            $doc,
            $request->input('photo_path'),
            $request->input('boxes'),
            $request->input('remarks'),
            $request->input('third_signer'),
            $request->input('blurred_path'),
        );

        return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
    }

    /** Queue rows with this controller's sign endpoints attached. */
    protected function signatureQueueRows(): array
    {
        $queue = app(SignatoryQueueService::class);

        return array_map(function (array $row) {
            $row['signUrl']    = route($this->queueRoutePrefix() . '.sign', [$row['docType'], $row['id']]);
            $row['confirmUrl'] = route($this->queueRoutePrefix() . '.sign.confirm', [$row['docType'], $row['id']]);
            return $row;
        }, $queue->forRole($this->queueRoleCode(), $this->queueOfficeIds()));
    }

    /**
     * EVERY document this role could ever be a signatory for — created,
     * in progress, or already fully signed — not just the ones currently
     * awaiting action. Powers a "show everything, gate the action" list +
     * detail-panel signatories page (mirrors Office Head's page). `canAct`
     * tells the UI which one row is actually actionable right now.
     *
     * Scoped to `queueOfficeIds()` when a controller overrides it (e.g.
     * Office Head only ever sees their own office's documents); roles with
     * no office boundary (VC, Chancellor, Accounting, BAC) leave it null.
     *
     * @param  array<string>  $docTypes  subset of ['pr','aoc','po'] this role
     *                                   is ever a signatory for
     */
    protected function signatureHistoryRows(array $docTypes): array
    {
        $rows      = collect();
        $officeIds = $this->queueOfficeIds();

        if (in_array('pr', $docTypes, true)) {
            $rows = $rows->concat(
                PurchaseRequest::with(['office', 'signatureLogs.signedBy', 'signatureLogs.attachments'])
                    ->when($officeIds, fn ($q, $ids) => $q->whereIn('office_id', $ids))
                    ->latest()
                    ->get()
                    ->map(fn ($pr) => $this->historyRowFor(
                        $pr, 'pr', $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT), $pr->office?->code, $pr->title, $pr->remarks
                    ))
            );
        }

        if (in_array('aoc', $docTypes, true)) {
            $rows = $rows->concat(
                AbstractOfCanvass::with(['purchaseRequest.office', 'purchaseRequest.items', 'purchaseRequest.documents', 'signatureLogs.signedBy', 'signatureLogs.attachments'])
                    ->when($officeIds, fn ($q, $ids) => $q->whereHas('purchaseRequest', fn ($q2) => $q2->whereIn('office_id', $ids)))
                    ->latest()
                    ->get()
                    ->map(fn ($aoc) => $this->historyRowFor(
                        $aoc, 'aoc', $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
                        $aoc->purchaseRequest?->office?->code, $aoc->purchaseRequest?->title, $aoc->remarks
                    ))
            );
        }

        if (in_array('po', $docTypes, true)) {
            $rows = $rows->concat(
                PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'signatureLogs.signedBy', 'signatureLogs.attachments'])
                    ->when($officeIds, fn ($q, $ids) => $q->whereHas('abstractOfCanvass.purchaseRequest', fn ($q2) => $q2->whereIn('office_id', $ids)))
                    ->latest()
                    ->get()
                    ->map(function ($po) {
                        $pr = $po->abstractOfCanvass?->purchaseRequest;
                        return $this->historyRowFor(
                            $po, 'po', $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                            $pr?->office?->code, ($pr?->title ?? '—') . ' — ' . $po->supplier_name, $po->remarks
                        );
                    })
            );
        }

        return $rows->sortByDesc('updatedAt')->values()->all();
    }

    /** JSON version of signatureHistoryRows(), for the page's background poll. */
    protected function signatureHistoryJson(array $docTypes): JsonResponse
    {
        return response()->json(['documents' => $this->signatureHistoryRows($docTypes)]);
    }

    private function historyRowFor(Model $doc, string $docType, string $number, ?string $office, ?string $title, ?string $remarks): array
    {
        $stages  = $doc::signatoryStages();
        $current = $doc->stageIndex($doc->signatory_stage);

        $chain = collect($doc->resolvedStageMeta())
            ->filter(fn ($m) => !in_array($m['key'], ['draft', 'fully_signed'], true))
            ->values()
            ->map(function ($meta) use ($stages, $current) {
                $idx = array_search($meta['key'], $stages);
                return [
                    'key'    => $meta['key'],
                    'label'  => $meta['label'],
                    'type'   => $meta['type'] ?? 'signature',
                    'status' => $idx < $current ? 'done' : ($idx === $current ? 'active' : 'pending'),
                ];
            })->values()->all();

        $row = [
            'docType'        => $docType,
            'docLabel'       => strtoupper($docType),
            'id'             => $doc->id,
            'number'         => $number,
            'office'         => $office ?? '—',
            'title'          => $title ?? '—',
            'remarks'        => $remarks ?: '—',
            'signatoryStage' => $doc->signatory_stage,
            'signatoryLabel' => $doc->signatory_label,
            'stageType'      => $doc->stageMetaFor($doc->signatory_stage)['type'] ?? 'signature',
            'nextStage'      => $doc->nextSignatoryStage(),
            'canAct'         => $doc->signatory_stage !== 'fully_signed'
                && $doc->stageOwnerRole($doc->signatory_stage) === $this->queueRoleCode(),
            'chain'          => $chain,
            'signatureLogs'  => $doc->signatureLogs->map(fn ($l) => [
                'display'     => $doc->describeSignatureLog($l),
                'by'          => $l->signedBy?->name ?? '—',
                'at'          => $l->signed_at?->format('M d, Y g:i A') ?? '—',
                'atRaw'       => $l->signed_at?->toIso8601String(),
                // Only meaningful for a 'returned' entry (the reason it was sent
                // back) — surfaced separately from 'display' so the reason is
                // visible to whoever the document lands with next, not just
                // logged silently.
                'remarks'     => $l->action === 'returned' ? $l->remarks : null,
                'attachments' => $l->attachments->map(fn ($a) => [
                    'filename' => $a->original_filename,
                    'isImage'  => str_starts_with($a->mime_type ?? '', 'image/'),
                    'url'      => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'signature-attachment.show', now()->addDay(), ['id' => $a->id]
                    ),
                ])->all(),
            ])->all(),
            'signUrl'        => route($this->queueRoutePrefix() . '.sign', [$docType, $doc->id]),
            'confirmUrl'     => route($this->queueRoutePrefix() . '.sign.confirm', [$docType, $doc->id]),
            'updatedAt'      => $doc->updated_at,
        ];

        // Preview: PR and AOC both embed their own uploaded scanned PDF.
        // AOC additionally carries the parent PR's items + the canvass
        // quotations gathered for it — the actual substance behind the
        // Abstract of Canvass. PO has no file of its own and isn't previewed.
        if ($docType === 'pr') {
            $row['pdfFile'] = $doc->file_path;
        } elseif ($docType === 'aoc') {
            $row['pdfFile'] = $doc->file_path;
            $pr = $doc->purchaseRequest;
            $row['prItems'] = $pr?->items->map(fn ($i) => [
                'name'      => $i->name,
                'quantity'  => (float) $i->quantity,
                'unit'      => $i->unit,
                'unitCost'  => (float) $i->estimated_unit_cost,
                'totalCost' => (float) $i->estimated_total_cost,
            ])->all() ?? [];
            $row['prTotal'] = (float) ($pr?->total_amount ?? 0);
            $row['quotations'] = $pr?->documents->where('document_type', 'canvass_quotation')->map(fn ($d) => [
                'supplier'   => $d->title,
                'filename'   => $d->original_filename,
                'url'        => \Illuminate\Support\Facades\Storage::url($d->file_path),
                'uploadedAt' => $d->uploaded_at?->format('M d, Y') ?? '—',
            ])->values()->all() ?? [];
        }

        return $row;
    }

    protected function resolveSignableDoc(string $docType, int $id): Model
    {
        return match ($docType) {
            'pr'    => PurchaseRequest::findOrFail($id),
            'aoc'   => AbstractOfCanvass::findOrFail($id),
            'po'    => PurchaseOrder::findOrFail($id),
            default => abort(404),
        };
    }

    /**
     * The queue UI is not a security boundary — every sign endpoint verifies
     * the document is actually at a stage owned by this controller's role.
     */
    protected function authorizeStageOwnership(Model $doc): void
    {
        if ($doc->stageOwnerRole($doc->signatory_stage) !== $this->queueRoleCode()) {
            abort(403, 'This document is not at your signature stage.');
        }
    }
}
