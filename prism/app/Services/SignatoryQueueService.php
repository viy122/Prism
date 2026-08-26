<?php

namespace App\Services;

use App\Models\AbstractOfCanvass;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;

/**
 * Builds the "For My Signature" queue for a signatory role by collecting
 * PR / AOC / PO documents whose current stage is owned by that role
 * (see HasSignatoryChain::stageOwnerRole()).
 */
class SignatoryQueueService
{
    /**
     * @param  array<int>|null  $officeIds  when set, only documents originating
     *                                      from these offices are included
     *                                      (used to scope VC queues to their
     *                                      assigned jurisdiction)
     * @return array<int, array<string, mixed>> uniform queue rows, oldest first
     */
    public function forRole(string $roleCode, ?array $officeIds = null): array
    {
        $rows = [];

        PurchaseRequest::with('office')
            ->whereNotIn('signatory_stage', ['draft', 'fully_signed'])
            ->when($officeIds, fn ($q, $ids) => $q->whereIn('office_id', $ids))
            ->orderBy('updated_at')
            ->get()
            ->filter(fn ($pr) => $pr->stageOwnerRole($pr->signatory_stage) === $roleCode)
            ->each(function ($pr) use (&$rows) {
                $rows[] = $this->row($pr, 'pr', $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT), $pr->office?->code, $pr->title);
            });

        AbstractOfCanvass::with('purchaseRequest.office')
            ->whereNotIn('signatory_stage', ['draft', 'fully_signed'])
            ->when($officeIds, fn ($q, $ids) => $q->whereHas('purchaseRequest', fn ($q2) => $q2->whereIn('office_id', $ids)))
            ->orderBy('updated_at')
            ->get()
            ->filter(fn ($aoc) => $aoc->stageOwnerRole($aoc->signatory_stage) === $roleCode)
            ->each(function ($aoc) use (&$rows) {
                $rows[] = $this->row(
                    $aoc,
                    'aoc',
                    $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
                    $aoc->purchaseRequest?->office?->code,
                    $aoc->purchaseRequest?->title
                );
            });

        PurchaseOrder::with('abstractOfCanvass.purchaseRequest.office')
            ->whereNotIn('signatory_stage', ['draft', 'fully_signed'])
            ->when($officeIds, fn ($q, $ids) => $q->whereHas('abstractOfCanvass.purchaseRequest', fn ($q2) => $q2->whereIn('office_id', $ids)))
            ->orderBy('updated_at')
            ->get()
            ->filter(fn ($po) => $po->stageOwnerRole($po->signatory_stage) === $roleCode)
            ->each(function ($po) use (&$rows) {
                $pr = $po->abstractOfCanvass?->purchaseRequest;
                $rows[] = $this->row(
                    $po,
                    'po',
                    $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                    $pr?->office?->code,
                    ($pr?->title ?? '—') . ' — ' . $po->supplier_name
                );
            });

        return $rows;
    }

    public function countForRole(string $roleCode, ?array $officeIds = null): int
    {
        return count($this->forRole($roleCode, $officeIds));
    }

    private function row($doc, string $docType, string $number, ?string $office, ?string $title): array
    {
        $stageMeta = $doc->stageMetaFor($doc->signatory_stage);
        $next      = $doc->nextSignatoryStage();

        return [
            'docType'          => $docType,
            'docLabel'         => strtoupper($docType),
            'id'               => $doc->id,
            'number'           => $number,
            'office'           => $office ?? '—',
            'title'            => $title ?? '—',
            'stageKey'         => $doc->signatory_stage,
            'stageLabel'       => $stageMeta['label'] ?? $doc->signatory_stage,
            'stageType'        => $stageMeta['type'] ?? 'signature',
            'nextStageLabel'   => $doc->stageMetaFor($next)['label'] ?? null,
            'requiresThirdSignerChoice' => $docType === 'pr' && $next === 'at_third_sign',
            'waitingSince'     => $doc->updated_at?->format('M d, Y g:i A') ?? '—',
        ];
    }
}
