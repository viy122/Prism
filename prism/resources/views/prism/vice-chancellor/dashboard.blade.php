@extends('prism.layouts.app')
@section('title', 'Division Dashboard | Vice Chancellor')

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

    .office-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .office-pill { display: inline-flex; align-items: center; padding: 6px 16px; border-radius: 99px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); font-size: 13px; font-weight: 700; color: var(--m); }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); white-space: nowrap; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 52vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; }
    tbody tr:hover { background: var(--crimson-mid); }
    tbody tr.flagged { background: rgba(252,235,235,.5); }
    tbody tr.flagged:hover { background: rgba(252,235,235,.8); }

    .prog-wrap  { min-width: 130px; }
    .prog-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 6px; }
    .prog-track { height: 10px; border-radius: 99px; background: var(--s100); overflow: hidden; border: 1px solid var(--s200); }
    .prog-fill  { height: 100%; border-radius: 99px; background: var(--m); }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-on-track { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-at-risk  { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-delayed  { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-pending  { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-progress { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .two-col { grid-template-columns: 1fr; } }
    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-layout-dashboard"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Vice Chancellor</p>
            <h1 class="page-hdr-title">Division Dashboard</h1>
            <p class="page-hdr-sub">Monitor assigned division offices, APP item movement, utilization, delayed work, overdue items, and pending PRs.</p>
        </div>
    </div>

    @if(($awaitingSignature ?? 0) > 0)
    <div class="card" style="display:flex;align-items:center;gap:14px;border-color:#fac775;background:#fdf7ec;">
        <i class="ti ti-signature" style="font-size:24px;color:#854f0b;"></i>
        <div style="flex:1;">
            <p style="font-size:13px;font-weight:800;color:#854f0b;">{{ $awaitingSignature }} document{{ $awaitingSignature > 1 ? 's' : '' }} awaiting your signature</p>
            <p style="font-size:12px;color:#a16207;">Take a photo of the signed document to record your signature.</p>
        </div>
        <a href="{{ route('vice-chancellor.for-my-signature') }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;border-radius:9px;background:#854f0b;color:#fff;font-size:12px;font-weight:700;text-decoration:none;">Open Queue <i class="ti ti-arrow-right"></i></a>
    </div>
    @endif

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">{{ $divisionName }}</p>
                <h2 class="card-title">Offices Under the Vice Chancellor</h2>
            </div>
            <span class="count-chip">{{ count($offices) }} offices</span>
        </div>
        <div class="office-pills">
            @foreach ($offices as $office)
                <span class="office-pill">{{ $office }}</span>
            @endforeach
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
            </div>
            <p class="stat-label">Total APP Items</p>
            <strong class="stat-value">{{ number_format($summary['totalAppItems']) }}</strong>
            <p class="stat-desc">Items assigned to division offices</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <p class="stat-label">Procured Count</p>
            <strong class="stat-value">{{ number_format($summary['procuredCount']) }}</strong>
            <p class="stat-desc">Completed procurement items</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <p class="stat-label">Division Utilization</p>
            <strong class="stat-value">{{ $summary['divisionUtilization'] }}%</strong>
            <p class="stat-desc">Budget utilization across assigned offices</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <p class="stat-label">Flagged Offices</p>
            <strong class="stat-value">{{ collect($officeUtilization)->where('risk', '!=', 'On Track')->count() }}</strong>
            <p class="stat-desc">Offices with delayed or overdue items</p>
        </div>
    </div>

    <div class="two-col">

        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Utilization rate per office</p>
                    <h2 class="card-title">Division Utilization</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Utilization</th>
                            <th>Delayed</th>
                            <th>Overdue</th>
                            <th>Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($officeUtilization as $row)
                            @php
                                $riskClass = match(strtolower($row['risk'])) {
                                    'on track'          => 'badge-on-track',
                                    'at risk', 'medium' => 'badge-at-risk',
                                    default             => 'badge-delayed',
                                };
                                $flagged = strtolower($row['risk']) !== 'on track';
                            @endphp
                            <tr class="{{ $flagged ? 'flagged' : '' }}">
                                <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                                <td>
                                    <div class="prog-wrap">
                                        <p class="prog-label">{{ $row['utilization'] }}%</p>
                                        <div class="prog-track">
                                            <div class="prog-fill" style="width:{{ $row['utilization'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-weight:700;color:{{ $row['delayed'] > 0 ? '#a32d2d' : 'var(--s500)' }};">{{ $row['delayed'] }}</td>
                                <td style="font-weight:700;color:{{ $row['overdue'] > 0 ? '#a32d2d' : 'var(--s500)' }};">{{ $row['overdue'] }}</td>
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
                    <p class="card-eyebrow">Pending PR summary</p>
                    <h2 class="card-title">Division PR Queue</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Pending PRs</th>
                            <th>In Progress</th>
                            <th>Oldest Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingPrSummary as $row)
                            <tr>
                                <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                                <td><span class="badge badge-pending">{{ $row['pendingPrs'] }} pending</span></td>
                                <td><span class="badge badge-progress">{{ $row['inProgress'] }} in progress</span></td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $row['oldestPending'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
