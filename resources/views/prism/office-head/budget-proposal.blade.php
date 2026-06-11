<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Budget Proposal | PRISM</title>
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
            --gold:  #c8922a;
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
           PAGE CONTENT
        ═══════════════════════════════════════ */
        .content {
            padding: 32px 32px 64px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ─── Workflow steps ─── */
        .steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .step {
            border-radius: 14px;
            border: 1px solid var(--s200);
            background: var(--white);
            padding: 16px 18px;
            box-shadow: var(--sh);
        }
        .step.active {
            border-color: rgba(104,16,18,.2);
            background: rgba(104,16,18,.04);
        }
        .step.accent {
            border-color: rgba(200,146,42,.3);
            background: rgba(200,146,42,.08);
        }
        .step-num {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .step.active .step-num,
        .step.accent .step-num { color: var(--m); }
        .step:not(.active):not(.accent) .step-num { color: var(--s400); }
        .step-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
            line-height: 1.4;
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
            line-height: 1.55;
        }
        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        /* ─── Badges / pills ─── */
        .badge {
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
        .badge-gray   { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
        .badge-maroon { background: rgba(104,16,18,.08); color: var(--m); border: 1px solid rgba(104,16,18,.15); }
        .badge-gold   { background: rgba(200,146,42,.12); color: #92610a; border: 1px solid rgba(200,146,42,.25); }

        /* ─── Form fields ─── */
        .field-group { display: flex; flex-direction: column; gap: 7px; }
        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--s700);
        }
        .field-input,
        .field-select {
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--s300);
            background: var(--white);
            padding: 0 14px;
            font-size: 14px;
            font-weight: 500;
            color: var(--s900);
            font-family: 'Poppins', sans-serif;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
            width: 100%;
        }
        .field-input:focus,
        .field-select:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.08);
        }
        .field-input.readonly {
            background: var(--s50);
            font-weight: 700;
            color: var(--m);
        }

        /* ─── Form grid ─── */
        .form-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .item-form-grid {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .item-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 16px;
            align-items: end;
        }

        /* ─── Buttons ─── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 20px;
            border-radius: 10px;
            background: var(--m);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 10px rgba(104,16,18,.2);
            transition: background .2s;
            white-space: nowrap;
        }
        .btn-primary:hover { background: var(--m-dk); }
        .btn-primary svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 20px;
            border-radius: 10px;
            background: var(--white);
            color: var(--m);
            font-size: 13px;
            font-weight: 700;
            border: 1px solid rgba(104,16,18,.3);
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: border-color .15s, background .15s;
            white-space: nowrap;
        }
        .btn-outline:hover { border-color: var(--m); background: rgba(104,16,18,.04); }
        .btn-outline svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 20px;
            border-radius: 10px;
            background: var(--white);
            color: var(--s700);
            font-size: 13px;
            font-weight: 700;
            border: 1px solid var(--s200);
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: border-color .15s, color .15s;
            white-space: nowrap;
        }
        .btn-ghost:hover { border-color: var(--m); color: var(--m); }
        .btn-ghost svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        .btn-full {
            width: 100%;
            height: 46px;
        }

        /* ─── Main 2-col ─── */
        .main-grid {
            display: grid;
            grid-template-columns: minmax(0,1fr) 360px;
            gap: 24px;
            align-items: start;
        }
        .col-left  { display: flex; flex-direction: column; gap: 24px; }
        .col-right {
            position: sticky;
            top: 86px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ─── Summary stats ─── */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .summary-stat {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 12px;
            padding: 16px;
        }
        .summary-stat dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 6px;
        }
        .summary-stat dd {
            font-size: 26px;
            font-weight: 800;
            color: var(--s900);
            line-height: 1;
        }
        .summary-stat dd.maroon { color: var(--m); }
        .summary-stat dd.sm { font-size: 17px; }

        /* ─── Reference list ─── */
        .ref-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 420px;
            overflow-y: auto;
            padding-right: 2px;
        }
        .ref-item {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .ref-item-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 6px;
        }
        .ref-item-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
            line-height: 1.4;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .ref-item-unit {
            font-size: 11px;
            font-weight: 700;
            color: var(--m);
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 99px;
            padding: 2px 10px;
            flex-shrink: 0;
        }
        .ref-item-meta {
            font-size: 12px;
            color: var(--s500);
        }
        .ref-item-meta.warn { color: #92400e; font-weight: 600; }

        /* ─── Table ─── */
        .table-wrap {
            border: 1px solid var(--s200);
            border-radius: 12px;
            overflow: hidden;
        }
        .table-scroll { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        thead {
            background: var(--s50);
        }
        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--s500);
            white-space: nowrap;
            border-bottom: 1px solid var(--s200);
        }
        thead th:last-child { text-align: right; }
        tbody tr { border-bottom: 1px solid var(--s100); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--s50); }
        tbody td {
            padding: 14px 16px;
            color: var(--s700);
            vertical-align: middle;
        }
        tbody td:last-child { text-align: right; }
        .td-name { font-weight: 700; color: var(--s900); max-width: 240px; }
        .td-amount { font-weight: 700; color: var(--s900); }
        .td-maroon { font-weight: 700; color: var(--m); }

        /* ─── Responsive ─── */
        @media (max-width: 1280px) {
            .main-grid { grid-template-columns: minmax(0,1fr) 320px; }
            .form-grid-4 { grid-template-columns: 1fr 1fr; }
            .steps { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            body { display: block; }
            .content { padding: 20px 20px 48px; gap: 20px; }
            .main-grid { grid-template-columns: 1fr; }
            .col-right { position: static; }
            .topbar { padding: 0 20px; }
            .steps { grid-template-columns: repeat(2, 1fr); }
            .item-form-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .form-grid-4 { grid-template-columns: 1fr; }
            .item-form-grid { grid-template-columns: 1fr; }
            .item-form-grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

@php
    $itemCount = count($encodedItems);
    $scopingReferenceCount = collect($encodedItems)->sum(fn ($item) => count($item['scoping']));
    $missingScopingCount = collect($encodedItems)->filter(fn ($item) => empty($item['scoping']))->count();
    $proposalTotal = collect($encodedItems)->sum('totalCost');
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
        <a href="{{ route('office-head.budget-proposal') }}" class="active">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Budget Proposal
        </a>
        <a href="{{ route('office-head.my-proposals') }}">
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
        <span class="topbar-title">Budget Proposal</span>
        <span class="topbar-chip">Annual Procurement Plan</span>
    </header>

    <div class="content">

        {{-- Main 2-col --}}
        <div class="main-grid">

            {{-- ── LEFT ── --}}
            <div class="col-left">

                {{-- Proposal details form --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <p class="card-eyebrow">Proposal info</p>
                            <h2 class="card-title">Proposal Details</h2>
                            <p class="card-sub">Basic information for the annual procurement budget proposal.</p>
                        </div>
                        <span class="badge badge-gray">Draft</span>
                    </div>

                    <div class="form-grid-4">
                        <div class="field-group">
                            <label class="field-label" for="officeName">Office / College</label>
                            <input id="officeName" class="field-input" value="{{ $proposalForm['officeName'] }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label" for="fiscalYear">Fiscal Year</label>
                            <input id="fiscalYear" class="field-input" value="{{ $proposalForm['fiscalYear'] }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label" for="datePrepared">Date Prepared</label>
                            <input id="datePrepared" class="field-input" type="date" value="{{ $proposalForm['date'] }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label" for="proposedBudget">Proposed Budget</label>
                            <input id="proposedBudget" class="field-input readonly" readonly value="PHP {{ number_format($proposalForm['totalProposedBudget']) }}">
                        </div>
                    </div>
                </div>

                {{-- Procurement items form --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <p class="card-eyebrow">Procurement items</p>
                            <h2 class="card-title">Add Items</h2>
                            <p class="card-sub">Encode item details, then run market scoping for price references.</p>
                        </div>
                        <button id="runMarketScopingButton" type="button" class="btn-primary">
                            <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            Run Market Scoping
                        </button>
                    </div>

                    <form id="proposalItemForm">
                        <input id="itemId" name="itemId" type="hidden">

                        <div class="item-form-grid">
                            <div class="field-group">
                                <label class="field-label" for="itemDescription">Item Description</label>
                                <input id="itemDescription" name="description" class="field-input" placeholder="e.g. Laptop computer for laboratory use" required>
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="itemUnit">Unit</label>
                                <select id="itemUnit" name="unit" class="field-select">
                                    <option>unit</option>
                                    <option>set</option>
                                    <option>lot</option>
                                    <option>piece</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="itemQuantity">Quantity</label>
                                <input id="itemQuantity" name="quantity" class="field-input" type="number" min="1" value="1" required>
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="itemUnitCost">Unit Cost</label>
                                <input id="itemUnitCost" name="estimatedUnitCost" class="field-input" type="number" min="0" value="0" required>
                            </div>
                        </div>

                        <div class="item-form-grid-2">
                            <div class="field-group" style="grid-column: span 2;">
                                <label class="field-label" for="itemJustification">Purpose / Justification</label>
                                <input id="itemJustification" name="justification" class="field-input" placeholder="Short procurement justification">
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="itemQuarter">Target Quarter</label>
                                <select id="itemQuarter" name="targetQuarter" class="field-select">
                                    <option>Q1</option>
                                    <option>Q2</option>
                                    <option>Q3</option>
                                    <option>Q4</option>
                                </select>
                            </div>
                            <div style="display:flex; align-items:flex-end;">
                                <button id="saveItemButton" type="submit" class="btn-outline btn-full">
                                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Add Item
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Line items table --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <p class="card-eyebrow">Encoded items</p>
                            <h2 class="card-title">Proposal Line Items</h2>
                            <p class="card-sub"><span id="proposalItemCount">{{ $itemCount }}</span> procurement items encoded.</p>
                        </div>
                        <button type="button" class="btn-ghost">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export Draft
                        </button>
                    </div>

                    <div class="table-wrap">
                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty / Unit</th>
                                        <th>Unit Cost</th>
                                        <th>Total</th>
                                        <th>Quarter</th>
                                        <th>Market Scoping</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="encodedItemsTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>{{-- /col-left --}}

            {{-- ── RIGHT ── --}}
            <div class="col-right">

                {{-- Submission readiness --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <p class="card-eyebrow">Readiness check</p>
                            <h2 class="card-title">Submission Readiness</h2>
                            <p class="card-sub">Market scoping must support all encoded items.</p>
                        </div>
                        <span id="proposalReadyBadge" class="badge badge-gray">Draft</span>
                    </div>

                    <dl class="summary-grid">
                        <div class="summary-stat">
                            <dt>Items</dt>
                            <dd id="proposalSummaryItems">{{ $itemCount }}</dd>
                        </div>
                        <div class="summary-stat">
                            <dt>References</dt>
                            <dd id="proposalSummaryReferences" class="maroon">{{ $scopingReferenceCount }}</dd>
                        </div>
                        <div class="summary-stat">
                            <dt>Missing scoping</dt>
                            <dd id="proposalSummaryMissing">{{ $missingScopingCount }}</dd>
                        </div>
                        <div class="summary-stat">
                            <dt>Total amount</dt>
                            <dd id="proposalSummaryTotal" class="sm">PHP {{ number_format($proposalTotal) }}</dd>
                        </div>
                    </dl>

                    <button id="submitProposalButton" type="button" class="btn-primary btn-full">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Submit Proposal
                    </button>
                </div>

                {{-- Market references --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <p class="card-eyebrow">Scoping summary</p>
                            <h2 class="card-title">Market References</h2>
                        </div>
                        <span id="proposalReferenceTotalBadge" class="badge badge-gold">{{ $scopingReferenceCount }} total</span>
                    </div>

                    <div class="ref-list" id="proposalReferenceList">
                        @foreach ($encodedItems as $item)
                            @php
                                $references = collect($item['scoping']);
                                $lowestReference = $references->sortBy('price')->first();
                            @endphp
                            <div class="ref-item">
                                <div class="ref-item-head">
                                    <p class="ref-item-name">{{ $item['description'] }}</p>
                                    <span class="ref-item-unit">{{ $item['unit'] }}</span>
                                </div>
                                <p class="text-xs" style="color:var(--s500); margin-bottom:4px;">
                                    {{ $item['targetQuarter'] }} &middot; {{ count($item['scoping']) }} references
                                </p>
                                @if ($lowestReference)
                                    <p class="ref-item-meta">Lowest: {{ $lowestReference['supplierName'] }} &middot; PHP {{ number_format($lowestReference['price']) }}</p>
                                @else
                                    <p class="ref-item-meta warn">Needs market scoping before submission.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /col-right --}}

        </div>{{-- /main-grid --}}

    </div>{{-- /content --}}
</div>{{-- /main --}}

<script type="application/json" id="initialProposalItems">@json($encodedItems)</script>

</body>
</html>