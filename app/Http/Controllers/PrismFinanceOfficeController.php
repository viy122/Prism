<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrismFinanceOfficeController extends Controller
{
    public function dashboard(): View
    {
        return view('prism.finance-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Finance Office Dashboard',
            'summary' => [
                'awaitingReview' => 14,
                'endorsedThisMonth' => 21,
                'returned' => 6,
                'totalCampusBudget' => 94350000,
            ],
            'officeStatusGroups' => [
                ['office' => 'College of Engineering', 'pending' => 3, 'endorsed' => 6, 'returned' => 1],
                ['office' => 'College of Science', 'pending' => 4, 'endorsed' => 5, 'returned' => 2],
                ['office' => 'University Library', 'pending' => 1, 'endorsed' => 4, 'returned' => 0],
                ['office' => 'Health Services', 'pending' => 2, 'endorsed' => 3, 'returned' => 1],
                ['office' => 'Student Affairs', 'pending' => 4, 'endorsed' => 3, 'returned' => 2],
            ],
            'recentSubmissions' => $this->recentSubmissions(),
        ]));
    }

    public function proposalReview(?string $proposal = null): View
    {
        $proposals = $this->reviewProposals();
        $selectedProposal = collect($proposals)->firstWhere('id', $proposal) ?? $proposals[0];

        return view('prism.finance-office.proposal-review', $this->withCommon('proposal-review', [
            'pageTitle' => 'Proposal Review',
            'proposals' => $proposals,
            'selectedProposal' => $selectedProposal,
        ]));
    }

    public function annualProcurementPlan(): View
    {
        $items = $this->appItems();

        return view('prism.finance-office.annual-procurement-plan', $this->withCommon('annual-procurement-plan', [
            'pageTitle' => 'Annual Procurement Plan',
            'appItems' => $items,
            'offices' => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters' => collect($items)->pluck('targetQuarter')->unique()->values()->all(),
            'procurementModes' => collect($items)->pluck('procurementMode')->unique()->values()->all(),
        ]));
    }

    public function budgetUtilizationReport(): View
    {
        return view('prism.finance-office.budget-utilization-report', $this->withCommon('budget-utilization-report', [
            'pageTitle' => 'Budget Utilization Report',
            'summary' => [
                'campusBudget' => 94350000,
                'totalUtilized' => 61275000,
                'utilizationPercent' => 65,
                'officesAtRisk' => 3,
            ],
            'quarters' => ['Q1', 'Q2', 'Q3', 'Q4'],
            'offices' => ['College of Engineering', 'College of Science', 'University Library', 'Health Services', 'Student Affairs'],
            'utilizationRows' => [
                ['office' => 'College of Engineering', 'quarter' => 'Q2', 'budget' => 18450000, 'utilized' => 12800000, 'percent' => 69, 'risk' => 'On Track'],
                ['office' => 'College of Science', 'quarter' => 'Q2', 'budget' => 22100000, 'utilized' => 17700000, 'percent' => 80, 'risk' => 'Watch'],
                ['office' => 'University Library', 'quarter' => 'Q2', 'budget' => 8900000, 'utilized' => 4200000, 'percent' => 47, 'risk' => 'At Risk'],
                ['office' => 'Health Services', 'quarter' => 'Q2', 'budget' => 7450000, 'utilized' => 2650000, 'percent' => 36, 'risk' => 'At Risk'],
                ['office' => 'Student Affairs', 'quarter' => 'Q2', 'budget' => 11950000, 'utilized' => 8050000, 'percent' => 67, 'risk' => 'On Track'],
                ['office' => 'Facilities Management', 'quarter' => 'Q3', 'budget' => 25450000, 'utilized' => 15875000, 'percent' => 62, 'risk' => 'Watch'],
            ],
        ]));
    }

    private function withCommon(string $activeFinancePage, array $data): array
    {
        return array_merge([
            'activeRole' => 'finance-office',
            'activeModulePage' => $activeFinancePage,
            'brandHref' => route('finance-office.dashboard'),
            'roleNavigation' => [
                ['slug' => 'office-head', 'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office', 'label' => 'Finance Office', 'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office', 'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor', 'label' => 'Chancellor', 'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor', 'label' => 'Vice Chancellor', 'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel' => 'Finance Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard', 'label' => 'Dashboard', 'href' => route('finance-office.dashboard'), 'icon' => 'layout-dashboard'],
                ['slug' => 'proposal-review', 'label' => 'Proposal Review', 'href' => route('finance-office.proposal-review'), 'icon' => 'clipboard-check'],
                ['slug' => 'annual-procurement-plan', 'label' => 'Annual Procurement Plan', 'href' => route('finance-office.annual-procurement-plan'), 'icon' => 'file-stack'],
                ['slug' => 'budget-utilization-report', 'label' => 'Budget Utilization Report', 'href' => route('finance-office.budget-utilization-report'), 'icon' => 'chart-no-axes-combined'],
            ],
        ], $data);
    }

    private function recentSubmissions(): array
    {
        return [
            ['proposalId' => 'eng-2027-main', 'office' => 'College of Engineering', 'submittedDate' => 'May 27, 2026', 'totalAmount' => 18450000],
            ['proposalId' => 'sci-2027-labs', 'office' => 'College of Science', 'submittedDate' => 'May 26, 2026', 'totalAmount' => 22100000],
            ['proposalId' => 'osa-2027-student', 'office' => 'Student Affairs', 'submittedDate' => 'May 25, 2026', 'totalAmount' => 11950000],
            ['proposalId' => 'clinic-2027-readiness', 'office' => 'Health Services', 'submittedDate' => 'May 24, 2026', 'totalAmount' => 7450000],
        ];
    }

    private function reviewProposals(): array
    {
        return [
            [
                'id' => 'eng-2027-main',
                'title' => 'FY 2027 Engineering Annual Procurement Plan',
                'status' => 'Pending',
                'office' => [
                    'name' => 'College of Engineering',
                    'head' => 'Dean Maria Santos',
                    'fiscalYear' => '2027',
                    'submittedDate' => 'May 27, 2026',
                    'totalAmount' => 18450000,
                    'fundSource' => 'General Fund and Laboratory Fees',
                ],
                'items' => [
                    [
                        'description' => 'Faculty laptop refresh package',
                        'unit' => 'lot',
                        'quantity' => 1,
                        'estimatedUnitCost' => 2850000,
                        'totalCost' => 2850000,
                        'justification' => 'Replace aging instructional laptops used for laboratory classes and faculty research.',
                        'targetQuarter' => 'Q2',
                        'scoping' => [
                            ['supplierName' => 'Northstar Systems', 'price' => 2795000, 'sourceLink' => 'https://example.com/northstar-laptop-scope', 'dateRetrieved' => 'May 27, 2026'],
                            ['supplierName' => 'CampusTech Supply', 'price' => 2870000, 'sourceLink' => 'https://example.com/campustech-laptop-scope', 'dateRetrieved' => 'May 27, 2026'],
                        ],
                    ],
                    [
                        'description' => 'Classroom laser projectors',
                        'unit' => 'unit',
                        'quantity' => 12,
                        'estimatedUnitCost' => 105000,
                        'totalCost' => 1260000,
                        'justification' => 'Upgrade lecture rooms with brighter displays and lower maintenance costs.',
                        'targetQuarter' => 'Q2',
                        'scoping' => [
                            ['supplierName' => 'AV Learning Solutions', 'price' => 102500, 'sourceLink' => 'https://example.com/av-projector-scope', 'dateRetrieved' => 'May 27, 2026'],
                            ['supplierName' => 'Metro Classroom Tech', 'price' => 106800, 'sourceLink' => 'https://example.com/metro-projector-scope', 'dateRetrieved' => 'May 27, 2026'],
                        ],
                    ],
                    [
                        'description' => 'Electronics laboratory instruments',
                        'unit' => 'set',
                        'quantity' => 10,
                        'estimatedUnitCost' => 340000,
                        'totalCost' => 3400000,
                        'justification' => 'Support redesigned circuits and embedded systems laboratory activities.',
                        'targetQuarter' => 'Q4',
                        'scoping' => [
                            ['supplierName' => 'Precision Lab Depot', 'price' => 338500, 'sourceLink' => 'https://example.com/lab-instrument-scope', 'dateRetrieved' => 'May 27, 2026'],
                            ['supplierName' => 'SignalWorks Trading', 'price' => 343200, 'sourceLink' => 'https://example.com/signalworks-scope', 'dateRetrieved' => 'May 27, 2026'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'sci-2027-labs',
                'title' => 'FY 2027 Science Laboratory Modernization',
                'status' => 'Pending',
                'office' => [
                    'name' => 'College of Science',
                    'head' => 'Dean Paolo Reyes',
                    'fiscalYear' => '2027',
                    'submittedDate' => 'May 26, 2026',
                    'totalAmount' => 22100000,
                    'fundSource' => 'General Fund and Research Support Fund',
                ],
                'items' => [
                    [
                        'description' => 'Analytical balance sets',
                        'unit' => 'unit',
                        'quantity' => 18,
                        'estimatedUnitCost' => 185000,
                        'totalCost' => 3330000,
                        'justification' => 'Increase shared laboratory capacity and replace balances with calibration issues.',
                        'targetQuarter' => 'Q1',
                        'scoping' => [
                            ['supplierName' => 'LabPoint Scientific', 'price' => 181500, 'sourceLink' => 'https://example.com/labpoint-balance-scope', 'dateRetrieved' => 'May 26, 2026'],
                            ['supplierName' => 'Precision Lab Depot', 'price' => 187000, 'sourceLink' => 'https://example.com/precision-balance-scope', 'dateRetrieved' => 'May 26, 2026'],
                        ],
                    ],
                    [
                        'description' => 'Chemical storage safety cabinets',
                        'unit' => 'unit',
                        'quantity' => 14,
                        'estimatedUnitCost' => 145000,
                        'totalCost' => 2030000,
                        'justification' => 'Meet laboratory safety standards and segregate incompatible chemicals.',
                        'targetQuarter' => 'Q2',
                        'scoping' => [
                            ['supplierName' => 'SafeLab Industrial', 'price' => 141900, 'sourceLink' => 'https://example.com/safelab-cabinet-scope', 'dateRetrieved' => 'May 26, 2026'],
                            ['supplierName' => 'LabWorks PH', 'price' => 147500, 'sourceLink' => 'https://example.com/labworks-cabinet-scope', 'dateRetrieved' => 'May 26, 2026'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function appItems(): array
    {
        return [
            ['office' => 'College of Engineering', 'item' => 'Faculty laptop refresh package', 'quantity' => 1, 'abcAmount' => 2850000, 'targetQuarter' => 'Q2', 'procurementMode' => 'Competitive Bidding', 'recommendation' => 'ABC exceeds small value threshold; consolidate warranty requirements.'],
            ['office' => 'College of Engineering', 'item' => 'Classroom laser projectors', 'quantity' => 12, 'abcAmount' => 1260000, 'targetQuarter' => 'Q2', 'procurementMode' => 'Small Value Procurement', 'recommendation' => 'Standardized specification and moderate ABC support SVP.'],
            ['office' => 'College of Science', 'item' => 'Analytical balance sets', 'quantity' => 18, 'abcAmount' => 3330000, 'targetQuarter' => 'Q1', 'procurementMode' => 'Competitive Bidding', 'recommendation' => 'Multiple units and technical specs should be bid as one lot.'],
            ['office' => 'College of Science', 'item' => 'Chemical storage safety cabinets', 'quantity' => 14, 'abcAmount' => 2030000, 'targetQuarter' => 'Q2', 'procurementMode' => 'Competitive Bidding', 'recommendation' => 'Safety compliance requirements need formal bid evaluation.'],
            ['office' => 'University Library', 'item' => 'Digital database subscription renewal', 'quantity' => 1, 'abcAmount' => 2150000, 'targetQuarter' => 'Q3', 'procurementMode' => 'Direct Contracting', 'recommendation' => 'Exclusive publisher platform requires direct contracting justification.'],
            ['office' => 'Health Services', 'item' => 'Emergency response medical kits', 'quantity' => 25, 'abcAmount' => 780000, 'targetQuarter' => 'Q1', 'procurementMode' => 'Shopping', 'recommendation' => 'Readily available supplies with clear specifications.'],
        ];
    }
}
