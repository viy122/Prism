<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Request Management | PRISM — Procurement Office</title>
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

        /* ─── Two-column layout ─── */
        .pr-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 420px;
            gap: 20px;
            align-items: start;
        }

        /* ─── Table ─── */
        .table-wrap {
            border-radius: 12px; border: 1px solid var(--s200);
            overflow: auto; max-height: 62vh;
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
        tbody tr.selected { background: rgba(104,16,18,.07); }
        tbody tr.selected td:first-child {
            border-left: 3px solid var(--m);
        }

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

        /* ─── Detail panel ─── */
        .detail-panel {
            display: flex; flex-direction: column; gap: 16px;
        }
        .detail-empty {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 10px; min-height: 220px;
            border-radius: 12px;
            border: 1.5px dashed var(--s300);
            background: var(--s50);
            text-align: center; padding: 32px;
        }
        .detail-empty svg { width: 36px; height: 36px; stroke: rgba(104,16,18,.4); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
        .detail-empty p { font-size: 13px; color: var(--s400); line-height: 1.6; max-width: 220px; }

        .detail-content { display: none; flex-direction: column; gap: 16px; }
        .detail-content.visible { display: flex; }

        /* ─── PDF preview ─── */
        .pdf-preview {
            border-radius: 12px; border: 1px solid var(--s200);
            background: var(--s50);
            overflow: hidden;
            aspect-ratio: 8.5 / 11;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .pdf-preview iframe { width: 100%; height: 100%; border: none; }
        .pdf-placeholder {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 8px; width: 100%; height: 100%;
            color: var(--s400);
        }
        .pdf-placeholder svg { width: 40px; height: 40px; stroke: var(--s300); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
        .pdf-placeholder span { font-size: 12px; font-weight: 600; }

        /* ─── Detail fields ─── */
        .detail-fields {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
        }
        .detail-field {
            background: var(--s50); border: 1px solid var(--s200);
            border-radius: 10px; padding: 10px 14px;
        }
        .detail-field.full { grid-column: 1 / -1; }
        .detail-field label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s400); display: block; margin-bottom: 3px; }
        .detail-field span { font-size: 13px; font-weight: 600; color: var(--s700); }

        /* ─── Status controls ─── */
        .status-control {
            display: flex; flex-direction: column; gap: 10px;
        }
        .status-control label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s500); }
        .status-select {
            width: 100%; height: 42px; padding: 0 14px;
            border-radius: 10px; border: 1px solid var(--s200);
            background: var(--white); color: var(--s700);
            font-size: 13px; font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer; outline: none;
            transition: border-color .2s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        .status-select:focus { border-color: var(--m); }

        .remarks-textarea {
            width: 100%; padding: 12px 14px;
            border-radius: 10px; border: 1px solid var(--s200);
            background: var(--white); color: var(--s700);
            font-size: 13px; font-family: 'Poppins', sans-serif;
            resize: vertical; min-height: 80px; outline: none;
            transition: border-color .2s; line-height: 1.6;
        }
        .remarks-textarea:focus { border-color: var(--m); }
        .remarks-textarea::placeholder { color: var(--s300); }

        .btn-save {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 42px; padding: 0 22px; border-radius: 10px;
            background: var(--m); color: #fff;
            font-size: 13px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            border: none; transition: background .2s; white-space: nowrap;
            width: 100%;
        }
        .btn-save:hover { background: var(--m-dk); }
        .btn-save svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Activity log ─── */
        .activity-log { display: flex; flex-direction: column; gap: 1px; }
        .activity-item {
            display: flex; gap: 12px; align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid var(--s100);
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--gold); flex-shrink: 0; margin-top: 5px;
        }
        .activity-item p { font-size: 12.5px; color: var(--s600); line-height: 1.6; }
        .activity-item time { font-size: 11px; color: var(--s400); display: block; margin-top: 2px; }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .pr-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            .main { margin-left: 0; }
            body { display: block; }
            .content { padding: 16px 16px 40px; }
            .topbar { padding: 0 16px; }
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
        <a href="{{ route('procurement-office.purchase-request-management') }}" class="active">
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
        <span class="topbar-title">Purchase Request Management</span>
    </header>

    <div class="content">

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Procurement Office</p>
            <h1 class="card-title">Purchase Request Management</h1>
            <p class="card-sub">Review uploaded PRs, update status, add remarks, and track activity history.</p>
        </div>

        {{-- Two-column: Queue + Detail --}}
        <div class="pr-grid">

            {{-- Left: PR Queue --}}
            <div class="card" style="padding-bottom: 22px;">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Uploaded PRs</p>
                        <h2 class="card-title">Purchase Request Queue</h2>
                    </div>
                    <span class="count-chip">{{ count($purchaseRequests) }} PRs</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>PR No.</th>
                                <th>Item</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseRequests as $request)
                                <tr data-procurement-pr-row data-pr-id="{{ $request['id'] }}" tabindex="0">
                                    <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $request['office'] }}</td>
                                    <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $request['prNumber'] }}</td>
                                    <td style="font-size:13px;color:var(--s900);font-weight:500;">{{ $request['item'] }}</td>
                                    <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $request['dateSubmitted'] }}</td>
                                    <td>
                                        @php
                                            $slug = match(strtolower($request['currentStatus'])) {
                                                'completed'   => 'badge-completed',
                                                'in progress' => 'badge-in-progress',
                                                'pending'     => 'badge-pending',
                                                default       => 'badge-overdue',
                                            };
                                        @endphp
                                        <span class="badge {{ $slug }}" data-pr-row-status="{{ $request['id'] }}">{{ $request['currentStatus'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right: Detail Panel --}}
            <div class="card detail-panel">
                <div class="card-head" style="margin-bottom: 14px;">
                    <div>
                        <p class="card-eyebrow">PR Details</p>
                        <h2 class="card-title" id="procurementPrTitle">Select a PR</h2>
                    </div>
                </div>

                {{-- Empty state --}}
                <div class="detail-empty" id="detailEmpty">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <p>Click any PR row to view details, update status, and add remarks.</p>
                </div>

                {{-- Filled state --}}
                <div class="detail-content" id="detailContent">

                    {{-- PDF Preview --}}
                    <div class="pdf-preview" id="pdfPreview">
                        <div class="pdf-placeholder">
                            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <span>No PDF attached</span>
                        </div>
                    </div>

                    {{-- OCR Fields --}}
                    <div class="detail-fields" id="detailFields">
                        <div class="detail-field">
                            <label>Office</label>
                            <span id="fieldOffice">—</span>
                        </div>
                        <div class="detail-field">
                            <label>PR Number</label>
                            <span id="fieldPrNumber">—</span>
                        </div>
                        <div class="detail-field full">
                            <label>Item / Description</label>
                            <span id="fieldItem">—</span>
                        </div>
                        <div class="detail-field">
                            <label>Date Submitted</label>
                            <span id="fieldDate">—</span>
                        </div>
                        <div class="detail-field">
                            <label>Current Status</label>
                            <span id="fieldStatus">—</span>
                        </div>
                    </div>

                    {{-- Status Update --}}
                    <div class="status-control">
                        <label>Update Status</label>
                        <select class="status-select" id="statusSelect">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Overdue">Overdue</option>
                        </select>
                    </div>

                    {{-- Remarks --}}
                    <div class="status-control">
                        <label>Remarks</label>
                        <textarea class="remarks-textarea" id="remarksInput" placeholder="Add a remark or note…"></textarea>
                    </div>

                    {{-- Save --}}
                    <button class="btn-save" id="btnSave" type="button">
                        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Changes
                    </button>

                    {{-- Activity Log --}}
                    <div>
                        <div class="card-head" style="margin-bottom:10px;">
                            <div>
                                <p class="card-eyebrow">History</p>
                                <h3 class="card-title" style="font-size:14px;">Activity Log</h3>
                            </div>
                        </div>
                        <div class="activity-log" id="activityLog">
                            {{-- Populated via JS --}}
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

<script type="application/json" id="procurementRequestData">@json($purchaseRequests)</script>

<script>
(function () {
    const data        = JSON.parse(document.getElementById('procurementRequestData').textContent);
    const rows        = document.querySelectorAll('[data-procurement-pr-row]');
    const emptyEl     = document.getElementById('detailEmpty');
    const contentEl   = document.getElementById('detailContent');
    const titleEl     = document.getElementById('procurementPrTitle');
    const statusSel   = document.getElementById('statusSelect');
    const remarksIn   = document.getElementById('remarksInput');
    const btnSave     = document.getElementById('btnSave');
    const activityLog = document.getElementById('activityLog');

    // In-memory activity log per PR id
    const logs = {};
    data.forEach(pr => { logs[pr.id] = []; });

    let activePrId = null;

    function getBadgeClass(status) {
        const s = status.toLowerCase();
        if (s === 'completed')   return 'badge-completed';
        if (s === 'in progress') return 'badge-in-progress';
        if (s === 'pending')     return 'badge-pending';
        return 'badge-overdue';
    }

    function formatNow() {
        return new Date().toLocaleString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit'
        });
    }

    function renderLog(prId) {
        activityLog.innerHTML = '';
        const entries = logs[prId];
        if (!entries.length) {
            activityLog.innerHTML = '<p style="font-size:12px;color:var(--s400);padding:8px 0;">No activity yet.</p>';
            return;
        }
        entries.slice().reverse().forEach(e => {
            activityLog.innerHTML += `
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                        <p>${e.text}</p>
                        <time>${e.time}</time>
                    </div>
                </div>`;
        });
    }

    function openPr(pr) {
        activePrId = pr.id;

        // Highlight row
        rows.forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-pr-id="${pr.id}"]`).classList.add('selected');

        // Update title
        titleEl.textContent = pr.prNumber;

        // Fill fields
        document.getElementById('fieldOffice').textContent   = pr.office;
        document.getElementById('fieldPrNumber').textContent = pr.prNumber;
        document.getElementById('fieldItem').textContent     = pr.item;
        document.getElementById('fieldDate').textContent     = pr.dateSubmitted;
        document.getElementById('fieldStatus').textContent   = pr.currentStatus;

        // Set status select
        statusSel.value = pr.currentStatus;

        // Clear remarks
        remarksIn.value = '';

        // Show content
        emptyEl.style.display   = 'none';
        contentEl.classList.add('visible');

        renderLog(pr.id);
    }

    rows.forEach(row => {
        row.addEventListener('click', () => {
            const pr = data.find(p => String(p.id) === row.dataset.prId);
            if (pr) openPr(pr);
        });
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                row.click();
            }
        });
    });

    btnSave.addEventListener('click', () => {
        if (!activePrId) return;
        const pr     = data.find(p => p.id == activePrId);
        const newSt  = statusSel.value;
        const remark = remarksIn.value.trim();

        const changed = pr.currentStatus !== newSt;
        pr.currentStatus = newSt;

        // Update badge in table
        const badgeEl = document.querySelector(`[data-pr-row-status="${activePrId}"]`);
        if (badgeEl) {
            badgeEl.textContent  = newSt;
            badgeEl.className    = 'badge ' + getBadgeClass(newSt);
        }

        // Update field
        document.getElementById('fieldStatus').textContent = newSt;

        // Log
        if (changed) {
            logs[activePrId].push({ text: `Status changed to <strong>${newSt}</strong>`, time: formatNow() });
        }
        if (remark) {
            logs[activePrId].push({ text: `Remark: ${remark}`, time: formatNow() });
            remarksIn.value = '';
        }
        if (!changed && !remark) {
            logs[activePrId].push({ text: 'No changes made.', time: formatNow() });
        }

        renderLog(activePrId);
    });
})();
</script>

</body>
</html>