<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procurement Reports | PRISM</title>
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

        /* ─── Print button ─── */
        .btn-print {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px;
            background: var(--m); color: #fff;
            font-size: 13px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            border: none; transition: opacity .2s;
            white-space: nowrap;
        }
        .btn-print:hover { opacity: .88; }
        .btn-print svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Completion chip ─── */
        .completion-chip {
            display: inline-flex; align-items: center;
            height: 28px; padding: 0 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            background: rgba(104,16,18,.06); color: var(--m);
            border: 1px solid rgba(104,16,18,.12);
            white-space: nowrap;
        }

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

        /* ─── Progress bar ─── */
        .prog-wrap { min-width: 140px; }
        .prog-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 6px; }
        .prog-track {
            height: 10px; border-radius: 99px;
            background: var(--s100); overflow: hidden;
            border: 1px solid var(--s200);
        }
        .prog-fill { height: 100%; border-radius: 99px; background: var(--m); }

        /* ─── Badges ─── */
        .badge {
            display: inline-flex; align-items: center;
            height: 24px; padding: 0 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-low    { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
        .badge-medium { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
        .badge-high   { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
        .badge-delayed { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

        /* ─── Two-col grid ─── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* ─── Delay cards ─── */
        .delay-list { display: flex; flex-direction: column; gap: 10px; }
        .delay-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #f7c1c1;
            background: rgba(252,235,235,.6);
            transition: background .15s, box-shadow .15s;
        }
        .delay-item:hover { background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.06); }
        .delay-office { font-size: 13px; font-weight: 700; color: var(--s900); margin-bottom: 4px; }
        .delay-entry  { font-size: 12px; color: var(--s600); line-height: 1.7; }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .two-col { grid-template-columns: 1fr; }
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

        /* ─── Print styles ─── */
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
        <a href="{{ route('chancellor.dashboard') }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('chancellor.budget-approval') }}">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Budget Approval
        </a>
        <a href="{{ route('chancellor.procurement-reports') }}" class="active">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Procurement Reports
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-workspace">
            <p class="sb-workspace-label">Workspace</p>
            <p class="sb-workspace-role">Chancellor's Office</p>
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
        <span class="topbar-title">Procurement Reports</span>
        <div class="topbar-actions">
            <button class="btn-print" type="button" id="printChancellorReportButton" onclick="window.print()">
                <svg viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Export to PDF or Print
            </button>
        </div>
    </header>

    <div class="content">

        @php
            $totalTargeted   = collect($accomplishmentRows)->sum('targeted');
            $totalProcured   = collect($accomplishmentRows)->sum('procured');
            $campusCompletion = $totalTargeted > 0 ? round(($totalProcured / $totalTargeted) * 100) : 0;
            $delayedCount    = collect($delayedByOffice)->flatten(1)->count();
        @endphp

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Chancellor</p>
            <h1 class="card-title">Procurement Reports</h1>
            <p class="card-sub">Review campus-wide procurement accomplishment, year-end utilization, and delayed items grouped by office.</p>
        </div>

        {{-- Stat cards --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                </div>
                <p class="stat-label">Campus Targets</p>
                <strong class="stat-value">{{ number_format($totalTargeted) }}</strong>
                <p class="stat-desc">Procurement targets across monitored offices</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <p class="stat-label">Items Procured</p>
                <strong class="stat-value">{{ number_format($totalProcured) }}</strong>
                <p class="stat-desc">Completed procurement items across campus</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <p class="stat-label">Completion Rate</p>
                <strong class="stat-value">{{ $campusCompletion }}%</strong>
                <p class="stat-desc">Campus-wide procurement accomplishment</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <p class="stat-label">Delayed Items</p>
                <strong class="stat-value">{{ $delayedCount }}</strong>
                <p class="stat-desc">Risk items grouped by office for follow-up</p>
            </div>
        </div>

        {{-- Items Targeted vs Procured per Office --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Campus-wide accomplishment</p>
                    <h2 class="card-title">Items Targeted vs Procured per Office</h2>
                </div>
                <span class="completion-chip">{{ $campusCompletion }}% campus completion</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Items Targeted</th>
                            <th>Items Procured</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accomplishmentRows as $row)
                            <tr>
                                <td style="font-weight:600;color:var(--s600);">{{ $row['office'] }}</td>
                                <td style="font-weight:600;color:var(--s700);">{{ $row['targeted'] }}</td>
                                <td style="font-weight:600;color:var(--s700);">{{ $row['procured'] }}</td>
                                <td>
                                    <div class="prog-wrap">
                                        <p class="prog-label">{{ $row['completionRate'] }}%</p>
                                        <div class="prog-track">
                                            <div class="prog-fill" style="width:{{ $row['completionRate'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Utilization Summary + Delay Reasons --}}
        <div class="two-col">

            {{-- Year-end utilization --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Year-end budget utilization</p>
                        <h2 class="card-title">Summary by Office</h2>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Budget</th>
                                <th>Utilized</th>
                                <th>Forecast</th>
                                <th>Risk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($utilizationSummary as $row)
                                @php
                                    $riskClass = match(strtolower($row['risk'])) {
                                        'low'  => 'badge-low',
                                        'high', 'critical' => 'badge-high',
                                        default => 'badge-medium',
                                    };
                                @endphp
                                <tr>
                                    <td style="font-weight:600;color:var(--s600);white-space:nowrap;">{{ $row['office'] }}</td>
                                    <td style="color:var(--s700);">PHP {{ number_format($row['budget']) }}</td>
                                    <td style="font-weight:700;color:var(--m);">PHP {{ number_format($row['utilized']) }}</td>
                                    <td style="font-weight:700;color:var(--s700);">{{ $row['forecast'] }}%</td>
                                    <td><span class="badge {{ $riskClass }}">{{ $row['risk'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Delay reasons --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Delayed items grouped by office</p>
                        <h2 class="card-title">Delay Reasons</h2>
                    </div>
                </div>
                <div class="delay-list">
                    @foreach ($delayedByOffice as $office => $items)
                        <div class="delay-item">
                            <div>
                                <p class="delay-office">{{ $office }}</p>
                                @foreach ($items as $item)
                                    <p class="delay-entry">{{ $item['item'] }} &mdash; {{ $item['prNumber'] }}: {{ $item['remarks'] }}</p>
                                @endforeach
                            </div>
                            <span class="badge badge-delayed" style="white-space:nowrap;flex-shrink:0;">{{ count($items) }} delayed</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>