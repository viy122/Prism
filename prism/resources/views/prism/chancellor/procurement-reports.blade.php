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

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card {
        position: relative; overflow: hidden;
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 15px; padding: 18px 20px 16px;
        box-shadow: var(--sh-sm); transition: box-shadow .25s, border-color .25s, transform .2s;
    }
    .stat-card:hover { box-shadow: var(--sh-lg); border-color: rgba(104,16,18,.2); transform: translateY(-2px); }
    .stat-card::before { content: ''; position: absolute; left: 0; top: 16px; width: 4px; height: 38px; border-radius: 0 4px 4px 0; background: var(--m); }
    .stat-icon { position: absolute; right: 16px; top: 16px; width: 38px; height: 38px; border-radius: 11px; background: rgba(104,16,18,.07); display: flex; align-items: center; justify-content: center; }
    .stat-icon svg { width: 19px; height: 19px; stroke: var(--m); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s400); margin-bottom: 9px; }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--m); display: block; letter-spacing: -.7px; line-height: 1; margin-bottom: 5px; }
    .stat-desc  { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

    .btn-print { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px; background: var(--crimson); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: opacity .2s; white-space: nowrap; }
    .btn-print:hover { opacity: .88; }
    .btn-print svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .report-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .office-filter-wrap { position: relative; display: inline-flex; align-items: center; }
    .office-filter-wrap i { position: absolute; left: 14px; font-size: 15px; color: var(--m); pointer-events: none; }
    .office-filter-select { height: 42px; padding: 0 14px 0 38px; border-radius: 10px; border: 1px solid var(--s300); background: var(--white); font-size: 13px; font-weight: 600; color: var(--s700); font-family: 'Poppins', sans-serif; cursor: pointer; }
    .print-only-filter-note { display: none; }

    .completion-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--crimson-mid); color: var(--crimson); border: 1px solid var(--crimson-border); white-space: nowrap; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 52vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--crimson-mid); }

    .prog-wrap  { min-width: 140px; }
    .prog-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 6px; }
    .prog-track { height: 10px; border-radius: 99px; background: var(--s100); overflow: hidden; border: 1px solid var(--s200); }
    .prog-fill  { height: 100%; border-radius: 99px; background: var(--m); }

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

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .two-col { grid-template-columns: 1fr; } .charts-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
    @media (max-width: 640px)  { .stats-grid { grid-template-columns: 1fr; } }

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

<div class="content">

    @php
        $totalTargeted    = collect($accomplishmentRows)->sum('targeted');
        $totalProcured    = collect($accomplishmentRows)->sum('procured');
        $campusCompletion = $totalTargeted > 0 ? round(($totalProcured / $totalTargeted) * 100) : 0;
        $delayedCount     = collect($delayedByOffice)->flatten(1)->count();
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
            @if($selectedOffice)
            <p class="print-only-filter-note">Filtered to office: {{ $selectedOffice }}</p>
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
            <button class="btn-print" type="button" onclick="window.print()">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print
            </button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/></svg></div>
            <p class="stat-label">Campus Targets</p>
            <strong class="stat-value">{{ number_format($totalTargeted) }}</strong>
            <p class="stat-desc">Procurement targets across monitored offices</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            <p class="stat-label">Items Procured</p>
            <strong class="stat-value">{{ number_format($totalProcured) }}</strong>
            <p class="stat-desc">Completed procurement items across campus</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
            <p class="stat-label">Completion Rate</p>
            <strong class="stat-value">{{ $campusCompletion }}%</strong>
            <p class="stat-desc">Campus-wide procurement accomplishment</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
            <p class="stat-label">Delayed Items</p>
            <strong class="stat-value">{{ $delayedCount }}</strong>
            <p class="stat-desc">Risk items grouped by office for follow-up</p>
        </div>
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
                        <tr>
                            <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['targeted'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                            <td>
                                <div class="prog-wrap">
                                    <p class="prog-label">{{ $row['completionRate'] }}%</p>
                                    <div class="prog-track"><div class="prog-fill" style="width:{{ $row['completionRate'] }}%"></div></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
                    @foreach ($quarterlyRows as $row)
                        <tr>
                            <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                            <td style="color:var(--s500);">{{ $row['quarter'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['targeted'] }}</td>
                            <td style="font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                            <td>
                                <div class="prog-wrap">
                                    <p class="prog-label">{{ $row['completionRate'] }}%</p>
                                    <div class="prog-track"><div class="prog-fill" style="width:{{ $row['completionRate'] }}%"></div></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
                        @foreach ($utilizationSummary as $row)
                            @php
                                $riskClass = match(strtolower($row['risk'])) {
                                    'low'  => 'badge-low',
                                    'high', 'critical' => 'badge-high',
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
@endsection

@push('scripts')
<script>
document.getElementById('officeFilter').addEventListener('change', function () {
    const url = new URL(window.location.href);
    if (this.value) {
        url.searchParams.set('office', this.value);
    } else {
        url.searchParams.delete('office');
    }
    window.location.href = url.toString();
});

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
