<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Proposals | PRISM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0e9e9;
            min-height: 100vh;
            display: flex;
        }

        /* ═══════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════ */
        .sb {
            width: 272px;
            min-height: 100vh;
            background: #681012;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
            z-index: 50;
        }
        .sb-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 22px 18px 20px;
            text-decoration: none;
        }
        .sb-logo {
            width: 44px; height: 44px;
            border-radius: 10px;
            background: #fff;
            padding: 6px;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .sb-logo img { width: 100%; height: 100%; object-fit: contain; }
        .sb-brand-name {
            font-size: 18px;
            font-weight: 900;
            color: #fff;
            letter-spacing: .5px;
        }
        .sb-divider {
            height: 1px;
            background: rgba(255,255,255,.1);
            margin: 0 18px;
        }
        .sb-nav {
            padding: 14px 12px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            flex: 1;
        }
        .sb-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: rgba(255,255,255,.65);
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .sb-nav a:hover { background: rgba(255,255,255,.1); color: #fff; }
        .sb-nav a.active { background: #fff; color: #681012; font-weight: 700; }
        .sb-nav a svg {
            width: 18px; height: 18px;
            flex-shrink: 0;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }
        .sb-bottom {
            padding: 12px 12px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .sb-workspace { padding: 16px 18px 8px; }
        .sb-workspace-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255,255,255,.38);
            margin-bottom: 3px;
        }
        .sb-workspace-role { font-size: 13px; font-weight: 800; color: #fff; }
        .sb-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,.15);
            background: rgba(255,255,255,.1);
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            transition: background .2s, color .2s;
            font-family: 'Poppins', sans-serif;
        }
        .sb-logout:hover { background: #fff; color: #681012; }
        .sb-logout svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ═══════════════════════════════════════
           MAIN
        ═══════════════════════════════════════ */
        .main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            height: 66px;
            gap: 16px;
            flex-shrink: 0;
        }
        .topbar-title { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -.4px; }
        .topbar-chip {
            display: inline-flex;
            align-items: center;
            height: 34px;
            padding: 0 16px;
            border-radius: 8px;
            background: rgba(104,16,18,.07);
            border: 1px solid rgba(104,16,18,.14);
            font-size: 12px;
            font-weight: 700;
            color: #681012;
            white-space: nowrap;
        }

        /* ═══════════════════════════════════════
           CSS VARS
        ═══════════════════════════════════════ */
        :root {
            --m:     #681012;
            --m-dk:  #4e0c0e;
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
            --sh-md: 0 4px 20px rgba(15,23,42,.09), 0 1px 4px rgba(15,23,42,.04);
        }

        /* ═══════════════════════════════════════
           CONTENT
        ═══════════════════════════════════════ */
        .content {
            padding: 32px 32px 64px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ─── Card ─── */
        .card {
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 18px;
            padding: 26px;
            box-shadow: var(--sh);
        }
        .card-eyebrow {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--m);
            margin-bottom: 4px;
        }
        .card-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--s900);
            letter-spacing: -.2px;
        }
        .card-sub {
            font-size: 13px;
            color: var(--s500);
            margin-top: 4px;
        }
        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        /* ─── Stats bar ─── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .stat-box {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 14px;
            padding: 18px 20px;
        }
        .stat-box dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 6px;
        }
        .stat-box dd {
            font-size: 28px;
            font-weight: 800;
            color: var(--s900);
            line-height: 1;
        }
        .stat-box dd.maroon { color: var(--m); }
        .stat-box dd.sm { font-size: 17px; }

        /* ─── Filters row ─── */
        .filters-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 16px;
            align-items: end;
        }
        .field-group { display: flex; flex-direction: column; gap: 7px; }
        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--s700);
        }
        .field-select {
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--s300);
            background: var(--white);
            padding: 0 14px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--s900);
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-select:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.08);
        }

        /* ─── Buttons ─── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 20px;
            border-radius: 10px;
            background: var(--m);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(104,16,18,.2);
            transition: background .2s;
            white-space: nowrap;
        }
        .btn-primary:hover { background: var(--m-dk); }
        .btn-primary svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Pill ─── */
        .pill {
            display: inline-flex;
            align-items: center;
            height: 28px;
            padding: 0 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .pill-gray   { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
        .pill-maroon { background: rgba(104,16,18,.08); color: var(--m); border: 1px solid rgba(104,16,18,.15); }

        /* ─── 2-col layout ─── */
        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 400px;
            gap: 24px;
            align-items: start;
        }
        .col-sticky {
            position: sticky;
            top: 86px;
        }

        /* ─── Proposal queue ─── */
        .queue-scroll {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 68vh;
            overflow-y: auto;
            padding-right: 2px;
        }
        .proposal-row {
            border: 1px solid var(--s200);
            border-radius: 14px;
            background: var(--white);
            padding: 18px 20px;
            cursor: pointer;
            transition: border-color .15s, background .15s, box-shadow .15s;
            outline: none;
        }
        .proposal-row:hover {
            border-color: rgba(104,16,18,.28);
            background: rgba(104,16,18,.03);
            box-shadow: var(--sh);
        }
        .proposal-row:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.1);
        }
        .proposal-row-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .proposal-row-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--s900);
            line-height: 1.4;
        }
        .proposal-row-meta {
            font-size: 12px;
            font-weight: 600;
            color: var(--s500);
            margin-top: 3px;
        }
        .proposal-row-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .proposal-stat-item dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 4px;
        }
        .proposal-stat-item dd {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
        }
        .proposal-stat-item dd.danger { color: #991b1b; }

        /* ─── Timeline panel ─── */
        .timeline-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .timeline-meta-box {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .timeline-meta-box dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 5px;
        }
        .timeline-meta-box dd {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
        }

        /* ─── Timeline empty state ─── */
        .timeline-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 200px;
            border: 1.5px dashed var(--s300);
            border-radius: 14px;
            background: var(--s50);
            padding: 32px;
            text-align: center;
            color: var(--s500);
            font-size: 13px;
            line-height: 1.65;
        }
        .timeline-empty svg {
            width: 40px; height: 40px;
            stroke: rgba(104,16,18,.5); fill: none;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }

        /* ─── Responsive ─── */
        @media (max-width: 1280px) {
            .two-col { grid-template-columns: minmax(0,1fr) 340px; }
            .stats-bar { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            body { display: block; }
            .content { padding: 20px 20px 48px; gap: 20px; }
            .topbar { padding: 0 20px; }
            .two-col { grid-template-columns: 1fr; }
            .col-sticky { position: static; }
            .filters-row { grid-template-columns: 1fr 1fr; }
            .filters-row .btn-primary { grid-column: span 2; }
        }
        @media (max-width: 640px) {
            .stats-bar { grid-template-columns: 1fr 1fr; }
            .filters-row { grid-template-columns: 1fr; }
            .filters-row .btn-primary { grid-column: span 1; }
        }
    </style>
</head>
<body>

@php
    $proposalCollection   = collect($proposals);
    $proposalCount        = $proposalCollection->count();
    $activeReviewCount    = $proposalCollection->whereIn('status', ['Submitted', 'Under Review', 'Endorsed'])->count();
    $returnedCount        = $proposalCollection->where('status', 'Returned')->count();
    $approvedAmount       = $proposalCollection->where('status', 'Approved')->sum('totalAmount');
@endphp

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<aside class="sb">
    <a class="sb-brand" href="{{ route('office-head.dashboard') }}">
        <div class="sb-logo">
            <img src="{{ asset('images/bsu-seal.png') }}" alt="BSU seal"
                 onerror="this.parentElement.innerHTML='🎓'">
        </div>
        <div>
            <span class="sb-brand-name">PRISM</span>
        </div>
    </a>

    <div class="sb-divider"></div>

    <nav class="sb-nav">
        <a href="{{ route('office-head.dashboard') }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('office-head.market-scoping') ?? '#' }}">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            Market Scoping
        </a>
        <a href="{{ route('office-head.budget-proposal') }}">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Budget Proposal
        </a>
        <a href="{{ route('office-head.my-proposals') }}" class="active">
            <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            My Proposals
        </a>
        <a href="{{ route('office-head.purchase-requests') }}">
            <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
            Purchase Requests
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-workspace">
            <p class="sb-workspace-label">Workspace</p>
            <p class="sb-workspace-role">Office Head / Dean</p>
        </div>
        <a href="{{ route('login') }}" class="sb-logout">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Logout
        </a>
    </div>
</aside>

{{-- ═══════════════ MAIN ═══════════════ --}}
<div class="main">

    <header class="topbar">
        <span class="topbar-title">My Proposals</span>
        <span class="topbar-chip">Proposal Tracker</span>
    </header>

    <div class="content">

        {{-- Stats + Filters card --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Overview</p>
                    <h2 class="card-title">Proposal Summary</h2>
                </div>
                <a class="btn-primary" href="{{ route('office-head.budget-proposal') }}">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    New Budget Proposal
                </a>
            </div>

            <dl class="stats-bar">
                <div class="stat-box">
                    <dt>Proposals</dt>
                    <dd>{{ $proposalCount }}</dd>
                </div>
                <div class="stat-box">
                    <dt>Active Review</dt>
                    <dd class="maroon">{{ $activeReviewCount }}</dd>
                </div>
                <div class="stat-box">
                    <dt>Returned</dt>
                    <dd>{{ $returnedCount }}</dd>
                </div>
                <div class="stat-box">
                    <dt>Approved Amount</dt>
                    <dd class="sm">PHP {{ number_format($approvedAmount) }}</dd>
                </div>
            </dl>

            <div class="filters-row" aria-label="Proposal filters">
                <div class="field-group">
                    <label class="field-label" for="proposalStatusFilter">Status</label>
                    <select id="proposalStatusFilter" class="field-select">
                        <option value="all">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label class="field-label" for="proposalYearFilter">Fiscal Year</label>
                    <select id="proposalYearFilter" class="field-select">
                        <option value="all">All fiscal years</option>
                        @foreach ($fiscalYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- 2-col: queue + timeline --}}
        <div class="two-col">

            {{-- Proposal queue --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Proposal queue</p>
                        <h2 class="card-title">All Proposals</h2>
                        <p class="card-sub">Select a proposal to review its approval movement.</p>
                    </div>
                    <span class="pill pill-gray" id="proposalVisibleCount">{{ $proposalCount }} shown</span>
                </div>

                <div class="queue-scroll" id="proposalRows">
                    @foreach ($proposals as $proposal)
                        @php
                            $latestEvent = collect($proposal['timeline'])->last();
                            $isReturned  = $proposal['status'] === 'Returned';
                        @endphp
                        <article
                            class="proposal-row"
                            data-proposal-row
                            data-proposal-id="{{ $proposal['id'] }}"
                            data-status="{{ $proposal['status'] }}"
                            data-year="{{ $proposal['fiscalYear'] }}"
                            tabindex="0"
                            aria-label="{{ $proposal['title'] }}"
                        >
                            <div class="proposal-row-head">
                                <div class="min-w-0">
                                    <p class="proposal-row-title">{{ $proposal['title'] }}</p>
                                    <p class="proposal-row-meta">FY {{ $proposal['fiscalYear'] }} &middot; Submitted {{ $proposal['dateSubmitted'] }}</p>
                                </div>
                                <x-prism.status-badge :status="$proposal['status']" />
                            </div>

                            <dl class="proposal-row-stats">
                                <div class="proposal-stat-item">
                                    <dt>Amount</dt>
                                    <dd>PHP {{ number_format($proposal['totalAmount']) }}</dd>
                                </div>
                                <div class="proposal-stat-item">
                                    <dt>Current Step</dt>
                                    <dd>{{ $latestEvent['step'] ?? 'Pending' }}</dd>
                                </div>
                                <div class="proposal-stat-item">
                                    <dt>Action</dt>
                                    <dd class="{{ $isReturned ? 'danger' : '' }}">{{ $isReturned ? 'Revise' : 'Track' }}</dd>
                                </div>
                            </dl>
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Timeline panel --}}
            <div class="card col-sticky" id="proposalTimelinePanel" aria-live="polite">
                <div class="card-head">
                    <div class="min-w-0" style="flex:1;">
                        <p class="card-eyebrow">Approval movement</p>
                        <h2 class="card-title" id="timelineTitle" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Select a proposal</h2>
                        <p class="card-sub" id="timelineMeta">Timeline details will appear here.</p>
                    </div>
                    <span class="pill pill-gray" id="timelineStatusBadge">Status</span>
                </div>

                <div class="timeline-meta-grid">
                    <div class="timeline-meta-box">
                        <dt>Total Amount</dt>
                        <dd id="timelineAmount">PHP 0</dd>
                    </div>
                    <div class="timeline-meta-box">
                        <dt>Next Action</dt>
                        <dd id="timelineAction">Select</dd>
                    </div>
                </div>

                <div id="timelineContent" class="timeline-empty">
                    <svg viewBox="0 0 24 24"><path d="M3 3h18v18H3z" rx="2" /><path d="M9 9h6M9 12h6M9 15h4"/><circle cx="17" cy="17" r="3"/><path d="M19.5 19.5L21 21"/></svg>
                    <p>Select a proposal to view timestamps, remarks, and revision status.</p>
                </div>
            </div>

        </div>{{-- /two-col --}}

    </div>{{-- /content --}}
</div>{{-- /main --}}

<script type="application/json" id="proposalData">@json($proposals)</script>

</body>
</html>