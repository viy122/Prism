{{--
    Single, shared confirmation modal for the whole system. Include once per
    layout (already wired into prism.layouts.app and prism.layouts.office-head)
    and call it from any page's JS via:

        const ok = await window.prismConfirm({
            title: 'Remove item?',
            message: 'Remove this item from the proposal?',
            confirmText: 'Remove',
            danger: true, // red confirm button for destructive actions; omit/false for neutral
        });
        if (!ok) return;

    Returns a Promise<boolean> — resolves true on Confirm, false on Cancel or
    backdrop/Escape dismiss. Do not build page-specific modals for confirmations
    — reuse this one so the design stays consistent everywhere.
--}}
<div id="prismConfirmOverlay" class="prism-confirm-overlay" aria-hidden="true">
    <div class="prism-confirm-card" role="alertdialog" aria-modal="true" aria-labelledby="prismConfirmTitle" aria-describedby="prismConfirmMessage">
        <p class="prism-confirm-title" id="prismConfirmTitle">Are you sure?</p>
        <p class="prism-confirm-message" id="prismConfirmMessage"></p>
        <div class="prism-confirm-actions">
            <button type="button" class="prism-confirm-btn prism-confirm-cancel" id="prismConfirmCancelBtn">Cancel</button>
            <button type="button" class="prism-confirm-btn prism-confirm-ok" id="prismConfirmOkBtn">Confirm</button>
        </div>
    </div>
</div>

{{--
    Shared info popup — same visual language as the confirm modal above (same
    overlay, card, radius, shadow, fonts) but for showing read-only content
    with a single Close button instead of asking for a Confirm/Cancel decision.

        window.prismInfoModal({ title: 'Product Details', bodyHtml: '<p>...</p>' });

    Do not build page-specific info popups — reuse this one so the design
    stays consistent everywhere.
--}}
<div id="prismInfoOverlay" class="prism-confirm-overlay" aria-hidden="true">
    <div class="prism-confirm-card prism-info-card" role="dialog" aria-modal="true" aria-labelledby="prismInfoTitle">
        <div class="prism-info-head">
            <p class="prism-confirm-title" id="prismInfoTitle">Details</p>
            <button type="button" class="prism-info-close" id="prismInfoCloseX" aria-label="Close">&times;</button>
        </div>
        <div class="prism-info-body" id="prismInfoBody"></div>
        <div class="prism-confirm-actions">
            <button type="button" class="prism-confirm-btn prism-confirm-cancel" id="prismInfoCloseBtn">Close</button>
        </div>
    </div>
</div>

{{--
    Shared success modal — for one-way transitions the user should consciously
    acknowledge before the page moves on (e.g. a form submit immediately
    followed by a redirect). Does NOT auto-dismiss; that's the point of using
    this instead of a toast for these specific cases.

        await window.prismSuccess({ title: 'Submitted', message: 'Your PPMP was submitted.' });
        window.location.href = redirectUrl;

    Returns a Promise that resolves once the user clicks OK/backdrop/Escape.
    For everything reversible/low-consequence (toggles, single-row deletes,
    inline saves), keep using the existing toast pattern instead — don't
    convert working toasts to this modal.
--}}
<div id="prismSuccessOverlay" class="prism-confirm-overlay" aria-hidden="true">
    <div class="prism-confirm-card prism-success-card" role="alertdialog" aria-modal="true" aria-labelledby="prismSuccessTitle" aria-describedby="prismSuccessMessage">
        <div class="prism-success-icon"><i class="ti ti-circle-check"></i></div>
        <p class="prism-confirm-title" id="prismSuccessTitle">Success</p>
        <p class="prism-confirm-message" id="prismSuccessMessage"></p>
        <div class="prism-confirm-actions">
            <button type="button" class="prism-confirm-btn prism-confirm-ok neutral" id="prismSuccessOkBtn">OK</button>
        </div>
    </div>
</div>

<style>
    .prism-confirm-overlay {
        position: fixed; inset: 0; z-index: 2000;
        background: rgba(28,16,16,.45);
        display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .prism-confirm-overlay.open { display: flex; }
    .prism-confirm-card {
        background: var(--white, #fff); border-radius: var(--r, 16px);
        box-shadow: var(--sh2, 0 6px 24px rgba(0,0,0,.15));
        width: 100%; max-width: 380px; padding: 22px 24px;
        font-family: 'Poppins', sans-serif;
    }
    .prism-confirm-title { font-size: 15px; font-weight: 800; color: var(--txt, #1C1010); margin-bottom: 8px; }
    .prism-confirm-message { font-size: 13px; line-height: 1.6; color: var(--txt2, #6B4F50); }
    .prism-confirm-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }
    .prism-confirm-btn {
        height: 38px; padding: 0 18px; border-radius: var(--r-sm, 10px);
        font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif;
        border: 1px solid transparent; transition: all .15s;
    }
    .prism-confirm-cancel { background: var(--white, #fff); color: var(--txt2, #6B4F50); border-color: var(--border2, #e5e5e5); }
    .prism-confirm-cancel:hover { border-color: var(--txt3, #A88B8C); }
    .prism-confirm-ok { background: var(--crimson, #8B1A1C); color: #fff; }
    .prism-confirm-ok:hover { background: var(--crimson-dark, #5C1011); }
    .prism-confirm-ok.neutral { background: var(--txt, #1C1010); }
    .prism-confirm-ok.neutral:hover { background: #000; }

    .prism-info-card { max-width: 440px; max-height: 82vh; display: flex; flex-direction: column; }
    .prism-info-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 4px; }
    .prism-info-head .prism-confirm-title { margin-bottom: 0; }
    .prism-info-close {
        background: none; border: none; cursor: pointer; line-height: 1;
        font-size: 20px; color: var(--txt3, #A88B8C); padding: 0 2px; flex-shrink: 0;
    }
    .prism-info-close:hover { color: var(--txt, #1C1010); }
    .prism-info-body { overflow-y: auto; margin-top: 10px; }

    .prism-success-icon { text-align: center; font-size: 34px; color: #166534; margin-bottom: 8px; }
    .prism-success-card .prism-confirm-title,
    .prism-success-card .prism-confirm-message { text-align: center; }
    .prism-success-card .prism-confirm-actions { justify-content: center; }
</style>

<script>
(function () {
    const overlay = document.getElementById('prismConfirmOverlay');
    if (!overlay || window.prismConfirm) return; // guard: only wire once even if included twice

    const titleEl   = document.getElementById('prismConfirmTitle');
    const msgEl     = document.getElementById('prismConfirmMessage');
    const okBtn     = document.getElementById('prismConfirmOkBtn');
    const cancelBtn = document.getElementById('prismConfirmCancelBtn');
    let resolvePromise = null;

    function close(result) {
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        if (resolvePromise) { resolvePromise(result); resolvePromise = null; }
    }

    okBtn.addEventListener('click', () => close(true));
    cancelBtn.addEventListener('click', () => close(false));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(false); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('open')) close(false);
    });

    window.prismConfirm = function (options) {
        options = options || {};
        titleEl.textContent = options.title || 'Are you sure?';
        msgEl.textContent   = options.message || '';
        okBtn.textContent   = options.confirmText || 'Confirm';
        cancelBtn.textContent = options.cancelText || 'Cancel';
        okBtn.className = 'prism-confirm-btn prism-confirm-ok' + (options.danger === false ? ' neutral' : '');

        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        okBtn.focus();

        return new Promise((resolve) => { resolvePromise = resolve; });
    };

    // ── Info popup (read-only content, single Close button) ────────────────
    const infoOverlay  = document.getElementById('prismInfoOverlay');
    const infoTitleEl  = document.getElementById('prismInfoTitle');
    const infoBodyEl   = document.getElementById('prismInfoBody');
    const infoCloseBtn = document.getElementById('prismInfoCloseBtn');
    const infoCloseX   = document.getElementById('prismInfoCloseX');

    function closeInfo() {
        infoOverlay.classList.remove('open');
        infoOverlay.setAttribute('aria-hidden', 'true');
    }

    infoCloseBtn.addEventListener('click', closeInfo);
    infoCloseX.addEventListener('click', closeInfo);
    infoOverlay.addEventListener('click', (e) => { if (e.target === infoOverlay) closeInfo(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && infoOverlay.classList.contains('open')) closeInfo();
    });

    window.prismInfoModal = function (options) {
        options = options || {};
        infoTitleEl.textContent = options.title || 'Details';
        infoBodyEl.innerHTML    = options.bodyHtml || '';

        infoOverlay.classList.add('open');
        infoOverlay.setAttribute('aria-hidden', 'false');
        infoCloseBtn.focus();
    };

    // ── Success modal (one-way transitions the user must acknowledge) ──────
    const successOverlay  = document.getElementById('prismSuccessOverlay');
    const successTitleEl  = document.getElementById('prismSuccessTitle');
    const successMsgEl    = document.getElementById('prismSuccessMessage');
    const successOkBtn    = document.getElementById('prismSuccessOkBtn');
    let resolveSuccessPromise = null;

    function closeSuccess() {
        successOverlay.classList.remove('open');
        successOverlay.setAttribute('aria-hidden', 'true');
        if (resolveSuccessPromise) { resolveSuccessPromise(); resolveSuccessPromise = null; }
    }

    successOkBtn.addEventListener('click', closeSuccess);
    successOverlay.addEventListener('click', (e) => { if (e.target === successOverlay) closeSuccess(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && successOverlay.classList.contains('open')) closeSuccess();
    });

    window.prismSuccess = function (options) {
        options = options || {};
        successTitleEl.textContent = options.title || 'Success';
        successMsgEl.textContent   = options.message || '';

        successOverlay.classList.add('open');
        successOverlay.setAttribute('aria-hidden', 'false');
        successOkBtn.focus();

        return new Promise((resolve) => { resolveSuccessPromise = resolve; });
    };
})();
</script>
