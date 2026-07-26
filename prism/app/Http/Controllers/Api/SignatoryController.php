<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbstractOfCanvass;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\SignatoryActionService;
use App\Services\SignatoryQueueService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignatoryController extends Controller
{
    public function queue(SignatoryQueueService $queue): JsonResponse
    {
        $user     = auth()->user();
        $roleCode = $user->roles()->first()?->code;

        if (!$roleCode) {
            return response()->json(['items' => []]);
        }

        // Office heads sign at_end_user for their own office's PRs.
        // This stage has no generic stageOwnerRole, so we handle it separately.
        if ($roleCode === 'office-head' && $user->office_id) {
            $items = PurchaseRequest::with('office')
                ->where('office_id', $user->office_id)
                ->where('signatory_stage', 'at_end_user')
                ->orderBy('updated_at')
                ->get()
                ->map(fn ($pr) => [
                    'docType'                   => 'pr',
                    'docLabel'                  => 'PR',
                    'id'                        => $pr->id,
                    'number'                    => $pr->number ?? ('PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT)),
                    'office'                    => $pr->office?->name ?? '—',
                    'title'                     => $pr->title ?? '—',
                    'stageKey'                  => 'at_end_user',
                    'stageLabel'                => 'End User',
                    'stageType'                 => 'signature',
                    'requiresThirdSignerChoice' => false,
                    'waitingSince'              => $pr->updated_at?->format('M d, Y g:i A') ?? '—',
                ])
                ->values()
                ->all();

            return response()->json(['items' => $items]);
        }

        // VCAA/VCAF are each the fixed, university-wide signatory for their
        // owned stages — not scoped to division jurisdiction, so no office
        // filter here regardless of which office originated the document.
        if ($roleCode === 'vice-chancellor') {
            $roleCode = $user->vc_type ?? 'vice-chancellor'; // only VCAA/VCAF actually sign
        }

        return response()->json(['items' => $queue->forRole($roleCode, null)]);
    }

    /**
     * Step 1 of 2: sign. Routing stages are still a one-click forward. Signature
     * stages never advance here — a photo is optional, but either way this only
     * processes/detects the photo (if any) and hands back "ready" so a distinct
     * "Next" call (confirm) is what actually sends it to the next signatory.
     */
    public function sign(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc      = $this->resolveDoc($docType, $id);
        $user     = auth()->user();
        $roleCode = $user->roles()->first()?->code;

        if (!$this->userOwnsStage($doc, $roleCode, $user)) {
            return response()->json(['error' => 'This document is not at your signature stage.'], 403);
        }

        $stageMeta = $doc->stageMetaFor($doc->signatory_stage);
        $isRouting = ($stageMeta['type'] ?? 'signature') === 'routing';

        if ($isRouting) {
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
    public function confirm(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc      = $this->resolveDoc($docType, $id);
        $user     = auth()->user();
        $roleCode = $user->roles()->first()?->code;

        if (!$this->userOwnsStage($doc, $roleCode, $user)) {
            return response()->json(['error' => 'This document is not at your signature stage.'], 403);
        }

        $rules = [
            'photo_path'   => 'nullable|string|max:500',
            'blurred_path' => 'nullable|string|max:500',
            'remarks'      => 'nullable|string|max:1000',
        ];
        if ($docType === 'pr' && $doc->nextSignatoryStage() === 'at_third_sign') {
            $rules['third_signer'] = 'required|in:accounting,vice_chancellor';
        }
        $request->validate($rules);

        $result = $signatory->confirmSign(
            $doc,
            $request->input('photo_path'),
            null,
            $request->input('remarks'),
            $request->input('third_signer'),
            $request->input('blurred_path'),
        );

        return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
    }

    public function document(string $docType, int $id): JsonResponse
    {
        $doc = match ($docType) {
            'pr'    => PurchaseRequest::with(['office', 'items', 'signatureLogs.signedBy'])->findOrFail($id),
            'aoc'   => AbstractOfCanvass::with(['purchaseRequest.office'])->findOrFail($id),
            'po'    => PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office'])->findOrFail($id),
            default => abort(404),
        };

        $allStages    = $doc->signatoryStages();
        $currentIndex = $doc->stageIndex($doc->signatory_stage);
        $resolvedMeta = $doc->resolvedStageMeta();

        // Build chain — skip draft and fully_signed (not real signatory steps)
        $chain = collect($resolvedMeta)
            ->filter(fn ($m) => !in_array($m['key'], ['draft', 'fully_signed']))
            ->values()
            ->map(function ($meta, $loopIdx) use ($allStages, $currentIndex) {
                $stageIdx = array_search($meta['key'], $allStages);
                if ($stageIdx === false) $stageIdx = $loopIdx + 1;

                return [
                    'step'   => $loopIdx + 1,
                    'key'    => $meta['key'],
                    'label'  => $meta['label'],
                    'type'   => $meta['type'] ?? 'signature',
                    'status' => $stageIdx < $currentIndex ? 'signed'
                              : ($stageIdx === $currentIndex ? 'current' : 'pending'),
                ];
            })
            ->all();

        $response = [
            'id'              => $doc->id,
            'docType'         => $docType,
            'signatory_stage' => $doc->signatory_stage,
            'stage_label'     => $doc->stageMetaFor($doc->signatory_stage)['label'] ?? $doc->signatory_stage,
            'chain'           => $chain,
        ];

        if ($doc instanceof AbstractOfCanvass) {
            $pr = $doc->purchaseRequest;
            $response = array_merge($response, [
                'number'       => $doc->code ?? ('AOC-' . str_pad($doc->id, 4, '0', STR_PAD_LEFT)),
                'title'        => $pr?->title ?? '—',
                'description'  => $doc->remarks ?? '',
                'office'       => $pr?->office?->name ?? '—',
                'office_code'  => $pr?->office?->code ?? '—',
                'fiscal_year'  => $pr?->fiscal_year,
                'total_amount' => (float) ($pr?->total_amount ?? 0),
                'items'        => [],
                'logs'         => [],
            ]);
        }

        if ($doc instanceof PurchaseOrder) {
            $pr = $doc->abstractOfCanvass?->purchaseRequest;
            $response = array_merge($response, [
                'number'       => $doc->po_number ?? ('PO-' . str_pad($doc->id, 4, '0', STR_PAD_LEFT)),
                'title'        => ($pr?->title ?? '—') . ($doc->supplier_name ? ' — ' . $doc->supplier_name : ''),
                'description'  => $doc->remarks ?? '',
                'office'       => $pr?->office?->name ?? '—',
                'office_code'  => $pr?->office?->code ?? '—',
                'fiscal_year'  => $pr?->fiscal_year,
                'total_amount' => (float) $doc->total_amount,
                'items'        => [],
                'logs'         => [],
            ]);
        }

        if ($doc instanceof PurchaseRequest) {
            $response = array_merge($response, [
                'number'      => $doc->number ?? ('PR-' . str_pad($doc->id, 4, '0', STR_PAD_LEFT)),
                'title'       => $doc->title ?? '—',
                'description' => $doc->description ?? '',
                'office'      => $doc->office?->name ?? '—',
                'office_code' => $doc->office?->code ?? '—',
                'fiscal_year' => $doc->fiscal_year,
                'total_amount'=> (float) $doc->total_amount,
                'print_url'   => \Illuminate\Support\Facades\URL::temporarySignedRoute('print.pr', now()->addDay(), ['id' => $doc->id]),
                'items'       => $doc->items->map(fn ($item) => [
                    'id'        => $item->id,
                    'name'      => $item->name,
                    'description'=> $item->description ?? '',
                    'quantity'  => (float) $item->quantity,
                    'unit'      => $item->unit,
                    'unit_cost' => (float) $item->estimated_unit_cost,
                    'total'     => (float) $item->estimated_total_cost,
                ])->values()->all(),
                'logs'        => $doc->signatureLogs()
                    ->with('signedBy')
                    ->where('action', '!=', 'returned')
                    ->orderBy('signatory_number')
                    ->get()
                    ->map(function ($log) use ($doc, $allStages) {
                        $key   = $allStages[$log->signatory_number] ?? null;
                        $label = $doc->stageMetaFor($key)['label'] ?? ('Stage ' . $log->signatory_number);
                        return [
                            'stage_label'      => $label,
                            'action'           => $log->action,
                            'signed_by'        => $log->signedBy?->name ?? '—',
                            'signed_at'        => $log->signed_at?->format('M d, Y g:i A') ?? '—',
                            'remarks'          => $log->remarks,
                            'detection_status' => $log->detection_status,
                        ];
                    })->values()->all(),
            ]);
        }

        return response()->json($response);
    }

    private function resolveDoc(string $docType, int $id): Model
    {
        return match ($docType) {
            'pr'    => PurchaseRequest::findOrFail($id),
            'aoc'   => AbstractOfCanvass::findOrFail($id),
            'po'    => PurchaseOrder::findOrFail($id),
            default => abort(404),
        };
    }

    private function userOwnsStage(Model $doc, ?string $roleCode, $user): bool
    {
        if ($roleCode === 'office-head'
            && $doc instanceof PurchaseRequest
            && $doc->signatory_stage === 'at_end_user'
        ) {
            return $doc->office_id === $user->office_id;
        }

        if ($roleCode === 'vice-chancellor') {
            $roleCode = $user->vc_type ?? 'vice-chancellor'; // only VCAA/VCAF actually sign
        }

        return $doc->stageOwnerRole($doc->signatory_stage) === $roleCode;
    }
}
