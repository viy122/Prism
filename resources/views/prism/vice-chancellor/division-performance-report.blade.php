@extends('prism.layouts.app')
@section('title', 'Division Performance Report | Vice Chancellor')

@push('page-css')
<style>
    .content {
        padding: 28px 32px 56px; flex: 1; display: flex; flex-direction: column; gap: 20px;
        --m: var(--crimson); --gold: #c9a84c; --white: #ffffff;
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .btn-print { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px; background: var(--m); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: opacity .2s; white-space: nowrap; }
    .btn-print:hover { opacity: .88; }
    .btn-print svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card {
        position: relative; overflow: hidden;
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 18px; padding: 20px 20px 20px 24px;
        box-shadow: var(--sh-sm); transition: border-color .2s, box-shadow .2s;
    }
    .stat-card:hover { border-color: rgba(201,168,76,.5); box-shadow: 0 12px 28px rgba(15,23,42,.07); }
    .stat-card::before { content: ''; position: absolute; left: 0; top: 20px; width: 4px; height: 40px; border-radius: 0 4px 4px 0; background: var(--gold); }
    .stat-icon { position: absolute; right: 16px; top: 16px; width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--crimson-border); background: var(--crimson-mid); display: flex; align-items: center; justify-content: center; }
    .stat-icon svg { width: 17px; height: 17px; stroke: var(--crimson); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); }
    .stat-value { font-size: 1.55rem; font-weight: 800; color: var(--m); margin-top: 10px; display: block; letter-spacing: -.5px; line-height: 1.1; }
    .stat-desc  { font-size: 12px; color: var(--s500); margin-top: 8px; line-height: 1.6; }

    .highlight-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .highlight-card { position: relative; overflow: hidden; background: var(--white); border-radius: 18px; padding: 20px 20px 20px 24px; box-shadow: var(--sh-sm); }
    .highlight-card::before { content: ''; position: absolute; left: 0; top: 20px; width: 4px; height: 40px; border-radius: 0 4px 4px 0; }
    .highlight-card .hl-icon { position: absolute; right: 16px; top: 16px; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .highlight-card .hl-icon svg { width: 17px; height: 17px; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .highlight-card .hl-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 10px; position: relative; }
    .highlight-card .hl-name  { font-size: 1.35rem; font-weight: 800; color: var(--s900); letter-spacing: -.3px; line-height: 1.2; position: relative; }
    .highlight-card .hl-desc  { font-size: 12px; margin-top: 8px; line-height: 1.6; color: var(--s500); position: relative; }

    .highlight-best { border: 1px solid #c0dd97; }
    .highlight-best::before { background: #3b6d11; }
    .highlight-best .hl-icon { background: #eaf3de; border: 1px solid #c0dd97; }
    .highlight-best .hl-icon svg { stroke: #3b6d11; }
    .highlight-best .hl-label { color: #3b6d11; }

    .highlight-low { border: 1px solid #f7c1c1; }
    .highlight-low::before { background: #a32d2d; }
    .highlight-low .hl-icon { background: #fcebeb; border: 1px solid #f7c1c1; }
    .highlight-low .hl-icon svg { stroke: #a32d2d; }
    .highlight-low .hl-label { color: #a32d2d; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 52vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; }
    tbody tr:hover { background: var(--crimson-mid); }
    tbody tr.row-best { background: rgba(234,243,222,.5); }
    tbody tr.row-best:hover { background: rgba(234,243,222,.85); }
    tbody tr.row-low  { background: rgba(252,235,235,.5); }
    tbody tr.row-low:hover { background: rgba(252,235,235,.85); }

    .prog-wrap  { min-width: 130px; }
    .prog-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 6px; }
    .prog-track { height: 10px; border-radius: 99px; background: var(--s100); overflow: hidden; border: 1px solid var(--s200); }
    .prog-fill       { height: 100%; border-radius: 99px; background: var(--m); }
    .prog-fill.best  { background: #3b6d11; }
    .prog-fill.low   { background: #a32d2d; }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-best   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-low    { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-steady { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .highlight-grid { grid-template-columns: 1fr; } }
    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }

    @media print {
        .btn-print { display: none !important; }
        body { background: #fff; }
        .content { padding: 0; }
        .table-wrap { max-height: none; overflow: visible; }
    }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-chart-no-axes-combined"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Vice Chancellor</p>
            <h1 class="page-hdr-title">Division Performance Report</h1>
            <p class="page-hdr-sub">Review APP item accomplishment, budget utilization, and procurement completion rates across division offices.</p>
        </div>
        <button class="btn-print" type="button" onclick="window.print()">
            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </button>
    </div>

    @php
        $totalAppItems      = collect($performanceRows)->sum('totalAppItems');
        $totalProcured      = collect($performanceRows)->sum('procured');
        $totalPending       = collect($performanceRows)->sum('pending');
        $averageUtilization = round(collect($performanceRows)->avg('utilization'));
    @endphp


    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
            </div>
            <p class="stat-label">Total APP Items</p>
            <strong class="stat-value">{{ number_format($totalAppItems) }}</strong>
            <p class="stat-desc">Division-wide approved procurement items</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <p class="stat-label">Procured</p>
            <strong class="stat-value">{{ number_format($totalProcured) }}</strong>
            <p class="stat-desc">Completed items across the division</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <p class="stat-label">Pending</p>
            <strong class="stat-value">{{ number_format($totalPending) }}</strong>
            <p class="stat-desc">Items requiring follow-up or completion</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <p class="stat-label">Average Utilization</p>
            <strong class="stat-value">{{ $averageUtilization }}%</strong>
            <p class="stat-desc">Mean utilization rate across offices</p>
        </div>
    </div>

    <div class="highlight-grid">
        <div class="highlight-card highlight-best">
            <div class="hl-icon">
                <svg viewBox="0 0 24 24"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
            </div>
            <p class="hl-label">Best-performing office</p>
            <p class="hl-name">{{ $bestOffice }}</p>
            <p class="hl-desc">Highest utilization and procurement accomplishment in the division</p>
        </div>
        <div class="highlight-card highlight-low">
            <div class="hl-icon">
                <svg viewBox="0 0 24 24"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>
            </div>
            <p class="hl-label">Lowest-performing office</p>
            <p class="hl-name">{{ $lowestOffice }}</p>
            <p class="hl-desc">Needs follow-up due to lower utilization and pending procurement items</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Office utilization rates</p>
                <h2 class="card-title">Division Performance Summary</h2>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office Name</th>
                        <th>Total APP Items</th>
                        <th>Procured</th>
                        <th>Pending</th>
                        <th>Utilization</th>
                        <th>Highlight</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($performanceRows as $row)
                        @php
                            $isBest   = $row['performance'] === 'best';
                            $isLow    = $row['performance'] === 'lowest';
                            $rowClass = $isBest ? 'row-best' : ($isLow ? 'row-low' : '');
                            $barClass = $isBest ? 'best' : ($isLow ? 'low' : '');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['totalAppItems'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                            <td style="font-weight:600;color:{{ $isLow ? '#a32d2d' : 'var(--s700)' }};">{{ $row['pending'] }}</td>
                            <td>
                                <div class="prog-wrap">
                                    <p class="prog-label">{{ $row['utilization'] }}%</p>
                                    <div class="prog-track">
                                        <div class="prog-fill {{ $barClass }}" style="width:{{ $row['utilization'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($isBest)
                                    <span class="badge badge-best">Best-performing</span>
                                @elseif ($isLow)
                                    <span class="badge badge-low">Lowest-performing</span>
                                @else
                                    <span class="badge badge-steady">Steady</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
