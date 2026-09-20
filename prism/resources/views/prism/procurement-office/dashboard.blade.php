@extends('prism.layouts.app')
@section('title', 'Dashboard | Procurement Office')

@push('head-extras')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
@endpush

@push('page-css')
<style>
    .page-hdr { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr .filter-bar { flex-shrink: 0; }
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

    .chart-card-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
    .chart-icon-badge {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        background: var(--crimson-mid); border: 1px solid var(--crimson-border);
        display: flex; align-items: center; justify-content: center;
    }
    .chart-icon-badge i { font-size: 20px; color: var(--m); }
    .chart-card-head-text { flex: 1; min-width: 180px; }

    /* ── Filter bar ── */
    .filter-bar { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .filter-box {
        position: relative; display: flex; align-items: center; gap: 10px;
        height: 58px; background: var(--white); border: 1px solid var(--s200);
        border-radius: 12px; padding: 0 16px; min-width: 260px;
        transition: border-color .15s, box-shadow .15s;
    }
    .filter-box:hover, .filter-box:focus-within { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }
    .filter-box-icon {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
        background: var(--crimson-mid); display: flex; align-items: center; justify-content: center;
    }
    .filter-box-icon i { font-size: 16px; color: var(--crimson); }
    .filter-box-body { display: flex; flex-direction: column; line-height: 1.3; overflow: hidden; }
    .filter-box-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .09em; color: var(--crimson); }
    .filter-box-value { font-size: 14px; font-weight: 800; color: var(--s900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .filter-box-chev { font-size: 13px; color: var(--crimson); flex-shrink: 0; margin-left: auto; }
    .filter-box-select {
        position: absolute; inset: 0; width: 100%; height: 100%;
        opacity: 0; border: none; cursor: pointer; font-family: 'Poppins', sans-serif;
    }
    .filter-reset { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--m); text-decoration: none; }
    .filter-reset:hover { text-decoration: underline; }
    .filter-active-note { font-size: 11.5px; color: var(--s500); margin-top: -6px; }

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
    .badge-doc-pr      { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
    .badge-doc-aoc     { background: #f3e8ff; color: #6b21a8; border: 1px solid #e0c3fb; }
    .badge-doc-po      { background: #fff1e6; color: #9a4a09; border: 1px solid #fbd9b8; }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    /* ── Both cards in this row are pinned to the SAME fixed height, so one
         chart can never dwarf the other — the office-volume chart (which can
         hold a couple dozen offices) scrolls internally instead of growing
         the card. ── */
    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .chart-card  { display: flex; flex-direction: column; height: 360px; }
    .chart-wrap  { position: relative; width: 100%; flex: 1; min-height: 0; }

    /* Fixed height by default (top offices only) so the x-axis stays visible
       without scrolling — "Show all" grows the canvas to fit every office at
       once instead, which only needs a scrollbar past a couple dozen rows. */
    .volume-card { display: flex; flex-direction: column; }
    .volume-scroll { overflow-y: auto; position: relative; max-height: 640px; }
    .volume-scroll .chart-wrap { position: static; height: 380px; }
    .volume-toggle { display: flex; justify-content: center; margin-top: 12px; }
    .volume-toggle button {
        background: none; border: 1px solid var(--s200); border-radius: 20px;
        padding: 7px 16px; font-size: 12px; font-weight: 700; color: var(--m);
        font-family: 'Poppins', sans-serif; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .volume-toggle button:hover { background: var(--crimson-mid); }

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } .charts-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    @php
        $selectedOfficeLabel = 'All Offices';
        foreach ($filters['officeOptions'] as $o) {
            if ($filters['selectedOffice'] == $o->id) { $selectedOfficeLabel = $o->code . ' — ' . $o->name; break; }
        }
        $selectedYearLabel = $filters['selectedYear'] ? 'FY ' . $filters['selectedYear'] : 'All Years';
    @endphp
    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-layout-dashboard"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Dashboard</h1>
            <p class="page-hdr-sub">Purchase Requests, Abstracts of Canvass, and Purchase Orders — status, volume, and what's been waiting longest.</p>
        </div>
        <form class="filter-bar" method="GET" action="{{ route('procurement-office.dashboard') }}" id="dashFilterForm">
            <div class="filter-box">
                <div class="filter-box-icon"><i class="ti ti-building"></i></div>
                <div class="filter-box-body">
                    <span class="filter-box-label">Office</span>
                    <span class="filter-box-value">{{ $selectedOfficeLabel }}</span>
                </div>
                <i class="ti ti-chevron-down filter-box-chev"></i>
                <select class="filter-box-select" id="filterOffice" name="office" onchange="document.getElementById('dashFilterForm').submit()">
                    <option value="">All Offices</option>
                    @foreach ($filters['officeOptions'] as $o)
                        <option value="{{ $o->id }}" @selected($filters['selectedOffice'] == $o->id)>{{ $o->code }} — {{ $o->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-box">
                <div class="filter-box-icon"><i class="ti ti-calendar"></i></div>
                <div class="filter-box-body">
                    <span class="filter-box-label">Fiscal Year</span>
                    <span class="filter-box-value">{{ $selectedYearLabel }}</span>
                </div>
                <i class="ti ti-chevron-down filter-box-chev"></i>
                <select class="filter-box-select" id="filterYear" name="year" onchange="document.getElementById('dashFilterForm').submit()">
                    <option value="">All Years</option>
                    @foreach ($filters['yearOptions'] as $y)
                        <option value="{{ $y }}" @selected($filters['selectedYear'] == $y)>FY {{ $y }}</option>
                    @endforeach
                </select>
            </div>
            @if ($filters['selectedOffice'] || $filters['selectedYear'])
                <a href="{{ route('procurement-office.dashboard') }}" class="filter-reset"><i class="ti ti-x"></i> Clear filters</a>
            @endif
        </form>
    </div>
    @if ($filters['selectedOffice'] || $filters['selectedYear'])
        <p class="filter-active-note">Showing filtered results — everything below updates to match.</p>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v3"/><path d="M9 11h4m-4 4h2"/><rect x="13" y="13" width="8" height="8" rx="1"/><path d="M17 13v-2"/></svg>
            </div>
            <p class="stat-label">Purchase Requests</p>
            <strong class="stat-value">{{ number_format($summary['totalPrs']) }}</strong>
            <p class="stat-desc">Uploaded and routed to Procurement Office</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 12h8M8 8h5M8 16h4"/></svg>
            </div>
            <p class="stat-label">Abstracts of Canvass</p>
            <strong class="stat-value">{{ number_format($summary['totalAocs']) }}</strong>
            <p class="stat-desc">Created from fully-canvassed PRs</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M6 2l1 4h10l1-4"/><rect x="4" y="6" width="16" height="16" rx="2"/><path d="M9 11h6M9 15h6"/></svg>
            </div>
            <p class="stat-label">Purchase Orders</p>
            <strong class="stat-value">{{ number_format($summary['totalPos']) }}</strong>
            <p class="stat-desc">Issued to suppliers, all statuses</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <p class="stat-label">Needs Attention</p>
            <strong class="stat-value">{{ number_format($summary['overdueCount']) }}</strong>
            <p class="stat-desc">PR/AOC/PO not yet fully signed, {{ $summary['overdueThresholdDays'] }}+ days since the PR was submitted</p>
        </div>
    </div>

    <div class="charts-grid">
        <article class="card chart-card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-chart-bar"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">By document type</p>
                    <h2 class="card-title">Status — PR vs AOC vs PO</h2>
                </div>
            </div>
            <div class="chart-wrap">
                <canvas id="docStatusChart" data-status="{{ json_encode($docStatusChart) }}"></canvas>
            </div>
        </article>
        <article class="card chart-card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-chart-donut"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">Pipeline share</p>
                    <h2 class="card-title">Document Mix</h2>
                </div>
            </div>
            <div class="chart-wrap">
                <canvas id="docMixChart" data-mix="{{ json_encode($docMixChart) }}"></canvas>
            </div>
        </article>
    </div>

    <div class="card volume-card">
        <div class="chart-card-head">
            <div class="chart-icon-badge"><i class="ti ti-building"></i></div>
            <div class="chart-card-head-text">
                <p class="card-eyebrow">By office</p>
                <h2 class="card-title">Volume — PR / AOC / PO per Office</h2>
            </div>
        </div>
        <div class="volume-scroll">
            <div class="chart-wrap" id="officeVolumeWrap">
                <canvas id="officeVolumeChart" data-offices="{{ json_encode($officeVolumeChart) }}"></canvas>
            </div>
        </div>
        @if (count($officeVolumeChart) > 10)
        <div class="volume-toggle">
            <button type="button" id="officeVolumeToggle"><i class="ti ti-chevron-down"></i> Show all {{ count($officeVolumeChart) }} offices</button>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-head">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div class="chart-icon-badge"><i class="ti ti-list-details"></i></div>
                <div>
                    <p class="card-eyebrow">Current status</p>
                    <h2 class="card-title">PRs per Office by Status</h2>
                </div>
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
                    @forelse ($officeStatusGroups as $office)
                        <tr>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);">{{ $office['office'] }}</td>
                            <td><span class="badge badge-completed">{{ $office['completed'] }}</span></td>
                            <td><span class="badge badge-in-progress">{{ $office['inProgress'] }}</span></td>
                            <td><span class="badge badge-pending">{{ $office['pending'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--s500);">No purchase requests match the current filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div style="display:flex;align-items:flex-start;gap:14px;flex:1;min-width:0;">
                <div class="chart-icon-badge"><i class="ti ti-alert-triangle"></i></div>
                <div style="min-width:0;">
                    <p class="card-eyebrow">Longest waiting</p>
                    <h2 class="card-title">Needs Attention — PR, AOC &amp; PO</h2>
                    <p class="card-sub">Not yet fully signed, oldest submission first, across all three document types. No due-date field exists on any of them — this is how long each has genuinely been waiting for action.</p>
                </div>
            </div>
            <span class="count-chip" id="urgentVisibleCount" style="flex-shrink:0;">{{ count($urgentDocs) }} shown</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Office</th>
                        <th>No.</th>
                        <th>Item / Title</th>
                        <th>Days Pending</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($urgentDocs as $d)
                        @php
                            $statusSlug = match(strtolower($d['status'])) {
                                'completed'   => 'badge-completed',
                                'in progress' => 'badge-in-progress',
                                'pending'     => 'badge-pending',
                                default       => 'badge-overdue',
                            };
                            $docSlug = match($d['docType']) { 'AOC' => 'badge-doc-aoc', 'PO' => 'badge-doc-po', default => 'badge-doc-pr' };
                        @endphp
                        <tr>
                            <td><span class="badge {{ $docSlug }}">{{ $d['docType'] }}</span></td>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $d['office'] }}</td>
                            <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $d['number'] }}</td>
                            <td style="font-size:13px;color:var(--s900);font-weight:600;">{{ $d['item'] }}</td>
                            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $d['daysPending'] }} days</td>
                            <td><span class="badge {{ $statusSlug }}">{{ $d['status'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--s500);">Nothing waiting — everything in scope is fully signed.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const STATUS_COLORS = { pending: '#c9862b', in_progress: '#2f7fc4', completed: '#4f8a1f' };
    const DOC_COLORS     = { pr: '#a13a3f', aoc: '#8a76b5', po: '#d9a15c' };

    // ── Status — PR vs AOC vs PO (stacked horizontal bar) ───────────────────
    // A stacked bar reads three documents' status mix at a glance and lets
    // segments be compared by length — three separate pies would ask the eye
    // to compare angles/areas across charts instead, which is a much harder
    // comparison for the same information.
    const statusEl = document.getElementById('docStatusChart');
    if (statusEl) {
        const rows = JSON.parse(statusEl.dataset.status || '[]');
        new Chart(statusEl, {
            type: 'bar',
            data: {
                labels: rows.map(r => r.doc),
                datasets: [
                    { label: 'Pending',     data: rows.map(r => r.pending),     backgroundColor: STATUS_COLORS.pending },
                    { label: 'In Progress', data: rows.map(r => r.in_progress), backgroundColor: STATUS_COLORS.in_progress },
                    { label: 'Completed',   data: rows.map(r => r.completed),   backgroundColor: STATUS_COLORS.completed },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                scales: { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true } },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                },
            },
        });
    }

    // ── Document Mix (donut, with initial minimal labels) ──────────────────
    // Pie/donut is the right call here: 3 categories, and the question is
    // genuinely part-to-whole ("what share of the pipeline is each document
    // type"), not a precise pairwise comparison — that's what the stacked
    // bar above is for. Counts are printed on the slices up front instead of
    // requiring a hover, per the "minimal initial labels" ask.
    const mixEl = document.getElementById('docMixChart');
    if (mixEl) {
        const m = JSON.parse(mixEl.dataset.mix || '{}');
        new Chart(mixEl, {
            type: 'doughnut',
            data: {
                labels: ['Purchase Requests', 'Abstracts of Canvass', 'Purchase Orders'],
                datasets: [{
                    data: [m.pr || 0, m.aoc || 0, m.po || 0],
                    backgroundColor: [DOC_COLORS.pr, DOC_COLORS.aoc, DOC_COLORS.po],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    datalabels: {
                        color: '#fff', font: { size: 12, weight: '700' },
                        formatter: (v) => v > 0 ? v : '',
                    },
                },
            },
            plugins: [ChartDataLabels],
        });
    }

    // ── Volume per office — grouped bar, PR/AOC/PO side by side ─────────────
    // Defaults to the top 10 offices (already sorted desc by total volume
    // server-side) at a fixed height matching its sibling cards, so the
    // x-axis is always visible without scrolling — "Show all" swaps in the
    // full list and grows the canvas to fit every office at once (only that
    // expanded view scrolls, which is expected once you've asked to see all
    // of them, rather than by default whenever the office count grows).
    const officeEl = document.getElementById('officeVolumeChart');
    if (officeEl) {
        const allOffices = JSON.parse(officeEl.dataset.offices || '[]');
        const topOffices  = allOffices.slice(0, 10);
        const wrap        = document.getElementById('officeVolumeWrap');
        const toggleBtn    = document.getElementById('officeVolumeToggle');
        let officeChart, expanded = false;

        function renderOfficeChart(offices) {
            if (officeChart) officeChart.destroy();
            officeChart = new Chart(officeEl, {
                type: 'bar',
                data: {
                    labels: offices.map(o => o.office),
                    datasets: [
                        { label: 'PR',  data: offices.map(o => o.pr),  backgroundColor: DOC_COLORS.pr,  borderRadius: 3 },
                        { label: 'AOC', data: offices.map(o => o.aoc), backgroundColor: DOC_COLORS.aoc, borderRadius: 3 },
                        { label: 'PO',  data: offices.map(o => o.po),  backgroundColor: DOC_COLORS.po,  borderRadius: 3 },
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

        renderOfficeChart(topOffices);

        toggleBtn?.addEventListener('click', () => {
            expanded = !expanded;
            wrap.style.height = expanded ? Math.max(380, allOffices.length * 46) + 'px' : '380px';
            renderOfficeChart(expanded ? allOffices : topOffices);
            toggleBtn.innerHTML = expanded
                ? '<i class="ti ti-chevron-up"></i> Show top 10 only'
                : `<i class="ti ti-chevron-down"></i> Show all ${allOffices.length} offices`;
        });
    }
})();
</script>
@endpush
