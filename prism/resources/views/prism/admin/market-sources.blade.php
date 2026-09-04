@extends('prism.layouts.app')
@section('title', 'Market Scoping Sources | PRISM')

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
    .card-sub     { font-size: 12px; color: var(--s500); margin-top: 2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .add-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
    .add-field { display: flex; flex-direction: column; gap: 4px; }
    .add-field label { font-size: 11px; font-weight: 700; color: var(--s600); }
    .add-field input, .add-field select { border: 1px solid var(--s300); border-radius: 9px; padding: 9px 12px; font-size: 12.5px; font-family: inherit; }
    .add-field-url { flex: 1; min-width: 260px; }
    .add-field-seller { min-width: 160px; }
    .add-field-dept { min-width: 150px; }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 9px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-crimson { background: var(--crimson); color: #fff; }
    .btn-crimson:hover:not(:disabled) { background: var(--crimson-dark, #5C1011); }
    .btn:disabled { opacity: .5; cursor: not-allowed; }
    .btn-sm { height: 28px; padding: 0 10px; font-size: 11px; }
    .btn-danger { background: #fee2e2; color: #b91c1c; }
    .btn-danger:hover:not(:disabled) { background: #fecaca; }
    .btn-ok { background: #dcfce7; color: #166534; }
    .btn-ok:hover:not(:disabled) { background: #bbf7d0; }

    .add-status { border-radius: 9px; padding: 10px 13px; font-size: 12px; font-weight: 600; margin-top: 12px; display: none; }
    .add-status.error   { display: block; background: #fee2e2; color: #b91c1c; }
    .add-status.success { display: block; background: #dcfce7; color: #166534; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 12px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }

    .badge { display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 18px; font-size: 10px; font-weight: 700; white-space: nowrap; }
    .badge-ok      { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-failing { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-unknown { background: var(--s100); color: var(--s500); border: 1px solid var(--s200); }
    .badge-manual  { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
    .badge-builtin { background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }
    .badge-verified   { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-unverified { background: var(--s100); color: var(--s500); border: 1px solid var(--s200); }

    .verify-cell { display: flex; flex-direction: column; gap: 5px; align-items: flex-start; }
    .verify-meta { font-size: 10px; color: var(--s400); }
    .btn-link { background: none; border: none; padding: 0; font-size: 11px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; color: var(--m); text-decoration: none; }
    .btn-link:hover { text-decoration: underline; }
    .btn-link.muted { color: var(--s500); }
    .philgeps-link { font-size: 11px; font-weight: 700; color: #185fa5; text-decoration: none; }
    .philgeps-link:hover { text-decoration: underline; }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 140px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); padding: 26px; text-align: center; }
    .empty-state i { font-size: 32px; color: var(--s300); }
    .empty-state p { font-size: 12.5px; color: var(--s400); max-width: 320px; line-height: 1.6; }

    .service-down { border-radius: 10px; padding: 12px 16px; font-size: 12.5px; font-weight: 600; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s; }
    .pr-toast.visible { opacity: 1; }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 900px) { .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-link"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">System Administrator</p>
            <h1 class="page-hdr-title">Market Scoping Sources</h1>
            <p class="page-hdr-sub">Add a shop's link here to include it in Market Scoping's price search — it goes live immediately, no separate approval step needed.</p>
        </div>
    </div>

    @if($serviceDown)
        <div class="service-down"><i class="ti ti-alert-triangle"></i> The price scoping service isn't reachable right now — try again in a moment.</div>
    @endif

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Add Source</p>
                <h2 class="card-title">Paste a Shop Link</h2>
                <p class="card-sub">Works for Shopify or WooCommerce storefronts. The platform is auto-detected — if it's neither, it can't be added.</p>
            </div>
        </div>

        <div class="add-row">
            <div class="add-field add-field-url">
                <label>Shop URL *</label>
                <input type="url" id="sourceUrl" placeholder="https://example-shop.com.ph">
            </div>
            <div class="add-field add-field-seller">
                <label>Seller Name (optional)</label>
                <input type="text" id="sourceSeller" placeholder="Auto-detected if blank" maxlength="255">
            </div>
            <div class="add-field add-field-dept">
                <label>Department (optional)</label>
                <select id="sourceDept">
                    <option value="">— Any —</option>
                    <option value="appliances">Appliances</option>
                    <option value="medical">Medical</option>
                    <option value="office">Office</option>
                    <option value="it">IT</option>
                    <option value="janitorial">Janitorial</option>
                    <option value="hardware">Hardware</option>
                    <option value="furniture">Furniture</option>
                    <option value="sports">Sports</option>
                </select>
            </div>
            <button class="btn btn-crimson" id="btnAddSource">
                <i class="ti ti-plus"></i> Add Source
            </button>
        </div>

        <div class="add-status" id="addStatus"></div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Active Sources</p>
                <h2 class="card-title">Live Price Sources ({{ count($sources) }})</h2>
            </div>
        </div>

        @if(count($sources) === 0)
            <div class="empty-state">
                <i class="ti ti-plug-off"></i>
                <p>No sources reported — the price scoping service may be unreachable.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Origin</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Success</th>
                        <th>PhilGEPS Verification</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="sourcesBody">
                    @foreach($sources as $s)
                        @php
                            $isManual = (bool) preg_match('/^DynamicVendor(\d+)$/', $s['source'] ?? '', $m);
                            $vendorId = $isManual ? $m[1] : null;
                            $statusCls = match($s['status'] ?? 'unknown') {
                                'ok' => 'badge-ok', 'failing' => 'badge-failing', 'disabled' => 'badge-unknown', default => 'badge-unknown',
                            };
                            $enabled = $s['enabled'] ?? true;
                        @endphp
                        <tr data-source-row="{{ $vendorId ?? '' }}" data-source-name-row="{{ $s['source'] ?? '' }}">
                            <td style="font-weight:700;font-size:12px;">{{ $s['source'] ?? '—' }}</td>
                            <td><span class="badge {{ $isManual ? 'badge-manual' : 'badge-builtin' }}">{{ $isManual ? 'Manually Added' : 'Built-in' }}</span></td>
                            <td style="font-size:12px;color:var(--s500);">{{ $s['department'] ?? '—' }}</td>
                            <td><span class="badge {{ $statusCls }}" data-status-badge>{{ ucfirst($s['status'] ?? 'unknown') }}</span></td>
                            <td style="font-size:12px;color:var(--s500);">{{ $s['last_success'] ?? '—' }}</td>
                            <td>
                                <div class="verify-cell" data-verify-cell="{{ $s['source'] ?? '' }}">
                                    @if($s['verified'])
                                        <span class="badge badge-verified" title="Verified by {{ $s['verifiedByName'] ?? '—' }} on {{ $s['verifiedAt'] }}"><i class="ti ti-shield-check"></i> Verified</span>
                                        <span class="verify-meta">{{ $s['verifiedAt'] }}</span>
                                        <button type="button" class="btn-link muted btn-unverify" data-source-name="{{ $s['source'] ?? '' }}">Unverify</button>
                                    @else
                                        <span class="badge badge-unverified">Not Verified</span>
                                        <button type="button" class="btn-link btn-verify" data-source-name="{{ $s['source'] ?? '' }}">Mark as Verified</button>
                                    @endif
                                    <a class="philgeps-link btn-check-philgeps" href="https://open.philgeps.gov.ph/analytics/load/merchantInfo" target="_blank" rel="noopener" data-source-name="{{ $s['source'] ?? '' }}">Check on PhilGEPS →</a>
                                </div>
                            </td>
                            <td style="display:flex;gap:6px;">
                                <button class="btn btn-sm {{ $enabled ? 'btn-danger' : 'btn-ok' }} btn-toggle-source"
                                    data-source-name="{{ $s['source'] ?? '' }}"
                                    data-enabled="{{ $enabled ? '1' : '0' }}">
                                    <i class="ti ti-{{ $enabled ? 'player-pause' : 'player-play' }}"></i> {{ $enabled ? 'Disable' : 'Enable' }}
                                </button>
                                @if($isManual)
                                <button class="btn btn-sm btn-danger btn-remove-source" data-vendor-id="{{ $vendorId }}">
                                    <i class="ti ti-x"></i> Remove
                                </button>
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

<div class="pr-toast" id="msToast"></div>

@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const toastEl    = document.getElementById('msToast');

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 3200);
    }

    const urlInput    = document.getElementById('sourceUrl');
    const sellerInput = document.getElementById('sourceSeller');
    const deptInput   = document.getElementById('sourceDept');
    const addBtn      = document.getElementById('btnAddSource');
    const statusEl    = document.getElementById('addStatus');

    function setStatus(kind, msg) {
        if (!kind) { statusEl.className = 'add-status'; return; }
        statusEl.className = 'add-status ' + kind;
        statusEl.textContent = msg;
    }

    addBtn.addEventListener('click', async () => {
        const url = urlInput.value.trim();
        if (!url) { setStatus('error', 'Enter a shop URL first.'); return; }

        addBtn.disabled = true;
        addBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Checking…';
        setStatus(null);

        try {
            const resp = await fetch(@json(route('admin.market-sources.store')), {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body:    JSON.stringify({
                    url:        url,
                    seller:     sellerInput.value.trim() || null,
                    department: deptInput.value || null,
                }),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                setStatus('success', `Added — detected as ${json.vendor.platform}. It's live now.`);
                showToast('Source added: ' + (json.vendor.seller || url));
                setTimeout(() => window.location.reload(), 1200);
            } else {
                setStatus('error', json.error || 'Could not add that source.');
            }
        } catch {
            setStatus('error', 'Network error — please try again.');
        } finally {
            addBtn.disabled = false;
            addBtn.innerHTML = '<i class="ti ti-plus"></i> Add Source';
        }
    });

    document.querySelectorAll('.btn-toggle-source').forEach(btn => {
        btn.addEventListener('click', async () => {
            const name       = btn.dataset.sourceName;
            const wasEnabled = btn.dataset.enabled === '1';
            const action     = wasEnabled ? 'disable' : 'enable';

            if (wasEnabled && !confirm(`Disable ${name}? It will stop being queried until re-enabled.`)) return;

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> ' + (action === 'disable' ? 'Disabling…' : 'Enabling…');
            try {
                const resp = await fetch(`/admin/market-sources/${name}/${action}`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body:    JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    const nowEnabled = !wasEnabled;
                    btn.dataset.enabled = nowEnabled ? '1' : '0';
                    btn.className = 'btn btn-sm ' + (nowEnabled ? 'btn-danger' : 'btn-ok') + ' btn-toggle-source';
                    btn.innerHTML = `<i class="ti ti-${nowEnabled ? 'player-pause' : 'player-play'}"></i> ${nowEnabled ? 'Disable' : 'Enable'}`;

                    const row = document.querySelector(`[data-source-name-row="${name}"]`);
                    const badge = row?.querySelector('[data-status-badge]');
                    if (badge) {
                        badge.textContent = nowEnabled ? 'Unknown' : 'Disabled';
                        badge.className = 'badge badge-unknown';
                    }

                    showToast(`${name} ${nowEnabled ? 're-enabled' : 'disabled'}.`);
                } else {
                    btn.innerHTML = originalHtml;
                    showToast(json.error || 'Failed.', true);
                }
            } catch {
                btn.innerHTML = originalHtml;
                showToast('Network error.', true);
            } finally {
                btn.disabled = false;
            }
        });
    });

    /* ── PhilGEPS verification: no public API for automated checking, so this
       is an admin's own manual confirmation, recorded in PRISM's DB. Each
       action re-renders its cell and re-wires the fresh buttons via these
       same named functions, so Verify → Unverify → Verify keeps working. ── */
    function wireVerifyBtn(btn) {
        btn.addEventListener('click', async () => {
            const name = btn.dataset.sourceName;
            const ok = await window.prismConfirm({
                title: 'Mark as verified?',
                message: `Confirm that you've personally checked "${name}" on PhilGEPS Open Data and it's a legitimate registered supplier.`,
                confirmText: 'Mark as Verified',
                danger: false,
            });
            if (!ok) return;

            btn.disabled = true;
            try {
                const resp = await fetch(`/admin/market-sources/${encodeURIComponent(name)}/verify`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body:    JSON.stringify({ seller: name }),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    const cell = document.querySelector(`[data-verify-cell="${name}"]`);
                    if (cell) {
                        cell.innerHTML = `
                            <span class="badge badge-verified" title="Verified by ${json.verifiedByName} on ${json.verifiedAt}"><i class="ti ti-shield-check"></i> Verified</span>
                            <span class="verify-meta">${json.verifiedAt}</span>
                            <button type="button" class="btn-link muted btn-unverify" data-source-name="${name}">Unverify</button>
                            <a class="philgeps-link btn-check-philgeps" href="https://open.philgeps.gov.ph/analytics/load/merchantInfo" target="_blank" rel="noopener" data-source-name="${name}">Check on PhilGEPS →</a>`;
                        wireVerifyCellButtons(cell);
                    }
                    showToast(`${name} marked as verified.`);
                } else {
                    showToast(json.error || 'Could not save verification.', true);
                    btn.disabled = false;
                }
            } catch {
                showToast('Network error.', true);
                btn.disabled = false;
            }
        });
    }

    function wireUnverifyBtn(btn) {
        btn.addEventListener('click', async () => {
            const name = btn.dataset.sourceName;
            const ok = await window.prismConfirm({
                title: 'Remove verification?',
                message: `"${name}" will show as Not Verified again until re-checked.`,
                confirmText: 'Unverify',
                danger: true,
            });
            if (!ok) return;

            btn.disabled = true;
            try {
                const resp = await fetch(`/admin/market-sources/${encodeURIComponent(name)}/unverify`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body:    JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    const cell = document.querySelector(`[data-verify-cell="${name}"]`);
                    if (cell) {
                        cell.innerHTML = `
                            <span class="badge badge-unverified">Not Verified</span>
                            <button type="button" class="btn-link btn-verify" data-source-name="${name}">Mark as Verified</button>
                            <a class="philgeps-link btn-check-philgeps" href="https://open.philgeps.gov.ph/analytics/load/merchantInfo" target="_blank" rel="noopener" data-source-name="${name}">Check on PhilGEPS →</a>`;
                        wireVerifyCellButtons(cell);
                    }
                    showToast(`${name} verification removed.`);
                } else {
                    showToast(json.error || 'Failed.', true);
                    btn.disabled = false;
                }
            } catch {
                showToast('Network error.', true);
                btn.disabled = false;
            }
        });
    }

    function wireCheckPhilgepsBtn(link) {
        link.addEventListener('click', async () => {
            const name = link.dataset.sourceName;
            try {
                await navigator.clipboard.writeText(name);
                showToast(`Copied "${name}" — paste it into PhilGEPS' merchant search.`);
            } catch {
                // Clipboard API needs a secure context; the link still opens PhilGEPS either way.
            }
        });
    }

    // Re-wires a cell's Verify/Unverify/Check-on-PhilGEPS buttons after an
    // in-place innerHTML swap (the freshly-inserted elements have no listeners yet).
    function wireVerifyCellButtons(cell) {
        cell.querySelectorAll('.btn-verify').forEach(wireVerifyBtn);
        cell.querySelectorAll('.btn-unverify').forEach(wireUnverifyBtn);
        cell.querySelectorAll('.btn-check-philgeps').forEach(wireCheckPhilgepsBtn);
    }

    document.querySelectorAll('.verify-cell').forEach(wireVerifyCellButtons);

    document.querySelectorAll('.btn-remove-source').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Remove this source from Market Scoping?')) return;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Removing…';
            const vendorId = btn.dataset.vendorId;

            try {
                const resp = await fetch(`/admin/market-sources/${vendorId}/remove`, {
                    method:  'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast('Source removed.');
                    document.querySelector(`[data-source-row="${vendorId}"]`)?.remove();
                } else {
                    btn.innerHTML = originalHtml;
                    showToast(json.error || 'Failed to remove.', true);
                    btn.disabled = false;
                }
            } catch {
                btn.innerHTML = originalHtml;
                showToast('Network error.', true);
                btn.disabled = false;
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
