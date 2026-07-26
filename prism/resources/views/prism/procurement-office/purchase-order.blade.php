@extends('prism.layouts.app')
@section('title', 'Purchase Orders | Procurement Office')

@push('page-css')
<style>
    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .content {
        padding: 28px 32px 56px; flex: 1; display: flex; flex-direction: column; gap: 20px;
        --m: var(--crimson); --m-dk: var(--crimson-dark); --gold: #c9a84c; --white: #ffffff;
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .eligible-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px; }
    .eligible-card { border: 1.5px solid var(--s200); border-radius: 12px; padding: 14px 18px; display: flex; flex-direction: column; gap: 8px; }
    .eligible-card h4 { font-size: 13px; font-weight: 700; color: var(--s900); }
    .eligible-card p { font-size: 12px; color: var(--s500); }

    .po-grid { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 20px; align-items: start; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 62vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; cursor: pointer; }
    tbody tr:hover { background: var(--crimson-mid); }
    tbody tr.selected { background: rgba(139,26,28,.07); }
    tbody tr.selected td:first-child { border-left: 3px solid var(--m); }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-issued    { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-delivery  { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-complete  { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-paid      { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }
    .badge-draft     { background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }
    .badge-routing   { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-signed    { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }

    /* Colors here use the globally-defined --crimson/--crimson-dark (set on
       :root in the base layout) rather than this page's --m/--m-dk aliases
       (scoped to .content) — the Issue PO modal sits outside .content, so
       a content-scoped variable would resolve to nothing there. */
    .btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 9px; font-size: 11px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-primary { background: var(--crimson); color: #fff; }
    .btn-primary:hover:not(:disabled) { background: var(--crimson-dark); }
    .btn-ghost { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }
    .btn-ghost:hover:not(:disabled) { background: #cbd5e1; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .form-field { display: flex; flex-direction: column; gap: 4px; }
    .form-field.full { grid-column: 1 / -1; }
    .form-field label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #64748b; }
    .form-field input, .form-field textarea { height: 38px; padding: 0 12px; border-radius: 9px; border: 1px solid var(--s200); font-size: 13px; font-family: 'Poppins', sans-serif; color: #334155; outline: none; transition: border-color .2s; }
    .form-field input:focus { border-color: var(--crimson); }
    .form-field textarea { height: auto; padding: 10px 12px; resize: vertical; }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--s400); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .search-input { height: 40px; width: 100%; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 16px 0 36px; font-size: 13px; font-weight: 500; color: var(--s900); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .search-input::placeholder { color: var(--s400); }

    /* Detail panel */
    .detail-panel { display: flex; flex-direction: column; gap: 16px; }
    .detail-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 220px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); text-align: center; padding: 32px; }
    .detail-empty i { font-size: 36px; color: var(--s300); }
    .detail-empty p { font-size: 13px; color: var(--s400); line-height: 1.6; max-width: 220px; }

    .detail-content { display: none; flex-direction: column; gap: 16px; }
    .detail-content.visible { display: flex; }

    .detail-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .detail-field { background: var(--s50); border: 1px solid var(--s200); border-radius: 10px; padding: 10px 14px; }
    .detail-field.full { grid-column: 1 / -1; }
    .detail-field label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s400); display: block; margin-bottom: 3px; }
    .detail-field span { font-size: 13px; font-weight: 600; color: var(--s700); }

    /* Uploaded PO PDF — the scanned, physically-signed document */
    .pdf-preview { border-radius: 12px; border: 1px solid var(--s200); background: var(--s50); overflow: hidden; aspect-ratio: 8.5 / 11; display: flex; align-items: center; justify-content: center; position: relative; }
    .pdf-preview iframe { width: 100%; height: 100%; border: none; }
    .pdf-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 100%; color: var(--s400); }
    .pdf-placeholder i { font-size: 42px; color: var(--s300); }
    .pdf-placeholder span { font-size: 12px; font-weight: 600; }
    .upload-pr-label { display: inline-flex; align-items: center; gap: 7px; border: 1.5px dashed var(--s300); border-radius: 9px; background: var(--s50); padding: 7px 14px; font-size: 12px; font-weight: 700; color: var(--s600); cursor: pointer; transition: background .15s; white-space: nowrap; width: 100%; justify-content: center; margin-top: 8px; box-sizing: border-box; }
    .upload-pr-label:hover { background: var(--s100); }
    .upload-pr-label i { font-size: 14px; }
    .upload-pr-label input { display: none; }

    .sig-timeline { display: flex; align-items: center; gap: 0; margin-bottom: 4px; flex-wrap: wrap; row-gap: 14px; }
    .sig-step { display: flex; flex-direction: column; align-items: center; flex: 1; min-width: 64px; position: relative; }
    .sig-step:not(:last-child)::after { content: ''; position: absolute; top: 10px; left: 50%; width: 100%; height: 2px; background: var(--s200); z-index: 0; }
    .sig-step.done::after { background: #3b6d11; }
    .sig-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--s300); background: var(--white); z-index: 1; position: relative; transition: all .2s; }
    .sig-step.done .sig-dot { background: #3b6d11; border-color: #3b6d11; }
    .sig-step.active .sig-dot { background: var(--m); border-color: var(--m); box-shadow: 0 0 0 3px rgba(139,26,28,.2); }
    .sig-step.routing .sig-dot { border-style: dashed; }
    .sig-step.routing.done .sig-dot { border-style: solid; }
    .sig-label { font-size: 9px; font-weight: 700; text-align: center; color: var(--s400); margin-top: 5px; line-height: 1.3; max-width: 84px; }
    .sig-step.done .sig-label, .sig-step.active .sig-label { color: var(--s700); }

    /* Delivery & payment timeline (separate chain, purple accent to distinguish from signatures) */
    .po-status-timeline .sig-step.done::after { background: #5b21b6; }
    .po-status-timeline .sig-step.done .sig-dot { background: #5b21b6; border-color: #5b21b6; }

    .btn-route { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; }
    .btn-route-fwd { background: #3b6d11; color: #fff; }
    .btn-route-fwd:hover:not(:disabled) { background: #2e560d; }
    .btn-route-ret { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
    .btn-route-ret:hover:not(:disabled) { background: var(--s200); }
    .btn-route:disabled { opacity: .5; cursor: not-allowed; }
    .btn-route-purple { background: #5b21b6; color: #fff; }
    .btn-route-purple:hover:not(:disabled) { background: #4c1d95; }

    .remarks-textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 13px; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 60px; outline: none; transition: border-color .2s; line-height: 1.6; box-sizing: border-box; }
    .remarks-textarea:focus { border-color: var(--m); }
    .remarks-textarea::placeholder { color: var(--s300); }

    .receipt-link { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; color: #5b21b6; text-decoration: none; }
    .po-done-note { display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 700; color: #166534; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; }
    .po-pending-note { font-size: 11px; color: var(--s500); }

    .activity-log { display: flex; flex-direction: column; gap: 1px; }
    .activity-item { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--s100); }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); flex-shrink: 0; margin-top: 5px; }
    .activity-item p { font-size: 12.5px; color: var(--s600); line-height: 1.6; }
    .activity-item time { font-size: 11px; color: var(--s400); display: block; margin-top: 2px; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 1200px) { .po-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } .form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-shopping-cart"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Purchase Orders</h1>
            <p class="page-hdr-sub">Issue POs from fully-signed AOCs, route them through the signature chain, and track delivery through to payment. The Supplier has no account — Procurement routes on their behalf.</p>
        </div>
    </div>

    {{-- ── Eligible AOCs (ready for PO issuance) ── --}}
    @if(count($eligibleAocs) > 0)
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Ready for PO</p>
                <h2 class="card-title">Fully-Signed AOCs — Issue Purchase Order</h2>
            </div>
        </div>
        <div class="eligible-grid">
            @foreach($eligibleAocs as $aoc)
            <div class="eligible-card">
                <div>
                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--s400);">{{ $aoc['office'] }}</span>
                    <h4>{{ $aoc['code'] }}</h4>
                    <p>{{ Str::limit($aoc['title'], 55) }}</p>
                    <p style="font-weight:600;color:var(--s700);margin-top:2px;">Budget: ₱{{ number_format($aoc['amount'], 2) }}</p>
                </div>
                <button class="btn btn-primary btn-issue-po" data-aoc-id="{{ $aoc['id'] }}" data-aoc-code="{{ $aoc['code'] }}" data-url="{{ $aoc['issueUrl'] }}" data-amount="{{ $aoc['amount'] }}">
                    <i class="ti ti-file-invoice"></i> Issue PO
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="po-grid">

        {{-- ── Left: PO list ── --}}
        <div class="card" style="padding-bottom: 22px;">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">PO Tracker</p>
                    <h2 class="card-title">Purchase Order List</h2>
                </div>
                <span class="count-chip" id="poVisibleCount" style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:var(--s100);color:var(--s700);border:1px solid var(--s200);">{{ count($purchaseOrders) }} PO{{ count($purchaseOrders) !== 1 ? 's' : '' }}</span>
            </div>

            @if(count($purchaseOrders) > 0)
                <div class="search-wrap" style="margin-bottom:14px;">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="search" id="poSearch" placeholder="Search by PO number, AOC code, office, or supplier">
                </div>
            @endif

            @if(count($purchaseOrders) === 0)
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:180px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:32px;text-align:center;">
                    <i class="ti ti-shopping-cart-off" style="font-size:38px;color:var(--s300);"></i>
                    <p style="font-size:13px;color:var(--s400);max-width:240px;line-height:1.6;">No purchase orders issued yet. Fully-signed AOCs will appear above.</p>
                </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>PO No.</th>
                            <th>AOC</th>
                            <th>Office</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Signatory Stage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrders as $po)
                        @php
                            $sigBadge = match($po['signatoryStage']) {
                                'fully_signed' => 'badge-signed',
                                'draft'        => 'badge-draft',
                                default        => 'badge-routing',
                            };
                        @endphp
                        <tr data-po-row data-po-id="{{ $po['id'] }}" data-search="{{ strtolower($po['poNumber'] . ' ' . $po['aocCode'] . ' ' . $po['office'] . ' ' . $po['supplier']) }}" tabindex="0">
                            <td style="font-weight:700;font-size:12px;color:var(--s500);white-space:nowrap;">{{ $po['poNumber'] }}</td>
                            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $po['aocCode'] }}</td>
                            <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['office'] }}</td>
                            <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['supplier'] }}</td>
                            <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                            <td><span class="badge {{ $sigBadge }}" data-po-sig-badge="{{ $po['id'] }}">{{ $po['signatoryLabel'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ── Right: Detail panel ── --}}
        <div class="card detail-panel">
            <div class="card-head" style="margin-bottom: 14px;">
                <div>
                    <p class="card-eyebrow">PO Details</p>
                    <h2 class="card-title" id="detailPoNumber">Select a PO</h2>
                </div>
            </div>

            <div class="detail-empty" id="detailEmpty">
                <i class="ti ti-arrow-left"></i>
                <p>Select a PO from the list to view details, route it forward, and track delivery.</p>
            </div>

            <div class="detail-content" id="detailContent">

                {{-- Uploaded PO PDF preview --}}
                <div class="pdf-preview" id="pdfPreview">
                    <div class="pdf-placeholder">
                        <i class="ti ti-file-off"></i>
                        <span>No PDF attached</span>
                    </div>
                </div>
                <label class="upload-pr-label" id="uploadPoLabel">
                    <i class="ti ti-upload"></i>
                    <span id="uploadPoText">Upload Signed PO PDF</span>
                    <input type="file" id="uploadPoInput" accept="application/pdf,.pdf">
                </label>

                <div class="detail-fields">
                    <div class="detail-field"><label>PO Number</label><span id="fPoNumber">—</span></div>
                    <div class="detail-field"><label>AOC Code</label><span id="fAocCode">—</span></div>
                    <div class="detail-field"><label>Office</label><span id="fOffice">—</span></div>
                    <div class="detail-field"><label>Amount</label><span id="fAmount">—</span></div>
                    <div class="detail-field"><label>Supplier</label><span id="fSupplier">—</span></div>
                    <div class="detail-field"><label>Expected Delivery</label><span id="fExpectedDate">—</span></div>
                    <div class="detail-field full"><label>Supplier Address</label><span id="fSupplierAddress">—</span></div>
                    <div class="detail-field full"><label>Remarks on File</label><span id="fRemarks" style="white-space:pre-line;">—</span></div>
                </div>

                {{-- Signatory timeline --}}
                <div>
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--s500);margin-bottom:10px;">Signature Routing</div>
                    <div class="sig-timeline" id="sigTimeline"></div>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <button class="btn-route btn-route-fwd" id="btnAdvance" type="button" disabled>
                            <i class="ti ti-circle-arrow-right"></i>
                            Route Forward
                        </button>
                        <button class="btn-route btn-route-ret" id="btnReturn" type="button" disabled>
                            <i class="ti ti-circle-arrow-left"></i>
                            Return
                        </button>
                    </div>
                    <div id="returnRemarks" style="display:none;margin-top:8px;">
                        <textarea class="remarks-textarea" id="returnRemarksInput" placeholder="Reason for returning one step back (required)…" style="min-height:60px;"></textarea>
                        <button class="btn-route btn-route-ret" id="btnConfirmReturn" type="button" style="margin-top:6px;background:#a32d2d;color:#fff;">
                            <i class="ti ti-send"></i> Confirm Return
                        </button>
                    </div>
                </div>

                {{-- Delivery & Payment status --}}
                <div>
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--s500);margin-bottom:10px;">Delivery &amp; Payment Status</div>
                    <div class="sig-timeline po-status-timeline" id="statusTimeline"></div>
                    <div id="statusAction" style="margin-top:12px;"></div>
                </div>

                {{-- Activity log --}}
                <div>
                    <div class="card-head" style="margin-bottom:10px;">
                        <div>
                            <p class="card-eyebrow">History</p>
                            <h3 class="card-title" style="font-size:14px;">Activity Log</h3>
                        </div>
                    </div>
                    <div class="activity-log" id="activityLog"></div>
                </div>

            </div>
        </div>

    </div>

</div>

<div class="pr-toast" id="poToast"></div>

{{-- Issue PO modal --}}
<div id="poModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:28px;max-width:500px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.25);max-height:90vh;overflow-y:auto;">
        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:4px;">Issue Purchase Order</h3>
        <p id="poModalAocCode" style="font-size:12px;color:#64748b;margin-bottom:18px;"></p>
        <div class="form-grid">
            <div class="form-field full">
                <label>Supplier Name *</label>
                <input type="text" id="poSupplierName" placeholder="Enter supplier name…">
            </div>
            <div class="form-field full">
                <label>Supplier Address</label>
                <textarea id="poSupplierAddr" rows="2" placeholder="Optional address…"></textarea>
            </div>
            <div class="form-field">
                <label>Total Amount (₱) *</label>
                <input type="number" id="poAmount" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="form-field">
                <label>Expected Delivery Date</label>
                <input type="date" id="poDeliveryDate">
            </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:18px;">
            <button class="btn btn-ghost" id="btnCancelPo" style="flex:1;height:40px;">Cancel</button>
            <button class="btn btn-primary" id="btnConfirmPo" style="flex:1;height:40px;font-size:13px;"><i class="ti ti-file-invoice"></i> Issue PO</button>
        </div>
    </div>
</div>

@endsection

<script type="application/json" id="poData">@json($purchaseOrders)</script>

@push('scripts')
<script>
(function () {
    const allPos       = JSON.parse(document.getElementById('poData').textContent);
    const rows         = document.querySelectorAll('[data-po-row]');
    const emptyEl       = document.getElementById('detailEmpty');
    const contentEl     = document.getElementById('detailContent');
    const titleEl       = document.getElementById('detailPoNumber');
    const logEl          = document.getElementById('activityLog');
    const toastEl        = document.getElementById('poToast');
    const btnAdvance     = document.getElementById('btnAdvance');
    const btnReturn      = document.getElementById('btnReturn');
    const returnRemarks  = document.getElementById('returnRemarks');
    const returnIn       = document.getElementById('returnRemarksInput');
    const btnConfirmRet  = document.getElementById('btnConfirmReturn');
    const statusAction   = document.getElementById('statusAction');
    const csrfToken      = document.querySelector('meta[name="csrf-token"]').content;
    const poSearch       = document.getElementById('poSearch');
    const poCount        = document.getElementById('poVisibleCount');
    const uploadPoInput  = document.getElementById('uploadPoInput');
    const uploadPoText   = document.getElementById('uploadPoText');

    const logs = {};
    let activePo = null;
    let saving = false;

    function nowStr() {
        return new Date().toLocaleString('en-PH', {
            timeZone: 'Asia/Manila', month: 'short', day: 'numeric',
            year: 'numeric', hour: 'numeric', minute: '2-digit'
        });
    }
    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 2800);
    }

    function timelineHtml(chain) {
        return chain.map(step =>
            `<div class="sig-step${step.status === 'done' ? ' done' : ''}${step.status === 'active' ? ' active' : ''}"><div class="sig-dot"></div><span class="sig-label">${step.label}</span></div>`
        ).join('');
    }

    function renderLog(poId) {
        const entries = logs[poId] || [];
        if (!entries.length) {
            logEl.innerHTML = '<p style="font-size:12px;color:var(--s400);padding:8px 0;">No activity recorded yet.</p>';
            return;
        }
        logEl.innerHTML = entries.slice().reverse().map(e => `
            <div class="activity-item">
                <div class="activity-dot"></div>
                <div>
                    <p>${e.text}</p>
                    <time>${e.time}</time>
                </div>
            </div>`).join('');
    }

    function updateRoutingButtons(po) {
        const isDraft  = po.signatoryStage === 'draft';
        const isSigned = po.signatoryStage === 'fully_signed';
        btnAdvance.disabled  = isSigned;
        btnAdvance.innerHTML = '<i class="ti ti-circle-arrow-right"></i> ' + (isSigned ? 'Fully Signed' : (po.currentStageType === 'routing' ? 'Mark Forwarded' : 'Mark Signed'));
        btnReturn.disabled   = isDraft || isSigned;
        returnRemarks.style.display = 'none';
        returnIn.value = '';
    }

    function renderStatusAction(po) {
        if (po.signatoryStage !== 'fully_signed') {
            statusAction.innerHTML = '<p class="po-pending-note">Delivery tracking starts once the PO is fully signed.</p>';
            return;
        }

        if (po.status === 'paid') {
            statusAction.innerHTML = `
                <div class="po-done-note">
                    <i class="ti ti-circle-check"></i> Payment Made${po.paidAt ? ' — ' + po.paidAt : ''}
                    ${po.receiptUrl ? `<a class="receipt-link" href="${po.receiptUrl}" target="_blank" rel="noopener" style="margin-left:auto;"><i class="ti ti-file-text"></i> View Receipt</a>` : ''}
                </div>`;
            return;
        }

        if (['complete_delivery', 'processing_payment'].includes(po.status)) {
            statusAction.innerHTML = '<p class="po-pending-note">At Accounting / Cashier for payment processing — nothing to do here.</p>';
            return;
        }

        if (po.nextStatus) {
            const nextLabel = ({
                awaiting_delivery: 'Forward to Supply Office',
                partial_delivery:  'Mark Partial Delivery',
                complete_delivery: 'Mark Complete Delivery',
            })[po.nextStatus] || 'Advance';
            statusAction.innerHTML = `<button class="btn-route btn-route-purple" id="btnAdvanceStatus" type="button"><i class="ti ti-circle-arrow-right"></i> ${nextLabel}</button>`;

            document.getElementById('btnAdvanceStatus').addEventListener('click', async () => {
                const btn = document.getElementById('btnAdvanceStatus');
                btn.disabled = true;
                btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i>';
                try {
                    const resp = await fetch(po.updateUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ _action: 'advance' }),
                    });
                    const json = await resp.json();
                    if (resp.ok && json.success) {
                        po.status      = json.status;
                        po.statusLabel = json.statusLabel;
                        po.nextStatus  = json.status === 'complete_delivery' ? null : po.nextStatus;
                        const idx = po.deliveryChain.findIndex(s => s.key === json.status);
                        po.deliveryChain = po.deliveryChain.map((s, i) => Object.assign({}, s, {
                            status: i < idx ? 'done' : (i === idx ? 'active' : 'pending'),
                        }));
                        showToast('PO status updated: ' + json.statusLabel);
                        renderDetail(po);
                    } else {
                        showToast(json.error || 'Failed.', true);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> ' + nextLabel;
                    }
                } catch {
                    showToast('Network error.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> ' + nextLabel;
                }
            });
            return;
        }

        statusAction.innerHTML = '<p class="po-pending-note">—</p>';
    }

    function renderDetail(po) {
        document.getElementById('fPoNumber').textContent       = po.poNumber;
        document.getElementById('fAocCode').textContent        = po.aocCode;
        document.getElementById('fOffice').textContent         = po.office;
        document.getElementById('fAmount').textContent         = '₱' + Number(po.totalAmount).toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('fSupplier').textContent       = po.supplier;
        document.getElementById('fExpectedDate').textContent   = po.expectedDate;
        document.getElementById('fSupplierAddress').textContent = po.supplierAddress;
        document.getElementById('fRemarks').textContent        = po.remarks;

        const pdfEl = document.getElementById('pdfPreview');
        pdfEl.innerHTML = po.pdfFile
            ? `<iframe src="/storage/${po.pdfFile}" title="PO Document"></iframe>`
            : `<div class="pdf-placeholder"><i class="ti ti-file-off"></i><span>No PDF attached</span></div>`;
        uploadPoText.textContent = po.pdfFile ? 'Re-upload PDF' : 'Upload Signed PO PDF';

        document.getElementById('sigTimeline').innerHTML = timelineHtml(
            po.stageMeta ? po.stageMeta.filter(m => !['draft', 'fully_signed'].includes(m.key)).map(m => ({
                label: m.label,
                status: (function () {
                    const keys = po.stageMeta.map(x => x.key);
                    const cur  = keys.indexOf(po.signatoryStage);
                    const idx  = keys.indexOf(m.key);
                    return idx < cur ? 'done' : (idx === cur ? 'active' : 'pending');
                })(),
            })) : []
        );

        document.getElementById('statusTimeline').innerHTML = timelineHtml(po.deliveryChain);

        updateRoutingButtons(po);
        renderStatusAction(po);
        renderLog(po.id);
    }

    function openPo(po) {
        activePo = po;
        rows.forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-po-id="${po.id}"]`)?.classList.add('selected');
        titleEl.textContent = po.poNumber;

        if (!logs[po.id]) {
            logs[po.id] = (po.signatureLogs || []).map(l => ({
                text: `<strong>${l.display}</strong>` + (l.by && l.by !== '—' ? ` by ${l.by}` : ''),
                time: l.at,
            }));
        }

        renderDetail(po);
        emptyEl.style.display = 'none';
        contentEl.classList.add('visible');
    }

    rows.forEach(row => {
        row.addEventListener('click', () => {
            const po = allPos.find(p => String(p.id) === row.dataset.poId);
            if (po) openPo(po);
        });
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
        });
    });

    poSearch?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const match = !q || (row.dataset.search ?? '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (poCount) poCount.textContent = visible + (visible === 1 ? ' PO' : ' POs');
    });

    function updateSigBadge(poId, label, stage) {
        const badge = document.querySelector(`[data-po-sig-badge="${poId}"]`);
        if (!badge) return;
        badge.textContent = label;
        badge.className = 'badge ' + (stage === 'fully_signed' ? 'badge-signed' : stage === 'draft' ? 'badge-draft' : 'badge-routing');
    }

    /* ── Route Forward ── */
    btnAdvance.addEventListener('click', async () => {
        if (!activePo || saving) return;
        saving = true;
        btnAdvance.disabled = true;
        btnAdvance.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Routing…';

        try {
            const resp = await fetch(activePo.advanceUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({}),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activePo.signatoryStage = json.signatoryStage;
                activePo.signatoryLabel = json.signatoryLabel;
                if (json.stageMeta) activePo.stageMeta = json.stageMeta;
                updateSigBadge(activePo.id, json.signatoryLabel, json.signatoryStage);
                logs[activePo.id].push({ text: `Routed forward → <strong>${json.signatoryLabel}</strong>`, time: nowStr() });
                renderDetail(activePo);
                showToast(json.currentStageType === 'routing' ? 'PO forwarded.' : 'PO signed and routed forward.');
            } else {
                showToast(json.error || 'Failed to route PO.', true);
            }
        } catch { showToast('Network error.', true); }
        finally {
            saving = false;
            if (activePo) updateRoutingButtons(activePo);
        }
    });

    /* ── Return (toggle panel) ── */
    btnReturn.addEventListener('click', () => {
        returnRemarks.style.display = returnRemarks.style.display === 'none' ? '' : 'none';
    });

    btnConfirmRet.addEventListener('click', async () => {
        if (!activePo || saving) return;
        const reason = returnIn.value.trim();
        if (!reason) { showToast('Please provide a reason for returning.', true); return; }
        saving = true;
        btnConfirmRet.disabled = true;

        try {
            const resp = await fetch(activePo.returnUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ remarks: reason }),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activePo.signatoryStage = json.signatoryStage;
                activePo.signatoryLabel = json.signatoryLabel;
                if (json.stageMeta) activePo.stageMeta = json.stageMeta;
                updateSigBadge(activePo.id, json.signatoryLabel, json.signatoryStage);
                returnRemarks.style.display = 'none';
                returnIn.value = '';
                logs[activePo.id].push({ text: `<strong>Returned to ${json.signatoryLabel}</strong> &mdash; ${reason}`, time: nowStr() });
                renderDetail(activePo);
                showToast('PO returned one step — now at ' + json.signatoryLabel + '.');
            } else {
                showToast(json.error || 'Failed to return PO.', true);
            }
        } catch { showToast('Network error.', true); }
        finally { saving = false; btnConfirmRet.disabled = false; }
    });

    /* ── Upload signed PO PDF ── */
    uploadPoInput.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file || !activePo) return;
        const origText = uploadPoText.textContent;
        uploadPoText.textContent = 'Uploading…';
        this.disabled = true;
        const fd = new FormData();
        fd.append('file', file);
        try {
            const resp = await fetch(activePo.uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activePo.pdfFile = json.filePath;
                document.getElementById('pdfPreview').innerHTML =
                    `<iframe src="/storage/${json.filePath}" title="PO Document"></iframe>`;
                uploadPoText.textContent = 'Re-upload PDF';
                showToast('PO PDF uploaded successfully.');
            } else {
                uploadPoText.textContent = origText;
                showToast(json.message || 'Upload failed.', true);
            }
        } catch {
            uploadPoText.textContent = origText;
            showToast('Network error during upload.', true);
        }
        this.disabled = false;
        this.value = '';
    });

    /* ── Open Issue PO modal ── */
    const modal      = document.getElementById('poModal');
    const btnCancel  = document.getElementById('btnCancelPo');
    const btnConfirm = document.getElementById('btnConfirmPo');
    let pendingIssueUrl = null;

    document.querySelectorAll('.btn-issue-po').forEach(btn => {
        btn.addEventListener('click', () => {
            pendingIssueUrl = btn.dataset.url;
            document.getElementById('poModalAocCode').textContent = 'AOC: ' + btn.dataset.aocCode;
            document.getElementById('poSupplierName').value = '';
            document.getElementById('poSupplierAddr').value = '';
            document.getElementById('poAmount').value = btn.dataset.amount || '';
            document.getElementById('poDeliveryDate').value = '';
            modal.style.display = 'flex';
        });
    });

    btnCancel.addEventListener('click', () => { modal.style.display = 'none'; });
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

    btnConfirm.addEventListener('click', async () => {
        const supplierName = document.getElementById('poSupplierName').value.trim();
        const totalAmount  = document.getElementById('poAmount').value;
        if (!supplierName || !totalAmount) { showToast('Supplier name and amount are required.', true); return; }

        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Issuing…';

        try {
            const resp = await fetch(pendingIssueUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({
                    supplier_name:          supplierName,
                    supplier_address:       document.getElementById('poSupplierAddr').value.trim(),
                    total_amount:           parseFloat(totalAmount),
                    expected_delivery_date: document.getElementById('poDeliveryDate').value || null,
                }),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                showToast('Purchase Order issued. Reloading…');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(json.error || 'Failed to issue PO.', true);
            }
        } catch { showToast('Network error.', true); }
        finally { btnConfirm.disabled = false; btnConfirm.innerHTML = '<i class="ti ti-file-invoice"></i> Issue PO'; }
    });

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
