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
        --m: var(--crimson); --m-dk: var(--crimson-dark);
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
    .badge-paid       { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .icon-btn { width: 34px; height: 34px; border-radius: 9px; border: 1px solid transparent; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all .15s; font-size: 16px; }
    .icon-btn-check { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .icon-btn-check:hover:not(:disabled) { background: #bbf7d0; }
    .icon-btn-x { background: var(--s100); color: var(--s400); border-color: var(--s200); cursor: default; }
    .icon-btn-view { background: #e6f1fb; color: #185fa5; border-color: #b5d4f4; }
    .icon-btn-view:hover:not(:disabled) { background: #b5d4f4; }
    .icon-btn:disabled { opacity: .5; cursor: not-allowed; }
    .payment-made-cell { display: flex; align-items: center; gap: 8px; }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--s400); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .search-input { height: 40px; width: 100%; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 16px 0 36px; font-size: 13px; font-weight: 500; color: var(--s900); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .search-input::placeholder { color: var(--s400); }
    .search-toolbar { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; }
    .search-toolbar .search-wrap { flex: 1; min-width: 0; }
    .filter-select { height: 40px; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 30px 0 14px; font-size: 12.5px; font-weight: 600; color: var(--s700); font-family: 'Poppins', sans-serif; outline: none; cursor: pointer; transition: border-color .15s, box-shadow .15s; flex-shrink: 0; }
    .filter-select:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    @media (max-width: 640px) { .search-toolbar { flex-wrap: wrap; } .search-toolbar .search-wrap { flex-basis: 100%; } }

    /* Colors here use the globally-defined --crimson/--crimson-dark/--s200 (set
       on :root in the base layout) rather than this page's --m/--s* aliases
       (scoped to .content) — this modal sits outside .content as a sibling, so
       a content-scoped variable would resolve to nothing there. */
    .cm-modal-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(15,23,42,.5); display: none; align-items: center; justify-content: center; padding: 20px; }
    .cm-modal-overlay.open { display: flex; }
    .cm-modal-card { background: #fff; border-radius: 16px; box-shadow: 0 6px 24px rgba(0,0,0,.18); width: 100%; max-width: 420px; padding: 24px 26px; font-family: 'Poppins', sans-serif; }
    .cm-modal-title { font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
    .cm-modal-sub { font-size: 12px; color: #64748b; margin-bottom: 18px; }
    .cm-form-field { margin-bottom: 14px; }
    .cm-form-field label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 6px; }
    .cm-form-field textarea { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 11px; font-size: 12.5px; color: #334155; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 64px; }
    .cm-form-field textarea:focus { outline: none; border-color: var(--crimson); }
    .cm-form-hint { font-size: 10.5px; color: #94a3b8; margin-top: 4px; }
    .cm-file-picker { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .cm-choose-btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 8px; border: 1.5px dashed #cbd5e1; background: #f8fafc; color: #475569; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .cm-choose-btn:hover { border-color: var(--crimson); color: var(--crimson); }
    .cm-file-view { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 12px; border-radius: 8px; background: #e6f1fb; border: 1px solid #b5d4f4; color: #185fa5; font-size: 12px; font-weight: 700; text-decoration: none; max-width: 220px; }
    .cm-file-view span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cm-file-view:hover { background: #b5d4f4; }
    .cm-modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }
    .cm-btn { height: 38px; padding: 0 18px; border-radius: 10px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: 1px solid transparent; transition: all .15s; }
    .cm-btn:disabled { opacity: .5; cursor: not-allowed; }
    .cm-btn-cancel { background: #e2e8f0; color: #334155; border-color: #cbd5e1; }
    .cm-btn-cancel:hover:not(:disabled) { background: #cbd5e1; }
    .cm-btn-submit { background: var(--crimson); color: #fff; }
    .cm-btn-submit:hover:not(:disabled) { background: var(--crimson-dark); }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 160px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); padding: 28px; text-align: center; }
    .empty-state i { font-size: 36px; color: var(--s300); }
    .empty-state p { font-size: 13px; color: var(--s400); max-width: 240px; line-height: 1.6; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 900px) { .stat-grid { grid-template-columns: 1fr 1fr; } .content { padding: 16px 16px 40px; } }
    @media (max-width: 580px) { .stat-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-receipt-2"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Cashier</p>
            <h1 class="page-hdr-title">Payments &amp; Receipts</h1>
            <p class="page-hdr-sub">Release payment for POs Accounting has processed. Upload the official receipt to mark a PO Payment Made — the final step of the procurement flow.</p>
        </div>
    </div>

    {{-- ── Summary stats ── --}}
    <div class="stat-grid">
        <div class="stat-card">
            <p class="stat-label">Processing — For Payment</p>
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

    {{-- ── Processing, waiting for the Cashier to release payment ── --}}
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Action Required</p>
                <h2 class="card-title">Processing Payment — Release Payment</h2>
            </div>
            @if(count($forPayment) > 0)
            <span style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:#faeeda;color:#854f0b;border:1px solid #fac775;">{{ count($forPayment) }} pending</span>
            @endif
        </div>

        @if(count($forPayment) === 0)
            <div class="empty-state">
                <i class="ti ti-checks"></i>
                <p>No POs are currently waiting for payment release.</p>
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
                        <th>Payment Made</th>
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
                        <td><span class="badge badge-processing">{{ $po['statusLabel'] }}</span></td>
                        <td>
                            <div class="payment-made-cell">
                                <button type="button" class="icon-btn icon-btn-view btn-view-po-doc"
                                    data-pdf="{{ $po['pdfFile'] }}"
                                    data-po-number="{{ $po['poNumber'] }}"
                                    title="View PO Document">
                                    <i class="ti ti-file-text"></i>
                                </button>
                                <button type="button" class="icon-btn icon-btn-view btn-view-processing-doc"
                                    data-pdf="{{ $po['processingAttachment'] }}"
                                    data-po-number="{{ $po['poNumber'] }}"
                                    title="View Accounting's Payment Processing Attachment">
                                    <i class="ti ti-paperclip"></i>
                                </button>
                                <button type="button" class="icon-btn icon-btn-x" disabled title="Not yet paid">
                                    <i class="ti ti-x"></i>
                                </button>
                                <button type="button" class="icon-btn icon-btn-check btn-open-payment"
                                    data-url="{{ $po['uploadUrl'] }}"
                                    data-po-id="{{ $po['id'] }}"
                                    data-po-number="{{ $po['poNumber'] }}"
                                    title="Mark Payment Made">
                                    <i class="ti ti-check"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Recently Paid ── --}}
    @if(count($recentlyPaid) > 0)
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">History</p>
                <h2 class="card-title">Recently Paid Purchase Orders</h2>
            </div>
            <span style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:var(--s100);color:var(--s700);border:1px solid var(--s200);" id="paidVisibleCount">{{ count($recentlyPaid) }} shown</span>
        </div>

        <div class="search-toolbar">
            <div class="search-wrap">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="search-input" type="search" id="paidSearch" placeholder="Search by PO number, office, or supplier">
            </div>
            <select class="filter-select" id="paidOfficeFilter" title="Filter by office">
                <option value="">All Offices</option>
            </select>
            <select class="filter-select" id="paidSortOrder" title="Sort by paid date">
                <option value="desc">Newest → Oldest</option>
                <option value="asc">Oldest → Newest</option>
            </select>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Office</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Paid Date</th>
                        <th>Paid By (Cashier)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="paidTbody">
                    @foreach($recentlyPaid as $po)
                    <tr data-paid-row data-office="{{ $po['office'] }}" data-paid-at-raw="{{ $po['paidAtRaw'] }}" data-search="{{ strtolower($po['poNumber'] . ' ' . $po['office'] . ' ' . $po['supplier']) }}">
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $po['poNumber'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);">{{ $po['office'] }}</td>
                        <td>{{ $po['supplier'] }}</td>
                        <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['paidAt'] }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['paidBy'] }}</td>
                        <td><span class="badge badge-paid">Payment Made ✓</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<div class="pr-toast" id="cashToast"></div>

{{-- ── Mark Payment Made modal ── --}}
<div class="cm-modal-overlay" id="cmModalOverlay">
    <div class="cm-modal-card">
        <p class="cm-modal-title">Mark Payment Made</p>
        <p class="cm-modal-sub" id="cmModalSub">Attach the official receipt for this PO.</p>
        <form id="cmModalForm">
            <div class="cm-form-field">
                <label>Receipt (PDF/JPG/PNG, required)</label>
                <div class="cm-file-picker">
                    <button type="button" class="cm-choose-btn" id="cmChooseBtn">
                        <i class="ti ti-upload"></i> Choose File
                    </button>
                    <a href="#" class="cm-file-view" id="cmFileView" style="display:none;" target="_blank" rel="noopener" title="Click to view the attached file">
                        <i class="ti ti-file-text"></i>
                        <span id="cmFileViewName"></span>
                    </a>
                </div>
                <input type="file" id="cmReceiptInput" accept=".pdf,.jpg,.jpeg,.png" required style="display:none;">
                <p class="cm-form-hint">Max 10MB. Click the file name above to preview what you attached.</p>
            </div>
            <div class="cm-form-field">
                <label>Remarks (optional)</label>
                <textarea id="cmRemarksInput" placeholder="e.g. OR number, payment mode…"></textarea>
            </div>
        </form>
        <div class="cm-modal-actions">
            <button type="button" class="cm-btn cm-btn-cancel" id="cmCancelBtn">Cancel</button>
            <button type="button" class="cm-btn cm-btn-submit" id="cmSubmitBtn">Submit</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const toastEl   = document.getElementById('cashToast');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 3000);
    }

    const overlay      = document.getElementById('cmModalOverlay');
    const modalSub      = document.getElementById('cmModalSub');
    const chooseBtn      = document.getElementById('cmChooseBtn');
    const receiptInput   = document.getElementById('cmReceiptInput');
    const fileView        = document.getElementById('cmFileView');
    const fileViewName    = document.getElementById('cmFileViewName');
    const remarksInput   = document.getElementById('cmRemarksInput');
    const cancelBtn      = document.getElementById('cmCancelBtn');
    const submitBtn       = document.getElementById('cmSubmitBtn');
    let activeBtn = null;
    let attachedFileUrl = null;

    function clearAttachedPreview() {
        if (attachedFileUrl) { URL.revokeObjectURL(attachedFileUrl); attachedFileUrl = null; }
        fileView.style.display = 'none';
        fileView.removeAttribute('href');
        fileViewName.textContent = '';
    }

    function openModal(btn) {
        activeBtn = btn;
        receiptInput.value = '';
        remarksInput.value = '';
        clearAttachedPreview();
        modalSub.textContent = `Attach the official receipt for ${btn.dataset.poNumber}.`;
        overlay.classList.add('open');
    }

    function closeModal() {
        overlay.classList.remove('open');
        clearAttachedPreview();
        activeBtn = null;
    }

    document.querySelectorAll('.btn-open-payment').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn));
    });

    // The attached file itself is a view-only preview link (opens in a new
    // tab) — click the "Choose File" button, not the file, to change it.
    chooseBtn.addEventListener('click', () => receiptInput.click());
    receiptInput.addEventListener('change', () => {
        const file = receiptInput.files[0];
        if (attachedFileUrl) URL.revokeObjectURL(attachedFileUrl);
        if (!file) { clearAttachedPreview(); return; }
        attachedFileUrl = URL.createObjectURL(file);
        fileView.href = attachedFileUrl;
        fileViewName.textContent = file.name;
        fileView.style.display = 'inline-flex';
    });

    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

    /* ── View PO Document / Accounting's processing attachment ── */
    function wireDocViewButtons(selector, missingMsg, titleSuffix) {
        document.querySelectorAll(selector).forEach(btn => {
            btn.addEventListener('click', () => {
                const pdf = btn.dataset.pdf;
                if (!pdf) { showToast(missingMsg, true); return; }
                if (window.prismInfoModal) {
                    window.prismInfoModal({
                        title: btn.dataset.poNumber + ' — ' + titleSuffix,
                        bodyHtml: `<iframe src="/storage/${pdf}#toolbar=0" style="width:100%;height:65vh;border:none;border-radius:8px;"></iframe>`,
                    });
                } else {
                    window.open('/storage/' + pdf, '_blank', 'noopener');
                }
            });
        });
    }
    wireDocViewButtons('.btn-view-po-doc', 'No PO document has been uploaded for this PO yet.', 'PO Document');
    wireDocViewButtons('.btn-view-processing-doc', "Accounting hasn't attached a payment processing file for this PO yet.", 'Payment Processing Attachment');

    submitBtn.addEventListener('click', async () => {
        if (!activeBtn) return;
        if (!receiptInput.files.length) {
            showToast('Please attach the receipt.', true);
            return;
        }

        const btn = activeBtn;
        const fd  = new FormData();
        fd.append('receipt', receiptInput.files[0]);
        if (remarksInput.value.trim()) fd.append('remarks', remarksInput.value.trim());

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting…';

        try {
            const resp = await fetch(btn.dataset.url, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body:    fd,
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                closeModal();
                showToast(`Payment made for ${btn.dataset.poNumber}.`);
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showToast(json.error || (json.errors ? Object.values(json.errors)[0][0] : 'Failed to mark payment made.'), true);
            }
        } catch {
            showToast('Network error. Please try again.', true);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit';
        }
    });

    /* ── Recently Paid: search + office filter + sort ── */
    const paidTbody       = document.getElementById('paidTbody');
    const paidSearch      = document.getElementById('paidSearch');
    const paidOfficeFilter = document.getElementById('paidOfficeFilter');
    const paidSortOrder   = document.getElementById('paidSortOrder');
    const paidCount       = document.getElementById('paidVisibleCount');

    if (paidTbody && paidSearch) {
        const paidRows = () => paidTbody.querySelectorAll('[data-paid-row]');

        // Office list varies per session, so populate it from the rows already
        // on the page instead of a fixed server-side list.
        const offices = [...new Set([...paidRows()].map(r => r.dataset.office).filter(o => o && o !== '—'))].sort();
        offices.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o;
            opt.textContent = o;
            paidOfficeFilter.appendChild(opt);
        });

        function applyPaidFilter() {
            const q      = paidSearch.value.trim().toLowerCase();
            const office = paidOfficeFilter.value;
            let visible = 0;
            paidRows().forEach(row => {
                const matchesSearch = !q || (row.dataset.search ?? '').includes(q);
                const matchesOffice = !office || row.dataset.office === office;
                const match = matchesSearch && matchesOffice;
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            paidCount.textContent = visible + ' shown';
        }

        function applyPaidSort() {
            const dir  = paidSortOrder.value;
            const rows = Array.from(paidRows());
            rows.sort((a, b) => {
                const ta = new Date(a.dataset.paidAtRaw || 0).getTime();
                const tb = new Date(b.dataset.paidAtRaw || 0).getTime();
                return dir === 'asc' ? ta - tb : tb - ta;
            });
            rows.forEach(row => paidTbody.appendChild(row));
        }

        paidSearch.addEventListener('input', applyPaidFilter);
        paidOfficeFilter.addEventListener('change', applyPaidFilter);
        paidSortOrder.addEventListener('change', applyPaidSort);
    }
})();
</script>
@endpush
