@extends('prism.layouts.app')
@section('title', 'Procurement Reports | Chancellor')

@push('head-extras')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
@endpush

@push('page-css')
<style>
    .content {
        padding: 28px 32px 56px; flex: 1; display: flex; flex-direction: column; gap: 20px;
        --m: var(--crimson); --gold: #c9a84c; --white: #ffffff;
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
        --sh-lg: 0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub   { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head  { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 13px; }
    .stat-wrap { position: relative; min-width: 0; outline: none; z-index: 1; }
    .stat-wrap:hover,
    .stat-wrap:focus-within { z-index: 80; }
    .stat-card {
        position: relative; overflow: visible;
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 15px; padding: 16px 18px;
        box-shadow: var(--sh-sm);
        transition: box-shadow .25s, border-color .25s, transform .2s;
        min-height: 112px; height: 100%;
        display: flex; flex-direction: column;
    }
    .stat-card:hover,
    .stat-wrap:hover .stat-card,
    .stat-wrap:focus-within .stat-card {
        border-color: rgba(192,57,59,.45);
        box-shadow:
            0 0 0 1px rgba(192,57,59,.20),
            0 10px 28px rgba(139,26,28,.16),
            0 2px 8px rgba(15,23,42,.08);
        transform: translateY(-2px);
    }
    .stat-card::before { content: ""; position: absolute; left: 0; top: 24px; width: 4px; height: 35px; border-radius: 0 4px 4px 0; background: var(--crimson); }
    .stat-icon { position: absolute; right: 16px; top: 14px; width: 36px; height: 36px; border-radius: 10px; background: var(--crimson-mid); display: flex; align-items: center; justify-content: center; }
    .stat-icon svg { width: 18px; height: 18px; stroke: var(--crimson); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s400); margin-bottom: 8px; max-width: calc(100% - 58px); }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--crimson); display: block; letter-spacing: -.7px; line-height: 1; margin-bottom: 7px; }
    .stat-value.sm { font-size: clamp(24px, 1.55vw, 28px); letter-spacing: -.7px; white-space: nowrap; }
    .stat-desc  { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

    .stat-kpi-popover {
        position: absolute; z-index: 90; left: 6px; top: calc(100% + 9px);
        width: min(520px, calc(100vw - 48px)); max-width: 520px;
        background: #fff; border: 1px solid var(--crimson-border);
        border-radius: 10px;
        box-shadow:
            0 0 0 1px rgba(192,57,59,.12),
            0 20px 52px rgba(15,23,42,.18),
            0 10px 28px rgba(139,26,28,.12);
        padding: 18px; color: var(--s700);
        opacity: 0; pointer-events: none; transform: translateY(-4px);
        visibility: hidden; transition: opacity .16s ease, transform .16s ease, visibility .16s;
    }
    .stat-kpi-popover::before {
        content: ""; position: absolute; top: -9px; left: 190px;
        width: 18px; height: 18px; background: #fff;
        border-left: 1px solid var(--crimson-border);
        border-top: 1px solid var(--crimson-border);
        transform: rotate(45deg);
    }
    .stat-wrap:nth-child(3) .stat-kpi-popover,
    .stat-wrap:nth-child(4) .stat-kpi-popover { left: auto; right: 0; }
    .stat-wrap:nth-child(3) .stat-kpi-popover::before,
    .stat-wrap:nth-child(4) .stat-kpi-popover::before { left: auto; right: 190px; }
    .stat-wrap:hover .stat-kpi-popover,
    .stat-wrap:focus-within .stat-kpi-popover {
        opacity: 1; pointer-events: auto; transform: translateY(0); visibility: visible;
    }
    .stat-kpi-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .stat-kpi-eyebrow { font-size: 9px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--crimson); margin-bottom: 4px; }
    .stat-kpi-title { font-size: 18px; font-weight: 800; color: var(--s900); letter-spacing: -.35px; margin: 0 0 5px; line-height: 1.15; }
    .stat-kpi-lead { font-size: 12.5px; color: var(--s600); line-height: 1.5; margin: 0; }
    .stat-kpi-count { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 11px; font-weight: 800; color: var(--crimson); }
    .stat-kpi-scroll { max-height: 310px; overflow: auto; padding-right: 3px; }
    .stat-kpi-list { display: flex; flex-direction: column; gap: 8px; }
    .stat-kpi-row {
        display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 10px;
        background: var(--s50); border: 1px solid var(--s200);
        border-radius: 8px; padding: 11px 12px;
    }
    .stat-kpi-row-main { min-width: 0; }
    .stat-kpi-row-title { color: var(--s900); font-size: 12.5px; font-weight: 800; line-height: 1.35; margin-bottom: 3px; overflow-wrap: anywhere; }
    .stat-kpi-row-meta { color: var(--s500); font-size: 11px; line-height: 1.55; }
    .stat-kpi-row-note { color: var(--s600); font-size: 11px; line-height: 1.5; margin-top: 3px; overflow-wrap: anywhere; }
    .stat-kpi-row-side { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; text-align: right; }
    .stat-kpi-row-side strong { color: var(--crimson); font-size: 12px; white-space: nowrap; }
    .stat-kpi-open-link {
        grid-column: 1 / -1; width: max-content;
        display: inline-flex; align-items: center; gap: 5px;
        color: var(--crimson); text-decoration: none; font-size: 11px; font-weight: 800;
    }
    .stat-kpi-open-link:hover { text-decoration: underline; }
    .stat-kpi-open-link svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-kpi-empty { padding: 18px 10px; text-align: center; color: var(--s400); font-size: 12.5px; font-weight: 700; }
    .pd-badge { display: inline-flex; align-items: center; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; white-space: nowrap; line-height: 1.4; flex-shrink: 0; }
    .pd-badge-approved  { background: #dcfce7; color: #166534; }
    .pd-badge-pending   { background: #fef3c7; color: #92400e; }
    .pd-badge-returned  { background: #fee2e2; color: #991b1b; }
    .pd-badge-info      { background: #e0f2fe; color: #0369a1; }
    .pd-badge-progress  { background: #ede9fe; color: #4c1d95; }

    .btn-print { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px; background: var(--crimson); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: opacity .2s; white-space: nowrap; }
    .btn-print:hover { opacity: .88; }
    .btn-print svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .report-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .office-filter-wrap { position: relative; display: inline-flex; align-items: center; }
    .office-filter-wrap i { position: absolute; left: 14px; font-size: 15px; color: var(--m); pointer-events: none; }
    .office-filter-select { height: 42px; padding: 0 14px 0 38px; border-radius: 10px; border: 1px solid var(--s300); background: var(--white); font-size: 13px; font-weight: 600; color: var(--s700); font-family: 'Poppins', sans-serif; cursor: pointer; }
    .print-only-filter-note { display: none; }

    .completion-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--crimson-mid); color: var(--crimson); border: 1px solid var(--crimson-border); white-space: nowrap; }
    .completion-legend { display: flex; align-items: center; justify-content: center; gap: 8px 14px; flex-wrap: wrap; margin: 14px 0 0; font-size: 11.5px; font-weight: 700; color: var(--s600); }
    .completion-legend-item { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .completion-legend-dot { width: 10px; height: 10px; border-radius: 999px; flex-shrink: 0; }
    .completion-legend-dot.complete { background: #3b6d11; }
    .completion-legend-dot.partial { background: #c9a84c; }
    .completion-legend-dot.none { background: #a32d2d; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 52vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--crimson-mid); }
    .quarter-office-cell { font-weight: 800; color: var(--s600); vertical-align: top; background: var(--s50); border-right: 1px solid var(--s100); }
    .prog-wrap  { min-width: 140px; }
    .prog-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 6px; }
    .prog-track { height: 10px; border-radius: 99px; background: var(--s100); overflow: hidden; border: 1px solid var(--s200); }
    .prog-fill  { height: 100%; border-radius: 99px; background: var(--m); }
    .prog-fill.complete { background: #3b6d11; }
    .prog-fill.partial { background: #c9a84c; }
    .prog-fill.none { background: #a32d2d; min-width: 6px; }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-low     { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-medium  { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-high    { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-delayed { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .two-col > .card { display: flex; flex-direction: column; }
    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .chart-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .chart-icon-badge {
        width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
        background: rgba(104,16,18,.07);
        display: flex; align-items: center; justify-content: center;
    }
    .chart-icon-badge i { font-size: 18px; color: var(--m); }
    .chart-card-head-text { flex: 1; min-width: 0; }
    .chart-wrap  { position: relative; width: 100%; height: 230px; }
    .report-meta { font-size: 11px; color: var(--s400); margin-top: 2px; }

    .pd-chart-legend { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px 14px; margin-top: 12px; font-size: 11.5px; font-weight: 600; color: var(--s600); }
    .pd-chart-legend-item { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .pd-chart-legend-dot { width: 9px; height: 9px; border-radius: 3px; flex-shrink: 0; }

    .delay-list { display: flex; flex-direction: column; gap: 10px; max-height: 52vh; overflow-y: auto; padding-right: 4px; }
    .delay-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 14px; border: 1px solid #f7c1c1; background: rgba(252,235,235,.6); transition: background .15s, box-shadow .15s; }
    .delay-item:hover { background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.06); }
    .delay-office { font-size: 13px; font-weight: 700; color: var(--s900); margin-bottom: 4px; }
    .delay-entry  { font-size: 12px; color: var(--s600); line-height: 1.7; }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .stat-wrap .stat-kpi-popover { left: 6px; right: auto; }
        .stat-wrap .stat-kpi-popover::before { left: 160px; right: auto; }
        .stat-wrap:nth-child(even) .stat-kpi-popover { left: auto; right: 0; }
        .stat-wrap:nth-child(even) .stat-kpi-popover::before { left: auto; right: 160px; }
        .two-col { grid-template-columns: 1fr; }
        .charts-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
    @media (max-width: 640px)  {
        .stats-grid { grid-template-columns: 1fr; }
        .stat-wrap:nth-child(n) .stat-kpi-popover {
            position: fixed; left: 16px; right: 16px; top: 96px;
            width: auto; max-width: none; max-height: calc(100vh - 128px); overflow: auto;
        }
        .stat-wrap:nth-child(n) .stat-kpi-popover::before { display: none; }
        .stat-kpi-row { grid-template-columns: 1fr; }
        .stat-kpi-row-side { align-items: flex-start; text-align: left; }
    }

    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }

    @media print {
        .btn-print, .office-filter-wrap { display: none !important; }
        .print-only-filter-note { display: block !important; font-size: 12px; font-weight: 700; color: var(--m); margin-top: 6px; }
        .content { padding: 0; }
        body { background: #fff; }
        .table-wrap { max-height: none; overflow: visible; }
    }
</style>
@endpush

@section('content')
@include('prism.shared.asset-report-tabs', ['assetReportRole' => 'chancellor'])
@include('prism.partials.report-version')

<div class="content">

    @php
        $selectedQuarter = $selectedQuarter ?? '';
        $quarterOptions = $quarterOptions ?? ['Q1', 'Q2', 'Q3', 'Q4'];
        $accomplishmentCollection = collect($accomplishmentRows);
        $totalTargeted    = $accomplishmentCollection->sum('targeted');
        $totalProcured    = $accomplishmentCollection->sum('procured');
        $campusCompletion = $totalTargeted > 0 ? round(($totalProcured / $totalTargeted) * 100) : 0;
        $scopeLabel       = implode(' · ', array_filter([$selectedOffice ?: 'Campus-wide', $selectedQuarter ?: null]));
        $printFilterNote  = collect([
            $selectedOffice ? 'office: ' . $selectedOffice : null,
            $selectedQuarter ? 'quarter: ' . $selectedQuarter : null,
        ])->filter()->implode(' · ');
        $reportUrl = function ($office = null) use ($selectedQuarter) {
            $params = ['year' => app(\App\Services\FiscalYearContext::class)->year, 'version' => request()->query('version')];
            if ($office && preg_match('/^[A-Za-z0-9_-]+$/', $office)) {
                $params['office'] = $office;
            }
            if ($selectedQuarter) {
                $params['quarter'] = $selectedQuarter;
            }

            return route('chancellor.procurement-reports', $params);
        };
        $rateBadge = fn ($rate) => $rate >= 75
            ? ['Complete', 'pd-badge-approved']
            : ($rate >= 40 ? ['In progress', 'pd-badge-progress'] : ['Needs attention', 'pd-badge-returned']);

        $targetRows = $accomplishmentCollection
            ->sortByDesc('targeted')
            ->map(function ($row) use ($reportUrl) {
                $remaining = max(0, (int) $row['targeted'] - (int) $row['procured']);

                return [
                    'title'      => $row['office'],
                    'meta'       => [
                        number_format($row['procured']) . ' procured',
                        number_format($remaining) . ' remaining',
                        $row['completionRate'] . '% complete',
                    ],
                    'side'       => number_format($row['targeted']),
                    'badge'      => 'Targets',
                    'badgeClass' => 'pd-badge-info',
                    'url'        => $reportUrl($row['office']),
                ];
            })
            ->values();

        $procuredRows = $accomplishmentCollection
            ->filter(fn ($row) => (int) $row['procured'] > 0)
            ->sortByDesc('procured')
            ->map(function ($row) use ($reportUrl) {
                return [
                    'title'      => $row['office'],
                    'meta'       => [
                        number_format($row['targeted']) . ' targeted',
                        $row['completionRate'] . '% completion',
                    ],
                    'side'       => number_format($row['procured']),
                    'badge'      => 'Procured',
                    'badgeClass' => 'pd-badge-approved',
                    'url'        => $reportUrl($row['office']),
                ];
            })
            ->values();

        $completionRows = $accomplishmentCollection
            ->sortByDesc('completionRate')
            ->map(function ($row) use ($rateBadge, $reportUrl) {
                [$badge, $badgeClass] = $rateBadge((int) $row['completionRate']);

                return [
                    'title'      => $row['office'],
                    'meta'       => [
                        number_format($row['procured']) . ' procured',
                        number_format($row['targeted']) . ' targeted',
                    ],
                    'side'       => $row['completionRate'] . '%',
                    'badge'      => $badge,
                    'badgeClass' => $badgeClass,
                    'url'        => $reportUrl($row['office']),
                ];
            })
            ->values();

        $delayedRows = collect($delayedByOffice)
            ->flatMap(function ($items, $office) use ($reportUrl) {
                return collect($items)->map(function ($item) use ($office, $reportUrl) {
                    return [
                        'title'      => $item['item'],
                        'meta'       => [$office],
                        'note'       => $item['remarks'],
                        'side'       => $item['prNumber'],
                        'badge'      => 'Delayed',
                        'badgeClass' => 'pd-badge-returned',
                        'url'        => $reportUrl($office),
                    ];
                });
            })
            ->sortBy(fn ($row) => implode('|', [$row['meta'][0] ?? '', strtolower($row['title'] ?? '')]))
            ->values();
        $delayedCount = $delayedRows->count();

        $kpiCards = [
            [
                'key'   => 'campusTargets',
                'label' => 'Campus Targets',
                'value' => number_format($totalTargeted),
                'hint'  => 'Procurement targets across monitored offices',
                'icon'  => '<svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/></svg>',
            ],
            [
                'key'   => 'itemsProcured',
                'label' => 'Items Procured',
                'value' => number_format($totalProcured),
                'hint'  => 'Completed procurement items across campus',
                'icon'  => '<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            ],
            [
                'key'   => 'completionRate',
                'label' => 'Completion Rate',
                'value' => $campusCompletion . '%',
                'hint'  => 'Campus-wide procurement accomplishment',
                'icon'  => '<svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
            ],
            [
                'key'   => 'delayedItems',
                'label' => 'Delayed Items',
                'value' => number_format($delayedCount),
                'hint'  => 'Risk items grouped by office for follow-up',
                'icon'  => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
            ],
        ];

        $kpiDetails = [
            'campusTargets' => [
                'title'      => 'Campus Targets by Office',
                'lead'       => 'Targeted procurement items from the monitored endorsed and approved PPMPs.',
                'rows'       => $targetRows,
                'empty'      => 'No procurement targets found for this report scope.',
                'countLabel' => 'office target record(s)',
            ],
            'itemsProcured' => [
                'title'      => 'Procured Items by Office',
                'lead'       => 'Offices with completed procurement items in the current report scope.',
                'rows'       => $procuredRows,
                'empty'      => 'No completed procurement items found for this report scope.',
                'countLabel' => 'office procurement record(s)',
            ],
            'completionRate' => [
                'title'      => 'Completion Rate by Office',
                'lead'       => 'Office accomplishment rates computed from procured items over targeted items.',
                'rows'       => $completionRows,
                'empty'      => 'No completion rates available yet.',
                'countLabel' => 'office rate record(s)',
            ],
            'delayedItems' => [
                'title'      => 'Delayed PR Items',
                'lead'       => 'Purchase requests still open beyond the 30-day target window.',
                'rows'       => $delayedRows,
                'empty'      => 'No delayed purchase requests found for this report scope.',
                'countLabel' => 'delayed item(s)',
                'type'       => 'delayed',
            ],
        ];
    @endphp

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-report-analytics"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Chancellor</p>
            <h1 class="page-hdr-title">Procurement Reports</h1>
            <p class="page-hdr-sub">Review campus-wide procurement accomplishment, year-end utilization, and delayed items grouped by office.</p>
            <p class="report-meta">Generated {{ $generatedAt }}</p>
            {{-- Hidden on screen (the dropdown already shows this); shown only
                 when printed, since the dropdown itself is hidden there — a
                 printed filtered report needs to say so on the page itself. --}}
            @if($printFilterNote)
            <p class="print-only-filter-note">Filtered to {{ $printFilterNote }}</p>
            @endif
        </div>
        <div class="report-actions">
            <div class="office-filter-wrap">
                <i class="ti ti-building"></i>
                <select id="officeFilter" class="office-filter-select">
                    <option value="">All Offices</option>
                    @foreach ($offices as $office)
                        <option value="{{ $office->code }}" {{ $selectedOffice === $office->code ? 'selected' : '' }}>{{ $office->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="office-filter-wrap">
                <i class="ti ti-calendar-stats"></i>
                <select id="quarterFilter" class="office-filter-select" title="Filter by quarter" aria-label="Filter procurement reports by quarter">
                    <option value="">All Quarters</option>
                    @foreach ($quarterOptions as $quarter)
                        <option value="{{ $quarter }}" {{ $selectedQuarter === $quarter ? 'selected' : '' }}>{{ $quarter }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-print" type="button" onclick="window.print()">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print
            </button>
        </div>
    </div>

    <div class="stats-grid">
        @foreach ($kpiCards as $card)
            @php
                $detail = $kpiDetails[$card['key']] ?? [
                    'title' => $card['label'],
                    'lead' => '',
                    'rows' => collect(),
                    'empty' => 'No records found.',
                    'countLabel' => 'record(s)',
                ];
                $rows = collect($detail['rows'] ?? []);
            @endphp
            <div class="stat-wrap" tabindex="0" aria-describedby="report-kpi-{{ $card['key'] }}">
                <article class="stat-card">
                    <div class="stat-icon">{!! $card['icon'] !!}</div>
                    <p class="stat-label">{{ $card['label'] }}</p>
                    <strong class="stat-value {{ $card['valueClass'] ?? '' }}">{!! $card['value'] !!}</strong>
                    <p class="stat-desc">{{ $card['hint'] }}</p>
                </article>

                <section class="stat-kpi-popover" id="report-kpi-{{ $card['key'] }}" aria-labelledby="report-kpi-title-{{ $card['key'] }}">
                    <div class="stat-kpi-head">
                        <div>
                            <p class="stat-kpi-eyebrow">{{ $scopeLabel }}</p>
                            <h2 class="stat-kpi-title" id="report-kpi-title-{{ $card['key'] }}">{{ $detail['title'] }}</h2>
                            <p class="stat-kpi-lead">{{ $detail['lead'] }}</p>
                        </div>
                    </div>
                    <div class="stat-kpi-count">
                        <span>{{ number_format($rows->count()) }}</span>
                        <span>{{ $detail['countLabel'] ?? 'record(s)' }}</span>
                    </div>
                    <div class="stat-kpi-scroll">
                        <div class="stat-kpi-list">
                            @forelse ($rows as $row)
                                <div class="stat-kpi-row">
                                    <div class="stat-kpi-row-main">
                                        <div class="stat-kpi-row-title">{{ $row['title'] }}</div>
                                        <div class="stat-kpi-row-meta">
                                            {{ implode(' · ', $row['meta'] ?? []) }}
                                        </div>
                                        @if(!empty($row['note']))
                                            <div class="stat-kpi-row-note">{{ $row['note'] }}</div>
                                        @endif
                                    </div>
                                    <div class="stat-kpi-row-side">
                                        <strong>{{ $row['side'] }}</strong>
                                        <span class="pd-badge {{ $row['badgeClass'] }}">{{ $row['badge'] }}</span>
                                    </div>
                                    @if(!empty($row['url']))
                                        <a class="stat-kpi-open-link" href="{{ $row['url'] }}">
                                            View office report
                                            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <div class="stat-kpi-empty">{{ $detail['empty'] }}</div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        @endforeach
    </div>

    {{-- Campus-wide totals only, not per office — the two tables further
         down already give the full per-office breakdown; a per-office chart
         here would just repeat the same numbers as bars instead of rows. --}}
    <div class="charts-grid">
        <div class="card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-chart-donut"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">Campus-wide</p>
                    <h2 class="card-title">Accomplishment Rate</h2>
                </div>
            </div>
            <div class="chart-wrap" style="height:180px;">
                <canvas id="accomplishmentChart" data-rows="{{ json_encode($accomplishmentChart) }}"></canvas>
            </div>
            <div class="pd-chart-legend" id="accomplishmentLegend"></div>
        </div>
        <div class="card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-report-money"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">Campus-wide</p>
                    <h2 class="card-title">Budget Utilization Rate</h2>
                </div>
            </div>
            <div class="chart-wrap" style="height:180px;">
                <canvas id="utilizationChart" data-rows="{{ json_encode($utilizationChart) }}"></canvas>
            </div>
            <div class="pd-chart-legend" id="utilizationLegend"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div class="chart-icon-badge"><i class="ti ti-target-arrow"></i></div>
                <div>
                    <p class="card-eyebrow">Campus-wide accomplishment</p>
                    <h2 class="card-title">Items Targeted vs Procured per Office</h2>
                </div>
            </div>
            <span class="completion-chip">{{ $campusCompletion }}% campus completion</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Office</th><th>Items Targeted</th><th>Items Procured</th><th>Completion Rate</th></tr>
                </thead>
                <tbody>
                    @foreach ($accomplishmentRows as $row)
                        @php
                            $completionClass = (int) $row['completionRate'] >= 100
                                ? 'complete'
                                : ((int) $row['completionRate'] > 0 ? 'partial' : 'none');
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['targeted'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                            <td>
                                <div class="prog-wrap">
                                    <p class="prog-label">{{ $row['completionRate'] }}%</p>
                                    <div class="prog-track"><div class="prog-fill {{ $completionClass }}" style="width:{{ $row['completionRate'] }}%"></div></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="completion-legend" aria-label="Completion rate color legend">
            <span class="completion-legend-item"><span class="completion-legend-dot complete"></span>100% Complete</span>
            <span class="completion-legend-item"><span class="completion-legend-dot partial"></span>1-99% In progress</span>
            <span class="completion-legend-item"><span class="completion-legend-dot none"></span>0% Not started</span>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div class="chart-icon-badge"><i class="ti ti-calendar-stats"></i></div>
                <div>
                    <p class="card-eyebrow">Q1 to Q4</p>
                    <h2 class="card-title">Quarterly Accomplishment</h2>
                </div>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Office</th><th>Quarter</th><th>Targeted</th><th>Procured</th><th>Completion Rate</th></tr>
                </thead>
                <tbody>
                    @php
                        $quarterlyStatusGroups = collect($quarterlyRows)
                            ->map(function ($row) {
                                $rate = (int) $row['completionRate'];
                                $row['completionClass'] = $rate >= 100 ? 'complete' : ($rate > 0 ? 'partial' : 'none');
                                $row['completionStatus'] = $rate >= 100 ? 'Complete' : ($rate > 0 ? 'In progress' : 'Not started');
                                $row['completionSort'] = $rate >= 100 ? 0 : ($rate > 0 ? 1 : 2);

                                return $row;
                            })
                            ->sortBy(fn ($row) => sprintf('%02d|%s|%s', $row['completionSort'], $row['office'], $row['quarter']))
                            ->groupBy('completionStatus');
                    @endphp
                    @foreach (['Complete' => 'complete', 'In progress' => 'partial', 'Not started' => 'none'] as $statusLabel => $legendClass)
                        @php $statusRows = $quarterlyStatusGroups->get($statusLabel, collect()); @endphp
                        @if($statusRows->isNotEmpty())
                            @foreach ($statusRows->groupBy('office') as $office => $officeRows)
                                @foreach ($officeRows as $row)
                                    <tr>
                                        @if($loop->first)
                                            <td class="quarter-office-cell" rowspan="{{ $officeRows->count() }}">{{ $office }}</td>
                                        @endif
                                        <td style="color:var(--s500);">{{ $row['quarter'] }}</td>
                                        <td style="font-weight:600;color:var(--s700);">{{ $row['targeted'] }}</td>
                                        <td style="font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                                        <td>
                                            <div class="prog-wrap">
                                                <p class="prog-label">{{ $row['completionRate'] }}%</p>
                                                <div class="prog-track"><div class="prog-fill {{ $row['completionClass'] }}" style="width:{{ $row['completionRate'] }}%"></div></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="completion-legend" aria-label="Quarterly completion rate color legend">
            <span class="completion-legend-item"><span class="completion-legend-dot complete"></span>100% Complete</span>
            <span class="completion-legend-item"><span class="completion-legend-dot partial"></span>1-99% In progress</span>
            <span class="completion-legend-item"><span class="completion-legend-dot none"></span>0% Not started</span>
        </div>
    </div>

    <div class="two-col">

        <div class="card">
            <div class="card-head">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="chart-icon-badge"><i class="ti ti-building"></i></div>
                    <div>
                        <p class="card-eyebrow">Year-end budget utilization</p>
                        <h2 class="card-title">Summary by Office</h2>
                    </div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Office</th><th>Budget</th><th>Utilized</th><th>Forecast</th><th>Risk</th></tr>
                    </thead>
                    <tbody>
                        @php
                            $sortedUtilizationSummary = collect($utilizationSummary)
                                ->sortBy(function ($row) {
                                    $riskOrder = ['on track' => 0, 'at risk' => 1, 'critical' => 2];

                                    return sprintf(
                                        '%02d|%03d|%s',
                                        $riskOrder[strtolower($row['risk'] ?? '')] ?? 9,
                                        100 - (int) ($row['forecast'] ?? 0),
                                        $row['office'] ?? ''
                                    );
                                })
                                ->values();
                        @endphp
                        @foreach ($sortedUtilizationSummary as $row)
                            @php
                                $riskClass = match(strtolower($row['risk'])) {
                                    'on track', 'low'  => 'badge-low',
                                    'at risk', 'medium' => 'badge-medium',
                                    'critical', 'high' => 'badge-high',
                                    default => 'badge-medium',
                                };
                            @endphp
                            <tr>
                                <td style="font-weight:600;color:var(--s600);white-space:nowrap;">{{ $row['office'] }}</td>
                                <td style="color:var(--s700);">PHP {{ number_format($row['budget']) }}</td>
                                <td style="font-weight:700;color:var(--m);">PHP {{ number_format($row['utilized']) }}</td>
                                <td style="font-weight:700;color:var(--s700);">{{ $row['forecast'] }}%</td>
                                <td><span class="badge {{ $riskClass }}">{{ $row['risk'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="chart-icon-badge"><i class="ti ti-alert-triangle"></i></div>
                    <div>
                        <p class="card-eyebrow">Delayed items grouped by office</p>
                        <h2 class="card-title">Delay Reasons</h2>
                    </div>
                </div>
            </div>
            <div class="delay-list">
                @foreach ($delayedByOffice as $office => $items)
                    <div class="delay-item">
                        <div>
                            <p class="delay-office">{{ $office }}</p>
                            @foreach ($items as $item)
                                <p class="delay-entry">{{ $item['item'] }} &mdash; {{ $item['prNumber'] }}: {{ $item['remarks'] }}</p>
                            @endforeach
                        </div>
                        <span class="badge badge-delayed" style="white-space:nowrap;flex-shrink:0;">{{ count($items) }} delayed</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@include('prism.shared.receiving-table', ['receivingReadOnly' => true])
@endsection

@push('scripts')
<script>
function applyReportFilters() {
    const url = new URL(window.location.href);
    const officeFilter = document.getElementById('officeFilter');
    const quarterFilter = document.getElementById('quarterFilter');

    if (officeFilter?.value) {
        url.searchParams.set('office', officeFilter.value);
    } else {
        url.searchParams.delete('office');
    }

    if (quarterFilter?.value) {
        url.searchParams.set('quarter', quarterFilter.value);
    } else {
        url.searchParams.delete('quarter');
    }

    window.location.href = url.toString();
}

document.getElementById('officeFilter')?.addEventListener('change', applyReportFilters);
document.getElementById('quarterFilter')?.addEventListener('change', applyReportFilters);

(function () {
    // Always-visible labels (not just on hover) — a compact legend below
    // each doughnut instead of Chart.js's own legend, which needs a hover
    // to read exact counts and takes more vertical space.
    function renderLegend(elId, entries) {
        const el = document.getElementById(elId);
        if (!el) return;
        el.innerHTML = entries.map(([label, value, color]) => `
            <span class="pd-chart-legend-item">
                <span class="pd-chart-legend-dot" style="background:${color}"></span>${label}: ${value}
            </span>
        `).join('');
    }

    const accEl = document.getElementById('accomplishmentChart');
    if (accEl) {
        const d = JSON.parse(accEl.dataset.rows || '{}');
        const procured = d.procured || 0, remaining = d.remaining || 0;
        const total = procured + remaining;
        const pct = total > 0 ? Math.round((procured / total) * 100) : 0;
        new Chart(accEl, {
            type: 'doughnut',
            data: {
                labels: ['Procured', 'Remaining'],
                datasets: [{ data: [procured, remaining], backgroundColor: ['#681012', '#e2e8f0'], borderWidth: 0 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { display: false } },
            },
            plugins: [{
                id: 'centerText',
                afterDraw(chart) {
                    const { ctx, chartArea: { left, top, width, height } } = chart;
                    ctx.save();
                    ctx.font = '700 20px Poppins, sans-serif';
                    ctx.fillStyle = '#681012';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(pct + '%', left + width / 2, top + height / 2);
                    ctx.restore();
                },
            }],
        });
        renderLegend('accomplishmentLegend', [
            ['Procured', procured, '#681012'],
            ['Remaining', remaining, '#e2e8f0'],
        ]);
    }

    const utilEl = document.getElementById('utilizationChart');
    if (utilEl) {
        const d = JSON.parse(utilEl.dataset.rows || '{}');
        const utilized = d.utilized || 0, unutilized = d.unutilized || 0;
        const total = utilized + unutilized;
        const pct = total > 0 ? Math.round((utilized / total) * 100) : 0;
        new Chart(utilEl, {
            type: 'doughnut',
            data: {
                labels: ['Utilized', 'Unutilized'],
                datasets: [{ data: [utilized, unutilized], backgroundColor: ['#c9a84c', '#e2e8f0'], borderWidth: 0 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { display: false } },
            },
            plugins: [{
                id: 'centerText',
                afterDraw(chart) {
                    const { ctx, chartArea: { left, top, width, height } } = chart;
                    ctx.save();
                    ctx.font = '700 20px Poppins, sans-serif';
                    ctx.fillStyle = '#7a5a10';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(pct + '%', left + width / 2, top + height / 2);
                    ctx.restore();
                },
            }],
        });
        renderLegend('utilizationLegend', [
            ['Utilized', '₱' + utilized.toLocaleString(), '#c9a84c'],
            ['Unutilized', '₱' + unutilized.toLocaleString(), '#e2e8f0'],
        ]);
    }
})();
</script>
@endpush
