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

    public function signDocument(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc = $this->resolveSignableDoc($docType, $id);
        $this->authorizeStageOwnership($doc);

        // Routing stages (e.g. VC review on a PO) are a one-click forward
        if (($doc->stageMetaFor($doc->signatory_stage)['type'] ?? 'signature') === 'routing') {
            $result = $signatory->advance($doc, $request->input('remarks'));
            return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
        }

        $rules = [
            'photo'   => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'remarks' => 'nullable|string|max:1000',
        ];
        if ($doc instanceof PurchaseRequest && $doc->nextSignatoryStage() === 'at_third_sign') {
            $rules['third_signer'] = 'required|in:accounting,vice_chancellor';
        }
        $request->validate($rules);

        $result = $signatory->signWithPhoto(
            $doc,
            $request->file('photo'),
            $request->input('remarks'),
            $request->input('third_signer'),
        );

        return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
    }

    public function confirmSignDocument(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc = $this->resolveSignableDoc($docType, $id);
        $this->authorizeStageOwnership($doc);

        $rules = [
            'photo_path' => 'required|string|max:500',
            'boxes'      => 'nullable|array|max:6',
            'boxes.*.x'  => 'required|numeric|min:0|max:1',
            'boxes.*.y'  => 'required|numeric|min:0|max:1',
            'boxes.*.w'  => 'required|numeric|min:0|max:1',
            'boxes.*.h'  => 'required|numeric|min:0|max:1',
            'remarks'    => 'nullable|string|max:1000',
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
        }, $queue->forRole($this->queueRoleCode()));
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
