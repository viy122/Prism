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
