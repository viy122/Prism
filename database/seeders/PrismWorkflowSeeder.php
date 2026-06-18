<?php

namespace Database\Seeders;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\MarketScopingReference;
use App\Models\Office;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrismWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $officeHead  = User::where('username', 'office_head')->firstOrFail();
        $finance     = User::where('username', 'finance')->firstOrFail();
        $chancellor  = User::where('username', 'chancellor')->firstOrFail();
        $procurement = User::where('username', 'procurement')->firstOrFail();

        $cics = Office::where('code', 'CICS')->firstOrFail();

        // ── Draft proposal (Office Head currently working on) ──────────────────
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

            // Add market scoping references for desktop computers
            if ($itemData['name'] === 'Desktop computers for computer lab') {
                $refs = [
                    ['supplier' => 'Asiaphil IT Solutions', 'price' => 43500, 'source' => 'lazada', 'url' => 'https://lazada.com.ph/products/asiaphil-desktop-i5', 'title' => 'Desktop PC Intel Core i5-12400 8GB 256GB SSD'],
                    ['supplier' => 'TechWorld Trading', 'price' => 44900, 'source' => 'shopee', 'url' => 'https://shopee.ph/techworld/desktop-core-i5', 'title' => 'Complete Desktop Set i5 12th Gen 16GB DDR4'],
                    ['supplier' => 'CompuCentro PH', 'price' => 46200, 'source' => 'lazada', 'url' => 'https://lazada.com.ph/products/compucentro-desktop', 'title' => 'Intel i5 Desktop Bundle with Monitor and Keyboard'],
                ];

                foreach ($refs as $ref) {
                    MarketScopingReference::updateOrCreate(
                        ['budget_proposal_item_id' => $item->id, 'supplier_name' => $ref['supplier']],
                        [
                            'created_by_user_id' => $officeHead->id,
                            'supplier_name'      => $ref['supplier'],
                            'source_type'        => $ref['source'] === 'lazada' ? 'Lazada PH' : 'Shopee PH',
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

        // ── Submitted proposal (under Finance review) ──────────────────────────
        $submitted = BudgetProposal::updateOrCreate(
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

        $submittedItems = [
            ['name' => 'Faculty laptop refresh package', 'quantity' => 10, 'unit' => 'unit', 'cost' => 85000, 'quarter' => 'Q2', 'category' => 'ICT Equipment'],
            ['name' => 'Classroom laser projectors', 'quantity' => 8, 'unit' => 'unit', 'cost' => 55000, 'quarter' => 'Q1', 'category' => 'ICT Equipment'],
            ['name' => 'Smart board for lecture room', 'quantity' => 2, 'unit' => 'unit', 'cost' => 120000, 'quarter' => 'Q3', 'category' => 'ICT Equipment'],
            ['name' => 'Networking laboratory modules', 'quantity' => 15, 'unit' => 'set', 'cost' => 18000, 'quarter' => 'Q1', 'category' => 'Laboratory Equipment'],
        ];

        $submittedTotal = 0;
        foreach ($submittedItems as $si) {
            $total = $si['quantity'] * $si['cost'];
            $submittedTotal += $total;

            BudgetProposalItem::updateOrCreate(
                ['budget_proposal_id' => $submitted->id, 'name' => $si['name']],
                [
                    'budget_proposal_id'   => $submitted->id,
                    'created_by_user_id'   => $officeHead->id,
                    'name'                 => $si['name'],
                    'description'          => $si['name'],
                    'category'             => $si['category'],
                    'quantity'             => $si['quantity'],
                    'unit'                 => $si['unit'],
                    'estimated_unit_cost'  => $si['cost'],
                    'estimated_total_cost' => $total,
                    'target_quarter'       => $si['quarter'],
                    'status'               => 'pending',
                ]
            );
        }

        $submitted->update(['total_estimated_cost' => $submittedTotal]);

        BudgetProposalReview::updateOrCreate(
            ['budget_proposal_id' => $submitted->id, 'action' => 'submitted'],
            [
                'reviewed_by_user_id' => $officeHead->id,
                'action'              => 'submitted',
                'status_from'         => 'draft',
                'status_to'           => 'submitted',
                'remarks'             => 'Proposal submitted for Finance Office review.',
                'reviewed_at'         => now()->subDays(12),
            ]
        );

        // ── Approved proposal (Chancellor approved, last fiscal year) ──────────
        $approved = BudgetProposal::updateOrCreate(
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
                'remarks'               => 'Approved with minor budget adjustment on ICT equipment line.',
            ]
        );

        foreach ([
            ['action' => 'submitted', 'from' => 'draft', 'to' => 'submitted', 'user' => $officeHead, 'days' => 240, 'note' => 'Submitted for Finance review.'],
            ['action' => 'endorsed', 'from' => 'submitted', 'to' => 'endorsed', 'user' => $finance, 'days' => 210, 'note' => 'Budget ceiling verified. Endorsed to Chancellor.'],
            ['action' => 'approved', 'from' => 'endorsed', 'to' => 'approved', 'user' => $chancellor, 'days' => 180, 'note' => 'Approved with budget adjustment on ICT line.'],
        ] as $rev) {
            BudgetProposalReview::updateOrCreate(
                ['budget_proposal_id' => $approved->id, 'action' => $rev['action']],
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

        // ── Purchase requests (approved + in-progress) ─────────────────────────
        $prs = [
            [
                'number'      => 'PR-CICS-2026-001',
                'title'       => 'Faculty laptop refresh package',
                'total'       => 850000,
                'status'      => 'approved',
                'uploaded_at' => now()->subDays(20),
                'remarks'     => 'Approved and posted. Delivery expected Q2.',
            ],
            [
                'number'      => 'PR-CICS-2026-002',
                'title'       => 'Networking laboratory modules',
                'total'       => 270000,
                'status'      => 'in_progress',
                'uploaded_at' => now()->subDays(10),
                'remarks'     => 'Canvassing ongoing.',
            ],
            [
                'number'      => 'PR-CICS-2026-003',
                'title'       => 'Classroom laser projectors',
                'total'       => 440000,
                'status'      => 'pending',
                'uploaded_at' => now()->subDays(3),
                'remarks'     => 'Awaiting procurement processing.',
            ],
        ];

        foreach ($prs as $pr) {
            PurchaseRequest::updateOrCreate(
                ['number' => $pr['number']],
                [
                    'office_id'           => $cics->id,
                    'created_by_user_id'  => $officeHead->id,
                    'submitted_by_user_id'=> $officeHead->id,
                    'number'              => $pr['number'],
                    'title'               => $pr['title'],
                    'fiscal_year'         => 2026,
                    'total_amount'        => $pr['total'],
                    'status'              => $pr['status'],
                    'remarks'             => $pr['remarks'],
                    'uploaded_at'         => $pr['uploaded_at'],
                    'submitted_at'        => $pr['uploaded_at'],
                ]
            );
        }
    }
}
