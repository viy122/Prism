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
        --m: var(--crimson); --gold: #c9a84c;
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

    .po-grid { display: grid; grid-template-columns: minmax(0, 1fr) 440px; gap: 20px; align-items: start; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 58vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; cursor: pointer; }
    tbody tr:hover { background: var(--crimson-mid); }
    tbody tr.selected { background: rgba(139,26,28,.07); }
    tbody tr.selected td:first-child { border-left: 3px solid var(--m); }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-processing { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-paid       { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }
    .badge-pending    { background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--s400); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .search-input { height: 40px; width: 100%; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 16px 0 36px; font-size: 13px; font-weight: 500; color: var(--s900); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .search-input::placeholder { color: var(--s400); }

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

    .sig-timeline { display: flex; align-items: center; gap: 0; margin-bottom: 4px; flex-wrap: wrap; row-gap: 14px; }
    .sig-step { display: flex; flex-direction: column; align-items: center; flex: 1; min-width: 64px; position: relative; }
    .sig-step:not(:last-child)::after { content: ''; position: absolute; top: 10px; left: 50%; width: 100%; height: 2px; background: var(--s200); z-index: 0; }
    .sig-step.done::after { background: #5b21b6; }
    .sig-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--s300); background: var(--white); z-index: 1; position: relative; transition: all .2s; }
    .sig-step.done .sig-dot { background: #5b21b6; border-color: #5b21b6; }
    .sig-step.active .sig-dot { background: var(--m); border-color: var(--m); box-shadow: 0 0 0 3px rgba(139,26,28,.2); }
    .sig-label { font-size: 9px; font-weight: 700; text-align: center; color: var(--s400); margin-top: 5px; line-height: 1.3; max-width: 84px; }
    .sig-step.done .sig-label, .sig-step.active .sig-label { color: var(--s700); }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-upload { background: #5b21b6; color: #fff; }
    .btn-upload:hover:not(:disabled) { background: #4c1d95; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .receipt-input-row { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
    .cash-dropzone { display: flex; align-items: center; gap: 8px; border: 1.5px dashed var(--s300); border-radius: 10px; background: var(--s50); padding: 10px 14px; cursor: pointer; text-align: left; font-size: 12px; color: var(--s500); }
    .cash-dropzone i { font-size: 18px; color: var(--s400); }
    .receipt-link { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; color: #5b21b6; text-decoration: none; }

    .remarks-textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 13px; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 60px; outline: none; transition: border-color .2s; line-height: 1.6; box-sizing: border-box; }
    .remarks-textarea:focus { border-color: var(--m); }
    .remarks-textarea::placeholder { color: var(--s300); }

    .cash-status { border-radius: 10px; padding: 10px 14px; font-size: 12px; font-weight: 600; margin-top: 8px; }
    .cash-status.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .cash-status.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

    /* Receipt preview — uploaded PDF or photo of the official receipt */
    .receipt-preview { border-radius: 12px; border: 1px solid var(--s200); background: var(--s50); overflow: hidden; aspect-ratio: 8.5 / 11; display: flex; align-items: center; justify-content: center; position: relative; margin-bottom: 4px; }
    .receipt-preview iframe { width: 100%; height: 100%; border: none; }
    .pdf-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 100%; color: var(--s400); }
    .pdf-placeholder i { font-size: 42px; color: var(--s300); }
    .pdf-placeholder span { font-size: 12px; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 1100px) { .po-grid { grid-template-columns: 1fr; } }
    @media (max-width: 900px) { .stat-grid { grid-template-columns: 1fr; } .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-receipt-2"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Cashier</p>
            <h1 class="page-hdr-title">Payments &amp; Receipts</h1>
            <p class="page-hdr-sub">Every issued PO's delivery and payment journey. Upload the official receipt once a PO reaches Processing Payment to mark it Paid — the final step of the procurement flow.</p>
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

    <div class="po-grid">

        {{-- ── Left: PO list ── --}}
        <div class="card" style="padding-bottom: 22px;">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">All Purchase Orders</p>
                    <h2 class="card-title">Purchase Orders</h2>
                </div>
                <span class="count-chip" id="poVisibleCount" style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:var(--s100);color:var(--s700);border:1px solid var(--s200);">{{ count($purchaseOrders) }} PO{{ count($purchaseOrders) !== 1 ? 's' : '' }}</span>
            </div>

            @if(count($purchaseOrders) > 0)
                <div class="search-wrap" style="margin-bottom:14px;">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="search" id="poSearch" placeholder="Search by PO number, office, or supplier">
                </div>
            @endif

            @if(count($purchaseOrders) === 0)
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:180px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:32px;text-align:center;">
                    <i class="ti ti-receipt-off" style="font-size:38px;color:var(--s300);"></i>
                    <p style="font-size:13px;color:var(--s400);max-width:240px;line-height:1.6;">No purchase orders issued yet.</p>
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrders as $po)
                            @php
                                $badgeCls = match(true) {
                                    $po['canAct']          => 'badge-processing',
                                    $po['status'] === 'paid' => 'badge-paid',
                                    default                 => 'badge-pending',
                                };
                            @endphp
                            <tr data-po-row data-po-id="{{ $po['id'] }}" data-search="{{ strtolower($po['poNumber'] . ' ' . $po['office'] . ' ' . $po['supplier']) }}" tabindex="0">
                                <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $po['poNumber'] }}</td>
                                <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['office'] }}</td>
                                <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['supplier'] }}</td>
                                <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                                <td><span class="badge {{ $badgeCls }}" data-po-badge="{{ $po['id'] }}">{{ $po['canAct'] ? 'Needs You' : $po['statusLabel'] }}</span></td>
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
                <p>Select a PO from the list to view its delivery and payment journey.</p>
            </div>

            <div class="detail-content" id="detailContent">

                {{-- Payment receipt preview --}}
                <div class="receipt-preview" id="receiptPreview">
                    <div class="pdf-placeholder">
                        <i class="ti ti-file-off"></i>
                        <span>No receipt uploaded yet</span>
                    </div>
                </div>

                <div class="detail-fields">
                    <div class="detail-field"><label>PO Number</label><span id="fPoNumber">—</span></div>
                    <div class="detail-field"><label>Office</label><span id="fOffice">—</span></div>
                    <div class="detail-field full"><label>Item / Title</label><span id="fTitle">—</span></div>
                    <div class="detail-field"><label>Supplier</label><span id="fSupplier">—</span></div>
                    <div class="detail-field"><label>Amount</label><span id="fAmount">—</span></div>
                    <div class="detail-field full"><label>Remarks on File</label><span id="fRemarks" style="white-space:pre-line;">—</span></div>
                </div>

                <div>
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--s500);margin-bottom:10px;">Delivery &amp; Payment Status</div>
                    <div class="sig-timeline" id="poTimeline"></div>
                </div>

                <div id="actionArea"></div>

            </div>
        </div>

    </div>

</div>

<div class="pr-toast" id="cashToast"></div>

@endsection

<script type="application/json" id="poData">@json($purchaseOrders)</script>

@push('scripts')
<script>
(function () {
    const allPos      = JSON.parse(document.getElementById('poData').textContent);
    const rows        = document.querySelectorAll('[data-po-row]');
    const emptyEl      = document.getElementById('detailEmpty');
    const contentEl    = document.getElementById('detailContent');
    const titleEl       = document.getElementById('detailPoNumber');
    const toastEl        = document.getElementById('cashToast');
    const csrfToken       = document.querySelector('meta[name="csrf-token"]').content;
    const poSearch        = document.getElementById('poSearch');
    const poCount         = document.getElementById('poVisibleCount');
    const actionArea      = document.getElementById('actionArea');

    let activePo = null;
    let saving   = false;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 2800);
    }

    function timelineHtml(po) {
        return po.chain.map(step =>
            `<div class="sig-step${step.status === 'done' ? ' done' : ''}${step.status === 'active' ? ' active' : ''}"><div class="sig-dot"></div><span class="sig-label">${step.label}</span></div>`
        ).join('');
    }

    function renderAction(po) {
        if (po.status === 'paid') {
            actionArea.innerHTML = `
                <div style="font-size:12px;font-weight:700;color:#166534;">✓ Payment Made — ${po.paidAt}${po.paidBy && po.paidBy !== '—' ? ' by ' + po.paidBy : ''}</div>
                ${po.receiptUrl ? `<a class="receipt-link" href="${po.receiptUrl}" target="_blank" rel="noopener" style="margin-top:6px;"><i class="ti ti-file-text"></i> Open in New Tab</a>` : ''}
            `;
            return;
        }

        if (!po.canAct) {
            actionArea.innerHTML = `<p style="font-size:11px;color:var(--s500);">This PO hasn't reached payment processing yet — nothing to do here.</p>`;
            return;
        }

        actionArea.innerHTML = `
            <div class="receipt-input-row">
                <label class="cash-dropzone" id="cashDropzone">
                    <input type="file" id="cashReceiptInput" accept="application/pdf,image/jpeg,image/png" hidden>
                    <i class="ti ti-file-upload"></i>
                    <span id="cashDropzoneText">Tap to choose the official receipt (PDF or photo)</span>
                </label>
                <textarea id="cashRemarks" class="remarks-textarea" rows="2" maxlength="1000" placeholder="Remarks (optional) — e.g. OR number, payment mode…"></textarea>
                <div id="cashStatus" class="cash-status" style="display:none;"></div>
                <button class="btn btn-upload" id="cashSubmitBtn" disabled><i class="ti ti-check"></i> Upload &amp; Mark Paid</button>
            </div>
        `;

        const input     = document.getElementById('cashReceiptInput');
        const dzText    = document.getElementById('cashDropzoneText');
        const submitBtn = document.getElementById('cashSubmitBtn');
        const statusEl  = document.getElementById('cashStatus');
        const remarksEl = document.getElementById('cashRemarks');

        function setStatus(kind, msg) {
            if (!kind) { statusEl.style.display = 'none'; return; }
            statusEl.style.display = 'block';
            statusEl.className = 'cash-status ' + kind;
            statusEl.textContent = msg;
        }

        document.getElementById('cashDropzone').addEventListener('click', () => input.click());
        input.addEventListener('change', () => {
            if (input.files[0]) {
                dzText.textContent = input.files[0].name;
                submitBtn.disabled = false;
            }
        });

        submitBtn.addEventListener('click', async () => {
            if (!input.files[0] || saving) return;
            saving = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Uploading…';

            const fd = new FormData();
            fd.append('receipt', input.files[0]);
            if (remarksEl.value) fd.append('remarks', remarksEl.value);

            try {
                const resp = await fetch(po.uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd,
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    po.status      = 'paid';
                    po.statusLabel = 'Paid – Payment Made (Cashier)';
                    po.canAct      = false;
                    po.paidAt      = json.paidAt;
                    po.paidBy      = 'You';
                    po.receiptUrl  = json.receiptUrl;
                    po.chain       = po.chain.map(s => Object.assign({}, s, { status: s.key === 'paid' ? 'active' : 'done' }));

                    const badge = document.querySelector(`[data-po-badge="${po.id}"]`);
                    if (badge) { badge.textContent = po.statusLabel; badge.className = 'badge badge-paid'; }

                    showToast('Payment made for ' + po.poNumber + '.');
                    renderDetail(po);
                } else {
                    setStatus('error', json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Upload failed.'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ti ti-check"></i> Upload &amp; Mark Paid';
                }
            } catch {
                setStatus('error', 'Network error — please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-check"></i> Upload &amp; Mark Paid';
            } finally {
                saving = false;
            }
        });
    }

    function renderDetail(po) {
        const previewEl = document.getElementById('receiptPreview');
        previewEl.innerHTML = po.receiptUrl
            ? `<iframe src="${po.receiptUrl}" title="Payment Receipt"></iframe>`
            : `<div class="pdf-placeholder"><i class="ti ti-file-off"></i><span>No receipt uploaded yet</span></div>`;

        document.getElementById('fPoNumber').textContent = po.poNumber;
        document.getElementById('fOffice').textContent   = po.office;
        document.getElementById('fTitle').textContent    = po.title;
        document.getElementById('fSupplier').textContent = po.supplier;
        document.getElementById('fAmount').textContent   = '₱' + Number(po.totalAmount).toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('fRemarks').textContent  = po.remarks;
        document.getElementById('poTimeline').innerHTML  = timelineHtml(po);
        renderAction(po);
    }

    function openPo(po) {
        activePo = po;
        rows.forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-po-id="${po.id}"]`)?.classList.add('selected');
        titleEl.textContent = po.poNumber;
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

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
