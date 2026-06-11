<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Division Procurement Status | PRISM</title>
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

        /* ─── Filters ─── */
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .filter-group label {
            display: block;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: var(--s500); margin-bottom: 7px;
        }
        .filter-group select {
            width: 100%;
            height: 42px;
            border-radius: 10px;
            border: 1px solid var(--s200);
            background: var(--s50);
            padding: 0 14px;
            font-size: 13px; font-weight: 500; color: var(--s700);
            font-family: 'Poppins', sans-serif;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 34px;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }
        .filter-group select:focus {
            outline: none;
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.12);
        }

        /* ─── Count chip ─── */
        .count-chip {
            display: inline-flex; align-items: center;
            height: 28px; padding: 0 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            background: var(--s100); color: var(--s700);
            border: 1px solid var(--s200);
            white-space: nowrap;
        }

        /* ─── Two-col layout ─── */
        .split-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 420px;
            gap: 20px;
            align-items: start;
        }

        /* ─── Table ─── */
        .table-wrap {
            border-radius: 12px; border: 1px solid var(--s200);
            overflow: auto; max-height: 60vh;
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
        tbody tr { transition: background .12s; cursor: pointer; }
        tbody tr:hover { background: rgba(104,16,18,.04); }
        tbody tr.delayed-row { background: rgba(252,235,235,.5); }
        tbody tr.delayed-row:hover { background: rgba(252,235,235,.85); }
        tbody tr.selected-row { background: rgba(104,16,18,.07) !important; outline: none; }

        /* ─── Badges ─── */
        .badge {
            display: inline-flex; align-items: center;
            height: 24px; padding: 0 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-on-track  { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
        .badge-at-risk   { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
        .badge-delayed   { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
        .badge-pending   { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
        .badge-progress  { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }

        /* ─── Detail panel ─── */
        .detail-panel {
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 18px;
            padding: 22px 26px;
            box-shadow: var(--sh-sm);
            position: sticky;
            top: 86px;
        }

        .detail-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 220px;
            border-radius: 12px;
            border: 1.5px dashed var(--s300);
            background: var(--s50);
            text-align: center;
            padding: 32px;
        }
        .detail-empty svg {
            width: 32px; height: 32px;
            stroke: rgba(104,16,18,.4); fill: none;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }
        .detail-empty p { font-size: 13px; color: var(--s400); line-height: 1.6; max-width: 220px; }

        /* ─── Timeline ─── */
        .timeline { display: flex; flex-direction: column; gap: 0; }
        .tl-item {
            display: flex;
            gap: 14px;
            padding-bottom: 20px;
            position: relative;
        }
        .tl-item:last-child { padding-bottom: 0; }
        .tl-item:last-child .tl-line { display: none; }
        .tl-dot-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            width: 28px;
        }
        .tl-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--m);
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px var(--m);
            flex-shrink: 0;
            margin-top: 4px;
        }
        .tl-line {
            flex: 1;
            width: 2px;
            background: var(--s200);
            margin-top: 6px;
            min-height: 24px;
        }
        .tl-content { flex: 1; min-width: 0; }
        .tl-date { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s400); margin-bottom: 3px; }
        .tl-action { font-size: 13px; font-weight: 600; color: var(--s700); }
        .tl-note { font-size: 12px; color: var(--s500); margin-top: 2px; line-height: 1.55; }

        /* ─── Notes textarea ─── */
        .notes-area {
            width: 100%;
            border-radius: 10px;
            border: 1px solid var(--s200);
            background: var(--s50);
            padding: 12px 14px;
            font-size: 13px; color: var(--s700);
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            resize: vertical;
            min-height: 90px;
            transition: border-color .15s, box-shadow .15s;
        }
        .notes-area:focus {
            outline: none;
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.12);
        }
        .btn-save {
            display: inline-flex; align-items: center; gap: 7px;
            height: 38px; padding: 0 18px; border-radius: 9px;
            background: var(--m); color: #fff;
            font-size: 12px; font-weight: 700; font-family: 'Poppins', sans-serif;
            border: none; cursor: pointer;
            transition: opacity .15s, transform .1s;
        }
        .btn-save:hover { opacity: .88; }
        .btn-save:active { transform: scale(.97); }
        .btn-save svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Section divider ─── */
        .section-sep {
            height: 1px;
            background: var(--s100);
            margin: 16px 0;
        }

        /* ─── Meta row ─── */
        .meta-row {
            display: flex; flex-wrap: wrap; gap: 16px;
            margin-bottom: 16px;
        }
        .meta-item { display: flex; flex-direction: column; gap: 2px; }
        .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s400); }
        .meta-value { font-size: 13px; font-weight: 600; color: var(--s700); }

        /* ─── Hidden rows ─── */
        tr[data-vice-status-row][style*="display:none"],
        tr[data-vice-status-row].hidden-row { display: none; }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .split-layout { grid-template-columns: 1fr; }
            .filters-grid { grid-template-columns: repeat(2, 1fr); }
            .detail-panel { position: static; }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            .main { margin-left: 0; }
            .content { padding: 16px 16px 40px; }
            .topbar { padding: 0 16px; }
        }
        @media (max-width: 640px) {
            .filters-grid { grid-template-columns: 1fr; }
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
        <a href="{{ route('vice-chancellor.division-procurement-status') }}" class="active">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Division Procurement Status
        </a>
        <a href="{{ route('vice-chancellor.division-performance-report') }}">
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
        <span class="topbar-title">Division Procurement Status</span>
    </header>

    <div class="content">

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Vice Chancellor</p>
            <h1 class="card-title">Division Procurement Status</h1>
            <p class="card-sub">Review APP items under the assigned division, inspect activity timelines, and add follow-up notes for offices.</p>
        </div>

        {{-- Filters --}}
        <div class="card">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="viceStatusOfficeFilter">Office</label>
                    <select id="viceStatusOfficeFilter">
                        <option value="all">All offices</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office }}">{{ $office }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="viceStatusQuarterFilter">Quarter</label>
                    <select id="viceStatusQuarterFilter">
                        <option value="all">All quarters</option>
                        @foreach ($quarters as $quarter)
                            <option value="{{ $quarter }}">{{ $quarter }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="viceStatusStatusFilter">Status</label>
                    <select id="viceStatusStatusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Main split: table + detail panel --}}
        <div class="split-layout">

            {{-- Table --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Grouped by office</p>
                        <h2 class="card-title">Division APP Items</h2>
                    </div>
                    <span class="count-chip" id="viceStatusVisibleCount">{{ count($divisionItems) }} shown</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Item</th>
                                <th>Quarter</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($divisionItems as $item)
                                <tr
                                    class="{{ $item['currentStatus'] === 'Delayed' ? 'delayed-row' : '' }}"
                                    data-vice-status-row
                                    data-item-id="{{ $item['id'] }}"
                                    data-office="{{ $item['office'] }}"
                                    data-quarter="{{ $item['targetQuarter'] }}"
                                    data-status="{{ $item['currentStatus'] }}"
                                    tabindex="0"
                                >
                                    <td style="font-weight:600;color:var(--s600);">{{ $item['office'] }}</td>
                                    <td>{{ $item['item'] }}</td>
                                    <td style="white-space:nowrap;">{{ $item['targetQuarter'] }}</td>
                                    <td><x-prism.status-badge :status="$item['currentStatus']" /></td>
                                    <td style="color:var(--s500);font-size:12px;">{{ $item['remarks'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Detail panel --}}
            <aside class="detail-panel" aria-live="polite">
                <div class="card-head" style="margin-bottom:14px;">
                    <div>
                        <p class="card-eyebrow">Procurement activity</p>
                        <h2 class="card-title" id="viceStatusTitle">Select an item</h2>
                    </div>
                </div>
                <div id="viceStatusDetails">
                    <div class="detail-empty">
                        <svg viewBox="0 0 24 24"><path d="M15 15l6 6m-6-6A7 7 0 1 0 3 9a7 7 0 0 0 12 6z"/></svg>
                        <p>Click any row to view its timeline, remarks, and add follow-up notes.</p>
                    </div>
                </div>
            </aside>

        </div>

    </div>
</div>

<script type="application/json" id="viceDivisionItemData">@json($divisionItems)</script>

<script>
(function () {
    const items   = JSON.parse(document.getElementById('viceDivisionItemData').textContent);
    const rows    = document.querySelectorAll('[data-vice-status-row]');
    const title   = document.getElementById('viceStatusTitle');
    const details = document.getElementById('viceStatusDetails');
    const counter = document.getElementById('viceStatusVisibleCount');

    const officeFilter  = document.getElementById('viceStatusOfficeFilter');
    const quarterFilter = document.getElementById('viceStatusQuarterFilter');
    const statusFilter  = document.getElementById('viceStatusStatusFilter');

    function statusBadgeClass(s) {
        const map = {
            'On Track': 'badge-on-track',
            'At Risk':  'badge-at-risk',
            'Delayed':  'badge-delayed',
            'Pending':  'badge-pending',
            'In Progress': 'badge-progress',
        };
        return map[s] || 'badge-pending';
    }

    function applyFilters() {
        const o = officeFilter.value;
        const q = quarterFilter.value;
        const s = statusFilter.value;
        let visible = 0;
        rows.forEach(row => {
            const show = (o === 'all' || row.dataset.office === o)
                      && (q === 'all' || row.dataset.quarter === q)
                      && (s === 'all' || row.dataset.status === s);
            row.classList.toggle('hidden-row', !show);
            if (show) visible++;
        });
        counter.textContent = visible + ' shown';
    }

    [officeFilter, quarterFilter, statusFilter].forEach(el =>
        el.addEventListener('change', applyFilters)
    );

    function renderDetail(item) {
        title.textContent = item.item;

        const timeline = (item.timeline || []).map(t => `
            <div class="tl-item">
                <div class="tl-dot-wrap">
                    <div class="tl-dot"></div>
                    <div class="tl-line"></div>
                </div>
                <div class="tl-content">
                    <p class="tl-date">${t.date || ''}</p>
                    <p class="tl-action">${t.action || ''}</p>
                    ${t.note ? `<p class="tl-note">${t.note}</p>` : ''}
                </div>
            </div>
        `).join('');

        details.innerHTML = `
            <div class="meta-row">
                <div class="meta-item">
                    <span class="meta-label">Office</span>
                    <span class="meta-value">${item.office}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Quarter</span>
                    <span class="meta-value">${item.targetQuarter}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status</span>
                    <span class="badge ${statusBadgeClass(item.currentStatus)}">${item.currentStatus}</span>
                </div>
            </div>

            ${item.remarks ? `
            <div style="background:var(--s50);border:1px solid var(--s200);border-radius:10px;padding:12px 14px;font-size:13px;color:var(--s600);line-height:1.6;margin-bottom:16px;">
                <span style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--s400);margin-bottom:4px;">Procurement Remarks</span>
                ${item.remarks}
            </div>` : ''}

            ${timeline ? `
            <div class="section-sep"></div>
            <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--s400);margin-bottom:14px;">Activity Timeline</p>
            <div class="timeline">${timeline}</div>
            ` : ''}

            <div class="section-sep"></div>
            <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--s400);margin-bottom:10px;">VC Follow-up Notes</p>
            <textarea class="notes-area" id="vcNoteInput" placeholder="Add a follow-up note…">${item.vcNote || ''}</textarea>
            <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                <button class="btn-save" id="vcNoteSave">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Save Note
                </button>
            </div>
        `;

        document.getElementById('vcNoteSave').addEventListener('click', () => {
            const note = document.getElementById('vcNoteInput').value;
            item.vcNote = note;
            const btn = document.getElementById('vcNoteSave');
            btn.textContent = '✓ Saved';
            btn.style.background = '#3b6d11';
            setTimeout(() => {
                btn.innerHTML = '<svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><polyline points="20 6 9 17 4 12"/></svg> Save Note';
                btn.style.background = '';
            }, 1800);
        });
    }

    rows.forEach(row => {
        const activate = () => {
            rows.forEach(r => r.classList.remove('selected-row'));
            row.classList.add('selected-row');
            const id = row.dataset.itemId;
            const item = items.find(i => String(i.id) === String(id));
            if (item) renderDetail(item);
        };
        row.addEventListener('click', activate);
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(); }
        });
    });
})();
</script>

</body>
</html>