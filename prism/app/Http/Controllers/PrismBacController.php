<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Models\AbstractOfCanvass;
use App\Models\AocSignatureLog;
use App\Services\SignatoryQueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PrismBacController extends Controller
{
    use HandlesSignatureQueue;

    protected function queueRoleCode(): string
    {
        return 'bac';
    }

    protected function queueRoutePrefix(): string
    {
        return 'bac';
    }

    private const BAC_STAGES = ['at_bac_member', 'at_bac_vice_chair', 'at_bac_chair'];

    public function dashboard(SignatoryQueueService $queue): View
    {
        $awaiting = $queue->countForRole('bac');
        $signatureQueueRows = $this->signatureQueueRows();

        $recentActivity = AocSignatureLog::with(['abstractOfCanvass.purchaseRequest.office', 'signedBy'])
            ->latest('signed_at')
            ->take(10)
            ->get()
            ->filter(fn ($log) => $log->abstractOfCanvass !== null)
            ->map(fn ($log) => [
                'code'    => $log->abstractOfCanvass->code ?? 'AOC-' . str_pad($log->abstractOfCanvass->id, 4, '0', STR_PAD_LEFT),
                'office'  => $log->abstractOfCanvass->purchaseRequest?->office?->code ?? '—',
                'display' => $log->abstractOfCanvass->describeSignatureLog($log),
                'by'      => $log->signedBy?->name ?? '—',
                'at'      => $log->signed_at?->format('M d, Y g:i A') ?? '—',
            ])
            ->values()
            ->all();

        // Every AOC currently sitting at a BAC stage — the base set for all
        // the "what's actually on our plate" figures below (value, per-office
        // breakdown, per-stage breakdown, and how long each has been waiting).
        $pending = AbstractOfCanvass::with('purchaseRequest.office')
            ->whereIn('signatory_stage', self::BAC_STAGES)
            ->get();

        $aocsInBacStages   = $pending->count();
        $aocsFullySigned   = AbstractOfCanvass::where('signatory_stage', 'fully_signed')->count();
        $totalValuePending = (float) $pending->sum(fn ($aoc) => $aoc->purchaseRequest?->total_amount ?? 0);
        $avgDaysPending    = $pending->isNotEmpty()
            ? round($pending->avg(fn ($aoc) => $aoc->updated_at->diffInDays(now())), 1)
            : 0;

        $fullySigned = AbstractOfCanvass::with('purchaseRequest.office')
            ->where('signatory_stage', 'fully_signed')
            ->latest('updated_at')
            ->get();

        $stageLabels = ['at_bac_member' => 'Member', 'at_bac_vice_chair' => 'Vice Chairperson', 'at_bac_chair' => 'Chairperson'];
        $byStage = collect(self::BAC_STAGES)->map(fn ($stage) => [
            'stage' => $stageLabels[$stage],
            'count' => $pending->where('signatory_stage', $stage)->count(),
        ])->all();

        $byOffice = $pending
            ->groupBy(fn ($aoc) => $aoc->purchaseRequest?->office?->code ?? '—')
            ->map(fn ($group, $office) => ['office' => $office, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values()
            ->all();

        // Oldest-waiting AOCs across all BAC stages — the ones most likely to
        // need follow-up, regardless of which specific stage they're stuck at.
        $oldestPending = $pending
            ->sortBy('updated_at')
            ->take(5)
            ->map(fn ($aoc) => [
                'code'        => $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
                'office'      => $aoc->purchaseRequest?->office?->code ?? '—',
                'stage'       => $stageLabels[$aoc->signatory_stage] ?? $aoc->signatory_label,
                'daysWaiting' => (int) $aoc->updated_at->diffInDays(now()),
            ])
            ->values()
            ->all();

        $kpiDetails = $this->dashboardKpiDetails($signatureQueueRows, $pending, $fullySigned, $stageLabels);

        return view('prism.bac.dashboard', $this->withCommon('dashboard', [
            'pageTitle'       => 'BAC Dashboard',
            'summary'         => [
                'awaitingMySignature' => $awaiting,
                'aocsInBacStages'     => $aocsInBacStages,
                'aocsFullySigned'     => $aocsFullySigned,
                'totalValuePending'   => $totalValuePending,
                'avgDaysPending'      => $avgDaysPending,
            ],
            'stageChart'      => $byStage,
            'officeChart'     => $byOffice,
            'oldestPending'   => $oldestPending,
            'recentActivity'  => $recentActivity,
            'kpiDetails'      => $kpiDetails,
        ]));
    }

    /** BAC only ever signs AOC (Member, Vice Chair, Chair) — shows EVERY AOC, not just currently-actionable ones. */
    public function forMySignature(): View
    {
        return view('prism.shared.for-my-signature', $this->withCommon('for-my-signature', [
            'pageTitle' => 'For My Signature',
            'documents' => $this->signatureHistoryRows($this->signatureDocTypes()),
            'refreshUrl' => route($this->queueRoutePrefix() . '.for-my-signature.refresh'),
        ]));
    }

    public function forMySignatureRefresh(): JsonResponse
    {
        return $this->signatureHistoryJson($this->signatureDocTypes());
    }

    private function signatureDocTypes(): array
    {
        return ['aoc'];
    }

    private function dashboardKpiDetails(array $signatureQueueRows, $pending, $fullySigned, array $stageLabels): array
    {
        $pendingRows = $pending
            ->map(fn (AbstractOfCanvass $aoc) => $this->dashboardAocKpiRow($aoc, $stageLabels))
            ->values();

        $awaitingRows = collect($signatureQueueRows)
            ->where('docType', 'aoc')
            ->map(fn ($row) => [
                'title'       => ($row['number'] ?? 'AOC') . ' - ' . ($row['title'] ?? 'Untitled AOC'),
                'code'        => $row['number'] ?? 'AOC',
                'office'      => $row['office'] ?? '-',
                'stage'       => $row['stageLabel'] ?? 'BAC',
                'amount'      => null,
                'daysWaiting' => null,
                'dateLabel'   => 'Waiting since',
                'date'        => $row['waitingSince'] ?? '-',
                'statusClass' => 'badge-stage',
                'remarks'     => 'Requires your signature at this BAC stage.',
                'url'         => route('bac.for-my-signature'),
                'urlLabel'    => 'Open queue',
            ])
            ->values();

        $fullySignedRows = $fullySigned
            ->map(fn (AbstractOfCanvass $aoc) => $this->dashboardAocKpiRow($aoc, $stageLabels, 'Fully Signed', 'badge-days-ok', 'Completed'))
            ->values();

        return [
            'awaitingMySignature' => [
                'title'      => 'Awaiting My Signature',
                'lead'       => 'AOCs currently requiring action at a BAC signature stage.',
                'rows'       => $awaitingRows->all(),
                'empty'      => 'No AOCs are waiting for your signature.',
                'countLabel' => 'AOC(s) to sign',
            ],
            'aocsInBacStages' => [
                'title'      => 'AOCs In BAC Stages',
                'lead'       => 'All AOCs currently moving through BAC Member, Vice Chairperson, or Chairperson review.',
                'rows'       => $pendingRows->all(),
                'empty'      => 'No AOCs are currently in BAC stages.',
                'countLabel' => 'pending AOC(s)',
            ],
            'totalValuePending' => [
                'title'      => 'Total Value Pending',
                'lead'       => 'Pending BAC-stage AOCs sorted by highest associated PR amount.',
                'rows'       => $pendingRows->sortByDesc('amount')->values()->all(),
                'empty'      => 'No pending value at BAC stages.',
                'countLabel' => 'value source(s)',
            ],
            'avgDaysPending' => [
                'title'      => 'Average Days Pending',
                'lead'       => 'AOCs contributing to the average wait time, oldest first.',
                'rows'       => $pendingRows->sortByDesc('daysWaiting')->values()->all(),
                'empty'      => 'No AOCs are waiting at BAC stages.',
                'countLabel' => 'waiting AOC(s)',
            ],
            'aocsFullySigned' => [
                'title'      => 'AOCs Fully Signed',
                'lead'       => 'AOCs already cleared through all BAC stages and completed in the signatory chain.',
                'rows'       => $fullySignedRows->all(),
                'empty'      => 'No fully signed AOCs yet.',
                'countLabel' => 'signed AOC(s)',
            ],
        ];
    }

    private function dashboardAocKpiRow(AbstractOfCanvass $aoc, array $stageLabels, ?string $stageOverride = null, string $statusClass = 'badge-stage', string $dateLabel = 'Waiting'): array
    {
        $amount = (float) ($aoc->purchaseRequest?->total_amount ?? 0);
        $daysWaiting = $aoc->updated_at ? (int) $aoc->updated_at->diffInDays(now()) : 0;

        return [
            'title'       => ($aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT)) . ' - ' . ($aoc->purchaseRequest?->title ?? 'Untitled AOC'),
            'code'        => $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
            'office'      => $aoc->purchaseRequest?->office?->code ?? '-',
            'stage'       => $stageOverride ?? ($stageLabels[$aoc->signatory_stage] ?? $aoc->signatory_label),
            'amount'      => $amount,
            'daysWaiting' => $daysWaiting,
            'dateLabel'   => $dateLabel,
            'date'        => $aoc->updated_at?->format('M d, Y') ?? '-',
            'statusClass' => $statusClass,
            'remarks'     => $daysWaiting . ' ' . \Illuminate\Support\Str::plural('day', $daysWaiting) . ' since last movement.',
            'url'         => route('bac.for-my-signature'),
            'urlLabel'    => 'View AOC',
        ];
    }

    private function withCommon(string $activePage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'bac',
            'activeModulePage' => $activePage,
            'brandHref'        => route('bac.dashboard'),
            'roleLabel'        => 'Bids and Awards Committee',
            'roleInitials'     => 'BAC',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'BAC pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',        'label' => 'Dashboard',        'href' => route('bac.dashboard'),        'icon' => 'layout-dashboard'],
                ['slug' => 'for-my-signature', 'label' => 'For My Signature', 'href' => route('bac.for-my-signature'), 'icon' => 'signature'],
            ],
        ], $data);
    }
}
