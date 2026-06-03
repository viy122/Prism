<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrismViceChancellorController extends Controller
{
    public function dashboard(): View
    {
        return view('prism.vice-chancellor.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Vice Chancellor Division Dashboard',
            'divisionName' => 'Academic Affairs Division',
            'offices' => ['College of Engineering', 'College of Science', 'University Library', 'Graduate School'],
            'summary' => [
                'totalAppItems' => 86,
                'procuredCount' => 53,
                'divisionUtilization' => 71,
            ],
            'officeUtilization' => [
                ['office' => 'College of Engineering', 'utilization' => 69, 'risk' => 'At Risk', 'delayed' => 2, 'overdue' => 1],
                ['office' => 'College of Science', 'utilization' => 80, 'risk' => 'On Track', 'delayed' => 1, 'overdue' => 1],
                ['office' => 'University Library', 'utilization' => 47, 'risk' => 'Critical', 'delayed' => 1, 'overdue' => 2],
                ['office' => 'Graduate School', 'utilization' => 76, 'risk' => 'On Track', 'delayed' => 0, 'overdue' => 0],
            ],
            'pendingPrSummary' => [
                ['office' => 'College of Engineering', 'pendingPrs' => 4, 'inProgress' => 5, 'oldestPending' => 'May 21, 2026'],
                ['office' => 'College of Science', 'pendingPrs' => 3, 'inProgress' => 4, 'oldestPending' => 'May 20, 2026'],
                ['office' => 'University Library', 'pendingPrs' => 2, 'inProgress' => 1, 'oldestPending' => 'May 18, 2026'],
                ['office' => 'Graduate School', 'pendingPrs' => 1, 'inProgress' => 2, 'oldestPending' => 'May 24, 2026'],
            ],
        ]));
    }

    public function divisionProcurementStatus(): View
    {
        $items = $this->divisionItems();

        return view('prism.vice-chancellor.division-procurement-status', $this->withCommon('division-procurement-status', [
            'pageTitle' => 'Vice Chancellor Division Procurement Status',
            'divisionItems' => $items,
            'offices' => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters' => collect($items)->pluck('targetQuarter')->unique()->values()->all(),
            'statuses' => collect($items)->pluck('currentStatus')->unique()->values()->all(),
        ]));
    }

    public function divisionPerformanceReport(): View
    {
        return view('prism.vice-chancellor.division-performance-report', $this->withCommon('division-performance-report', [
            'pageTitle' => 'Vice Chancellor Division Performance Report',
            'performanceRows' => [
                ['office' => 'College of Science', 'totalAppItems' => 31, 'procured' => 24, 'pending' => 7, 'utilization' => 80, 'performance' => 'best'],
                ['office' => 'Graduate School', 'totalAppItems' => 12, 'procured' => 8, 'pending' => 4, 'utilization' => 76, 'performance' => 'steady'],
                ['office' => 'College of Engineering', 'totalAppItems' => 34, 'procured' => 22, 'pending' => 12, 'utilization' => 69, 'performance' => 'steady'],
                ['office' => 'University Library', 'totalAppItems' => 9, 'procured' => 4, 'pending' => 5, 'utilization' => 47, 'performance' => 'lowest'],
            ],
            'bestOffice' => 'College of Science',
            'lowestOffice' => 'University Library',
        ]));
    }

    private function withCommon(string $activeViceChancellorPage, array $data): array
    {
        return array_merge([
            'activeRole' => 'vice-chancellor',
            'activeModulePage' => $activeViceChancellorPage,
            'brandHref' => route('vice-chancellor.dashboard'),
            'roleNavigation' => [
                ['slug' => 'office-head', 'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office', 'label' => 'Finance Office', 'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office', 'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor', 'label' => 'Chancellor', 'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor', 'label' => 'Vice Chancellor', 'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel' => 'Vice Chancellor pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard', 'label' => 'Division Dashboard', 'href' => route('vice-chancellor.dashboard'), 'icon' => 'layout-dashboard'],
                ['slug' => 'division-procurement-status', 'label' => 'Division Procurement Status', 'href' => route('vice-chancellor.division-procurement-status'), 'icon' => 'list-checks'],
                ['slug' => 'division-performance-report', 'label' => 'Division Performance Report', 'href' => route('vice-chancellor.division-performance-report'), 'icon' => 'chart-no-axes-combined'],
            ],
        ], $data);
    }

    private function divisionItems(): array
    {
        return [
            [
                'id' => 'vc-eng-021',
                'office' => 'College of Engineering',
                'item' => 'Electronics laboratory instruments',
                'targetQuarter' => 'Q1',
                'currentStatus' => 'Delayed',
                'remarks' => 'Updated delivery lead times requested from suppliers.',
                'procurementRemarks' => 'Procurement Office requested revised lead time matrix before canvassing.',
                'timeline' => [
                    ['timestamp' => 'May 20, 2026 9:15 AM', 'status' => 'Pending', 'remarks' => 'PR queued for technical review.'],
                    ['timestamp' => 'May 25, 2026 3:40 PM', 'status' => 'In Progress', 'remarks' => 'Canvassing officer assigned.'],
                    ['timestamp' => 'May 27, 2026 4:05 PM', 'status' => 'Delayed', 'remarks' => 'Supplier lead times need validation.'],
                ],
            ],
            [
                'id' => 'vc-sci-017',
                'office' => 'College of Science',
                'item' => 'Chemical storage safety cabinets',
                'targetQuarter' => 'Q2',
                'currentStatus' => 'In Progress',
                'remarks' => 'Technical specifications under validation.',
                'procurementRemarks' => 'Safety committee comments expected this week.',
                'timeline' => [
                    ['timestamp' => 'May 22, 2026 10:10 AM', 'status' => 'Pending', 'remarks' => 'PR received from Finance-endorsed APP.'],
                    ['timestamp' => 'May 27, 2026 10:18 AM', 'status' => 'In Progress', 'remarks' => 'Specification validation assigned.'],
                ],
            ],
            [
                'id' => 'vc-lib-006',
                'office' => 'University Library',
                'item' => 'Digital database renewal',
                'targetQuarter' => 'Q3',
                'currentStatus' => 'Pending',
                'remarks' => 'Awaiting direct contracting documents.',
                'procurementRemarks' => 'Direct contracting justification is incomplete.',
                'timeline' => [
                    ['timestamp' => 'May 24, 2026 3:50 PM', 'status' => 'Pending', 'remarks' => 'PR uploaded with subscription matrix.'],
                ],
            ],
            [
                'id' => 'vc-eng-019',
                'office' => 'College of Engineering',
                'item' => 'Classroom laser projectors',
                'targetQuarter' => 'Q2',
                'currentStatus' => 'Completed',
                'remarks' => 'Purchase completed and delivery accepted.',
                'procurementRemarks' => 'Completed purchase and posted delivery acceptance.',
                'timeline' => [
                    ['timestamp' => 'May 18, 2026 11:30 AM', 'status' => 'Pending', 'remarks' => 'PR received for processing.'],
                    ['timestamp' => 'May 20, 2026 4:40 PM', 'status' => 'Completed', 'remarks' => 'Purchase order issued and posted.'],
                ],
            ],
            [
                'id' => 'vc-grad-004',
                'office' => 'Graduate School',
                'item' => 'Research commons workstations',
                'targetQuarter' => 'Q2',
                'currentStatus' => 'In Progress',
                'remarks' => 'Supplier quotations under comparison.',
                'procurementRemarks' => 'Canvassing completed; preparing recommendation.',
                'timeline' => [
                    ['timestamp' => 'May 23, 2026 8:45 AM', 'status' => 'Pending', 'remarks' => 'PR received with approved specifications.'],
                    ['timestamp' => 'May 26, 2026 2:30 PM', 'status' => 'In Progress', 'remarks' => 'Supplier quotations under comparison.'],
                ],
            ],
        ];
    }
}
