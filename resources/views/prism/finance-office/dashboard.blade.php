<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | PRISM — Finance Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
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
           SIDEBAR — identical across all pages
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
        .sb-brand-name { font-size: 18px; font-weight: 900; color: #fff; letter-spacing: .5px; }
        .sb-divider { height: 1px; background: rgba(255,255,255,.1); margin: 0 18px; }
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
        .sb-bottom { padding: 12px 12px 20px; display: flex; flex-direction: column; gap: 10px; }
        .sb-workspace { padding: 16px 18px 8px; }
        .sb-workspace-label {
            font-size: 9px; font-weight: 700; letter-spacing: .18em;
            text-transform: uppercase; color: rgba(255,255,255,.38); margin-bottom: 3px;
        }
        .sb-workspace-role { font-size: 13px; font-weight: 800; color: #fff; }
        .sb-logout {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 42px; border-radius: 10px;
            border: 1px solid rgba(255,255,255,.15);
            background: rgba(255,255,255,.1);
            font-size: 13px; font-weight: 700; color: #fff;
            text-decoration: none; transition: background .2s, color .2s;
            font-family: 'Poppins', sans-serif;
        }
        .sb-logout:hover { background: #fff; color: #681012; }
        .sb-logout svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ═══════════════════════════════════════
           MAIN
        ═══════════════════════════════════════ */
        .main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .topbar {
            position: sticky; top: 0; z-index: 40;
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; height: 66px; gap: 16px; flex-shrink: 0;
        }
        .topbar-title { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -.4px; }
        .topbar-chip {
            display: inline-flex; align-items: center;
            height: 34px; padding: 0 16px; border-radius: 8px;
            background: rgba(104,16,18,.07); border: 1px solid rgba(104,16,18,.14);
            font-size: 12px; font-weight: 700; color: #681012; white-space: nowrap;
        }

        /* ═══════════════════════════════════════
           DASHBOARD
        ═══════════════════════════════════════ */
        .dash {
            padding: 28px 32px 56px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;

            --m:     #681012;
            --m-dk:  #4e0c0e;
            --white: #ffffff;
            --s50:   #f8fafc;
            --s100:  #f1f5f9;
            --s200:  #e2e8f0;
            --s400:  #94a3b8;
            --s500:  #64748b;
            --s600:  #475569;
            --s700:  #334155;
            --s900:  #0f172a;
            --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
            --sh-md: 0 4px 16px rgba(15,23,42,.08), 0 1px 4px rgba(15,23,42,.04);
            --sh-lg: 0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05);
        }

        /* ─── Page header ─── */
        .pd-header {
            background: var(--white); border: 1px solid var(--s200);
            border-radius: 18px; padding: 22px 26px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap; box-shadow: var(--sh-sm);
        }
        .pd-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
        .pd-header h1 { font-size: 26px; font-weight: 800; color: var(--s900); letter-spacing: -.5px; margin-bottom: 5px; line-height: 1.15; }
        .pd-header-sub { font-size: 13px; color: var(--s600); line-height: 1.65; max-width: 520px; }

        /* ─── Stat cards ─── */
        .pd-stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 13px; }
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
        .pd-stat-value.sm { font-size: 17px; letter-spacing: -.3px; }
        .pd-stat-hint { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

        /* ─── Section card ─── */
        .pd-card { background: var(--white); border: 1px solid var(--s200); border-radius: 15px; padding: 20px 22px; box-shadow: var(--sh-sm); }
        .pd-card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 3px; }
        .pd-card-title { font-size: 15px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; margin-bottom: 16px; }
        .pd-card-sub { font-size: 12px; color: var(--s500); margin-top: 3px; }

        /* ─── 2-col grid ─── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* ─── Table ─── */
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
        tbody tr:hover { background: rgba(104,16,18,.03); }

        .office-name { font-size: 13px; font-weight: 700; color: var(--s900); }
        .date-text   { font-size: 13px; font-weight: 600; color: var(--s500); }
        .amount-text { font-size: 13px; font-weight: 700; color: var(--s900); }

        /* ─── Review button ─── */
        .btn-review {
            display: inline-flex; align-items: center; gap: 6px;
            height: 32px; padding: 0 14px; border-radius: 8px;
            border: 1px solid rgba(104,16,18,.3); background: #fff;
            font-size: 12px; font-weight: 700; color: var(--m);
            text-decoration: none; transition: background .15s, border-color .15s;
            font-family: 'Poppins', sans-serif;
        }
        .btn-review:hover { background: rgba(104,16,18,.05); border-color: var(--m); }
        .btn-review svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Badges ─── */
        .badge { display: inline-flex; align-items: center; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; white-space: nowrap; line-height: 1.4; flex-shrink: 0; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-endorsed  { background: #dbeafe; color: #1e40af; }
        .badge-returned  { background: #fee2e2; color: #991b1b; }
        .badge-default   { background: var(--s100); color: var(--s700); }

        /* ─── Chart wrap ─── */
        .pd-chart-wrap { position: relative; width: 100%; }
        .pd-legend { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 10px; font-size: 12px; color: var(--s600); }
        .pd-legend-item { display: flex; align-items: center; gap: 6px; }
        .pd-legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .pd-stat-grid { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            body { display: block; }
            .dash { padding: 16px 16px 40px; }
            .two-col { grid-template-columns: 1fr; }
            .topbar { padding: 0 16px; }
        }
        @media (max-width: 640px) {
            .pd-stat-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<aside class="sb">
    <a class="sb-brand" href="{{ route('finance-office.dashboard') }}">
        <div class="sb-logo">
            <img src="{{ asset('images/bsu-seal.png') }}" alt="BSU seal"
                 onerror="this.parentElement.innerHTML='🎓'">
        </div>
        <span class="sb-brand-name">PRISM</span>
    </a>

    <div class="sb-divider"></div>

    <nav class="sb-nav">
        <a href="{{ route('finance-office.dashboard') }}" class="active">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('finance-office.proposal-review') }}">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Proposal Review
        </a>
        <a href="{{ route('finance-office.annual-procurement-plan') }}">
            <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
            Annual Procurement Plan
        </a>
        <a href="{{ route('finance-office.budget-utilization-report') }}">
            <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Budget Utilization Report
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-workspace">
            <p class="sb-workspace-label">Workspace</p>
            <p class="sb-workspace-role">Finance Office</p>
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
        <span class="topbar-title">Dashboard</span>
        <span class="topbar-chip">Finance Office</span>
    </header>

    <div class="dash">

        {{-- Page header --}}
        <div class="pd-header">
            <div>
                <p class="pd-eyebrow">Finance Office</p>
                <h1>Dashboard</h1>
                <p class="pd-header-sub">Monitor proposal review workload, office submission status, and the campus-wide proposed budget.</p>
            </div>
        </div>

        {{-- Stat cards --}}
        <dl class="pd-stat-grid">
            <article class="pd-stat">
                <div class="pd-stat-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="pd-stat-label">Awaiting Review</div>
                <div class="pd-stat-value">{{ number_format($summary['awaitingReview']) }}</div>
                <div class="pd-stat-hint">Submitted proposals pending Finance action</div>
            </article>
            <article class="pd-stat">
                <div class="pd-stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="pd-stat-label">Endorsed This Month</div>
                <div class="pd-stat-value">{{ number_format($summary['endorsedThisMonth']) }}</div>
                <div class="pd-stat-hint">Forwarded for Chancellor approval</div>
            </article>
            <article class="pd-stat">
                <div class="pd-stat-icon">
                    <svg viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                </div>
                <div class="pd-stat-label">Returned</div>
                <div class="pd-stat-value">{{ number_format($summary['returned']) }}</div>
                <div class="pd-stat-hint">Returned to offices with remarks</div>
            </article>
            <article class="pd-stat">
                <div class="pd-stat-icon">
                    <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
                <div class="pd-stat-label">Total Proposed Budget</div>
                <div class="pd-stat-value sm">PHP {{ number_format($summary['totalCampusBudget']) }}</div>
                <div class="pd-stat-hint">Campus-wide across active submissions</div>
            </article>
        </dl>

        {{-- 2-col: status table + review queue --}}
        <div class="two-col">

            {{-- Proposals by Status --}}
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
                            @foreach ($officeStatusGroups as $office)
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

            {{-- Review Queue --}}
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
                            @foreach ($recentSubmissions as $submission)
                                <tr>
                                    <td><span class="office-name">{{ $submission['office'] }}</span></td>
                                    <td><span class="date-text">{{ $submission['submittedDate'] }}</span></td>
                                    <td><span class="amount-text">PHP {{ number_format($submission['totalAmount']) }}</span></td>
                                    <td>
                                        <a class="btn-review" href="{{ route('finance-office.proposal-review.show', ['proposal' => $submission['proposalId']]) }}">
                                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

        </div>

    </div>
</div>

</body>
</html>