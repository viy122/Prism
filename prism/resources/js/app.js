import './bootstrap';
import {
    ArrowLeft,
    Bell,
    CalendarDays,
    ChartNoAxesCombined,
    ChevronLeft,
    CheckCircle2,
    ClipboardCheck,
    Construction,
    createIcons,
    FileDown,
    FileText,
    FilePlus2,
    FileStack,
    FolderCheck,
    History,
    LayoutDashboard,
    ListChecks,
    LogOut,
    MousePointerClick,
    Pencil,
    Plus,
    Printer,
    ReceiptText,
    RefreshCw,
    RotateCcw,
    Save,
    Search,
    Send,
    ShieldCheck,
    Sparkles,
    MessageCircle,
    Trash2,
    Undo2,
    Upload,
    UploadCloud,
} from 'lucide';

const icons = {
    ArrowLeft,
    Bell,
    CalendarDays,
    ChartNoAxesCombined,
    ChevronLeft,
    CheckCircle2,
    ClipboardCheck,
    Construction,
    FileDown,
    FileText,
    FilePlus2,
    FileStack,
    FolderCheck,
    History,
    LayoutDashboard,
    ListChecks,
    LogOut,
    MousePointerClick,
    Pencil,
    Plus,
    Printer,
    ReceiptText,
    RefreshCw,
    RotateCcw,
    Save,
    Search,
    Send,
    ShieldCheck,
    Sparkles,
    MessageCircle,
    Trash2,
    Undo2,
    Upload,
    UploadCloud,
};

const money = (value) => `PHP ${new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 0,
}).format(Number(value) || 0)}`;

const readJson = (id, fallback = []) => {
    const element = document.getElementById(id);

    if (!element) {
        return fallback;
    }

    try {
        return JSON.parse(element.textContent);
    } catch {
        return fallback;
    }
};

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const slugStatus = (value) => String(value ?? '')
    .toLowerCase()
    .replaceAll(' ', '-');

const neutralBadgeClass = 'inline-flex min-h-7 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200';
const badgeBaseClass = 'inline-flex min-h-7 items-center rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset';
const badgeTones = {
    draft: 'bg-slate-100 text-slate-700 ring-slate-200',
    submitted: 'bg-blue-50 text-blue-700 ring-blue-200',
    'under-review': 'bg-amber-50 text-amber-700 ring-amber-200',
    endorsed: 'bg-purple-50 text-purple-700 ring-purple-200',
    returned: 'bg-red-50 text-red-700 ring-red-200',
    approved: 'bg-green-50 text-green-700 ring-green-200',
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    'in-progress': 'bg-blue-50 text-blue-700 ring-blue-200',
    completed: 'bg-green-50 text-green-700 ring-green-200',
    delayed: 'bg-red-50 text-red-700 ring-red-200',
    'on-track': 'bg-green-50 text-green-700 ring-green-200',
    'at-risk': 'bg-amber-50 text-amber-700 ring-amber-200',
    watch: 'bg-amber-50 text-amber-700 ring-amber-200',
    critical: 'bg-red-50 text-red-700 ring-red-200',
};
const badgeClass = (status) => `${badgeBaseClass} ${badgeTones[slugStatus(status)] ?? badgeTones.draft}`;
const primaryButtonClass = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70';
const secondaryButtonClass = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-bsu-maroon/35 bg-white px-4 text-sm font-bold text-bsu-maroon shadow-sm transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70';
const rowActionsClass = 'flex flex-wrap gap-2';
const tableActionClass = 'inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-bsu-maroon shadow-sm transition hover:border-bsu-gold hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70';
const tableActionDangerClass = 'inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-white text-red-700 shadow-sm transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200';
const scopeListClass = 'grid min-w-60 gap-2.5';
const scopeItemClass = 'rounded-xl border border-slate-200 bg-white p-3 text-sm leading-6 shadow-sm [&_strong]:block [&_strong]:font-bold [&_strong]:text-slate-950 [&_span]:block [&_span]:text-slate-600 [&_a]:font-bold [&_a]:text-bsu-maroon [&_a:hover]:underline';
const noteClass = 'mt-1.5 block text-sm leading-6 text-slate-500';
const timelineClass = 'relative grid gap-3 border-l border-bsu-gold/60 pl-5 [&_li]:relative [&_li]:grid [&_li]:gap-1.5 [&_li]:rounded-2xl [&_li]:border [&_li]:border-slate-200 [&_li]:bg-white [&_li]:p-4 [&_li]:shadow-sm [&_li:before]:absolute [&_li:before]:-left-[1.62rem] [&_li:before]:top-5 [&_li:before]:h-3 [&_li:before]:w-3 [&_li:before]:rounded-full [&_li:before]:bg-bsu-maroon [&_strong]:text-sm [&_strong]:font-bold [&_strong]:text-slate-950 [&_span]:text-xs [&_span]:font-bold [&_span]:text-slate-500 [&_p]:text-sm [&_p]:leading-6 [&_p]:text-slate-600';
const timelineEmptyClass = 'flex min-h-48 flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-base leading-7 text-slate-500 [&_svg]:h-10 [&_svg]:w-10 [&_svg]:text-bsu-maroon/70';
const returnedBoxClass = 'rounded-2xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-800 shadow-sm';
const panelHeaderClass = 'mb-4 flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950';
const eyebrowClass = 'text-xs font-extrabold uppercase tracking-[0.12em] text-bsu-maroon';
const detailGridClass = 'grid gap-3 sm:grid-cols-2 xl:grid-cols-3 [&_div]:rounded-2xl [&_div]:border [&_div]:border-slate-200 [&_div]:bg-slate-50 [&_div]:p-4 [&_dt]:text-xs [&_dt]:font-extrabold [&_dt]:uppercase [&_dt]:tracking-[0.07em] [&_dt]:text-slate-500 [&_dd]:mt-1.5 [&_dd]:text-sm [&_dd]:font-bold [&_dd]:text-slate-950';
const ocrPanelClass = 'grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[220px_minmax(0,1fr)] [&_h3]:mt-1.5 [&_h3]:text-base [&_h3]:font-extrabold [&_h3]:text-slate-950 [&_dl]:grid [&_dl]:gap-3 [&_dl]:sm:grid-cols-2 [&_dt]:text-xs [&_dt]:font-extrabold [&_dt]:uppercase [&_dt]:tracking-[0.07em] [&_dt]:text-slate-500 [&_dd]:mt-1 [&_dd]:text-sm [&_dd]:font-bold [&_dd]:text-slate-950';
const pdfPreviewClass = 'flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm [&_svg]:h-10 [&_svg]:w-10 [&_svg]:text-bsu-maroon [&_span]:text-xs [&_span]:font-extrabold [&_span]:uppercase [&_span]:tracking-[0.07em] [&_span]:text-slate-500 [&_strong]:mt-1 [&_strong]:block [&_strong]:text-base [&_strong]:font-extrabold [&_strong]:text-slate-950 [&_small]:mt-1 [&_small]:block [&_small]:text-sm [&_small]:text-slate-500';
const formGridClass = 'grid gap-4 sm:grid-cols-2 [&_label]:block [&_label>span]:mb-1.5 [&_label>span]:block [&_label>span]:text-sm [&_label>span]:font-bold [&_label>span]:text-slate-700 [&_select]:h-10 [&_select]:w-full [&_select]:rounded-xl [&_select]:border [&_select]:border-slate-300 [&_select]:bg-white [&_select]:px-4 [&_select]:text-sm [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:px-4 [&_textarea]:py-3 [&_textarea]:text-sm [&_select:focus]:border-bsu-maroon [&_select:focus]:outline-none [&_select:focus]:ring-2 [&_select:focus]:ring-bsu-gold/40 [&_textarea:focus]:border-bsu-maroon [&_textarea:focus]:outline-none [&_textarea:focus]:ring-2 [&_textarea:focus]:ring-bsu-gold/40';
const wideFieldClass = 'sm:col-span-2';
const formActionsClass = 'flex flex-wrap items-end gap-2 sm:col-span-2';
const detailPanelClass = 'grid gap-5';
const approvalItemsClass = 'grid gap-4';
const approvalItemClass = 'grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 xl:grid-cols-[minmax(0,1fr)_minmax(260px,0.8fr)] [&_strong]:block [&_strong]:text-base [&_strong]:font-extrabold [&_strong]:text-slate-950 [&_span]:mt-1.5 [&_span]:block [&_span]:text-sm [&_span]:font-bold [&_span]:text-slate-600 [&_p]:mt-2 [&_p]:text-sm [&_p]:leading-6 [&_p]:text-slate-600';
const reviewActionsClass = 'mt-4 flex flex-wrap gap-2.5';
const selectedRowClasses = ['bg-bsu-maroon/5', 'outline', 'outline-1', 'outline-bsu-maroon', 'outline-offset-[-1px]'];
const delayedRowClasses = ['bg-red-50'];

const toggleClasses = (element, classes, force) => {
    classes.forEach((className) => element.classList.toggle(className, force));
};

const refreshIcons = () => createIcons({ icons });

const showToast = (message) => {
    const oldToast = document.querySelector('[data-prism-toast]');

    if (oldToast) {
        oldToast.remove();
    }

    const toast = document.createElement('div');
    toast.dataset.prismToast = 'true';
    toast.className = 'fixed bottom-5 right-5 z-[80] max-w-sm rounded-2xl border border-bsu-maroon/20 bg-bsu-maroon px-5 py-4 text-sm font-bold text-white shadow-2xl shadow-bsu-maroon/25';
    toast.textContent = message;
    document.body.appendChild(toast);

    window.setTimeout(() => toast.remove(), 2800);
};

const marketScopeFor = (item) => {
    const unitCost = Number(item.estimatedUnitCost) || 0;
    const retrieved = new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date());

    return [
        {
            supplierName: 'Validated Market Source A',
            price: Math.round(unitCost * 0.97),
            sourceLink: 'https://example.com/validated-market-source-a',
            dateRetrieved: retrieved,
        },
        {
            supplierName: 'Validated Market Source B',
            price: Math.round(unitCost * 1.03),
            sourceLink: 'https://example.com/validated-market-source-b',
            dateRetrieved: retrieved,
        },
    ];
};

const initBudgetProposal = () => {
    const tableBody = document.getElementById('encodedItemsTable');
    const form = document.getElementById('proposalItemForm');

    if (!tableBody || !form) {
        return;
    }

    let items = readJson('initialProposalItems');

    const fields = {
        id: document.getElementById('itemId'),
        description: document.getElementById('itemDescription'),
        unit: document.getElementById('itemUnit'),
        quantity: document.getElementById('itemQuantity'),
        unitCost: document.getElementById('itemUnitCost'),
        quarter: document.getElementById('itemQuarter'),
        justification: document.getElementById('itemJustification'),
    };
    const saveButton = document.getElementById('saveItemButton');
    const clearButton = document.getElementById('clearItemButton');
    const scopeButton = document.getElementById('runMarketScopingButton');
    const itemCount = document.getElementById('proposalItemCount');
    const submitButton = document.getElementById('submitProposalButton');
    const summaryItems = document.getElementById('proposalSummaryItems');
    const summaryTotal = document.getElementById('proposalSummaryTotal');
    const summaryReferences = document.getElementById('proposalSummaryReferences');
    const summaryMissing = document.getElementById('proposalSummaryMissing');
    const readyBadge = document.getElementById('proposalReadyBadge');
    const referenceList = document.getElementById('proposalReferenceList');
    const referenceTotalBadge = document.getElementById('proposalReferenceTotalBadge');

    const scopeMarkup = (scoping) => {
        if (!scoping?.length) {
            return `<span class="${neutralBadgeClass}">No references</span>`;
        }

        const lowest = [...scoping].sort((first, second) => Number(first.price) - Number(second.price))[0];

        return `
            <div class="grid gap-1.5">
                <span class="inline-flex min-h-7 w-max items-center rounded-full bg-bsu-maroon/10 px-3 py-1 text-xs font-bold text-bsu-maroon ring-1 ring-inset ring-bsu-maroon/20">${scoping.length} refs</span>
                ${lowest ? `<span class="text-xs font-semibold text-slate-500">Lowest: ${escapeHtml(lowest.supplierName)} · ${money(lowest.price)}</span>` : ''}
            </div>
        `;
    };

    const referenceCardMarkup = (item) => {
        const scoping = item.scoping ?? [];
        const lowest = [...scoping].sort((first, second) => Number(first.price) - Number(second.price))[0];

        return `
            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-950">${escapeHtml(item.description)}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">${escapeHtml(item.targetQuarter)} · ${scoping.length} references</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-bsu-maroon ring-1 ring-inset ring-slate-200">${escapeHtml(item.unit)}</span>
                </div>
                ${lowest
                    ? `<p class="mt-2 text-xs font-semibold text-slate-600">Lowest: ${escapeHtml(lowest.supplierName)} · ${money(lowest.price)}</p>`
                    : '<p class="mt-2 text-xs font-semibold text-amber-700">Needs market scoping before submission.</p>'}
            </article>
        `;
    };

    const renderItems = () => {
        tableBody.innerHTML = items.map((item) => `
            <tr>
                <td class="min-w-72">
                    <strong class="text-sm font-bold text-slate-950">${escapeHtml(item.description)}</strong>
                    <span class="${noteClass}">${escapeHtml(item.justification)}</span>
                </td>
                <td class="whitespace-nowrap">${escapeHtml(item.quantity)} ${escapeHtml(item.unit)}</td>
                <td class="whitespace-nowrap">${money(item.estimatedUnitCost)}</td>
                <td class="whitespace-nowrap font-bold text-slate-950">${money(item.totalCost)}</td>
                <td class="whitespace-nowrap">${escapeHtml(item.targetQuarter)}</td>
                <td>${scopeMarkup(item.scoping)}</td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        <button class="${tableActionClass}" type="button" data-action="scope" data-id="${escapeHtml(item.id)}" title="Run market scoping" aria-label="Run market scoping">
                            <i data-lucide="sparkles" aria-hidden="true"></i>
                        </button>
                        <button class="${tableActionClass}" type="button" data-action="edit" data-id="${escapeHtml(item.id)}" title="Edit item" aria-label="Edit item">
                            <i data-lucide="pencil" aria-hidden="true"></i>
                        </button>
                        <button class="${tableActionDangerClass}" type="button" data-action="delete" data-id="${escapeHtml(item.id)}" title="Delete item" aria-label="Delete item">
                            <i data-lucide="trash-2" aria-hidden="true"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        if (itemCount) {
            itemCount.textContent = items.length;
        }

        const total = items.reduce((sum, item) => sum + (Number(item.totalCost) || 0), 0);
        const references = items.reduce((sum, item) => sum + (item.scoping?.length ?? 0), 0);
        const missing = items.filter((item) => !(item.scoping?.length)).length;
        const isReady = items.length > 0 && missing === 0;

        if (summaryItems) {
            summaryItems.textContent = items.length;
        }

        if (summaryTotal) {
            summaryTotal.textContent = money(total);
        }

        if (summaryReferences) {
            summaryReferences.textContent = references;
        }

        if (summaryMissing) {
            summaryMissing.textContent = missing;
        }

        if (readyBadge) {
            readyBadge.textContent = isReady ? 'Ready for Review' : 'Draft';
            readyBadge.className = `${badgeBaseClass} ${isReady ? 'bg-green-50 text-green-700 ring-green-200' : 'bg-slate-100 text-slate-700 ring-slate-200'}`;
        }

        if (referenceTotalBadge) {
            referenceTotalBadge.textContent = `${references} total`;
        }

        if (referenceList) {
            referenceList.innerHTML = items.length
                ? items.map(referenceCardMarkup).join('')
                : '<p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm font-semibold text-slate-500">No encoded items yet.</p>';
        }

        refreshIcons();
    };

    const clearForm = () => {
        fields.id.value = '';
        fields.description.value = '';
        fields.unit.value = '';
        fields.quantity.value = '1';
        fields.unitCost.value = '0';
        fields.quarter.value = 'Q1';
        fields.justification.value = '';
        saveButton.innerHTML = '<i data-lucide="plus" aria-hidden="true"></i>Add Item';
        refreshIcons();
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const quantity = Number(fields.quantity.value) || 0;
        const estimatedUnitCost = Number(fields.unitCost.value) || 0;
        const item = {
            id: fields.id.value || `item-${Date.now()}`,
            description: fields.description.value.trim(),
            unit: fields.unit.value.trim(),
            quantity,
            estimatedUnitCost,
            totalCost: quantity * estimatedUnitCost,
            justification: fields.justification.value.trim(),
            targetQuarter: fields.quarter.value,
            scoping: fields.id.value
                ? (items.find((existingItem) => existingItem.id === fields.id.value)?.scoping ?? [])
                : [],
        };

        if (!item.description || !item.unit || !item.justification) {
            showToast('Complete the item description, unit, and justification before saving.');
            return;
        }

        if (fields.id.value) {
            items = items.map((existingItem) => existingItem.id === item.id ? item : existingItem);
            showToast('Item row updated.');
        } else {
            items = [...items, item];
            showToast('Item row added.');
        }

        clearForm();
        renderItems();
    });

    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');

        if (!button) {
            return;
        }

        const item = items.find((candidate) => candidate.id === button.dataset.id);

        if (!item) {
            return;
        }

        if (button.dataset.action === 'edit') {
            fields.id.value = item.id;
            fields.description.value = item.description;
            fields.unit.value = item.unit;
            fields.quantity.value = item.quantity;
            fields.unitCost.value = item.estimatedUnitCost;
            fields.quarter.value = item.targetQuarter;
            fields.justification.value = item.justification;
            saveButton.innerHTML = '<i data-lucide="save" aria-hidden="true"></i>Update Item';
            fields.description.focus();
            refreshIcons();
        }

        if (button.dataset.action === 'delete') {
            items = items.filter((candidate) => candidate.id !== item.id);
            renderItems();
            showToast('Item row deleted.');
        }

        if (button.dataset.action === 'scope') {
            items = items.map((candidate) => candidate.id === item.id
                ? { ...candidate, scoping: marketScopeFor(candidate) }
                : candidate);
            renderItems();
            showToast('Market scoping results refreshed for this item.');
        }
    });

    clearButton?.addEventListener('click', clearForm);

    scopeButton?.addEventListener('click', () => {
        if (!items.length) {
            showToast('Add at least one item before running market scoping.');
            return;
        }

        items = items.map((item) => ({
            ...item,
            scoping: marketScopeFor(item),
        }));
        renderItems();
        showToast('Market scoping results refreshed for all encoded items.');
    });

    submitButton?.addEventListener('click', () => {
        if (!items.length) {
            showToast('Add at least one item before submitting the proposal.');
            return;
        }

        showToast('Proposal submitted for Finance Review.');
    });

    renderItems();
};

const initProposalTimeline = () => {
    const rows = [...document.querySelectorAll('[data-proposal-row]')];
    const statusFilter = document.getElementById('proposalStatusFilter');
    const yearFilter = document.getElementById('proposalYearFilter');
    const visibleCount = document.getElementById('proposalVisibleCount');
    const title = document.getElementById('timelineTitle');
    const content = document.getElementById('timelineContent');
    const meta = document.getElementById('timelineMeta');
    const statusBadge = document.getElementById('timelineStatusBadge');
    const amount = document.getElementById('timelineAmount');
    const action = document.getElementById('timelineAction');

    if (!rows.length || !statusFilter || !yearFilter || !title || !content) {
        return;
    }

    const proposals = readJson('proposalData');
    const proposalMap = new Map(proposals.map((proposal) => [proposal.id, proposal]));
    let selectedProposalId = null;

    const nextActionFor = (proposal) => {
        if (proposal.status === 'Returned') {
            return 'Revise proposal';
        }

        if (proposal.status === 'Approved') {
            return 'Prepare PR';
        }

        if (proposal.status === 'Endorsed') {
            return 'Await approval';
        }

        if (proposal.status === 'Under Review') {
            return 'Monitor review';
        }

        if (proposal.status === 'Submitted') {
            return 'Await Finance';
        }

        return 'Continue draft';
    };

    const resetTimeline = () => {
        selectedProposalId = null;
        rows.forEach((row) => toggleClasses(row, selectedRowClasses, false));
        title.textContent = 'Select a proposal';

        if (meta) {
            meta.textContent = 'Timeline details will appear here.';
        }

        if (statusBadge) {
            statusBadge.textContent = 'Status';
            statusBadge.className = badgeClass('Draft');
        }

        if (amount) {
            amount.textContent = 'PHP 0';
        }

        if (action) {
            action.textContent = 'Select';
            action.className = 'mt-1 text-sm font-bold text-slate-950';
        }

        content.className = timelineEmptyClass;
        content.innerHTML = '<i data-lucide="mouse-pointer-click" aria-hidden="true"></i><p>Select a proposal to view timestamps, remarks, and revision status.</p>';
        refreshIcons();
    };

    const renderTimeline = (proposalId) => {
        const proposal = proposalMap.get(proposalId);

        if (!proposal) {
            return;
        }

        selectedProposalId = proposalId;
        rows.forEach((row) => toggleClasses(row, selectedRowClasses, row.dataset.proposalId === proposalId));
        title.textContent = proposal.title;
        content.className = 'grid gap-4';

        if (meta) {
            meta.textContent = `FY ${proposal.fiscalYear} · Submitted ${proposal.dateSubmitted}`;
        }

        if (statusBadge) {
            statusBadge.textContent = proposal.status;
            statusBadge.className = badgeClass(proposal.status);
        }

        if (amount) {
            amount.textContent = money(proposal.totalAmount);
        }

        if (action) {
            action.textContent = nextActionFor(proposal);
            action.className = `mt-1 text-sm font-bold ${proposal.status === 'Returned' ? 'text-red-700' : 'text-slate-950'}`;
        }

        content.innerHTML = `
            <ol class="${timelineClass}">
                ${proposal.timeline.map((event) => `
                    <li>
                        <strong>${escapeHtml(event.step)}</strong>
                        <span>${escapeHtml(event.timestamp)}</span>
                        <p>${escapeHtml(event.remarks)}</p>
                    </li>
                `).join('')}
            </ol>
            ${proposal.status === 'Returned' ? `
                <div class="${returnedBoxClass}">
                    <p><strong>Returned remarks:</strong> ${escapeHtml(proposal.returnedRemarks)}</p>
                    <a class="${primaryButtonClass}" href="/office-head/budget-proposal">
                        <i data-lucide="refresh-cw" aria-hidden="true"></i>
                        Revise Proposal
                    </a>
                </div>
            ` : ''}
        `;
        refreshIcons();
    };

    const applyFilters = () => {
        const selectedStatus = statusFilter.value;
        const selectedYear = yearFilter.value;
        let count = 0;

        rows.forEach((row) => {
            const matchesStatus = selectedStatus === 'all' || row.dataset.status === selectedStatus;
            const matchesYear = selectedYear === 'all' || row.dataset.year === selectedYear;
            const isVisible = matchesStatus && matchesYear;

            row.hidden = !isVisible;
            count += isVisible ? 1 : 0;
        });

        visibleCount.textContent = `${count} shown`;

        const selectedRow = rows.find((row) => row.dataset.proposalId === selectedProposalId);
        const firstVisibleRow = rows.find((row) => !row.hidden);

        if (selectedRow?.hidden) {
            resetTimeline();
        }

        if (!selectedProposalId && firstVisibleRow) {
            renderTimeline(firstVisibleRow.dataset.proposalId);
        }

        if (!firstVisibleRow) {
            resetTimeline();
        }
    };

    rows.forEach((row) => {
        row.addEventListener('click', () => renderTimeline(row.dataset.proposalId));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                renderTimeline(row.dataset.proposalId);
            }
        });
    });

    statusFilter.addEventListener('change', applyFilters);
    yearFilter.addEventListener('change', applyFilters);
    applyFilters();
};

const initPurchaseRequests = () => {
    const uploads = [...document.querySelectorAll('[data-pr-upload]')];

    if (!uploads.length) {
        return;
    }

    const purchaseItems = readJson('purchaseRequestData');
    const purchaseMap = new Map(purchaseItems.map((item) => [item.id, item]));

    uploads.forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            const itemId = input.dataset.prUpload;
            const item = purchaseMap.get(itemId);

            if (!file || !item) {
                return;
            }

            if (!file.name.toLowerCase().endsWith('.pdf')) {
                input.value = '';
                showToast('Upload a signed PR PDF file.');
                return;
            }

            const label = document.querySelector(`[data-upload-label="${itemId}"]`);
            const ocrRow = document.querySelector(`[data-ocr-row="${itemId}"]`);
            const fileTitle = document.querySelector(`[data-ocr-file="${itemId}"]`);
            const prNumber = document.querySelector(`[data-ocr-pr-number="${itemId}"]`);
            const date = document.querySelector(`[data-ocr-date="${itemId}"]`);
            const items = document.querySelector(`[data-ocr-items="${itemId}"]`);
            const amount = document.querySelector(`[data-ocr-amount="${itemId}"]`);

            if (label) {
                label.textContent = file.name;
            }

            if (ocrRow) {
                ocrRow.hidden = false;
            }

            if (fileTitle) {
                fileTitle.textContent = file.name;
            }

            if (prNumber) {
                prNumber.textContent = item.ocr.prNumber;
            }

            if (date) {
                date.textContent = item.ocr.date;
            }

            if (items) {
                items.textContent = item.ocr.items;
            }

            if (amount) {
                amount.textContent = money(item.ocr.amount);
            }

            showToast('OCR details extracted from the uploaded PR.');
        });
    });
};

const initFinanceProposalReview = () => {
    const selector = document.getElementById('financeProposalSelector');
    const buttons = [...document.querySelectorAll('[data-finance-review-action]')];

    selector?.addEventListener('change', () => {
        window.location.href = selector.value;
    });

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const remarks = document.getElementById('financeOverallRemarks')?.value.trim();

            if (button.dataset.financeReviewAction === 'return' && !remarks) {
                showToast('Add overall remarks before returning the proposal.');
                return;
            }

            showToast(button.dataset.financeReviewAction === 'endorse'
                ? 'Proposal endorsed for Chancellor approval.'
                : 'Proposal returned to the office with remarks.');
        });
    });
};

const filterRows = ({ rows, filters, countElement }) => {
    let count = 0;

    rows.forEach((row) => {
        const visible = filters.every(({ key, value }) => value === 'all' || row.dataset[key] === value);
        row.hidden = !visible;
        count += visible ? 1 : 0;
    });

    if (countElement) {
        countElement.textContent = `${count} shown`;
    }
};

const initAnnualProcurementPlan = () => {
    const rows = [...document.querySelectorAll('[data-app-row]')];
    const officeFilter = document.getElementById('appOfficeFilter');
    const quarterFilter = document.getElementById('appQuarterFilter');
    const modeFilter = document.getElementById('appModeFilter');
    const count = document.getElementById('appVisibleCount');
    const printButton = document.getElementById('printAppButton');

    if (!rows.length || !officeFilter || !quarterFilter || !modeFilter) {
        return;
    }

    const apply = () => filterRows({
        rows,
        countElement: count,
        filters: [
            { key: 'office', value: officeFilter.value },
            { key: 'quarter', value: quarterFilter.value },
            { key: 'mode', value: modeFilter.value },
        ],
    });

    officeFilter.addEventListener('change', apply);
    quarterFilter.addEventListener('change', apply);
    modeFilter.addEventListener('change', apply);
    printButton?.addEventListener('click', () => window.print());
};

const initBudgetUtilizationReport = () => {
    const rows = [...document.querySelectorAll('[data-util-row]')];
    const quarterFilter = document.getElementById('utilQuarterFilter');
    const officeFilter = document.getElementById('utilOfficeFilter');
    const count = document.getElementById('utilVisibleCount');

    if (!rows.length || !quarterFilter || !officeFilter) {
        return;
    }

    const apply = () => filterRows({
        rows,
        countElement: count,
        filters: [
            { key: 'quarter', value: quarterFilter.value },
            { key: 'office', value: officeFilter.value },
        ],
    });

    quarterFilter.addEventListener('change', apply);
    officeFilter.addEventListener('change', apply);
};

const initProcurementDashboard = () => {
    const button = document.getElementById('dueThisMonthButton');
    const rows = [...document.querySelectorAll('[data-urgent-pr-row]')];
    const count = document.getElementById('urgentPrVisibleCount');

    if (!button || !rows.length) {
        return;
    }

    let active = false;

    const render = () => {
        let visibleCount = 0;

        rows.forEach((row) => {
            const visible = !active || row.dataset.dueMonth === 'yes';
            row.hidden = !visible;
            visibleCount += visible ? 1 : 0;
        });

        count.textContent = `${visibleCount} shown`;
        button.classList.toggle('is-active', active);
        button.innerHTML = active
            ? '<i data-lucide="calendar-days" aria-hidden="true"></i>Show All Urgent PRs'
            : '<i data-lucide="calendar-days" aria-hidden="true"></i>PRs Due This Month';
        refreshIcons();
    };

    button.addEventListener('click', () => {
        active = !active;
        render();
    });
};

const initProcurementRequestManagement = () => {
    const rows = [...document.querySelectorAll('[data-procurement-pr-row]')];
    const title = document.getElementById('procurementPrTitle');
    const detail = document.getElementById('procurementPrDetails');

    if (!rows.length || !title || !detail) {
        return;
    }

    const requests = readJson('procurementRequestData');
    const requestMap = new Map(requests.map((request) => [request.id, request]));
    const statuses = ['Pending', 'In Progress', 'Completed', 'Delayed'];

    const renderActivityLog = (request) => `
        <ol class="${timelineClass}">
            ${request.activityLog.map((entry) => `
                <li>
                    <strong>${escapeHtml(entry.status)}</strong>
                    <span>${escapeHtml(entry.timestamp)}</span>
                    <p>${escapeHtml(entry.remarks)}</p>
                </li>
            `).join('')}
        </ol>
    `;

    const updateRowStatus = (request) => {
        const pill = document.querySelector(`[data-pr-row-status="${request.id}"]`);

        if (!pill) {
            return;
        }

        pill.className = badgeClass(request.currentStatus);
        pill.textContent = request.currentStatus;
    };

    const renderDetails = (requestId) => {
        const request = requestMap.get(requestId);

        if (!request) {
            return;
        }

        rows.forEach((row) => toggleClasses(row, selectedRowClasses, row.dataset.prId === requestId));
        title.textContent = `${request.prNumber} - ${request.office}`;
        detail.className = detailPanelClass;
        detail.innerHTML = `
            <div class="${pdfPreviewClass}">
                <i data-lucide="file-text" aria-hidden="true"></i>
                <div>
                    <span>PDF preview</span>
                    <strong>${escapeHtml(request.pdfFile)}</strong>
                    <small>Signed purchase request document preview</small>
                </div>
            </div>

            <div class="${ocrPanelClass}">
                <div>
                    <p class="${eyebrowClass}">OCR extracted fields</p>
                    <h3>${escapeHtml(request.ocr.prNumber)}</h3>
                </div>
                <dl>
                    <div>
                        <dt>Date</dt>
                        <dd>${escapeHtml(request.ocr.date)}</dd>
                    </div>
                    <div>
                        <dt>Office</dt>
                        <dd>${escapeHtml(request.ocr.requestingOffice)}</dd>
                    </div>
                    <div>
                        <dt>Items</dt>
                        <dd>${escapeHtml(request.ocr.items)}</dd>
                    </div>
                    <div>
                        <dt>Amount</dt>
                        <dd>${money(request.ocr.amount)}</dd>
                    </div>
                </dl>
            </div>

            <div class="${formGridClass}">
                <label>
                    <span>Status update</span>
                    <select id="procurementPrStatusSelect">
                        ${statuses.map((status) => `<option value="${status}" ${status === request.currentStatus ? 'selected' : ''}>${status}</option>`).join('')}
                    </select>
                </label>
                <label class="${wideFieldClass}">
                    <span>Remarks</span>
                    <textarea id="procurementPrRemarks" rows="3">${escapeHtml(request.remarks)}</textarea>
                </label>
                <div class="${formActionsClass}">
                    <button class="${primaryButtonClass}" type="button" id="saveProcurementPrUpdate">
                        <i data-lucide="save" aria-hidden="true"></i>
                        Save Status Update
                    </button>
                </div>
            </div>

            <div>
                <div class="${panelHeaderClass}">
                    <div>
                        <p class="${eyebrowClass}">Activity log</p>
                        <h2>Status Changes and Remarks</h2>
                    </div>
                </div>
                <div id="procurementActivityLog">${renderActivityLog(request)}</div>
            </div>
        `;

        document.getElementById('saveProcurementPrUpdate')?.addEventListener('click', () => {
            const status = document.getElementById('procurementPrStatusSelect').value;
            const remarks = document.getElementById('procurementPrRemarks').value.trim();
            const timestamp = new Intl.DateTimeFormat('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            }).format(new Date());

            request.currentStatus = status;
            request.remarks = remarks || 'Status updated without additional remarks.';
            request.activityLog = [
                { timestamp, status, remarks: request.remarks },
                ...request.activityLog,
            ];
            updateRowStatus(request);
            document.getElementById('procurementActivityLog').innerHTML = renderActivityLog(request);
            refreshIcons();
            showToast('PR status and activity log updated.');
        });

        refreshIcons();
    };

    rows.forEach((row) => {
        row.addEventListener('click', () => renderDetails(row.dataset.prId));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                renderDetails(row.dataset.prId);
            }
        });
    });

    renderDetails(rows[0].dataset.prId);
};

const initProcurementStatusTracking = () => {
    const rows = [...document.querySelectorAll('[data-procurement-track-row]')];
    const officeFilter = document.getElementById('procurementTrackOfficeFilter');
    const quarterFilter = document.getElementById('procurementTrackQuarterFilter');
    const statusFilter = document.getElementById('procurementTrackStatusFilter');
    const count = document.getElementById('procurementTrackVisibleCount');

    if (!rows.length || !officeFilter || !quarterFilter || !statusFilter) {
        return;
    }

    const apply = () => filterRows({
        rows,
        countElement: count,
        filters: [
            { key: 'office', value: officeFilter.value },
            { key: 'quarter', value: quarterFilter.value },
            { key: 'status', value: statusFilter.value },
        ],
    });

    rows.forEach((row) => {
        row.querySelector('[data-track-update]')?.addEventListener('click', () => {
            const status = row.querySelector('[data-track-status-select]').value;
            const remarks = row.querySelector('[data-track-remarks-input]').value.trim();
            const pill = row.querySelector('[data-track-status-pill]');
            const remarksDisplay = row.querySelector('[data-track-remarks-display]');

            row.dataset.status = status;
            toggleClasses(row, delayedRowClasses, status === 'Delayed');
            pill.className = badgeClass(status);
            pill.textContent = status;
            remarksDisplay.textContent = remarks || 'No remarks entered.';
            apply();
            showToast('Procurement item status updated.');
        });
    });

    officeFilter.addEventListener('change', apply);
    quarterFilter.addEventListener('change', apply);
    statusFilter.addEventListener('change', apply);
};

const initProcurementReports = () => {
    document.getElementById('printProcurementReportButton')?.addEventListener('click', () => window.print());
};

const initChancellorBudgetApproval = () => {
    const rows = [...document.querySelectorAll('[data-chancellor-proposal-row]')];
    const title = document.getElementById('chancellorProposalTitle');
    const detail = document.getElementById('chancellorProposalDetails');

    if (!rows.length || !title || !detail) {
        return;
    }

    const proposals = readJson('chancellorProposalData');
    const proposalMap = new Map(proposals.map((proposal) => [proposal.id, proposal]));

    const trailMarkup = (proposal) => `
        <ol class="${timelineClass}">
            <li>
                <strong>Finance Endorsement</strong>
                <span>${escapeHtml(proposal.financeEndorsementTimestamp)}</span>
                <p>${escapeHtml(proposal.financeRemarks)}</p>
            </li>
            <li>
                <strong>Chancellor Approval</strong>
                <span>${escapeHtml(proposal.chancellorApprovalTimestamp)}</span>
                <p>${proposal.status === 'Approved'
                    ? 'Approved by Chancellor.'
                    : proposal.status === 'Returned'
                        ? escapeHtml(proposal.chancellorReturnRemarks)
                        : 'Pending Chancellor action.'}</p>
            </li>
        </ol>
    `;

    const scopeMarkup = (item) => `
        <div class="${scopeListClass}">
            ${item.scoping.map((scope) => `
                <div class="${scopeItemClass}">
                    <strong>${escapeHtml(scope.supplierName)}</strong>
                    <span>${money(scope.price)}</span>
                    <a href="${escapeHtml(scope.sourceLink)}" target="_blank" rel="noreferrer">Source link</a>
                    <span>${escapeHtml(scope.dateRetrieved)}</span>
                </div>
            `).join('')}
        </div>
    `;

    const renderDetails = (proposalId) => {
        const proposal = proposalMap.get(proposalId);

        if (!proposal) {
            return;
        }

        rows.forEach((row) => toggleClasses(row, selectedRowClasses, row.dataset.proposalId === proposalId));
        title.textContent = proposal.title;
        detail.className = detailPanelClass;
        detail.innerHTML = `
            <dl class="${detailGridClass}">
                <div>
                    <dt>Office</dt>
                    <dd>${escapeHtml(proposal.office)}</dd>
                </div>
                <div>
                    <dt>Total amount</dt>
                    <dd>${money(proposal.totalAmount)}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd><span class="${badgeClass(proposal.status)}">${escapeHtml(proposal.status)}</span></dd>
                </div>
            </dl>

            <div class="${approvalItemsClass}">
                ${proposal.items.map((item) => `
                    <article class="${approvalItemClass}">
                        <div>
                            <strong>${escapeHtml(item.description)}</strong>
                            <span>${escapeHtml(item.quantity)} ${escapeHtml(item.unit)} - ${money(item.cost)}</span>
                            <p>${escapeHtml(item.justification)}</p>
                        </div>
                        ${scopeMarkup(item)}
                    </article>
                `).join('')}
            </div>

            <div class="${returnedBoxClass}">
                <p><strong>Finance remarks:</strong> ${escapeHtml(proposal.financeRemarks)}</p>
            </div>

            <label class="block [&>span]:mb-1.5 [&>span]:block [&>span]:text-sm [&>span]:font-bold [&>span]:text-slate-700 [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:px-4 [&_textarea]:py-3 [&_textarea]:text-base [&_textarea:focus]:border-bsu-maroon [&_textarea:focus]:outline-none [&_textarea:focus]:ring-2 [&_textarea:focus]:ring-bsu-gold/40">
                <span>Chancellor remarks</span>
                <textarea id="chancellorReturnRemarks" rows="4" placeholder="Required when returning the proposal"></textarea>
            </label>

            <div class="${reviewActionsClass}">
                <button class="${primaryButtonClass}" type="button" id="approveChancellorProposal">
                    <i data-lucide="check-circle-2" aria-hidden="true"></i>
                    Approve
                </button>
                <button class="${secondaryButtonClass}" type="button" id="returnChancellorProposal">
                    <i data-lucide="undo-2" aria-hidden="true"></i>
                    Return with Remarks
                </button>
            </div>

            <div>
                <div class="${panelHeaderClass}">
                    <div>
                        <p class="${eyebrowClass}">Approval trail</p>
                        <h2>Finance and Chancellor Actions</h2>
                    </div>
                </div>
                <div id="chancellorApprovalTrail">${trailMarkup(proposal)}</div>
            </div>
        `;

        document.getElementById('approveChancellorProposal')?.addEventListener('click', () => {
            proposal.status = 'Approved';
            proposal.chancellorApprovalTimestamp = new Intl.DateTimeFormat('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            }).format(new Date());
            proposal.chancellorReturnRemarks = null;
            renderDetails(proposal.id);
            showToast('Proposal approved by the Chancellor.');
        });

        document.getElementById('returnChancellorProposal')?.addEventListener('click', () => {
            const remarks = document.getElementById('chancellorReturnRemarks').value.trim();

            if (!remarks) {
                showToast('Add remarks before returning the proposal.');
                return;
            }

            proposal.status = 'Returned';
            proposal.chancellorApprovalTimestamp = new Intl.DateTimeFormat('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            }).format(new Date());
            proposal.chancellorReturnRemarks = remarks;
            renderDetails(proposal.id);
            showToast('Proposal returned with Chancellor remarks.');
        });

        refreshIcons();
    };

    rows.forEach((row) => {
        row.addEventListener('click', () => renderDetails(row.dataset.proposalId));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                renderDetails(row.dataset.proposalId);
            }
        });
    });

    renderDetails(rows[0].dataset.proposalId);
};

const initChancellorReports = () => {
    document.getElementById('printChancellorReportButton')?.addEventListener('click', () => window.print());
};

const initViceChancellorStatus = () => {
    const rows = [...document.querySelectorAll('[data-vice-status-row]')];
    const officeFilter = document.getElementById('viceStatusOfficeFilter');
    const quarterFilter = document.getElementById('viceStatusQuarterFilter');
    const statusFilter = document.getElementById('viceStatusStatusFilter');
    const count = document.getElementById('viceStatusVisibleCount');
    const title = document.getElementById('viceStatusTitle');
    const detail = document.getElementById('viceStatusDetails');

    if (!rows.length || !officeFilter || !quarterFilter || !statusFilter || !title || !detail) {
        return;
    }

    const items = readJson('viceDivisionItemData');
    const itemMap = new Map(items.map((item) => [item.id, item]));

    const renderTimeline = (item) => `
        <ol class="${timelineClass}">
            ${item.timeline.map((entry) => `
                <li>
                    <strong>${escapeHtml(entry.status)}</strong>
                    <span>${escapeHtml(entry.timestamp)}</span>
                    <p>${escapeHtml(entry.remarks)}</p>
                </li>
            `).join('')}
        </ol>
    `;

    const renderDetails = (itemId) => {
        const item = itemMap.get(itemId);

        if (!item) {
            return;
        }

        rows.forEach((row) => toggleClasses(row, selectedRowClasses, row.dataset.itemId === itemId));
        title.textContent = `${item.office} - ${item.item}`;
        detail.className = detailPanelClass;
        detail.innerHTML = `
            <dl class="${detailGridClass}">
                <div>
                    <dt>Target quarter</dt>
                    <dd>${escapeHtml(item.targetQuarter)}</dd>
                </div>
                <div>
                    <dt>Current status</dt>
                    <dd><span class="${badgeClass(item.currentStatus)}">${escapeHtml(item.currentStatus)}</span></dd>
                </div>
                <div>
                    <dt>Office</dt>
                    <dd>${escapeHtml(item.office)}</dd>
                </div>
            </dl>

            <div class="${returnedBoxClass}">
                <p><strong>Procurement Office remarks:</strong> ${escapeHtml(item.procurementRemarks)}</p>
            </div>

            <div>
                <div class="${panelHeaderClass}">
                    <div>
                        <p class="${eyebrowClass}">Procurement activity timeline</p>
                        <h2>Status History</h2>
                    </div>
                </div>
                ${renderTimeline(item)}
            </div>

            <label class="block [&>span]:mb-1.5 [&>span]:block [&>span]:text-sm [&>span]:font-bold [&>span]:text-slate-700 [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:px-4 [&_textarea]:py-3 [&_textarea]:text-base [&_textarea:focus]:border-bsu-maroon [&_textarea:focus]:outline-none [&_textarea:focus]:ring-2 [&_textarea:focus]:ring-bsu-gold/40">
                <span>Vice Chancellor follow-up notes</span>
                <textarea id="viceFollowUpNotes" rows="4" placeholder="Add follow-up notes for this office">${escapeHtml(item.followUpNotes ?? '')}</textarea>
            </label>
            <button class="${primaryButtonClass}" type="button" id="saveViceFollowUpNotes">
                <i data-lucide="save" aria-hidden="true"></i>
                Save Follow-up Notes
            </button>
        `;

        document.getElementById('saveViceFollowUpNotes')?.addEventListener('click', () => {
            item.followUpNotes = document.getElementById('viceFollowUpNotes').value.trim();
            showToast('Vice Chancellor follow-up notes saved.');
        });

        refreshIcons();
    };

    const apply = () => filterRows({
        rows,
        countElement: count,
        filters: [
            { key: 'office', value: officeFilter.value },
            { key: 'quarter', value: quarterFilter.value },
            { key: 'status', value: statusFilter.value },
        ],
    });

    rows.forEach((row) => {
        row.addEventListener('click', () => renderDetails(row.dataset.itemId));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                renderDetails(row.dataset.itemId);
            }
        });
    });

    officeFilter.addEventListener('change', apply);
    quarterFilter.addEventListener('change', apply);
    statusFilter.addEventListener('change', apply);
    renderDetails(rows[0].dataset.itemId);
};

const initViceChancellorReports = () => {
    document.getElementById('printViceReportButton')?.addEventListener('click', () => window.print());
};

document.addEventListener('DOMContentLoaded', () => {
    refreshIcons();
    initBudgetProposal();
    initProposalTimeline();
    initPurchaseRequests();
    initFinanceProposalReview();
    initAnnualProcurementPlan();
    initBudgetUtilizationReport();
    initProcurementDashboard();
    initProcurementRequestManagement();
    initProcurementStatusTracking();
    initProcurementReports();
    initChancellorBudgetApproval();
    initChancellorReports();
    initViceChancellorStatus();
    initViceChancellorReports();
});
