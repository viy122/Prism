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
        border-radius: 18px; padding: 20px 20px 20px 24px;
        box-shadow: var(--sh-sm); transition: border-color .2s, box-shadow .2s;
    }
    .stat-card:hover { border-color: var(--crimson-border); box-shadow: 0 12px 28px rgba(15,23,42,.07); }
    .stat-card::before { content: ''; position: absolute; left: 0; top: 20px; width: 4px; height: 40px; border-radius: 0 4px 4px 0; background: var(--gold); }
    .stat-icon { position: absolute; right: 16px; top: 16px; width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--crimson-border); background: var(--crimson-mid); display: flex; align-items: center; justify-content: center; }
    .stat-icon svg { width: 17px; height: 17px; stroke: var(--crimson); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); }
    .stat-value { font-size: 1.55rem; font-weight: 800; color: var(--m); margin-top: 10px; display: block; letter-spacing: -.5px; line-height: 1.1; }
    .stat-desc  { font-size: 12px; color: var(--s500); margin-top: 8px; line-height: 1.6; }

    .btn-print { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px; background: var(--crimson); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: opacity .2s; white-space: nowrap; }
    .btn-print:hover { opacity: .88; }
    .btn-print svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

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
    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .chart-wrap  { position: relative; width: 100%; height: 230px; }
    .report-meta { font-size: 11px; color: var(--s400); margin-top: 2px; }

    .delay-list { display: flex; flex-direction: column; gap: 10px; }
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
        .btn-print { display: none !important; }
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
        <div class="page-hdr-icon"><i class="ti ti-chart-no-axes-combined"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Chancellor</p>
            <h1 class="page-hdr-title">Procurement Reports</h1>
            <p class="page-hdr-sub">Review campus-wide procurement accomplishment, year-end utilization, and delayed items grouped by office.</p>
            <p class="report-meta">Generated {{ $generatedAt }}</p>
        </div>
        <button class="btn-print" type="button" onclick="window.print()">
            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </button>
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

    <div class="charts-grid">
        <div class="card">
            <p class="card-eyebrow">Per office</p>
            <h2 class="card-title" style="margin-bottom:16px;">Targeted vs Procured</h2>
            <div class="chart-wrap">
                <canvas id="accomplishmentChart" data-rows="{{ json_encode($accomplishmentChart) }}"></canvas>
            </div>
        </div>
        <div class="card">
            <p class="card-eyebrow">Per office</p>
            <h2 class="card-title" style="margin-bottom:16px;">Budget vs Utilized</h2>
            <div class="chart-wrap">
                <canvas id="utilizationChart" data-rows="{{ json_encode($utilizationChart) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Campus-wide accomplishment</p>
                <h2 class="card-title">Items Targeted vs Procured per Office</h2>
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
            <div>
                <p class="card-eyebrow">Q1 to Q4</p>
                <h2 class="card-title">Quarterly Accomplishment</h2>
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
                <div>
                    <p class="card-eyebrow">Year-end budget utilization</p>
                    <h2 class="card-title">Summary by Office</h2>
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
                <div>
                    <p class="card-eyebrow">Delayed items grouped by office</p>
                    <h2 class="card-title">Delay Reasons</h2>
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
(function () {
    // Horizontal, not vertical — dozens of offices campus-wide (many with
    // long codes) means a vertical bar chart's x-axis labels collide past a
    // handful of bars. Horizontal bars read one office per row regardless of
    // count, with the row growing to fit the data instead of squeezing it.
    const accEl = document.getElementById('accomplishmentChart');
    if (accEl) {
        const rows = JSON.parse(accEl.dataset.rows || '[]');
        accEl.parentElement.style.height = Math.max(230, rows.length * 34) + 'px';
        new Chart(accEl, {
            type: 'bar',
            data: {
                labels: rows.map(r => r.office),
                datasets: [
                    { label: 'Targeted', data: rows.map(r => r.targeted), backgroundColor: '#c9a84c', borderRadius: 4 },
                    { label: 'Procured', data: rows.map(r => r.procured), backgroundColor: '#681012', borderRadius: 4 },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }

    const utilEl = document.getElementById('utilizationChart');
    if (utilEl) {
        const rows = JSON.parse(utilEl.dataset.rows || '[]');
        utilEl.parentElement.style.height = Math.max(230, rows.length * 34) + 'px';
        new Chart(utilEl, {
            type: 'bar',
            data: {
                labels: rows.map(r => r.office),
                datasets: [
                    { label: 'Budget', data: rows.map(r => r.budget), backgroundColor: '#c9a84c', borderRadius: 4 },
                    { label: 'Utilized', data: rows.map(r => r.utilized), backgroundColor: '#681012', borderRadius: 4 },
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
})();
</script>
@endpush
