@extends('prism.layouts.office-head')
@section('title', 'Purchase Requests')

@php
    $prs             = collect($purchaseItems);
    $totalAmount     = $prs->sum('totalAmount');
    $pendingCount    = $prs->where('status', 'pending')->count();
    $inProgressCount = $prs->where('status', 'in_progress')->count();
    $approvedCount   = $prs->where('status', 'approved')->count();
    $completedCount  = $prs->where('status', 'completed')->count();
    $delayedCount    = $prs->where('status', 'delayed')->count();
@endphp

@push('page-css')
<style>
        :root {
            --m:     #681012;
            --m-dk:  #4e0c0e;
            --gold:  #c9a84c;
            --white: #ffffff;
            --s50:   #f8fafc;
            --s100:  #f1f5f9;
            --s200:  #e2e8f0;
            --s300:  #cbd5e1;
            --s400:  #94a3b8;
            --s500:  #64748b;
            --s600:  #475569;
            --s700:  #334155;
            --s900:  #0f172a;
            --sh:    0 2px 8px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.04);
            --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
            --sh-lg: 0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05);
        }

        .content { padding: 32px 32px 64px; flex: 1; display: flex; flex-direction: column; gap: 24px; }

        /* ─── Card shell ─── */
        .card {
            background: var(--white); border: 1px solid var(--s200);
            border-radius: 18px; padding: 26px; box-shadow: var(--sh);
        }
        .card-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
        .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
        .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; }
        .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 22px; flex-wrap: wrap; }

        /* ─── Stats bar ─── */
        .stats-bar { display: grid; grid-template-columns: repeat(4,1fr); gap: 13px; }
        .stat-box  {
            background: var(--white); border: 1px solid var(--s200);
            border-radius: 15px; padding: 18px 20px 16px;
            position: relative; overflow: hidden; box-shadow: var(--sh-sm);
            transition: box-shadow .25s, border-color .25s, transform .2s;
        }
        .stat-box:hover { box-shadow: var(--sh-lg); border-color: rgba(104,16,18,.2); transform: translateY(-2px); }
        .stat-box::before {
            content: ""; position: absolute; left: 0; top: 16px;
            width: 4px; height: 38px; background: var(--m); border-radius: 0 4px 4px 0;
        }
        .stat-icon {
            position: absolute; right: 16px; top: 16px;
            width: 38px; height: 38px; border-radius: 11px;
            background: rgba(104,16,18,.07);
            display: flex; align-items: center; justify-content: center;
        }
        .stat-icon svg { width: 19px; height: 19px; stroke: var(--m); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .stat-box dt { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--s400); margin-bottom: 9px; padding-right: 46px; }
        .stat-box dd { font-size: 28px; font-weight: 800; color: var(--m); letter-spacing: -.7px; line-height: 1; margin-bottom: 5px; }
        .stat-box dd.maroon { color: var(--m); }
        .stat-box dd.amber  { color: #92400e; }
        .stat-box dd.green  { color: #15803d; }
        .stat-box dd.sm     { font-size: 18px; letter-spacing: -.3px; }
        .stat-box small { display: block; font-size: 11.5px; font-weight: 500; color: var(--s400); line-height: 1.5; }
        .stat-box small.green { color: #15803d; }
        .stat-box small.amber { color: #92400e; }

        /* ─── 2-col ─── */
        .two-col   { display: grid; grid-template-columns: minmax(0,1fr) 340px; gap: 24px; align-items: start; }
        .col-sticky { position: sticky; top: 86px; }

        /* ─── Search ─── */
        .search-wrap { position: relative; }
        .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--s400); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
        .search-input { height: 40px; width: 100%; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 16px 0 36px; font-size: 13px; font-weight: 500; color: var(--s900); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
        .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
        .search-input::placeholder { color: var(--s400); }

        /* ─── PR list ─── */
        .pr-list { display: flex; flex-direction: column; gap: 10px; }

        /* ─── PR card ─── */
        .pr-card { border: 1px solid var(--s200); border-radius: 14px; background: var(--white); overflow: hidden; transition: box-shadow .15s; }
        .pr-card:hover { box-shadow: 0 4px 16px rgba(15,23,42,.08); }

        .pr-card-header {
            display: flex; align-items: center; gap: 14px;
            padding: 16px 20px; cursor: pointer;
            user-select: none;
        }
        .pr-card-header:hover { background: rgba(104,16,18,.02); }

        .pr-quarter-tag {
            width: 44px; height: 44px; border-radius: 11px; flex-shrink: 0;
            background: rgba(104,16,18,.08); border: 1px solid rgba(104,16,18,.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; color: var(--m); letter-spacing: -.5px;
        }
        .pr-quarter-tag.q1 { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .pr-quarter-tag.q2 { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .pr-quarter-tag.q3 { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .pr-quarter-tag.q4 { background: #fdf4ff; border-color: #e9d5ff; color: #7e22ce; }

        .pr-card-info { flex: 1; min-width: 0; }
        .pr-card-title { font-size: 14px; font-weight: 700; color: var(--s900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pr-card-meta  { font-size: 11.5px; color: var(--s400); margin-top: 3px; font-weight: 500; }

        .pr-card-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; flex-wrap: wrap; }

        .pr-chevron {
            width: 28px; height: 28px; border-radius: 8px;
            border: 1px solid var(--s200); background: var(--s50);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: transform .2s, background .15s;
            color: var(--s400); font-size: 14px;
        }
        .pr-card.open .pr-chevron { transform: rotate(180deg); background: var(--s100); }

        /* ─── Items panel ─── */
        .pr-items-panel { border-top: 1px solid var(--s100); background: var(--s50); display: none; }
        .pr-card.open .pr-items-panel { display: block; }

        .pr-items-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        .pr-items-table th {
            padding: 10px 16px; text-align: left;
            font-size: 9.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
            color: var(--s400); border-bottom: 1px solid var(--s200);
            background: var(--s50); white-space: nowrap;
        }
        .pr-items-table td { padding: 11px 16px; border-bottom: 1px solid var(--s200); color: var(--s600); vertical-align: middle; }
        .pr-items-table tbody tr:last-child td { border-bottom: none; }
        .pr-items-table tbody tr:hover td { background: rgba(104,16,18,.025); }
        .pr-items-table .item-name-cell { font-weight: 600; color: var(--s900); }
        .pr-items-table .num-cell       { font-weight: 700; color: var(--s700); text-align: right; }
        .pr-items-table .total-cell     { font-weight: 700; color: var(--m); text-align: right; }
        .pr-items-table tfoot td        { padding: 11px 16px; font-size: 12px; font-weight: 700; color: var(--s900); background: var(--s100); border-top: 1px solid var(--s200); text-align: right; }


        /* ─── Status badges ─── */
        .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .badge-pending   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-progress  { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-delayed   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-approved  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-completed { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-default   { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

        /* ─── Sidebar panels ─── */
        .side-panels  { display: flex; flex-direction: column; gap: 20px; }
        .health-row   { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--s100); }
        .health-row:last-child { border-bottom: none; padding-bottom: 0; }
        .health-label { font-size: 13px; font-weight: 600; color: var(--s600); }
        .checklist    { display: flex; flex-direction: column; gap: 12px; margin-top: 4px; }
        .check-item   { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: var(--s600); line-height: 1.55; }
        .check-item svg { width: 16px; height: 16px; stroke: #16a34a; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; margin-top: 2px; }
        .remark-item  { border-left: 3px solid var(--gold); padding-left: 12px; }
        .remark-name  { font-size: 13px; font-weight: 700; color: var(--s900); }
        .remark-text  { font-size: 12px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
        .remarks-list { display: flex; flex-direction: column; gap: 14px; margin-top: 4px; }

        /* ─── Responsive ─── */
        @media (max-width: 1280px) {
            .two-col   { grid-template-columns: minmax(0,1fr) 300px; }
            .stats-bar { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 1024px) {
            .content { padding: 20px 20px 48px; gap: 20px; }
            .two-col { grid-template-columns: 1fr; }
            .col-sticky { position: static; }
        }
        @media (max-width: 640px) {
            .stats-bar { grid-template-columns: 1fr 1fr; }
            .pr-card-header { flex-wrap: wrap; }
        }
</style>
@endpush

@section('content')
    <div class="content">

        {{-- Page header --}}
        <div class="card">
            <div class="card-head" style="margin-bottom:0;">
                <div>
                    <p class="card-eyebrow">Purchase Request Monitoring</p>
                    <h2 class="card-title">Purchase Requests</h2>
                    <p class="card-sub">Track the purchase requests submitted by your office and their procurement status.</p>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <dl class="stats-bar">
            <div class="stat-box">
                <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></div>
                <dt>Total PRs</dt>
                <dd>{{ $prs->count() }}</dd>
            </div>
            <div class="stat-box">
                <div class="stat-icon"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
                <dt>Total Amount</dt>
                <dd class="sm">₱ {{ number_format($totalAmount) }}</dd>
                
            </div>
            <div class="stat-box">
                <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                <dt>In Progress</dt>
                <dd class="maroon">{{ $inProgressCount }}</dd>
            </div>
            <div class="stat-box">
                <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                <dt>Completed</dt>
                <dd class="maroon">{{ $completedCount }}</dd>
            </div>
        </dl>

        {{-- 2-col: PR list + sidebar --}}
        <div class="two-col">

            {{-- Quarterly PR list --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Purchase Requests</p>
                        <h2 class="card-title">My Purchase Requests</h2>
                        <p class="card-sub">Purchase requests submitted by your office. PDF upload is handled by the Procurement Office.</p>
                    </div>
                </div>

                <div class="search-wrap" style="width:100%;margin-bottom:14px;">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="search" id="prSearch" placeholder="Search Purchase Request number or title">
                </div>

                <div class="pr-list" id="prList">
                    @forelse ($purchaseItems as $pr)
                    @php
                        $q      = strtolower($pr['quarter'] ?: 'pr');
                        $status = $pr['status'];
                        $badgeCls = match($status) {
                            'pending'     => 'badge-pending',
                            'in_progress' => 'badge-progress',
                            'approved'    => 'badge-approved',
                            'completed'   => 'badge-completed',
                            'delayed'     => 'badge-delayed',
                            default       => 'badge-default',
                        };
                        $trackingKey = $pr['trackingStatus']['key'] ?? null;
                        $trackingBadgeCls = match(true) {
                            $trackingKey === 'paid' => 'badge-completed',
                            in_array($trackingKey, ['pr:draft', 'awaiting_aoc', 'awaiting_po'], true) => 'badge-pending',
                            default => 'badge-progress',
                        };
                    @endphp
                    <div class="pr-card" data-search="{{ strtolower($pr['title'] . ' ' . $pr['number']) }}">
                        <div class="pr-card-header" onclick="this.closest('.pr-card').classList.toggle('open')">
                            <div class="pr-quarter-tag {{ $q }}">{{ $pr['quarter'] ?: 'PR' }}</div>
                            <div class="pr-card-info">
                                <p class="pr-card-title">{{ $pr['title'] }}</p>
                                <p class="pr-card-meta">
                                    {{ $pr['number'] }}
                                    &nbsp;•&nbsp; {{ $pr['itemCount'] }} {{ Str::plural('item', $pr['itemCount']) }}
                                    &nbsp;•&nbsp; PHP {{ number_format($pr['totalAmount']) }}
                                    @if($pr['uploadedAt'])
                                        &nbsp;•&nbsp; Uploaded {{ $pr['uploadedAt'] }}
                                    @endif
                                </p>
                            </div>
                            <div class="pr-card-right">
                                <span class="badge {{ $badgeCls }}">{{ $pr['statusLabel'] }}</span>
                                @if($trackingKey)
                                    <span class="badge {{ $trackingBadgeCls }}" title="Tracking Status">{{ $pr['trackingStatus']['label'] }}</span>
                                @endif
                                <i class="ti ti-chevron-down pr-chevron"></i>
                            </div>
                        </div>

                        {{-- Items list (expanded) --}}
                        <div class="pr-items-panel">
                            <table class="pr-items-table">
                                <thead>
                                    <tr>
                                        <th style="width:40%">Item Description</th>
                                        <th class="num-cell">Quantity</th>
                                        <th>Unit</th>
                                        <th class="num-cell">Unit Cost</th>
                                        <th class="num-cell">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pr['items'] as $item)
                                    <tr>
                                        <td class="item-name-cell">{{ $item['name'] }}</td>
                                        <td class="num-cell">{{ $item['quantity'] }}</td>
                                        <td>{{ $item['unit'] }}</td>
                                        <td class="num-cell">PHP {{ number_format($item['unitCost']) }}</td>
                                        <td class="total-cell">PHP {{ number_format($item['totalCost']) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" style="text-align:right;font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--s400);">Total Amount</td>
                                        <td class="total-cell" style="font-size:14px;">PHP {{ number_format($pr['totalAmount']) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            @if($pr['remarks'])
                            <p style="padding:10px 16px 12px;font-size:12px;color:var(--s500);border-top:1px solid var(--s200);">
                                <strong style="color:var(--s700);">Remarks:</strong> {{ $pr['remarks'] }}
                            </p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="padding:40px;text-align:center;color:var(--s400);font-size:13px;font-weight:600;">
                        No purchase requests found for your office.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-sticky side-panels">

                {{-- Queue Health --}}
                <div class="card">
                    <p class="card-eyebrow">Processing snapshot</p>
                    <h2 class="card-title" style="margin-bottom:18px;">Queue Health</h2>
                    <div>
                        <div class="health-row">
                            <span class="health-label">Pending</span>
                            <span class="badge badge-pending">{{ $pendingCount }}</span>
                        </div>
                        <div class="health-row">
                            <span class="health-label">In Progress</span>
                            <span class="badge badge-progress">{{ $inProgressCount }}</span>
                        </div>
                        <div class="health-row">
                            <span class="health-label">Approved</span>
                            <span class="badge badge-approved">{{ $approvedCount }}</span>
                        </div>
                        <div class="health-row">
                            <span class="health-label">Delayed</span>
                            <span class="badge badge-delayed">{{ $delayedCount }}</span>
                        </div>
                    </div>
                </div>

                {{-- Recent Remarks --}}
                <div class="card">
                    <p class="card-eyebrow">Recent remarks</p>
                    <h2 class="card-title" style="margin-bottom:16px;">Procurement Notes</h2>
                    <div class="remarks-list">
                        @foreach ($prs->whereNotNull('remarks')->where('remarks', '!=', '')->take(3) as $pr)
                            <div class="remark-item">
                                <p class="remark-name">{{ $pr['title'] }}</p>
                                <p class="remark-text">{{ $pr['remarks'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── toast ── */
    function toast(msg, type) {
        let el = document.getElementById('pr-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'pr-toast';
            el.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:700;color:#fff;opacity:0;transition:opacity .3s;pointer-events:none;font-family:Poppins,sans-serif;max-width:320px;line-height:1.4;';
            document.body.appendChild(el);
        }
        el.textContent = msg;
        el.style.background = type === 'error' ? '#991B1B' : '#166534';
        el.style.opacity = '1';
        clearTimeout(el._t);
        el._t = setTimeout(() => { el.style.opacity = '0'; }, 3500);
    }

    /* ── badge updater ── */
    function updateBadge(number, statusLabel) {
        const badge = document.querySelector(`[data-pr-status="${number}"]`);
        if (!badge) return;
        const map = {
            'pending':     'badge-pending',
            'in progress': 'badge-progress',
            'in_progress': 'badge-progress',
            'approved':    'badge-approved',
            'completed':   'badge-completed',
            'delayed':     'badge-delayed',
        };
        badge.className = 'badge ' + (map[statusLabel.toLowerCase()] ?? 'badge-default');
        badge.textContent = statusLabel.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    /* ── search ── */
    document.getElementById('prSearch')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#prList .pr-card').forEach(function (card) {
            card.style.display = q && !card.dataset.search?.includes(q) ? 'none' : '';
        });
    });
})();
</script>
@endpush