@extends('prism.layouts.app')
@section('title', 'Abstract of Canvass | Procurement Office')

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
    .badge-signed  { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-routing { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-draft   { background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }

    /* Signatory timeline */
    .sig-timeline { display: flex; align-items: center; gap: 0; }
    .sig-step { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
    .sig-step:not(:last-child)::after { content: ''; position: absolute; top: 10px; left: 50%; width: 100%; height: 2px; background: var(--s200); z-index: 0; }
    .sig-step.done::after { background: #3b6d11; }
    .sig-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--s300); background: var(--white); z-index: 1; position: relative; }
    .sig-step.done .sig-dot { background: #3b6d11; border-color: #3b6d11; }
    .sig-step.active .sig-dot { background: var(--m); border-color: var(--m); box-shadow: 0 0 0 3px rgba(139,26,28,.2); }
    .sig-label { font-size: 9px; font-weight: 700; text-align: center; color: var(--s400); margin-top: 5px; white-space: nowrap; }
    .sig-step.done .sig-label, .sig-step.active .sig-label { color: var(--s700); }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-primary { background: var(--m); color: #fff; }
    .btn-primary:hover:not(:disabled) { background: var(--m-dk); }
    .btn-green { background: #3b6d11; color: #fff; }
    .btn-green:hover:not(:disabled) { background: #2e560d; }
    .btn-ghost { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
    .btn-ghost:hover:not(:disabled) { background: var(--s200); }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .eligible-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; }
    .eligible-card { border: 1.5px solid var(--s200); border-radius: 12px; padding: 14px 18px; display: flex; flex-direction: column; gap: 8px; }
    .eligible-card h4 { font-size: 13px; font-weight: 700; color: var(--s900); }
    .eligible-card p { font-size: 12px; color: var(--s500); }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-file-text"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Abstract of Canvass</h1>
            <p class="page-hdr-sub">Create AOCs from fully-signed PRs and route them through the signature chain.</p>
        </div>
    </div>

    {{-- ── Eligible PRs (ready for AOC creation) ── --}}
    @if(count($eligiblePrs) > 0)
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Ready for AOC</p>
                <h2 class="card-title">Fully-Signed PRs — Create AOC</h2>
            </div>
            <span style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:var(--s100);color:var(--s700);border:1px solid var(--s200);">{{ count($eligiblePrs) }} PR{{ count($eligiblePrs) !== 1 ? 's' : '' }} waiting</span>
        </div>
        <div class="eligible-grid">
            @foreach($eligiblePrs as $pr)
            <div class="eligible-card">
                <div>
                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--s400);">{{ $pr['office'] }}</span>
                    <h4>{{ $pr['prNumber'] }}</h4>
                    <p>{{ Str::limit($pr['title'], 60) }}</p>
                </div>
                <button class="btn btn-primary btn-create-aoc" data-url="{{ $pr['createUrl'] }}" data-pr-id="{{ $pr['id'] }}" data-pr-number="{{ $pr['prNumber'] }}">
                    <i class="ti ti-file-plus"></i> Create AOC
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── AOC List ── --}}
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">AOC Tracker</p>
                <h2 class="card-title">Abstract of Canvass List</h2>
            </div>
        </div>

        @if(count($aocs) === 0)
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:180px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:32px;text-align:center;">
                <i class="ti ti-file-off" style="font-size:38px;color:var(--s300);"></i>
                <p style="font-size:13px;color:var(--s400);max-width:240px;line-height:1.6;">No AOCs created yet. Fully-signed PRs will appear above.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>AOC Code</th>
                        <th>PR No.</th>
                        <th>Office</th>
                        <th>Description</th>
                        <th>Signatory Stage</th>
                        <th>PO</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aocs as $aoc)
                    @php
                        $stageBadge = match($aoc['signatoryStage']) {
                            'fully_signed' => 'badge-signed',
                            'draft'        => 'badge-draft',
                            default        => 'badge-routing',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $aoc['code'] }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $aoc['prNumber'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $aoc['office'] }}</td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $aoc['title'] }}</td>
                        <td>
                            <span class="badge {{ $stageBadge }}" data-aoc-stage-badge="{{ $aoc['id'] }}">{{ $aoc['signatoryLabel'] }}</span>
                        </td>
                        <td>
                            @if($aoc['hasPo'])
                                <span class="badge badge-signed">PO Issued</span>
                            @else
                                <span style="font-size:11px;color:var(--s400);">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @if($aoc['signatoryStage'] !== 'fully_signed')
                                    <button class="btn btn-green btn-advance-aoc" data-url="{{ $aoc['advanceUrl'] }}" data-aoc-id="{{ $aoc['id'] }}" {{ $aoc['signatoryStage'] === 'fully_signed' ? 'disabled' : '' }}>
                                        <i class="ti ti-circle-arrow-right"></i> Route
                                    </button>
                                    <button class="btn btn-ghost btn-return-aoc" data-url="{{ $aoc['returnUrl'] }}" data-aoc-id="{{ $aoc['id'] }}" {{ $aoc['signatoryStage'] === 'draft' ? 'disabled' : '' }}>
                                        <i class="ti ti-circle-arrow-left"></i> Return
                                    </button>
                                @elseif(!$aoc['hasPo'])
                                    <a href="{{ route('procurement-office.purchase-orders') }}" class="btn btn-primary">
                                        <i class="ti ti-shopping-cart"></i> Issue PO
                                    </a>
                                @else
                                    <span style="font-size:11px;color:var(--s400);">Complete</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

<div class="pr-toast" id="aocToast"></div>

{{-- Return remarks modal-like inline --}}
<div id="returnModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.4);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:28px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:4px;">Return AOC</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:16px;">Please provide a reason for returning this AOC to draft.</p>
        <textarea id="returnAocRemarks" rows="3" style="width:100%;padding:10px 14px;border-radius:10px;border:1px solid #e2e8f0;font-size:13px;font-family:inherit;resize:vertical;box-sizing:border-box;" placeholder="Reason for return…"></textarea>
        <div style="display:flex;gap:8px;margin-top:14px;">
            <button class="btn btn-ghost" id="btnCancelReturn" style="flex:1;">Cancel</button>
            <button class="btn" id="btnConfirmReturn" style="flex:1;background:#a32d2d;color:#fff;"><i class="ti ti-send"></i> Confirm Return</button>
        </div>
    </div>
</div>

@endsection

<script type="application/json" id="aocData">@json($aocs)</script>
<script type="application/json" id="stagesData">@json($stages)</script>

@push('scripts')
<script>
(function () {
    const toastEl    = document.getElementById('aocToast');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    const modal      = document.getElementById('returnModal');
    const returnIn   = document.getElementById('returnAocRemarks');
    const btnConfirm = document.getElementById('btnConfirmReturn');
    const btnCancel  = document.getElementById('btnCancelReturn');

    let pendingReturnUrl = null;
    let pendingReturnId  = null;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 2800);
    }

    function updateBadge(aocId, label, stage) {
        const badge = document.querySelector(`[data-aoc-stage-badge="${aocId}"]`);
        if (!badge) return;
        badge.textContent = label;
        badge.className = 'badge ' + (stage === 'fully_signed' ? 'badge-signed' : stage === 'draft' ? 'badge-draft' : 'badge-routing');
    }

    /* ── Create AOC ── */
    document.querySelectorAll('.btn-create-aoc').forEach(btn => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Creating…';
            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast('AOC created. Reloading…');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(json.error || 'Failed to create AOC.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-file-plus"></i> Create AOC';
                }
            } catch { showToast('Network error.', true); btn.disabled = false; btn.innerHTML = '<i class="ti ti-file-plus"></i> Create AOC'; }
        });
    });

    /* ── Advance AOC ── */
    document.querySelectorAll('.btn-advance-aoc').forEach(btn => {
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
                    updateBadge(btn.dataset.aocId, json.signatoryLabel, json.signatoryStage);
                    showToast('AOC routed forward.');
                    if (json.signatoryStage === 'fully_signed') {
                        btn.closest('td').innerHTML = '<a href="{{ route('procurement-office.purchase-orders') }}" class="btn btn-primary"><i class="ti ti-shopping-cart"></i> Issue PO</a>';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Route';
                    }
                } else {
                    showToast(json.error || 'Failed.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Route';
                }
            } catch { showToast('Network error.', true); btn.disabled = false; btn.innerHTML = '<i class="ti ti-circle-arrow-right"></i> Route'; }
        });
    });

    /* ── Return AOC ── */
    document.querySelectorAll('.btn-return-aoc').forEach(btn => {
        btn.addEventListener('click', () => {
            pendingReturnUrl = btn.dataset.url;
            pendingReturnId  = btn.dataset.aocId;
            returnIn.value   = '';
            modal.style.display = 'flex';
        });
    });

    btnCancel.addEventListener('click', () => { modal.style.display = 'none'; });
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

    btnConfirm.addEventListener('click', async () => {
        const reason = returnIn.value.trim();
        if (!reason) { showToast('Reason is required.', true); return; }
        btnConfirm.disabled = true;
        try {
            const resp = await fetch(pendingReturnUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ remarks: reason }),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                updateBadge(pendingReturnId, 'AOC Created', 'draft');
                showToast('AOC returned to draft.');
                modal.style.display = 'none';
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(json.error || 'Failed to return AOC.', true);
            }
        } catch { showToast('Network error.', true); }
        finally { btnConfirm.disabled = false; }
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
