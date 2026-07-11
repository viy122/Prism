@extends('prism.layouts.app')
@section('title', 'Cashier | PRISM')

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
        --m: var(--crimson);
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .stat-card { background: var(--s50); border: 1px solid var(--s200); border-radius: 14px; padding: 18px 20px; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .14em; color: var(--s500); margin-bottom: 6px; }
    .stat-value { font-size: 26px; font-weight: 900; color: var(--s900); letter-spacing: -.5px; }
    .stat-value.green { color: #3b6d11; }
    .stat-value.crimson { color: var(--m); }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-processing { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-paid       { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-upload { background: #5b21b6; color: #fff; }
    .btn-upload:hover:not(:disabled) { background: #4c1d95; }
    .btn-neutral { background: #e2e8f0; color: #334155; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .receipt-link { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #5b21b6; text-decoration: none; }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 160px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); padding: 28px; text-align: center; }
    .empty-state i { font-size: 36px; color: var(--s300); }
    .empty-state p { font-size: 13px; color: var(--s400); max-width: 240px; line-height: 1.6; }

    /* Upload modal */
    .cash-modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; }
    .cash-modal-backdrop.open { display: flex; }
    .cash-modal { background: #fff; border-radius: 18px; width: 100%; max-width: 460px; padding: 24px 26px; box-shadow: 0 24px 60px rgba(0,0,0,.25); display: flex; flex-direction: column; gap: 14px; }
    .cash-modal h3 { font-size: 16px; font-weight: 800; color: var(--s900); }
    .cash-dropzone { display: flex; flex-direction: column; align-items: center; gap: 8px; border: 2px dashed var(--s300); border-radius: 14px; background: var(--s50); padding: 24px 18px; cursor: pointer; text-align: center; font-size: 12px; color: var(--s500); }
    .cash-dropzone i { font-size: 28px; color: var(--s400); }
    .cash-remarks { width: 100%; border: 1px solid var(--s300); border-radius: 9px; padding: 8px 12px; font-size: 12px; font-family: inherit; resize: vertical; }
    .cash-status { border-radius: 10px; padding: 10px 14px; font-size: 12px; font-weight: 600; }
    .cash-status.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .cash-status.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .cash-actions { display: flex; justify-content: flex-end; gap: 10px; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 900px) { .stat-grid { grid-template-columns: 1fr; } .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-receipt-2"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Cashier</p>
            <h1 class="page-hdr-title">Payments & Receipts</h1>
            <p class="page-hdr-sub">Upload the official receipt for POs in payment processing to mark them as Paid — the final step of the procurement flow.</p>
        </div>
    </div>

    {{-- ── Summary ── --}}
    <div class="stat-grid">
        <div class="stat-card">
            <p class="stat-label">For Payment (Receipt Needed)</p>
            <p class="stat-value crimson">{{ $summary['forPayment'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Payments Made</p>
            <p class="stat-value green">{{ $summary['totalPaid'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Amount Released</p>
            <p class="stat-value">₱{{ number_format($summary['totalAmount'], 0) }}</p>
        </div>
    </div>

    {{-- ── For payment ── --}}
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Action Required</p>
                <h2 class="card-title">POs Awaiting Payment — Upload Receipt</h2>
            </div>
        </div>

        @if(count($forPayment) === 0)
            <div class="empty-state">
                <i class="ti ti-checks"></i>
                <p>No POs are waiting for payment right now.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Office</th>
                        <th>Description</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Processing Since</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forPayment as $po)
                    <tr id="po-row-{{ $po['id'] }}">
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $po['poNumber'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['office'] }}</td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['title'] }}</td>
                        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['supplier'] }}</td>
                        <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                        <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $po['processingAt'] }}</td>
                        <td><span class="badge badge-processing">Processing Payment</span></td>
                        <td>
                            <button class="btn btn-upload btn-open-receipt"
                                data-url="{{ $po['uploadUrl'] }}"
                                data-po-id="{{ $po['id'] }}"
                                data-po-number="{{ $po['poNumber'] }}">
                                <i class="ti ti-upload"></i>
                                Upload Receipt
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Payment history ── --}}
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">History</p>
                <h2 class="card-title">Payment History</h2>
            </div>
        </div>

        @if(count($paymentHistory) === 0)
            <div class="empty-state">
                <i class="ti ti-history"></i>
                <p>No payments recorded yet.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Office</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Paid Date</th>
                        <th>Paid By</th>
                        <th>Receipt</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentHistory as $po)
                    <tr>
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $po['poNumber'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);">{{ $po['office'] }}</td>
                        <td>{{ $po['supplier'] }}</td>
                        <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['paidAt'] }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['paidBy'] }}</td>
                        <td>
                            @if($po['receiptUrl'])
                                <a class="receipt-link" href="{{ $po['receiptUrl'] }}" target="_blank" rel="noopener"><i class="ti ti-file-text"></i> View</a>
                            @else
                                <span style="font-size:11px;color:var(--s400);">—</span>
                            @endif
                        </td>
                        <td><span class="badge badge-paid">Payment Made ✓</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

{{-- ── Receipt upload modal ── --}}
<div class="cash-modal-backdrop" id="cashModalBackdrop">
    <div class="cash-modal">
        <h3 id="cashModalTitle">Upload Payment Receipt</h3>
        <label class="cash-dropzone" id="cashDropzone">
            <input type="file" id="cashReceiptInput" accept="application/pdf,image/jpeg,image/png" hidden>
            <i class="ti ti-file-upload"></i>
            <span id="cashDropzoneText">Tap to choose the official receipt (PDF or photo)</span>
        </label>
        <div>
            <p style="font-size:11px;font-weight:700;color:var(--s600);margin-bottom:6px;">Remarks (optional)</p>
            <textarea id="cashRemarks" class="cash-remarks" rows="2" maxlength="1000" placeholder="e.g. OR number, payment mode…"></textarea>
        </div>
        <div class="cash-status" id="cashStatus" style="display:none;"></div>
        <div class="cash-actions">
            <button class="btn btn-neutral" id="cashCancelBtn">Cancel</button>
            <button class="btn btn-upload" id="cashSubmitBtn" disabled><i class="ti ti-check"></i> Upload & Mark Paid</button>
        </div>
    </div>
</div>

<div class="pr-toast" id="cashToast"></div>

@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const toastEl   = document.getElementById('cashToast');
    const backdrop  = document.getElementById('cashModalBackdrop');
    const titleEl   = document.getElementById('cashModalTitle');
    const input     = document.getElementById('cashReceiptInput');
    const dzText    = document.getElementById('cashDropzoneText');
    const remarksEl = document.getElementById('cashRemarks');
    const statusEl  = document.getElementById('cashStatus');
    const submitBtn = document.getElementById('cashSubmitBtn');

    let ctx = null; // { url, poId, poNumber }

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 3000);
    }

    function setStatus(kind, msg) {
        if (!kind) { statusEl.style.display = 'none'; return; }
        statusEl.style.display = 'block';
        statusEl.className = 'cash-status ' + kind;
        statusEl.textContent = msg;
    }

    document.querySelectorAll('.btn-open-receipt').forEach(btn => {
        btn.addEventListener('click', () => {
            ctx = { url: btn.dataset.url, poId: btn.dataset.poId, poNumber: btn.dataset.poNumber };
            titleEl.textContent = 'Upload Payment Receipt — ' + ctx.poNumber;
            input.value = '';
            remarksEl.value = '';
            dzText.textContent = 'Tap to choose the official receipt (PDF or photo)';
            setStatus(null);
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-check"></i> Upload & Mark Paid';
            backdrop.classList.add('open');
        });
    });

    document.getElementById('cashDropzone').addEventListener('click', () => input.click());
    input.addEventListener('change', () => {
        if (input.files[0]) {
            dzText.textContent = input.files[0].name;
            submitBtn.disabled = false;
        }
    });

    function closeModal() { backdrop.classList.remove('open'); ctx = null; }
    document.getElementById('cashCancelBtn').addEventListener('click', closeModal);
    backdrop.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });

    submitBtn.addEventListener('click', async () => {
        if (!ctx || !input.files[0]) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Uploading…';

        const fd = new FormData();
        fd.append('receipt', input.files[0]);
        if (remarksEl.value) fd.append('remarks', remarksEl.value);

        try {
            const resp = await fetch(ctx.url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                setStatus('success', 'Receipt uploaded — ' + ctx.poNumber + ' is now marked Paid (' + json.paidAt + ').');
                showToast('Payment made for ' + ctx.poNumber + '.');
                const row = document.getElementById('po-row-' + ctx.poId);
                if (row) { row.style.opacity = '.45'; setTimeout(() => row.remove(), 600); }
                setTimeout(() => window.location.reload(), 1400);
            } else {
                setStatus('error', json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Upload failed.'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-check"></i> Upload & Mark Paid';
            }
        } catch {
            setStatus('error', 'Network error — please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ti ti-check"></i> Upload & Mark Paid';
        }
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
