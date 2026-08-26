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
    /**
     * AOC's three BAC-owned stages all share the generic 'bac' role code —
     * this is the mobile-side split (per user->bac_position) so a BAC Member
     * doesn't see/act on the Vice Chairperson's or Chairperson's stage, and
     * vice versa. Kept mobile-only for now; web's BAC queue is unchanged.
     */
    private const BAC_STAGE_POSITIONS = [
        'at_bac_member'     => 'member',
        'at_bac_vice_chair' => 'vice_chair',
        'at_bac_chair'      => 'chair',
    ];

    public function queue(SignatoryQueueService $queue): JsonResponse
    {
        $user      = auth()->user();
        $roleCodes = $user->roles()->pluck('code')->all();

        if (!$roleCodes) {
            return response()->json(['items' => []]);
        }

        $items = [];

        // Office heads sign at_end_user for their own office's PRs — AND their
        // own office's AOCs, which have their own independent at_end_user
        // stage after the parent PR is fully signed. Neither has a generic
        // stageOwnerRole (office scoping isn't expressible there), so both
        // are handled separately here.
        if (in_array('office-head', $roleCodes, true) && $user->office_id) {
            $items = array_merge($items, PurchaseRequest::with('office')
                ->where('office_id', $user->office_id)
                ->where('signatory_stage', 'at_end_user')
                ->orderBy('updated_at')
                ->get()
                ->map(fn ($pr) => [
                    'docType'                   => 'pr',
                    'docLabel'                  => 'PR',
                    'id'                        => $pr->id,
                    'number'                    => $pr->number ?? ('PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT)),
                    'office'                    => $pr->office?->code ?? '—',
                    'title'                     => $pr->title ?? '—',
                    'stageKey'                  => 'at_end_user',
                    'stageLabel'                => 'End User',
                    'stageType'                 => 'signature',
                    'requiresThirdSignerChoice' => false,
                    'waitingSince'              => $pr->updated_at?->format('M d, Y g:i A') ?? '—',
                ])
                ->values()
                ->all());

            $items = array_merge($items, AbstractOfCanvass::with('purchaseRequest.office')
                ->whereHas('purchaseRequest', fn ($q) => $q->where('office_id', $user->office_id))
                ->where('signatory_stage', 'at_end_user')
                ->orderBy('updated_at')
                ->get()
                ->map(fn ($aoc) => [
                    'docType'                   => 'aoc',
                    'docLabel'                  => 'AOC',
                    'id'                        => $aoc->id,
                    'number'                    => $aoc->code ?? ('AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT)),
                    'office'                    => $aoc->purchaseRequest?->office?->code ?? '—',
                    'title'                     => $aoc->purchaseRequest?->title ?? '—',
                    'stageKey'                  => 'at_end_user',
                    'stageLabel'                => 'End User',
                    'stageType'                 => 'signature',
                    'requiresThirdSignerChoice' => false,
                    'waitingSince'              => $aoc->updated_at?->format('M d, Y g:i A') ?? '—',
                ])
                ->values()
                ->all());
        }

        // A user can hold more than one signatory role at once (e.g. a VC who
        // is also Dean of their home college) — collect every role's queue
        // rather than only the first assigned role, so a dual-hat account
        // sees everything they're actually entitled to sign.
        foreach (array_diff($roleCodes, ['office-head']) as $roleCode) {
            // VCAA/VCAF are each the fixed, university-wide signatory for their
            // owned stages — not scoped to division jurisdiction, so no office
            // filter here regardless of which office originated the document.
            if ($roleCode === 'vice-chancellor') {
                $roleCode = $user->vc_type ?? 'vice-chancellor'; // only VCAA/VCAF actually sign
            }

            $roleItems = $queue->forRole($roleCode, null);

            // A BAC user whose specific seat is known only sees the one
            // stage that seat owns — an unset bac_position (e.g. a generic
            // demo account) falls back to seeing all three, same as before.
            if ($roleCode === 'bac' && $user->bac_position) {
                $roleItems = array_values(array_filter(
                    $roleItems,
                    fn ($item) => (self::BAC_STAGE_POSITIONS[$item['stageKey']] ?? null) === $user->bac_position
                ));
            }

            $items = array_merge($items, $roleItems);
        }

        return response()->json(['items' => $items]);
    }

    /**
     * Sign a document from the mobile app. Routing stages are a one-click
     * forward (no attachment needed). Signature stages advance in this same
     * call once at least one attached photo/file is present — the mobile
     * "attach & route" screen only calls this after the signatory has
     * confirmed their attachments and explicitly tapped "Route to Next
     * Signatory", so there's no separate confirm step here.
     */
    public function sign(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc       = $this->resolveDoc($docType, $id);
        $user      = auth()->user();
        $roleCodes = $user->roles()->pluck('code')->all();

        if (!$this->userOwnsStage($doc, $roleCodes, $user)) {
            return response()->json(['error' => 'This document is not at your signature stage.'], 403);
        }

        $stageMeta = $doc->stageMetaFor($doc->signatory_stage);
        $isRouting = ($stageMeta['type'] ?? 'signature') === 'routing';

        if ($isRouting) {
            $result = $signatory->advance($doc, $request->input('remarks'));
            return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
        }

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|mimes:jpeg,jpg,png,pdf|max:10240',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $result = $signatory->signWithAttachments(
            $doc,
            $request->file('files'),
            $request->input('remarks'),
            $request->input('third_signer'),
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

        // Computed live from the document's current stage (same rule
        // SignatoryQueueService::row() uses for /my-signatures), rather than
        // trusted from a route param the mobile client carries between screens —
        // a stale/omitted param here was exactly what let a PR advance off
        // at_vice_chancellor (e.g. after Accounting returns it) without ever
        // collecting the required third_signer choice, which the backend then
        // rejects when routing to the next signatory.
        $requiresThirdSignerChoice = $docType === 'pr' && $doc->nextSignatoryStage() === 'at_third_sign';

        $response = [
            'id'                          => $doc->id,
            'docType'                     => $docType,
            'signatory_stage'             => $doc->signatory_stage,
            'stage_label'                 => $doc->stageMetaFor($doc->signatory_stage)['label'] ?? $doc->signatory_stage,
            'chain'                       => $chain,
            'requires_third_signer_choice' => $requiresThirdSignerChoice,
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
                'logs'         => $this->signatureLogsFor($doc, $allStages),
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
                'logs'         => $this->signatureLogsFor($doc, $allStages),
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
                'logs'        => $this->signatureLogsFor($doc, $allStages),
            ]);
        }

        return response()->json($response);
    }

    /**
     * Signature history + attachments for a PR/AOC/PO — shared across all three
     * doc types since they all expose the same signatoryStages()/stageMetaFor()
     * contract and an identically-shaped SignatureLog relation. Every log entry
     * (not just the latest) is included, each carrying its own attachments, so a
     * document with multiple signatories shows every one of their uploads.
     */
    private function signatureLogsFor(Model $doc, array $allStages): array
    {
        return $doc->signatureLogs()
            ->with(['signedBy', 'attachments'])
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
                    'attachments'      => $log->attachments->map(fn ($a) => [
                        'filename' => $a->original_filename,
                        'is_image' => str_starts_with($a->mime_type ?? '', 'image/'),
                        'url'      => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'signature-attachment.show', now()->addDay(), ['id' => $a->id]
                        ),
                    ])->all(),
                ];
            })->values()->all();
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

    /**
     * A user can hold more than one signatory role at once (e.g. a VC who is
     * also Dean of their home college) — checked against every role they
     * hold, not just the first one assigned.
     *
     * @param string[] $roleCodes
     */
    private function userOwnsStage(Model $doc, array $roleCodes, $user): bool
    {
        foreach ($roleCodes as $roleCode) {
            // Office-head ownership is always office-scoped and never
            // expressible via the generic stageOwnerRole() check below (it
            // has no concept of "whose office") — for both PR and AOC (AOC
            // has its own independent at_end_user stage), so this never
            // falls through to the generic check, which would otherwise
            // wrongly grant any office-head access regardless of office.
            if ($roleCode === 'office-head') {
                if ($doc instanceof PurchaseRequest
                    && $doc->signatory_stage === 'at_end_user'
                    && $doc->office_id === $user->office_id
                ) {
                    return true;
                }
                if ($doc instanceof AbstractOfCanvass
                    && $doc->signatory_stage === 'at_end_user'
                    && $doc->purchaseRequest?->office_id === $user->office_id
                ) {
                    return true;
                }
                continue;
            }

            // A BAC user whose specific seat is known may only act at the one
            // stage that seat owns — an unset bac_position falls back to the
            // old any-of-the-three behavior.
            if ($roleCode === 'bac' && $user->bac_position) {
                $requiredPosition = self::BAC_STAGE_POSITIONS[$doc->signatory_stage] ?? null;
                if ($doc->stageOwnerRole($doc->signatory_stage) === 'bac' && $requiredPosition === $user->bac_position) {
                    return true;
                }
                continue;
            }

            $resolved = $roleCode === 'vice-chancellor'
                ? ($user->vc_type ?? 'vice-chancellor') // only VCAA/VCAF actually sign
                : $roleCode;

            if ($doc->stageOwnerRole($doc->signatory_stage) === $resolved) {
                return true;
            }
        }

        return false;
    }
}
