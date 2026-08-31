@extends('prism.layouts.app')
@section('title', 'Dashboard | Procurement Office')

@push('head-extras')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
@endpush

@push('page-css')
<style>
    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .content {
        padding: 28px 32px 56px; flex: 1; display: flex; flex-direction: column; gap: 20px;
        --m: var(--crimson); --m-dk: var(--crimson-dark); --white: #ffffff;
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .btn-outline {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px;
        background: var(--white); color: var(--m);
        font-size: 13px; font-weight: 700; cursor: pointer;
        font-family: 'Poppins', sans-serif;
        border: 1px solid rgba(139,26,28,.3);
        transition: background .2s, border-color .2s; white-space: nowrap;
    }
    .btn-outline:hover { background: rgba(139,26,28,.05); border-color: var(--m); }
    .btn-outline svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card {
        position: relative; overflow: hidden;
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 18px; padding: 20px 20px 20px 24px;
        box-shadow: var(--sh-sm); transition: border-color .2s, box-shadow .2s;
    }
    .stat-card:hover { border-color: var(--crimson-border); box-shadow: 0 12px 28px rgba(15,23,42,.07); }
    .stat-card::before { content: ''; position: absolute; left: 0; top: 20px; width: 4px; height: 40px; border-radius: 0 4px 4px 0; background: var(--crimson); }
    .stat-icon { position: absolute; right: 16px; top: 16px; width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--crimson-border); background: var(--crimson-mid); display: flex; align-items: center; justify-content: center; }
    .stat-icon svg { width: 17px; height: 17px; stroke: var(--crimson); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); }
    .stat-value { font-size: 1.55rem; font-weight: 800; color: var(--m); margin-top: 10px; display: block; letter-spacing: -.5px; line-height: 1.1; }
    .stat-desc  { font-size: 12px; color: var(--s500); margin-top: 8px; line-height: 1.6; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 52vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; }
    tbody tr:hover { background: var(--crimson-mid); }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-completed   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-in-progress { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-pending     { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-overdue     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    .charts-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 16px; }
    .chart-wrap  { position: relative; width: 100%; height: 230px; }

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } .charts-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-layout-dashboard"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Dashboard</h1>
            <p class="page-hdr-sub">Track received purchase requests, current processing status, and the PRs that have been waiting longest for action.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v3"/><path d="M9 11h4m-4 4h2"/><rect x="13" y="13" width="8" height="8" rx="1"/><path d="M17 13v-2"/></svg>
            </div>
            <p class="stat-label">Total PRs received</p>
            <strong class="stat-value">{{ number_format($summary['totalPrsReceived']) }}</strong>
            <p class="stat-desc">Uploaded and routed to Procurement Office</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <p class="stat-label">PRs in progress</p>
            <strong class="stat-value">{{ number_format($summary['prsInProgress']) }}</strong>
            <p class="stat-desc">Signature routing has started, not yet fully signed</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <p class="stat-label">Completed</p>
            <strong class="stat-value">{{ number_format($summary['prsCompleted']) }}</strong>
            <p class="stat-desc">Fully signed PRs, all time</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <p class="stat-label">Overdue PRs</p>
            <strong class="stat-value">{{ number_format($summary['overduePrs']) }}</strong>
            <p class="stat-desc">Not yet fully signed, {{ $summary['overdueThresholdDays'] }}+ days since submission</p>
        </div>
    </div>

    <div class="charts-grid">
        <article class="card">
            <p class="card-eyebrow">Campus-wide</p>
            <h2 class="card-title" style="margin-bottom:16px;">PR Status</h2>
            <div class="chart-wrap">
                <canvas id="statusChart" data-status="{{ json_encode($statusChart) }}"></canvas>
            </div>
        </article>
        <article class="card">
            <p class="card-eyebrow">By office</p>
            <h2 class="card-title" style="margin-bottom:16px;">PR Volume</h2>
            <div class="chart-wrap">
                <canvas id="officeVolumeChart" data-offices="{{ json_encode($officeVolumeChart) }}"></canvas>
            </div>
        </article>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Current status</p>
                <h2 class="card-title">PRs per Office by Status</h2>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Completed</th>
                        <th>In Progress</th>
                        <th>Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($officeStatusGroups as $office)
                        <tr>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);">{{ $office['office'] }}</td>
                            <td><span class="badge badge-completed">{{ $office['completed'] }}</span></td>
                            <td><span class="badge badge-in-progress">{{ $office['inProgress'] }}</span></td>
                            <td><span class="badge badge-pending">{{ $office['pending'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Longest waiting</p>
                <h2 class="card-title">Urgent PRs</h2>
                <p class="card-sub">Not yet fully signed, oldest submission first. No due-date field exists on a PR — this is how long each has genuinely been waiting for action.</p>
            </div>
            <span class="count-chip" id="urgentPrVisibleCount">{{ count($urgentPrs) }} shown</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>PR No.</th>
                        <th>Item</th>
                        <th>Quarter</th>
                        <th>Days Pending</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($urgentPrs as $pr)
                        @php
                            $statusSlug = match(strtolower($pr['status'])) {
                                'completed'   => 'badge-completed',
                                'in progress' => 'badge-in-progress',
                                'pending'     => 'badge-pending',
                                default       => 'badge-overdue',
                            };
                        @endphp
                        <tr>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $pr['office'] }}</td>
                            <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $pr['prNumber'] }}</td>
                            <td style="font-size:13px;color:var(--s900);font-weight:600;">{{ $pr['item'] }}</td>
                            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $pr['targetQuarter'] }}</td>
                            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $pr['daysPending'] }} days</td>
                            <td><span class="badge {{ $statusSlug }}">{{ $pr['status'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const statusEl = document.getElementById('statusChart');
    if (statusEl) {
        const s = JSON.parse(statusEl.dataset.status || '{}');
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Completed'],
                datasets: [{
                    data: [s.pending || 0, s.in_progress || 0, s.completed || 0],
                    backgroundColor: ['#854f0b', '#185fa5', '#3b6d11'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            },
        });
    }

    const officeEl = document.getElementById('officeVolumeChart');
    if (officeEl) {
        const offices = JSON.parse(officeEl.dataset.offices || '[]');
        // Horizontal, not vertical — dozens of offices campus-wide means a
        // vertical bar chart's x-axis labels collide past a handful of bars.
        officeEl.parentElement.style.height = Math.max(230, offices.length * 34) + 'px';
        new Chart(officeEl, {
            type: 'bar',
            data: {
                labels: offices.map(o => o.office),
                datasets: [{ label: 'Purchase Requests', data: offices.map(o => o.count), backgroundColor: '#681012', borderRadius: 4 }],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }
})();
</script>
@endpush
