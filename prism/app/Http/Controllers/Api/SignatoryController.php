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

        return response()->json(['items' => $queue->forRole($roleCode)]);
    }

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

        $rules = ['photo' => 'required|image|mimes:jpeg,jpg,png|max:10240', 'remarks' => 'nullable|string|max:1000'];
        if ($docType === 'pr' && $doc->nextSignatoryStage() === 'at_third_sign') {
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

    public function confirm(Request $request, string $docType, int $id, SignatoryActionService $signatory): JsonResponse
    {
        $doc      = $this->resolveDoc($docType, $id);
        $user     = auth()->user();
        $roleCode = $user->roles()->first()?->code;

        if (!$this->userOwnsStage($doc, $roleCode, $user)) {
            return response()->json(['error' => 'This document is not at your signature stage.'], 403);
        }

        $rules = ['photo_path' => 'required|string|max:500', 'remarks' => 'nullable|string|max:1000'];
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
        );

        return response()->json($result, isset($result['error']) ? ($result['status'] ?? 422) : 200);
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

        return $doc->stageOwnerRole($doc->signatory_stage) === $roleCode;
    }
}
