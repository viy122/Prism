@extends('prism.layouts.app')
@section('title', 'User Management | PRISM')

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

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 12px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }

    .badge { display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 18px; font-size: 10px; font-weight: 700; white-space: nowrap; }
    .badge-active   { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-inactive { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-crimson { background: var(--crimson); color: #fff; }
    .btn-neutral { background: #e2e8f0; color: #334155; }
    .btn-sm { height: 28px; padding: 0 10px; font-size: 11px; }
    .btn-warn { background: #fdf7ec; color: #854f0b; border: 1px solid #fac775; }
    .btn-ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .search-input { border: 1px solid var(--s300); border-radius: 9px; padding: 8px 12px; font-size: 12px; font-family: inherit; min-width: 220px; }

    /* This modal sits outside .content as a sibling, so the --s and --m
       aliases .content defines for the page don't reach it — every color
       below is either a genuine :root global (--crimson, --white, --s200)
       or a hardcoded fallback, never a bare .content-scoped var(). */
    .adm-modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; }
    .adm-modal-backdrop.open { display: flex; }
    .adm-modal { background: #fff; border-radius: 16px; width: 100%; max-width: 520px; max-height: 92vh; overflow-y: auto; padding: 22px 24px; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 24px 60px rgba(0,0,0,.25); font-family: 'Poppins', sans-serif; }
    .adm-modal h3 { font-size: 16px; font-weight: 800; color: #0f172a; }
    .adm-modal-sub { font-size: 12px; color: #64748b; margin-top: -8px; }
    .adm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .adm-field { margin-bottom: 0; }
    .adm-field label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 6px; }
    .adm-field input, .adm-field select {
        width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc;
        padding: 9px 11px; font-size: 12.5px; color: #334155; font-family: 'Poppins', sans-serif;
        outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .adm-field input:focus, .adm-field select:focus { border-color: var(--crimson); background: #fff; box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .adm-field-hint { font-size: 10.5px; color: #94a3b8; margin-top: 4px; }
    .adm-status { border-radius: 9px; padding: 9px 13px; font-size: 12px; font-weight: 600; display: none; }
    .adm-status.error { display: block; background: #fee2e2; color: #b91c1c; }

    .adm-pw-generated { display: flex; align-items: center; gap: 8px; }
    .adm-pw-generated input { font-family: 'SFMono-Regular', Consolas, monospace; letter-spacing: .03em; font-weight: 700; color: #0f172a !important; background: #fff !important; }
    .adm-pw-icon-btn { flex-shrink: 0; width: 36px; height: 36px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all .15s; }
    .adm-pw-icon-btn:hover { border-color: var(--crimson); color: var(--crimson); }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s; }
    .pr-toast.visible { opacity: 1; }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 900px) { .content { padding: 16px 16px 40px; } .adm-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-users"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">System Administrator</p>
            <h1 class="page-hdr-title">User Management</h1>
            <p class="page-hdr-sub">Create accounts, assign roles and offices, and deactivate or reactivate users.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Accounts</p>
                <h2 class="card-title">All Users ({{ count($users) }})</h2>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <input type="text" id="userSearch" class="search-input" placeholder="Search name, username, office…">
                <select id="roleFilter" class="search-input" style="min-width:auto;width:auto;">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)<option value="{{ $r['name'] }}">{{ $r['name'] }}</option>@endforeach
                </select>
                <button class="btn btn-crimson" id="newUserBtn"><i class="ti ti-user-plus"></i> New User</button>
            </div>
        </div>

        <div class="table-wrap">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr data-search="{{ strtolower($u['name'] . ' ' . $u['username'] . ' ' . $u['email'] . ' ' . $u['office'] . ' ' . $u['role']) }}" data-role="{{ $u['role'] }}">
                        <td>
                            <span style="font-weight:700;">{{ $u['name'] }}</span><br>
                            <span style="font-size:11px;color:var(--s400);">{{ $u['email'] }}</span>
                        </td>
                        <td style="color:var(--s500);">{{ $u['username'] }}</td>
                        <td style="font-size:12px;font-weight:600;">{{ $u['role'] }}</td>
                        <td style="font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u['office'] }}</td>
                        <td><span class="badge {{ $u['status'] === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($u['status']) }}</span></td>
                        <td style="font-size:11px;color:var(--s500);white-space:nowrap;">{{ $u['lastLogin'] }}</td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button class="btn btn-sm btn-neutral btn-edit-user" data-user='@json($u)'><i class="ti ti-pencil"></i> Edit</button>
                                @if(!$u['isSelf'] && $u['status'] === 'active')
                                <button class="btn btn-sm btn-warn btn-toggle-user" data-url="{{ route('admin.users.deactivate', $u['id']) }}" data-action="deactivate"><i class="ti ti-user-off"></i> Deactivate</button>
                                @elseif($u['status'] !== 'active' && $u['status'] !== 'deleted')
                                <button class="btn btn-sm btn-ok btn-toggle-user" data-url="{{ route('admin.users.reactivate', $u['id']) }}" data-action="reactivate"><i class="ti ti-user-check"></i> Reactivate</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Create / edit user modal ── --}}
<div class="adm-modal-backdrop" id="userModalBackdrop">
    <div class="adm-modal">
        <h3 id="userModalTitle">New User</h3>
        <p class="adm-modal-sub" id="userModalSub">The account's email must match the address they'll sign in with.</p>
        <div class="adm-grid">
            <div class="adm-field"><label>Full name</label><input id="umName" maxlength="255"></div>
            <div class="adm-field"><label>Username</label><input id="umUsername" maxlength="100"></div>
            <div class="adm-field"><label>Email</label><input id="umEmail" type="email" maxlength="255"></div>
            <div class="adm-field"><label>Position title</label><input id="umPosition" maxlength="255"></div>
            <div class="adm-field">
                <label>Role</label>
                <select id="umRole">
                    @foreach($roles as $r)<option value="{{ $r['id'] }}">{{ $r['name'] }}</option>@endforeach
                </select>
            </div>
            <div class="adm-field">
                <label>Office</label>
                <select id="umOffice">
                    @foreach($offices as $o)<option value="{{ $o['id'] }}">{{ $o['name'] }}</option>@endforeach
                </select>
            </div>
            <div class="adm-field" id="umEmployeeField"><label>Employee number (optional)</label><input id="umEmployee" maxlength="100"></div>
            <div class="adm-field" id="umPasswordField" style="grid-column:1/-1;">
                <label>Auto-Generated Password</label>
                <div class="adm-pw-generated">
                    <input id="umPassword" type="text" readonly>
                    <button type="button" class="adm-pw-icon-btn" id="umPwRegenBtn" title="Generate a new password"><i class="ti ti-refresh"></i></button>
                    <button type="button" class="adm-pw-icon-btn" id="umPwCopyBtn" title="Copy password"><i class="ti ti-copy"></i></button>
                </div>
                <p class="adm-field-hint">Share this with the user — it won't be shown again after you save. They can change it once they sign in.</p>
            </div>
        </div>
        <div class="adm-status" id="umStatus"></div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button class="btn btn-neutral" id="umCancelBtn">Cancel</button>
            <button class="btn btn-crimson" id="umSubmitBtn"><i class="ti ti-check"></i> Save User</button>
        </div>
    </div>
</div>

<div class="pr-toast" id="admToast"></div>

@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const storeUrl  = '{{ route('admin.users.store') }}';
    const updateBase = '{{ url('admin/users') }}/';

    const backdrop = document.getElementById('userModalBackdrop');
    const toastEl  = document.getElementById('admToast');
    const statusEl = document.getElementById('umStatus');
    const pwInput  = document.getElementById('umPassword');
    let editingId  = null;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 3000);
    }

    // Admins never type a password by hand — one is generated here and can
    // only be regenerated, never edited, matching the "admin never sees/sets
    // an existing user's password" rule (this one's only visible once, at
    // creation, so it can be handed to the new user).
    function generatePassword(length = 12) {
        const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const lower = 'abcdefghijkmnpqrstuvwxyz';
        const digits = '23456789';
        const symbols = '!@#$%&*?';
        const all = upper + lower + digits + symbols;
        // Falls back to Math.random if the Web Crypto API isn't reachable
        // (e.g. blocked by an extension/policy) — a weaker random source is
        // still far better than the "New User" modal silently failing to open.
        const randomInt = (max) => {
            try { return crypto.getRandomValues(new Uint32Array(1))[0] % max; }
            catch { return Math.floor(Math.random() * max); }
        };
        const pick = (chars) => chars[randomInt(chars.length)];

        let chars = [pick(upper), pick(lower), pick(digits), pick(symbols)];
        while (chars.length < length) chars.push(pick(all));

        // Fisher-Yates shuffle so the guaranteed classes aren't always up front.
        for (let i = chars.length - 1; i > 0; i--) {
            const j = randomInt(i + 1);
            [chars[i], chars[j]] = [chars[j], chars[i]];
        }
        return chars.join('');
    }

    function openModal(user) {
        editingId = user ? user.id : null;
        document.getElementById('userModalTitle').textContent = user ? 'Edit User — ' + user.name : 'New User';
        document.getElementById('userModalSub').textContent = user
            ? 'Role and office assignment can only be changed by an administrator.'
            : "The account's email must match the address they'll sign in with.";
        document.getElementById('umName').value     = user?.name ?? '';
        document.getElementById('umUsername').value = user?.username ?? '';
        document.getElementById('umEmail').value    = user?.email ?? '';
        document.getElementById('umPosition').value = user?.positionTitle === '—' ? '' : (user?.positionTitle ?? '');
        if (user?.roleId)   document.getElementById('umRole').value   = user.roleId;
        if (user?.officeId) document.getElementById('umOffice').value = user.officeId;
        document.getElementById('umEmployee').value = '';
        document.getElementById('umPasswordField').style.display = user ? 'none' : '';
        document.getElementById('umEmployeeField').style.display = user ? 'none' : '';
        statusEl.className = 'adm-status';
        backdrop.classList.add('open');
        if (!user) pwInput.value = generatePassword();
    }
    function closeModal() { backdrop.classList.remove('open'); editingId = null; }

    document.getElementById('umPwRegenBtn').addEventListener('click', () => { pwInput.value = generatePassword(); });
    document.getElementById('umPwCopyBtn').addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(pwInput.value);
            showToast('Password copied.');
        } catch { showToast('Could not copy — select and copy manually.', true); }
    });

    document.getElementById('newUserBtn').addEventListener('click', () => openModal(null));
    document.querySelectorAll('.btn-edit-user').forEach(btn => {
        btn.addEventListener('click', () => openModal(JSON.parse(btn.dataset.user)));
    });
    document.getElementById('umCancelBtn').addEventListener('click', closeModal);
    backdrop.addEventListener('click', e => { if (e.target === backdrop) closeModal(); });

    document.getElementById('umSubmitBtn').addEventListener('click', async function () {
        const payload = {
            name:           document.getElementById('umName').value.trim(),
            username:       document.getElementById('umUsername').value.trim(),
            email:          document.getElementById('umEmail').value.trim(),
            position_title: document.getElementById('umPosition').value.trim() || null,
            role_id:        document.getElementById('umRole').value,
            office_id:      document.getElementById('umOffice').value,
        };
        if (!editingId) {
            payload.password        = document.getElementById('umPassword').value;
            payload.employee_number = document.getElementById('umEmployee').value.trim() || null;
        }

        this.disabled = true;
        this.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Saving…';

        try {
            const resp = await fetch(editingId ? updateBase + editingId : storeUrl, {
                method: editingId ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                showToast(editingId ? 'User updated.' : 'User created.');
                setTimeout(() => window.location.reload(), 900);
            } else {
                statusEl.className = 'adm-status error';
                statusEl.textContent = json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Could not save user.');
                this.disabled = false;
                this.innerHTML = '<i class="ti ti-check"></i> Save User';
            }
        } catch {
            statusEl.className = 'adm-status error';
            statusEl.textContent = 'Network error — please try again.';
            this.disabled = false;
            this.innerHTML = '<i class="ti ti-check"></i> Save User';
        }
    });

    document.querySelectorAll('.btn-toggle-user').forEach(btn => {
        btn.addEventListener('click', async () => {
            const verb = btn.dataset.action;
            const ok = await window.prismConfirm({
                title: verb === 'deactivate' ? 'Deactivate this user?' : 'Reactivate this user?',
                message: verb === 'deactivate'
                    ? 'They will immediately lose access to PRISM until reactivated.'
                    : 'They will regain access to PRISM with their existing credentials.',
                confirmText: verb === 'deactivate' ? 'Deactivate' : 'Reactivate',
                danger: verb === 'deactivate',
            });
            if (!ok) return;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> ' + (verb === 'deactivate' ? 'Deactivating…' : 'Reactivating…');
            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast('User ' + verb + 'd.');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    btn.innerHTML = originalHtml;
                    showToast(json.error || 'Action failed.', true);
                    btn.disabled = false;
                }
            } catch { btn.innerHTML = originalHtml; showToast('Network error.', true); btn.disabled = false; }
        });
    });

    const roleFilterEl = document.getElementById('roleFilter');

    function applyUserFilters() {
        const q    = document.getElementById('userSearch').value.trim().toLowerCase();
        const role = roleFilterEl.value;
        document.querySelectorAll('#usersTable tbody tr').forEach(tr => {
            const matchesSearch = !q || tr.dataset.search.includes(q);
            const matchesRole   = !role || tr.dataset.role === role;
            tr.style.display = matchesSearch && matchesRole ? '' : 'none';
        });
    }

    document.getElementById('userSearch').addEventListener('input', applyUserFilters);
    roleFilterEl.addEventListener('change', applyUserFilters);

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
