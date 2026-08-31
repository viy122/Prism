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
    .quote-row { display: flex; align-items: center; gap: 10px; border: 1px solid var(--s200); border-radius: 10px; padding: 8px 14px; font-size: 12px; background: var(--s50); }
    .quote-row i { color: var(--s400); }
    .quote-supplier { font-weight: 700; color: var(--s700); }
    .quote-file { color: var(--s500); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px; }
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

    @foreach($prs as $pr)
    <div class="card" id="canvass-card-{{ $pr['id'] }}">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">{{ $pr['prNumber'] }} — {{ $pr['office'] }}</p>
                <h2 class="card-title">{{ $pr['title'] }}</h2>
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

        {{-- Quotations --}}
        <div class="quote-list" data-quote-list="{{ $pr['id'] }}">
            @foreach($pr['quotations'] as $q)
            <div class="quote-row" id="quote-{{ $q['id'] }}">
                <i class="ti ti-file-invoice"></i>
                <span class="quote-supplier">{{ $q['supplier'] }}</span>
                <span class="quote-file">{{ $q['filename'] }}</span>
                <span style="font-size:11px;color:var(--s400);">{{ $q['uploadedAt'] }}</span>
                <span class="quote-actions">
                    <a class="quote-link" href="{{ $q['url'] }}" target="_blank" rel="noopener">View</a>
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

</div>

<div class="pr-toast" id="cvToast"></div>

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
        row.innerHTML = `
            <i class="ti ti-file-invoice"></i>
            <span class="quote-supplier">${escapeHtml(q.supplierName)}</span>
            <span class="quote-file">${escapeHtml(q.filename)}</span>
            <span style="font-size:11px;color:var(--s400);">${escapeHtml(q.uploadedAt)}</span>
            <span class="quote-actions">
                <a class="quote-link" href="${q.url}" target="_blank" rel="noopener">View</a>
                <button class="quote-del btn-delete-quote" data-url="${q.deleteUrl}" data-quote-id="${q.documentId}" title="Remove quotation" type="button"><i class="ti ti-trash"></i></button>
            </span>
        `;
        if (emptyP) list.insertBefore(row, emptyP); else list.appendChild(row);
        wireDeleteButton(row.querySelector('.btn-delete-quote'));
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
            if (file.type === 'application/pdf') {
                try {
                    const fd = new FormData();
                    fd.append('document', file);
                    const resp = await fetch(extractSupplierUrl, {
                        method:  'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body:    fd,
                    });
                    const json = await resp.json();
                    if (resp.ok && json.supplierName) {
                        supplierName = json.supplierName;
                        supplierInput.value = supplierName;
                    }
                } catch {
                    // best-effort only — filename-derived fallback stays in place
                }
            }

            label.textContent = 'Uploading…';

            const fd = new FormData();
            fd.append('document', file);
            fd.append('supplier_name', supplierName);

            try {
                const resp = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd,
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast(`Quotation from ${supplierName} uploaded.`);
                    addQuoteRow(prId, json);
                    updateStageUi(prId, json.canvassingStage, json.canvassingLabel, json.readyForAoc, true);
                    row.remove();
                } else {
                    showToast(json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Upload failed.'), true);
                    fileInput.disabled = false;
                    cancelBtn.disabled = false;
                    filePick.style.pointerEvents = '';
                    filePick.style.opacity = '';
                    label.textContent = 'Choose file';
                    supplierInput.value = '';
                    fileInput.value = '';
                }
            } catch {
                showToast('Network error — please try again.', true);
                fileInput.disabled = false;
                cancelBtn.disabled = false;
                filePick.style.pointerEvents = '';
                filePick.style.opacity = '';
                label.textContent = 'Choose file';
            }
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
