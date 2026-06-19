<?php

namespace App\Http\Controllers;

use App\Models\BudgetProposalItem;
use App\Models\Office;
use App\Models\ProcurementStatusUpdate;
use App\Models\PurchaseRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrismProcurementOfficeController extends Controller
{
    public function dashboard(): View
    {
        $totalPrsReceived      = PurchaseRequest::count();
        $prsInProgress         = PurchaseRequest::whereIn('status', ['in_progress', 'pending'])->count();
        $prsCompletedThisMonth = PurchaseRequest::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $overduePrs = PurchaseRequest::where('status', 'delayed')->count();

        $officeStatusGroups = Office::has('purchaseRequests')
            ->with('purchaseRequests')
            ->get()
            ->map(fn ($office) => [
                'office'     => $office->name,
                'completed'  => $office->purchaseRequests->where('status', 'completed')->count(),
                'inProgress' => $office->purchaseRequests->where('status', 'in_progress')->count(),
                'pending'    => $office->purchaseRequests->where('status', 'pending')->count(),
            ])
            ->filter(fn ($g) => $g['completed'] + $g['inProgress'] + $g['pending'] > 0)
            ->values()
            ->all();

        $urgentPrs = PurchaseRequest::with('office')
            ->whereIn('status', ['pending', 'in_progress', 'delayed'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($pr) => [
                'office'        => $pr->office?->name ?? '—',
                'prNumber'      => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'item'          => $pr->title,
                'targetQuarter' => '—',
                'dueDate'       => $pr->submitted_at?->format('M d, Y') ?? '—',
                'status'        => ucfirst(str_replace('_', ' ', $pr->status)),
                'dueThisMonth'  => $pr->submitted_at?->isCurrentMonth() ?? false,
            ])
            ->all();

        return view('prism.procurement-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Procurement Office Dashboard',
            'summary'   => [
                'totalPrsReceived'      => $totalPrsReceived,
                'prsInProgress'         => $prsInProgress,
                'prsCompletedThisMonth' => $prsCompletedThisMonth,
                'overduePrs'            => $overduePrs,
            ],
            'officeStatusGroups' => $officeStatusGroups,
            'urgentPrs'          => $urgentPrs,
        ]));
    }

    public function purchaseRequestManagement(): View
    {
        $prs = PurchaseRequest::with(['office', 'statusUpdates' => fn ($q) => $q->latest()])
            ->latest()
            ->get()
            ->map(fn ($pr) => [
                'id'            => $pr->id,
                'office'        => $pr->office?->name ?? '—',
                'prNumber'      => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'item'          => $pr->title,
                'dateSubmitted' => $pr->submitted_at?->format('M d, Y') ?? $pr->created_at->format('M d, Y'),
                'currentStatus' => ucfirst(str_replace('_', ' ', $pr->status)),
                'pdfFile'       => $pr->file_path,
                'remarks'       => $pr->remarks ?? '—',
                'ocr'           => $pr->extracted_fields_json ?? [],
                'activityLog'   => $pr->statusUpdates->map(fn ($u) => [
                    'timestamp' => $u->created_at->format('M d, Y g:i A'),
                    'status'    => ucfirst(str_replace('_', ' ', $u->status)),
                    'remarks'   => $u->remarks ?? '—',
                ])->all(),
                'updateUrl'     => route('procurement-office.purchase-request.update-status', $pr->id),
            ])
            ->all();

        return view('prism.procurement-office.purchase-request-management', $this->withCommon('purchase-request-management', [
            'pageTitle'        => 'Purchase Request Management',
            'purchaseRequests' => $prs,
        ]));
    }

    public function updatePrStatus(Request $request, PurchaseRequest $pr): JsonResponse
    {
        $request->validate([
            'status'  => 'required|in:pending,in_progress,completed,delayed',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $pr->update(['status' => $request->input('status'), 'remarks' => $request->input('remarks')]);

        ProcurementStatusUpdate::create([
            'purchase_request_id' => $pr->id,
            'updated_by_user_id'  => auth()->user()?->id,
            'status'              => $request->input('status'),
            'remarks'             => $request->input('remarks', ''),
        ]);

        NotificationService::prStatusUpdated($pr);

        return response()->json(['success' => true]);
    }

    public function procurementStatusTracking(): View
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id'             => $item->id,
                'office'         => $item->budgetProposal?->office?->name ?? '—',
                'item'           => $item->name,
                'approvedAmount' => (float) $item->estimated_total_cost,
                'targetQuarter'  => $item->target_quarter ?? '—',
                'currentStatus'  => 'Pending',
                'remarks'        => $item->remarks ?? '—',
            ])
            ->all();

        return view('prism.procurement-office.procurement-status-tracking', $this->withCommon('procurement-status-tracking', [
            'pageTitle'     => 'Procurement Status Tracking',
            'trackingItems' => $items,
            'offices'       => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters'      => collect($items)->pluck('targetQuarter')->filter(fn ($q) => $q !== '—')->unique()->sort()->values()->all(),
            'statuses'      => ['Pending', 'In Progress', 'Completed', 'Delayed'],
        ]));
    }

    public function procurementReports(): View
    {
        $offices = Office::has('purchaseRequests')
            ->with('purchaseRequests')
            ->get();

        $quarterlyRows = $offices->flatMap(function ($office) {
            $total    = $office->purchaseRequests->count();
            $procured = $office->purchaseRequests->where('status', 'completed')->count();
            $rate     = $total > 0 ? round(($procured / $total) * 100) : 0;

            return collect(['Q1', 'Q2', 'Q3', 'Q4'])->map(fn ($q) => [
                'office'         => $office->name,
                'quarter'        => $q,
                'targeted'       => (int) ceil($total / 4),
                'procured'       => (int) floor($procured / 4),
                'completionRate' => $rate,
            ])->all();
        })->filter(fn ($r) => $r['targeted'] > 0)->values()->all();

        $completedPurchases = PurchaseRequest::with('office')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(fn ($pr) => [
                'office'        => $pr->office?->name ?? '—',
                'item'          => $pr->title,
                'prNumber'      => $pr->number ?? '—',
                'completedDate' => $pr->updated_at->format('M d, Y'),
                'amount'        => (float) $pr->total_amount,
            ])
            ->all();

        $delayedItems = PurchaseRequest::with('office')
            ->where('status', 'delayed')
            ->latest()
            ->get()
            ->map(fn ($pr) => [
                'office'   => $pr->office?->name ?? '—',
                'item'     => $pr->title,
                'prNumber' => $pr->number ?? '—',
                'reason'   => $pr->remarks ?? 'No remarks provided.',
            ])
            ->all();

        return view('prism.procurement-office.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle'          => 'Procurement Reports',
            'quarterlyRows'      => $quarterlyRows,
            'completedPurchases' => $completedPurchases,
            'delayedItems'       => $delayedItems,
        ]));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function withCommon(string $activeProcurementPage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'procurement-office',
            'activeModulePage' => $activeProcurementPage,
            'brandHref'        => route('procurement-office.dashboard'),
            'roleLabel'        => 'Procurement Office',
            'roleInitials'     => 'PO',
            'roleNavigation'   => [
                ['slug' => 'office-head',        'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office',     'label' => 'Finance Office',      'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office',  'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor',         'label' => 'Chancellor',           'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor',    'label' => 'Vice Chancellor',      'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel'   => 'Procurement Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',                   'label' => 'Dashboard',                  'href' => route('procurement-office.dashboard'),                   'icon' => 'layout-dashboard'],
                ['slug' => 'purchase-request-management', 'label' => 'Purchase Request Management', 'href' => route('procurement-office.purchase-request-management'), 'icon' => 'receipt'],
                ['slug' => 'procurement-status-tracking', 'label' => 'Procurement Status Tracking', 'href' => route('procurement-office.procurement-status-tracking'), 'icon' => 'list-check'],
                ['slug' => 'procurement-reports',         'label' => 'Procurement Reports',         'href' => route('procurement-office.procurement-reports'),         'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
