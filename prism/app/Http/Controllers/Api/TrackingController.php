<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbstractOfCanvass;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    public function myDocuments(): JsonResponse
    {
        $user     = auth()->user();
        $uid      = $user->id;
        $officeId = $user->office_id;
        $roleCode = $user->roles()->first()?->code;

        // Roles with school-wide visibility see all in-pipeline documents.
        // 'draft' excluded — office heads push to pipeline on the web.
        $schoolWide = in_array($roleCode, ['chancellor', 'vice-chancellor', 'accounting-office', 'procurement-office', 'bac'], true);

        $items = collect()
            ->concat($this->purchaseRequests($schoolWide, $uid, $officeId))
            ->concat($this->abstracts($schoolWide, $uid, $officeId))
            ->concat($this->purchaseOrders($schoolWide, $uid, $officeId))
            ->sortByDesc('last_activity')
            ->values();

        return response()->json(['items' => $items]);
    }

    /**
     * Chain progress as [signed steps, total signable steps] — draft and
     * fully_signed bookends excluded from the count.
     */
    private function chainProgress($doc): array
    {
        $total = count($doc::signatoryStages()) - 2;
        $done  = max(0, min($doc->stageIndex($doc->signatory_stage) - 1, $total));
        if ($doc->signatory_stage === 'fully_signed') {
            $done = $total;
        }
        return [$done, $total];
    }

    private function purchaseRequests(bool $schoolWide, int $uid, ?int $officeId)
    {
        $query = PurchaseRequest::with([
            'office',
            'signatureLogs' => fn ($q) => $q->latest('signed_at')->with('signedBy'),
        ]);

        if ($schoolWide) {
            $query->where('signatory_stage', '!=', 'draft');
        } else {
            $query->where(function ($q) use ($uid, $officeId) {
                $q->where('created_by_user_id', $uid);
                if ($officeId) {
                    $q->orWhere('office_id', $officeId);
                }
                $q->orWhereHas('signatureLogs', fn ($q2) => $q2->where('signed_by_user_id', $uid));
            });
        }

        return $query->latest('updated_at')->get()->map(function (PurchaseRequest $pr) {
            $latestLog = $pr->signatureLogs->first();
            $stageMeta = $pr->stageMetaFor($pr->signatory_stage);
            [$done, $total] = $this->chainProgress($pr);

            return [
                'docType'         => 'pr',
                'steps_done'      => $done,
                'steps_total'     => $total,
                'id'              => $pr->id,
                'number'          => $pr->number ?? ('PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT)),
                'title'           => $pr->title ?? '—',
                'office'          => $pr->office?->name ?? '—',
                'signatory_stage' => $pr->signatory_stage,
                'stage_label'     => $stageMeta['label'] ?? $pr->signatory_stage,
                'total_amount'    => (float) $pr->total_amount,
                'last_activity'   => $latestLog?->signed_at?->toIso8601String() ?? $pr->updated_at?->toIso8601String(),
                'last_actor'      => $latestLog?->signedBy?->name,
            ];
        });
    }

    private function abstracts(bool $schoolWide, int $uid, ?int $officeId)
    {
        $query = AbstractOfCanvass::with([
            'purchaseRequest.office',
            'signatureLogs' => fn ($q) => $q->latest('signed_at')->with('signedBy'),
        ]);

        if ($schoolWide) {
            $query->where('signatory_stage', '!=', 'draft');
        } else {
            $query->where(function ($q) use ($uid, $officeId) {
                $q->whereHas('signatureLogs', fn ($q2) => $q2->where('signed_by_user_id', $uid));
                if ($officeId) {
                    $q->orWhereHas('purchaseRequest', fn ($q2) => $q2->where('office_id', $officeId));
                }
            });
        }

        return $query->latest('updated_at')->get()->map(function (AbstractOfCanvass $aoc) {
            $latestLog = $aoc->signatureLogs->first();
            $stageMeta = $aoc->stageMetaFor($aoc->signatory_stage);
            $pr        = $aoc->purchaseRequest;
            [$done, $total] = $this->chainProgress($aoc);

            return [
                'docType'         => 'aoc',
                'steps_done'      => $done,
                'steps_total'     => $total,
                'id'              => $aoc->id,
                'number'          => $aoc->code ?? ('AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT)),
                'title'           => $pr?->title ?? '—',
                'office'          => $pr?->office?->name ?? '—',
                'signatory_stage' => $aoc->signatory_stage,
                'stage_label'     => $stageMeta['label'] ?? $aoc->signatory_stage,
                'total_amount'    => (float) ($pr?->total_amount ?? 0),
                'last_activity'   => $latestLog?->signed_at?->toIso8601String() ?? $aoc->updated_at?->toIso8601String(),
                'last_actor'      => $latestLog?->signedBy?->name,
            ];
        });
    }

    private function purchaseOrders(bool $schoolWide, int $uid, ?int $officeId)
    {
        $query = PurchaseOrder::with([
            'abstractOfCanvass.purchaseRequest.office',
            'signatureLogs' => fn ($q) => $q->latest('signed_at')->with('signedBy'),
        ]);

        if ($schoolWide) {
            $query->where('signatory_stage', '!=', 'draft');
        } else {
            $query->where(function ($q) use ($uid, $officeId) {
                $q->whereHas('signatureLogs', fn ($q2) => $q2->where('signed_by_user_id', $uid));
                if ($officeId) {
                    $q->orWhereHas('abstractOfCanvass.purchaseRequest', fn ($q2) => $q2->where('office_id', $officeId));
                }
            });
        }

        return $query->latest('updated_at')->get()->map(function (PurchaseOrder $po) {
            $latestLog = $po->signatureLogs->first();
            $pr        = $po->abstractOfCanvass?->purchaseRequest;

            // Once fully signed, the PO's delivery status is more informative
            // than the signatory stage.
            if ($po->signatory_stage === 'fully_signed' && $po->status) {
                $stageLabel = $po->status_label;
            } else {
                $stageLabel = $po->stageMetaFor($po->signatory_stage)['label'] ?? $po->signatory_stage;
            }

            [$done, $total] = $this->chainProgress($po);

            return [
                'docType'         => 'po',
                'steps_done'      => $done,
                'steps_total'     => $total,
                'id'              => $po->id,
                'number'          => $po->po_number ?? ('PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT)),
                'title'           => ($pr?->title ?? '—') . ($po->supplier_name ? ' — ' . $po->supplier_name : ''),
                'office'          => $pr?->office?->name ?? '—',
                'signatory_stage' => $po->signatory_stage,
                'stage_label'     => $stageLabel,
                'total_amount'    => (float) $po->total_amount,
                'last_activity'   => $latestLog?->signed_at?->toIso8601String() ?? $po->updated_at?->toIso8601String(),
                'last_actor'      => $latestLog?->signedBy?->name,
            ];
        });
    }
}
