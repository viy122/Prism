<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Annual Procurement Plan | PRISM — Finance Office</title>
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
            --sh-md: 0 4px 16px rgba(15,23,42,.08), 0 1px 4px rgba(15,23,42,.04);
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
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 42px; padding: 0 20px; border-radius: 10px;
            background: var(--m); color: #fff;
            font-size: 13px; font-weight: 700; border: none; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 10px rgba(104,16,18,.2);
            transition: background .2s; white-space: nowrap;
        }
        .btn-primary:hover { background: var(--m-dk); }
        .btn-primary svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Filters ─── */
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .field-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 7px; display: block; }
        .field-select {
            height: 44px; width: 100%; border-radius: 10px;
            border: 1px solid var(--s300); background: var(--white);
            padding: 0 14px; font-size: 13.5px; font-weight: 500;
            color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-select:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }

        .field-input {
            height: 44px; width: 100%; border-radius: 10px;
            border: 1px solid var(--s300); background: var(--white);
            padding: 0 14px; font-size: 13px; font-weight: 500;
            color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
        .field-input::placeholder { color: var(--s400); }

        /* ─── Table ─── */
        .table-wrap {
            border-radius: 12px; border: 1px solid var(--s200);
            overflow: auto; max-height: 64vh;
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
        tbody td { padding: 14px 16px; border-bottom: 1px solid var(--s100); vertical-align: top; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .12s; }
        tbody tr:hover { background: rgba(104,16,18,.03); }

        /* ─── Cell helpers ─── */
        .item-name  { font-size: 13px; font-weight: 700; color: var(--s900); }
        .amount-val { font-size: 13px; font-weight: 700; color: var(--s900); }

        .badge {
            display: inline-flex; align-items: center;
            height: 26px; padding: 0 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            background: var(--s100); color: var(--s600);
            border: 1px solid var(--s200); white-space: nowrap;
        }

        /* ─── Count chip ─── */
        .count-chip {
            display: inline-flex; align-items: center;
            height: 28px; padding: 0 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            background: var(--s100); color: var(--s700);
            border: 1px solid var(--s200);
        }

        /* ─── Override cell ─── */
        .override-cell { display: grid; gap: 8px; min-width: 220px; }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .sb { display: none; }
            body { display: block; }
            .content { padding: 16px 16px 40px; }
            .topbar { padding: 0 16px; }
            .filters-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .filters-grid { grid-template-columns: 1fr; }
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
        <a href="{{ route('finance-office.dashboard') }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('finance-office.proposal-review') }}">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Proposal Review
        </a>
        <a href="{{ route('finance-office.annual-procurement-plan') }}" class="active">
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
        <span class="topbar-title">Annual Procurement Plan</span>
        <div class="topbar-actions">
            <button class="btn-primary" type="button" id="printAppButton">
                <svg viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Export or Print APP
            </button>
        </div>
    </header>

    <div class="content">

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Finance Office</p>
            <h1 class="card-title">Annual Procurement Plan</h1>
            <p class="card-sub">Consolidate approved items from all offices, review recommended procurement modes, and record override reasons.</p>
        </div>

        {{-- Filters --}}
        <div class="card">
            <div class="filters-grid">
                <div>
                    <label class="field-label" for="appOfficeFilter">Office</label>
                    <select class="field-select" id="appOfficeFilter">
                        <option value="all">All offices</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office }}">{{ $office }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label" for="appQuarterFilter">Quarter</label>
                    <select class="field-select" id="appQuarterFilter">
                        <option value="all">All quarters</option>
                        @foreach ($quarters as $quarter)
                            <option value="{{ $quarter }}">{{ $quarter }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label" for="appModeFilter">Procurement mode</label>
                    <select class="field-select" id="appModeFilter">
                        <option value="all">All modes</option>
                        @foreach ($procurementModes as $mode)
                            <option value="{{ $mode }}">{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- APP Item Matrix --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Consolidated approved items</p>
                    <h2 class="card-title">APP Item Matrix</h2>
                </div>
                <span class="count-chip" id="appVisibleCount">{{ count($appItems) }} shown</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>ABC Amount</th>
                            <th>Quarter</th>
                            <th>Procurement Mode</th>
                            <th>Recommendation</th>
                            <th>Override Mode &amp; Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appItems as $item)
                            <tr data-app-row
                                data-office="{{ $item['office'] }}"
                                data-quarter="{{ $item['targetQuarter'] }}"
                                data-mode="{{ $item['procurementMode'] }}">
                                <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">
                                    {{ $item['office'] }}
                                </td>
                                <td>
                                    <p class="item-name">{{ $item['item'] }}</p>
                                </td>
                                <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">
                                    {{ $item['quantity'] }}
                                </td>
                                <td>
                                    <p class="amount-val">PHP {{ number_format($item['abcAmount']) }}</p>
                                </td>
                                <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">
                                    {{ $item['targetQuarter'] }}
                                </td>
                                <td>
                                    <span class="badge">{{ $item['procurementMode'] }}</span>
                                </td>
                                <td style="font-size:13px;color:var(--s600);max-width:180px;">
                                    {{ $item['recommendation'] }}
                                </td>
                                <td>
                                    <div class="override-cell">
                                        <select class="field-select" style="height:40px;font-size:13px;" aria-label="Override procurement mode for {{ $item['item'] }}">
                                            @foreach ($procurementModes as $mode)
                                                <option value="{{ $mode }}" @selected($mode === $item['procurementMode'])>{{ $mode }}</option>
                                            @endforeach
                                        </select>
                                        <input class="field-input" style="height:40px;font-size:13px;" type="text" placeholder="Reason for override">
                                    </div>
                                </td>
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
        const officeEl  = document.getElementById('appOfficeFilter');
        const quarterEl = document.getElementById('appQuarterFilter');
        const modeEl    = document.getElementById('appModeFilter');
        const countEl   = document.getElementById('appVisibleCount');
        const rows      = document.querySelectorAll('[data-app-row]');

        function applyFilters() {
            const office  = officeEl.value;
            const quarter = quarterEl.value;
            const mode    = modeEl.value;
            let visible   = 0;

            rows.forEach(row => {
                const match =
                    (office  === 'all' || row.dataset.office  === office)  &&
                    (quarter === 'all' || row.dataset.quarter === quarter) &&
                    (mode    === 'all' || row.dataset.mode    === mode);

                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            countEl.textContent = visible + ' shown';
        }

        officeEl.addEventListener('change',  applyFilters);
        quarterEl.addEventListener('change', applyFilters);
        modeEl.addEventListener('change',    applyFilters);
    })();
</script>

</body>
</html>