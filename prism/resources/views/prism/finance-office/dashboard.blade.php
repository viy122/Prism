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

    .pd-stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 13px; }
    .pd-stat {
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 15px; padding: 18px 20px 16px;
        position: relative; overflow: hidden; box-shadow: var(--sh-sm);
        transition: box-shadow .25s, border-color .25s, transform .2s;
    }
    .pd-stat:hover { box-shadow: var(--sh-lg); border-color: var(--crimson-border); transform: translateY(-2px); }
    .pd-stat::before {
        content: ""; position: absolute; left: 0; top: 16px;
        width: 4px; height: 38px; background: var(--crimson); border-radius: 0 4px 4px 0;
    }
    .pd-stat-icon {
        position: absolute; right: 16px; top: 16px;
        width: 38px; height: 38px; border-radius: 11px;
        background: var(--crimson-mid);
        display: flex; align-items: center; justify-content: center;
    }
    .pd-stat-icon i { font-size: 19px; color: var(--crimson); }
    .pd-stat-label { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--s400); margin-bottom: 9px; }
    .pd-stat-value { font-size: 28px; font-weight: 800; color: var(--crimson); letter-spacing: -.7px; line-height: 1; margin-bottom: 5px; }
    .pd-stat-value.sm { font-size: 17px; letter-spacing: -.3px; }
    .pd-stat-hint { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

    .pd-card { background: var(--white); border: 1px solid var(--s200); border-radius: 15px; padding: 20px 22px; box-shadow: var(--sh-sm); }
    .pd-card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .pd-card-title { font-size: 15px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; margin-bottom: 16px; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

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
    .badge-default   { background: var(--s100); color: var(--s700); }

    .pd-chart-wrap { position: relative; width: 100%; }
    .pd-legend { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 10px; font-size: 12px; color: var(--s600); }
    .pd-legend-item { display: flex; align-items: center; gap: 6px; }
    .pd-legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

    @media (max-width: 1200px) { .pd-stat-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 1024px) {
        .page-shell { padding: 16px 16px 40px; }
        .two-col { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) { .pd-stat-grid { grid-template-columns: 1fr 1fr; } }
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

    <dl class="pd-stat-grid">
        <article class="pd-stat">
            <div class="pd-stat-icon">
                <i class="ti ti-clock"></i>
            </div>
            <div class="pd-stat-label">Awaiting Review</div>
            <div class="pd-stat-value">{{ number_format($summary['awaitingReview']) }}</div>
            <div class="pd-stat-hint">Submitted proposals pending Finance action</div>
        </article>
        <article class="pd-stat">
            <div class="pd-stat-icon">
                <i class="ti ti-circle-check"></i>
            </div>
            <div class="pd-stat-label">Endorsed This Month</div>
            <div class="pd-stat-value">{{ number_format($summary['endorsedThisMonth']) }}</div>
            <div class="pd-stat-hint">Forwarded for Chancellor approval</div>
        </article>
        <article class="pd-stat">
            <div class="pd-stat-icon">
                <i class="ti ti-arrow-back-up"></i>
            </div>
            <div class="pd-stat-label">Returned</div>
            <div class="pd-stat-value">{{ number_format($summary['returned']) }}</div>
            <div class="pd-stat-hint">Returned to offices with remarks</div>
        </article>
        <article class="pd-stat">
            <div class="pd-stat-icon">
                <i class="ti ti-coin"></i>
            </div>
            <div class="pd-stat-label">Total Proposed Budget</div>
            <div class="pd-stat-value sm">PHP {{ number_format($summary['totalCampusBudget']) }}</div>
            <div class="pd-stat-hint">Campus-wide across active submissions</div>
        </article>
    </dl>

    <div class="two-col">

        <article class="pd-card">
            <p class="pd-card-eyebrow">Grouped by office</p>
            <h2 class="pd-card-title">Proposals by Status</h2>
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
                                        <span style="font-size:13px;color:var(--s400);font-weight:600;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($office['endorsed'] > 0)
                                        <span class="badge badge-endorsed">{{ $office['endorsed'] }}</span>
                                    @else
                                        <span style="font-size:13px;color:var(--s400);font-weight:600;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($office['returned'] > 0)
                                        <span class="badge badge-returned">{{ $office['returned'] }}</span>
                                    @else
                                        <span style="font-size:13px;color:var(--s400);font-weight:600;">—</span>
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
            <p class="pd-card-eyebrow">Recent submissions</p>
            <h2 class="pd-card-title">Review Queue</h2>
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
