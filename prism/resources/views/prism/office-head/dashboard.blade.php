@extends('prism.layouts.office-head')
@section('title', 'Dashboard')

@push('head-extras')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
@endpush

@push('page-css')
<style>
        /* ═══════════════════════════════════════
           DASHBOARD CONTENT
        ═══════════════════════════════════════ */
        .dash {
            padding: 28px 32px 56px;
            flex: 1;

            --m:      #681012;
            --m-dark: #4e0c0e;
            --white:  #ffffff;
            --s50:    #f8fafc;
            --s100:   #f1f5f9;
            --s200:   #e2e8f0;
            --s400:   #94a3b8;
            --s500:   #64748b;
            --s600:   #475569;
            --s700:   #334155;
            --s900:   #0f172a;
            --sh-sm:  0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
            --sh-md:  0 4px 16px rgba(15,23,42,.08), 0 1px 4px rgba(15,23,42,.04);
            --sh-lg:  0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05);
        }

        /* Page header card */
        .pd-header {
            background: var(--white); border: 1px solid var(--border2);
            border-radius: 18px; padding: 22px 26px; margin-bottom: 20px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap; box-shadow: var(--sh);
        }
        .pd-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
        .pd-header h1 { font-size: 26px; font-weight: 800; color: var(--s900); letter-spacing: -.5px; margin-bottom: 5px; line-height: 1.15; }
        .pd-header-sub { font-size: 13px; color: var(--s600); line-height: 1.65; max-width: 500px; }
        .pd-header-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

        .pd-year-filter {
            position: relative; display: flex; align-items: center; gap: 8px;
            height: 44px; background: var(--white); border: 1.5px solid var(--s200);
            border-radius: 11px; padding: 0 12px 0 7px;
            transition: border-color .15s, box-shadow .15s;
        }
        .pd-year-filter:hover, .pd-year-filter:focus-within { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
        .pd-year-icon {
            width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
            background: rgba(104,16,18,.08); display: flex; align-items: center; justify-content: center;
        }
        .pd-year-icon svg { width: 14px; height: 14px; stroke: var(--m); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .pd-year-body { display: flex; flex-direction: column; line-height: 1.2; }
        .pd-year-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); }
        .pd-year-value { font-size: 12.5px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
        .pd-year-chevron { width: 13px; height: 13px; stroke: var(--s500); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; margin-left: 2px; }
        .pd-year-filter form { position: absolute; inset: 0; }
        .pd-year-filter select {
            position: absolute; inset: 0; width: 100%; height: 100%;
            opacity: 0; border: none; cursor: pointer; font-family: 'Poppins', sans-serif;
        }

        .pd-btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            height: 44px; background: var(--m); color: #fff;
            padding: 0 18px; border-radius: 11px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 10px rgba(104,16,18,.22);
            transition: background .2s, transform .15s;
        }
        .pd-btn-primary:hover { background: var(--m-dark); transform: translateY(-1px); }
        .pd-btn-primary svg { width: 15px; height: 15px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        .pd-btn-outline {
            display: inline-flex; align-items: center; gap: 8px;
            height: 44px; background: var(--white); color: var(--m);
            border: 1.5px solid rgba(104,16,18,.35);
            padding: 0 18px; border-radius: 11px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: border-color .2s, background .2s, transform .15s;
        }
        .pd-btn-outline:hover { border-color: var(--m); background: rgba(104,16,18,.04); transform: translateY(-1px); }
        .pd-btn-outline svg { width: 15px; height: 15px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* Stat cards */
        .pd-stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 13px; margin-bottom: 20px; }
        .pd-stat {
            background: var(--white); border: 1px solid var(--s200);
            border-radius: 15px; padding: 18px 20px 16px;
            position: relative; overflow: hidden; box-shadow: var(--sh-sm);
            transition: box-shadow .25s, border-color .25s, transform .2s;
        }
        .pd-stat:hover { box-shadow: var(--sh-lg); border-color: rgba(104,16,18,.2); transform: translateY(-2px); }
        .pd-stat::before {
            content: ""; position: absolute; left: 0; top: 16px;
            width: 4px; height: 38px; background: var(--m); border-radius: 0 4px 4px 0;
        }
        .pd-stat-icon {
            position: absolute; right: 16px; top: 16px;
            width: 38px; height: 38px; border-radius: 11px;
            background: rgba(104,16,18,.07);
            display: flex; align-items: center; justify-content: center;
        }
        .pd-stat-icon svg { width: 19px; height: 19px; stroke: var(--m); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .pd-stat-label { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--s400); margin-bottom: 9px; }
        .pd-stat-value { font-size: 28px; font-weight: 800; color: var(--m); letter-spacing: -.7px; line-height: 1; margin-bottom: 5px; }
        .pd-stat-value.sm { font-size: 18px; letter-spacing: -.3px; }
        .pd-stat-hint { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

        /* Section card */
        .pd-card { background: var(--white); border: 1px solid var(--s200); border-radius: 15px; padding: 20px 22px; box-shadow: var(--sh-sm); }
        .pd-card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 3px; }
        .pd-card-title { font-size: 15px; font-weight: 800; color: var(--s900); margin: 0 0 16px; letter-spacing: -.2px; }
        .pd-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
        .pd-card-head .pd-card-title { margin-bottom: 0; }

        /* Badges */
        .pd-badge { display: inline-flex; align-items: center; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; white-space: nowrap; line-height: 1.4; flex-shrink: 0; }
        .pd-badge-approved  { background: #dcfce7; color: #166534; }
        .pd-badge-pending   { background: #fef3c7; color: #92400e; }
        .pd-badge-returned  { background: #fee2e2; color: #991b1b; }
        .pd-badge-submitted { background: #dbeafe; color: #1e40af; }
        .pd-badge-info      { background: #e0f2fe; color: #0369a1; }
        .pd-badge-progress  { background: #ede9fe; color: #4c1d95; }
        .pd-badge-finance   { background: #f0fdf4; color: #166534; }

        /* Main 2-col */
        .pd-main-grid { display: grid; grid-template-columns: 1fr; gap: 15px; margin-top: 15px; margin-bottom: 15px; }

        /* Progress */
        .pd-prog-nums { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; margin-bottom: 13px; }
        .pd-prog-box { background: var(--s50); border: 1px solid var(--s200); border-radius: 11px; padding: 13px 15px; }
        .pd-prog-box strong { display: block; font-size: 26px; font-weight: 800; color: var(--m); letter-spacing: -.5px; line-height: 1; }
        .pd-prog-box span   { display: block; font-size: 12px; font-weight: 600; color: var(--s400); margin-top: 3px; }
        .pd-prog-bar-wrap   { height: 9px; background: var(--s100); border-radius: 99px; overflow: hidden; margin-bottom: 11px; }
        .pd-prog-bar        { height: 100%; background: var(--m); border-radius: 99px; }
        .pd-prog-note       { background: rgba(104,16,18,.05); border-radius: 9px; padding: 9px 13px; font-size: 12px; color: var(--s600); line-height: 1.6; margin-bottom: 14px; }

        /* Updates */
        .pd-updates { display: flex; flex-direction: column; gap: 7px; }
        .pd-update {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 12px; background: var(--s50); border: 1px solid var(--s200);
            border-radius: 11px; padding: 12px 14px;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .pd-update:hover { border-color: rgba(104,16,18,.18); background: var(--white); box-shadow: var(--sh-sm); }
        .pd-update-title  { font-size: 13px; font-weight: 700; color: var(--s900); margin-bottom: 2px; }
        .pd-update-detail { font-size: 12px; color: var(--s600); line-height: 1.55; }
        .pd-update-meta   { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
        .pd-update-time   { font-size: 11px; color: var(--s400); white-space: nowrap; }

        /* Recent Activity table card */
        .pd-activity-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .pd-activity-heading { display: flex; align-items: flex-start; gap: 12px; }
        .pd-activity-icon {
            width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
            background: rgba(104,16,18,.08); display: flex; align-items: center; justify-content: center;
        }
        .pd-activity-icon svg { width: 17px; height: 17px; stroke: var(--m); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .pd-activity-title { font-size: 15px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; margin-bottom: 2px; }
        .pd-activity-sub   { font-size: 12px; color: var(--s500); }
        .pd-activity-link  { display: inline-flex; align-items: center; gap: 5px; font-size: 12.5px; font-weight: 700; color: var(--m); text-decoration: none; white-space: nowrap; flex-shrink: 0; margin-top: 2px; }
        .pd-activity-link:hover { text-decoration: underline; }
        .pd-activity-link svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }

        .pd-activity-table-wrap { overflow-x: auto; }
        .pd-activity-table { width: 100%; border-collapse: collapse; min-width: 640px; }
        .pd-activity-table thead th {
            text-align: left; font-size: 10.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            color: var(--s400); padding: 0 10px 10px; border-bottom: 1px solid var(--s200);
        }
        .pd-activity-table tbody td { padding: 13px 10px; border-bottom: 1px solid var(--s100); vertical-align: top; }
        .pd-activity-table tbody tr:last-child td { border-bottom: none; }
        .pd-activity-row-el { transition: background .15s; cursor: pointer; }
        .pd-activity-row-el:hover { background: var(--s50); }
        .pd-activity-row-el td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
        .pd-activity-row-el td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }
        .pd-activity-datetime { font-size: 12px; color: var(--s500); white-space: nowrap; }
        .pd-activity-ref { font-size: 13px; font-weight: 700; color: var(--s900); }
        .pd-activity-remarks { font-size: 12.5px; color: var(--s600); line-height: 1.5; max-width: 320px; }
        .pd-activity-chev { color: var(--s400); text-align: right; }

        /* Bottom 3-col */
        .pd-bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        /* Both cards stretch to the same row height, and their chart fills
           the extra space instead of leaving a gap below a shorter card's
           legend/content when its sibling is taller. */
        .pd-bottom-grid > .pd-card { display: flex; flex-direction: column; }

        /* Extra charts row */
        .pd-charts-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px; margin-bottom: 15px; }

        /* Chart card icon header — reused on every chart card for a
           consistent icon + eyebrow + title look. */
        .pd-chart-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .pd-chart-heading { display: flex; align-items: flex-start; gap: 10px; }
        .pd-chart-icon {
            width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0;
            background: rgba(104,16,18,.08); display: flex; align-items: center; justify-content: center;
        }
        .pd-chart-icon svg { width: 16px; height: 16px; stroke: var(--m); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .pd-chart-title { font-size: 14px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; margin-bottom: 2px; }

        /* Legend */
        .pd-legend { display: flex; flex-wrap: wrap; gap: 13px; margin-bottom: 10px; font-size: 12px; color: var(--s600); }
        .pd-legend-item { display: flex; align-items: center; gap: 6px; }
        .pd-legend-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

        /* Chart */
        .pd-chart-wrap { position: relative; width: 100%; }

        /* Always-visible chart legend (labels shouldn't require a hover to
           read) — kept compact so it doesn't get crowded with many slices. */
        .pd-chart-legend { display: flex; flex-wrap: wrap; gap: 6px 12px; margin-top: 10px; }
        .pd-chart-legend-center { justify-content: center; }
        .pd-chart-legend-item { display: flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 600; color: var(--s600); white-space: nowrap; }
        .pd-chart-legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }

        /* Mobile */
        @media (max-width: 1024px) {
            .dash { padding: 16px 16px 40px; }
            .pd-stat-grid { grid-template-columns: repeat(2,1fr); }
            .pd-main-grid { grid-template-columns: 1fr; }
            .pd-bottom-grid { grid-template-columns: 1fr 1fr; }
            .pd-charts-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .pd-stat-grid { grid-template-columns: 1fr; }
            .pd-bottom-grid { grid-template-columns: 1fr; }
            .pd-charts-grid { grid-template-columns: 1fr; }
        }
</style>
@endpush

@section('content')
        <div class="dash">

            {{-- Page header card --}}
            <div class="pd-header">
                <div>
                    <p class="pd-eyebrow">Office Head / Dean</p>
                    <h1>Dashboard</h1>
                    <p class="pd-header-sub">Track proposed budgets, approvals, procurement movement, and PR readiness for your office.</p>
                </div>
                <div class="pd-header-actions">
                    <div class="pd-year-filter">
                        <div class="pd-year-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <div class="pd-year-body">
                            <span class="pd-year-label">Year</span>
                            <span class="pd-year-value">{{ $selectedYear ? 'FY ' . $selectedYear : 'Overall' }}</span>
                        </div>
                        <svg class="pd-year-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        <form method="GET" action="{{ route('office-head.dashboard') }}">
                            <select name="year" id="pdYearSelect" onchange="this.form.submit()" aria-label="Filter dashboard by fiscal year">
                                <option value="all" {{ is_null($selectedYear) ? 'selected' : '' }}>Overall</option>
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>FY {{ $y }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <a href="{{ route('office-head.budget-proposal') }}" class="pd-btn-primary">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        New PPMP
                    </a>
                    <a href="{{ route('office-head.purchase-requests') }}" class="pd-btn-outline">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        View PRs
                    </a>
                </div>
            </div>

            {{-- Stat cards --}}
            <div class="pd-stat-grid">
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></div>
                    <div class="pd-stat-label">Total Proposed Items</div>
                    <div class="pd-stat-value">{{ number_format($summary['totalProposedItems']) }}</div>
                    <div class="pd-stat-hint">Across all your office's PPMPs, any status</div>
                </article>
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
                    <div class="pd-stat-label">Total Proposed Budget</div>
                    <div class="pd-stat-value sm">₱ {{ number_format($summary['totalProposedBudget']) }}</div>
                    <div class="pd-stat-hint">Across all your office's PPMPs, any status</div>
                </article>
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                    <div class="pd-stat-label">Items Approved</div>
                    <div class="pd-stat-value">{{ number_format($summary['approvedItems']) }}</div>
                    <div class="pd-stat-hint">Eligible for PR preparation</div>
                </article>
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                    <div class="pd-stat-label">Pending Approval</div>
                    <div class="pd-stat-value">{{ number_format($summary['pendingItems']) }}</div>
                    <div class="pd-stat-hint">Under Budget or Chancellor review</div>
                </article>
            </div>

            {{-- Extra charts row --}}
            <div class="pd-charts-grid">

                {{-- PR pipeline funnel --}}
                <article class="pd-card">
                    <div class="pd-chart-head">
                        <div class="pd-chart-heading">
                            <div class="pd-chart-icon">
                                <svg viewBox="0 0 24 24"><path d="M4 4h16l-6.5 8.5v6l-3 2v-8L4 4z"/></svg>
                            </div>
                            <div>
                                <p class="pd-card-eyebrow" style="margin-bottom:2px;">Pipeline</p>
                                <div class="pd-chart-title">PR → AOC → PO → Payment</div>
                            </div>
                        </div>
                    </div>
                    @if($summary['funnelStages']['halted'] > 0)
                        <p style="font-size:11px;color:var(--s400);margin:-6px 0 10px;">{{ $summary['funnelStages']['halted'] }} cancelled/denied not shown</p>
                    @endif
                    <div class="pd-chart-wrap" style="height:196px;">
                        <canvas id="funnelChart" role="img" aria-label="PR pipeline funnel chart"
                            data-stages="{{ json_encode($summary['funnelStages']['buckets']) }}">
                        </canvas>
                    </div>
                </article>

                {{-- Status breakdown --}}
                <article class="pd-card">
                    <div class="pd-chart-head">
                        <div class="pd-chart-heading">
                            <div class="pd-chart-icon">
                                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                            </div>
                            <div>
                                <p class="pd-card-eyebrow" style="margin-bottom:2px;">Proposal Breakdown</p>
                                <div class="pd-chart-title">Items by Status</div>
                            </div>
                        </div>
                    </div>
                    <div class="pd-chart-wrap" style="height:190px;">
                        <canvas id="statusChart" role="img" aria-label="Items by status pie chart"
                            data-approved="{{ $summary['approvedItems'] }}"
                            data-pending="{{ $summary['pendingItems'] }}"
                            data-returned="{{ $summary['returnedItems'] ?? 0 }}"
                            data-draft="{{ $summary['draftItems'] ?? 0 }}">
                        </canvas>
                    </div>
                    <div class="pd-chart-legend pd-chart-legend-center" id="statusChartLegend"></div>
                </article>

                {{-- Budget by quarter --}}
                <article class="pd-card">
                    <div class="pd-chart-head">
                        <div class="pd-chart-heading">
                            <div class="pd-chart-icon">
                                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <div>
                                <p class="pd-card-eyebrow" style="margin-bottom:2px;">PPMP Planning</p>
                                <div class="pd-chart-title">Budget by Quarter</div>
                            </div>
                        </div>
                    </div>
                    <div class="pd-chart-wrap" style="height:196px;">
                        <canvas id="quarterChart" role="img" aria-label="Planned budget by quarter bar chart"
                            data-quarters="{{ json_encode($summary['budgetByQuarter']) }}">
                        </canvas>
                    </div>
                    <div class="pd-legend" style="justify-content:center; margin-top:10px; margin-bottom:0;">
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#681012"></span>Planned Budget</span>
                    </div>
                </article>

            </div>

            {{-- Bottom 3-col --}}
            <div class="pd-bottom-grid">

                {{-- Monthly bar chart --}}
                <article class="pd-card">
                    <div class="pd-chart-head">
                        <div class="pd-chart-heading">
                            <div class="pd-chart-icon">
                                <svg viewBox="0 0 24 24"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/></svg>
                            </div>
                            <div>
                                <p class="pd-card-eyebrow" style="margin-bottom:2px;">Budget Overview</p>
                                <div class="pd-chart-title">Monthly Budget Utilization</div>
                            </div>
                        </div>
                    </div>
                    <div class="pd-chart-wrap" style="flex:1; min-height:220px;">
                        <canvas id="barChart" role="img" aria-label="Monthly budget utilization bar chart">Monthly budget data.</canvas>
                    </div>
                    <div class="pd-legend" style="justify-content:center; margin-top:10px; margin-bottom:0;">
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#681012"></span>Monthly Budget Spending</span>
                    </div>
                </article>

                {{-- PPMP category breakdown --}}
                <article class="pd-card">
                    <div class="pd-chart-head">
                        <div class="pd-chart-heading">
                            <div class="pd-chart-icon">
                                <svg viewBox="0 0 24 24"><line x1="6" y1="20" x2="6" y2="14"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="18" y1="20" x2="18" y2="10"/></svg>
                            </div>
                            <div>
                                <p class="pd-card-eyebrow" style="margin-bottom:2px;">Proposal Breakdown</p>
                                <div class="pd-chart-title">Spend by Category</div>
                            </div>
                        </div>
                    </div>
                    @php
                        $categoryCount = count($summary['categoryBreakdown'] ?? []);
                        $categoryChartHeight = max(150, min(260, $categoryCount * 34 + 20));
                    @endphp
                    <div class="pd-chart-wrap" style="flex:1; min-height:{{ $categoryChartHeight }}px;">
                        <canvas id="categoryChart" role="img" aria-label="Spend by category horizontal bar chart"
                            data-categories="{{ json_encode($summary['categoryBreakdown']) }}">
                        </canvas>
                    </div>
                </article>

            </div>

            {{-- Main 2-col --}}
            <div class="pd-main-grid">

                {{-- Recent activity --}}
                <article class="pd-card">
                    <div class="pd-activity-head">
                        <div class="pd-activity-heading">
                            <div class="pd-activity-icon">
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div>
                                <div class="pd-activity-title">Recent Activity</div>
                                <div class="pd-activity-sub">Latest updates from your office's PPMPs and PRs</div>
                            </div>
                        </div>
                        <a class="pd-activity-link" href="{{ route('office-head.my-proposals') }}">
                            View all activity
                            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                    <div class="pd-activity-table-wrap">
                        <table class="pd-activity-table">
                            <thead>
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>Reference</th>
                                    <th>Action / Status</th>
                                    <th>Remarks</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentActivity as $activity)
                                    @php
                                        $statusLower = strtolower($activity['status']);
                                        $sc = match(true) {
                                            str_contains($statusLower, 'approved')
                                                || str_contains($statusLower, 'fully signed')
                                                || str_contains($statusLower, 'delivered')
                                                || str_contains($statusLower, 'complete')
                                                || str_contains($statusLower, 'paid')      => 'pd-badge-approved',
                                            str_contains($statusLower, 'denied')
                                                || str_contains($statusLower, 'cancelled')
                                                || str_contains($statusLower, 'returned')  => 'pd-badge-returned',
                                            str_contains($statusLower, 'for signature')
                                                || str_contains($statusLower, 'pending')
                                                || str_contains($statusLower, 'awaiting')  => 'pd-badge-pending',
                                            str_contains($statusLower, 'submitted')        => 'pd-badge-submitted',
                                            str_contains($statusLower, 'canvass')
                                                || str_contains($statusLower, 'forwarded')
                                                || str_contains($statusLower, 'progress')  => 'pd-badge-progress',
                                            default                                        => 'pd-badge-info',
                                        };
                                    @endphp
                                    <tr class="pd-activity-row-el" onclick="window.location='{{ $activity['href'] }}'">
                                        <td class="pd-activity-datetime">{{ $activity['time'] }}</td>
                                        <td class="pd-activity-ref">{{ $activity['reference'] }}</td>
                                        <td><span class="pd-badge {{ $sc }}">{{ $activity['status'] }}</span></td>
                                        <td class="pd-activity-remarks">{{ $activity['remarks'] }}</td>
                                        <td class="pd-activity-chev">
                                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center;padding:24px 0;color:#94a3b8;font-size:13px;">No recent activity.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

            </div>
        </div>{{-- /dash --}}
@endsection

@push('scripts')
<script>
(function () {
    const pp = "'Poppins', sans-serif";
    Chart.defaults.font.family = pp;

    /* LINE — Monthly */
    const bEl = document.getElementById('barChart');
    if (bEl) {
        new Chart(bEl, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Budget Used (₱)',
                    data: {!! json_encode($summary['monthlyBudgetUsage'] ?? array_fill(0,12,0)) !!},
                    borderColor: '#681012', backgroundColor: 'rgba(104,16,18,.10)',
                    fill: true, tension: .35, borderWidth: 2.5,
                    pointRadius: 3, pointBackgroundColor: '#681012', pointBorderColor: '#fff', pointBorderWidth: 1.5
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: pp } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10, family: pp }, callback: v => v>=1000?'₱ '+Math.round(v/1000)+'k':'₱ '+v } }
                }
            }
        });
    }

    /* PIE — Items by status */
    const sEl = document.getElementById('statusChart');
    if (sEl) {
        const sLabels = ['Approved','Pending','Returned','Draft'];
        const sValues = [parseInt(sEl.dataset.approved||0),parseInt(sEl.dataset.pending||0),parseInt(sEl.dataset.returned||0),parseInt(sEl.dataset.draft||0)];
        const sColors = ['#16a34a','#e3a53d','#df5b53','#cbd5e1'];
        const sTotal  = sValues.reduce((a, b) => a + b, 0);

        // Always-visible percentage labels on each slice (not just on hover) —
        // the tooltip below is left untouched so hovering still shows the
        // exact label/value.
        const sliceLabelPlugin = {
            id: 'statusSliceLabel',
            afterDraw(chart) {
                if (!sTotal) return;
                const { ctx } = chart;
                chart.getDatasetMeta(0).data.forEach((arc, i) => {
                    const val = sValues[i];
                    if (!val) return;
                    const pct = val / sTotal;
                    if (pct < 0.04) return;
                    const angle  = (arc.startAngle + arc.endAngle) / 2;
                    const radius = arc.outerRadius * 0.65;
                    const x = arc.x + Math.cos(angle) * radius;
                    const y = arc.y + Math.sin(angle) * radius;
                    ctx.save();
                    ctx.fillStyle = '#fff';
                    ctx.font = '700 11px ' + pp;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(Math.round(pct * 100) + '%', x, y);
                    ctx.restore();
                });
            }
        };

        new Chart(sEl, {
            type: 'pie',
            data: {
                labels: sLabels,
                datasets: [{
                    data: sValues,
                    backgroundColor: sColors,
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => '  ' + c.label + ': ' + c.parsed } }
                }
            },
            plugins: [sliceLabelPlugin]
        });

        const sLegendEl = document.getElementById('statusChartLegend');
        if (sLegendEl) {
            sLegendEl.innerHTML = sLabels.map((label, i) => `
                <span class="pd-chart-legend-item">
                    <span class="pd-chart-legend-dot" style="background:${sColors[i]}"></span>${label}
                </span>
            `).join('');
        }
    }

    /* HORIZONTAL BAR — Pipeline funnel */
    const fEl = document.getElementById('funnelChart');
    if (fEl) {
        const stages = JSON.parse(fEl.dataset.stages || '{}');
        new Chart(fEl, {
            type: 'bar',
            data: {
                labels: Object.keys(stages),
                datasets: [{
                    data: Object.values(stages),
                    backgroundColor: ['#94a3b8','#0369a1','#854f0b','#5b21b6','#166534'],
                    borderRadius: 6, borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10, family: pp }, precision: 0 } },
                    y: { grid: { display: false }, ticks: { color: '#334155', font: { size: 11, weight: '600', family: pp } } }
                }
            }
        });
    }

    /* HORIZONTAL BAR — Category breakdown */
    const cEl = document.getElementById('categoryChart');
    if (cEl) {
        const categories = JSON.parse(cEl.dataset.categories || '{}');
        const palette = ['#681012','#0369a1','#854f0b','#5b21b6','#166534','#991b1b','#334155','#c9a84c'];
        const labels = Object.keys(categories);
        const values = Object.values(categories);
        const colors = labels.map((_, i) => palette[i % palette.length]);
        new Chart(cEl, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderRadius: 6, borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => '  ₱ ' + Number(c.parsed.x).toLocaleString() } }
                },
                scales: {
                    x: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10, family: pp }, callback: v => v>=1000?'₱ '+Math.round(v/1000)+'k':'₱ '+v } },
                    y: { grid: { display: false }, ticks: { color: '#334155', font: { size: 11, weight: '600', family: pp } } }
                }
            }
        });
    }

    /* BAR — Budget by quarter */
    const qEl = document.getElementById('quarterChart');
    if (qEl) {
        new Chart(qEl, {
            type: 'bar',
            data: {
                labels: ['Q1','Q2','Q3','Q4'],
                datasets: [{
                    label: 'Planned Budget (₱)',
                    data: JSON.parse(qEl.dataset.quarters || '[0,0,0,0]'),
                    backgroundColor: '#681012', borderRadius: 6, borderSkipped: false
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: pp } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10, family: pp }, callback: v => v>=1000?'₱ '+Math.round(v/1000)+'k':'₱ '+v } }
                }
            }
        });
    }
})();
</script>
@endpush
