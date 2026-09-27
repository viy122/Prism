@extends('prism.layouts.app')
@section('title', 'Dashboard | Budget Office')

@push('head-extras')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
@endpush

@push('page-css')
<style>
    :root {
        --s50:   #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0;
        --s400:  #94a3b8; --s500: #64748b; --s600: #475569;
        --s700:  #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
        --sh-md: 0 4px 16px rgba(15,23,42,.08), 0 1px 4px rgba(15,23,42,.04);
        --sh-lg: 0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05);
    }

    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .pd-stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 13px; margin-bottom: 0; }
    .pd-stat-wrap { position: relative; min-width: 0; outline: none; z-index: 1; }
    .pd-stat-wrap:hover,
    .pd-stat-wrap:focus-within { z-index: 80; }
    .pd-stat {
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 15px; padding: 16px 18px;
        position: relative; overflow: visible; box-shadow: var(--sh-sm);
        transition: box-shadow .25s, border-color .25s, transform .2s;
        min-height: 112px; height: 100%;
        display: flex; flex-direction: column;
    }
    .pd-stat:hover,
    .pd-stat-wrap:hover .pd-stat,
    .pd-stat-wrap:focus-within .pd-stat {
        border-color: rgba(192,57,59,.45);
        box-shadow:
            0 0 0 1px rgba(192,57,59,.20),
            0 10px 28px rgba(139,26,28,.16),
            0 2px 8px rgba(15,23,42,.08);
        transform: translateY(-2px);
    }
    .pd-stat::before {
        content: ""; position: absolute; left: 0; top: 24px;
        width: 4px; height: 35px; background: var(--crimson); border-radius: 0 4px 4px 0;
    }
    .pd-stat-icon {
        position: absolute; right: 16px; top: 14px;
        width: 36px; height: 36px; border-radius: 10px;
        background: var(--crimson-mid);
        display: flex; align-items: center; justify-content: center;
    }
    .pd-stat-icon i { font-size: 18px; color: var(--crimson); }
    .pd-stat-label { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--s400); margin-bottom: 8px; max-width: calc(100% - 58px); }
    .pd-stat-value { font-size: 28px; font-weight: 800; color: var(--crimson); letter-spacing: -.7px; line-height: 1; margin-bottom: 7px; }
    .pd-stat-value.sm { font-size: clamp(24px, 1.55vw, 28px); letter-spacing: -.7px; white-space: nowrap; }
    .pd-stat-hint { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

    .pd-kpi-popover {
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
    .pd-kpi-popover::before {
        content: ""; position: absolute; top: -9px; left: 190px;
        width: 18px; height: 18px; background: #fff;
        border-left: 1px solid var(--crimson-border);
        border-top: 1px solid var(--crimson-border);
        transform: rotate(45deg);
    }
    .pd-stat-wrap:nth-child(3) .pd-kpi-popover,
    .pd-stat-wrap:nth-child(4) .pd-kpi-popover { left: auto; right: 0; }
    .pd-stat-wrap:nth-child(3) .pd-kpi-popover::before,
    .pd-stat-wrap:nth-child(4) .pd-kpi-popover::before { left: auto; right: 190px; }
    .pd-stat-wrap:hover .pd-kpi-popover,
    .pd-stat-wrap:focus-within .pd-kpi-popover {
        opacity: 1; pointer-events: auto; transform: translateY(0); visibility: visible;
    }
    .pd-kpi-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .pd-kpi-eyebrow { font-size: 9px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--crimson); margin-bottom: 4px; }
    .pd-kpi-title { font-size: 18px; font-weight: 800; color: var(--s900); letter-spacing: -.35px; margin: 0 0 5px; line-height: 1.15; }
    .pd-kpi-lead { font-size: 12.5px; color: var(--s600); line-height: 1.5; margin: 0; }
    .pd-kpi-count { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 11px; font-weight: 800; color: var(--crimson); }
    .pd-kpi-scroll { max-height: 310px; overflow: auto; padding-right: 3px; }
    .pd-kpi-row {
        display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 10px;
        background: var(--s50); border: 1px solid var(--s200);
        border-radius: 8px; padding: 11px 12px;
    }
    .pd-kpi-row + .pd-kpi-row { margin-top: 8px; }
    .pd-kpi-row-main { min-width: 0; }
    .pd-kpi-row-title { color: var(--s900); font-size: 12.5px; font-weight: 800; line-height: 1.35; margin-bottom: 3px; }
    .pd-kpi-row-meta { color: var(--s500); font-size: 11px; line-height: 1.55; }
    .pd-kpi-row-remarks { color: var(--s600); font-size: 11px; line-height: 1.45; margin-top: 4px; }
    .pd-kpi-row-side { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; text-align: right; }
    .pd-kpi-row-side strong { color: var(--crimson); font-size: 12px; white-space: nowrap; }
    .pd-kpi-open-link {
        grid-column: 1 / -1; width: max-content;
        display: inline-flex; align-items: center; gap: 5px;
        color: var(--crimson); text-decoration: none; font-size: 11px; font-weight: 800;
    }
    .pd-kpi-open-link:hover { text-decoration: underline; }
    .pd-kpi-empty { padding: 18px 10px; text-align: center; color: var(--s400); font-size: 12.5px; font-weight: 700; }

    .pd-card { background: var(--white); border: 1px solid var(--s200); border-radius: 15px; padding: 20px 22px; box-shadow: var(--sh-sm); }
    .pd-card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .pd-card-title { font-size: 15px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }

    .pd-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
    .pd-card-icon-badge {
        width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
        background: var(--crimson-mid); border: 1px solid var(--crimson-border);
        display: flex; align-items: center; justify-content: center;
    }
    .pd-card-icon-badge i { font-size: 18px; color: var(--crimson); }
    .pd-card-head-text { flex: 1; min-width: 0; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .pd-charts-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

    .table-wrap {
        border-radius: 12px; border: 1px solid var(--s200);
        overflow: auto; max-height: 62vh; background: var(--white);
        box-shadow: inset 0 1px 4px rgba(15,23,42,.04);
    }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th {
        position: sticky; top: 0; z-index: 5;
        background: var(--s50); border-bottom: 1px solid var(--s200);
        padding: 11px 16px;
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--s500); white-space: nowrap;
    }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: top; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; }
    tbody tr:hover { background: var(--crimson-mid); }

    .office-name { font-size: 13px; font-weight: 700; color: var(--s900); }
    .date-text   { font-size: 13px; font-weight: 600; color: var(--s500); }
    .amount-text { font-size: 13px; font-weight: 700; color: var(--s900); }

    .btn-review {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 14px; border-radius: 8px;
        border: 1px solid var(--crimson-border); background: #fff;
        font-size: 12px; font-weight: 700; color: var(--crimson);
        text-decoration: none; transition: background .15s, border-color .15s;
        font-family: 'Poppins', sans-serif;
    }
    .btn-review:hover { background: var(--crimson-mid); border-color: var(--crimson); }

    .badge { display: inline-flex; align-items: center; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; white-space: nowrap; line-height: 1.4; flex-shrink: 0; }
    .badge-pending   { background: var(--amber-bg); color: var(--amber); }
    .badge-endorsed  { background: var(--blue-bg);  color: var(--blue); }
    .badge-returned  { background: var(--red-bg);   color: var(--red); }
    .badge-approved  { background: var(--green-bg); color: var(--green); }
    .badge-default   { background: var(--s100); color: var(--s700); }

    .pd-chart-wrap { position: relative; width: 100%; }
    /* Office-budget chart grows with however many offices have active
       submissions — left uncapped, that stretched the WHOLE grid row (grid
       items stretch to the tallest sibling by default), leaving the other
       two charts with a lot of dead blank space underneath their own,
       much-shorter content. Capped + scrollable now, with an explicit
       expand toggle for when you actually want to see every office at once. */
    .pd-chart-wrap.collapsible { max-height: 220px; overflow-y: auto; transition: max-height .25s ease; }
    .pd-chart-wrap.collapsible.expanded { max-height: 2000px; }
    .pd-chart-expand-btn {
        display: inline-flex; align-items: center; gap: 4px;
        background: none; border: none; cursor: pointer;
        font-size: 11px; font-weight: 700; color: var(--crimson);
        font-family: 'Poppins', sans-serif; padding: 0; margin-left: auto;
    }
    .pd-chart-expand-btn:hover { text-decoration: underline; }
    .pd-card-head-row { display: flex; align-items: center; gap: 8px; }
    .pd-legend { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 10px; font-size: 12px; color: var(--s600); }
    .pd-legend-item { display: flex; align-items: center; gap: 6px; }
    .pd-legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

    @media (max-width: 1200px) { .pd-stat-grid { grid-template-columns: repeat(2,1fr); } .pd-charts-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) {
        .page-shell { padding: 16px 16px 40px; }
        .two-col { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .pd-stat-grid { grid-template-columns: 1fr; }
        .pd-kpi-popover { position: fixed; left: 16px; right: 16px; top: 96px; width: auto; max-width: none; max-height: calc(100vh - 128px); overflow: auto; }
        .pd-kpi-popover::before { display: none; }
        .pd-kpi-row { grid-template-columns: 1fr; }
        .pd-kpi-row-side { align-items: flex-start; text-align: left; }
    }
</style>
@endpush

@section('content')

<div class="page-shell">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-layout-dashboard"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Budget Office</p>
            <h1 class="page-hdr-title">Dashboard</h1>
            <p class="page-hdr-sub">Monitor proposal review workload, office submission status, and the campus-wide proposed budget.</p>
        </div>
    </div>

    @php
        $kpiCards = [
            [
                'key' => 'awaitingReview',
                'label' => 'Awaiting Review',
                'value' => number_format($summary['awaitingReview']),
                'hint' => 'Submitted proposals pending Budget action',
                'icon' => 'ti ti-clock',
            ],
            [
                'key' => 'endorsed',
                'label' => 'Endorsed',
                'value' => number_format($summary['endorsed']),
                'hint' => 'Forwarded for Chancellor approval',
                'icon' => 'ti ti-circle-check',
            ],
            [
                'key' => 'returned',
                'label' => 'Returned',
                'value' => number_format($summary['returned']),
                'hint' => 'Returned to offices with remarks',
                'icon' => 'ti ti-arrow-back-up',
            ],
            [
                'key' => 'totalCampusBudget',
                'label' => 'Total Proposed Budget',
                'value' => 'PHP ' . number_format($summary['totalCampusBudget']),
                'valueClass' => 'sm',
                'hint' => 'Campus-wide across active submissions',
                'icon' => 'ti ti-coin',
            ],
        ];
    @endphp
    <div class="pd-stat-grid">
        @foreach($kpiCards as $card)
            @php
                $detail = $kpiDetails[$card['key']] ?? ['title' => $card['label'], 'lead' => '', 'rows' => [], 'empty' => 'No records yet.', 'countLabel' => 'record(s)'];
                $rows = $detail['rows'] ?? [];
            @endphp
            <div class="pd-stat-wrap" tabindex="0" aria-describedby="kpi-{{ $card['key'] }}">
                <article class="pd-stat">
                    <div class="pd-stat-icon">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    <div class="pd-stat-label">{{ $card['label'] }}</div>
                    <div class="pd-stat-value {{ $card['valueClass'] ?? '' }}">{{ $card['value'] }}</div>
                    <div class="pd-stat-hint">{{ $card['hint'] }}</div>
                </article>

                <section class="pd-kpi-popover" id="kpi-{{ $card['key'] }}" aria-labelledby="kpi-title-{{ $card['key'] }}">
                    <div class="pd-kpi-head">
                        <div>
                            <p class="pd-kpi-eyebrow">Budget Office</p>
                            <h2 class="pd-kpi-title" id="kpi-title-{{ $card['key'] }}">{{ $detail['title'] }}</h2>
                            <p class="pd-kpi-lead">{{ $detail['lead'] }}</p>
                        </div>
                    </div>
                    <div class="pd-kpi-count">
                        <span>{{ number_format(count($rows)) }}</span>
                        <span>{{ $detail['countLabel'] ?? 'record(s)' }}</span>
                    </div>
                    <div class="pd-kpi-scroll">
                        @forelse($rows as $row)
                            <div class="pd-kpi-row">
                                <div class="pd-kpi-row-main">
                                    <div class="pd-kpi-row-title">{{ $row['title'] }}</div>
                                    <div class="pd-kpi-row-meta">
                                        {{ $row['office'] }} &middot; {{ $row['code'] }}
                                        @if($row['fiscalYear'])
                                            &middot; FY {{ $row['fiscalYear'] }}
                                        @endif
                                        &middot; {{ number_format($row['itemCount']) }} item(s)
                                        &middot; {{ $row['date'] }}
                                    </div>
                                    <div class="pd-kpi-row-remarks">{{ $row['remarks'] }}</div>
                                </div>
                                <div class="pd-kpi-row-side">
                                    <strong>PHP {{ number_format($row['amount']) }}</strong>
                                    <span class="badge {{ $row['statusClass'] }}">{{ $row['status'] }}</span>
                                </div>
                                <a class="pd-kpi-open-link" href="{{ $row['url'] }}" target="_blank" rel="noopener">
                                    View PPMP
                                    <i class="ti ti-arrow-right" style="font-size:12px"></i>
                                </a>
                            </div>
                        @empty
                            <div class="pd-kpi-empty">{{ $detail['empty'] }}</div>
                        @endforelse
                    </div>
                </section>
            </div>
        @endforeach
    </div>

    <div class="pd-charts-grid">
        <article class="pd-card">
            <div class="pd-card-head">
                <div class="pd-card-icon-badge"><i class="ti ti-chart-donut"></i></div>
                <div class="pd-card-head-text">
                    <p class="pd-card-eyebrow">Campus PPMP pipeline</p>
                    <h2 class="pd-card-title">Proposals by Status</h2>
                </div>
            </div>
            <div class="pd-chart-wrap" style="height:220px;">
                <canvas id="statusChart" data-statuses="{{ json_encode($proposalsByStatus) }}"></canvas>
            </div>
        </article>
        <article class="pd-card">
            <div class="pd-card-head">
                <div class="pd-card-icon-badge"><i class="ti ti-building"></i></div>
                <div class="pd-card-head-text">
                    <p class="pd-card-eyebrow">Active submissions</p>
                    <div class="pd-card-head-row">
                        <h2 class="pd-card-title">Proposed Budget by Office</h2>
                        <button type="button" class="pd-chart-expand-btn" id="officeBudgetExpandBtn" style="display:none;">
                            <i class="ti ti-arrows-vertical"></i><span>Expand</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="pd-chart-wrap collapsible" id="officeBudgetWrap">
                <canvas id="officeBudgetChart" data-offices="{{ json_encode($budgetByOffice) }}"></canvas>
            </div>
        </article>
        <article class="pd-card">
            <div class="pd-card-head">
                <div class="pd-card-icon-badge"><i class="ti ti-calendar-stats"></i></div>
                <div class="pd-card-head-text">
                    <p class="pd-card-eyebrow">This fiscal year</p>
                    <h2 class="pd-card-title">Monthly Review Activity</h2>
                </div>
            </div>
            <div class="pd-chart-wrap" style="height:220px;">
                <canvas id="activityChart" data-activity="{{ json_encode($monthlyReviewActivity) }}"></canvas>
            </div>
        </article>
    </div>

    <div class="two-col">

        <article class="pd-card">
            <div class="pd-card-head">
                <div class="pd-card-icon-badge"><i class="ti ti-list-details"></i></div>
                <div class="pd-card-head-text">
                    <p class="pd-card-eyebrow">Grouped by office</p>
                    <h2 class="pd-card-title">Proposals by Status</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Pending</th>
                            <th>Endorsed</th>
                            <th>Returned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($officeStatusGroups as $office)
                            <tr>
                                <td><span class="office-name">{{ $office['office'] }}</span></td>
                                <td>
                                    @if($office['pending'] > 0)
                                        <span class="badge badge-pending">{{ $office['pending'] }}</span>
                                    @else
                                        <span class="badge badge-default">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($office['endorsed'] > 0)
                                        <span class="badge badge-endorsed">{{ $office['endorsed'] }}</span>
                                    @else
                                        <span class="badge badge-default">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($office['returned'] > 0)
                                        <span class="badge badge-returned">{{ $office['returned'] }}</span>
                                    @else
                                        <span class="badge badge-default">0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;font-weight:600;">No proposals in the system yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="pd-card">
            <div class="pd-card-head">
                <div class="pd-card-icon-badge"><i class="ti ti-clipboard-list"></i></div>
                <div class="pd-card-head-text">
                    <p class="pd-card-eyebrow">Recent submissions</p>
                    <h2 class="pd-card-title">Review Queue</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Submitted</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentSubmissions as $submission)
                            <tr>
                                <td><span class="office-name">{{ $submission['office'] }}</span></td>
                                <td><span class="date-text">{{ $submission['submittedDate'] }}</span></td>
                                <td><span class="amount-text">PHP {{ number_format($submission['totalAmount']) }}</span></td>
                                <td>
                                    <a class="btn-review" href="{{ route('finance-office.proposal-review.show', ['proposal' => $submission['proposalId']]) }}">
                                        <i class="ti ti-eye" style="font-size:12px"></i>
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;font-weight:600;">No pending proposals at this time.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const statusEl = document.getElementById('statusChart');
    if (statusEl) {
        const statuses = JSON.parse(statusEl.dataset.statuses || '[]');
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: statuses.map(s => s.label),
                datasets: [{
                    data: statuses.map(s => s.count),
                    backgroundColor: ['#94a3b8', '#d97706', '#2563eb', '#dc2626', '#16a34a'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            },
        });
    }

    const officeEl = document.getElementById('officeBudgetChart');
    if (officeEl) {
        const offices = JSON.parse(officeEl.dataset.offices || '[]');
        // The canvas itself keeps growing with office count instead of
        // squeezing many bars into a fixed box — this campus has dozens of
        // offices — but the outer wrap caps/scrolls that at 220px (matching
        // the other two charts) until "Expand" is clicked, instead of
        // stretching the whole grid row's height.
        officeEl.parentElement.style.height = Math.max(230, offices.length * 34) + 'px';
        const expandBtn = document.getElementById('officeBudgetExpandBtn');
        if (expandBtn && offices.length * 34 > 220) {
            expandBtn.style.display = '';
            expandBtn.addEventListener('click', () => {
                const wrap = document.getElementById('officeBudgetWrap');
                const expanded = wrap.classList.toggle('expanded');
                expandBtn.querySelector('span').textContent = expanded ? 'Collapse' : 'Expand';
            });
        }
        new Chart(officeEl, {
            type: 'bar',
            data: {
                labels: offices.map(o => o.office),
                datasets: [{
                    label: 'Proposed Budget',
                    data: offices.map(o => o.total),
                    backgroundColor: '#681012',
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => 'PHP ' + Number(ctx.raw).toLocaleString() } },
                },
                scales: { x: { ticks: { callback: (v) => 'PHP ' + Number(v).toLocaleString(undefined, { notation: 'compact' }) } } },
            },
        });
    }

    const activityEl = document.getElementById('activityChart');
    if (activityEl) {
        const activity = JSON.parse(activityEl.dataset.activity || '{}');
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        new Chart(activityEl, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'Submitted', data: activity.submitted || [], backgroundColor: '#d97706', borderRadius: 3 },
                    { label: 'Endorsed', data: activity.endorsed || [], backgroundColor: '#2563eb', borderRadius: 3 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }
})();
</script>
@endpush
