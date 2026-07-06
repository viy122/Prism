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
        --m: var(--crimson); --m-dk: var(--crimson-dark);
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-issued    { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-delivery  { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-complete  { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-paid      { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }
    .badge-draft     { background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }
    .badge-routing   { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-signed    { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 9px; font-size: 11px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-primary { background: var(--m); color: #fff; }
    .btn-primary:hover:not(:disabled) { background: var(--m-dk); }
    .btn-green { background: #3b6d11; color: #fff; }
    .btn-green:hover:not(:disabled) { background: #2e560d; }
    .btn-ghost { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
    .btn-ghost:hover:not(:disabled) { background: var(--s200); }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .eligible-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px; }
    .eligible-card { border: 1.5px solid var(--s200); border-radius: 12px; padding: 14px 18px; display: flex; flex-direction: column; gap: 8px; }
    .eligible-card h4 { font-size: 13px; font-weight: 700; color: var(--s900); }
    .eligible-card p { font-size: 12px; color: var(--s500); }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .form-field { display: flex; flex-direction: column; gap: 4px; }
    .form-field.full { grid-column: 1 / -1; }
    .form-field label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); }
    .form-field input, .form-field textarea { height: 38px; padding: 0 12px; border-radius: 9px; border: 1px solid var(--s200); font-size: 13px; font-family: 'Poppins', sans-serif; color: var(--s700); outline: none; transition: border-color .2s; }
    .form-field input:focus { border-color: var(--m); }
    .form-field textarea { height: auto; padding: 10px 12px; resize: vertical; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

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
            <p class="page-hdr-sub">Issue POs from fully-signed AOCs and track delivery status through to payment.</p>
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

    {{-- ── PO List ── --}}
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">PO Tracker</p>
                <h2 class="card-title">Purchase Order List</h2>
            </div>
        </div>

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
                        <th>Delivery Status</th>
                        <th>Expected Delivery</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrders as $po)
                    @php
                        $badgeCls = match(true) {
                            $po['status'] === 'paid'                                           => 'badge-paid',
                            in_array($po['status'], ['complete_delivery', 'receipt_uploaded']) => 'badge-complete',
                            in_array($po['status'], ['awaiting_delivery', 'partial_delivery']) => 'badge-delivery',
                            default => 'badge-issued',
                        };
                        $sigBadge = match($po['signatoryStage']) {
                            'fully_signed' => 'badge-signed',
                            'draft'        => 'badge-draft',
                            default        => 'badge-routing',
                        };
                        $sigAdvLabel = $po['signatoryStage'] === 'draft'
                            ? 'Route'
                            : ($po['currentStageType'] === 'routing' ? 'Mark Forwarded' : 'Mark Signed');
                    @endphp
                    <tr>
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $po['poNumber'] }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['aocCode'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['office'] }}</td>
                        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['supplier'] }}</td>
                        <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                        <td><span class="badge {{ $sigBadge }}" data-po-sig-badge="{{ $po['id'] }}">{{ $po['signatoryLabel'] }}</span></td>
                        <td><span class="badge {{ $badgeCls }}" data-po-status-badge="{{ $po['id'] }}">{{ $po['statusLabel'] }}</span></td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['expectedDate'] }}</td>
                        <td>
                            @if($po['signatoryStage'] !== 'fully_signed')
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <button class="btn btn-green btn-advance-po-sig" data-url="{{ $po['advanceUrl'] }}" data-po-id="{{ $po['id'] }}">
                                        <i class="ti ti-circle-arrow-right"></i> {{ $sigAdvLabel }}
                                    </button>
                                    @if($po['signatoryStage'] !== 'draft')
                                        <button class="btn btn-ghost btn-return-po-sig" data-url="{{ $po['returnUrl'] }}" data-po-id="{{ $po['id'] }}">
                                            <i class="ti ti-circle-arrow-left"></i> Return
                                        </button>
                                    @endif
                                </div>
                            @elseif($po['status'] === 'paid')
                                <span style="font-size:11px;color:#3b6d11;font-weight:700;">✓ Payment Made</span>
                            @elseif($po['nextStatus'])
                                <button class="btn btn-green btn-advance-po" data-url="{{ $po['updateUrl'] }}" data-po-id="{{ $po['id'] }}" data-next-status="{{ $po['nextStatus'] }}">
                                    <i class="ti ti-circle-arrow-right"></i>
                                    @php
                                        $nextLabel = match($po['nextStatus']) {
                                            'awaiting_delivery' => 'Forward to Supply Office',
                                            'partial_delivery'  => 'Mark Partial Delivery',
                                            'complete_delivery' => 'Mark Complete Delivery',
                                            'receipt_uploaded'  => 'Forward to Accounting',
                                            default             => 'Advance',
                                        };
                                    @endphp
                                    {{ $nextLabel }}
                                </button>
                            @else
                                <span style="font-size:11px;color:var(--s400);">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
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

{{-- Return PO signatory-stage modal --}}
<div id="poReturnModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.4);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:28px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:4px;">Return PO</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:16px;">Please provide a reason for returning this PO to draft.</p>
        <textarea id="returnPoRemarks" rows="3" style="width:100%;padding:10px 14px;border-radius:10px;border:1px solid #e2e8f0;font-size:13px;font-family:inherit;resize:vertical;box-sizing:border-box;" placeholder="Reason for return…"></textarea>
        <div style="display:flex;gap:8px;margin-top:14px;">
            <button class="btn btn-ghost" id="btnCancelPoReturn" style="flex:1;">Cancel</button>
            <button class="btn" id="btnConfirmPoReturn" style="flex:1;background:#a32d2d;color:#fff;"><i class="ti ti-send"></i> Confirm Return</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const toastEl    = document.getElementById('poToast');
    const modal      = document.getElementById('poModal');
    const btnCancel  = document.getElementById('btnCancelPo');
    const btnConfirm = document.getElementById('btnConfirmPo');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;

    let pendingIssueUrl = null;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 2800);
    }

    function statusBadgeClass(status) {
        if (status === 'paid') return 'badge-paid';
        if (['complete_delivery','receipt_uploaded'].includes(status)) return 'badge-complete';
        if (['awaiting_delivery','partial_delivery'].includes(status)) return 'badge-delivery';
        return 'badge-issued';
    }

    const statusLabels = {
        issued: 'Issued to Supplier',
        awaiting_delivery: 'Awaiting Delivery (Supply Office)',
        partial_delivery: 'Partial Delivery (Supply Office)',
        complete_delivery: 'Complete Delivery (Supply Office)',
        receipt_uploaded: 'At Accounting – For Payment',
        paid: 'Paid – Cashier Payment Made',
    };

    /* ── PO signatory chain: advance ── */
    document.querySelectorAll('.btn-advance-po-sig').forEach(btn => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i>';
            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    const badge = document.querySelector(`[data-po-sig-badge="${btn.dataset.poId}"]`);
                    if (badge) {
                        badge.textContent = json.signatoryLabel;
                        badge.className = 'badge ' + (json.signatoryStage === 'fully_signed' ? 'badge-signed' : 'badge-routing');
                    }
                    showToast(json.signatoryLabel);
                    if (json.signatoryStage === 'fully_signed') {
                        setTimeout(() => location.reload(), 900);   // reveal delivery controls
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> ' + (json.currentStageType === 'routing' ? 'Mark Forwarded' : 'Mark Signed');
                    }
                } else {
                    showToast(json.error || 'Failed.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Route';
                }
            } catch { showToast('Network error.', true); btn.disabled = false; btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Route'; }
        });
    });

    /* ── PO signatory chain: return ── */
    const poRetModal   = document.getElementById('poReturnModal');
    const poRetIn      = document.getElementById('returnPoRemarks');
    const btnPoRetOk   = document.getElementById('btnConfirmPoReturn');
    const btnPoRetNo   = document.getElementById('btnCancelPoReturn');
    let pendingPoReturnUrl = null;

    document.querySelectorAll('.btn-return-po-sig').forEach(btn => {
        btn.addEventListener('click', () => {
            pendingPoReturnUrl = btn.dataset.url;
            poRetIn.value = '';
            poRetModal.style.display = 'flex';
        });
    });
    btnPoRetNo.addEventListener('click', () => { poRetModal.style.display = 'none'; });
    poRetModal.addEventListener('click', e => { if (e.target === poRetModal) poRetModal.style.display = 'none'; });

    btnPoRetOk.addEventListener('click', async () => {
        const reason = poRetIn.value.trim();
        if (!reason) { showToast('Reason is required.', true); return; }
        btnPoRetOk.disabled = true;
        try {
            const resp = await fetch(pendingPoReturnUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ remarks: reason }),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                showToast('PO returned to draft. Reloading…');
                poRetModal.style.display = 'none';
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(json.error || 'Failed to return PO.', true);
            }
        } catch { showToast('Network error.', true); }
        finally { btnPoRetOk.disabled = false; }
    });

    /* ── Open Issue PO modal ── */
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

    /* ── Issue PO submit ── */
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

    /* ── Advance PO status ── */
    document.querySelectorAll('.btn-advance-po').forEach(btn => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i>';
            const nextStatus = btn.dataset.nextStatus;

            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ _action: 'advance' }),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    const badge = document.querySelector(`[data-po-status-badge="${btn.dataset.poId}"]`);
                    if (badge) {
                        badge.textContent = json.statusLabel;
                        badge.className   = 'badge ' + statusBadgeClass(json.status);
                    }
                    showToast('PO status updated: ' + json.statusLabel);
                    if (json.status === 'receipt_uploaded') {
                        btn.closest('td').innerHTML = '<span style="font-size:11px;color:var(--s400);">Awaiting Payment</span>';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Advance';
                        btn.dataset.nextStatus = json.status; // update for next click
                    }
                } else {
                    showToast(json.error || 'Failed.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Advance';
                }
            } catch { showToast('Network error.', true); btn.disabled = false; btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Advance'; }
        });
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
