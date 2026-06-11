<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | PRISM — Procurement Office</title>
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
            top: 0;
            left: 0;
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
        .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
        .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
        .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
        .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

        /* ─── Buttons ─── */
        .btn-outline {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px;
            background: var(--white); color: var(--m);
            font-size: 13px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            border: 1px solid rgba(104,16,18,.3);
            transition: background .2s, border-color .2s; white-space: nowrap;
        }
        .btn-outline:hover { background: rgba(104,16,18,.05); border-color: var(--m); }
        .btn-outline svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

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

        /* ─── Status badges ─── */
        .badge {
            display: inline-flex; align-items: center;
            height: 24px; padding: 0 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-completed   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
        .badge-in-progress { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
        .badge-pending     { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
        .badge-overdue     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

        /* ─── Count chip ─── */
        .count-chip {
            display: inline-flex; align-items: center;
            height: 28px; padding: 0 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            background: var(--s100); color: var(--s700);
            border: 1px solid var(--s200);
        }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            body { display: block; }
            .content { padding: 16px 16px 40px; }
            .topbar { padding: 0 16px; }
        }
        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<aside class="sb">
    <a class="sb-brand" href="{{ route('procurement-office.dashboard') }}">
        <div class="sb-logo">
            <img src="{{ asset('images/bsu-seal.png') }}" alt="BSU seal"
                 onerror="this.parentElement.innerHTML='🎓'">
        </div>
        <span class="sb-brand-name">PRISM</span>
    </a>

    <div class="sb-divider"></div>

    <nav class="sb-nav">
        <a href="{{ route('procurement-office.dashboard') }}" class="active">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('procurement-office.purchase-request-management') }}">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Purchase Request Management
        </a>
        <a href="{{ route('procurement-office.procurement-status-tracking') }}">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Procurement Status Tracking
        </a>
        <a href="{{ route('procurement-office.procurement-reports') }}">
            <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            Procurement Reports
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-workspace">
            <p class="sb-workspace-label">Workspace</p>
            <p class="sb-workspace-role">Procurement Office</p>
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
        <div class="topbar-actions">
            <button class="btn-outline" type="button" id="dueThisMonthButton">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                PRs Due This Month
            </button>
        </div>
    </header>

    <div class="content">

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Procurement Office</p>
            <h1 class="card-title">Dashboard</h1>
            <p class="card-sub">Track received purchase requests, current processing status, overdue action items, and urgent PRs past target quarter.</p>
        </div>

        {{-- Summary stat cards --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v3"/><path d="M9 11h4m-4 4h2"/><rect x="13" y="13" width="8" height="8" rx="1"/><path d="M17 13v-2"/></svg>
                </div>
                <p class="stat-label">Total PRs received</p>
                <strong class="stat-value">{{ number_format($summary['totalPrsReceived']) }}</strong>
                <p class="stat-desc">Uploaded and routed to Procurement Office</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <p class="stat-label">PRs in progress</p>
                <strong class="stat-value">{{ number_format($summary['prsInProgress']) }}</strong>
                <p class="stat-desc">Under canvassing, validation, or PO preparation</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <p class="stat-label">Completed this month</p>
                <strong class="stat-value">{{ number_format($summary['prsCompletedThisMonth']) }}</strong>
                <p class="stat-desc">Completed purchases in the current month</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <p class="stat-label">Overdue PRs</p>
                <strong class="stat-value">{{ number_format($summary['overduePrs']) }}</strong>
                <p class="stat-desc">Past target quarter and needing action</p>
            </div>
        </div>

        {{-- PRs per Office by Status — full width landscape --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">This quarter</p>
                    <h2 class="card-title">PRs per Office by Status</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Completed</th>
                            <th>In Progress</th>
                            <th>Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($officeStatusGroups as $office)
                            <tr>
                                <td style="font-size:13px;font-weight:600;color:var(--s600);">{{ $office['office'] }}</td>
                                <td><span class="badge badge-completed">{{ $office['completed'] }}</span></td>
                                <td><span class="badge badge-in-progress">{{ $office['inProgress'] }}</span></td>
                                <td><span class="badge badge-pending">{{ $office['pending'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Urgent PRs — full width below --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Past target quarter</p>
                    <h2 class="card-title">Urgent PRs</h2>
                </div>
                <span class="count-chip" id="urgentPrVisibleCount">{{ count($urgentPrs) }} shown</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>PR No.</th>
                            <th>Item</th>
                            <th>Quarter</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($urgentPrs as $pr)
                            @php
                                $statusSlug = match(strtolower($pr['status'])) {
                                    'completed'   => 'badge-completed',
                                    'in progress' => 'badge-in-progress',
                                    'pending'     => 'badge-pending',
                                    default       => 'badge-overdue',
                                };
                            @endphp
                            <tr data-urgent-pr-row data-due-month="{{ $pr['dueThisMonth'] ? 'yes' : 'no' }}">
                                <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $pr['office'] }}</td>
                                <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $pr['prNumber'] }}</td>
                                <td style="font-size:13px;color:var(--s900);font-weight:600;">{{ $pr['item'] }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $pr['targetQuarter'] }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $pr['dueDate'] }}</td>
                                <td><span class="badge {{ $statusSlug }}">{{ $pr['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        const btn     = document.getElementById('dueThisMonthButton');
        const countEl = document.getElementById('urgentPrVisibleCount');
        const rows    = document.querySelectorAll('[data-urgent-pr-row]');
        let   filtered = false;

        btn.addEventListener('click', function () {
            filtered = !filtered;
            let visible = 0;

            rows.forEach(row => {
                const show = !filtered || row.dataset.dueMonth === 'yes';
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            countEl.textContent = visible + ' shown';
            btn.style.background = filtered ? 'rgba(104,16,18,.07)' : '';
            btn.style.borderColor = filtered ? '#681012' : '';
        });
    })();
</script>

</body>
</html>