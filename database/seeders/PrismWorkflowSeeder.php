<?php

namespace Database\Seeders;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\MarketScopingReference;
use App\Models\Office;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrismWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $officeHead  = User::where('username', 'office_head')->firstOrFail();
        $coeHead     = User::where('username', 'coe_head')->firstOrFail();
        $cbaHead     = User::where('username', 'cba_head')->firstOrFail();
        $finance     = User::where('username', 'finance')->firstOrFail();
        $chancellor  = User::where('username', 'chancellor')->firstOrFail();

        $cics = Office::where('code', 'CICS')->firstOrFail();
        $coe  = Office::where('code', 'COE')->firstOrFail();
        $cba  = Office::where('code', 'CBA')->firstOrFail();

        // ═══════════════════════════════════════════════════════════════════════
        // CICS — Draft (Office Head currently working on it)
        // ═══════════════════════════════════════════════════════════════════════
        $draft = BudgetProposal::updateOrCreate(
            ['code' => 'BP-CICS-2027-DRAFT'],
            [
                'office_id'            => $cics->id,
                'created_by_user_id'   => $officeHead->id,
                'title'                => 'FY 2027 CICS Annual Budget Proposal (Draft)',
                'fiscal_year'          => 2027,
                'total_estimated_cost' => 0,
                'status'               => 'draft',
            ]
        );

        $draftItems = [
            [
                'name'                => 'Desktop computers for computer lab',
                'description'         => 'High-performance desktop computers for the undergraduate programming laboratory',
                'category'            => 'ICT Equipment',
                'quantity'            => 20,
                'unit'                => 'unit',
                'estimated_unit_cost' => 45000,
                'target_quarter'      => 'Q2',
                'remarks'             => 'Replacement for units older than 7 years; needed for new curriculum',
            ],
            [
                'name'                => 'Network switches and cabling',
                'description'         => 'Managed network switches and structured cabling for lab interconnection',
                'category'            => 'ICT Equipment',
                'quantity'            => 5,
                'unit'                => 'set',
                'estimated_unit_cost' => 28000,
                'target_quarter'      => 'Q1',
                'remarks'             => 'Current infrastructure is aging and causing intermittent disconnections',
            ],
            [
                'name'                => 'Office supplies and consumables',
                'description'         => 'Bond paper, printer ink, toner, and miscellaneous office supplies',
                'category'            => 'Office Supplies',
                'quantity'            => 1,
                'unit'                => 'lot',
                'estimated_unit_cost' => 85000,
                'target_quarter'      => 'Q1',
                'remarks'             => 'Annual replenishment for all CICS offices and laboratories',
            ],
        ];

        $draftTotal = 0;
        foreach ($draftItems as $itemData) {
            $total = $itemData['quantity'] * $itemData['estimated_unit_cost'];
            $draftTotal += $total;

            $item = BudgetProposalItem::updateOrCreate(
                ['budget_proposal_id' => $draft->id, 'name' => $itemData['name']],
                array_merge($itemData, [
                    'budget_proposal_id'   => $draft->id,
                    'created_by_user_id'   => $officeHead->id,
                    'estimated_total_cost' => $total,
                    'status'               => 'draft',
                ])
            );

            if ($itemData['name'] === 'Desktop computers for computer lab') {
                foreach ([
                    ['supplier' => 'Asiaphil IT Solutions', 'price' => 43500, 'source' => 'Lazada PH', 'url' => 'https://lazada.com.ph/products/asiaphil-desktop-i5', 'title' => 'Desktop PC Intel Core i5-12400 8GB 256GB SSD'],
                    ['supplier' => 'TechWorld Trading',     'price' => 44900, 'source' => 'Shopee PH', 'url' => 'https://shopee.ph/techworld/desktop-core-i5',           'title' => 'Complete Desktop Set i5 12th Gen 16GB DDR4'],
                    ['supplier' => 'CompuCentro PH',        'price' => 46200, 'source' => 'Lazada PH', 'url' => 'https://lazada.com.ph/products/compucentro-desktop',    'title' => 'Intel i5 Desktop Bundle with Monitor and Keyboard'],
                ] as $ref) {
                    MarketScopingReference::updateOrCreate(
                        ['budget_proposal_item_id' => $item->id, 'supplier_name' => $ref['supplier']],
                        [
                            'created_by_user_id' => $officeHead->id,
                            'supplier_name'      => $ref['supplier'],
                            'source_type'        => $ref['source'],
                            'source_url'         => $ref['url'],
                            'title'              => $ref['title'],
                            'price'              => $ref['price'],
                            'currency'           => 'PHP',
                            'date_accessed'      => now()->subDays(rand(1, 5))->toDateString(),
                            'match_status'       => 'Verified',
                            'status'             => 'needs_review',
                            'is_selected'        => false,
                        ]
                    );
                }
            }
        }

        $draft->update(['total_estimated_cost' => $draftTotal]);

        // ═══════════════════════════════════════════════════════════════════════
        // CICS — Submitted (under Finance Office review)
        // ═══════════════════════════════════════════════════════════════════════
        $cicsSubmitted = BudgetProposal::updateOrCreate(
            ['code' => 'BP-CICS-2026-001'],
            [
                'office_id'              => $cics->id,
                'created_by_user_id'     => $officeHead->id,
                'submitted_by_user_id'   => $officeHead->id,
                'title'                  => 'FY 2026 CICS Annual Budget Proposal',
                'fiscal_year'            => 2026,
                'total_estimated_cost'   => 1_850_000,
                'status'                 => 'submitted',
                'submitted_at'           => now()->subDays(12),
            ]
        );

        $submittedTotal = 0;
        foreach ([
            ['name' => 'Faculty laptop refresh package',  'qty' => 10, 'unit' => 'unit', 'cost' => 85000,  'q' => 'Q2', 'cat' => 'ICT Equipment'],
            ['name' => 'Classroom laser projectors',       'qty' => 8,  'unit' => 'unit', 'cost' => 55000,  'q' => 'Q1', 'cat' => 'ICT Equipment'],
            ['name' => 'Smart board for lecture room',     'qty' => 2,  'unit' => 'unit', 'cost' => 120000, 'q' => 'Q3', 'cat' => 'ICT Equipment'],
            ['name' => 'Networking laboratory modules',    'qty' => 15, 'unit' => 'set',  'cost' => 18000,  'q' => 'Q1', 'cat' => 'Laboratory Equipment'],
        ] as $si) {
            $total = $si['qty'] * $si['cost'];
            $submittedTotal += $total;
            BudgetProposalItem::updateOrCreate(
                ['budget_proposal_id' => $cicsSubmitted->id, 'name' => $si['name']],
                [
                    'budget_proposal_id'   => $cicsSubmitted->id,
                    'created_by_user_id'   => $officeHead->id,
                    'name'                 => $si['name'],
                    'description'          => $si['name'],
                    'category'             => $si['cat'],
                    'quantity'             => $si['qty'],
                    'unit'                 => $si['unit'],
                    'estimated_unit_cost'  => $si['cost'],
                    'estimated_total_cost' => $total,
                    'target_quarter'       => $si['q'],
                    'status'               => 'pending',
                ]
            );
        }

        $cicsSubmitted->update(['total_estimated_cost' => $submittedTotal]);

        BudgetProposalReview::updateOrCreate(
            ['budget_proposal_id' => $cicsSubmitted->id, 'action' => 'submitted'],
            [
                'reviewed_by_user_id' => $officeHead->id,
                'action'              => 'submitted',
                'status_from'         => 'draft',
                'status_to'           => 'submitted',
                'remarks'             => 'Proposal submitted for Finance Office review.',
                'reviewed_at'         => now()->subDays(12),
            ]
        );

        // ═══════════════════════════════════════════════════════════════════════
        // CICS — Approved (last fiscal year, fully processed)
        // ═══════════════════════════════════════════════════════════════════════
        $cicsApproved = BudgetProposal::updateOrCreate(
            ['code' => 'BP-CICS-2025-001'],
            [
                'office_id'              => $cics->id,
                'created_by_user_id'     => $officeHead->id,
                'submitted_by_user_id'   => $officeHead->id,
                'reviewed_by_user_id'    => $finance->id,
                'approved_by_user_id'    => $chancellor->id,
                'title'                  => 'FY 2025 CICS Annual Budget Proposal',
                'fiscal_year'            => 2025,
                'total_estimated_cost'   => 1_420_000,
                'approved_budget'        => 1_350_000,
                'status'                 => 'approved',
                'submitted_at'           => now()->subMonths(8),
                'reviewed_at'            => now()->subMonths(7),
                'approved_at'            => now()->subMonths(6),
                'remarks'                => 'Approved with minor budget adjustment on ICT equipment line.',
            ]
        );

        foreach ([
            ['action' => 'submitted', 'from' => 'draft',      'to' => 'submitted', 'user' => $officeHead, 'days' => 240, 'note' => 'Submitted for Finance review.'],
            ['action' => 'endorsed',  'from' => 'submitted',   'to' => 'endorsed',  'user' => $finance,    'days' => 210, 'note' => 'Budget ceiling verified. Endorsed to Chancellor.'],
            ['action' => 'approved',  'from' => 'endorsed',    'to' => 'approved',  'user' => $chancellor, 'days' => 180, 'note' => 'Approved with budget adjustment on ICT line.'],
        ] as $rev) {
            BudgetProposalReview::updateOrCreate(
                ['budget_proposal_id' => $cicsApproved->id, 'action' => $rev['action']],
                [
                    'reviewed_by_user_id' => $rev['user']->id,
                    'action'              => $rev['action'],
                    'status_from'         => $rev['from'],
                    'status_to'           => $rev['to'],
                    'remarks'             => $rev['note'],
                    'reviewed_at'         => now()->subDays($rev['days']),
                ]
            );
        }

        // ═══════════════════════════════════════════════════════════════════════
        // COE — Endorsed (Chancellor sees this for approval)
        // ═══════════════════════════════════════════════════════════════════════
        $coeEndorsed = BudgetProposal::updateOrCreate(
            ['code' => 'BP-COE-2026-001'],
            [
                'office_id'              => $coe->id,
                'created_by_user_id'     => $coeHead->id,
                'submitted_by_user_id'   => $coeHead->id,
                'reviewed_by_user_id'    => $finance->id,
                'title'                  => 'FY 2026 COE Annual Budget Proposal',
                'fiscal_year'            => 2026,
                'total_estimated_cost'   => 2_340_000,
                'status'                 => 'endorsed',
                'submitted_at'           => now()->subDays(30),
                'reviewed_at'            => now()->subDays(10),
                'remarks'                => 'Endorsed to Chancellor for final approval. Budget allocation verified against ceiling.',
            ]
        );

        $coeTotal = 0;
        foreach ([
            ['name' => 'Engineering software licenses (AutoCAD, MATLAB)', 'qty' => 25, 'unit' => 'license', 'cost' => 35000, 'q' => 'Q1', 'cat' => 'Software'],
            ['name' => 'Laboratory benches and workstations',              'qty' => 12, 'unit' => 'set',     'cost' => 45000, 'q' => 'Q2', 'cat' => 'Laboratory Equipment'],
            ['name' => '3D printer for prototyping laboratory',            'qty' => 3,  'unit' => 'unit',    'cost' => 95000, 'q' => 'Q1', 'cat' => 'ICT Equipment'],
            ['name' => 'Safety equipment and PPE kit',                     'qty' => 50, 'unit' => 'set',     'cost' => 8000,  'q' => 'Q1', 'cat' => 'Safety Equipment'],
            ['name' => 'Faculty laptop refresh package',                   'qty' => 8,  'unit' => 'unit',    'cost' => 85000, 'q' => 'Q2', 'cat' => 'ICT Equipment'],
        ] as $si) {
            $total = $si['qty'] * $si['cost'];
            $coeTotal += $total;
            BudgetProposalItem::updateOrCreate(
                ['budget_proposal_id' => $coeEndorsed->id, 'name' => $si['name']],
                [
                    'budget_proposal_id'   => $coeEndorsed->id,
                    'created_by_user_id'   => $coeHead->id,
                    'name'                 => $si['name'],
                    'description'          => $si['name'],
                    'category'             => $si['cat'],
                    'quantity'             => $si['qty'],
                    'unit'                 => $si['unit'],
                    'estimated_unit_cost'  => $si['cost'],
                    'estimated_total_cost' => $total,
                    'target_quarter'       => $si['q'],
                    'status'               => 'pending',
                ]
            );
        }

        $coeEndorsed->update(['total_estimated_cost' => $coeTotal]);

        foreach ([
            ['action' => 'submitted', 'from' => 'draft',    'to' => 'submitted', 'user' => $coeHead, 'days' => 30, 'note' => 'Submitted for Finance Office review.'],
            ['action' => 'endorsed',  'from' => 'submitted','to' => 'endorsed',  'user' => $finance,  'days' => 10, 'note' => 'Budget allocation verified. Endorsed to Chancellor for approval.'],
        ] as $rev) {
            BudgetProposalReview::updateOrCreate(
                ['budget_proposal_id' => $coeEndorsed->id, 'action' => $rev['action']],
                [
                    'reviewed_by_user_id' => $rev['user']->id,
                    'action'              => $rev['action'],
                    'status_from'         => $rev['from'],
                    'status_to'           => $rev['to'],
                    'remarks'             => $rev['note'],
                    'reviewed_at'         => now()->subDays($rev['days']),
                ]
            );
        }

        // ═══════════════════════════════════════════════════════════════════════
        // CBA — Submitted (Finance Office can endorse this)
        // ═══════════════════════════════════════════════════════════════════════
        $cbaSubmitted = BudgetProposal::updateOrCreate(
            ['code' => 'BP-CBA-2026-001'],
            [
                'office_id'              => $cba->id,
                'created_by_user_id'     => $cbaHead->id,
                'submitted_by_user_id'   => $cbaHead->id,
                'title'                  => 'FY 2026 CBA Annual Budget Proposal',
                'fiscal_year'            => 2026,
                'total_estimated_cost'   => 980_000,
                'status'                 => 'submitted',
                'submitted_at'           => now()->subDays(7),
            ]
        );

        $cbaTotal = 0;
        foreach ([
            ['name' => 'Business simulation software',     'qty' => 40, 'unit' => 'license', 'cost' => 12000, 'q' => 'Q1', 'cat' => 'Software'],
            ['name' => 'Case study materials and textbooks','qty' => 1,  'unit' => 'lot',     'cost' => 150000,'q' => 'Q1', 'cat' => 'Library Materials'],
            ['name' => 'LCD monitors for study cubicles',   'qty' => 20, 'unit' => 'unit',    'cost' => 18000, 'q' => 'Q2', 'cat' => 'ICT Equipment'],
            ['name' => 'Video conferencing equipment',      'qty' => 2,  'unit' => 'set',     'cost' => 85000, 'q' => 'Q1', 'cat' => 'ICT Equipment'],
        ] as $si) {
            $total = $si['qty'] * $si['cost'];
            $cbaTotal += $total;
            BudgetProposalItem::updateOrCreate(
                ['budget_proposal_id' => $cbaSubmitted->id, 'name' => $si['name']],
                [
                    'budget_proposal_id'   => $cbaSubmitted->id,
                    'created_by_user_id'   => $cbaHead->id,
                    'name'                 => $si['name'],
                    'description'          => $si['name'],
                    'category'             => $si['cat'],
                    'quantity'             => $si['qty'],
                    'unit'                 => $si['unit'],
                    'estimated_unit_cost'  => $si['cost'],
                    'estimated_total_cost' => $total,
                    'target_quarter'       => $si['q'],
                    'status'               => 'pending',
                ]
            );
        }

        $cbaSubmitted->update(['total_estimated_cost' => $cbaTotal]);

        BudgetProposalReview::updateOrCreate(
            ['budget_proposal_id' => $cbaSubmitted->id, 'action' => 'submitted'],
            [
                'reviewed_by_user_id' => $cbaHead->id,
                'action'              => 'submitted',
                'status_from'         => 'draft',
                'status_to'           => 'submitted',
                'remarks'             => 'Budget proposal submitted for Finance Office review.',
                'reviewed_at'         => now()->subDays(7),
            ]
        );

        // ═══════════════════════════════════════════════════════════════════════
        // Purchase Requests — Per quarter, not per item
        // ═══════════════════════════════════════════════════════════════════════
        $quarterlyPRs = [
            // CICS Q1: Projectors + Networking modules
            [
                'number' => 'PR-CICS-2026-Q1', 'title' => 'Q1 FY 2026 CICS Purchase Request',
                'office' => $cics->id, 'user' => $officeHead->id, 'fiscal_year' => 2026,
                'status' => 'in_progress', 'uploaded_at' => now()->subDays(14),
                'remarks' => 'Q1 items canvassing ongoing. Suppliers shortlisted.',
                'items' => [
                    ['name' => 'Classroom laser projectors',    'qty' => 8,  'unit' => 'unit', 'cost' => 55000],
                    ['name' => 'Networking laboratory modules', 'qty' => 15, 'unit' => 'set',  'cost' => 18000],
                ],
            ],
            // CICS Q2: Faculty laptops
            [
                'number' => 'PR-CICS-2026-Q2', 'title' => 'Q2 FY 2026 CICS Purchase Request',
                'office' => $cics->id, 'user' => $officeHead->id, 'fiscal_year' => 2026,
                'status' => 'approved', 'uploaded_at' => now()->subDays(20),
                'remarks' => 'Approved and posted. Delivery expected Q2.',
                'items' => [
                    ['name' => 'Faculty laptop refresh package', 'qty' => 10, 'unit' => 'unit', 'cost' => 85000],
                ],
            ],
            // CICS Q3: Smart boards
            [
                'number' => 'PR-CICS-2026-Q3', 'title' => 'Q3 FY 2026 CICS Purchase Request',
                'office' => $cics->id, 'user' => $officeHead->id, 'fiscal_year' => 2026,
                'status' => 'pending', 'uploaded_at' => now()->subDays(3),
                'remarks' => 'Awaiting procurement processing.',
                'items' => [
                    ['name' => 'Smart board for lecture room', 'qty' => 2, 'unit' => 'unit', 'cost' => 120000],
                ],
            ],
            // COE Q1: Software licenses + 3D printers + Safety PPE
            [
                'number' => 'PR-COE-2026-Q1', 'title' => 'Q1 FY 2026 COE Purchase Request',
                'office' => $coe->id, 'user' => $coeHead->id, 'fiscal_year' => 2026,
                'status' => 'in_progress', 'uploaded_at' => now()->subDays(14),
                'remarks' => 'Purchase order issued. Awaiting vendor confirmation.',
                'items' => [
                    ['name' => 'Engineering software licenses (AutoCAD, MATLAB)', 'qty' => 25, 'unit' => 'license', 'cost' => 35000],
                    ['name' => '3D printer for prototyping laboratory',           'qty' => 3,  'unit' => 'unit',    'cost' => 95000],
                    ['name' => 'Safety equipment and PPE kit',                    'qty' => 50, 'unit' => 'set',     'cost' => 8000],
                ],
            ],
            // COE Q2: Lab benches + Faculty laptops
            [
                'number' => 'PR-COE-2026-Q2', 'title' => 'Q2 FY 2026 COE Purchase Request',
                'office' => $coe->id, 'user' => $coeHead->id, 'fiscal_year' => 2026,
                'status' => 'pending', 'uploaded_at' => now()->subDays(5),
                'remarks' => 'Submitted for procurement processing.',
                'items' => [
                    ['name' => 'Laboratory benches and workstations', 'qty' => 12, 'unit' => 'set',  'cost' => 45000],
                    ['name' => 'Faculty laptop refresh package',      'qty' => 8,  'unit' => 'unit', 'cost' => 85000],
                ],
            ],
            // COE Q1 FY2025 (completed, previous year)
            [
                'number' => 'PR-COE-2025-Q1', 'title' => 'Q1 FY 2025 COE Purchase Request',
                'office' => $coe->id, 'user' => $coeHead->id, 'fiscal_year' => 2025,
                'status' => 'completed', 'uploaded_at' => now()->subMonths(5),
                'remarks' => 'Delivered and accepted. COA endorsed.',
                'items' => [
                    ['name' => 'Laboratory equipment complete set', 'qty' => 1, 'unit' => 'lot', 'cost' => 540000],
                ],
            ],
            // CBA Q1: Software + Materials + Video conferencing
            [
                'number' => 'PR-CBA-2026-Q1', 'title' => 'Q1 FY 2026 CBA Purchase Request',
                'office' => $cba->id, 'user' => $cbaHead->id, 'fiscal_year' => 2026,
                'status' => 'pending', 'uploaded_at' => now()->subDays(2),
                'remarks' => 'Waiting for procurement processing.',
                'items' => [
                    ['name' => 'Business simulation software',      'qty' => 40, 'unit' => 'license', 'cost' => 12000],
                    ['name' => 'Case study materials and textbooks','qty' => 1,  'unit' => 'lot',     'cost' => 150000],
                    ['name' => 'Video conferencing equipment',      'qty' => 2,  'unit' => 'set',     'cost' => 85000],
                ],
            ],
        ];

        foreach ($quarterlyPRs as $prData) {
            $total = array_sum(array_map(fn ($i) => $i['qty'] * $i['cost'], $prData['items']));

            $pr = PurchaseRequest::updateOrCreate(
                ['number' => $prData['number']],
                [
                    'office_id'            => $prData['office'],
                    'created_by_user_id'   => $prData['user'],
                    'submitted_by_user_id' => $prData['user'],
                    'title'                => $prData['title'],
                    'fiscal_year'          => $prData['fiscal_year'],
                    'total_amount'         => $total,
                    'status'               => $prData['status'],
                    'remarks'              => $prData['remarks'],
                    'uploaded_at'          => $prData['uploaded_at'],
                    'submitted_at'         => $prData['uploaded_at'],
                ]
            );

            foreach ($prData['items'] as $item) {
                PurchaseRequestItem::updateOrCreate(
                    ['purchase_request_id' => $pr->id, 'name' => $item['name']],
                    [
                        'name'                 => $item['name'],
                        'description'          => $item['name'],
                        'quantity'             => $item['qty'],
                        'unit'                 => $item['unit'],
                        'estimated_unit_cost'  => $item['cost'],
                        'estimated_total_cost' => $item['qty'] * $item['cost'],
                    ]
                );
            }
        }
    }
}
