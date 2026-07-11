@extends('prism.layouts.app')
@section('title', 'BAC | PRISM')

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
        --m: var(--crimson);
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .stat-card { background: var(--s50); border: 1px solid var(--s200); border-radius: 14px; padding: 18px 20px; }
    .stat-card.action { border-color: #fac775; background: #fdf7ec; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .14em; color: var(--s500); margin-bottom: 6px; }
    .stat-value { font-size: 26px; font-weight: 900; color: var(--s900); letter-spacing: -.5px; }
    .stat-value.crimson { color: var(--m); }
    .stat-value.green { color: #3b6d11; }
    .stat-link { display: inline-flex; align-items: center; gap: 4px; margin-top: 8px; font-size: 11px; font-weight: 700; color: var(--m); text-decoration: none; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 140px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); padding: 28px; text-align: center; }
    .empty-state i { font-size: 36px; color: var(--s300); }
    .empty-state p { font-size: 13px; color: var(--s400); max-width: 260px; line-height: 1.6; }

    @media (max-width: 900px) { .stat-grid { grid-template-columns: 1fr; } .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-gavel"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Bids and Awards Committee</p>
            <h1 class="page-hdr-title">BAC Dashboard</h1>
            <p class="page-hdr-sub">Sign Abstracts of Canvass at the BAC Member, Vice Chairperson, and Chairperson stages.</p>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card action">
            <p class="stat-label">Awaiting My Signature</p>
            <p class="stat-value crimson">{{ $summary['awaitingMySignature'] }}</p>
            <a class="stat-link" href="{{ route('bac.for-my-signature') }}">Open queue <i class="ti ti-arrow-right"></i></a>
        </div>
        <div class="stat-card">
            <p class="stat-label">AOCs In BAC Stages</p>
            <p class="stat-value">{{ $summary['aocsInBacStages'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">AOCs Fully Signed</p>
            <p class="stat-value green">{{ $summary['aocsFullySigned'] }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Activity</p>
                <h2 class="card-title">Recent AOC Signature Activity</h2>
            </div>
        </div>

        @if(count($recentActivity) === 0)
            <div class="empty-state">
                <i class="ti ti-file-text"></i>
                <p>No AOC signature activity yet.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>AOC</th>
                        <th>Office</th>
                        <th>Action</th>
                        <th>By</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActivity as $log)
                    <tr>
                        <td style="font-weight:700;font-size:12px;color:var(--s500);white-space:nowrap;">{{ $log['code'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);">{{ $log['office'] }}</td>
                        <td>{{ $log['display'] }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $log['by'] }}</td>
                        <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $log['at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

@endsection
