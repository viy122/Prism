<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrismProcurementOfficeController extends Controller
{
    public function dashboard(): View
    {
        return view('prism.procurement-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Procurement Office Dashboard',
            'summary' => [
                'totalPrsReceived' => 38,
                'prsInProgress' => 17,
                'prsCompletedThisMonth' => 12,
                'overduePrs' => 5,
            ],
            'officeStatusGroups' => [
                ['office' => 'College of Engineering', 'completed' => 4, 'inProgress' => 5, 'pending' => 2],
                ['office' => 'College of Science', 'completed' => 3, 'inProgress' => 4, 'pending' => 3],
                ['office' => 'University Library', 'completed' => 2, 'inProgress' => 1, 'pending' => 1],
                ['office' => 'Health Services', 'completed' => 1, 'inProgress' => 3, 'pending' => 2],
                ['office' => 'Student Affairs', 'completed' => 2, 'inProgress' => 4, 'pending' => 1],
            ],
            'urgentPrs' => [
                ['office' => 'College of Engineering', 'prNumber' => 'PR-2026-ENG-021', 'item' => 'Electronics laboratory instruments', 'targetQuarter' => 'Q1', 'dueDate' => 'May 30, 2026', 'status' => 'Delayed', 'dueThisMonth' => true],
                ['office' => 'Health Services', 'prNumber' => 'PR-2026-HS-009', 'item' => 'Emergency response medical kits', 'targetQuarter' => 'Q1', 'dueDate' => 'May 29, 2026', 'status' => 'In Progress', 'dueThisMonth' => true],
                ['office' => 'University Library', 'prNumber' => 'PR-2026-LIB-006', 'item' => 'Digital database renewal', 'targetQuarter' => 'Q1', 'dueDate' => 'June 4, 2026', 'status' => 'Pending', 'dueThisMonth' => false],
                ['office' => 'College of Science', 'prNumber' => 'PR-2026-SCI-017', 'item' => 'Chemical storage safety cabinets', 'targetQuarter' => 'Q1', 'dueDate' => 'May 31, 2026', 'status' => 'Delayed', 'dueThisMonth' => true],
            ],
        ]));
    }

    public function purchaseRequestManagement(): View
    {
        return view('prism.procurement-office.purchase-request-management', $this->withCommon('purchase-request-management', [
            'pageTitle' => 'Purchase Request Management',
            'purchaseRequests' => $this->purchaseRequests(),
        ]));
    }

    public function procurementStatusTracking(): View
    {
        $items = $this->trackingItems();

        return view('prism.procurement-office.procurement-status-tracking', $this->withCommon('procurement-status-tracking', [
            'pageTitle' => 'Procurement Status Tracking',
            'trackingItems' => $items,
            'offices' => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters' => collect($items)->pluck('targetQuarter')->unique()->values()->all(),
            'statuses' => ['Pending', 'In Progress', 'Completed', 'Delayed'],
        ]));
    }

    public function procurementReports(): View
    {
        return view('prism.procurement-office.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle' => 'Procurement Reports',
            'quarterlyRows' => [
                ['office' => 'College of Engineering', 'quarter' => 'Q2', 'targeted' => 11, 'procured' => 7, 'completionRate' => 64],
                ['office' => 'College of Science', 'quarter' => 'Q2', 'targeted' => 10, 'procured' => 6, 'completionRate' => 60],
                ['office' => 'University Library', 'quarter' => 'Q2', 'targeted' => 4, 'procured' => 3, 'completionRate' => 75],
                ['office' => 'Health Services', 'quarter' => 'Q2', 'targeted' => 6, 'procured' => 2, 'completionRate' => 33],
                ['office' => 'Student Affairs', 'quarter' => 'Q2', 'targeted' => 7, 'procured' => 5, 'completionRate' => 71],
            ],
            'completedPurchases' => [
                ['office' => 'College of Engineering', 'item' => 'Classroom laser projectors', 'prNumber' => 'PR-2026-ENG-019', 'completedDate' => 'May 20, 2026', 'amount' => 1260000],
                ['office' => 'University Library', 'item' => 'Reading room barcode scanners', 'prNumber' => 'PR-2026-LIB-004', 'completedDate' => 'May 18, 2026', 'amount' => 340000],
                ['office' => 'Student Affairs', 'item' => 'Student event audio equipment', 'prNumber' => 'PR-2026-OSA-011', 'completedDate' => 'May 14, 2026', 'amount' => 620000],
            ],
            'delayedItems' => [
                ['office' => 'College of Engineering', 'item' => 'Electronics laboratory instruments', 'prNumber' => 'PR-2026-ENG-021', 'reason' => 'Updated delivery lead time requested from suppliers.'],
                ['office' => 'College of Science', 'item' => 'Chemical storage safety cabinets', 'prNumber' => 'PR-2026-SCI-017', 'reason' => 'Technical specification validation pending safety committee reply.'],
                ['office' => 'Health Services', 'item' => 'Emergency response medical kits', 'prNumber' => 'PR-2026-HS-009', 'reason' => 'Partial supplier stock availability.'],
            ],
        ]));
    }

    private function withCommon(string $activeProcurementPage, array $data): array
    {
        return array_merge([
            'activeRole' => 'procurement-office',
            'activeModulePage' => $activeProcurementPage,
            'brandHref' => route('procurement-office.dashboard'),
            'roleNavigation' => [
                ['slug' => 'office-head', 'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office', 'label' => 'Finance Office', 'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office', 'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor', 'label' => 'Chancellor', 'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor', 'label' => 'Vice Chancellor', 'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel' => 'Procurement Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard', 'label' => 'Dashboard', 'href' => route('procurement-office.dashboard'), 'icon' => 'layout-dashboard'],
                ['slug' => 'purchase-request-management', 'label' => 'Purchase Request Management', 'href' => route('procurement-office.purchase-request-management'), 'icon' => 'receipt-text'],
                ['slug' => 'procurement-status-tracking', 'label' => 'Procurement Status Tracking', 'href' => route('procurement-office.procurement-status-tracking'), 'icon' => 'list-checks'],
                ['slug' => 'procurement-reports', 'label' => 'Procurement Reports', 'href' => route('procurement-office.procurement-reports'), 'icon' => 'chart-no-axes-combined'],
            ],
        ], $data);
    }

    private function purchaseRequests(): array
    {
        return [
            [
                'id' => 'pr-eng-021',
                'office' => 'College of Engineering',
                'prNumber' => 'PR-2026-ENG-021',
                'item' => 'Electronics laboratory instruments',
                'dateSubmitted' => 'May 27, 2026',
                'currentStatus' => 'Delayed',
                'pdfFile' => 'signed-pr-eng-021.pdf',
                'remarks' => 'Procurement Office requested updated delivery lead times.',
                'ocr' => [
                    'prNumber' => 'PR-2026-ENG-021',
                    'date' => 'May 27, 2026',
                    'requestingOffice' => 'College of Engineering',
                    'items' => 'Oscilloscopes, power supplies, signal generators',
                    'amount' => 3400000,
                ],
                'activityLog' => [
                    ['timestamp' => 'May 27, 2026 2:20 PM', 'status' => 'Pending', 'remarks' => 'Signed PR uploaded by Office Head.'],
                    ['timestamp' => 'May 27, 2026 4:05 PM', 'status' => 'Delayed', 'remarks' => 'Updated delivery lead times requested before canvassing.'],
                ],
            ],
            [
                'id' => 'pr-sci-017',
                'office' => 'College of Science',
                'prNumber' => 'PR-2026-SCI-017',
                'item' => 'Chemical storage safety cabinets',
                'dateSubmitted' => 'May 26, 2026',
                'currentStatus' => 'In Progress',
                'pdfFile' => 'signed-pr-sci-017.pdf',
                'remarks' => 'Technical specifications under validation.',
                'ocr' => [
                    'prNumber' => 'PR-2026-SCI-017',
                    'date' => 'May 26, 2026',
                    'requestingOffice' => 'College of Science',
                    'items' => '14 chemical storage safety cabinets',
                    'amount' => 2030000,
                ],
                'activityLog' => [
                    ['timestamp' => 'May 26, 2026 9:42 AM', 'status' => 'Pending', 'remarks' => 'PR received from Finance-endorsed APP.'],
                    ['timestamp' => 'May 27, 2026 10:18 AM', 'status' => 'In Progress', 'remarks' => 'Specification validation assigned to procurement analyst.'],
                ],
            ],
            [
                'id' => 'pr-lib-006',
                'office' => 'University Library',
                'prNumber' => 'PR-2026-LIB-006',
                'item' => 'Digital database renewal',
                'dateSubmitted' => 'May 24, 2026',
                'currentStatus' => 'Pending',
                'pdfFile' => 'signed-pr-lib-006.pdf',
                'remarks' => 'Awaiting direct contracting justification attachment.',
                'ocr' => [
                    'prNumber' => 'PR-2026-LIB-006',
                    'date' => 'May 24, 2026',
                    'requestingOffice' => 'University Library',
                    'items' => 'Digital database subscription renewal',
                    'amount' => 2150000,
                ],
                'activityLog' => [
                    ['timestamp' => 'May 24, 2026 3:50 PM', 'status' => 'Pending', 'remarks' => 'PR uploaded with subscription matrix.'],
                ],
            ],
            [
                'id' => 'pr-eng-019',
                'office' => 'College of Engineering',
                'prNumber' => 'PR-2026-ENG-019',
                'item' => 'Classroom laser projectors',
                'dateSubmitted' => 'May 18, 2026',
                'currentStatus' => 'Completed',
                'pdfFile' => 'signed-pr-eng-019.pdf',
                'remarks' => 'Purchase completed and delivery accepted.',
                'ocr' => [
                    'prNumber' => 'PR-2026-ENG-019',
                    'date' => 'May 18, 2026',
                    'requestingOffice' => 'College of Engineering',
                    'items' => '12 laser projectors with ceiling mount kits',
                    'amount' => 1260000,
                ],
                'activityLog' => [
                    ['timestamp' => 'May 18, 2026 11:30 AM', 'status' => 'Pending', 'remarks' => 'PR received for processing.'],
                    ['timestamp' => 'May 19, 2026 1:15 PM', 'status' => 'In Progress', 'remarks' => 'Canvassing completed.'],
                    ['timestamp' => 'May 20, 2026 4:40 PM', 'status' => 'Completed', 'remarks' => 'Purchase order issued and posted.'],
                ],
            ],
        ];
    }

    private function trackingItems(): array
    {
        return [
            ['id' => 'track-eng-021', 'office' => 'College of Engineering', 'item' => 'Electronics laboratory instruments', 'approvedAmount' => 3400000, 'targetQuarter' => 'Q1', 'currentStatus' => 'Delayed', 'remarks' => 'Updated delivery lead times requested.'],
            ['id' => 'track-sci-017', 'office' => 'College of Science', 'item' => 'Chemical storage safety cabinets', 'approvedAmount' => 2030000, 'targetQuarter' => 'Q2', 'currentStatus' => 'In Progress', 'remarks' => 'Technical specifications under validation.'],
            ['id' => 'track-lib-006', 'office' => 'University Library', 'item' => 'Digital database renewal', 'approvedAmount' => 2150000, 'targetQuarter' => 'Q3', 'currentStatus' => 'Pending', 'remarks' => 'Awaiting direct contracting documents.'],
            ['id' => 'track-eng-019', 'office' => 'College of Engineering', 'item' => 'Classroom laser projectors', 'approvedAmount' => 1260000, 'targetQuarter' => 'Q2', 'currentStatus' => 'Completed', 'remarks' => 'Purchase completed and delivery accepted.'],
            ['id' => 'track-hs-009', 'office' => 'Health Services', 'item' => 'Emergency response medical kits', 'approvedAmount' => 780000, 'targetQuarter' => 'Q1', 'currentStatus' => 'Delayed', 'remarks' => 'Partial supplier stock availability.'],
            ['id' => 'track-osa-011', 'office' => 'Student Affairs', 'item' => 'Student event audio equipment', 'approvedAmount' => 620000, 'targetQuarter' => 'Q2', 'currentStatus' => 'Completed', 'remarks' => 'Purchase completed this month.'],
        ];
    }
}
