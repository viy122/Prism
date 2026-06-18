@extends('prism.layouts.app')
@section('title', 'Purchase Request Management | Procurement Office')

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
        --m: var(--crimson); --m-dk: var(--crimson-dark); --gold: #c9a84c; --white: #ffffff;
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .pr-grid { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 20px; align-items: start; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 62vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; cursor: pointer; }
    tbody tr:hover { background: var(--crimson-mid); }
    tbody tr.selected { background: rgba(139,26,28,.07); }
    tbody tr.selected td:first-child { border-left: 3px solid var(--m); }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-completed   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-in-progress { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-pending     { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-delayed     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    /* Detail panel */
    .detail-panel { display: flex; flex-direction: column; gap: 16px; }
    .detail-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 220px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); text-align: center; padding: 32px; }
    .detail-empty i { font-size: 36px; color: var(--s300); }
    .detail-empty p { font-size: 13px; color: var(--s400); line-height: 1.6; max-width: 220px; }

    .detail-content { display: none; flex-direction: column; gap: 16px; }
    .detail-content.visible { display: flex; }

    /* PDF preview */
    .pdf-preview { border-radius: 12px; border: 1px solid var(--s200); background: var(--s50); overflow: hidden; aspect-ratio: 8.5 / 11; display: flex; align-items: center; justify-content: center; position: relative; }
    .pdf-preview iframe { width: 100%; height: 100%; border: none; }
    .pdf-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 100%; color: var(--s400); }
    .pdf-placeholder i { font-size: 42px; color: var(--s300); }
    .pdf-placeholder span { font-size: 12px; font-weight: 600; }

    /* Detail fields */
    .detail-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .detail-field { background: var(--s50); border: 1px solid var(--s200); border-radius: 10px; padding: 10px 14px; }
    .detail-field.full { grid-column: 1 / -1; }
    .detail-field label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s400); display: block; margin-bottom: 3px; }
    .detail-field span { font-size: 13px; font-weight: 600; color: var(--s700); }

    /* Status control */
    .status-control { display: flex; flex-direction: column; gap: 8px; }
    .status-control label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s500); }
    .status-select { width: 100%; height: 42px; padding: 0 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; outline: none; transition: border-color .2s; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }
    .status-select:focus { border-color: var(--m); }

    .remarks-textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 13px; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 80px; outline: none; transition: border-color .2s; line-height: 1.6; box-sizing: border-box; }
    .remarks-textarea:focus { border-color: var(--m); }
    .remarks-textarea::placeholder { color: var(--s300); }

    .btn-save { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 22px; border-radius: 10px; background: var(--m); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: background .2s, opacity .2s; white-space: nowrap; width: 100%; }
    .btn-save:hover:not(:disabled) { background: var(--m-dk); }
    .btn-save:disabled { opacity: .6; cursor: not-allowed; }
    .btn-save i { font-size: 16px; }

    /* Activity log */
    .activity-log { display: flex; flex-direction: column; gap: 1px; }
    .activity-item { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--s100); }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); flex-shrink: 0; margin-top: 5px; }
    .activity-item p { font-size: 12.5px; color: var(--s600); line-height: 1.6; }
    .activity-item time { font-size: 11px; color: var(--s400); display: block; margin-top: 2px; }

    /* Toast */
    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 1200px) { .pr-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-file-invoice"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Purchase Request Management</h1>
            <p class="page-hdr-sub">Review uploaded PRs, update status, add remarks, and track activity history.</p>
        </div>
    </div>

    <div class="pr-grid">

        {{-- ── Left: PR list ── --}}
        <div class="card" style="padding-bottom: 22px;">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Uploaded PRs</p>
                    <h2 class="card-title">Purchase Request Queue</h2>
                </div>
                <span class="count-chip">{{ count($purchaseRequests) }} PR{{ count($purchaseRequests) !== 1 ? 's' : '' }}</span>
            </div>

            @if(count($purchaseRequests) === 0)
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:180px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:32px;text-align:center;">
                    <i class="ti ti-inbox" style="font-size:38px;color:var(--s300);"></i>
                    <p style="font-size:13px;color:var(--s400);max-width:240px;line-height:1.6;">No purchase requests have been uploaded yet.</p>
                </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>PR No.</th>
                            <th>Description</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseRequests as $pr)
                            @php
                                $slug = match(strtolower($pr['currentStatus'])) {
                                    'completed'   => 'badge-completed',
                                    'in progress' => 'badge-in-progress',
                                    'pending'     => 'badge-pending',
                                    default       => 'badge-delayed',
                                };
                            @endphp
                            <tr data-pr-row data-pr-id="{{ $pr['id'] }}" tabindex="0">
                                <td style="font-size:12px;font-weight:600;color:var(--s600);white-space:nowrap;max-width:120px;overflow:hidden;text-overflow:ellipsis;">{{ $pr['office'] }}</td>
                                <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $pr['prNumber'] }}</td>
                                <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $pr['item'] }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $pr['dateSubmitted'] }}</td>
                                <td><span class="badge {{ $slug }}" data-status-badge="{{ $pr['id'] }}">{{ $pr['currentStatus'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ── Right: Detail panel ── --}}
        <div class="card detail-panel">
            <div class="card-head" style="margin-bottom: 14px;">
                <div>
                    <p class="card-eyebrow">PR Details</p>
                    <h2 class="card-title" id="detailPrNumber">Select a PR</h2>
                </div>
                <span class="count-chip" id="detailPrOffice" style="display:none;"></span>
            </div>

            {{-- Empty state --}}
            <div class="detail-empty" id="detailEmpty">
                <i class="ti ti-arrow-left"></i>
                <p>Select a PR from the list to view details, update status, and track activity.</p>
            </div>

            {{-- Loaded state --}}
            <div class="detail-content" id="detailContent">

                {{-- PDF preview --}}
                <div class="pdf-preview" id="pdfPreview">
                    <div class="pdf-placeholder">
                        <i class="ti ti-file-off"></i>
                        <span>No PDF attached</span>
                    </div>
                </div>

                {{-- Extracted fields --}}
                <div class="detail-fields">
                    <div class="detail-field"><label>Office</label><span id="fOffice">—</span></div>
                    <div class="detail-field"><label>PR Number</label><span id="fPrNumber">—</span></div>
                    <div class="detail-field full"><label>Description / Item</label><span id="fItem">—</span></div>
                    <div class="detail-field"><label>Date Submitted</label><span id="fDate">—</span></div>
                    <div class="detail-field"><label>Current Status</label><span id="fStatus">—</span></div>
                    <div class="detail-field full"><label>Remarks on File</label><span id="fRemarks" style="white-space:pre-line;">—</span></div>
                </div>

                {{-- Update controls --}}
                <div class="status-control">
                    <label>Update Status</label>
                    <select class="status-select" id="statusSelect">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>

                <div class="status-control">
                    <label>Remarks</label>
                    <textarea class="remarks-textarea" id="remarksInput" placeholder="Add a remark or note about this update…"></textarea>
                </div>

                <button class="btn-save" id="btnSave" type="button">
                    <i class="ti ti-device-floppy"></i>
                    Save Changes
                </button>

                {{-- Activity log --}}
                <div>
                    <div class="card-head" style="margin-bottom:10px;">
                        <div>
                            <p class="card-eyebrow">History</p>
                            <h3 class="card-title" style="font-size:14px;">Activity Log</h3>
                        </div>
                    </div>
                    <div class="activity-log" id="activityLog"></div>
                </div>

            </div>
        </div>

    </div>

</div>

{{-- Toast --}}
<div class="pr-toast" id="prToast"></div>

@endsection

<script type="application/json" id="prData">@json($purchaseRequests)</script>

@push('scripts')
<script>
(function () {
    const allPrs     = JSON.parse(document.getElementById('prData').textContent);
    const rows       = document.querySelectorAll('[data-pr-row]');
    const emptyEl    = document.getElementById('detailEmpty');
    const contentEl  = document.getElementById('detailContent');
    const titleEl    = document.getElementById('detailPrNumber');
    const officeChip = document.getElementById('detailPrOffice');
    const statusSel  = document.getElementById('statusSelect');
    const remarksIn  = document.getElementById('remarksInput');
    const btnSave    = document.getElementById('btnSave');
    const logEl      = document.getElementById('activityLog');
    const toastEl    = document.getElementById('prToast');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;

    const logs = {};
    let activePrId = null;
    let saving = false;

    /* ── Helpers ── */
    function badgeClass(status) {
        const s = (status || '').toLowerCase().replace(/_/g, ' ');
        if (s === 'completed')   return 'badge-completed';
        if (s === 'in progress') return 'badge-in-progress';
        if (s === 'pending')     return 'badge-pending';
        return 'badge-delayed';
    }

    function displayStatus(val) {
        return { pending: 'Pending', in_progress: 'In Progress', completed: 'Completed', delayed: 'Delayed' }[val] ?? val;
    }

    function nowStr() {
        return new Date().toLocaleString('en-PH', {
            timeZone: 'Asia/Manila', month: 'short', day: 'numeric',
            year: 'numeric', hour: 'numeric', minute: '2-digit'
        });
    }

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 2800);
    }

    function renderLog(prId) {
        const entries = logs[prId] || [];
        if (!entries.length) {
            logEl.innerHTML = '<p style="font-size:12px;color:var(--s400);padding:8px 0;">No activity recorded yet.</p>';
            return;
        }
        logEl.innerHTML = entries.slice().reverse().map(e => `
            <div class="activity-item">
                <div class="activity-dot"></div>
                <div>
                    <p>${e.text}</p>
                    <time>${e.time}</time>
                </div>
            </div>`).join('');
    }

    /* ── Open PR ── */
    function openPr(pr) {
        activePrId = pr.id;

        // Highlight row
        rows.forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-pr-id="${pr.id}"]`).classList.add('selected');

        // Header
        titleEl.textContent      = pr.prNumber;
        officeChip.textContent   = pr.office;
        officeChip.style.display = '';

        // Fields
        document.getElementById('fOffice').textContent   = pr.office;
        document.getElementById('fPrNumber').textContent = pr.prNumber;
        document.getElementById('fItem').textContent     = pr.item;
        document.getElementById('fDate').textContent     = pr.dateSubmitted;
        document.getElementById('fRemarks').textContent  = pr.remarks !== '—' ? pr.remarks : '—';

        const currentDisplay = pr.currentStatus;
        document.getElementById('fStatus').textContent = currentDisplay;

        // Reverse-map display label → select value
        const toValue = { 'Pending': 'pending', 'In Progress': 'in_progress', 'Completed': 'completed', 'Delayed': 'delayed' };
        statusSel.value = toValue[currentDisplay] ?? 'pending';
        remarksIn.value = '';

        // PDF preview
        const pdfEl = document.getElementById('pdfPreview');
        if (pr.pdfFile) {
            pdfEl.innerHTML = `<iframe src="/storage/${pr.pdfFile}" title="PR Document"></iframe>`;
        } else {
            pdfEl.innerHTML = `<div class="pdf-placeholder"><i class="ti ti-file-off"></i><span>No PDF attached</span></div>`;
        }

        // Seed log from server history (first open only)
        if (!logs[pr.id]) {
            logs[pr.id] = (pr.activityLog || []).map(e => ({
                text: `Status set to <strong>${e.status}</strong>` +
                      (e.remarks && e.remarks !== '—' ? ` &mdash; ${e.remarks}` : ''),
                time: e.timestamp,
            }));
        }

        emptyEl.style.display = 'none';
        contentEl.classList.add('visible');
        renderLog(pr.id);
    }

    /* ── Row click / keyboard ── */
    rows.forEach(row => {
        row.addEventListener('click', () => {
            const pr = allPrs.find(p => String(p.id) === row.dataset.prId);
            if (pr) openPr(pr);
        });
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
        });
    });

    /* ── Save ── */
    btnSave.addEventListener('click', async () => {
        if (!activePrId || saving) return;
        const pr         = allPrs.find(p => p.id == activePrId);
        const statusVal  = statusSel.value;
        const statusDisp = displayStatus(statusVal);
        const remarks    = remarksIn.value.trim();

        saving = true;
        const origHtml   = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Saving…';

        try {
            const resp = await fetch(pr.updateUrl, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  csrfToken,
                    'Accept':        'application/json',
                },
                body: JSON.stringify({ status: statusVal, remarks }),
            });

            if (resp.ok) {
                // Update in-memory PR record
                pr.currentStatus = statusDisp;
                if (remarks) pr.remarks = remarks;

                // Update table badge
                const badge = document.querySelector(`[data-status-badge="${activePrId}"]`);
                if (badge) { badge.textContent = statusDisp; badge.className = 'badge ' + badgeClass(statusDisp); }

                // Update status field in panel
                document.getElementById('fStatus').textContent = statusDisp;
                if (remarks) document.getElementById('fRemarks').textContent = remarks;

                // Append to log
                let logText = `Status updated to <strong>${statusDisp}</strong>`;
                if (remarks) logText += ` &mdash; ${remarks}`;
                logs[activePrId].push({ text: logText, time: nowStr() });
                remarksIn.value = '';
                renderLog(activePrId);

                showToast('Saved successfully.');
            } else {
                const json = await resp.json().catch(() => null);
                showToast(json?.message || 'Save failed. Please try again.', true);
            }
        } catch {
            showToast('Network error. Please try again.', true);
        } finally {
            saving          = false;
            btnSave.disabled = false;
            btnSave.innerHTML = origHtml;
        }
    });

    /* ── Spin keyframe ── */
    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
