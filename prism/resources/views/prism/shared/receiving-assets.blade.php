@once
@push('page-css')
<style>
    .asset-tabs { display:flex; flex-wrap:wrap; gap:8px; margin:14px 0; }
    .asset-tabs button { padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; background:white; color:#681012; cursor:pointer; font:inherit; }
    .asset-tabs button[aria-selected="true"] { background:#681012; color:white; }
    .asset-panel[hidden] { display:none !important; }
    .receiving-section { margin: 24px 28px; padding: 22px; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; color: #1e293b; }
    .receiving-section h2 { margin: 0 0 6px; font-size: 18px; }
    .receiving-note { color: #475569; font-size: 12px; line-height: 1.6; margin: 4px 0 12px; }
    .receiving-scroll { overflow-x: auto; }
    .receiving-table { width: 100%; min-width: 1000px; border-collapse: collapse; font-size: 12px; }
    .receiving-table th, .receiving-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .receiving-table th { background: #f8fafc; color: #334155; font-size: 11px; white-space: normal; }
    .receiving-status { display: inline-block; color: #334155; font-weight: 700; }
    .receiving-late { color: #b91c1c; font-weight: 600; }
    .receiving-details { text-align: left; color: #334155; font-size: 12px; }
    .receiving-details summary { cursor: pointer; color: #681012; font-weight: 700; padding: 10px 0; }
    .receiving-details dl { display: flex; flex-wrap: wrap; gap: 20px; margin: 12px 0; }
    .receiving-details dt { color: #64748b; font-size: 11px; }
    .receiving-details dd { margin: 4px 0; font-weight: 600; }
    .receiving-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; padding: 16px; margin: 12px 0; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; }
    .receiving-form label { display: flex; flex-direction: column; gap: 6px; color: #334155; font-weight: 600; font-size: 12px; }
    .receiving-form input, .receiving-form textarea { width: 100%; min-width: 0; padding: 9px; border: 1px solid #94a3b8; border-radius: 6px; background: #fff; color: #0f172a; font: inherit; box-sizing: border-box; }
    .receiving-form button { align-self: end; padding: 10px 16px; border: 0; border-radius: 6px; background: #681012; color: white; font-weight: 700; cursor: pointer; }
    .receiving-form .receiving-wide { grid-column: 1 / -1; }
    .receiving-history { padding: 12px 16px; margin: 10px 0; background: #f8fafc; border-left: 3px solid #cbd5e1; }
    .receiving-message { padding: 12px; margin: 12px 0; background: #dcfce7; color: #166534; border-radius: 8px; }
    .receiving-message.error { background: #fee2e2; color: #991b1b; }
    .receiving-form [hidden] { display: none !important; }
    .receiving-form button:disabled { opacity: .65; cursor: wait; }
    .receiving-success-modal { width: min(420px, calc(100vw - 40px)); margin: auto; padding: 30px; border: 0; border-radius: 18px; background: #fff; color: #1e293b; box-shadow: 0 24px 80px rgba(15,23,42,.25); text-align: center; }
    .receiving-success-modal::backdrop { background: rgba(15,23,42,.5); }
    .receiving-success-modal .receiving-success-icon { display: grid; place-items: center; width: 54px; height: 54px; margin: 0 auto 16px; border-radius: 50%; background: #dcfce7; color: #166534; }
    .receiving-success-modal h2 { margin: 0 0 10px; font-size: 20px; }
    .receiving-success-modal p { margin: 0 0 22px; font-size: 14px; line-height: 1.6; }
    .receiving-success-modal button { padding: 11px 30px; border: 0; border-radius: 8px; background: #681012; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
    .pr-items-panel { overflow-x: auto; }
    @media (max-width: 640px) {
        .receiving-table .receiving-details { width: calc(100vw - 128px); }
        .receiving-form { grid-template-columns: minmax(0, 1fr); }
        .receiving-table th, .receiving-table td { overflow-wrap: anywhere; }
    }
    @media print { .receiving-details, .receiving-message, .receiving-actions-row { display: none !important; } .receiving-section { margin: 12px 0; padding: 8px; } .receiving-scroll { overflow: visible; } .receiving-table { min-width: 0; } }
</style>
@endpush
@endonce
@once
@push('scripts')
<dialog class="receiving-success-modal" id="receivingSuccessModal" aria-labelledby="receivingSuccessTitle" aria-describedby="receivingSuccessMessage">
    <div class="receiving-success-icon" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"/></svg></div>
    <h2 id="receivingSuccessTitle">Receipt saved</h2>
    <p id="receivingSuccessMessage"></p>
    <form method="dialog"><button type="submit" autofocus>OK</button></form>
</dialog>
<script>
(() => {
    document.addEventListener('click', event => {
        const tab = event.target.closest('[data-receiving-tab]');
        if (!tab) return;
        const details = tab.closest('.receiving-details');
        details.querySelectorAll('[data-receiving-tab]').forEach(button => button.setAttribute('aria-selected', String(button === tab)));
        details.querySelectorAll('[data-receiving-panel]').forEach(panel => { panel.hidden = panel.dataset.receivingPanel !== tab.dataset.receivingTab; });
    });
    const modal = document.getElementById('receivingSuccessModal');
    let returnFocus = null;
    modal.addEventListener('close', () => returnFocus?.focus({ preventScroll: true }));

    document.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-receipt-form]')) return;
        event.preventDefault();
        if (form.dataset.saving === 'true') return;

        const submit = form.querySelector('button[type="submit"]');
        const errors = form.querySelector('[data-receipt-errors]');
        const originalLabel = submit.textContent;
        const body = new FormData(form);
        form.dataset.saving = 'true';
        form.setAttribute('aria-busy', 'true');
        submit.disabled = true;
        submit.textContent = 'Saving…';
        errors.hidden = true;
        errors.textContent = '';

        try {
            const response = await fetch(form.action, {
                method: 'POST', body, credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                const messages = data?.errors ? Object.values(data.errors).flat() : [data?.message || 'Could not save the receipt. Please try again.'];
                for (const message of messages) {
                    const line = document.createElement('div');
                    line.textContent = message;
                    errors.append(line);
                }
                errors.hidden = false;
                errors.focus({ preventScroll: true });
                return;
            }
            if (!data?.delivery || typeof data.detailsHtml !== 'string') {
                throw new Error('Unexpected save response.');
            }

            const delivery = data.delivery;
            const details = form.closest('.receiving-details');
            const template = document.createElement('template');
            template.innerHTML = data.detailsHtml.trim();
            const updated = template.content.querySelector('.receiving-details');
            if (!updated) throw new Error('Receipt details were not returned.');

            const scrollX = window.scrollX;
            const scrollY = window.scrollY;
            const values = {
                arrival: delivery.arrivalDate || (delivery.lastArrivalDate ? 'Latest partial: ' + delivery.lastArrivalDate : 'Arrival not recorded'),
                quantity: `${delivery.receivedQuantity} / ${delivery.quantity} ${delivery.unit}`,
                status: delivery.receivingStatus,
                review: delivery.receivingReviewRequired ? 'Receipt discrepancy: review required' : '',
                duration: delivery.daysToReceive !== null ? delivery.daysToReceive + ' days' : '—',
                delay: delivery.delayLabel,
            };
            document.querySelectorAll('[data-receiving-item]').forEach(cell => {
                if (cell.dataset.receivingItem !== String(delivery.id)) return;
                const field = cell.dataset.receivingField;
                if (field in values) cell.textContent = values[field];
                if (field === 'delay') cell.classList.toggle('receiving-late', delivery.daysDelayed > 0);
            });
            // Keep the expanded item and surrounding page in place; refresh only its contents.
            details.replaceChildren(...updated.childNodes);
            document.querySelectorAll('[data-receiving-po]').forEach(cell => {
                if (cell.dataset.receivingPo === String(delivery.poId)) cell.textContent = delivery.paymentStatus;
            });
            const badge = details.closest('.pr-card')?.querySelector('.pr-card-right .badge');
            if (badge && data.trackingStatus) {
                badge.textContent = data.trackingStatus.key === 'paid' ? 'Paid — see item receiving status' : data.trackingStatus.label;
                badge.classList.toggle('badge-completed', data.trackingStatus.key === 'paid');
                badge.classList.toggle('badge-progress', data.trackingStatus.key !== 'paid');
                badge.classList.remove('badge-pending');
            }
            returnFocus = details.querySelector('summary');
            document.getElementById('receivingSuccessMessage').textContent = data.message;
            if (!modal.open) modal.showModal();
            window.scrollTo({ left: scrollX, top: scrollY, behavior: 'instant' });
        } catch (error) {
            errors.textContent = 'Could not confirm the save. Check your connection before trying again.';
            errors.hidden = false;
            errors.focus({ preventScroll: true });
        } finally {
            form.dataset.saving = 'false';
            form.removeAttribute('aria-busy');
            submit.disabled = false;
            submit.textContent = originalLabel;
        }
    });
})();
</script>
@endpush
@endonce
@once
    @if(!($suppressReceivingMessages ?? false) && session('receiving_success'))
        <div class="receiving-message" role="status">{{ session('receiving_success') }}</div>
    @endif
    @if(!($suppressReceivingMessages ?? false) && $errors->any())
        <div class="receiving-message error" role="alert">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif
@endonce
