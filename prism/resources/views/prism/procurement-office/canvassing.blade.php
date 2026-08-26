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

    .upload-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 8px; }
    .upload-row input[type="text"] { flex: 1; min-width: 160px; border: 1px solid var(--s300); border-radius: 9px; padding: 8px 12px; font-size: 12px; font-family: inherit; }
    .file-pick { display: inline-flex; align-items: center; gap: 6px; border: 1.5px dashed var(--s300); border-radius: 9px; padding: 8px 14px; font-size: 12px; color: var(--s500); cursor: pointer; background: var(--s50); }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-upload { background: var(--crimson); color: #fff; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

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
            <p class="page-hdr-sub">Upload a canvass document for each fully signed PR. Canvassing completes as soon as the document is uploaded, and the PR becomes eligible for AOC creation.</p>
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
                @if($pr['readyForAoc'])
                    <a class="quote-link" href="{{ route('procurement-office.abstract-of-canvass') }}">Create AOC →</a>
                @endif
            </div>
        </div>

        {{-- Quotations --}}
        <div class="quote-list" data-quote-list="{{ $pr['id'] }}">
            @forelse($pr['quotations'] as $q)
            <div class="quote-row" id="quote-{{ $q['id'] }}">
                <i class="ti ti-file-invoice"></i>
                <span class="quote-supplier">{{ $q['supplier'] }}</span>
                <span class="quote-file">{{ $q['filename'] }}</span>
                <span style="font-size:11px;color:var(--s400);">{{ $q['uploadedAt'] }}</span>
                <span class="quote-actions">
                    <a class="quote-link" href="{{ $q['url'] }}" target="_blank" rel="noopener">View</a>
                    @if($pr['canvassingStage'] !== 'completed')
                    <button class="quote-del btn-delete-quote" data-url="{{ $q['deleteUrl'] }}" data-quote-id="{{ $q['id'] }}" title="Remove quotation"><i class="ti ti-trash"></i></button>
                    @endif
                </span>
            </div>
            @empty
            <p style="font-size:12px;color:var(--s400);">No document uploaded yet.</p>
            @endforelse
        </div>

        @if($pr['canvassingStage'] !== 'completed')
        <div class="upload-row">
            <input type="text" placeholder="Supplier name" data-supplier-input="{{ $pr['id'] }}" maxlength="255">
            <label class="file-pick">
                <input type="file" accept="application/pdf,image/jpeg,image/png" data-file-input="{{ $pr['id'] }}" hidden>
                <i class="ti ti-paperclip"></i> <span data-file-label="{{ $pr['id'] }}">Choose file</span>
            </label>
            <button class="btn btn-upload btn-upload-quote" data-pr-id="{{ $pr['id'] }}" data-url="{{ $pr['uploadUrl'] }}">
                <i class="ti ti-upload"></i> Upload Document
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

    document.querySelectorAll('[data-file-input]').forEach(input => {
        input.addEventListener('change', async () => {
            const prId  = input.dataset.fileInput;
            const label = document.querySelector(`[data-file-label="${prId}"]`);
            const file  = input.files[0];
            if (!file) return;
            if (label) label.textContent = file.name;

            if (file.type !== 'application/pdf') return;

            const supplierInput = document.querySelector(`[data-supplier-input="${prId}"]`);
            if (!supplierInput || supplierInput.value.trim()) return; // don't clobber a manual entry

            const originalLabelText = label ? label.textContent : '';
            if (label) label.textContent = 'Reading document…';

            try {
                const fd = new FormData();
                fd.append('document', file);
                const resp = await fetch(extractSupplierUrl, {
                    method:  'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body:    fd,
                });
                const json = await resp.json();
                if (resp.ok && json.supplierName && !supplierInput.value.trim()) {
                    supplierInput.value = json.supplierName;
                }
            } catch {
                // best-effort only — leave the field for manual entry
            } finally {
                if (label) label.textContent = originalLabelText;
            }
        });
    });

    document.querySelectorAll('.btn-upload-quote').forEach(btn => {
        btn.addEventListener('click', async () => {
            const prId     = btn.dataset.prId;
            const supplier = document.querySelector(`[data-supplier-input="${prId}"]`);
            const file     = document.querySelector(`[data-file-input="${prId}"]`);

            if (!supplier.value.trim()) { showToast('Enter the supplier name first.', true); return; }
            if (!file.files[0])         { showToast('Choose a file first.', true); return; }

            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Uploading…';

            const fd = new FormData();
            fd.append('document', file.files[0]);
            fd.append('supplier_name', supplier.value.trim());

            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd,
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast('Canvassing completed — PR is ready for AOC.');
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    showToast(json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Upload failed.'), true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-upload"></i> Upload Document';
                }
            } catch {
                showToast('Network error — please try again.', true);
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-upload"></i> Upload Document';
            }
        });
    });

    document.querySelectorAll('.btn-delete-quote').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Remove this document?')) return;
            const originalHtml = btn.innerHTML;
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
                    setTimeout(() => window.location.reload(), 700);
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
