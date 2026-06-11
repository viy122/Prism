<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Division Performance Report | PRISM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            font-family: 'Poppins', sans-serif;
            background: #f0e9e9;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
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
            position: fixed;
            top: 0; left: 0;
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
        .main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            margin-left: 272px;
        }

        .topbar {
            position: sticky; top: 0; z-index: 40;
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; height: 66px; gap: 16px; flex-shrink: 0;
        }
        .topbar-title { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -.4px; }
        .topbar-actions { display: flex; align-items: center; gap: 10px; }

        /* ═══════════════════════════════════════
           CONTENT
        ═══════════════════════════════════════ */
        .content {
            padding: 28px 32px 56px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;

            --m:     #681012;
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
            --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
        }

        /* ─── Card ─── */
        .card {
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 18px;
            padding: 22px 26px;
            box-shadow: var(--sh-sm);
        }
        .card-eyebrow {
            font-size: 10px; font-weight: 700; letter-spacing: .18em;
            text-transform: uppercase; color: var(--m); margin-bottom: 4px;
        }
        .card-title { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
        .card-sub   { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
        .card-head  { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

        /* ─── Print button ─── */
        .btn-print {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px;
            background: var(--m); color: #fff;
            font-size: 13px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            border: none; transition: opacity .2s; white-space: nowrap;
        }
        .btn-print:hover { opacity: .88; }
        .btn-print svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Stat cards ─── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .stat-card {
            position: relative;
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 18px;
            padding: 20px 20px 20px 24px;
            box-shadow: var(--sh-sm);
            transition: border-color .2s, box-shadow .2s;
        }
        .stat-card:hover {
            border-color: rgba(201,168,76,.5);
            box-shadow: 0 12px 28px rgba(15,23,42,.07);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            left: 0; top: 20px;
            width: 4px; height: 40px;
            border-radius: 0 4px 4px 0;
            background: var(--gold);
        }
        .stat-icon {
            position: absolute;
            right: 16px; top: 16px;
            width: 36px; height: 36px;
            border-radius: 10px;
            border: 1px solid rgba(104,16,18,.12);
            background: rgba(104,16,18,.06);
            display: flex; align-items: center; justify-content: center;
        }
        .stat-icon svg {
            width: 17px; height: 17px;
            stroke: #681012; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }
        .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); }
        .stat-value { font-size: 1.55rem; font-weight: 800; color: var(--m); margin-top: 10px; display: block; letter-spacing: -.5px; line-height: 1.1; }
        .stat-desc  { font-size: 12px; color: var(--s500); margin-top: 8px; line-height: 1.6; }

        /* ─── Performance highlight cards ─── */
        .highlight-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .highlight-card {
            position: relative;
            overflow: hidden;
            background: var(--white);
            border-radius: 18px;
            padding: 20px 20px 20px 24px;
            box-shadow: var(--sh-sm);
        }
        .highlight-card::before {
            content: '';
            position: absolute;
            left: 0; top: 20px;
            width: 4px; height: 40px;
            border-radius: 0 4px 4px 0;
        }
        .highlight-card .hl-icon {
            position: absolute;
            right: 16px; top: 16px;
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .highlight-card .hl-icon svg {
            width: 17px; height: 17px;
            fill: none; stroke-width: 2;
            stroke-linecap: round; stroke-linejoin: round;
        }
        .highlight-card .hl-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; margin-bottom: 10px; position: relative;
        }
        .highlight-card .hl-name {
            font-size: 1.35rem; font-weight: 800; color: var(--s900);
            letter-spacing: -.3px; line-height: 1.2; position: relative;
        }
        .highlight-card .hl-desc {
            font-size: 12px; margin-top: 8px; line-height: 1.6;
            color: var(--s500); position: relative;
        }

        .highlight-best  { border: 1px solid #c0dd97; }
        .highlight-best::before  { background: #3b6d11; }
        .highlight-best .hl-icon { background: #eaf3de; border: 1px solid #c0dd97; }
        .highlight-best .hl-icon svg { stroke: #3b6d11; }
        .highlight-best .hl-label { color: #3b6d11; }

        .highlight-low   { border: 1px solid #f7c1c1; }
        .highlight-low::before   { background: #a32d2d; }
        .highlight-low .hl-icon  { background: #fcebeb; border: 1px solid #f7c1c1; }
        .highlight-low .hl-icon svg  { stroke: #a32d2d; }
        .highlight-low .hl-label { color: #a32d2d; }

        /* ─── Table ─── */
        .table-wrap {
            border-radius: 12px; border: 1px solid var(--s200);
            overflow: auto; max-height: 52vh;
            background: var(--white);
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
        tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .12s; }
        tbody tr:hover { background: rgba(104,16,18,.03); }
        tbody tr.row-best   { background: rgba(234,243,222,.5); }
        tbody tr.row-best:hover { background: rgba(234,243,222,.85); }
        tbody tr.row-low    { background: rgba(252,235,235,.5); }
        tbody tr.row-low:hover  { background: rgba(252,235,235,.85); }

        /* ─── Progress bar ─── */
        .prog-wrap  { min-width: 130px; }
        .prog-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 6px; }
        .prog-track {
            height: 10px; border-radius: 99px;
            background: var(--s100); overflow: hidden;
            border: 1px solid var(--s200);
        }
        .prog-fill         { height: 100%; border-radius: 99px; background: var(--m); }
        .prog-fill.best    { background: #3b6d11; }
        .prog-fill.low     { background: #a32d2d; }

        /* ─── Badges ─── */
        .badge {
            display: inline-flex; align-items: center;
            height: 24px; padding: 0 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-best   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
        .badge-low    { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
        .badge-steady { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .stats-grid    { grid-template-columns: repeat(2, 1fr); }
            .highlight-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            .main { margin-left: 0; }
            .content { padding: 16px 16px 40px; }
            .topbar { padding: 0 16px; }
        }
        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        @media print {
            .sb, .topbar, .btn-print { display: none !important; }
            .main { margin-left: 0; }
            .content { padding: 0; }
            body { background: #fff; }
            .table-wrap { max-height: none; overflow: visible; }
        }
    </style>
</head>
<body>

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<aside class="sb">
    <a class="sb-brand" href="#">
        <div class="sb-logo">
            <img src="{{ asset('images/bsu-seal.png') }}" alt="BSU seal"
                 onerror="this.parentElement.innerHTML='🎓'">
        </div>
        <span class="sb-brand-name">PRISM</span>
    </a>

    <div class="sb-divider"></div>

    <nav class="sb-nav">
        <a href="{{ route('vice-chancellor.dashboard') }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Division Dashboard
        </a>
        <a href="{{ route('vice-chancellor.division-procurement-status') }}">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Division Procurement Status
        </a>
        <a href="{{ route('vice-chancellor.division-performance-report') }}" class="active">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Division Performance Report
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-workspace">
            <p class="sb-workspace-label">Workspace</p>
            <p class="sb-workspace-role">Vice Chancellor</p>
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
        <span class="topbar-title">Division Performance Report</span>
        <div class="topbar-actions">
            <button class="btn-print" type="button" id="printViceReportButton" onclick="window.print()">
                <svg viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Export to PDF or Print
            </button>
        </div>
    </header>

    <div class="content">

        @php
            $totalAppItems       = collect($performanceRows)->sum('totalAppItems');
            $totalProcured       = collect($performanceRows)->sum('procured');
            $totalPending        = collect($performanceRows)->sum('pending');
            $averageUtilization  = round(collect($performanceRows)->avg('utilization'));
        @endphp

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Vice Chancellor</p>
            <h1 class="card-title">Division Performance Report</h1>
            <p class="card-sub">Compare utilization rates, APP item accomplishment, pending items, and performance extremes within the division.</p>
        </div>

        {{-- Stat cards --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                </div>
                <p class="stat-label">Total APP Items</p>
                <strong class="stat-value">{{ number_format($totalAppItems) }}</strong>
                <p class="stat-desc">Division-wide approved procurement items</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <p class="stat-label">Procured</p>
                <strong class="stat-value">{{ number_format($totalProcured) }}</strong>
                <p class="stat-desc">Completed items across the division</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <p class="stat-label">Pending</p>
                <strong class="stat-value">{{ number_format($totalPending) }}</strong>
                <p class="stat-desc">Items requiring follow-up or completion</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <p class="stat-label">Average Utilization</p>
                <strong class="stat-value">{{ $averageUtilization }}%</strong>
                <p class="stat-desc">Mean utilization rate across offices</p>
            </div>
        </div>

        {{-- Best vs Lowest highlight cards --}}
        <div class="highlight-grid">
            <div class="highlight-card highlight-best">
                <div class="hl-icon">
                    <svg viewBox="0 0 24 24"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                </div>
                <p class="hl-label">Best-performing office</p>
                <p class="hl-name">{{ $bestOffice }}</p>
                <p class="hl-desc">Highest utilization and procurement accomplishment in the division</p>
            </div>
            <div class="highlight-card highlight-low">
                <div class="hl-icon">
                    <svg viewBox="0 0 24 24"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>
                </div>
                <p class="hl-label">Lowest-performing office</p>
                <p class="hl-name">{{ $lowestOffice }}</p>
                <p class="hl-desc">Needs follow-up due to lower utilization and pending procurement items</p>
            </div>
        </div>

        {{-- Performance summary table --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Office utilization rates</p>
                    <h2 class="card-title">Division Performance Summary</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office Name</th>
                            <th>Total APP Items</th>
                            <th>Procured</th>
                            <th>Pending</th>
                            <th>Utilization</th>
                            <th>Highlight</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($performanceRows as $row)
                            @php
                                $isBest  = $row['performance'] === 'best';
                                $isLow   = $row['performance'] === 'lowest';
                                $rowClass = $isBest ? 'row-best' : ($isLow ? 'row-low' : '');
                                $barClass = $isBest ? 'best' : ($isLow ? 'low' : '');
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                                <td style="font-weight:600;color:var(--s700);">{{ $row['totalAppItems'] }}</td>
                                <td style="font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                                <td style="font-weight:600;color:{{ $isLow ? '#a32d2d' : 'var(--s700)' }};">{{ $row['pending'] }}</td>
                                <td>
                                    <div class="prog-wrap">
                                        <p class="prog-label">{{ $row['utilization'] }}%</p>
                                        <div class="prog-track">
                                            <div class="prog-fill {{ $barClass }}" style="width:{{ $row['utilization'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($isBest)
                                        <span class="badge badge-best">Best-performing</span>
                                    @elseif ($isLow)
                                        <span class="badge badge-low">Lowest-performing</span>
                                    @else
                                        <span class="badge badge-steady">Steady</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>