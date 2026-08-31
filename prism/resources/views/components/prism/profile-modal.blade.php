@php
    $me = auth()->user();
    $meInitials = $me
        ? collect(preg_split('/\s+/', trim($me->name)))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('')
        : '?';
    $meAvatarUrl = $me?->avatar_path ? \Illuminate\Support\Facades\Storage::url($me->avatar_path) : null;
    $meRole = $me?->roles->first()?->name ?? '—';
    $meOffice = $me?->office?->name ?? '—';
@endphp
{{--
    Self-service "edit your own info" — opened by clicking your name/avatar in
    the sidebar (see .sb-user in the layout). Never touches role or office;
    only an administrator can change those (see admin/user-management.blade.php).

        window.prismOpenProfile();
--}}
<div class="pm-overlay" id="pmOverlay" aria-hidden="true">
    <div class="pm-card" role="dialog" aria-modal="true" aria-labelledby="pmTitle">
        <div class="pm-head">
            <p class="pm-title" id="pmTitle">Edit Your Info</p>
            <button type="button" class="pm-close" id="pmCloseBtn" aria-label="Close">&times;</button>
        </div>

        <form id="pmForm" autocomplete="off">
            <div class="pm-avatar-row">
                <button type="button" class="pm-avatar-btn" id="pmAvatarBtn" title="Change photo">
                    @if($meAvatarUrl)
                        <img src="{{ $meAvatarUrl }}" alt="" id="pmAvatarImg" class="pm-avatar-img">
                        <span id="pmAvatarInitials" class="pm-avatar-initials" style="display:none;">{{ $meInitials }}</span>
                    @else
                        <img src="" alt="" id="pmAvatarImg" class="pm-avatar-img" style="display:none;">
                        <span id="pmAvatarInitials" class="pm-avatar-initials">{{ $meInitials }}</span>
                    @endif
                    <span class="pm-avatar-edit"><i class="ti ti-camera"></i></span>
                </button>
                <input type="file" id="pmAvatarInput" accept="image/*" style="display:none;">
                <div>
                    <p class="pm-avatar-hint">Click the photo to change it.</p>
                    <p class="pm-avatar-hint">Defaults to your initials if none is set.</p>
                </div>
            </div>

            <div class="pm-grid">
                <div class="pm-field"><label>Full Name</label><input id="pmName" maxlength="255"></div>
                <div class="pm-field"><label>Username</label><input id="pmUsername" maxlength="100"></div>
                <div class="pm-field"><label>Email</label><input id="pmEmail" type="email" maxlength="255"></div>
                <div class="pm-field"><label>Employee Number</label><input id="pmEmployee" maxlength="100"></div>
                <div class="pm-field" style="grid-column:1/-1;"><label>Position Title</label><input id="pmPosition" maxlength="255"></div>
            </div>

            <div class="pm-readonly-grid">
                <div>
                    <p class="pm-readonly-label">Role</p>
                    <span class="pm-readonly-badge">{{ $meRole }}</span>
                </div>
                <div>
                    <p class="pm-readonly-label">Office</p>
                    <span class="pm-readonly-badge">{{ $meOffice }}</span>
                </div>
            </div>
            <p class="pm-readonly-note">Role and office can only be changed by an administrator.</p>

            <div class="pm-pw-toggle" id="pmPwToggle">
                <span><i class="ti ti-lock"></i> Change Password</span>
                <i class="ti ti-chevron-down pm-chev"></i>
            </div>
            <div class="pm-pw-section" id="pmPwSection">
                <div class="pm-field">
                    <label>Current Password</label>
                    <div class="pm-pw-wrap">
                        <input id="pmCurrentPassword" type="password" autocomplete="current-password">
                        <button type="button" class="pm-eye-btn" data-target="pmCurrentPassword"><i class="ti ti-eye"></i></button>
                    </div>
                </div>
                <div class="pm-field">
                    <label>New Password</label>
                    <div class="pm-pw-wrap">
                        <input id="pmNewPassword" type="password" autocomplete="new-password">
                        <button type="button" class="pm-eye-btn" data-target="pmNewPassword"><i class="ti ti-eye"></i></button>
                    </div>
                    <div class="pm-strength-bar"><span id="pmStrengthFill"></span></div>
                    <p class="pm-strength-label" id="pmStrengthLabel">&nbsp;</p>
                    <ul class="pm-req-list" id="pmReqList">
                        <li data-req="length"><i class="ti ti-circle"></i> At least 8 characters</li>
                        <li data-req="lower"><i class="ti ti-circle"></i> A lowercase letter</li>
                        <li data-req="upper"><i class="ti ti-circle"></i> An uppercase letter</li>
                        <li data-req="digit"><i class="ti ti-circle"></i> A number</li>
                        <li data-req="symbol"><i class="ti ti-circle"></i> A special character</li>
                    </ul>
                </div>
                <div class="pm-field">
                    <label>Confirm New Password</label>
                    <div class="pm-pw-wrap">
                        <input id="pmConfirmPassword" type="password" autocomplete="new-password">
                        <button type="button" class="pm-eye-btn" data-target="pmConfirmPassword"><i class="ti ti-eye"></i></button>
                    </div>
                    <p class="pm-match-label" id="pmMatchLabel">&nbsp;</p>
                </div>
            </div>
        </form>

        <div class="pm-status" id="pmStatus"></div>
        <div class="pm-actions">
            <button type="button" class="pm-btn pm-btn-cancel" id="pmCancelBtn">Cancel</button>
            <button type="button" class="pm-btn pm-btn-save" id="pmSaveBtn"><i class="ti ti-check"></i> Save Changes</button>
        </div>
    </div>
</div>

<style>
    .pm-overlay { position: fixed; inset: 0; z-index: 2100; background: rgba(28,16,16,.45); display: none; align-items: center; justify-content: center; padding: 20px; }
    .pm-overlay.open { display: flex; }
    .pm-card { background: #fff; border-radius: 18px; box-shadow: 0 24px 60px rgba(0,0,0,.25); width: 100%; max-width: 460px; max-height: 90vh; overflow-y: auto; padding: 22px 24px; font-family: 'Poppins', sans-serif; }
    .pm-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px; }
    .pm-title { font-size: 16px; font-weight: 800; color: #0f172a; }
    .pm-close { background: none; border: none; cursor: pointer; line-height: 1; font-size: 20px; color: #94a3b8; padding: 0 2px; }
    .pm-close:hover { color: #0f172a; }

    .pm-avatar-row { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
    .pm-avatar-btn { position: relative; width: 64px; height: 64px; border-radius: 50%; border: none; padding: 0; cursor: pointer; flex-shrink: 0; }
    .pm-avatar-img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; display: block; }
    .pm-avatar-initials { width: 64px; height: 64px; border-radius: 50%; background: var(--crimson, #8B1A1C); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; }
    .pm-avatar-edit { position: absolute; bottom: -2px; right: -2px; width: 24px; height: 24px; border-radius: 50%; background: var(--crimson, #8B1A1C); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 2px solid #fff; }
    .pm-avatar-hint { font-size: 10.5px; color: #94a3b8; line-height: 1.5; }

    .pm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
    .pm-field label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 6px; }
    .pm-field input {
        width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc;
        padding: 9px 11px; font-size: 12.5px; color: #334155; font-family: 'Poppins', sans-serif;
        outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .pm-field input:focus { border-color: var(--crimson, #8B1A1C); background: #fff; box-shadow: 0 0 0 3px rgba(139,26,28,.08); }

    .pm-readonly-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 4px; }
    .pm-readonly-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 6px; }
    .pm-readonly-badge { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 8px; background: #f1f5f9; border: 1px solid #e2e8f0; color: #334155; font-size: 12px; font-weight: 600; }
    .pm-readonly-note { font-size: 10.5px; color: #94a3b8; margin: 6px 0 16px; }

    .pm-pw-toggle { display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; padding: 10px 2px; border-top: 1px solid #f1f5f9; font-size: 12.5px; font-weight: 700; color: #334155; }
    .pm-pw-toggle i.pm-chev { transition: transform .18s; color: #94a3b8; }
    .pm-pw-toggle.open i.pm-chev { transform: rotate(180deg); }
    .pm-pw-section { display: none; flex-direction: column; gap: 14px; padding-top: 10px; }
    .pm-pw-section.open { display: flex; }

    .pm-pw-wrap { position: relative; }
    .pm-pw-wrap input { padding-right: 38px; }
    .pm-eye-btn { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); width: 30px; height: 30px; border: none; background: none; color: #94a3b8; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .pm-eye-btn:hover { color: #475569; }

    .pm-strength-bar { height: 5px; border-radius: 4px; background: #e2e8f0; margin-top: 8px; overflow: hidden; }
    .pm-strength-bar span { display: block; height: 100%; width: 0%; border-radius: 4px; background: #cbd5e1; transition: width .2s, background-color .2s; }
    .pm-strength-label { font-size: 10.5px; font-weight: 700; margin-top: 4px; color: #94a3b8; }
    .pm-req-list { list-style: none; margin-top: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 4px 10px; }
    .pm-req-list li { font-size: 10.5px; color: #94a3b8; display: flex; align-items: center; gap: 5px; }
    .pm-req-list li i { font-size: 12px; }
    .pm-req-list li.met { color: #166534; }
    .pm-req-list li.met i::before { content: "\ea5e"; } /* ti-circle-check */

    .pm-match-label { font-size: 10.5px; font-weight: 700; margin-top: 6px; color: #94a3b8; }
    .pm-match-label.ok { color: #166534; }
    .pm-match-label.bad { color: #a32d2d; }

    .pm-status { border-radius: 9px; padding: 9px 13px; font-size: 12px; font-weight: 600; display: none; margin-top: 14px; }
    .pm-status.error { display: block; background: #fee2e2; color: #b91c1c; }
    .pm-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
    .pm-btn { height: 38px; padding: 0 18px; border-radius: 10px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: 1px solid transparent; transition: all .15s; }
    .pm-btn:disabled { opacity: .5; cursor: not-allowed; }
    .pm-btn-cancel { background: #e2e8f0; color: #334155; border-color: #cbd5e1; }
    .pm-btn-cancel:hover:not(:disabled) { background: #cbd5e1; }
    .pm-btn-save { background: var(--crimson, #8B1A1C); color: #fff; }
    .pm-btn-save:hover:not(:disabled) { background: var(--crimson-dark, #5C1011); }

    @media (max-width: 520px) { .pm-grid, .pm-readonly-grid { grid-template-columns: 1fr; } .pm-req-list { grid-template-columns: 1fr; } }
</style>

<script>
(function () {
    const overlay = document.getElementById('pmOverlay');
    if (!overlay || window.prismOpenProfile) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const updateUrl = '{{ route('profile.update') }}';

    const nameInput     = document.getElementById('pmName');
    const usernameInput = document.getElementById('pmUsername');
    const emailInput    = document.getElementById('pmEmail');
    const employeeInput = document.getElementById('pmEmployee');
    const positionInput = document.getElementById('pmPosition');

    const avatarBtn      = document.getElementById('pmAvatarBtn');
    const avatarInput    = document.getElementById('pmAvatarInput');
    const avatarImg      = document.getElementById('pmAvatarImg');
    const avatarInitials = document.getElementById('pmAvatarInitials');
    let avatarFile = null;
    let avatarPreviewUrl = null;

    const pwToggle  = document.getElementById('pmPwToggle');
    const pwSection = document.getElementById('pmPwSection');
    const currentPw = document.getElementById('pmCurrentPassword');
    const newPw     = document.getElementById('pmNewPassword');
    const confirmPw = document.getElementById('pmConfirmPassword');
    const strengthFill  = document.getElementById('pmStrengthFill');
    const strengthLabel = document.getElementById('pmStrengthLabel');
    const matchLabel    = document.getElementById('pmMatchLabel');
    const reqItems = document.querySelectorAll('#pmReqList li');

    const statusEl = document.getElementById('pmStatus');
    const saveBtn  = document.getElementById('pmSaveBtn');

    const originalName     = nameInput.value;
    const originalUsername = usernameInput.value;
    const originalEmail    = emailInput.value;
    const originalEmployee = employeeInput.value;
    const originalPosition = positionInput.value;

    function resetForm() {
        nameInput.value     = originalName;
        usernameInput.value = originalUsername;
        emailInput.value    = originalEmail;
        employeeInput.value = originalEmployee;
        positionInput.value = originalPosition;
        avatarFile = null;
        avatarInput.value = '';
        if (avatarPreviewUrl) { URL.revokeObjectURL(avatarPreviewUrl); avatarPreviewUrl = null; }
        currentPw.value = ''; newPw.value = ''; confirmPw.value = '';
        [currentPw, newPw, confirmPw].forEach(el => el.type = 'password');
        pwToggle.classList.remove('open');
        pwSection.classList.remove('open');
        statusEl.className = 'pm-status';
        updateStrength();
        updateMatch();
    }

    window.prismOpenProfile = function () {
        resetForm();
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
    };
    function closeModal() {
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        if (avatarPreviewUrl) { URL.revokeObjectURL(avatarPreviewUrl); avatarPreviewUrl = null; }
    }
    document.getElementById('pmCloseBtn').addEventListener('click', closeModal);
    document.getElementById('pmCancelBtn').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal(); });

    avatarBtn.addEventListener('click', () => avatarInput.click());
    avatarInput.addEventListener('change', () => {
        const file = avatarInput.files[0];
        if (!file) return;
        avatarFile = file;
        if (avatarPreviewUrl) URL.revokeObjectURL(avatarPreviewUrl);
        avatarPreviewUrl = URL.createObjectURL(file);
        avatarImg.src = avatarPreviewUrl;
        avatarImg.style.display = '';
        avatarInitials.style.display = 'none';
    });

    pwToggle.addEventListener('click', () => {
        pwToggle.classList.toggle('open');
        pwSection.classList.toggle('open');
    });

    document.querySelectorAll('.pm-eye-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.innerHTML = showing ? '<i class="ti ti-eye"></i>' : '<i class="ti ti-eye-off"></i>';
        });
    });

    // Password fields must be typed, not pasted in — the whole point of the
    // "type it twice" confirm step and the live strength checklist.
    [newPw, confirmPw].forEach(el => el.addEventListener('paste', (e) => e.preventDefault()));

    function passwordChecks(pw) {
        return {
            length: pw.length >= 8,
            lower:  /[a-z]/.test(pw),
            upper:  /[A-Z]/.test(pw),
            digit:  /[0-9]/.test(pw),
            symbol: /[^A-Za-z0-9]/.test(pw),
        };
    }

    function updateStrength() {
        const pw = newPw.value;
        const checks = passwordChecks(pw);
        reqItems.forEach(li => li.classList.toggle('met', !!checks[li.dataset.req]));

        if (!pw) {
            strengthFill.style.width = '0%';
            strengthFill.style.background = '#cbd5e1';
            strengthLabel.textContent = ' ';
            return;
        }
        const classesMet = ['lower', 'upper', 'digit', 'symbol'].filter(k => checks[k]).length;
        let level, pct, color;
        if (!checks.length || classesMet <= 1) { level = 'Weak'; pct = 33; color = '#a32d2d'; }
        else if (classesMet <= 3) { level = 'Medium'; pct = 66; color = '#854f0b'; }
        else { level = 'Strong'; pct = 100; color = '#166534'; }
        strengthFill.style.width = pct + '%';
        strengthFill.style.background = color;
        strengthLabel.textContent = level + ' password';
        strengthLabel.style.color = color;
    }
    function updateMatch() {
        if (!confirmPw.value) { matchLabel.textContent = ' '; matchLabel.className = 'pm-match-label'; return; }
        const ok = newPw.value === confirmPw.value;
        matchLabel.textContent = ok ? 'Passwords match.' : 'Passwords do not match.';
        matchLabel.className = 'pm-match-label ' + (ok ? 'ok' : 'bad');
    }
    newPw.addEventListener('input', () => { updateStrength(); updateMatch(); });
    confirmPw.addEventListener('input', updateMatch);

    saveBtn.addEventListener('click', async () => {
        statusEl.className = 'pm-status';

        const wantsPasswordChange = pwSection.classList.contains('open') && (newPw.value || confirmPw.value || currentPw.value);
        if (wantsPasswordChange) {
            const checks = passwordChecks(newPw.value);
            const meetsAll = Object.values(checks).every(Boolean);
            if (!currentPw.value) {
                statusEl.className = 'pm-status error'; statusEl.textContent = 'Enter your current password.'; return;
            }
            if (!meetsAll) {
                statusEl.className = 'pm-status error'; statusEl.textContent = 'Your new password does not meet the requirements below.'; return;
            }
            if (newPw.value !== confirmPw.value) {
                statusEl.className = 'pm-status error'; statusEl.textContent = 'New password and confirmation do not match.'; return;
            }
        }

        const fd = new FormData();
        fd.append('name', nameInput.value.trim());
        fd.append('username', usernameInput.value.trim());
        fd.append('email', emailInput.value.trim());
        if (employeeInput.value.trim()) fd.append('employee_number', employeeInput.value.trim());
        if (positionInput.value.trim()) fd.append('position_title', positionInput.value.trim());
        if (avatarFile) fd.append('avatar', avatarFile);
        if (wantsPasswordChange) {
            fd.append('current_password', currentPw.value);
            fd.append('new_password', newPw.value);
            fd.append('new_password_confirmation', confirmPw.value);
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Saving…';

        try {
            const resp = await fetch(updateUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                const sbName = document.getElementById('sbUserName');
                if (sbName) sbName.textContent = json.name;
                const sbAvatarImg = document.getElementById('sbAvatarImg');
                const sbAvatarInitials = document.getElementById('sbAvatarInitials');
                if (json.avatarUrl && sbAvatarImg) {
                    sbAvatarImg.src = json.avatarUrl;
                    sbAvatarImg.style.display = '';
                    if (sbAvatarInitials) sbAvatarInitials.style.display = 'none';
                }
                if (window.prismToast) window.prismToast('Your info was updated.');
                closeModal();
            } else {
                statusEl.className = 'pm-status error';
                statusEl.textContent = json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Could not save changes.');
            }
        } catch {
            statusEl.className = 'pm-status error';
            statusEl.textContent = 'Network error — please try again.';
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="ti ti-check"></i> Save Changes';
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
