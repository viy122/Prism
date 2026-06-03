<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrismChancellorController extends Controller
{
    public function dashboard(): View
    {
        return view('prism.chancellor.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Chancellor Campus Monitoring Dashboard',
            'summary' => [
                'totalAppItems' => 146,
                'itemsProcured' => 94,
                'itemsPending' => 37,
                'itemsOverdue' => 15,
                'campusUtilization' => 68,
            ],
            'quarterlyStatuses' => [
                ['office' => 'College of Engineering', 'q1' => 'Completed', 'q2' => 'In Progress', 'q3' => 'Pending', 'q4' => 'Pending', 'risk' => 'At Risk'],
                ['office' => 'College of Science', 'q1' => 'Completed', 'q2' => 'Delayed', 'q3' => 'Pending', 'q4' => 'Pending', 'risk' => 'Critical'],
                ['office' => 'University Library', 'q1' => 'Completed', 'q2' => 'Completed', 'q3' => 'In Progress', 'q4' => 'Pending', 'risk' => 'On Track'],
                ['office' => 'Health Services', 'q1' => 'Delayed', 'q2' => 'In Progress', 'q3' => 'Pending', 'q4' => 'Pending', 'risk' => 'Critical'],
                ['office' => 'Student Affairs', 'q1' => 'Completed', 'q2' => 'Completed', 'q3' => 'In Progress', 'q4' => 'Pending', 'risk' => 'On Track'],
            ],
            'forecasts' => [
                ['office' => 'College of Engineering', 'currentUtilization' => 69, 'forecast' => 84, 'risk' => 'At Risk'],
                ['office' => 'College of Science', 'currentUtilization' => 80, 'forecast' => 92, 'risk' => 'On Track'],
                ['office' => 'University Library', 'currentUtilization' => 47, 'forecast' => 61, 'risk' => 'Critical'],
                ['office' => 'Health Services', 'currentUtilization' => 36, 'forecast' => 52, 'risk' => 'Critical'],
                ['office' => 'Student Affairs', 'currentUtilization' => 67, 'forecast' => 86, 'risk' => 'On Track'],
            ],
            'utilizationRankings' => [
                ['rank' => 1, 'office' => 'College of Science', 'utilization' => 80, 'risk' => 'On Track'],
                ['rank' => 2, 'office' => 'College of Engineering', 'utilization' => 69, 'risk' => 'At Risk'],
                ['rank' => 3, 'office' => 'Student Affairs', 'utilization' => 67, 'risk' => 'On Track'],
                ['rank' => 4, 'office' => 'University Library', 'utilization' => 47, 'risk' => 'Critical'],
                ['rank' => 5, 'office' => 'Health Services', 'utilization' => 36, 'risk' => 'Critical'],
            ],
            'overdueAlerts' => [
                ['office' => 'College of Engineering', 'prNumber' => 'PR-2026-ENG-021', 'item' => 'Electronics laboratory instruments', 'daysOverdue' => 18, 'status' => 'Delayed'],
                ['office' => 'College of Science', 'prNumber' => 'PR-2026-SCI-017', 'item' => 'Chemical storage safety cabinets', 'daysOverdue' => 15, 'status' => 'Delayed'],
                ['office' => 'Health Services', 'prNumber' => 'PR-2026-HS-009', 'item' => 'Emergency response medical kits', 'daysOverdue' => 21, 'status' => 'In Progress'],
            ],
        ]));
    }

    public function budgetApproval(): View
    {
        return view('prism.chancellor.budget-approval', $this->withCommon('budget-approval', [
            'pageTitle' => 'Chancellor Budget Approval',
            'proposals' => $this->endorsedProposals(),
        ]));
    }

    public function procurementReports(): View
    {
        return view('prism.chancellor.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle' => 'Chancellor Procurement Reports',
            'accomplishmentRows' => [
                ['office' => 'College of Engineering', 'targeted' => 34, 'procured' => 24, 'completionRate' => 71],
                ['office' => 'College of Science', 'targeted' => 31, 'procured' => 19, 'completionRate' => 61],
                ['office' => 'University Library', 'targeted' => 14, 'procured' => 10, 'completionRate' => 71],
                ['office' => 'Health Services', 'targeted' => 18, 'procured' => 8, 'completionRate' => 44],
                ['office' => 'Student Affairs', 'targeted' => 20, 'procured' => 16, 'completionRate' => 80],
            ],
            'utilizationSummary' => [
                ['office' => 'College of Engineering', 'budget' => 18450000, 'utilized' => 12800000, 'forecast' => 84, 'risk' => 'At Risk'],
                ['office' => 'College of Science', 'budget' => 22100000, 'utilized' => 17700000, 'forecast' => 92, 'risk' => 'On Track'],
                ['office' => 'University Library', 'budget' => 8900000, 'utilized' => 4200000, 'forecast' => 61, 'risk' => 'Critical'],
                ['office' => 'Health Services', 'budget' => 7450000, 'utilized' => 2650000, 'forecast' => 52, 'risk' => 'Critical'],
                ['office' => 'Student Affairs', 'budget' => 11950000, 'utilized' => 8050000, 'forecast' => 86, 'risk' => 'On Track'],
            ],
            'delayedByOffice' => [
                'College of Engineering' => [
                    ['item' => 'Electronics laboratory instruments', 'prNumber' => 'PR-2026-ENG-021', 'remarks' => 'Updated delivery lead times requested.'],
                ],
                'College of Science' => [
                    ['item' => 'Chemical storage safety cabinets', 'prNumber' => 'PR-2026-SCI-017', 'remarks' => 'Safety specification validation pending.'],
                    ['item' => 'Analytical balance sets', 'prNumber' => 'PR-2026-SCI-013', 'remarks' => 'Calibration certification under review.'],
                ],
                'Health Services' => [
                    ['item' => 'Emergency response medical kits', 'prNumber' => 'PR-2026-HS-009', 'remarks' => 'Partial supplier stock availability.'],
                ],
            ],
        ]));
    }

    private function withCommon(string $activeChancellorPage, array $data): array
    {
        return array_merge([
            'activeRole' => 'chancellor',
            'activeModulePage' => $activeChancellorPage,
            'brandHref' => route('chancellor.dashboard'),
            'roleNavigation' => [
                ['slug' => 'office-head', 'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office', 'label' => 'Finance Office', 'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office', 'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor', 'label' => 'Chancellor', 'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor', 'label' => 'Vice Chancellor', 'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel' => 'Chancellor pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard', 'label' => 'Campus Monitoring', 'href' => route('chancellor.dashboard'), 'icon' => 'layout-dashboard'],
                ['slug' => 'budget-approval', 'label' => 'Budget Approval', 'href' => route('chancellor.budget-approval'), 'icon' => 'shield-check'],
                ['slug' => 'procurement-reports', 'label' => 'Procurement Reports', 'href' => route('chancellor.procurement-reports'), 'icon' => 'chart-no-axes-combined'],
            ],
        ], $data);
    }

    private function endorsedProposals(): array
    {
        return [
            [
                'id' => 'endorse-eng-2027',
                'office' => 'College of Engineering',
                'title' => 'FY 2027 Engineering Annual Procurement Plan',
                'totalAmount' => 18450000,
                'dateEndorsed' => 'May 27, 2026',
                'financeRemarks' => 'Budget ceiling verified. Funding source and item classifications are acceptable.',
                'status' => 'Endorsed',
                'financeEndorsementTimestamp' => 'May 27, 2026 2:45 PM',
                'chancellorApprovalTimestamp' => 'Pending',
                'items' => [
                    [
                        'description' => 'Faculty laptop refresh package',
                        'quantity' => 1,
                        'unit' => 'lot',
                        'cost' => 2850000,
                        'justification' => 'Replace aging instructional laptops used for laboratory classes and faculty research.',
                        'scoping' => [
                            ['supplierName' => 'Northstar Systems', 'price' => 2795000, 'sourceLink' => 'https://example.com/northstar-laptop-scope', 'dateRetrieved' => 'May 27, 2026'],
                            ['supplierName' => 'CampusTech Supply', 'price' => 2870000, 'sourceLink' => 'https://example.com/campustech-laptop-scope', 'dateRetrieved' => 'May 27, 2026'],
                        ],
                    ],
                    [
                        'description' => 'Electronics laboratory instruments',
                        'quantity' => 10,
                        'unit' => 'set',
                        'cost' => 3400000,
                        'justification' => 'Support redesigned circuits and embedded systems laboratory activities.',
                        'scoping' => [
                            ['supplierName' => 'Precision Lab Depot', 'price' => 338500, 'sourceLink' => 'https://example.com/lab-instrument-scope', 'dateRetrieved' => 'May 27, 2026'],
                            ['supplierName' => 'SignalWorks Trading', 'price' => 343200, 'sourceLink' => 'https://example.com/signalworks-scope', 'dateRetrieved' => 'May 27, 2026'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'endorse-sci-2027',
                'office' => 'College of Science',
                'title' => 'FY 2027 Science Laboratory Modernization',
                'totalAmount' => 22100000,
                'dateEndorsed' => 'May 26, 2026',
                'financeRemarks' => 'Endorsed subject to Chancellor confirmation of safety-critical procurement priority.',
                'status' => 'Endorsed',
                'financeEndorsementTimestamp' => 'May 26, 2026 4:20 PM',
                'chancellorApprovalTimestamp' => 'Pending',
                'items' => [
                    [
                        'description' => 'Analytical balance sets',
                        'quantity' => 18,
                        'unit' => 'unit',
                        'cost' => 3330000,
                        'justification' => 'Increase shared laboratory capacity and replace balances with calibration issues.',
                        'scoping' => [
                            ['supplierName' => 'LabPoint Scientific', 'price' => 181500, 'sourceLink' => 'https://example.com/labpoint-balance-scope', 'dateRetrieved' => 'May 26, 2026'],
                            ['supplierName' => 'Precision Lab Depot', 'price' => 187000, 'sourceLink' => 'https://example.com/precision-balance-scope', 'dateRetrieved' => 'May 26, 2026'],
                        ],
                    ],
                    [
                        'description' => 'Chemical storage safety cabinets',
                        'quantity' => 14,
                        'unit' => 'unit',
                        'cost' => 2030000,
                        'justification' => 'Meet laboratory safety standards and segregate incompatible chemicals.',
                        'scoping' => [
                            ['supplierName' => 'SafeLab Industrial', 'price' => 141900, 'sourceLink' => 'https://example.com/safelab-cabinet-scope', 'dateRetrieved' => 'May 26, 2026'],
                            ['supplierName' => 'LabWorks PH', 'price' => 147500, 'sourceLink' => 'https://example.com/labworks-cabinet-scope', 'dateRetrieved' => 'May 26, 2026'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'endorse-lib-2027',
                'office' => 'University Library',
                'title' => 'FY 2027 Library Digital Resources Renewal',
                'totalAmount' => 8900000,
                'dateEndorsed' => 'May 24, 2026',
                'financeRemarks' => 'Funding source confirmed. Direct contracting documents should be attached before procurement execution.',
                'status' => 'Endorsed',
                'financeEndorsementTimestamp' => 'May 24, 2026 11:10 AM',
                'chancellorApprovalTimestamp' => 'Pending',
                'items' => [
                    [
                        'description' => 'Digital database subscription renewal',
                        'quantity' => 1,
                        'unit' => 'lot',
                        'cost' => 2150000,
                        'justification' => 'Maintain uninterrupted access to licensed research databases.',
                        'scoping' => [
                            ['supplierName' => 'Global Academic Index', 'price' => 2150000, 'sourceLink' => 'https://example.com/global-academic-index', 'dateRetrieved' => 'May 24, 2026'],
                            ['supplierName' => 'Scholarly Data Hub', 'price' => 2190000, 'sourceLink' => 'https://example.com/scholarly-data-hub', 'dateRetrieved' => 'May 24, 2026'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
