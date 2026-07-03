@extends('prism.layouts.office-head')
@section('title', 'Budget Proposal')

@php
    $isReadOnly            = $isReadOnly ?? false;
    $proposalStatus        = $proposalStatus ?? 'draft';
    $itemCount             = count($encodedItems);
    $scopingReferenceCount = collect($encodedItems)->sum(fn ($item) => count($item['scoping']));
    $missingScopingCount   = collect($encodedItems)->filter(fn ($item) => empty($item['scoping']))->count();
    $proposalTotal         = collect($encodedItems)->sum('totalCost');
@endphp

@push('page-css')
<style>
        /* ══ TOP GRID ══ */
        .top-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 16px;
            align-items: start;
        }
        .col-left  { display: flex; flex-direction: column; gap: 16px; }
        .col-right {
            position: sticky;
            top: 22px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ══ CARD ══ */
        .card {
            background: var(--white);
            border: 1px solid var(--border2);
            border-radius: var(--r);
            box-shadow: var(--sh);
            overflow: hidden;
        }
        .card-head {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 22px 14px;
            border-bottom: 1px solid var(--border2);
            flex-wrap: wrap;
        }
        .card-head-icon {
            width: 32px; height: 32px; border-radius: 9px;
            background: var(--crimson-mid); border: 1px solid var(--crimson-border);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .card-head-icon i { font-size: 17px; color: var(--crimson); }
        .card-head-text { flex: 1; min-width: 0; }
        .card-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 2px; }
        .card-title   { font-size: 14px; font-weight: 800; color: var(--txt); letter-spacing: -.2px; }
        .card-sub     { font-size: 12px; color: var(--txt3); margin-top: 2px; }
        .card-body    { padding: 18px 22px; }

        /* ── Badges ── */
        .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .badge-gray  { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
        .badge-green { background: var(--green-bg); color: var(--green); border: 1px solid #bbf7d0; }
        .badge-blue  { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }

        /* ── Form fields ── */
        .field-group { display: flex; flex-direction: column; gap: 5px; }
        .field-label { font-size: 9px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--txt3); }
        .field-input,
        .field-select {
            height: 40px; border-radius: var(--r-sm);
            border: 1px solid var(--border2); background: var(--bg);
            padding: 0 12px; font-size: 13px; font-weight: 500; color: var(--txt);
            font-family: 'Poppins', sans-serif; outline: none; width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-input:focus,
        .field-select:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); background: var(--white); }
        .field-input.readonly {
            background: var(--crimson-mid); font-weight: 700; color: var(--crimson);
            border-color: var(--crimson-border); cursor: default;
        }
        .form-grid-4 { display: grid; grid-template-columns: 2fr 1fr; gap: 14px; }
        .item-row1   { display: grid; grid-template-columns: 3fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .item-row2   { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 14px; align-items: end; }

        /* ── Buttons ── */
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            height: 40px; padding: 0 18px; border-radius: var(--r-sm);
            background: var(--crimson); color: #fff; border: none;
            font-size: 12.5px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 3px 12px rgba(139,26,28,.28); transition: all .18s; white-space: nowrap;
        }
        .btn-primary:hover { background: var(--crimson-dark); transform: translateY(-1px); }
        .btn-primary i { font-size: 15px; }

        .btn-outline {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            height: 40px; padding: 0 18px; border-radius: var(--r-sm);
            background: var(--white); color: var(--crimson);
            border: 1.5px solid var(--crimson-border);
            font-size: 12.5px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif; transition: all .15s; white-space: nowrap;
        }
        .btn-outline:hover { border-color: var(--crimson); background: var(--crimson-mid); }
        .btn-outline i { font-size: 15px; }

        .btn-ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            height: 36px; padding: 0 16px; border-radius: var(--r-sm);
            background: var(--white); color: var(--txt2);
            border: 1px solid var(--border2);
            font-size: 12px; font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif; transition: all .15s; white-space: nowrap;
        }
        .btn-ghost:hover { border-color: var(--crimson); color: var(--crimson); }
        .btn-ghost i { font-size: 14px; }

        .btn-submit {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            height: 48px; width: 100%; border-radius: var(--r-sm);
            background: var(--crimson); color: #fff; border: none;
            font-size: 14px; font-weight: 800; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 16px rgba(139,26,28,.30); transition: all .2s;
        }
        .btn-submit:hover { background: var(--crimson-dark); transform: translateY(-1px); }
        .btn-submit i { font-size: 17px; }

        /* ══ TABLE ══ */
        .table-outer { margin: 16px 22px 0; border: 1px solid var(--border2); border-radius: 10px; overflow: hidden; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        thead { background: var(--bg); }
        thead th {
            padding: 10px 14px; text-align: left;
            font-size: 9px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
            color: var(--txt3); white-space: nowrap; border-bottom: 1px solid var(--border2);
        }
        thead th:last-child { text-align: right; }
        tbody tr { border-bottom: 1px solid rgba(0,0,0,.04); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fdf8f8; }
        tbody td { padding: 13px 14px; color: var(--txt2); vertical-align: middle; }
        tbody td:last-child { text-align: right; vertical-align: middle; }
        .td-name { font-weight: 700; color: var(--txt); max-width: 240px; vertical-align: top !important; }
        .td-name small { display: block; font-size: 11px; font-weight: 500; color: var(--txt3); margin-top: 2px; line-height: 1.5; }
        .td-bold    { font-weight: 700; color: var(--txt); white-space: nowrap; }
        .td-crimson { font-weight: 700; color: var(--crimson); }

        /* ══ Market Scoping cell ══ */
        .scoping-block { display: flex; flex-direction: column; gap: 4px; min-width: 160px; }
        .scoping-count-line { font-size: 12.5px; font-weight: 700; color: var(--crimson); line-height: 1.2; }
        .scoping-count-line .scoping-num { font-size: 14px; font-weight: 800; }
        .scoping-count-line .scoping-word { font-size: 12px; font-weight: 600; color: var(--txt2); }
        .scoping-lowest-line { font-size: 11.5px; color: var(--txt3); font-weight: 500; line-height: 1.55; }
        .scoping-lowest-line strong { color: var(--txt); font-weight: 700; }
        .scoping-divider { width: 28px; height: 2px; background: var(--crimson-border); border-radius: 2px; margin: 2px 0; }
        .scoping-empty { display: flex; flex-direction: column; gap: 3px; }
        .scoping-empty-label { font-size: 12px; font-weight: 700; color: var(--amber); }
        .scoping-empty-hint { font-size: 11px; color: var(--txt3); font-weight: 500; }
        .scoping-toggle { display: flex; align-items: center; gap: 6px; background: none; border: none; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 700; color: var(--crimson); padding: 0; line-height: 1.4; }
        .scoping-toggle:hover { text-decoration: underline; }
        .scoping-chevron { font-size: 13px; transition: transform .2s; flex-shrink: 0; }
        .scoping-refs-list { list-style: none; padding: 6px 0 2px 4px; display: flex; flex-direction: column; gap: 4px; }
        .scoping-refs-list li { display: flex; align-items: baseline; gap: 5px; }
        .ref-tree-icon { font-family: monospace; color: var(--txt3); font-size: 11px; flex-shrink: 0; line-height: 1.5; }
        .ref-tree-link { font-size: 11px; color: var(--txt2); text-decoration: none; line-height: 1.5; }
        .ref-tree-link:hover { color: var(--crimson); text-decoration: underline; }

        /* ══ Actions ══ */
        .tbl-actions { display: flex; align-items: center; justify-content: flex-end; gap: 5px; }
        .tbl-btn {
            width: 28px; height: 28px; border-radius: 7px;
            border: 1px solid var(--border2); background: var(--white);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .14px; color: var(--txt3);
            font-family: 'Poppins', sans-serif;
        }
        .tbl-btn i { font-size: 13px; }
        .tbl-btn:hover { border-color: var(--crimson); color: var(--crimson); background: var(--crimson-pale); }
        .tbl-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: var(--red-bg); }

        /* total row */
        .total-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 13px 22px; background: var(--bg); border-top: 1px solid var(--border2);
        }
        .total-label  { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--txt3); }
        .total-amount { font-size: 16px; font-weight: 800; color: var(--crimson); }

        /* ══ RIGHT PANEL ══ */
        .readiness-badge {
            display: inline-flex; align-items: center; gap: 5px;
            height: 24px; padding: 0 10px; border-radius: 99px; font-size: 11px; font-weight: 700;
        }
        .readiness-badge.ready { background: var(--green-bg); color: var(--green); border: 1px solid #bbf7d0; }
        .readiness-badge.draft { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 14px 22px; }
        .summary-stat { background: var(--bg); border: 1px solid var(--border2); border-radius: 10px; padding: 13px 15px; }
        .summary-stat dt { font-size: 8.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--txt3); margin-bottom: 6px; }
        .summary-stat dd { font-size: 24px; font-weight: 800; color: var(--txt); line-height: 1; }
        .summary-stat dd.crimson { color: var(--crimson); }
        .summary-stat dd.sm { font-size: 14px; font-weight: 700; }

        .submit-wrap { padding: 0 22px 18px; }

        /* ══ RESPONSIVE ══ */
        @media (max-width: 1280px) {
            .top-grid { grid-template-columns: minmax(0,1fr) 280px; }
            .form-grid-4 { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 1100px) {
            .top-grid { grid-template-columns: 1fr; }
            .col-right { position: static; }
        }
        @media (max-width: 960px) {
            .item-row1 { grid-template-columns: 1fr 1fr; }
            .item-row2 { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 720px) {
            .form-grid-4 { grid-template-columns: 1fr; }
            .item-row1 { grid-template-columns: 1fr; }
            .item-row2 { grid-template-columns: 1fr; }
        }

        /* ── Submitted banner ── */
        .submitted-banner { display: flex; align-items: center; gap: 12px; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--r-sm); padding: 14px 18px; margin-bottom: 4px; }
        .submitted-banner i { font-size: 22px; color: #2563EB; flex-shrink: 0; }
        .submitted-banner-title { font-size: 14px; font-weight: 800; color: #1E40AF; }
        .submitted-banner-sub   { font-size: 12px; color: #3B82F6; margin-top: 2px; }

        /* ── Inline submit feedback ── */
        .submit-msg { font-size: 12px; font-weight: 600; border-radius: var(--r-sm); padding: 9px 13px; margin-bottom: 8px; display: none; }
        .submit-msg.err  { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; }
        .submit-msg.warn { background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; }

        /* ── Submitted state in right panel ── */
        .submitted-state { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 22px 22px; text-align: center; }
        .submitted-state i { font-size: 32px; color: #2563EB; }
        .submitted-state-title { font-size: 13px; font-weight: 800; color: var(--txt); }
        .submitted-state-sub   { font-size: 11.5px; color: var(--txt3); line-height: 1.5; }
</style>
@endpush

@section('content')
    <div class="page-shell">

        @if($isReadOnly)
        <div class="submitted-banner">
            <i class="ti ti-send"></i>
            <div>
                <p class="submitted-banner-title">Proposal Submitted — Under Review</p>
                <p class="submitted-banner-sub">This proposal has been submitted and is awaiting Finance Office review. Editing is disabled.</p>
            </div>
        </div>
        @endif

        {{-- ═══ TOP GRID ═══ --}}
        <div class="top-grid">
            <div class="col-left">

                {{-- PROPOSAL DETAILS --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-head-icon"><i class="ti ti-file-invoice"></i></div>
                        <div class="card-head-text">
                            <p class="card-eyebrow">Proposal info</p>
                            <p class="card-title">Proposal Details</p>
                            <p class="card-sub">Basic information for the annual procurement budget proposal.</p>
                        </div>
                        <span class="badge {{ $isReadOnly ? 'badge-blue' : 'badge-gray' }}" style="margin-left:auto;">{{ ucfirst($proposalStatus) }}</span>
                    </div>
                    <div class="card-body">
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
                </div>

                {{-- ADD ITEMS --}}
                @if(!$isReadOnly)
                <div class="card">
                    <div class="card-head">
                        <div class="card-head-icon"><i class="ti ti-plus"></i></div>
                        <div class="card-head-text">
                            <p class="card-eyebrow">Procurement items</p>
                            <p class="card-title">Add Items</p>
                            <p class="card-sub">Encode item details, then run market scoping for price references.</p>
                        </div>
                        <button id="runMarketScopingButton" type="button" class="btn-primary" style="margin-left:auto;">
                            <i class="ti ti-bolt"></i>Run Market Scoping
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="proposalItemForm">
                            <input id="itemId" name="itemId" type="hidden">
                            <div class="item-row1">
                                <div class="field-group">
                                    <label class="field-label" for="itemDescription">Item Description</label>
                                    <input id="itemDescription" name="description" class="field-input" placeholder="e.g. Laptop computer for laboratory use" required>
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="itemUnit">Unit</label>
                                    <select id="itemUnit" name="unit" class="field-select">
                                        <option>unit</option><option>set</option>
                                        <option>lot</option><option>piece</option>
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
                            <div class="item-row2">
                                <div class="field-group" style="grid-column:span 2;">
                                    <label class="field-label" for="itemJustification">Purpose / Justification</label>
                                    <input id="itemJustification" name="justification" class="field-input" placeholder="Short procurement justification">
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="itemQuarter">Target Quarter</label>
                                    <select id="itemQuarter" name="targetQuarter" class="field-select">
                                        <option>Q1</option><option>Q2</option>
                                        <option>Q3</option><option>Q4</option>
                                    </select>
                                </div>
                                <div style="display:flex;align-items:flex-end;">
                                    <button id="saveItemButton" type="submit" class="btn-outline" style="width:100%;">
                                        <i class="ti ti-plus"></i>Add Item
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

            </div>{{-- /col-left --}}

            {{-- ═══ RIGHT — sticky readiness panel ═══ --}}
            <div class="col-right">
                <div class="card">
                    <div class="card-head">
                        <div class="card-head-icon"><i class="ti ti-shield-check"></i></div>
                        <div class="card-head-text">
                            <p class="card-eyebrow">Readiness check</p>
                            <p class="card-title">Submission Readiness</p>
                            <p class="card-sub">Market scoping must support all encoded items.</p>
                        </div>
                    </div>
                    <div style="padding:12px 22px 0;">
                        <span id="proposalReadyBadge" class="readiness-badge {{ $missingScopingCount === 0 ? 'ready' : 'draft' }}">
                            {{ $missingScopingCount === 0 ? '✓ Ready for Review' : 'Draft' }}
                        </span>
                    </div>
                    <dl class="summary-grid">
                        <div class="summary-stat">
                            <dt>Items</dt>
                            <dd id="proposalSummaryItems">{{ $itemCount }}</dd>
                        </div>
                        <div class="summary-stat">
                            <dt>References</dt>
                            <dd id="proposalSummaryReferences" class="crimson">{{ $scopingReferenceCount }}</dd>
                        </div>
                        <div class="summary-stat">
                            <dt>Missing Scoping</dt>
                            <dd id="proposalSummaryMissing">{{ $missingScopingCount }}</dd>
                        </div>
                        <div class="summary-stat">
                            <dt>Total Amount</dt>
                            <dd class="sm crimson">PHP {{ number_format($proposalTotal) }}</dd>
                        </div>
                    </dl>
                    <div class="submit-wrap">
                        @if($isReadOnly)
                        <div class="submitted-state">
                            <i class="ti ti-circle-check"></i>
                            <p class="submitted-state-title">Proposal Submitted</p>
                            <p class="submitted-state-sub">Awaiting Finance Office review. You will be notified of any updates.</p>
                        </div>
                        @else
                        <p id="submitMsg" class="submit-msg"></p>
                        <button id="submitProposalButton" type="button" class="btn-submit">
                            <i class="ti ti-send"></i>Submit Proposal
                        </button>
                        @endif
                    </div>
                </div>
            </div>{{-- /col-right --}}

        </div>{{-- /top-grid --}}

        {{-- ═══ FULL-WIDTH: Proposal Line Items ═══ --}}
        <div class="card">
            <div class="card-head">
                <div class="card-head-icon"><i class="ti ti-list-details"></i></div>
                <div class="card-head-text">
                    <p class="card-eyebrow">Encoded items</p>
                    <p class="card-title">Proposal Line Items</p>
                    <p class="card-sub"><span id="proposalItemCount">{{ $itemCount }}</span> procurement items encoded.</p>
                </div>
                <button type="button" class="btn-ghost" style="margin-left:auto;">
                    <i class="ti ti-download"></i>Export Draft
                </button>
            </div>

            <div class="table-outer">
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
                                @if(!$isReadOnly)<th>Actions</th>@endif
                            </tr>
                        </thead>
                        <tbody id="encodedItemsTable"></tbody>
                    </table>
                </div>
            </div>

            <div class="total-row">
                <span class="total-label">Total Proposed Budget</span>
                <span class="total-amount" id="proposalSummaryTotal">PHP {{ number_format($proposalTotal) }}</span>
            </div>
        </div>

    </div>{{-- /page-shell --}}
@endsection

@push('scripts')
<script type="application/json" id="initialProposalItems">@json($encodedItems)</script>
<script>
(function () {
    const csrf        = document.querySelector('meta[name="csrf-token"]').content;
    const storeUrl    = '{{ route("office-head.budget-proposal.store-item") }}';
    const destroyBase = '{{ url("office-head/budget-proposal/item") }}/';
    const submitUrl   = '{{ route("office-head.budget-proposal.submit") }}';
    const scopingUrl  = '{{ route("office-head.market-scoping") }}';
    const isReadOnly  = {{ $isReadOnly ? 'true' : 'false' }};

    let items = JSON.parse(document.getElementById('initialProposalItems').textContent || '[]');

    // ── Helpers ──────────────────────────────────────────────────────────────
    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function fmt(n) {
        return Number(n).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // ── Render table ─────────────────────────────────────────────────────────
    function renderTable() {
        const tbody = document.getElementById('encodedItemsTable');
        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:28px;color:var(--txt3);font-weight:600;">
                No items added yet. Use the form above to add procurement items.</td></tr>`;
            return;
        }
        tbody.innerHTML = items.map(item => {
            const refs   = item.scoping || [];
            const lowest = refs.length ? [...refs].sort((a, b) => a.price - b.price)[0] : null;
            const refsHtml = refs.map((ref, i) => {
                const isLast = i === refs.length - 1;
                const tree   = isLast ? '└──' : '├──';
                const label  = esc((ref.title || ref.supplierName) + ' – ₱' + fmt(ref.price) + ' – ' + (ref.source || 'Online'));
                const href   = ref.sourceLink ? ` href="${esc(ref.sourceLink)}" target="_blank" rel="noopener"` : '';
                return `<li><span class="ref-tree-icon">${tree}</span><a class="ref-tree-link"${href}>${label}</a></li>`;
            }).join('');

            const scopingCell = refs.length
                ? `<div class="scoping-block">
                    <button class="scoping-toggle" onclick="window.prismBP.toggleRefs(this)" type="button">
                        <i class="ti ti-list" style="font-size:14px"></i>
                        Market References (${refs.length})
                        <i class="ti ti-chevron-down scoping-chevron"></i>
                    </button>
                    <ul class="scoping-refs-list" style="display:none">${refsHtml}</ul>
                   </div>`
                : `<div class="scoping-empty">
                    <span class="scoping-empty-label">No references yet</span>
                    <span class="scoping-empty-hint"><a href="${esc(scopingUrl)}?q=${encodeURIComponent(item.description)}" style="color:var(--crimson);text-decoration:none;font-weight:700">Run scoping →</a></span>
                   </div>`;

            return `<tr>
                <td class="td-name">${esc(item.description)}<small>${esc(item.justification)}</small></td>
                <td>${esc(item.quantity)} ${esc(item.unit)}</td>
                <td class="td-bold">PHP ${fmt(item.estimatedUnitCost)}</td>
                <td class="td-bold">PHP ${fmt(item.totalCost)}</td>
                <td>${esc(item.targetQuarter)}</td>
                <td>${scopingCell}</td>
                ${isReadOnly ? '' : `<td><div class="tbl-actions">
                    <button class="tbl-btn danger" title="Remove item" type="button" onclick="prismBP.deleteItem('${esc(item.id)}')">
                        <i class="ti ti-trash"></i>
                    </button>
                </div></td>`}
            </tr>`;
        }).join('');
    }

    // ── Update summary panel ──────────────────────────────────────────────────
    function updateSummary() {
        const total   = items.reduce((s, i) => s + (i.totalCost || 0), 0);
        const missing = items.filter(i => !i.scoping || !i.scoping.length).length;

        document.getElementById('proposalItemCount').textContent        = items.length;
        document.getElementById('proposalSummaryItems').textContent     = items.length;
        document.getElementById('proposalSummaryMissing').textContent   = missing;
        document.getElementById('proposalSummaryTotal').textContent     = 'PHP ' + fmt(total);

        const refs = items.reduce((s, i) => s + (i.scoping?.length || 0), 0);
        document.getElementById('proposalSummaryReferences').textContent = refs;

        const badge = document.getElementById('proposalReadyBadge');
        if (items.length > 0 && missing === 0) {
            badge.className   = 'readiness-badge ready';
            badge.textContent = '✓ Ready for Review';
        } else {
            badge.className   = 'readiness-badge draft';
            badge.textContent = 'Draft';
        }
    }

    // ── Add item ──────────────────────────────────────────────────────────────
    document.getElementById('proposalItemForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const f   = e.target;
        const btn = document.getElementById('saveItemButton');
        btn.disabled = true;
        btn.innerHTML = '<i class="ti ti-loader-2"></i> Adding…';

        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({
                    description:       f.description.value.trim(),
                    unit:              f.unit.value,
                    quantity:          parseFloat(f.quantity.value),
                    estimatedUnitCost: parseFloat(f.estimatedUnitCost.value),
                    justification:     f.justification.value.trim(),
                    targetQuarter:     f.targetQuarter.value,
                }),
            });
            const data = await res.json();
            if (data.success) {
                items.push(data.item);
                renderTable();
                updateSummary();
                f.reset();
                f.quantity.value = 1;
                f.estimatedUnitCost.value = 0;
            } else {
                alert(data.message || 'Could not add item.');
            }
        } catch {
            alert('Network error. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-plus"></i>Add Item';
        }
    });

    // ── Delete item ───────────────────────────────────────────────────────────
    async function deleteItem(id) {
        if (!confirm('Remove this item from the proposal?')) return;
        try {
            const res  = await fetch(destroyBase + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) {
                items = items.filter(i => i.id !== id);
                renderTable();
                updateSummary();
            }
        } catch {
            alert('Network error. Please try again.');
        }
    }

    // ── Submit proposal ───────────────────────────────────────────────────────
    if (!isReadOnly) {
        document.getElementById('submitProposalButton').addEventListener('click', async function () {
            const msgEl = document.getElementById('submitMsg');

            const showMsg = (text, type) => {
                msgEl.textContent = text;
                msgEl.className   = 'submit-msg ' + type;
                msgEl.style.display = '';
            };
            msgEl.style.display = 'none';

            if (!items.length) {
                showMsg('Please add at least one item before submitting.', 'err');
                return;
            }

            const missingScoping = items.filter(i => !i.scoping || !i.scoping.length).length;
            if (missingScoping > 0) {
                showMsg(
                    `${missingScoping} item${missingScoping > 1 ? 's have' : ' has'} no market references attached. Consider adding price references before submitting.`,
                    'warn'
                );
            }

            const btn = this;
            btn.disabled  = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite"></i> Submitting…';

            try {
                const res  = await fetch(submitUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showMsg(data.message || 'Could not submit proposal.', 'err');
                    btn.disabled  = false;
                    btn.innerHTML = '<i class="ti ti-send"></i>Submit Proposal';
                }
            } catch {
                showMsg('Network error. Please try again.', 'err');
                btn.disabled  = false;
                btn.innerHTML = '<i class="ti ti-send"></i>Submit Proposal';
            }
        });
    }

    // ── Run Market Scoping redirect ───────────────────────────────────────────
    document.getElementById('runMarketScopingButton')?.addEventListener('click', function () {
        window.location.href = scopingUrl;
    });

    // expose deleteItem globally for inline onclick
    window.prismBP = { deleteItem };

    prismBP.toggleRefs = function (btn) {
        const list = btn.nextElementSibling;
        const icon = btn.querySelector('.scoping-chevron');
        const open = list.style.display !== 'none';
        list.style.display = open ? 'none' : 'block';
        icon.style.transform = open ? '' : 'rotate(180deg)';
    };

    // ── Init ──────────────────────────────────────────────────────────────────
    renderTable();
    updateSummary();
})();
</script>
@endpush
