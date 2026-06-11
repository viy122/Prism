<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Market Scoping | PRISM</title>
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
            display: block;
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
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
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
        .sb-logout svg {
            width: 16px; height: 16px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

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
            --white: #ffffff;
            --s50:   #f8fafc;
            --s100:  #f1f5f9;
            --s200:  #e2e8f0;
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

        /* Page header */
        .page-header {
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 18px;
            padding: 26px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: var(--sh);
        }
        .eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--m);
            margin-bottom: 5px;
        }
        .page-header h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--s900);
            letter-spacing: -.5px;
            line-height: 1.2;
            margin-bottom: 6px;
        }
        .page-header p {
            font-size: 13.5px;
            color: var(--s600);
            line-height: 1.65;
        }

        /* 3-col grid */
        .scoping-grid {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr) 360px;
            gap: 24px;
            align-items: start;
        }

        /* Sticky columns */
        .col-sticky {
            position: sticky;
            top: 86px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Card */
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
            font-size: 16px;
            font-weight: 800;
            color: var(--s900);
            letter-spacing: -.2px;
            margin-bottom: 20px;
        }
        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 20px;
        }
        .card-head .card-title { margin-bottom: 0; }

        /* Filter form */
        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .field-group { display: flex; flex-direction: column; }
        .field-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--s500);
            margin-bottom: 7px;
        }
        .field-input,
        .field-select {
            width: 100%;
            height: 42px;
            border-radius: 10px;
            border: 1px solid var(--s200);
            background: var(--white);
            padding: 0 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--s900);
            font-family: 'Poppins', sans-serif;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }
        .field-input:focus,
        .field-select:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.08);
        }
        .search-wrap { position: relative; }
        .search-wrap svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px; height: 15px;
            stroke: var(--s400); fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            pointer-events: none;
        }
        .search-wrap input { padding-left: 36px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* Item preview */
        .item-preview {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 12px;
            padding: 16px;
        }
        .item-preview-code {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--m);
            margin-bottom: 4px;
        }
        .item-preview-name {
            font-size: 13px;
            font-weight: 800;
            color: var(--s900);
            margin-bottom: 12px;
            line-height: 1.45;
        }
        .item-preview dl { display: flex; flex-direction: column; gap: 7px; }
        .item-preview .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
        }
        .item-preview dt { color: var(--s500); }
        .item-preview dd { font-weight: 700; color: var(--s900); }
        .item-preview-specs {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--s200);
            font-size: 12px;
            color: var(--s600);
            line-height: 1.65;
        }

        /* Buttons */
        .btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            width: 100%;
            border-radius: 10px;
            border: 1px solid rgba(104,16,18,.28);
            background: var(--white);
            font-size: 13px;
            font-weight: 700;
            color: var(--m);
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: border-color .15s, background .15s;
        }
        .btn-reset:hover { border-color: var(--m); background: rgba(104,16,18,.04); }
        .btn-reset svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        .btn-attach {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            width: 100%;
            margin-top: 18px;
            border-radius: 10px;
            background: var(--m);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 3px 12px rgba(104,16,18,.22);
            transition: background .2s;
        }
        .btn-attach:hover { background: var(--m-dk); }
        .btn-attach:disabled {
            background: var(--s200);
            color: var(--s400);
            box-shadow: none;
            cursor: not-allowed;
        }
        .btn-attach svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* Count pills */
        .pill {
            display: inline-flex;
            align-items: center;
            height: 28px;
            padding: 0 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pill-gray   { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
        .pill-maroon { background: rgba(104,16,18,.08); color: var(--m); border: 1px solid rgba(104,16,18,.15); }

        /* Results area */
        .results-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        #marketSmartMessages { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
        #marketResultsGrid   { display: grid; grid-template-columns: 1fr; gap: 14px; }
        #marketShortlist     { display: flex; flex-direction: column; gap: 12px; }
        #marketSummary       { display: flex; flex-direction: column; gap: 12px; }

        /* Drawer */
        #marketReferenceDrawer {
            position: fixed;
            inset: 0;
            z-index: 90;
            display: none;
        }
        #marketReferenceDrawer .overlay {
            position: absolute;
            inset: 0;
            background: rgba(15,23,42,.4);
        }
        #marketReferenceDrawer aside {
            position: absolute;
            right: 0; top: 0;
            width: 100%;
            max-width: 640px;
            height: 100%;
            background: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: -8px 0 48px rgba(15,23,42,.14);
        }
        .drawer-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--s200);
            flex-shrink: 0;
        }
        .drawer-close {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid var(--s200);
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--s600);
            cursor: pointer;
            flex-shrink: 0;
            transition: border-color .15s, color .15s;
            font-family: 'Poppins', sans-serif;
        }
        .drawer-close:hover { border-color: var(--m); color: var(--m); }
        #marketDetailsContent { flex: 1; overflow-y: auto; padding: 24px; }

        /* Responsive */
        @media (max-width: 1280px) {
            .scoping-grid { grid-template-columns: 280px minmax(0,1fr) 320px; gap: 20px; }
        }
        @media (max-width: 1024px) {
            .sb { display: none; }
            body { display: block; }
            .content { padding: 20px 20px 48px; gap: 20px; }
            .scoping-grid { grid-template-columns: 1fr; }
            .col-sticky { position: static; }
            .topbar { padding: 0 20px; }
        }
    </style>
</head>
<body>

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
        <a href="{{ route('office-head.market-scoping') ?? '#' }}" class="active">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            Market Scoping
        </a>
        <a href="{{ route('office-head.budget-proposal') }}">
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
        <span class="topbar-title">Market Scoping</span>
        <span class="topbar-chip">Minimum 3 valid references</span>
    </header>

    <div class="content">

        @php
            $sources            = collect($supplierReferences)->pluck('sourceType')->unique()->sort()->values();
            $brands             = collect($supplierReferences)->pluck('brand')->unique()->sort()->values();
            $categories         = collect($supplierReferences)->pluck('category')->unique()->sort()->values();
            $availabilityOptions = collect($supplierReferences)->pluck('availability')->unique()->sort()->values();
            $initialItem        = collect($proposalItems)->first();
        @endphp

        {{-- Page header --}}
        <div class="page-header">
            <div>
                <p class="eyebrow">Office Head / Dean</p>
                <h1>Market Scoping</h1>
                <p>Compare supplier price references and attach valid sources to the proposal.</p>
            </div>
        </div>

        {{-- 3-col layout --}}
        <div class="scoping-grid">

            {{-- ── LEFT: Filters ── --}}
            <div class="col-sticky">
                <div class="card">
                    <p class="card-eyebrow">Search & Filter</p>
                    <h2 class="card-title">Reference Search</h2>

                    <div class="filter-form">

                        <div class="field-group">
                            <label class="field-label" for="marketItemSelect">Proposal item</label>
                            <select id="marketItemSelect" class="field-select">
                                @foreach ($proposalItems as $item)
                                    <option value="{{ $item['id'] }}">{{ $item['itemName'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="item-preview">
                            <p class="item-preview-code" id="marketItemCode">{{ $initialItem['proposalCode'] ?? '' }}</p>
                            <p class="item-preview-name" id="marketItemTitle">{{ $initialItem['itemName'] ?? '' }}</p>
                            <dl>
                                <div class="row">
                                    <dt>Unit cost</dt>
                                    <dd id="marketItemBudget">PHP {{ number_format($initialItem['estimatedUnitCost'] ?? 0) }}</dd>
                                </div>
                                <div class="row">
                                    <dt>Quantity</dt>
                                    <dd id="marketItemQuantity">{{ $initialItem['quantity'] ?? 0 }} {{ $initialItem['unit'] ?? '' }}</dd>
                                </div>
                            </dl>
                            <p class="item-preview-specs" id="marketItemSpecs">{{ $initialItem['specification'] ?? '' }}</p>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="marketSearchInput">Search</label>
                            <div class="search-wrap">
                                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input id="marketSearchInput" class="field-input" type="search" placeholder="Item, brand, supplier…">
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="field-group">
                                <label class="field-label" for="marketMinPrice">Min price</label>
                                <input id="marketMinPrice" class="field-input" type="number" min="0" placeholder="0">
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="marketMaxPrice">Max price</label>
                                <input id="marketMaxPrice" class="field-input" type="number" min="0" placeholder="Any">
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="marketSourceFilter">Source type</label>
                            <select id="marketSourceFilter" class="field-select">
                                <option value="all">All source types</option>
                                @foreach ($sources as $source)
                                    <option value="{{ $source }}">{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="marketBrandFilter">Brand</label>
                            <select id="marketBrandFilter" class="field-select">
                                <option value="all">All brands</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand }}">{{ $brand }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="marketCategoryFilter">Category</label>
                            <select id="marketCategoryFilter" class="field-select">
                                <option value="all">All categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="marketAvailabilityFilter">Availability</label>
                            <select id="marketAvailabilityFilter" class="field-select">
                                <option value="all">All statuses</option>
                                @foreach ($availabilityOptions as $availability)
                                    <option value="{{ $availability }}">{{ $availability }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="marketKeywordFilter">Spec keywords</label>
                            <input id="marketKeywordFilter" class="field-input" type="text" placeholder="e.g. warranty, HDMI…">
                        </div>

                        <button id="marketResetFilters" class="btn-reset" type="button">
                            <svg viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                            Reset Filters
                        </button>

                    </div>
                </div>
            </div>

            {{-- ── CENTER: Results ── --}}
            <div class="card" style="min-width:0; align-self:start;">
                <div class="results-topbar">
                    <div>
                        <p class="card-eyebrow">Supplier price references</p>
                        <h2 class="card-title" style="margin-bottom:0;">Supplier References</h2>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <span id="marketResultCount"  class="pill pill-gray">0 references</span>
                        <span id="marketSelectedCount" class="pill pill-maroon">0 selected</span>
                    </div>
                </div>

                <div id="marketSmartMessages"></div>
                <div id="marketResultsGrid"></div>
            </div>

            {{-- ── RIGHT: Shortlist + Summary ── --}}
            <div class="col-sticky">

                <div class="card">
                    <div class="card-head">
                        <div>
                            <p class="card-eyebrow">Selected references</p>
                            <h2 class="card-title" style="margin-bottom:0;">Reference Shortlist</h2>
                        </div>
                        <span id="marketValidCount" class="pill pill-gray">0 valid</span>
                    </div>
                    <div id="marketShortlist"></div>
                    <button id="marketAttachButton" class="btn-attach" type="button" disabled>
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Attach to Proposal
                    </button>
                </div>

                <div class="card">
                    <p class="card-eyebrow">Comparison summary</p>
                    <h2 class="card-title">Budget Validation</h2>
                    <div id="marketSummary"></div>
                </div>

            </div>

        </div>{{-- /scoping-grid --}}
    </div>{{-- /content --}}
</div>{{-- /main --}}

{{-- Reference details drawer --}}
<div id="marketReferenceDrawer" aria-hidden="true">
    <div class="overlay" data-market-details-close></div>
    <aside>
        <div class="drawer-head">
            <div style="min-width:0;">
                <p class="card-eyebrow">Supplier reference details</p>
                <h2 id="marketDetailsTitle" style="font-size:19px; font-weight:800; color:var(--s900); margin-top:4px; line-height:1.35;">Reference details</h2>
            </div>
            <button class="drawer-close" type="button" data-market-details-close aria-label="Close">✕</button>
        </div>
        <div id="marketDetailsContent"></div>
    </aside>
</div>

<script type="application/json" id="marketScopingItems">@json($proposalItems)</script>
<script type="application/json" id="marketSupplierReferences">@json($supplierReferences)</script>

</body>
</html>