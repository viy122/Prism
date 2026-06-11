<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Budget Approval | PRISM</title>
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

        /* ─── Two-panel layout ─── */
        .two-panel {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(380px, 0.75fr);
            gap: 20px;
            align-items: start;
        }

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
        tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .12s; cursor: pointer; }
        tbody tr:hover { background: rgba(104,16,18,.03); }
        tbody tr.selected { background: rgba(104,16,18,.06); }

        /* ─── Badges ─── */
        .badge {
            display: inline-flex; align-items: center;
            height: 24px; padding: 0 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-endorsed   { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
        .badge-approved   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
        .badge-returned   { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
        .badge-pending    { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }

        /* ─── Count chip ─── */
        .count-chip {
            display: inline-flex; align-items: center;
            height: 28px; padding: 0 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            background: #e6f1fb; color: #185fa5;
            border: 1px solid #b5d4f4;
        }

        /* ─── Detail panel ─── */
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
            padding: 32px;
            text-align: center;
        }
        .detail-empty svg { width: 36px; height: 36px; stroke: rgba(104,16,18,.5); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
        .detail-empty p { font-size: 13px; color: var(--s500); line-height: 1.7; max-width: 260px; }

        .detail-body { display: flex; flex-direction: column; gap: 16px; }

        /* ─── Detail meta row ─── */
        .detail-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .meta-item {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 10px;
            padding: 12px 14px;
        }
        .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s400); margin-bottom: 4px; }
        .meta-value { font-size: 13px; font-weight: 700; color: var(--s900); }

        /* ─── Finance remark box ─── */
        .remark-box {
            background: #e6f1fb;
            border: 1px solid #b5d4f4;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .remark-box .remark-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #185fa5; margin-bottom: 6px; }
        .remark-box p { font-size: 13px; color: #185fa5; line-height: 1.7; font-weight: 500; }

        /* ─── Items list ─── */
        .items-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); margin-bottom: 8px; }
        .item-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--s200);
            background: var(--white);
            margin-bottom: 6px;
        }
        .item-row:last-child { margin-bottom: 0; }
        .item-name { font-size: 13px; font-weight: 600; color: var(--s900); }
        .item-qty  { font-size: 12px; color: var(--s500); margin-top: 2px; }
        .item-cost { font-size: 13px; font-weight: 700; color: var(--m); white-space: nowrap; }

        /* ─── Action buttons ─── */
        .action-row { display: flex; gap: 10px; }
        .btn {
            flex: 1;
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 44px; padding: 0 20px; border-radius: 10px;
            font-size: 13px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            border: none; transition: opacity .2s, transform .1s;
        }
        .btn:active { transform: scale(.98); }
        .btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .btn-approve { background: #3b6d11; color: #fff; }
        .btn-approve:hover { opacity: .88; }
        .btn-return  { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
        .btn-return:hover { background: #f7c1c1; }

        /* ─── Remarks textarea ─── */
        .remarks-area {
            width: 100%; min-height: 90px; resize: vertical;
            border: 1px solid var(--s200); border-radius: 10px;
            padding: 12px 14px; font-size: 13px; font-family: 'Poppins', sans-serif;
            color: var(--s900); background: var(--white);
            transition: border-color .2s;
            outline: none;
        }
        .remarks-area:focus { border-color: var(--m); }
        .remarks-area::placeholder { color: var(--s400); }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .two-panel { grid-template-columns: 1fr; }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            .main { margin-left: 0; }
            .content { padding: 16px 16px 40px; }
            .topbar { padding: 0 16px; }
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
        <a href="{{ route('chancellor.budget-approval') }}" class="active">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Budget Approval
        </a>
        <a href="{{ route('chancellor.procurement-reports') }}">
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
        <span class="topbar-title">Budget Approval</span>
    </header>

    <div class="content">

        {{-- Page header --}}
        <div class="card">
            <p class="card-eyebrow">Chancellor</p>
            <h1 class="card-title">Budget Approval</h1>
            <p class="card-sub">Review proposals endorsed by Finance Office, inspect item details and market scoping, then approve or return with required remarks.</p>
        </div>

        {{-- Two-panel --}}
        <div class="two-panel">

            {{-- Left: Approval Queue --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Finance-endorsed proposals</p>
                        <h2 class="card-title">Approval Queue</h2>
                    </div>
                    <span class="count-chip">{{ count($proposals) }} endorsed</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Total Amount</th>
                                <th>Date Endorsed</th>
                                <th>Finance Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proposals as $proposal)
                                <tr data-chancellor-proposal-row
                                    data-proposal-id="{{ $proposal['id'] }}"
                                    tabindex="0">
                                    <td style="font-weight:600;color:var(--s600);">{{ $proposal['office'] }}</td>
                                    <td style="font-weight:700;color:var(--m);">PHP {{ number_format($proposal['totalAmount']) }}</td>
                                    <td style="color:var(--s500);white-space:nowrap;">{{ $proposal['dateEndorsed'] }}</td>
                                    <td style="color:var(--s600);max-width:200px;">{{ $proposal['financeRemarks'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right: Detail Panel --}}
            <aside class="card" aria-live="polite" style="position:sticky;top:86px;">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Full proposal details</p>
                        <h2 class="card-title" id="chancellorProposalTitle">Select a proposal</h2>
                    </div>
                </div>

                <div id="chancellorProposalDetails">
                    <div class="detail-empty">
                        <svg viewBox="0 0 24 24"><path d="M15 15l6 6m-11-4a7 7 0 110-14 7 7 0 010 14z"/></svg>
                        <p>Click a proposal row to view items, costs, justifications, market scoping, Finance remarks, and approval trail.</p>
                    </div>
                </div>

                {{-- Action area (hidden until proposal selected) --}}
                <div id="chancellorActionArea" style="display:none;margin-top:16px;display:none;flex-direction:column;gap:10px;">
                    <textarea class="remarks-area" id="chancellorRemarks" placeholder="Enter your remarks (required for return)…"></textarea>
                    <div class="action-row">
                        <button class="btn btn-approve" id="btnApprove" type="button">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Approve
                        </button>
                        <button class="btn btn-return" id="btnReturn" type="button">
                            <svg viewBox="0 0 24 24"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 00-4-4H4"/></svg>
                            Return
                        </button>
                    </div>
                </div>
            </aside>

        </div>

    </div>
</div>

<script type="application/json" id="chancellorProposalData">@json($proposals)</script>

<script>
(function () {
    const proposals   = JSON.parse(document.getElementById('chancellorProposalData').textContent);
    const rows        = document.querySelectorAll('[data-chancellor-proposal-row]');
    const titleEl     = document.getElementById('chancellorProposalTitle');
    const detailsEl   = document.getElementById('chancellorProposalDetails');
    const actionArea  = document.getElementById('chancellorActionArea');
    const remarksEl   = document.getElementById('chancellorRemarks');
    const btnApprove  = document.getElementById('btnApprove');
    const btnReturn   = document.getElementById('btnReturn');
    let   activeId    = null;

    function php(n) {
        return 'PHP ' + Number(n).toLocaleString('en-PH');
    }

    function badgeClass(status) {
        const s = (status || '').toLowerCase();
        if (s.includes('approv'))  return 'badge-approved';
        if (s.includes('return'))  return 'badge-returned';
        if (s.includes('endors'))  return 'badge-endorsed';
        return 'badge-pending';
    }

    function renderDetail(proposal) {
        titleEl.textContent = proposal.office;

        const items = Array.isArray(proposal.items) ? proposal.items : [];

        const itemsHtml = items.length ? items.map(item => `
            <div class="item-row">
                <div>
                    <p class="item-name">${item.name || item.description || 'Item'}</p>
                    <p class="item-qty">Qty: ${item.quantity ?? '—'} &nbsp;|&nbsp; ${item.justification ?? ''}</p>
                </div>
                <span class="item-cost">${php(item.amount ?? item.cost ?? 0)}</span>
            </div>
        `).join('') : `<p style="font-size:13px;color:var(--s400);padding:8px 0;">No items listed.</p>`;

        detailsEl.innerHTML = `
            <div class="detail-body">
                <div class="detail-meta">
                    <div class="meta-item">
                        <p class="meta-label">Total Amount</p>
                        <p class="meta-value">${php(proposal.totalAmount ?? 0)}</p>
                    </div>
                    <div class="meta-item">
                        <p class="meta-label">Date Endorsed</p>
                        <p class="meta-value">${proposal.dateEndorsed ?? '—'}</p>
                    </div>
                    <div class="meta-item">
                        <p class="meta-label">Market Scoping</p>
                        <p class="meta-value">${proposal.marketScoping ?? '—'}</p>
                    </div>
                    <div class="meta-item">
                        <p class="meta-label">Status</p>
                        <p class="meta-value">
                            <span class="badge ${badgeClass(proposal.status)}">${proposal.status ?? 'Endorsed'}</span>
                        </p>
                    </div>
                </div>

                <div class="remark-box">
                    <p class="remark-label">Finance Remarks</p>
                    <p>${proposal.financeRemarks ?? '—'}</p>
                </div>

                <div>
                    <p class="items-label">Proposed Items</p>
                    ${itemsHtml}
                </div>

                ${proposal.approvalTrail ? `
                <div>
                    <p class="items-label">Approval Trail</p>
                    <div style="font-size:13px;color:var(--s600);line-height:1.7;">${proposal.approvalTrail}</div>
                </div>` : ''}
            </div>
        `;

        actionArea.style.display = 'flex';
        remarksEl.value = '';
    }

    rows.forEach(row => {
        row.addEventListener('click', function () {
            const id = this.dataset.proposalId;
            if (activeId === id) return;
            activeId = id;

            rows.forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');

            const proposal = proposals.find(p => String(p.id) === String(id));
            if (proposal) renderDetail(proposal);
        });

        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
        });
    });

    btnApprove.addEventListener('click', function () {
        if (!activeId) return;
        const remarks = remarksEl.value.trim();
        alert('Proposal approved.' + (remarks ? '\nRemarks: ' + remarks : ''));
    });

    btnReturn.addEventListener('click', function () {
        if (!activeId) return;
        const remarks = remarksEl.value.trim();
        if (!remarks) { remarksEl.focus(); remarksEl.style.borderColor = '#a32d2d'; return; }
        remarksEl.style.borderColor = '';
        alert('Proposal returned.\nRemarks: ' + remarks);
    });

    remarksEl.addEventListener('input', function () {
        this.style.borderColor = '';
    });
})();
</script>

</body>
</html>