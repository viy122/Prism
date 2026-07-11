<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Models\AbstractOfCanvass;
use App\Models\AocSignatureLog;
use App\Services\SignatoryQueueService;
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
                'office'  => $log->abstractOfCanvass->purchaseRequest?->office?->name ?? '—',
                'display' => $log->abstractOfCanvass->describeSignatureLog($log),
                'by'      => $log->signedBy?->name ?? '—',
                'at'      => $log->signed_at?->format('M d, Y g:i A') ?? '—',
            ])
            ->values()
            ->all();

        $aocsInBacStages = AbstractOfCanvass::whereIn('signatory_stage', ['at_bac_member', 'at_bac_vice_chair', 'at_bac_chair'])->count();
        $aocsFullySigned = AbstractOfCanvass::where('signatory_stage', 'fully_signed')->count();

        return view('prism.bac.dashboard', $this->withCommon('dashboard', [
            'pageTitle'       => 'BAC Dashboard',
            'summary'         => [
                'awaitingMySignature' => $awaiting,
                'aocsInBacStages'     => $aocsInBacStages,
                'aocsFullySigned'     => $aocsFullySigned,
            ],
            'recentActivity'  => $recentActivity,
        ]));
    }

    public function forMySignature(): View
    {
        return view('prism.shared.for-my-signature', $this->withCommon('for-my-signature', [
            'pageTitle' => 'For My Signature',
            'queueRows' => $this->signatureQueueRows(),
        ]));
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
