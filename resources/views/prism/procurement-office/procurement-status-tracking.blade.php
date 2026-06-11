<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procurement Status Tracking | PRISM — Procurement Office</title>
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
            display: flex; align-items: center; gap: 12px;
            padding: 22px 18px 20px; text-decoration: none;
        }
        .sb-logo {
            width: 44px; height: 44px; border-radius: 10px;
            background: #fff; padding: 6px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .sb-logo img { width: 100%; height: 100%; object-fit: contain; }
        .sb-brand-name { font-size: 18px; font-weight: 900; color: #fff; letter-spacing: .5px; }
        .sb-divider { height: 1px; background: rgba(255,255,255,.1); margin: 0 18px; }
        .sb-nav {
            padding: 14px 12px; display: flex;
            flex-direction: column; gap: 3px; flex: 1;
        }
        .sb-nav a {
            display: flex; align-items: center; gap: 11px;
            padding: 12px 14px; border-radius: 10px;
            font-size: 13.5px; font-weight: 600;
            color: rgba(255,255,255,.65); text-decoration: none;
            transition: background .15s, color .15s;
        }
        .sb-nav a:hover { background: rgba(255,255,255,.1); color: #fff; }
        .sb-nav a.active { background: #fff; color: #681012; font-weight: 700; }
        .sb-nav a svg {
            width: 18px; height: 18px; flex-shrink: 0;
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
            flex: 1; min-width: 0; display: flex;
            flex-direction: column; overflow-x: hidden;
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
            flex: 1; display: flex; flex-direction: column; gap: 20px;

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

        /* ─── Filters ─── */
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .filter-group { display: flex; flex-direction: column; gap: 6px; }
        .filter-group label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s500); }
        .filter-select {
            height: 42px; width: 100%; padding: 0 14px;
            border-radius: 10px; border: 1px solid var(--s200);
            background: var(--white); color: var(--s700);
            font-size: 13px; font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer; outline: none;
            transition: border-color .2s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }
        .filter-select:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }

        /* ─── Count chip ─── */
        .count-chip {
            display: inline-flex; align-items: center;
            height: 28px; padding: 0 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            background: var(--s100); color: var(--s700);
            border: 1px solid var(--s200);
        }

        /* ─── Table ─── */
        .table-wrap {
            border-radius: 12px; border: 1px solid var(--s200);
            overflow: auto; max-height: 65vh;
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
        tbody td { padding: 12px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .12s; }
        tbody tr:hover { background: rgba(104,16,18,.03); }
        tbody tr.row-delayed { background: #fff5f5; }
        tbody tr.row-delayed:hover { background: #fee2e2; }

        /* ─── Status badges ─── */
        .badge {
            display: inline-flex; align-items: center;
            height: 24px; padding: 0 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-completed   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
        .badge-in-progress { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
        .badge-pending     { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
        .badge-delayed     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
        .badge-overdue     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

        /* ─── Inline update controls ─── */
        .update-cell { display: flex; flex-direction: column; gap: 7px; min-width: 200px; }

        .inline-select {
            height: 36px; width: 100%; padding: 0 10px;
            border-radius: 8px; border: 1px solid var(--s200);
            background: var(--white); color: var(--s700);
            font-size: 12px; font-weight: 500;
            font-family: 'Poppins', sans-serif;
            outline: none; cursor: pointer;
            transition: border-color .2s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }
        .inline-select:focus { border-color: var(--m); }

        .inline-textarea {
            width: 100%; padding: 8px 10px;
            border-radius: 8px; border: 1px solid var(--s200);
            background: var(--white); color: var(--s700);
            font-size: 12px; font-family: 'Poppins', sans-serif;
            resize: vertical; min-height: 60px; outline: none;
            transition: border-color .2s; line-height: 1.5;
        }
        .inline-textarea:focus { border-color: var(--m); }
        .inline-textarea::placeholder { color: var(--s300); }

        .btn-save-inline {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 6px; height: 34px; padding: 0 14px; border-radius: 8px;
            background: var(--white); color: var(--m);
            font-size: 12px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            border: 1px solid rgba(104,16,18,.3);
            transition: background .15s, border-color .15s; white-space: nowrap;
        }
        .btn-save-inline:hover { background: rgba(104,16,18,.05); border-color: var(--m); }
        .btn-save-inline svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .btn-save-inline.saved { background: #eaf3de; color: #3b6d11; border-color: #c0dd97; }

        /* ─── Remarks display ─── */
        .remarks-text { font-size: 12px; color: var(--s500); line-height: 1.6; max-width: 200px; }
        .remarks-text:empty::before { content: '—'; color: var(--s300); }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .sb { display: none; }
            .main { margin-left: 0; }
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
    <a class="sb-brand" href="{{ route('procurement-office.dashboard') }}">
        <div class="sb-logo">
            <img src="{{ asset('images/bsu-seal.png') }}" alt="BSU seal"
                 onerror="this.parentElement.innerHTML='🎓'">
        </div>
        <span class="sb-brand-name">PRISM</span>
    </a>

    <div class="sb-divider"></div>

    <nav class="sb-nav">
        <a href="{{ route('procurement-office.dashboard') }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('procurement-office.purchase-request-management') }}">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Purchase Request Management
        </a>
        <a href="{{ route('procurement-office.procurement-status-tracking') }}" class="active">
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
        <span class="topbar-title">Procurement Status Tracking</span>
    </header>

    <div class="content">

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Procurement Office</p>
            <h1 class="card-title">Procurement Status Tracking</h1>
            <p class="card-sub">Monitor approved items being processed, update statuses, and record remarks for requesting offices.</p>
        </div>

        {{-- Filters --}}
        <div class="card">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="filterOffice">Office</label>
                    <select class="filter-select" id="filterOffice">
                        <option value="all">All offices</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office }}">{{ $office }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterQuarter">Quarter</label>
                    <select class="filter-select" id="filterQuarter">
                        <option value="all">All quarters</option>
                        @foreach ($quarters as $quarter)
                            <option value="{{ $quarter }}">{{ $quarter }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterStatus">Status</label>
                    <select class="filter-select" id="filterStatus">
                        <option value="all">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Tracker table --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Approved items being processed</p>
                    <h2 class="card-title">Procurement Item Tracker</h2>
                </div>
                <span class="count-chip" id="visibleCount">{{ count($trackingItems) }} shown</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Item</th>
                            <th>Approved Amount</th>
                            <th>Target Quarter</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody id="trackerBody">
                        @foreach ($trackingItems as $item)
                            <tr
                                data-procurement-track-row
                                data-office="{{ $item['office'] }}"
                                data-quarter="{{ $item['targetQuarter'] }}"
                                data-status="{{ $item['currentStatus'] }}"
                                class="{{ strtolower($item['currentStatus']) === 'delayed' ? 'row-delayed' : '' }}"
                            >
                                <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $item['office'] }}</td>
                                <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;">{{ $item['item'] }}</td>
                                <td style="font-size:13px;font-weight:600;color:var(--s700);white-space:nowrap;">PHP {{ number_format($item['approvedAmount']) }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $item['targetQuarter'] }}</td>
                                <td>
                                    @php
                                        $slug = match(strtolower($item['currentStatus'])) {
                                            'completed'   => 'badge-completed',
                                            'in progress' => 'badge-in-progress',
                                            'pending'     => 'badge-pending',
                                            'delayed'     => 'badge-delayed',
                                            default       => 'badge-overdue',
                                        };
                                    @endphp
                                    <span class="badge {{ $slug }}" data-track-status-pill>{{ $item['currentStatus'] }}</span>
                                </td>
                                <td>
                                    <p class="remarks-text" data-track-remarks-display>{{ $item['remarks'] }}</p>
                                </td>
                                <td>
                                    <div class="update-cell">
                                        <select class="inline-select" data-track-status-select aria-label="Update status for {{ $item['item'] }}">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status }}" @selected($status === $item['currentStatus'])>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <textarea class="inline-textarea" rows="2" data-track-remarks-input placeholder="Add a remark…">{{ $item['remarks'] }}</textarea>
                                        <button class="btn-save-inline" type="button" data-track-update>
                                            <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                            Save
                                        </button>
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
    /* ─── Filters ─── */
    const filterOffice  = document.getElementById('filterOffice');
    const filterQuarter = document.getElementById('filterQuarter');
    const filterStatus  = document.getElementById('filterStatus');
    const visibleCount  = document.getElementById('visibleCount');
    const rows          = document.querySelectorAll('[data-procurement-track-row]');

    function applyFilters() {
        const office  = filterOffice.value;
        const quarter = filterQuarter.value;
        const status  = filterStatus.value;
        let visible   = 0;

        rows.forEach(row => {
            const match =
                (office  === 'all' || row.dataset.office  === office)  &&
                (quarter === 'all' || row.dataset.quarter === quarter) &&
                (status  === 'all' || row.dataset.status  === status);

            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        visibleCount.textContent = visible + ' shown';
    }

    filterOffice.addEventListener('change',  applyFilters);
    filterQuarter.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change',  applyFilters);

    /* ─── Inline save ─── */
    function getBadgeClass(status) {
        const s = status.toLowerCase();
        if (s === 'completed')   return 'badge-completed';
        if (s === 'in progress') return 'badge-in-progress';
        if (s === 'pending')     return 'badge-pending';
        if (s === 'delayed')     return 'badge-delayed';
        return 'badge-overdue';
    }

    document.querySelectorAll('[data-track-update]').forEach(btn => {
        btn.addEventListener('click', function () {
            const row      = btn.closest('[data-procurement-track-row]');
            const sel      = row.querySelector('[data-track-status-select]');
            const textarea = row.querySelector('[data-track-remarks-input]');
            const pill     = row.querySelector('[data-track-status-pill]');
            const display  = row.querySelector('[data-track-remarks-display]');
            const newStatus = sel.value;

            // Update badge
            pill.textContent = newStatus;
            pill.className   = 'badge ' + getBadgeClass(newStatus);

            // Update remarks display
            display.textContent = textarea.value;

            // Update row data attrs for filter
            row.dataset.status = newStatus;

            // Delayed row highlight
            row.classList.toggle('row-delayed', newStatus.toLowerCase() === 'delayed');

            // Button feedback
            btn.classList.add('saved');
            btn.innerHTML = `<svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="20 6 9 17 4 12"/></svg> Saved`;
            setTimeout(() => {
                btn.classList.remove('saved');
                btn.innerHTML = `<svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save`;
            }, 2000);

            // Re-run filters so count stays accurate
            applyFilters();
        });
    });
})();
</script>

</body>
</html>