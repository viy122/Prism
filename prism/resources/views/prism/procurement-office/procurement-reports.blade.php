@extends('prism.layouts.app')
@section('title', 'Procurement Reports | Procurement Office')

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
        --m: var(--crimson); --m-dk: var(--crimson-dark); --gold: #c9a84c; --white: #ffffff;
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 20px; border-radius: 10px; background: var(--m); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: background .2s; white-space: nowrap; }
    .btn-primary:hover { background: var(--m-dk); }
    .btn-primary svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

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

    .completion-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--crimson-mid); color: var(--crimson); border: 1px solid var(--crimson-border); }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 56vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; }
    tbody tr:hover { background: var(--crimson-mid); }

    .progress-wrap { display: flex; flex-direction: column; gap: 5px; min-width: 140px; }
    .progress-label { font-size: 13px; font-weight: 700; color: var(--s700); }
    .progress-track { height: 8px; border-radius: 99px; background: var(--s100); overflow: hidden; border: 1px solid var(--s200); }
    .progress-fill { height: 100%; border-radius: 99px; background: var(--m); transition: width .4s ease; }
    .progress-fill.high   { background: #3b6d11; }
    .progress-fill.medium { background: var(--m); }
    .progress-fill.low    { background: #a32d2d; }

    .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .delayed-list { display: flex; flex-direction: column; gap: 8px; }
    .delayed-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 12px; border: 1px solid #fecaca; background: #fff5f5; transition: background .15s, box-shadow .15s; }
    .delayed-item:hover { background: var(--white); box-shadow: var(--sh-sm); }
    .delayed-item-info { display: flex; flex-direction: column; gap: 3px; }
    .delayed-item-name { font-size: 13px; font-weight: 700; color: var(--s900); }
    .delayed-item-meta { font-size: 12px; color: var(--s500); }
    .delayed-item-reason { font-size: 12px; color: var(--s600); margin-top: 4px; line-height: 1.5; }
    .badge-delayed { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; flex-shrink: 0; }

    /* PPMP validation flags */
    .badge-ok      { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-flag    { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-neutral { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .bottom-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }

    @media print {
        .btn-primary { display: none !important; }
        body { background: #fff !important; }
        .content { padding: 0 !important; }
        .card, .stat-card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        .table-wrap { max-height: none !important; overflow: visible !important; }
    }
</style>
@endpush

@section('content')

<div class="content">

    @php
        $totalTargeted     = collect($quarterlyRows)->sum('targeted');
        $totalProcured     = collect($quarterlyRows)->sum('procured');
        $averageCompletion = $totalTargeted > 0 ? round(($totalProcured / $totalTargeted) * 100) : 0;
    @endphp

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-chart-no-axes-combined"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Procurement Reports</h1>
            <p class="page-hdr-sub">Review quarterly accomplishment, completed purchases, and delayed items with remarks.</p>
        </div>
        <button class="btn-primary" type="button" id="printReportBtn">
            <svg viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Export / Print
        </button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <p class="stat-label">Items Targeted</p>
            <strong class="stat-value">{{ number_format($totalTargeted) }}</strong>
            <p class="stat-desc">Total procurement targets in the current report set</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <p class="stat-label">Items Procured</p>
            <strong class="stat-value">{{ number_format($totalProcured) }}</strong>
            <p class="stat-desc">Completed against quarterly targets</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <p class="stat-label">Completion Rate</p>
            <strong class="stat-value">{{ $averageCompletion }}%</strong>
            <p class="stat-desc">Overall targeted-versus-procured accomplishment</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <p class="stat-label">Delayed Items</p>
            <strong class="stat-value">{{ count($delayedItems) }}</strong>
            <p class="stat-desc">Items requiring procurement follow-up</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Quarterly Accomplishment</p>
                <h2 class="card-title">Items Targeted vs Procured</h2>
            </div>
            <span class="completion-chip">{{ $averageCompletion }}% completion</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Quarter</th>
                        <th>Targeted</th>
                        <th>Procured</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quarterlyRows as $row)
                        <tr>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $row['office'] }}</td>
                            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $row['quarter'] }}</td>
                            <td style="font-size:13px;font-weight:600;color:var(--s700);">{{ $row['targeted'] }}</td>
                            <td style="font-size:13px;font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                            <td>
                                @php
                                    $rate = $row['completionRate'];
                                    $fillClass = $rate >= 80 ? 'high' : ($rate >= 50 ? 'medium' : 'low');
                                @endphp
                                <div class="progress-wrap">
                                    <span class="progress-label">{{ $rate }}%</span>
                                    <div class="progress-track">
                                        <div class="progress-fill {{ $fillClass }}" style="width: {{ $rate }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bottom-grid">

        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Completed Purchases</p>
                    <h2 class="card-title">Completed This Quarter</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Item</th>
                            <th>PR No.</th>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($completedPurchases as $purchase)
                            <tr>
                                <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $purchase['office'] }}</td>
                                <td style="font-size:13px;color:var(--s900);font-weight:500;">{{ $purchase['item'] }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $purchase['prNumber'] }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $purchase['completedDate'] }}</td>
                                <td style="font-size:13px;font-weight:600;color:var(--s700);white-space:nowrap;">PHP {{ number_format($purchase['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Delayed Items</p>
                    <h2 class="card-title">Reasons and Remarks</h2>
                </div>
                <span style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:#fcebeb;color:#a32d2d;border:1px solid #f7c1c1;">
                    {{ count($delayedItems) }} items
                </span>
            </div>
            <div class="delayed-list">
                @foreach ($delayedItems as $item)
                    <div class="delayed-item">
                        <div class="delayed-item-info">
                            <span class="delayed-item-name">{{ $item['item'] }}</span>
                            <span class="delayed-item-meta">{{ $item['office'] }} &middot; {{ $item['prNumber'] }}</span>
                            <span class="delayed-item-reason">{{ $item['reason'] }}</span>
                        </div>
                        <span class="badge-delayed">Delayed</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    @php
        $flaggedCount = collect($ppmpValidationRows)->whereNotIn('flag', ['ok', 'pending'])->count();
    @endphp

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">PPMP Validation</p>
                <h2 class="card-title">Planned vs. Actually Purchased</h2>
            </div>
            <span class="badge-flag">{{ $flaggedCount }} flagged</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>PPMP Item</th>
                        <th>Planned Qty</th>
                        <th>Planned Amount</th>
                        <th>Matched PR Item</th>
                        <th>Purchased Qty</th>
                        <th>Purchased Amount</th>
                        <th>Tracking Status</th>
                        <th>Flag</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ppmpValidationRows as $row)
                        @php
                            $flagBadge = match ($row['flag']) {
                                'ok'      => ['badge-ok', 'OK'],
                                'pending' => ['badge-neutral', 'Not Yet Purchased'],
                                'qty_and_over_budget' => ['badge-flag', 'Qty & Over Budget'],
                                'qty_mismatch' => ['badge-flag', 'Qty Mismatch'],
                                'over_budget'  => ['badge-flag', 'Over Budget'],
                                default   => ['badge-neutral', '—'],
                            };
                        @endphp
                        <tr>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $row['office'] }}</td>
                            <td style="font-size:13px;color:var(--s900);font-weight:500;">{{ $row['item'] }}</td>
                            <td style="font-size:13px;color:var(--s700);white-space:nowrap;">{{ rtrim(rtrim(number_format($row['plannedQty'], 2), '0'), '.') }}</td>
                            <td style="font-size:13px;font-weight:600;color:var(--s700);white-space:nowrap;">PHP {{ number_format($row['plannedTotal']) }}</td>
                            <td style="font-size:13px;color:var(--s600);">{{ $row['matchedItem'] ?? '—' }}</td>
                            <td style="font-size:13px;color:var(--s700);white-space:nowrap;">{{ $row['purchasedQty'] !== null ? rtrim(rtrim(number_format($row['purchasedQty'], 2), '0'), '.') : '—' }}</td>
                            <td style="font-size:13px;font-weight:600;color:var(--s700);white-space:nowrap;">{{ $row['purchasedTotal'] !== null ? 'PHP ' . number_format($row['purchasedTotal']) : '—' }}</td>
                            <td style="font-size:12px;color:var(--s600);white-space:nowrap;">{{ $row['trackingStatus']['label'] }}</td>
                            <td><span class="{{ $flagBadge[0] }}">{{ $flagBadge[1] }}</span></td>
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
document.getElementById('printReportBtn').addEventListener('click', function () {
    window.print();
});
</script>
@endpush
