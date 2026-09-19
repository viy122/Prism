@extends('prism.layouts.app')
@section('title', 'Canvassing | PRISM')

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
    .card-title   { font-size: 16px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-not-started { background: var(--s100); color: var(--s500); border: 1px solid var(--s200); }
    .badge-in-progress { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-completed   { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

    .quote-list { display: flex; flex-direction: column; gap: 8px; margin: 12px 0; }
    .quote-row { display: flex; align-items: center; gap: 10px; border: 1px solid var(--s200); border-radius: 10px; padding: 8px 14px; font-size: 12px; background: var(--s50); cursor: pointer; transition: background .12s, border-color .12s; }
    .quote-row:hover { background: var(--s100); border-color: var(--s300); }
    .quote-row i { color: var(--s400); }
    .quote-supplier { font-weight: 700; color: var(--s700); }
    .quote-file { color: #1d4ed8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px; }
    .quote-row:hover .quote-file { text-decoration: underline; }
    .quote-actions { margin-left: auto; display: flex; gap: 8px; align-items: center; }
    .quote-link { font-size: 11px; font-weight: 700; color: #1d4ed8; text-decoration: none; }
    .quote-del { border: none; background: none; color: #b91c1c; cursor: pointer; font-size: 14px; }

    .upload-rows { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; }
    .upload-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .upload-row input[type="text"] { flex: 1; min-width: 160px; border: 1px solid var(--s300); border-radius: 9px; padding: 8px 12px; font-size: 12px; font-family: inherit; }
    .upload-row input[type="text"][readonly] { background: var(--s100); color: var(--s600); cursor: not-allowed; }
    .file-pick { display: inline-flex; align-items: center; gap: 6px; border: 1.5px dashed var(--s300); border-radius: 9px; padding: 8px 14px; font-size: 12px; color: var(--s500); cursor: pointer; background: var(--s50); }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-upload { background: var(--crimson); color: #fff; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }
    .btn-cancel-row { border: 1px solid var(--s300); background: var(--white); color: var(--s500); border-radius: 9px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
    .btn-add-row { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; background: none; border: 1.5px dashed var(--s300); color: var(--s600); border-radius: 9px; height: 36px; padding: 0 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .btn-add-row:hover { border-color: var(--crimson); color: var(--crimson); }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 160px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); padding: 28px; text-align: center; }
    .empty-state i { font-size: 36px; color: var(--s300); }
    .empty-state p { font-size: 13px; color: var(--s400); max-width: 280px; line-height: 1.6; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 900px) { .content { padding: 16px 16px 40px; } }

    .ppmp-section-hdr { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: var(--s500); padding: 4px 2px; margin-top: 4px; }
    .ppmp-section-hdr i { font-size: 15px; color: var(--m); }

    .card-meta { font-size: 11.5px; color: var(--s400); margin-top: 3px; font-weight: 500; }

    .items-toggle { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--s600); border: 1px solid var(--s200); border-radius: 9px; padding: 8px 14px; margin-bottom: 12px; background: var(--s50); user-select: none; }
    .items-toggle:hover { border-color: var(--m); color: var(--m); }
    .items-toggle .chev { margin-left: auto; transition: transform .18s; }
    .items-toggle.open .chev { transform: rotate(180deg); }

    .items-panel { display: none; margin: -6px 0 12px; border: 1px solid var(--s200); border-radius: 10px; overflow: hidden; }
    .items-panel.open { display: block; }

    .items-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .items-table th { padding: 9px 14px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--s400); border-bottom: 1px solid var(--s200); background: var(--s50); white-space: nowrap; }
    .items-table td { padding: 10px 14px; border-bottom: 1px solid var(--s200); color: var(--s600); vertical-align: middle; }
    .items-table tbody tr:last-child td { border-bottom: none; }
    .items-table .item-name-cell { font-weight: 600; color: var(--s900); }
    .items-table .num-cell   { font-weight: 700; color: var(--s700); text-align: right; }
    .items-table .total-cell { font-weight: 700; color: var(--m); text-align: right; }

    /* Review Quotation modal */
    .quote-modal-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(28,16,16,.45); display: none; align-items: center; justify-content: center; padding: 20px; }
    .quote-modal-overlay.open { display: flex; }
    .quote-modal { position: relative; background: #fff; border-radius: 18px; box-shadow: 0 6px 24px rgba(0,0,0,.18); width: 100%; max-width: 520px; max-height: 86vh; overflow-y: auto; padding: 26px 28px; font-family: 'Poppins', sans-serif; }
    .quote-modal-close { position: absolute; top: 18px; right: 20px; background: none; border: none; font-size: 22px; line-height: 1; color: var(--s400); cursor: pointer; }
    .quote-modal-close:hover { color: var(--s700); }
    .quote-modal-title { font-size: 16px; font-weight: 800; color: var(--s900); padding-right: 30px; }
    .quote-modal-sub { font-size: 12px; color: var(--s500); margin-top: 4px; line-height: 1.6; }

    .quote-review-field { margin-top: 16px; }
    .quote-review-field label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--s500); margin-bottom: 6px; }
    .quote-review-field input { width: 100%; height: 38px; border: 1px solid var(--s300); border-radius: 9px; padding: 0 12px; font-size: 13px; font-family: inherit; }
    .quote-review-file { font-size: 11.5px; color: var(--s400); margin-top: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .quote-validation { margin-top: 14px; border-radius: 10px; padding: 10px 14px; }
    .quote-validation.pass { background: #dcfce7; border: 1px solid #bbf7d0; }
    .quote-validation.fail { background: #fee2e2; border: 1px solid #fecaca; }
    .quote-validation-summary { font-size: 12.5px; font-weight: 700; }
    .quote-validation.pass .quote-validation-summary { color: #166534; }
    .quote-validation.fail .quote-validation-summary { color: #b91c1c; }

    .quote-review-table { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 12px; }
    .quote-review-table th { text-align: left; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s400); padding: 8px 10px; border-bottom: 1px solid var(--s200); }
    .quote-review-table td { padding: 9px 10px; border-bottom: 1px solid var(--s200); color: var(--s700); vertical-align: top; }
    .quote-review-table tbody tr:last-child td { border-bottom: none; }
    .quote-review-table tr.row-fail td { background: #fef2f2; }
    .quote-review-item-note { display: block; font-size: 10.5px; margin-top: 3px; font-weight: 600; }
    .quote-review-item-note.ok { color: #166534; }
    .quote-review-item-note.bad { color: #b91c1c; }
    .quote-review-note { font-size: 12px; color: var(--s500); margin-top: 14px; line-height: 1.6; }

    .quote-modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 22px; }
    .btn-cancel-quote { height: 36px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: 1px solid var(--s300); background: #fff; color: var(--s500); }
    .btn-cancel-quote:hover { border-color: var(--s400); color: var(--s700); }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-clipboard-list"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Canvassing</h1>
            <p class="page-hdr-sub">Attach canvass documents from different suppliers for each fully signed PR to compare quotes — choosing a file uploads it right away. Click "Mark Ready for AOC" once you're done comparing suppliers; quotations can still be added or removed until an Abstract of Canvass is actually created.</p>
        </div>
    </div>

    @if(count($prs) === 0)
        <div class="card">
            <div class="empty-state">
                <i class="ti ti-clipboard-off"></i>
                <p>No fully signed PRs are available for canvassing yet. PRs appear here once their signatory chain is complete.</p>
            </div>
        </div>
    @endif

    @foreach($sections as $section)
        @if($section['label'])
        <div class="ppmp-section-hdr">
            <i class="ti ti-folder"></i>
            <span>{{ $section['label'] }}</span>
        </div>
        @endif
        @foreach($section['prs'] as $pr)
    <div class="card" id="canvass-card-{{ $pr['id'] }}">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">{{ $pr['prNumber'] }} — {{ $pr['office'] }}</p>
                <h2 class="card-title">{{ $pr['title'] }}</h2>
                <p class="card-meta">{{ $pr['itemCount'] }} {{ Str::plural('item', $pr['itemCount']) }}</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                @php
                    $badgeCls = match($pr['canvassingStage']) {
                        'in_progress' => 'badge-in-progress',
                        'completed'   => 'badge-completed',
                        default       => 'badge-not-started',
                    };
                @endphp
                <span class="badge {{ $badgeCls }}" data-stage-badge="{{ $pr['id'] }}">{{ $pr['canvassingLabel'] }}</span>
                <a class="quote-link" href="{{ route('procurement-office.abstract-of-canvass') }}" data-aoc-link="{{ $pr['id'] }}" style="{{ $pr['readyForAoc'] ? '' : 'display:none;' }}">Create AOC →</a>
            </div>
        </div>

        <div class="items-toggle" onclick="this.classList.toggle('open'); this.nextElementSibling.classList.toggle('open')">
            <span>{{ $pr['itemCount'] }} {{ Str::plural('item', $pr['itemCount']) }} in this PR</span>
            <i class="ti ti-chevron-down chev"></i>
        </div>
        <div class="items-panel">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:40%">Item Description</th>
                        <th class="num-cell">Quantity</th>
                        <th>Unit</th>
                        <th class="num-cell">Unit Cost</th>
                        <th class="num-cell">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pr['items'] as $item)
                    <tr>
                        <td class="item-name-cell">{{ $item['name'] }}</td>
                        <td class="num-cell">{{ $item['quantity'] }}</td>
                        <td>{{ $item['unit'] }}</td>
                        <td class="num-cell">PHP {{ number_format($item['unitCost']) }}</td>
                        <td class="total-cell">PHP {{ number_format($item['totalCost']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Quotations --}}
        <div class="quote-list" data-quote-list="{{ $pr['id'] }}">
            @foreach($pr['quotations'] as $q)
            <div class="quote-row" id="quote-{{ $q['id'] }}" data-preview-url="{{ $q['url'] }}" data-preview-name="{{ $q['filename'] }}" title="Click to preview {{ $q['filename'] }}">
                <i class="ti ti-file-invoice"></i>
                <span class="quote-supplier">{{ $q['supplier'] }}</span>
                <span class="quote-file">{{ $q['filename'] }}</span>
                <span style="font-size:11px;color:var(--s400);">{{ $q['uploadedAt'] }}</span>
                <span class="quote-actions">
                    @if(!$pr['quotationsLocked'])
                    <button class="quote-del btn-delete-quote" data-url="{{ $q['deleteUrl'] }}" data-quote-id="{{ $q['id'] }}" title="Remove quotation" type="button"><i class="ti ti-trash"></i></button>
                    @endif
                </span>
            </div>
            @endforeach
            <p style="font-size:12px;color:var(--s400);{{ count($pr['quotations']) ? ' display:none;' : '' }}" data-quote-empty="{{ $pr['id'] }}">No document uploaded yet.</p>
        </div>

        @if(!$pr['quotationsLocked'])
        <div class="upload-rows" data-upload-rows="{{ $pr['id'] }}"></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <button class="btn-add-row" data-add-row="{{ $pr['id'] }}" data-upload-url="{{ $pr['uploadUrl'] }}" type="button">
                <i class="ti ti-plus"></i> Add Supplier Quotation
            </button>
            <button class="btn btn-upload btn-finalize" data-pr-id="{{ $pr['id'] }}" data-url="{{ $pr['finalizeUrl'] }}" type="button"
                style="{{ $pr['canvassingStage'] === 'completed' ? 'display:none;' : '' }}"
                {{ count($pr['quotations']) === 0 ? 'disabled' : '' }}>
                <i class="ti ti-circle-check"></i> Mark Ready for AOC
            </button>
        </div>
        @endif
    </div>
        @endforeach
    @endforeach

</div>

<div class="pr-toast" id="cvToast"></div>

{{-- Review Quotation modal — shows what was actually read off the document
     (supplier + items, each checked against this PR's own items) before the
     file is attached for real, the same way the PR upload wizard reviews an
     extraction before creating the PR. --}}
<div class="quote-modal-overlay" id="quoteReviewOverlay">
    <div class="quote-modal">
        <button type="button" class="quote-modal-close" id="quoteReviewCloseBtn" aria-label="Close">&times;</button>
        <p class="quote-modal-title">Review Quotation</p>
        <p class="quote-modal-sub">Confirm the supplier and the items read from this document before attaching it.</p>

        <div class="quote-review-field">
            <label>Supplier Name</label>
            <input type="text" id="quoteReviewSupplier">
        </div>
        <p class="quote-review-file" id="quoteReviewFileName"></p>

        <div class="quote-validation" id="quoteReviewValidation" style="display:none;">
            <p class="quote-validation-summary" id="quoteReviewValidationSummary"></p>
        </div>

        <table class="quote-review-table" id="quoteReviewTable" style="display:none;">
            <thead>
                <tr><th>Item</th><th>Qty</th><th>Unit</th><th>Unit Price</th></tr>
            </thead>
            <tbody id="quoteReviewItemsBody"></tbody>
        </table>
        <p class="quote-review-note" id="quoteReviewNote" style="display:none;"></p>

        <div class="quote-modal-actions">
            <button type="button" class="btn-cancel-quote" id="quoteReviewCancelBtn">Cancel</button>
            <button type="button" class="btn btn-upload" id="quoteReviewConfirmBtn" disabled><i class="ti ti-check"></i> Confirm &amp; Attach</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const csrfToken         = document.querySelector('meta[name="csrf-token"]').content;
    const toastEl           = document.getElementById('cvToast');
    const extractSupplierUrl = @json($extractSupplierUrl);

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 3200);
    }

    let rowSeq = 0;

    function deriveNameFromFilename(filename) {
        return filename.replace(/\.[^/.]+$/, '').replace(/[_-]+/g, ' ').trim() || 'Unnamed Supplier';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function money(n) {
        return '₱' + (Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ── Attached-quotation preview ──────────────────────────────────────
    // Clicking an already-attached quotation's row opens the actual file
    // in a modal (print button included for a PDF) instead of navigating
    // away to a new tab — same pattern as every other uploaded-document
    // preview in the system.
    function openDocPreview(url, filename) {
        const isImage = /\.(png|jpe?g)$/i.test(filename || url);
        const body = isImage
            ? `<img src="${url}" alt="${escapeHtml(filename || '')}" style="max-width:100%;border-radius:10px;display:block;margin:0 auto;">`
            : `<div style="position:relative;">
                 <button type="button" class="pdf-print-btn" title="Print" onclick="window.prismPrintFrame(this.nextElementSibling)"><i class="ti ti-printer"></i></button>
                 <iframe src="${url}#toolbar=0" style="width:100%;height:65vh;border:none;border-radius:8px;"></iframe>
               </div>`;
        window.prismInfoModal({
            title: filename || 'Quotation',
            bodyHtml: body + `<p style="margin-top:10px;font-size:11px;"><a href="${url}" target="_blank" rel="noopener">Open in new tab ↗</a></p>`,
        });
    }

    function wireQuoteRowPreview(row) {
        row.addEventListener('click', (e) => {
            if (e.target.closest('.quote-actions')) return;
            openDocPreview(row.dataset.previewUrl, row.dataset.previewName);
        });
    }

    document.querySelectorAll('.quote-row').forEach(wireQuoteRowPreview);

    // ── Review Quotation modal ──────────────────────────────────────────
    // Shows what was actually read off the document — supplier name and
    // items, each checked against this PR's own items — before the file is
    // attached for real, the same way the PR upload wizard's Step 3 reviews
    // an extraction before Step 2's file is turned into an actual PR.
    const quoteOverlay      = document.getElementById('quoteReviewOverlay');
    const quoteSupplierIn   = document.getElementById('quoteReviewSupplier');
    const quoteFileNameEl   = document.getElementById('quoteReviewFileName');
    const quoteValidationEl = document.getElementById('quoteReviewValidation');
    const quoteValidationSummaryEl = document.getElementById('quoteReviewValidationSummary');
    const quoteTableEl      = document.getElementById('quoteReviewTable');
    const quoteItemsBodyEl  = document.getElementById('quoteReviewItemsBody');
    const quoteNoteEl       = document.getElementById('quoteReviewNote');
    const quoteConfirmBtn   = document.getElementById('quoteReviewConfirmBtn');
    const quoteCancelBtn    = document.getElementById('quoteReviewCancelBtn');
    const quoteCloseBtn     = document.getElementById('quoteReviewCloseBtn');

    let quoteReviewState = null;

    function closeQuoteReview() {
        quoteOverlay.classList.remove('open');
        if (quoteReviewState && quoteReviewState.onCancel) quoteReviewState.onCancel();
        quoteReviewState = null;
    }

    quoteCancelBtn.addEventListener('click', closeQuoteReview);
    quoteCloseBtn.addEventListener('click', closeQuoteReview);
    quoteOverlay.addEventListener('click', (e) => { if (e.target === quoteOverlay) closeQuoteReview(); });

    quoteConfirmBtn.addEventListener('click', () => {
        if (quoteConfirmBtn.disabled || !quoteReviewState) return;
        const onConfirm = quoteReviewState.onConfirm;
        const supplierName = quoteSupplierIn.value.trim() || quoteReviewState.supplierName;
        quoteReviewState = null; // cleared before close so closeQuoteReview()'s onCancel doesn't also fire
        quoteOverlay.classList.remove('open');
        onConfirm(supplierName);
    });

    /**
     * Opens the review modal for one just-selected quotation file.
     * @param {object} opts
     * @param {string} opts.fileName
     * @param {string} opts.supplierName
     * @param {Array}  opts.items       Items read from the document (empty if none/unreadable).
     * @param {object|null} opts.validation  Result of validateQuotationAgainstPr(), or null if not run (image file, or the read failed before validation).
     * @param {boolean} opts.isPdf
     * @param {(supplierName: string) => void} opts.onConfirm  Called once, only when Confirm & Attach is clicked.
     * @param {() => void} opts.onCancel  Called on Cancel/×/backdrop/Escape-equivalent dismissal.
     */
    function openQuoteReviewModal(opts) {
        quoteReviewState = opts;
        quoteSupplierIn.value = opts.supplierName || '';
        quoteFileNameEl.textContent = opts.fileName;

        const items = opts.items || [];
        const validation = opts.validation;

        if (validation) {
            const passed = validation.verdict === 'passed';
            quoteValidationEl.style.display = '';
            quoteValidationEl.className = 'quote-validation ' + (passed ? 'pass' : 'fail');
            quoteValidationSummaryEl.textContent = (passed ? '✓ ' : '✕ ') + (validation.summary || '');
        } else {
            quoteValidationEl.style.display = 'none';
        }

        if (items.length) {
            quoteTableEl.style.display = '';
            quoteNoteEl.style.display = 'none';
            const verdictByIndex = (validation?.items || []);
            quoteItemsBodyEl.innerHTML = items.map((it, i) => {
                const v = verdictByIndex[i];
                const ok = !v || v.verdict === 'passed';
                const note = v ? `<span class="quote-review-item-note ${ok ? 'ok' : 'bad'}">${ok ? '✓ ' : '✕ '}${escapeHtml(v.reason || '')}</span>` : '';
                return `<tr class="${ok ? '' : 'row-fail'}">
                    <td>${escapeHtml(it.name)}${note}</td>
                    <td>${escapeHtml(String(it.quantity ?? ''))}</td>
                    <td>${escapeHtml(it.unit || '')}</td>
                    <td>${it.unitPrice != null ? money(it.unitPrice) : ''}</td>
                </tr>`;
            }).join('');
        } else {
            quoteTableEl.style.display = 'none';
            quoteNoteEl.style.display = '';
            quoteNoteEl.textContent = opts.isPdf
                ? 'Could not read item rows from this document automatically — it will be attached without an item check.'
                : "Item rows can't be read from an image file — this quotation will be attached without an item check.";
        }

        // Same gate as the PR wizard: a document that was read and found to
        // include an item not on this PR can't be confirmed. Nothing to
        // check (image file, or the read failed) is allowed through instead
        // of blocked — best-effort, not a hard requirement.
        quoteConfirmBtn.disabled = !!(validation && validation.verdict !== 'passed');

        quoteOverlay.classList.add('open');
    }

    function updateStageUi(prId, stage, label, readyForAoc, hasQuotes) {
        const badge = document.querySelector(`[data-stage-badge="${prId}"]`);
        if (badge) {
            badge.className = 'badge ' + (stage === 'in_progress' ? 'badge-in-progress' : stage === 'completed' ? 'badge-completed' : 'badge-not-started');
            badge.textContent = label;
        }
        const aocLink = document.querySelector(`[data-aoc-link="${prId}"]`);
        if (aocLink) aocLink.style.display = readyForAoc ? '' : 'none';

        const finalizeBtn = document.querySelector(`.btn-finalize[data-pr-id="${prId}"]`);
        if (finalizeBtn) {
            finalizeBtn.style.display = stage === 'completed' ? 'none' : '';
            finalizeBtn.disabled = !hasQuotes;
        }
    }

    function addQuoteRow(prId, q) {
        const list = document.querySelector(`[data-quote-list="${prId}"]`);
        if (!list) return;

        const emptyP = list.querySelector(`[data-quote-empty="${prId}"]`);
        if (emptyP) emptyP.style.display = 'none';

        const row = document.createElement('div');
        row.className = 'quote-row';
        row.id = `quote-${q.documentId}`;
        row.dataset.previewUrl = q.url;
        row.dataset.previewName = q.filename;
        row.title = `Click to preview ${q.filename}`;
        row.innerHTML = `
            <i class="ti ti-file-invoice"></i>
            <span class="quote-supplier">${escapeHtml(q.supplierName)}</span>
            <span class="quote-file">${escapeHtml(q.filename)}</span>
            <span style="font-size:11px;color:var(--s400);">${escapeHtml(q.uploadedAt)}</span>
            <span class="quote-actions">
                <button class="quote-del btn-delete-quote" data-url="${q.deleteUrl}" data-quote-id="${q.documentId}" title="Remove quotation" type="button"><i class="ti ti-trash"></i></button>
            </span>
        `;
        if (emptyP) list.insertBefore(row, emptyP); else list.appendChild(row);
        wireDeleteButton(row.querySelector('.btn-delete-quote'));
        wireQuoteRowPreview(row);
    }

    function addUploadRow(prId, uploadUrl) {
        const rowsWrap = document.querySelector(`[data-upload-rows="${prId}"]`);
        if (!rowsWrap) return;

        const rowId = `${prId}-${++rowSeq}`;
        const row = document.createElement('div');
        row.className = 'upload-row';
        row.dataset.rowId = rowId;
        row.innerHTML = `
            <input type="text" placeholder="Choose a file to auto-fill the supplier name" data-supplier-input="${rowId}" readonly>
            <label class="file-pick" data-file-pick="${rowId}">
                <input type="file" accept="application/pdf,image/jpeg,image/png" data-file-input="${rowId}" hidden>
                <i class="ti ti-paperclip"></i> <span data-file-label="${rowId}">Choose file</span>
            </label>
            <button class="btn-cancel-row" data-row-id="${rowId}" title="Remove this row" type="button"><i class="ti ti-x"></i></button>
        `;
        rowsWrap.appendChild(row);

        const fileInput     = row.querySelector(`[data-file-input="${rowId}"]`);
        const supplierInput = row.querySelector(`[data-supplier-input="${rowId}"]`);
        const label          = row.querySelector(`[data-file-label="${rowId}"]`);
        const filePick       = row.querySelector(`[data-file-pick="${rowId}"]`);
        const cancelBtn      = row.querySelector('.btn-cancel-row');

        cancelBtn.addEventListener('click', () => row.remove());

        function resetRow() {
            fileInput.disabled = false;
            cancelBtn.disabled = false;
            filePick.style.pointerEvents = '';
            filePick.style.opacity = '';
            label.textContent = 'Choose file';
            supplierInput.value = '';
            fileInput.value = '';
        }

        fileInput.addEventListener('change', async () => {
            const file = fileInput.files[0];
            if (!file) return;

            fileInput.disabled = true;
            cancelBtn.disabled = true;
            filePick.style.pointerEvents = 'none';
            filePick.style.opacity = '.6';
            supplierInput.value = deriveNameFromFilename(file.name);
            label.textContent = 'Reading document…';

            let supplierName = supplierInput.value;
            let items = [];
            let validation = null;
            const isPdf = file.type === 'application/pdf';

            if (isPdf) {
                try {
                    const fd = new FormData();
                    fd.append('document', file);
                    fd.append('purchase_request_id', prId);
                    const resp = await fetch(extractSupplierUrl, {
                        method:  'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body:    fd,
                    });
                    const json = await resp.json();
                    if (resp.ok) {
                        if (json.supplierName) { supplierName = json.supplierName; supplierInput.value = supplierName; }
                        items      = json.items || [];
                        validation = json.validation || null;
                    }
                } catch {
                    // best-effort only — the review modal still opens below,
                    // just without an item list or validation result
                }
            }

            label.textContent = 'Choose file';

            // Nothing is uploaded yet — the review modal shows exactly what
            // was read (supplier + items, each checked against this PR's own
            // items) and only the Confirm & Attach button inside it actually
            // sends the file, mirroring the PR upload wizard's Step 3.
            openQuoteReviewModal({
                fileName: file.name,
                supplierName,
                items,
                validation,
                isPdf,
                onConfirm: async (finalSupplierName) => {
                    label.textContent = 'Uploading…';

                    const fd = new FormData();
                    fd.append('document', file);
                    fd.append('supplier_name', finalSupplierName);

                    try {
                        const resp = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: fd,
                        });
                        const json = await resp.json();
                        if (resp.ok && json.success) {
                            showToast(`Quotation from ${finalSupplierName} uploaded.`);
                            addQuoteRow(prId, json);
                            updateStageUi(prId, json.canvassingStage, json.canvassingLabel, json.readyForAoc, true);
                            row.remove();
                        } else {
                            showToast(json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Upload failed.'), true);
                            resetRow();
                        }
                    } catch {
                        showToast('Network error — please try again.', true);
                        resetRow();
                    }
                },
                onCancel: () => resetRow(),
            });
        });
    }

    document.querySelectorAll('.btn-add-row').forEach(btn => {
        btn.addEventListener('click', () => addUploadRow(btn.dataset.addRow, btn.dataset.uploadUrl));
    });

    document.querySelectorAll('.btn-finalize').forEach(btn => {
        btn.addEventListener('click', async () => {
            const originalHtml = btn.innerHTML;
            const prId = btn.dataset.prId;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Finalizing…';
            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast('Canvassing finalized — ready for AOC.');
                    updateStageUi(prId, json.canvassingStage, json.canvassingLabel, json.readyForAoc, true);
                } else {
                    showToast(json.error || 'Failed to finalize.', true);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch {
                showToast('Network error.', true);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    });

    function wireDeleteButton(btn) {
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const ok = await window.prismConfirm({
                title: 'Remove quotation?',
                message: 'Remove this supplier quotation? You can attach a new file afterward.',
                confirmText: 'Remove',
            });
            if (!ok) return;
            const originalHtml = btn.innerHTML;
            const prId = btn.closest('[data-quote-list]')?.dataset.quoteList;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i>';
            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast('Document removed.');
                    document.getElementById(`quote-${btn.dataset.quoteId}`)?.remove();
                    if (prId) {
                        if (json.quotationsRemaining === 0) {
                            const emptyP = document.querySelector(`[data-quote-empty="${prId}"]`);
                            if (emptyP) emptyP.style.display = '';
                        }
                        updateStageUi(prId, json.canvassingStage, json.canvassingLabel, json.readyForAoc, json.quotationsRemaining > 0);
                    }
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    showToast(json.error || 'Failed to remove.', true);
                }
            } catch {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showToast('Network error.', true);
            }
        });
    }

    document.querySelectorAll('.btn-delete-quote').forEach(wireDeleteButton);

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
