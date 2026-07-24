@extends('prism.layouts.app')
@section('title', 'Budget Utilization Report | Budget Office')

@push('page-css')
<style>
    :root {
        --s50:   #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
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

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card {
        position: relative; overflow: hidden;
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 18px; padding: 20px 20px 20px 24px;
        box-shadow: var(--sh-sm); transition: border-color .2s, box-shadow .2s, transform .2s;
    }
    .stat-card:hover { border-color: var(--crimson-border); box-shadow: var(--sh-lg); transform: translateY(-2px); }
    .stat-card::before { content: ''; position: absolute; left: 0; top: 20px; width: 4px; height: 40px; border-radius: 0 4px 4px 0; background: var(--crimson); }
    .stat-icon {
        position: absolute; right: 16px; top: 16px;
        width: 38px; height: 38px; border-radius: 11px;
        background: var(--crimson-mid); border: 1px solid var(--crimson-border);
        display: flex; align-items: center; justify-content: center;
    }
    .stat-icon svg { width: 19px; height: 19px; stroke: var(--crimson); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); }
    .stat-value { font-size: 1.55rem; font-weight: 800; color: var(--crimson); margin-top: 10px; display: block; letter-spacing: -.5px; line-height: 1.1; }
    .stat-desc  { font-size: 12px; color: var(--s500); margin-top: 8px; line-height: 1.6; }

    .filters-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .field-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 7px; display: block; }
    .field-select {
        height: 44px; width: 100%; border-radius: 10px;
        border: 1px solid var(--s300); background: var(--white);
        padding: 0 14px; font-size: 13.5px; font-weight: 500;
        color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .field-select:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 64vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 14px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--crimson-mid); }

    .amount-val { font-size: 13px; font-weight: 700; color: var(--s900); }

    .progress-wrap { min-width: 140px; }
    .progress-pct  { font-size: 13px; font-weight: 700; color: var(--s900); margin-bottom: 6px; }
    .progress-bar  { height: 8px; border-radius: 99px; background: var(--s100); border: 1px solid var(--s200); overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 99px; background: var(--crimson); transition: width .4s ease; }
    .progress-fill.low    { background: #e24b4a; }
    .progress-fill.medium { background: #ef9f27; }
    .progress-fill.high   { background: #639922; }

    .risk-badge { display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .risk-badge.at-risk  { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .risk-badge.on-track { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .risk-badge.moderate { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 1024px) { .page-shell { padding: 16px 16px 40px; } }
    @media (max-width: 640px) {
        .stats-grid { grid-template-columns: 1fr; }
        .filters-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="page-shell">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-report-money"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Budget Office</p>
            <h1 class="page-hdr-title">Budget Utilization Report</h1>
            <p class="page-hdr-sub">Track campus budget utilization, offices at risk, and office-level spending progress by quarter.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
            <p class="stat-label">Total campus budget</p>
            <strong class="stat-value">₱ {{ number_format($summary['campusBudget']) }}</strong>
            <p class="stat-desc">Approved allocation for monitored offices</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            <p class="stat-label">Total utilized</p>
            <strong class="stat-value">₱ {{ number_format($summary['totalUtilized']) }}</strong>
            <p class="stat-desc">Posted utilization across procurement activity</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
            <p class="stat-label">Overall utilization</p>
            <strong class="stat-value">{{ $summary['utilizationPercent'] }}%</strong>
            <p class="stat-desc">Campus-wide budget utilization percentage</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
            <p class="stat-label">Offices at risk</p>
            <strong class="stat-value">{{ $summary['officesAtRisk'] }}</strong>
            <p class="stat-desc">Low utilization or delayed procurement movement</p>
        </div>
    </div>

    <div class="card">
        <div class="filters-grid">
            <div>
                <label class="field-label" for="utilQuarterFilter">Quarter</label>
                <select class="field-select" id="utilQuarterFilter">
                    <option value="all">All quarters</option>
                    @foreach ($quarters as $quarter)
                        <option value="{{ $quarter }}">{{ $quarter }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="utilOfficeFilter">Office</label>
                <select class="field-select" id="utilOfficeFilter">
                    <option value="all">All offices</option>
                    @foreach ($offices as $office)
                        <option value="{{ $office }}">{{ $office }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Utilization per office</p>
                <h2 class="card-title">Office Spending Progress</h2>
            </div>
            <span class="count-chip" id="utilVisibleCount">{{ count($utilizationRows) }} shown</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Quarter</th>
                        <th>Budget</th>
                        <th>Utilized</th>
                        <th>Utilization</th>
                        <th>Risk</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($utilizationRows as $row)
                        @php
                            $pct = $row['percent'];
                            $fillClass = $pct >= 70 ? 'high' : ($pct >= 40 ? 'medium' : 'low');
                            $riskSlug = strtolower(str_replace(' ', '-', $row['risk']));
                        @endphp
                        <tr data-util-row data-office="{{ $row['office'] }}" data-quarter="{{ $row['quarter'] }}">
                            <td style="font-size:13px;font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $row['quarter'] }}</td>
                            <td><p class="amount-val">₱ {{ number_format($row['budget']) }}</p></td>
                            <td><p class="amount-val">₱ {{ number_format($row['utilized']) }}</p></td>
                            <td>
                                <div class="progress-wrap">
                                    <p class="progress-pct">{{ $pct }}%</p>
                                    <div class="progress-bar">
                                        <div class="progress-fill {{ $fillClass }}" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="risk-badge {{ $riskSlug }}">{{ $row['risk'] }}</span></td>
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
    const quarterEl = document.getElementById('utilQuarterFilter');
    const officeEl  = document.getElementById('utilOfficeFilter');
    const countEl   = document.getElementById('utilVisibleCount');
    const rows      = document.querySelectorAll('[data-util-row]');

    function applyFilters() {
        const quarter = quarterEl.value;
        const office  = officeEl.value;
        let visible   = 0;

        rows.forEach(row => {
            const match =
                (quarter === 'all' || row.dataset.quarter === quarter) &&
                (office  === 'all' || row.dataset.office  === office);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        countEl.textContent = visible + ' shown';
    }

    quarterEl.addEventListener('change', applyFilters);
    officeEl.addEventListener('change',  applyFilters);
})();
</script>
@endpush
