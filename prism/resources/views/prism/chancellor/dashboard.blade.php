@extends('prism.layouts.app')
@section('title', 'Campus Monitoring | Chancellor')

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

    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 13px; }
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
    .stat-card::before { content: ''; position: absolute; left: 0; top: 24px; width: 4px; height: 35px; border-radius: 0 4px 4px 0; background: var(--crimson); }
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
    .stat-wrap:nth-child(n+4) .stat-kpi-popover { left: auto; right: 0; }
    .stat-wrap:nth-child(n+4) .stat-kpi-popover::before { left: auto; right: 190px; }
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
    .stat-kpi-row-meta .badge { height: 20px; padding: 0 8px; font-size: 10px; margin-right: 6px; vertical-align: middle; }
    .stat-kpi-row-remarks { color: var(--s600); font-size: 11px; line-height: 1.5; margin-top: 3px; overflow-wrap: anywhere; }
    .stat-kpi-row-side { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; text-align: right; }
    .stat-kpi-row-side strong { color: var(--crimson); font-size: 12px; white-space: nowrap; }
    .stat-kpi-open-link {
        grid-column: 1 / -1; width: max-content;
        display: inline-flex; align-items: center; gap: 5px;
        color: var(--crimson); text-decoration: none; font-size: 11px; font-weight: 800;
    }
    .stat-kpi-open-link:hover { text-decoration: underline; }
    .stat-kpi-empty { padding: 18px 10px; text-align: center; color: var(--s400); font-size: 12.5px; font-weight: 700; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 52vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--crimson-mid); }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-completed   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-in-progress { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-pending     { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-overdue     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-critical    { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-low-risk    { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-medium-risk { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-high-risk   { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .prog-wrap { min-width: 140px; }
    .prog-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 6px; }
    .prog-track { height: 10px; border-radius: 99px; background: var(--s100); overflow: hidden; border: 1px solid var(--s200); }
    .prog-fill-maroon { height: 100%; border-radius: 99px; background: var(--m); }
    .prog-fill-gold   { height: 100%; border-radius: 99px; background: var(--gold); }
    .quarter-status-cell { position: relative; outline: none; }
    .quarter-status-cell:hover,
    .quarter-status-cell:focus-within { z-index: 80; }
    .quarter-status-surface { border-radius: 10px; padding: 4px 0; outline: none; }
    .quarter-status-surface:focus-visible { box-shadow: 0 0 0 3px rgba(104,16,18,.10); }
    .quarter-detail-popover {
        position: absolute; z-index: 120; left: 14px; top: calc(100% - 4px);
        width: min(440px, calc(100vw - 72px)); max-width: 440px;
        background: #fff; border: 1px solid var(--crimson-border); border-radius: 10px;
        box-shadow: 0 18px 42px rgba(15,23,42,.16), 0 8px 20px rgba(139,26,28,.10);
        padding: 14px; color: var(--s700);
        opacity: 0; pointer-events: none; transform: translateY(-4px);
        visibility: hidden; transition: opacity .15s ease, transform .15s ease, visibility .15s;
    }
    .quarter-detail-popover.right { left: auto; right: 14px; }
    .quarter-status-cell:hover .quarter-detail-popover,
    .quarter-status-cell:focus-within .quarter-detail-popover {
        opacity: 1; pointer-events: auto; transform: translateY(0); visibility: visible;
    }
    .quarter-detail-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    .quarter-detail-title { font-size: 13px; font-weight: 800; color: var(--s900); line-height: 1.25; }
    .quarter-detail-meta { font-size: 10.5px; font-weight: 800; color: var(--crimson); white-space: nowrap; }
    .quarter-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .quarter-detail-section { min-width: 0; }
    .quarter-detail-section-title { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); }
    .quarter-detail-dot { width: 8px; height: 8px; border-radius: 999px; flex-shrink: 0; }
    .quarter-detail-dot.done { background: #3b6d11; }
    .quarter-detail-dot.todo { background: #a32d2d; }
    .quarter-detail-list { display: flex; flex-direction: column; gap: 6px; max-height: 220px; overflow: auto; padding-right: 2px; }
    .quarter-detail-item { border: 1px solid var(--s200); border-radius: 8px; padding: 8px 9px; background: var(--s50); }
    .quarter-detail-item strong { display: block; font-size: 11.5px; color: var(--s900); line-height: 1.35; overflow-wrap: anywhere; }
    .quarter-detail-item span { display: block; margin-top: 3px; font-size: 10.5px; color: var(--s500); line-height: 1.45; overflow-wrap: anywhere; }
    .quarter-detail-empty { border: 1px dashed var(--s300); border-radius: 8px; padding: 10px; color: var(--s400); font-size: 11px; font-weight: 700; text-align: center; }

    .alert-list-scroll { max-height: 520px; overflow-y: auto; padding-right: 4px; }
    .alert-card-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
    .alert-count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; white-space: nowrap; }
    .alert-filter-wrap { position: relative; display: inline-flex; align-items: center; flex-shrink: 0; }
    .alert-filter-wrap i.ficon { position: absolute; left: 14px; font-size: 14px; color: var(--m); pointer-events: none; }
    .alert-filter-wrap i.fchev { position: absolute; right: 13px; font-size: 12px; color: var(--s400); pointer-events: none; }
    .alert-category-select { height: 36px; min-width: 220px; max-width: 280px; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 32px 0 38px; font-size: 12px; font-weight: 700; color: var(--s700); font-family: 'Poppins', sans-serif; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; -moz-appearance: none; transition: border-color .15s, box-shadow .15s; }
    .alert-category-select:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .alert-category-group { display: flex; flex-direction: column; gap: 10px; }
    .alert-category-group[hidden] { display: none; }
    .alert-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 16px; border-radius: 14px; border: 1px solid #f7c1c1; background: rgba(252,235,235,.6); transition: background .15s, box-shadow .15s; }
    .alert-item:hover { background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.06); }
    .alert-item strong { font-size: 13px; font-weight: 700; color: var(--s900); display: block; }
    .alert-item span   { font-size: 12px; color: var(--s600); display: block; margin-top: 3px; line-height: 1.6; }

    .rank-num { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; font-size: 12px; font-weight: 800; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
    .rank-num.top { background: rgba(201,168,76,.15); color: #7a5a10; border-color: rgba(201,168,76,.4); }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    .charts-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 16px; }
    .chart-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .chart-icon-badge {
        width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
        background: rgba(104,16,18,.07);
        display: flex; align-items: center; justify-content: center;
    }
    .chart-icon-badge i { font-size: 18px; color: var(--m); }
    .chart-card-head-text { flex: 1; min-width: 0; }
    .chart-card-head-text .card-eyebrow { margin-bottom: 2px; }
    .chart-wrap  { position: relative; width: 100%; height: 230px; }
    /* Office-utilization chart grows with office count instead of squeezing
       many bars into a fixed box — left uncapped, that stretched the WHOLE
       grid row (grid items stretch to the tallest sibling by default),
       leaving the item-status doughnut with a lot of dead blank space below
       its own much-shorter content. Capped + scrollable now, with an
       explicit expand toggle for when every office is actually needed. */
    .chart-wrap.collapsible { max-height: 230px; overflow-y: auto; transition: max-height .25s ease; }
    .chart-wrap.collapsible.expanded { max-height: 2000px; }
    .chart-expand-btn {
        display: inline-flex; align-items: center; gap: 4px;
        background: none; border: none; cursor: pointer;
        font-size: 11px; font-weight: 700; color: var(--m);
        font-family: 'Poppins', sans-serif; padding: 0; margin-left: auto;
    }
    .chart-expand-btn:hover { text-decoration: underline; }
    .card-head-row { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
    .card-head-row .card-title { margin-bottom: 0; }
    /* Always-visible chart legend (labels shouldn't require a hover to
       read) — kept compact so it doesn't get crowded with many slices. */
    .pd-chart-legend { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px 12px; margin-top: 10px; font-size: 10.5px; font-weight: 600; color: var(--s600); }
    .pd-chart-legend-item { display: flex; align-items: center; gap: 5px; white-space: nowrap; }
    .pd-chart-legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }

    @media (max-width: 1400px) {
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
        .stat-wrap .stat-kpi-popover { left: 6px; right: auto; }
        .stat-wrap .stat-kpi-popover::before { left: 190px; right: auto; }
        .stat-wrap:nth-child(3n) .stat-kpi-popover { left: auto; right: 0; }
        .stat-wrap:nth-child(3n) .stat-kpi-popover::before { left: auto; right: 190px; }
    }
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
        .alert-card-actions { width: 100%; justify-content: flex-start; }
        .alert-filter-wrap { flex: 1; min-width: 0; }
        .alert-category-select { width: 100%; min-width: 0; max-width: none; }
        .stat-wrap:nth-child(n) .stat-kpi-popover {
            position: fixed; left: 16px; right: 16px; top: 96px;
            width: auto; max-width: none; max-height: calc(100vh - 128px); overflow: auto;
        }
        .stat-wrap:nth-child(n) .stat-kpi-popover::before { display: none; }
        .stat-kpi-row { grid-template-columns: 1fr; }
        .stat-kpi-row-side { align-items: flex-start; text-align: left; }
        .quarter-detail-popover { position: fixed; left: 16px; right: 16px; top: 96px; width: auto; max-width: none; max-height: calc(100vh - 128px); overflow: auto; }
        .quarter-detail-popover.right { left: 16px; right: 16px; }
        .quarter-detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-layout-dashboard"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Chancellor</p>
            <h1 class="page-hdr-title">Campus Monitoring Dashboard</h1>
            <p class="page-hdr-sub">Monitor APP accomplishment, utilization, office risk, per-office procurement completion, and overdue PR alerts across campus.</p>
        </div>
    </div>

    @if(($awaitingSignature ?? 0) > 0)
    <div class="card" style="display:flex;align-items:center;gap:14px;border-color:#fac775;background:#fdf7ec;">
        <i class="ti ti-signature" style="font-size:24px;color:#854f0b;"></i>
        <div style="flex:1;">
            <p style="font-size:13px;font-weight:800;color:#854f0b;">{{ $awaitingSignature }} document{{ $awaitingSignature > 1 ? 's' : '' }} awaiting your signature</p>
            <p style="font-size:12px;color:#a16207;">Take a photo of the signed document to record your signature.</p>
        </div>
        <a href="{{ route('chancellor.for-my-signature') }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;border-radius:9px;background:#854f0b;color:#fff;font-size:12px;font-weight:700;text-decoration:none;">Open Queue <i class="ti ti-arrow-right"></i></a>
    </div>
    @endif

    <div class="stats-grid">
        @php
            $kpiCards = [
                [
                    'key' => 'totalAppItems',
                    'label' => 'Total APP Items',
                    'value' => number_format($summary['totalAppItems']),
                    'hint' => 'Approved items across campus',
                    'icon' => '<svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>',
                ],
                [
                    'key' => 'itemsProcured',
                    'label' => 'Items Procured',
                    'value' => number_format($summary['itemsProcured']),
                    'hint' => 'Completed procurement items',
                    'icon' => '<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                ],
                [
                    'key' => 'itemsPending',
                    'label' => 'Items Pending',
                    'value' => number_format($summary['itemsPending']),
                    'hint' => 'Not yet completed or in execution',
                    'icon' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                ],
                [
                    'key' => 'itemsOverdue',
                    'label' => 'Items Overdue',
                    'value' => number_format($summary['itemsOverdue']),
                    'hint' => 'Past target quarter or due date',
                    'icon' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                ],
                [
                    'key' => 'campusUtilization',
                    'label' => 'Campus Utilization',
                    'value' => $summary['campusUtilization'] . '%',
                    'hint' => 'Campus-wide APP budget coverage',
                    'icon' => '<svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
                ],
            ];
        @endphp

        @foreach ($kpiCards as $card)
            @php
                $detail = $kpiDetails[$card['key']] ?? [
                    'title' => $card['label'],
                    'lead' => '',
                    'rows' => [],
                    'empty' => 'No records found.',
                    'type' => 'items',
                    'countLabel' => 'record(s)',
                ];
                $rows = $detail['rows'] ?? [];
                $detailType = $detail['type'] ?? 'items';
            @endphp
            <div class="stat-wrap" tabindex="0" aria-describedby="chancellor-kpi-{{ $card['key'] }}">
                <article class="stat-card">
                    <div class="stat-icon">{!! $card['icon'] !!}</div>
                    <p class="stat-label">{{ $card['label'] }}</p>
                    <strong class="stat-value {{ $card['valueClass'] ?? '' }}">{!! $card['value'] !!}</strong>
                    <p class="stat-desc">{{ $card['hint'] }}</p>
                </article>

                <section class="stat-kpi-popover" id="chancellor-kpi-{{ $card['key'] }}" aria-labelledby="chancellor-kpi-title-{{ $card['key'] }}">
                    <div class="stat-kpi-head">
                        <div>
                            <p class="stat-kpi-eyebrow">Chancellor</p>
                            <h2 class="stat-kpi-title" id="chancellor-kpi-title-{{ $card['key'] }}">{{ $detail['title'] }}</h2>
                            <p class="stat-kpi-lead">{{ $detail['lead'] }}</p>
                        </div>
                    </div>
                    <div class="stat-kpi-count">
                        <span>{{ number_format(count($rows)) }}</span>
                        <span>{{ $detail['countLabel'] ?? 'record(s)' }}</span>
                    </div>
                    <div class="stat-kpi-scroll">
                        <div class="stat-kpi-list">
                            @forelse ($rows as $row)
                                @if ($detailType === 'utilization')
                                    <div class="stat-kpi-row">
                                        <div class="stat-kpi-row-main">
                                            <div class="stat-kpi-row-title">{{ $row['title'] }}</div>
                                            <div class="stat-kpi-row-meta">
                                                APP Budget PHP {{ number_format($row['budget']) }}
                                                &middot; Covered PHP {{ number_format($row['utilized']) }}
                                                &middot; Forecast {{ $row['forecast'] }}%
                                            </div>
                                            <div class="stat-kpi-row-remarks">Office utilization based on approved APP item amounts with matched active or completed PRs.</div>
                                        </div>
                                        <div class="stat-kpi-row-side">
                                            <strong>{{ $row['utilization'] }}%</strong>
                                            <span class="badge {{ $row['statusClass'] }}">{{ $row['risk'] }}</span>
                                        </div>
                                        <a class="stat-kpi-open-link" href="{{ $row['url'] }}">
                                            View report <i class="ti ti-arrow-right" style="font-size:12px"></i>
                                        </a>
                                    </div>
                                @else
                                    <div class="stat-kpi-row">
                                        <div class="stat-kpi-row-main">
                                            <div class="stat-kpi-row-title">{{ $row['name'] }}</div>
                                            <div class="stat-kpi-row-meta">
                                                <span class="badge {{ $row['statusClass'] }}">{{ $row['status'] }}</span>
                                                {{ $row['office'] }} &middot; {{ $row['code'] }}
                                                @if($row['fiscalYear'])
                                                    &middot; FY {{ $row['fiscalYear'] }}
                                                @endif
                                                &middot; {{ $row['quantity'] }} {{ $row['unit'] }}
                                                &middot; {{ $row['category'] }}
                                                &middot; {{ $row['quarter'] }}
                                            </div>
                                            <div class="stat-kpi-row-remarks">{{ $row['prNumber'] }} &middot; {{ $row['remarks'] }}</div>
                                        </div>
                                        <div class="stat-kpi-row-side">
                                            <strong>PHP {{ number_format($row['amount']) }}</strong>
                                        </div>
                                        <a class="stat-kpi-open-link" href="{{ $row['url'] }}" target="_blank" rel="noopener">
                                            View PPMP <i class="ti ti-arrow-right" style="font-size:12px"></i>
                                        </a>
                                    </div>
                                @endif
                            @empty
                                <div class="stat-kpi-empty">{{ $detail['empty'] }}</div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        @endforeach
    </div>

    <div class="charts-grid">
        <div class="card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-chart-donut"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">Campus-wide</p>
                    <h2 class="card-title">APP Item Status</h2>
                </div>
            </div>
            <div class="chart-wrap">
                <canvas id="itemStatusChart" data-status="{{ json_encode($itemStatusChart) }}"></canvas>
            </div>
            <div class="pd-chart-legend" id="itemStatusLegend"></div>
        </div>
        <div class="card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-building"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">Budget vs. Covered</p>
                    <div class="card-head-row" style="margin-bottom:0;">
                        <h2 class="card-title">Utilization by Office</h2>
                        <button type="button" class="chart-expand-btn" id="officeUtilExpandBtn" style="display:none;">
                            <i class="ti ti-arrows-vertical"></i><span>Expand</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="chart-wrap collapsible" id="officeUtilWrap">
                <canvas id="officeUtilizationChart" data-offices="{{ json_encode($officeUtilizationChart) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div class="chart-icon-badge"><i class="ti ti-calendar-stats"></i></div>
                <div>
                    <p class="card-eyebrow">Per office item count</p>
                    <h2 class="card-title">Procurement Status per Office</h2>
                </div>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Office</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th></tr>
                </thead>
                <tbody>
                    @forelse ($officeProcurementStatuses as $row)
                        <tr>
                            <td style="font-weight:600;color:var(--s600);white-space:nowrap;">
                                {{ $row['office'] }}
                                <span style="display:block;font-size:11px;font-weight:500;color:var(--s400);white-space:normal;">{{ $row['officeName'] }}</span>
                            </td>
                            @foreach (['q1', 'q2', 'q3', 'q4'] as $quarterKey)
                                @php
                                    $quarter = $row[$quarterKey];
                                    $quarterLabel = strtoupper($quarterKey);
                                    $popoverAlign = $loop->index >= 2 ? ' right' : '';
                                    $statusClass = match(strtolower($quarter['status'])) {
                                        'complete'    => 'badge-completed',
                                        'in progress' => 'badge-in-progress',
                                        'no items'    => 'badge-pending',
                                        default       => 'badge-pending',
                                    };
                                @endphp
                                <td class="quarter-status-cell" style="min-width:150px;">
                                    @if($quarter['totalItems'] > 0)
                                        <div class="quarter-status-surface" tabindex="0" aria-describedby="quarter-detail-{{ $row['office'] }}-{{ $quarterKey }}">
                                            <span class="badge {{ $statusClass }}">{{ $quarter['status'] }}</span>
                                            <span style="display:block;margin-top:6px;font-size:12px;color:var(--s600);">
                                                {{ $quarter['procuredItems'] }} {{ \Illuminate\Support\Str::plural('item', $quarter['procuredItems']) }} procured over {{ $quarter['totalItems'] }}
                                            </span>
                                            <div class="prog-wrap" style="margin-top:7px;">
                                                <p class="prog-label">{{ $quarter['procuredItems'] }} over {{ $quarter['totalItems'] }} ({{ $quarter['completionRate'] }}%)</p>
                                                <div class="prog-track"><div class="prog-fill-maroon" style="width:{{ min(100, $quarter['completionRate']) }}%"></div></div>
                                            </div>
                                        </div>
                                        <div class="quarter-detail-popover{{ $popoverAlign }}" id="quarter-detail-{{ $row['office'] }}-{{ $quarterKey }}" role="tooltip">
                                            <div class="quarter-detail-head">
                                                <div>
                                                    <div class="quarter-detail-title">{{ $row['office'] }} {{ $quarterLabel }} Items</div>
                                                    <div style="font-size:11px;color:var(--s500);margin-top:2px;">{{ $quarter['procuredItems'] }} procured, {{ $quarter['remainingItems'] }} not yet procured</div>
                                                </div>
                                                <div class="quarter-detail-meta">{{ $quarter['completionRate'] }}%</div>
                                            </div>
                                            <div class="quarter-detail-grid">
                                                <div class="quarter-detail-section">
                                                    <div class="quarter-detail-section-title"><span class="quarter-detail-dot done"></span>Procured</div>
                                                    <div class="quarter-detail-list">
                                                        @forelse (($quarter['procuredList'] ?? []) as $item)
                                                            <div class="quarter-detail-item">
                                                                <strong>{{ $item['name'] }}</strong>
                                                                <span>{{ $item['proposal'] }} &middot; {{ $item['prNumber'] }} &middot; {{ $item['status'] }}</span>
                                                            </div>
                                                        @empty
                                                            <div class="quarter-detail-empty">No procured items yet.</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                                <div class="quarter-detail-section">
                                                    <div class="quarter-detail-section-title"><span class="quarter-detail-dot todo"></span>Not yet procured</div>
                                                    <div class="quarter-detail-list">
                                                        @forelse (($quarter['notProcuredList'] ?? []) as $item)
                                                            <div class="quarter-detail-item">
                                                                <strong>{{ $item['name'] }}</strong>
                                                                <span>{{ $item['proposal'] }} &middot; {{ $item['prNumber'] }} &middot; {{ $item['status'] }}</span>
                                                            </div>
                                                        @empty
                                                            <div class="quarter-detail-empty">Everything in this quarter is procured.</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span style="font-size:12px;color:var(--s400);font-weight:600;">No APP items</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--s400);padding:22px;">No office APP items found yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="two-col">

        <div class="card">
            <div class="card-head">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="chart-icon-badge"><i class="ti ti-trending-up"></i></div>
                    <div>
                        <p class="card-eyebrow">Forecast - attention order</p>
                        <h2 class="card-title">Year-End Utilization by Office</h2>
                    </div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Office</th><th>Current</th><th>Forecast</th><th>Risk</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($forecasts as $forecast)
                            @php
                                $riskClass = match(strtolower($forecast['risk'])) {
                                    'low'  => 'badge-low-risk',
                                    'high', 'critical' => 'badge-high-risk',
                                    default => 'badge-medium-risk',
                                };
                            @endphp
                            <tr>
                                <td style="font-weight:600;color:var(--s600);white-space:nowrap;">{{ $forecast['office'] }}</td>
                                <td>
                                    <div class="prog-wrap">
                                        <p class="prog-label">{{ $forecast['currentUtilization'] }}%</p>
                                        <div class="prog-track"><div class="prog-fill-maroon" style="width:{{ min(100, $forecast['currentUtilization']) }}%"></div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="prog-wrap">
                                        <p class="prog-label">{{ $forecast['forecast'] }}%</p>
                                        <div class="prog-track"><div class="prog-fill-gold" style="width:{{ min(100, $forecast['forecast']) }}%"></div></div>
                                    </div>
                                </td>
                                <td><span class="badge {{ $riskClass }}">{{ $forecast['risk'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="chart-icon-badge"><i class="ti ti-list-numbers"></i></div>
                    <div>
                        <p class="card-eyebrow">Ranked by utilization rate</p>
                        <h2 class="card-title">Office Utilization Ranking</h2>
                    </div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Rank</th><th>Office</th><th>Utilization</th><th>Risk</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($utilizationRankings as $rank)
                            @php
                                $riskClass = match(strtolower($rank['risk'])) {
                                    'low'  => 'badge-low-risk',
                                    'high', 'critical' => 'badge-high-risk',
                                    default => 'badge-medium-risk',
                                };
                            @endphp
                            <tr>
                                <td><span class="rank-num {{ $rank['rank'] <= 3 ? 'top' : '' }}">{{ $rank['rank'] }}</span></td>
                                <td style="font-weight:600;color:var(--s600);">{{ $rank['office'] }}</td>
                                <td style="font-weight:700;color:var(--m);">{{ $rank['utilization'] }}%</td>
                                <td><span class="badge {{ $riskClass }}">{{ $rank['risk'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-head">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div class="chart-icon-badge"><i class="ti ti-alert-triangle"></i></div>
                <div>
                    <p class="card-eyebrow">Grouped by category</p>
                    <h2 class="card-title">Overdue PR Alerts</h2>
                </div>
            </div>
            <div class="alert-card-actions">
                @if(count($overdueAlertGroups) > 0)
                    <div class="alert-filter-wrap">
                        <i class="ti ti-filter ficon"></i>
                        <select class="alert-category-select" id="overdueCategoryFilter" title="Filter by category" aria-label="Filter overdue PR alerts by category">
                            <option value="">All categories</option>
                            @foreach ($overdueAlertGroups as $group)
                                <option value="{{ $group['category'] }}">{{ $group['category'] }} ({{ $group['count'] }})</option>
                            @endforeach
                        </select>
                        <i class="ti ti-chevron-down fchev"></i>
                    </div>
                @endif
                <span class="alert-count-chip" id="overdueAlertCount" data-total="{{ $summary['itemsOverdue'] }}">
                    {{ $summary['itemsOverdue'] }} {{ \Illuminate\Support\Str::plural('alert', $summary['itemsOverdue']) }}
                </span>
            </div>
        </div>
        <div class="alert-list-scroll" id="overdueAlertList" style="display:flex;flex-direction:column;gap:16px;">
            @forelse ($overdueAlertGroups as $group)
                <div class="alert-category-group" data-overdue-category-group data-category="{{ $group['category'] }}" data-count="{{ $group['count'] }}">
                    @foreach ($group['alerts'] as $alert)
                        @php
                            $statusClass = match(strtolower($alert['status'])) {
                                'completed'   => 'badge-completed',
                                'in progress' => 'badge-in-progress',
                                'pending'     => 'badge-pending',
                                default       => 'badge-overdue',
                            };
                        @endphp
                        <div class="alert-item">
                            <div>
                                <strong>{{ $alert['item'] }}</strong>
                                <span>{{ $alert['office'] }} &mdash; {{ $alert['prNumber'] }}</span>
                                <span>{{ $alert['daysOverdue'] }} days overdue</span>
                                <span>Action: {{ $alert['action'] }}</span>
                            </div>
                            <span class="badge {{ $statusClass }}">{{ $alert['status'] }}</span>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="alert-item">
                    <div>
                        <strong>No overdue PR alerts.</strong>
                        <span>All monitored APP items are within their target schedule.</span>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const statusEl = document.getElementById('itemStatusChart');
    if (statusEl) {
        const s = JSON.parse(statusEl.dataset.status || '{}');
        const labels = ['Procured', 'Pending', 'Overdue'];
        const colors = ['#3b6d11', '#185fa5', '#a32d2d'];
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: [s.procured || 0, s.pending || 0, s.overdue || 0],
                    backgroundColor: colors,
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
            },
        });
        // Always-visible labels (not just on hover) — a compact legend below
        // the doughnut instead of the default one, which needs a hover to
        // read counts and takes noticeably more vertical space.
        const legendEl = document.getElementById('itemStatusLegend');
        if (legendEl) {
            const counts = [s.procured || 0, s.pending || 0, s.overdue || 0];
            legendEl.innerHTML = labels.map((label, i) => `
                <span class="pd-chart-legend-item">
                    <span class="pd-chart-legend-dot" style="background:${colors[i]}"></span>${label} (${counts[i]})
                </span>
            `).join('');
        }
    }

    const officeEl = document.getElementById('officeUtilizationChart');
    if (officeEl) {
        const offices = JSON.parse(officeEl.dataset.offices || '[]');
        // The canvas itself keeps growing with office count instead of
        // squeezing many bars into a fixed box — this campus has dozens of
        // offices — but the outer wrap caps/scrolls that at 230px (matching
        // the item-status card) until "Expand" is clicked, instead of
        // stretching the whole grid row's height.
        officeEl.parentElement.style.height = Math.max(230, offices.length * 34) + 'px';
        const expandBtn = document.getElementById('officeUtilExpandBtn');
        if (expandBtn && offices.length * 34 > 230) {
            expandBtn.style.display = '';
            expandBtn.addEventListener('click', () => {
                const wrap = document.getElementById('officeUtilWrap');
                const expanded = wrap.classList.toggle('expanded');
                expandBtn.querySelector('span').textContent = expanded ? 'Collapse' : 'Expand';
            });
        }
        new Chart(officeEl, {
            type: 'bar',
            data: {
                labels: offices.map(o => o.office),
                datasets: [
                    { label: 'Budget', data: offices.map(o => o.budget), backgroundColor: '#c9a84c', borderRadius: 4 },
                    { label: 'Covered', data: offices.map(o => o.utilized), backgroundColor: '#681012', borderRadius: 4 },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: { x: { beginAtZero: true, ticks: { callback: v => '₱' + Number(v).toLocaleString() } } },
            },
        });
    }

    const overdueFilter = document.getElementById('overdueCategoryFilter');
    if (overdueFilter) {
        const alertList = document.getElementById('overdueAlertList');
        const countEl = document.getElementById('overdueAlertCount');
        const groups = Array.from(document.querySelectorAll('[data-overdue-category-group]'));

        const setAlertCount = (count) => {
            if (!countEl) return;
            const total = Number(count) || 0;
            countEl.textContent = `${total.toLocaleString()} ${total === 1 ? 'alert' : 'alerts'}`;
        };

        const applyOverdueFilter = () => {
            const selectedCategory = overdueFilter.value;
            let visibleCount = 0;

            groups.forEach(group => {
                const isVisible = !selectedCategory || group.dataset.category === selectedCategory;
                group.hidden = !isVisible;
                if (isVisible) visibleCount += Number(group.dataset.count || 0);
            });

            setAlertCount(visibleCount);
            if (alertList) alertList.scrollTop = 0;
        };

        overdueFilter.addEventListener('change', applyOverdueFilter);
        applyOverdueFilter();
    }
})();
</script>
@endpush
