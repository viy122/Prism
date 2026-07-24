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
})();
</script>
