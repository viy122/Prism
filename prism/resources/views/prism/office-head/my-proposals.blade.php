@extends('prism.layouts.office-head')
@section('title', 'My PPMPs')

@php
    $proposalCollection   = collect($proposals);
    $proposalCount        = $proposalCollection->count();
    $approvedCount        = $proposalCollection->where('status', 'Approved')->count();
    $returnedCount        = $proposalCollection->where('status', 'Returned')->count();
    $approvedAmount       = $proposalCollection->where('status', 'Approved')->sum('totalAmount');
    $proposalStatusClass = function ($status) {
        $status = strtolower((string) $status);

        return match (true) {
            str_contains($status, 'approved') || str_contains($status, 'endorsed') => 'pd-badge-approved',
            str_contains($status, 'returned') => 'pd-badge-returned',
            str_contains($status, 'submitted') => 'pd-badge-submitted',
            str_contains($status, 'review') => 'pd-badge-progress',
            str_contains($status, 'draft') => 'pd-badge-info',
            default => 'pd-badge-pending',
        };
    };
    $proposalKpiRows = $proposalCollection
        ->map(function ($proposal) use ($proposalStatusClass) {
            $latestEvent = collect($proposal['timeline'] ?? [])->first();

            return [
                'id'          => $proposal['id'],
                'title'       => $proposal['title'],
                'meta'        => 'FY ' . $proposal['fiscalYear'] . ' · Submitted ' . $proposal['dateSubmitted'],
                'side'        => 'PHP ' . number_format($proposal['totalAmount']),
                'status'      => $proposal['status'],
                'statusClass' => $proposalStatusClass($proposal['status']),
                'note'        => ($latestEvent['step'] ?? 'Pending') . ' · ' . ($latestEvent['timestamp'] ?? 'No timeline yet'),
            ];
        })
        ->values();
    $kpiCards = [
        [
            'key' => 'proposals',
            'label' => 'Proposals',
            'value' => number_format($proposalCount),
            'hint' => 'All PPMPs submitted or saved by your office',
            'icon' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
        ],
        [
            'key' => 'approved',
            'label' => 'Approved',
            'value' => number_format($approvedCount),
            'hint' => 'PPMPs cleared for procurement preparation',
            'icon' => '<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        ],
        [
            'key' => 'returned',
            'label' => 'Returned',
            'value' => number_format($returnedCount),
            'hint' => 'PPMPs needing revision or resubmission',
            'icon' => '<svg viewBox="0 0 24 24"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 00-4-4H4"/></svg>',
        ],
        [
            'key' => 'approvedAmount',
            'label' => 'Approved Amount',
            'value' => 'PHP ' . number_format($approvedAmount),
            'valueClass' => 'sm',
            'hint' => 'Total budget value from approved PPMPs',
            'icon' => '<svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
        ],
    ];
    $kpiDetails = [
        'proposals' => [
            'title' => 'All Proposals',
            'lead' => 'Every PPMP currently listed in your proposal queue.',
            'rows' => $proposalKpiRows,
            'empty' => 'No proposals found yet.',
            'countLabel' => 'proposal(s)',
        ],
        'approved' => [
            'title' => 'Approved PPMPs',
            'lead' => 'PPMPs that have already passed approval.',
            'rows' => $proposalKpiRows->where('status', 'Approved')->values(),
            'empty' => 'No approved PPMPs yet.',
            'countLabel' => 'approved proposal(s)',
        ],
        'returned' => [
            'title' => 'Returned PPMPs',
            'lead' => 'PPMPs sent back for correction or revision.',
            'rows' => $proposalKpiRows->where('status', 'Returned')->values(),
            'empty' => 'No returned PPMPs.',
            'countLabel' => 'returned proposal(s)',
        ],
        'approvedAmount' => [
            'title' => 'Approved Amount Sources',
            'lead' => 'Approved PPMPs contributing to the approved amount total.',
            'rows' => $proposalKpiRows->where('status', 'Approved')->sortByDesc(fn ($row) => (float) str_replace([',', 'PHP '], '', $row['side']))->values(),
            'empty' => 'No approved amount sources yet.',
            'countLabel' => 'approved budget source(s)',
        ],
    ];
@endphp

@push('page-css')
<style>
        :root {
            --m:     #681012;
            --m-dk:  #4e0c0e;
            --white: #ffffff;
            --s50:   #f8fafc;
            --s100:  #f1f5f9;
            --s200:  #e2e8f0;
            --s300:  #cbd5e1;
            --s400:  #94a3b8;
            --s500:  #64748b;
            --s600:  #475569;
            --s700:  #334155;
            --s900:  #0f172a;
            --sh:    0 2px 8px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.04);
            --sh-md: 0 4px 20px rgba(15,23,42,.09), 0 1px 4px rgba(15,23,42,.04);
            --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
            --sh-lg: 0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05);
        }

        .content {
            padding: 32px 32px 64px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ─── Card ─── */
        .card {
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 18px;
            padding: 26px;
            box-shadow: var(--sh);
        }
        .card-eyebrow {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--m);
            margin-bottom: 4px;
        }
        .card-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--s900);
            letter-spacing: -.2px;
        }
        .card-sub {
            font-size: 13px;
            color: var(--s500);
            margin-top: 4px;
        }
        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        /* ─── Stats bar ─── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 13px;
            margin-bottom: 24px;
        }
        .stat-wrap {
            position: relative;
            min-width: 0;
            outline: none;
            z-index: 1;
        }
        .stat-wrap:hover,
        .stat-wrap:focus-within {
            z-index: 80;
        }
        .stat-box {
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 15px;
            padding: 16px 18px;
            position: relative;
            overflow: visible;
            box-shadow: var(--sh-sm);
            transition: box-shadow .25s, border-color .25s, transform .2s;
            min-height: 112px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .stat-box:hover,
        .stat-wrap:hover .stat-box,
        .stat-wrap:focus-within .stat-box {
            border-color: rgba(192,57,59,.45);
            box-shadow:
                0 0 0 1px rgba(192,57,59,.20),
                0 10px 28px rgba(139,26,28,.16),
                0 2px 8px rgba(15,23,42,.08);
            transform: translateY(-2px);
        }
        .stat-box::before {
            content: "";
            position: absolute;
            left: 0;
            top: 24px;
            width: 4px;
            height: 35px;
            background: var(--m);
            border-radius: 0 4px 4px 0;
        }
        .stat-icon {
            position: absolute;
            right: 16px;
            top: 14px;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(104,16,18,.07);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon svg {
            width: 18px;
            height: 18px;
            stroke: var(--m);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .stat-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 8px;
            padding-right: 46px;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--m);
            letter-spacing: -.7px;
            line-height: 1;
            margin-bottom: 7px;
        }
        .stat-value.maroon { color: var(--m); }
        .stat-value.sm { font-size: clamp(20px, 1.45vw, 24px); letter-spacing: -.3px; white-space: nowrap; }
        .stat-hint {
            font-size: 11.5px;
            color: var(--s400);
            line-height: 1.5;
        }

        .kpi-popover {
            position: absolute;
            z-index: 90;
            left: 6px;
            top: calc(100% + 9px);
            width: min(520px, calc(100vw - 48px));
            max-width: 520px;
            background: #fff;
            border: 1px solid rgba(104,16,18,.18);
            border-radius: 10px;
            box-shadow:
                0 0 0 1px rgba(192,57,59,.12),
                0 20px 52px rgba(15,23,42,.18),
                0 10px 28px rgba(139,26,28,.12);
            padding: 18px;
            color: var(--s700);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-4px);
            visibility: hidden;
            transition: opacity .16s ease, transform .16s ease, visibility .16s;
        }
        .kpi-popover::before {
            content: "";
            position: absolute;
            top: -9px;
            left: 190px;
            width: 18px;
            height: 18px;
            background: #fff;
            border-left: 1px solid rgba(104,16,18,.18);
            border-top: 1px solid rgba(104,16,18,.18);
            transform: rotate(45deg);
        }
        .stat-wrap:nth-child(3) .kpi-popover,
        .stat-wrap:nth-child(4) .kpi-popover { left: auto; right: 0; }
        .stat-wrap:nth-child(3) .kpi-popover::before,
        .stat-wrap:nth-child(4) .kpi-popover::before { left: auto; right: 190px; }
        .stat-wrap:hover .kpi-popover,
        .stat-wrap:focus-within .kpi-popover {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
            visibility: visible;
        }
        .kpi-head { margin-bottom: 12px; }
        .kpi-eyebrow { font-size: 9px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
        .kpi-title { font-size: 18px; font-weight: 800; color: var(--s900); letter-spacing: -.35px; margin: 0 0 5px; line-height: 1.15; }
        .kpi-lead { font-size: 12.5px; color: var(--s600); line-height: 1.5; margin: 0; }
        .kpi-count { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 11px; font-weight: 800; color: var(--m); }
        .kpi-scroll { max-height: 310px; overflow: auto; padding-right: 3px; }
        .kpi-list { display: flex; flex-direction: column; gap: 8px; }
        .kpi-row {
            display: grid;
            grid-template-columns: minmax(0,1fr) auto;
            gap: 10px;
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 8px;
            padding: 11px 12px;
        }
        .kpi-row-main { min-width: 0; }
        .kpi-row-title { color: var(--s900); font-size: 12.5px; font-weight: 800; line-height: 1.35; margin-bottom: 3px; overflow-wrap: anywhere; }
        .kpi-row-meta { color: var(--s500); font-size: 11px; line-height: 1.55; }
        .kpi-row-note { color: var(--s600); font-size: 11px; line-height: 1.5; margin-top: 3px; overflow-wrap: anywhere; }
        .kpi-row-side { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; text-align: right; }
        .kpi-row-side strong { color: var(--m); font-size: 12px; white-space: nowrap; }
        .kpi-open-link {
            grid-column: 1 / -1;
            width: max-content;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--m);
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
        }
        .kpi-open-link:hover { text-decoration: underline; }
        .kpi-open-link svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
        .kpi-empty { padding: 18px 10px; text-align: center; color: var(--s400); font-size: 12.5px; font-weight: 700; }
        .pd-badge { display: inline-flex; align-items: center; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; white-space: nowrap; line-height: 1.4; flex-shrink: 0; }
        .pd-badge-approved  { background: #dcfce7; color: #166534; }
        .pd-badge-pending   { background: #fef3c7; color: #92400e; }
        .pd-badge-returned  { background: #fee2e2; color: #991b1b; }
        .pd-badge-submitted { background: #dbeafe; color: #1e40af; }
        .pd-badge-info      { background: #e0f2fe; color: #0369a1; }
        .pd-badge-progress  { background: #ede9fe; color: #4c1d95; }

        /* ─── Filters row ─── */
        .filters-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 16px;
            align-items: end;
        }
        .field-group { display: flex; flex-direction: column; gap: 7px; }
        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--s700);
        }
        .field-select {
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--s300);
            background: var(--white);
            padding: 0 14px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--s900);
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-select:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.08);
        }

        /* ─── Buttons ─── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 20px;
            border-radius: 10px;
            background: var(--m);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(104,16,18,.2);
            transition: background .2s;
            white-space: nowrap;
        }
        .btn-primary:hover { background: var(--m-dk); }
        .btn-primary svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Pill ─── */
        .pill {
            display: inline-flex;
            align-items: center;
            height: 28px;
            padding: 0 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .pill-gray   { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
        .pill-maroon { background: rgba(104,16,18,.08); color: var(--m); border: 1px solid rgba(104,16,18,.15); }

        /* ─── 2-col layout ─── */
        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 440px;
            gap: 24px;
            align-items: start;
        }
        .col-sticky {
            position: sticky;
            top: 86px;
            align-self: start;
            max-height: calc(100vh - 106px);
            display: flex;
            flex-direction: column;
        }
        .col-sticky #timelineContent {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
        }

        /* ─── Proposal queue ─── */
        .queue-scroll {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 68vh;
            overflow-y: auto;
            padding-right: 2px;
        }
        .proposal-row {
            border: 1px solid var(--s200);
            border-radius: 14px;
            background: var(--white);
            padding: 18px 20px;
            cursor: pointer;
            transition: border-color .15s, background .15s, box-shadow .15s;
            outline: none;
        }
        .proposal-row:hover {
            border-color: rgba(104,16,18,.28);
            background: rgba(104,16,18,.03);
            box-shadow: var(--sh);
        }
        .proposal-row:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.1);
        }
        .proposal-row-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .proposal-row-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--s900);
            line-height: 1.4;
        }
        .proposal-row-meta {
            font-size: 12px;
            font-weight: 600;
            color: var(--s500);
            margin-top: 3px;
        }
        .proposal-row-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .proposal-stat-item dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 4px;
        }
        .proposal-stat-item dd {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
        }
        .proposal-stat-item dd.danger { color: #991b1b; }

        /* ─── Proposal queue empty state ─── */
        .proposal-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 200px;
            border: 1.5px dashed var(--s300);
            border-radius: 14px;
            background: var(--s50);
            padding: 32px;
            text-align: center;
            color: var(--s500);
            font-size: 13px;
            line-height: 1.65;
        }
        .proposal-empty svg {
            width: 36px; height: 36px;
            stroke: rgba(104,16,18,.4); fill: none;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }
        .proposal-empty strong { color: var(--s700); }

        /* ─── Timeline panel ─── */
        .timeline-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .timeline-meta-box {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .timeline-meta-box dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 5px;
        }
        .timeline-meta-box dd {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
        }

        /* ─── Timeline empty state ─── */
        .timeline-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            min-height: 220px;
            border: 1.5px dashed var(--s300);
            border-radius: 16px;
            background: var(--s50);
            padding: 36px 28px;
            text-align: center;
        }
        .timeline-empty-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(104,16,18,.08);
            border: 1px solid rgba(104,16,18,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .timeline-empty-icon i {
            font-size: 24px;
            color: var(--m);
        }
        .timeline-empty-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--s700);
        }
        .timeline-empty-sub {
            font-size: 12.5px;
            color: var(--s500);
            line-height: 1.6;
            max-width: 240px;
        }

        /* ─── Responsive ─── */
        @media (max-width: 1280px) {
            .two-col { grid-template-columns: minmax(0,1fr) 380px; }
            .stats-bar { grid-template-columns: repeat(2,1fr); }
            .stat-wrap .kpi-popover { left: 6px; right: auto; }
            .stat-wrap .kpi-popover::before { left: 160px; right: auto; }
            .stat-wrap:nth-child(even) .kpi-popover { left: auto; right: 0; }
            .stat-wrap:nth-child(even) .kpi-popover::before { left: auto; right: 160px; }
        }
        @media (max-width: 1024px) {
            .content { padding: 20px 20px 48px; gap: 20px; }
            .two-col { grid-template-columns: 1fr; }
            .col-sticky { position: static; }
            .filters-row { grid-template-columns: 1fr 1fr; }
            .filters-row .btn-primary { grid-column: span 2; }
        }
        @media (max-width: 640px) {
            .stats-bar { grid-template-columns: 1fr; }
            .filters-row { grid-template-columns: 1fr; }
            .filters-row .btn-primary { grid-column: span 1; }
            .stat-wrap:nth-child(n) .kpi-popover {
                position: fixed;
                left: 16px;
                right: 16px;
                top: 96px;
                width: auto;
                max-width: none;
                max-height: calc(100vh - 128px);
                overflow: auto;
            }
            .stat-wrap:nth-child(n) .kpi-popover::before { display: none; }
            .kpi-row { grid-template-columns: 1fr; }
            .kpi-row-side { align-items: flex-start; text-align: left; }
        }

        /* ── Success toast ── */
        .mp-toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); z-index: 300; display: flex; align-items: center; gap: 10px; background: #166534; color: #fff; font-size: 13px; font-weight: 700; border-radius: 99px; padding: 12px 24px; box-shadow: 0 4px 20px rgba(0,0,0,.2); white-space: nowrap; opacity: 0; transition: opacity .3s; pointer-events: none; }
        .mp-toast.show { opacity: 1; pointer-events: auto; }
        .mp-toast i { font-size: 17px; }
</style>
@endpush

@section('content')
    <div class="content">

        {{-- Stats + Filters card --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Overview</p>
                    <h2 class="card-title">PPMP Summary</h2>
                </div>
                <a class="btn-primary" href="{{ route('office-head.budget-proposal') }}">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    New PPMP
                </a>
            </div>

            <div class="stats-bar">
                @foreach ($kpiCards as $card)
                    @php
                        $detail = $kpiDetails[$card['key']] ?? [
                            'title' => $card['label'],
                            'lead' => '',
                            'rows' => collect(),
                            'empty' => 'No records yet.',
                            'countLabel' => 'record(s)',
                        ];
                        $rows = collect($detail['rows'] ?? []);
                        $valueId = match($card['key']) {
                            'proposals' => 'kpiProposalCount',
                            'approved' => 'kpiApprovedCount',
                            'returned' => 'kpiReturnedCount',
                            'approvedAmount' => 'kpiApprovedAmount',
                            default => null,
                        };
                    @endphp
                    <div class="stat-wrap" tabindex="0" aria-describedby="proposal-kpi-{{ $card['key'] }}">
                        <article class="stat-box">
                            <div class="stat-icon">{!! $card['icon'] !!}</div>
                            <div class="stat-label">{{ $card['label'] }}</div>
                            <div class="stat-value {{ $card['valueClass'] ?? '' }}" @if($valueId) id="{{ $valueId }}" @endif>{!! $card['value'] !!}</div>
                            <p class="stat-hint">{{ $card['hint'] }}</p>
                        </article>

                        <section class="kpi-popover" id="proposal-kpi-{{ $card['key'] }}" aria-labelledby="proposal-kpi-title-{{ $card['key'] }}">
                            <div class="kpi-head">
                                <p class="kpi-eyebrow">My PPMPs</p>
                                <h2 class="kpi-title" id="proposal-kpi-title-{{ $card['key'] }}">{{ $detail['title'] }}</h2>
                                <p class="kpi-lead">{{ $detail['lead'] }}</p>
                            </div>
                            <div class="kpi-count">
                                <span>{{ number_format($rows->count()) }}</span>
                                <span>{{ $detail['countLabel'] ?? 'record(s)' }}</span>
                            </div>
                            <div class="kpi-scroll">
                                <div class="kpi-list">
                                    @forelse ($rows as $row)
                                        <div class="kpi-row">
                                            <div class="kpi-row-main">
                                                <div class="kpi-row-title">{{ $row['title'] }}</div>
                                                <div class="kpi-row-meta">{{ $row['meta'] }}</div>
                                                <div class="kpi-row-note">{{ $row['note'] }}</div>
                                            </div>
                                            <div class="kpi-row-side">
                                                <strong>{{ $row['side'] }}</strong>
                                                <span class="pd-badge {{ $row['statusClass'] }}">{{ $row['status'] }}</span>
                                            </div>
                                            <a class="kpi-open-link" href="#proposal-row-{{ $row['id'] }}">
                                                View in queue
                                                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="kpi-empty">{{ $detail['empty'] }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </section>
                    </div>
                @endforeach
            </div>

            <div class="filters-row" aria-label="Proposal filters">
                <div class="field-group">
                    <label class="field-label" for="proposalStatusFilter">Status</label>
                    <select id="proposalStatusFilter" class="field-select">
                        <option value="all">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label class="field-label" for="proposalYearFilter">Fiscal Year</label>
                    <select id="proposalYearFilter" class="field-select">
                        <option value="all">All fiscal years</option>
                        @foreach ($fiscalYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- 2-col: queue + timeline --}}
        <div class="two-col">

            {{-- Proposal queue --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Proposal queue</p>
                        <h2 class="card-title">All Proposals</h2>
                        <p class="card-sub">Select a proposal to review its approval movement.</p>
                    </div>
                    <span class="pill pill-gray" id="proposalVisibleCount">{{ $proposalCount }} shown</span>
                </div>

                <div class="queue-scroll" id="proposalRows">
                    @foreach ($proposals as $proposal)
                        @php
                            $latestEvent = collect($proposal['timeline'])->last();
                            $isReturned  = $proposal['status'] === 'Returned';
                        @endphp
                        <article
                            id="proposal-row-{{ $proposal['id'] }}"
                            class="proposal-row"
                            data-proposal-row
                            data-proposal-id="{{ $proposal['id'] }}"
                            data-status="{{ $proposal['status'] }}"
                            data-year="{{ $proposal['fiscalYear'] }}"
                            tabindex="0"
                            aria-label="{{ $proposal['title'] }}"
                        >
                            <div class="proposal-row-head">
                                <div class="min-w-0">
                                    <p class="proposal-row-title">{{ $proposal['title'] }}</p>
                                    <p class="proposal-row-meta">FY {{ $proposal['fiscalYear'] }} &middot; Submitted {{ $proposal['dateSubmitted'] }}</p>
                                </div>
                                <x-prism.status-badge :status="$proposal['status']" />
                            </div>

                            <dl class="proposal-row-stats">
                                <div class="proposal-stat-item">
                                    <dt>Amount</dt>
                                    <dd>PHP {{ number_format($proposal['totalAmount']) }}</dd>
                                </div>
                                <div class="proposal-stat-item">
                                    <dt>Current Step</dt>
                                    <dd>{{ $latestEvent['step'] ?? 'Pending' }}</dd>
                                </div>
                                <div class="proposal-stat-item">
                                    <dt>Action</dt>
                                    <dd class="{{ $isReturned ? 'danger' : '' }}">{{ $isReturned ? 'Revise' : 'Track' }}</dd>
                                </div>
                            </dl>
                        </article>
                    @endforeach
                </div>

                <div class="proposal-empty" id="proposalEmptyState" style="display:none;">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <p><strong>No matching PPMPs found.</strong><br>Try a different status or fiscal year filter.</p>
                </div>
            </div>

            {{-- Timeline panel --}}
            <div class="card col-sticky" id="proposalTimelinePanel" aria-live="polite">
                <div class="card-head">
                    <div class="min-w-0" style="flex:1;">
                        <p class="card-eyebrow">Approval movement</p>
                        <h2 class="card-title" id="timelineTitle" style="white-space:normal;overflow-wrap:break-word;">Select a proposal</h2>
                        <p class="card-sub" id="timelineMeta">Timeline details will appear here.</p>
                    </div>
                    <span class="pill pill-gray" id="timelineStatusBadge">Status</span>
                </div>

                <div class="timeline-meta-grid">
                    <div class="timeline-meta-box">
                        <dt>Total Amount</dt>
                        <dd id="timelineAmount">PHP 0</dd>
                    </div>
                    <div class="timeline-meta-box">
                        <dt>Next Action</dt>
                        <dd id="timelineAction">Select</dd>
                    </div>
                </div>

                <div id="timelineContent" class="timeline-empty">
                    <div class="timeline-empty-icon"><i class="ti ti-click"></i></div>
                    <p class="timeline-empty-title">No proposal selected</p>
                    <p class="timeline-empty-sub">Select a proposal from the queue to view its timestamps, remarks, and revision status.</p>
                </div>
            </div>

        </div>{{-- /two-col --}}

    </div>{{-- /content --}}

    <div id="mpToast" class="mp-toast" role="status" aria-live="polite">
        <i class="ti ti-circle-check"></i>
        <span id="mpToastMsg"></span>
    </div>
@endsection

@push('scripts')
<script type="application/json" id="proposalData">@json($proposals)</script>
<script>
(function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('submitted') === '1') {
        const toast = document.getElementById('mpToast');
        document.getElementById('mpToastMsg').textContent = 'Your proposal has been submitted successfully.';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
        history.replaceState({}, '', window.location.pathname);
    }
})();
</script>
@endpush
