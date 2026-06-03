<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrismOfficeHeadController extends Controller
{
    public function dashboard(): View
    {
        return view('prism.office-head.dashboard', $this->withCommon('office-head', 'dashboard', [
            'pageTitle' => 'Office Head / Dean Dashboard',
            'summary' => [
                'totalProposedItems' => 42,
                'totalProposedBudget' => 18450000,
                'approvedItems' => 27,
                'pendingItems' => 9,
                'purchasedThisQuarter' => 18,
                'unpurchasedThisQuarter' => 9,
            ],
            'recentUpdates' => [
                [
                    'title' => 'ICT equipment proposal',
                    'status' => 'Finance Review completed',
                    'time' => 'Today, 10:35 AM',
                    'details' => 'Budget ceiling verified. Forwarded to Chancellor Approval.',
                ],
                [
                    'title' => 'Chemistry laboratory supplies PR',
                    'status' => 'In Progress',
                    'time' => 'Yesterday, 4:18 PM',
                    'details' => 'Procurement Office assigned canvassing officer.',
                ],
                [
                    'title' => 'Library subscription renewal',
                    'status' => 'Returned',
                    'time' => 'May 25, 2026, 2:02 PM',
                    'details' => 'Please attach supplier justification and revised market scope.',
                ],
                [
                    'title' => 'Office furniture replacement',
                    'status' => 'Approved',
                    'time' => 'May 24, 2026, 9:47 AM',
                    'details' => 'Eligible for PR upload this quarter.',
                ],
            ],
        ]));
    }

    public function budgetProposal(): View
    {
        return view('prism.office-head.budget-proposal', $this->withCommon('office-head', 'budget-proposal', [
            'pageTitle' => 'Annual Budget Proposal',
            'proposalForm' => [
                'officeName' => 'College of Engineering',
                'fiscalYear' => '2027',
                'date' => '2026-05-27',
                'totalProposedBudget' => 18450000,
            ],
            'encodedItems' => $this->proposalItems(),
        ]));
    }

    public function marketScoping(): View
    {
        return view('prism.office-head.market-scoping', $this->withCommon('office-head', 'market-scoping', [
            'pageTitle' => 'Market Scoping',
            'proposalItems' => $this->marketScopingItems(),
            // Backend integration point: replace this dummy list with AI-assisted market scoping results.
            'supplierReferences' => $this->marketSupplierReferences(),
        ]));
    }

    public function myProposals(): View
    {
        return view('prism.office-head.my-proposals', $this->withCommon('office-head', 'my-proposals', [
            'pageTitle' => 'My Proposals',
            'statuses' => ['Draft', 'Submitted', 'Under Review', 'Endorsed', 'Returned', 'Approved'],
            'fiscalYears' => ['2027', '2026', '2025'],
            'proposals' => [
                [
                    'id' => 'bp-2027-engineering',
                    'title' => 'FY 2027 Engineering Annual Procurement Plan',
                    'fiscalYear' => '2027',
                    'dateSubmitted' => 'May 27, 2026',
                    'totalAmount' => 18450000,
                    'status' => 'Under Review',
                    'returnedRemarks' => null,
                    'timeline' => [
                        ['step' => 'Submitted', 'timestamp' => 'May 27, 2026 9:15 AM', 'remarks' => 'Proposal submitted by Dean Maria Santos.'],
                        ['step' => 'Finance Review', 'timestamp' => 'May 27, 2026 11:40 AM', 'remarks' => 'Checking budget ceiling and item classification.'],
                        ['step' => 'Chancellor Approval', 'timestamp' => 'Pending', 'remarks' => 'Awaiting endorsement from Finance Office.'],
                    ],
                ],
                [
                    'id' => 'bp-2026-science-lab',
                    'title' => 'Science Laboratory Safety Upgrade',
                    'fiscalYear' => '2026',
                    'dateSubmitted' => 'April 18, 2026',
                    'totalAmount' => 6250000,
                    'status' => 'Returned',
                    'returnedRemarks' => 'Revise the equipment specifications and attach updated supplier quotations.',
                    'timeline' => [
                        ['step' => 'Submitted', 'timestamp' => 'April 18, 2026 2:09 PM', 'remarks' => 'Initial proposal received.'],
                        ['step' => 'Finance Review', 'timestamp' => 'April 22, 2026 10:22 AM', 'remarks' => 'Budget allocation available with required revisions.'],
                        ['step' => 'Chancellor Approval', 'timestamp' => 'April 24, 2026 3:31 PM', 'remarks' => 'Returned for supporting documents.'],
                    ],
                ],
                [
                    'id' => 'bp-2026-library',
                    'title' => 'Library Digital Resources Renewal',
                    'fiscalYear' => '2026',
                    'dateSubmitted' => 'March 8, 2026',
                    'totalAmount' => 2150000,
                    'status' => 'Approved',
                    'returnedRemarks' => null,
                    'timeline' => [
                        ['step' => 'Submitted', 'timestamp' => 'March 8, 2026 8:47 AM', 'remarks' => 'Proposal submitted with subscription matrix.'],
                        ['step' => 'Finance Review', 'timestamp' => 'March 10, 2026 1:16 PM', 'remarks' => 'Funding source confirmed.'],
                        ['step' => 'Chancellor Approval', 'timestamp' => 'March 12, 2026 5:02 PM', 'remarks' => 'Approved for PR preparation.'],
                    ],
                ],
                [
                    'id' => 'bp-2025-clinic',
                    'title' => 'Clinic Emergency Readiness Supplies',
                    'fiscalYear' => '2025',
                    'dateSubmitted' => 'November 14, 2025',
                    'totalAmount' => 780000,
                    'status' => 'Endorsed',
                    'returnedRemarks' => null,
                    'timeline' => [
                        ['step' => 'Submitted', 'timestamp' => 'November 14, 2025 10:11 AM', 'remarks' => 'Submitted for year-end supplemental procurement.'],
                        ['step' => 'Finance Review', 'timestamp' => 'November 17, 2025 4:45 PM', 'remarks' => 'Endorsed within available balance.'],
                        ['step' => 'Chancellor Approval', 'timestamp' => 'Pending', 'remarks' => 'For final signing.'],
                    ],
                ],
            ],
        ]));
    }

    public function purchaseRequests(): View
    {
        return view('prism.office-head.purchase-requests', $this->withCommon('office-head', 'purchase-requests', [
            'pageTitle' => 'Purchase Requests',
            'purchaseItems' => [
                [
                    'id' => 'pr-laptops',
                    'itemName' => 'Faculty laptop refresh package',
                    'approvedAmount' => 2850000,
                    'targetQuarter' => 'Q2',
                    'procurementStatus' => 'Ready for PR',
                    'prStatus' => 'Pending',
                    'remarks' => 'Upload signed PR with the approved item list as attachment.',
                    'ocr' => [
                        'prNumber' => 'PR-2026-ENG-018',
                        'date' => 'May 27, 2026',
                        'items' => '30 laptop units, docking stations, warranty support',
                        'amount' => 2850000,
                    ],
                ],
                [
                    'id' => 'pr-projectors',
                    'itemName' => 'Classroom laser projectors',
                    'approvedAmount' => 1260000,
                    'targetQuarter' => 'Q2',
                    'procurementStatus' => 'Awaiting PR',
                    'prStatus' => 'Pending',
                    'remarks' => 'Ensure room deployment list is included in the PR.',
                    'ocr' => [
                        'prNumber' => 'PR-2026-ENG-019',
                        'date' => 'May 27, 2026',
                        'items' => '12 laser projectors with ceiling mount kits',
                        'amount' => 1260000,
                    ],
                ],
                [
                    'id' => 'pr-furniture',
                    'itemName' => 'Faculty office furniture replacement',
                    'approvedAmount' => 920000,
                    'targetQuarter' => 'Q3',
                    'procurementStatus' => 'PR received',
                    'prStatus' => 'In Progress',
                    'remarks' => 'Technical specifications are under procurement validation.',
                    'ocr' => [
                        'prNumber' => 'PR-2026-ENG-014',
                        'date' => 'May 21, 2026',
                        'items' => 'Ergonomic chairs, desks, filing cabinets',
                        'amount' => 920000,
                    ],
                ],
                [
                    'id' => 'pr-lab',
                    'itemName' => 'Electronics laboratory instruments',
                    'approvedAmount' => 3400000,
                    'targetQuarter' => 'Q4',
                    'procurementStatus' => 'For procurement schedule',
                    'prStatus' => 'Delayed',
                    'remarks' => 'Procurement Office requested updated delivery lead times.',
                    'ocr' => [
                        'prNumber' => 'PR-2026-ENG-021',
                        'date' => 'May 27, 2026',
                        'items' => 'Oscilloscopes, power supplies, signal generators',
                        'amount' => 3400000,
                    ],
                ],
            ],
        ]));
    }

    public function placeholder(string $role): View
    {
        $titles = [
            'vice-chancellor' => 'Vice Chancellor',
        ];

        return view('prism.placeholder', $this->withCommon($role, null, [
            'pageTitle' => $titles[$role],
            'sectionTitle' => $titles[$role],
        ]));
    }

    private function withCommon(string $activeRole, ?string $activeOfficePage, array $data): array
    {
        return array_merge([
            'activeRole' => $activeRole,
            'activeOfficePage' => $activeOfficePage,
            'activeModulePage' => $activeOfficePage,
            'brandHref' => route('office-head.dashboard'),
            'roleNavigation' => [
                ['slug' => 'office-head', 'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office', 'label' => 'Finance Office', 'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office', 'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor', 'label' => 'Chancellor', 'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor', 'label' => 'Vice Chancellor', 'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel' => 'Office Head / Dean pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard', 'label' => 'Dashboard', 'href' => route('office-head.dashboard'), 'icon' => 'layout-dashboard'],
                ['slug' => 'market-scoping', 'label' => 'Market Scoping', 'href' => route('office-head.market-scoping'), 'icon' => 'search'],
                ['slug' => 'budget-proposal', 'label' => 'Budget Proposal', 'href' => route('office-head.budget-proposal'), 'icon' => 'file-plus-2'],
                ['slug' => 'my-proposals', 'label' => 'My Proposals', 'href' => route('office-head.my-proposals'), 'icon' => 'folder-check'],
                ['slug' => 'purchase-requests', 'label' => 'Purchase Requests', 'href' => route('office-head.purchase-requests'), 'icon' => 'receipt-text'],
            ],
        ], $data);
    }

    private function marketScopingItems(): array
    {
        return [
            [
                'id' => 'item-laptops',
                'proposalCode' => 'BP-2027-ENG-001',
                'itemName' => 'Faculty laptop refresh package',
                'category' => 'ICT Equipment',
                'brandPreference' => 'Any enterprise-grade brand',
                'specification' => 'Intel Core i7 or equivalent, 16GB RAM, 512GB SSD, 14-inch display, 3-year warranty',
                'estimatedUnitCost' => 95000,
                'quantity' => 30,
                'unit' => 'unit',
            ],
            [
                'id' => 'item-projectors',
                'proposalCode' => 'BP-2027-ENG-002',
                'itemName' => 'Classroom laser projectors',
                'category' => 'AV Equipment',
                'brandPreference' => 'Epson, BenQ, ViewSonic, or equivalent',
                'specification' => 'Laser projector, 4,000 lumens minimum, HDMI, ceiling mount compatible, education warranty',
                'estimatedUnitCost' => 105000,
                'quantity' => 12,
                'unit' => 'unit',
            ],
            [
                'id' => 'item-lab',
                'proposalCode' => 'BP-2027-ENG-003',
                'itemName' => 'Electronics laboratory instruments',
                'category' => 'Laboratory Equipment',
                'brandPreference' => 'Tektronix, Rigol, Keysight, or equivalent',
                'specification' => 'Oscilloscope set with signal generator, power supply, probes, and calibration support',
                'estimatedUnitCost' => 340000,
                'quantity' => 10,
                'unit' => 'set',
            ],
        ];
    }

    private function marketSupplierReferences(): array
    {
        return collect([
            [
                'id' => 'ref-laptop-northstar',
                'itemId' => 'item-laptops',
                'supplierName' => 'Northstar Systems',
                'productTitle' => 'Enterprise laptop bundle, Core i7, 16GB RAM, 512GB SSD',
                'price' => 93200,
                'sourceLink' => 'https://example.com/northstar-enterprise-laptop-reference',
                'dateAccessed' => 'May 28, 2026',
                'specs' => 'Core i7 equivalent, 16GB RAM, 512GB SSD, 14-inch display, 3-year support',
                'availability' => 'Available',
                'credibility' => 'Verified supplier page',
                'sourceType' => 'Published Supplier Page',
                'brand' => 'Lenovo',
                'category' => 'ICT Equipment',
                'keywords' => ['laptop', 'core i7', '16gb', '512gb ssd', 'warranty'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-laptop-campustech',
                'itemId' => 'item-laptops',
                'supplierName' => 'CampusTech Supply',
                'productTitle' => 'Business notebook education package with docking station',
                'price' => 97400,
                'sourceLink' => 'https://example.com/campustech-business-notebook-reference',
                'dateAccessed' => 'May 28, 2026',
                'specs' => 'Core i7, 16GB RAM, 512GB SSD, USB-C dock, onsite warranty',
                'availability' => 'Available',
                'credibility' => 'Accredited quotation reference',
                'sourceType' => 'Accredited Supplier Quote',
                'brand' => 'HP',
                'category' => 'ICT Equipment',
                'keywords' => ['laptop', 'core i7', '16gb', '512gb ssd', 'dock'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-laptop-metro',
                'itemId' => 'item-laptops',
                'supplierName' => 'Metro Digital Procurement',
                'productTitle' => 'Commercial laptop 14-inch 16GB RAM procurement reference',
                'price' => 88750,
                'sourceLink' => 'https://example.com/metro-commercial-laptop-reference',
                'dateAccessed' => 'May 27, 2026',
                'specs' => 'Core i5, 16GB RAM, 512GB SSD, 14-inch display, 1-year warranty',
                'availability' => 'Limited stock',
                'credibility' => 'Supplier catalog reference',
                'sourceType' => 'Published Supplier Page',
                'brand' => 'Dell',
                'category' => 'ICT Equipment',
                'keywords' => ['laptop', 'core i5', '16gb', '512gb ssd'],
                'matchLevel' => 'Partial match',
                'isValidMatch' => false,
            ],
            [
                'id' => 'ref-laptop-procurelink',
                'itemId' => 'item-laptops',
                'supplierName' => 'ProcureLink PH',
                'productTitle' => 'Faculty workstation laptop, 16GB memory, 3-year coverage',
                'price' => 101500,
                'sourceLink' => 'https://example.com/procurelink-faculty-laptop-reference',
                'dateAccessed' => 'May 26, 2026',
                'specs' => 'Core i7, 16GB RAM, 1TB SSD, 14-inch display, 3-year warranty',
                'availability' => 'Available',
                'credibility' => 'Government reference listing',
                'sourceType' => 'Government Reference',
                'brand' => 'Acer',
                'category' => 'ICT Equipment',
                'keywords' => ['laptop', 'core i7', '16gb', '1tb ssd', 'warranty'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-laptop-southridge',
                'itemId' => 'item-laptops',
                'supplierName' => 'Southridge IT Solutions',
                'productTitle' => 'Education laptop package with OS and warranty',
                'price' => 91800,
                'sourceLink' => 'https://example.com/southridge-education-laptop-reference',
                'dateAccessed' => 'May 25, 2026',
                'specs' => 'Core i7 equivalent, 16GB RAM, 512GB SSD, licensed OS, 3-year warranty',
                'availability' => 'Available',
                'credibility' => 'Recent supplier quotation',
                'sourceType' => 'Accredited Supplier Quote',
                'brand' => 'Asus',
                'category' => 'ICT Equipment',
                'keywords' => ['laptop', 'core i7', '16gb', '512gb ssd', 'licensed os'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-projector-avlearn',
                'itemId' => 'item-projectors',
                'supplierName' => 'AV Learning Solutions',
                'productTitle' => 'Laser classroom projector, 4,200 lumens, HDMI',
                'price' => 102500,
                'sourceLink' => 'https://example.com/avlearn-laser-projector-reference',
                'dateAccessed' => 'May 28, 2026',
                'specs' => '4,200 lumens laser projector, HDMI, ceiling mount compatible, 3-year warranty',
                'availability' => 'Available',
                'credibility' => 'Verified supplier page',
                'sourceType' => 'Published Supplier Page',
                'brand' => 'Epson',
                'category' => 'AV Equipment',
                'keywords' => ['projector', 'laser', '4200 lumens', 'hdmi', 'ceiling mount'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-projector-metroclass',
                'itemId' => 'item-projectors',
                'supplierName' => 'Metro Classroom Tech',
                'productTitle' => 'Education laser projector with installation kit',
                'price' => 106800,
                'sourceLink' => 'https://example.com/metroclass-laser-projector-reference',
                'dateAccessed' => 'May 27, 2026',
                'specs' => '4,000 lumens laser projector, HDMI, mount kit, education warranty',
                'availability' => 'Available',
                'credibility' => 'Accredited quotation reference',
                'sourceType' => 'Accredited Supplier Quote',
                'brand' => 'BenQ',
                'category' => 'AV Equipment',
                'keywords' => ['projector', 'laser', '4000 lumens', 'hdmi', 'mount kit'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-projector-viewtech',
                'itemId' => 'item-projectors',
                'supplierName' => 'ViewTech Manila',
                'productTitle' => 'Classroom display projector, lamp-based model',
                'price' => 74500,
                'sourceLink' => 'https://example.com/viewtech-lamp-projector-reference',
                'dateAccessed' => 'May 26, 2026',
                'specs' => '3,500 lumens lamp projector, HDMI, tabletop use',
                'availability' => 'Available',
                'credibility' => 'Supplier catalog reference',
                'sourceType' => 'Published Supplier Page',
                'brand' => 'ViewSonic',
                'category' => 'AV Equipment',
                'keywords' => ['projector', 'lamp', '3500 lumens', 'hdmi'],
                'matchLevel' => 'Partial match',
                'isValidMatch' => false,
            ],
            [
                'id' => 'ref-projector-learnware',
                'itemId' => 'item-projectors',
                'supplierName' => 'LearnWare Systems',
                'productTitle' => 'Laser projector for lecture rooms, 4,500 lumens',
                'price' => 111200,
                'sourceLink' => 'https://example.com/learnware-projector-reference',
                'dateAccessed' => 'May 25, 2026',
                'specs' => '4,500 lumens laser projector, HDMI, ceiling mount, service warranty',
                'availability' => 'For confirmation',
                'credibility' => 'Recent supplier quotation',
                'sourceType' => 'Accredited Supplier Quote',
                'brand' => 'Panasonic',
                'category' => 'AV Equipment',
                'keywords' => ['projector', 'laser', '4500 lumens', 'hdmi', 'ceiling mount'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-lab-precision',
                'itemId' => 'item-lab',
                'supplierName' => 'Precision Lab Depot',
                'productTitle' => 'Electronics lab bench instrument set',
                'price' => 338500,
                'sourceLink' => 'https://example.com/precision-lab-bench-reference',
                'dateAccessed' => 'May 28, 2026',
                'specs' => 'Oscilloscope, signal generator, DC power supply, probes, calibration certificate',
                'availability' => 'Available',
                'credibility' => 'Verified supplier page',
                'sourceType' => 'Published Supplier Page',
                'brand' => 'Rigol',
                'category' => 'Laboratory Equipment',
                'keywords' => ['oscilloscope', 'signal generator', 'power supply', 'calibration'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-lab-signalworks',
                'itemId' => 'item-lab',
                'supplierName' => 'SignalWorks Trading',
                'productTitle' => 'Circuit laboratory instrumentation package',
                'price' => 343200,
                'sourceLink' => 'https://example.com/signalworks-instrument-package-reference',
                'dateAccessed' => 'May 28, 2026',
                'specs' => 'Digital oscilloscope, waveform generator, bench supply, probes, training support',
                'availability' => 'Available',
                'credibility' => 'Accredited quotation reference',
                'sourceType' => 'Accredited Supplier Quote',
                'brand' => 'Tektronix',
                'category' => 'Laboratory Equipment',
                'keywords' => ['oscilloscope', 'waveform generator', 'bench supply', 'training'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
            [
                'id' => 'ref-lab-techsource',
                'itemId' => 'item-lab',
                'supplierName' => 'TechSource Instruments',
                'productTitle' => 'Electronics training instrument kit',
                'price' => 319800,
                'sourceLink' => 'https://example.com/techsource-training-kit-reference',
                'dateAccessed' => 'May 26, 2026',
                'specs' => 'Entry oscilloscope, compact signal generator, power supply, limited warranty',
                'availability' => 'Limited stock',
                'credibility' => 'Supplier catalog reference',
                'sourceType' => 'Published Supplier Page',
                'brand' => 'Keysight',
                'category' => 'Laboratory Equipment',
                'keywords' => ['oscilloscope', 'signal generator', 'power supply', 'limited warranty'],
                'matchLevel' => 'Partial match',
                'isValidMatch' => false,
            ],
            [
                'id' => 'ref-lab-edulab',
                'itemId' => 'item-lab',
                'supplierName' => 'EduLab Scientific',
                'productTitle' => 'Advanced electronics instrument set with calibration',
                'price' => 356000,
                'sourceLink' => 'https://example.com/edulab-advanced-instrument-set-reference',
                'dateAccessed' => 'May 25, 2026',
                'specs' => 'Oscilloscope, arbitrary waveform generator, triple-output power supply, calibration support',
                'availability' => 'For confirmation',
                'credibility' => 'Government reference listing',
                'sourceType' => 'Government Reference',
                'brand' => 'GW Instek',
                'category' => 'Laboratory Equipment',
                'keywords' => ['oscilloscope', 'waveform generator', 'triple-output supply', 'calibration'],
                'matchLevel' => 'Strong match',
                'isValidMatch' => true,
            ],
        ])->map(fn (array $reference) => $this->enrichMarketReference($reference))->all();
    }

    private function enrichMarketReference(array $reference): array
    {
        $requestedSpecs = [
            'item-laptops' => ['Core i7 or equivalent', '16GB RAM', '512GB SSD', '14-inch display', '3-year warranty'],
            'item-projectors' => ['Laser projector', '4,000 lumens minimum', 'HDMI', 'Ceiling mount compatible', 'Education warranty'],
            'item-lab' => ['Oscilloscope', 'Signal generator', 'Power supply', 'Probes', 'Calibration support'],
        ];

        $specs = collect(explode(',', $reference['specs']))
            ->map(fn (string $spec) => trim($spec))
            ->filter()
            ->values()
            ->all();

        $missingSpecs = [];

        if (! $reference['isValidMatch']) {
            $missingSpecs = match ($reference['itemId']) {
                'item-laptops' => ['Core i7 or equivalent', '3-year warranty'],
                'item-projectors' => ['Laser projection', '4,000 lumens minimum', 'Ceiling mount compatibility'],
                'item-lab' => ['Advanced instrument grade', 'Calibration support'],
                default => ['Some requested specifications need confirmation'],
            };
        }

        $score = $reference['isValidMatch']
            ? match ($reference['credibility']) {
                'Verified supplier page', 'Government reference listing' => 94,
                'Accredited quotation reference' => 91,
                default => 88,
            }
            : 68;

        return array_merge($reference, [
            'imageLabel' => $reference['brand'] ?? $reference['category'],
            'shortSpecs' => implode(' | ', array_slice($specs, 0, 4)),
            'fullSpecs' => $specs,
            'warrantySupport' => str($reference['specs'])->lower()->contains(['warranty', 'support', 'coverage', 'calibration'])
                ? 'Warranty/support detail is included in the supplier reference and should be verified during procurement review.'
                : 'Warranty/support detail is not explicit and should be confirmed before final documentation.',
            'credibilityInfo' => match ($reference['credibility']) {
                'Verified supplier page' => 'Published supplier information is available for validation by the requesting office and Finance Office.',
                'Accredited quotation reference' => 'Reference is treated as an accredited quotation source for market scoping documentation.',
                'Supplier catalog reference' => 'Catalog listing can support price discovery but may require stronger supporting quotation documents.',
                'Government reference listing' => 'Government listing can support reasonableness checks and documentary review.',
                default => 'Supplier credibility should be reviewed before attaching the reference.',
            },
            'requestedSpecs' => $requestedSpecs[$reference['itemId']] ?? [],
            'matchedSpecs' => $reference['isValidMatch'] ? $specs : array_slice($specs, 0, 3),
            'missingSpecs' => $missingSpecs,
            'matchScore' => $score,
            'matchStatus' => $reference['matchLevel'],
        ]);
    }

    private function proposalItems(): array
    {
        return [
            [
                'id' => 'item-laptops',
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
                'id' => 'item-projectors',
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
                'id' => 'item-lab',
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
        ];
    }
}
