@extends($layout ?? 'prism.layouts.app')
@section('title', 'For My Signature | ' . ($roleLabel ?? 'PRISM'))

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
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .pr-grid { display: grid; grid-template-columns: minmax(0, 1fr) 440px; gap: 20px; align-items: start; }

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
    .badge-signed  { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-routing { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-draft   { background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }
    .badge-action  { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .doc-badge { display:inline-flex; align-items:center; height:22px; padding:0 10px; border-radius:6px; font-size:10px; font-weight:800; letter-spacing:.06em; }
    .doc-pr  { background:#e0ecff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .doc-aoc { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
    .doc-po  { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--s400); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .search-input { height: 40px; width: 100%; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 16px 0 36px; font-size: 13px; font-weight: 500; color: var(--s900); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .search-input::placeholder { color: var(--s400); }

    .detail-panel { display: flex; flex-direction: column; gap: 16px; }
    .detail-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 220px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); text-align: center; padding: 32px; }
    .detail-empty i { font-size: 36px; color: var(--s300); }
    .detail-empty p { font-size: 13px; color: var(--s400); line-height: 1.6; max-width: 220px; }

    .detail-content { display: none; flex-direction: column; gap: 16px; }
    .detail-content.visible { display: flex; }

    .detail-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .detail-field { background: var(--s50); border: 1px solid var(--s200); border-radius: 10px; padding: 10px 14px; }
    .detail-field.full { grid-column: 1 / -1; }
    .detail-field label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s400); display: block; margin-bottom: 3px; }
    .detail-field span { font-size: 13px; font-weight: 600; color: var(--s700); }

    /* Document preview — open by default the moment a document is selected;
       the toggle just lets the reviewer collapse it out of the way. */
    .preview-toggle { display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; }
    .preview-toggle i.chev { transition: transform .18s; color: var(--s400); }
    .preview-toggle.open i.chev { transform: rotate(180deg); }
    .preview-body { display: none; margin-top: 10px; }
    .preview-body.open { display: block; }
    .pdf-preview { border-radius: 12px; border: 1px solid var(--s200); background: var(--s50); overflow: hidden; aspect-ratio: 8.5 / 11; display: flex; align-items: center; justify-content: center; position: relative; }
    .pdf-preview iframe { width: 100%; height: 100%; border: none; }
    .pdf-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 100%; color: var(--s400); }
    .pdf-placeholder i { font-size: 34px; color: var(--s300); }
    .pdf-placeholder span { font-size: 12px; font-weight: 600; }
    .preview-items-table { width: 100%; border-collapse: collapse; font-size: 11.5px; margin-bottom: 10px; }
    .preview-items-table th { background: var(--s100); padding: 6px 8px; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: .06em; color: var(--s500); }
    .preview-items-table td { padding: 6px 8px; border-bottom: 1px solid var(--s100); }
    .preview-items-table tfoot td { font-weight: 800; color: var(--m); border-top: 2px solid var(--s200); border-bottom: none; }
    .preview-quotes { display: flex; flex-direction: column; gap: 6px; }
    .preview-quote-row { display: flex; align-items: center; gap: 8px; border: 1px solid var(--s200); border-radius: 8px; padding: 7px 10px; font-size: 11.5px; background: var(--s50); }
    .preview-quote-row .qs { font-weight: 700; color: var(--s700); }
    .preview-quote-row .qf { color: var(--s500); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
    .preview-quote-row a { font-size: 11px; font-weight: 700; color: #1d4ed8; text-decoration: none; }
    .preview-empty-note { font-size: 12px; color: var(--s400); }

    .sig-timeline { display: flex; align-items: center; gap: 0; margin-bottom: 4px; flex-wrap: wrap; row-gap: 14px; }
    .sig-step { display: flex; flex-direction: column; align-items: center; flex: 1; min-width: 64px; position: relative; }
    .sig-step:not(:last-child)::after { content: ''; position: absolute; top: 10px; left: 50%; width: 100%; height: 2px; background: var(--s200); z-index: 0; }
    .sig-step.done::after { background: #3b6d11; }
    .sig-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--s300); background: var(--white); z-index: 1; position: relative; transition: all .2s; }
    .sig-step.done .sig-dot { background: #3b6d11; border-color: #3b6d11; }
    .sig-step.active .sig-dot { background: var(--m); border-color: var(--m); box-shadow: 0 0 0 3px rgba(139,26,28,.2); }
    .sig-label { font-size: 9px; font-weight: 700; text-align: center; color: var(--s400); margin-top: 5px; line-height: 1.3; max-width: 84px; }
    .sig-step.done .sig-label, .sig-step.active .sig-label { color: var(--s700); }

    .btn-route { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; }
    .btn-route-fwd { background: #3b6d11; color: #fff; }
    .btn-route-fwd:hover:not(:disabled) { background: #2e560d; }
    .btn-route:disabled { opacity: .5; cursor: not-allowed; }

    .third-signer-panel { margin-top: 8px; }
    .third-signer-panel p { font-size: 11px; font-weight: 700; color: var(--s500); margin-bottom: 6px; }
    .third-signer-panel label { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--s700); margin-right: 16px; cursor: pointer; }

    .remarks-textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 13px; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 70px; outline: none; transition: border-color .2s; line-height: 1.6; box-sizing: border-box; }
    .remarks-textarea:focus { border-color: var(--m); }
    .remarks-textarea::placeholder { color: var(--s300); }

    .activity-log { display: flex; flex-direction: column; gap: 1px; }
    .activity-item { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--s100); }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); flex-shrink: 0; margin-top: 5px; }
    .activity-item p { font-size: 12.5px; color: var(--s600); line-height: 1.6; }
    .activity-item time { font-size: 11px; color: var(--s400); display: block; margin-top: 2px; }
    .activity-attachments { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .activity-remarks { font-size: 11.5px; font-weight: 600; color: #92400E; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 6px; padding: 6px 9px; margin-top: 6px; line-height: 1.5; white-space: pre-wrap; }
    .activity-attachment { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: var(--m); background: var(--s50); border: 1px solid var(--s100); border-radius: 6px; padding: 3px 8px; text-decoration: none; max-width: 160px; }
    .activity-attachment span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .activity-attachment:hover { background: var(--s100); }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 1100px) { .pr-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-signature"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">{{ $roleLabel ?? '' }}</p>
            <h1 class="page-hdr-title">For My Signature</h1>
            <p class="page-hdr-sub">Every document that passes through your stage — created, in progress, or already fully signed. Rows highlighted "Needs You" are awaiting your action right now.</p>
        </div>
    </div>

    <div class="pr-grid">

        {{-- ── Left: document list ── --}}
        <div class="card" style="padding-bottom: 22px;">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">All Signatories</p>
                    <h2 class="card-title">Documents</h2>
                </div>
                <span class="count-chip" id="docVisibleCount" style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:var(--s100);color:var(--s700);border:1px solid var(--s200);">{{ count($documents) }} document{{ count($documents) !== 1 ? 's' : '' }}</span>
            </div>

            @if(count($documents) > 0)
                <div class="search-wrap" style="margin-bottom:14px;">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="search" id="docSearch" placeholder="Search by number, office, or title">
                </div>
            @endif

            @if(count($documents) === 0)
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:180px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:32px;text-align:center;">
                    <i class="ti ti-inbox" style="font-size:38px;color:var(--s300);"></i>
                    <p style="font-size:13px;color:var(--s400);max-width:240px;line-height:1.6;">No documents yet.</p>
                </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Number</th>
                            <th>Office</th>
                            <th>Title</th>
                            <th>Current Stage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $doc)
                            @php
                                $stageBadge = match(true) {
                                    $doc['canAct']                          => 'badge-action',
                                    $doc['signatoryStage'] === 'fully_signed' => 'badge-signed',
                                    $doc['signatoryStage'] === 'draft'        => 'badge-draft',
                                    default                                  => 'badge-routing',
                                };
                            @endphp
                            <tr data-doc-row data-doc-key="{{ $doc['docType'] }}-{{ $doc['id'] }}" data-search="{{ strtolower($doc['number'] . ' ' . $doc['office'] . ' ' . $doc['title']) }}" tabindex="0">
                                <td><span class="doc-badge doc-{{ $doc['docType'] }}">{{ $doc['docLabel'] }}</span></td>
                                <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $doc['number'] }}</td>
                                <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $doc['office'] }}</td>
                                <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $doc['title'] }}</td>
                                <td><span class="badge {{ $stageBadge }}" data-sig-badge="{{ $doc['docType'] }}-{{ $doc['id'] }}">{{ $doc['canAct'] ? 'Needs You' : $doc['signatoryLabel'] }}</span></td>
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
                    <p class="card-eyebrow">Document Details</p>
                    <h2 class="card-title" id="detailDocNumber">Select a document</h2>
                </div>
            </div>

            <div class="detail-empty" id="detailEmpty">
                <i class="ti ti-arrow-left"></i>
                <p>Select a document from the list to view its full signatory journey.</p>
            </div>

            <div class="detail-content" id="detailContent">

                <div class="detail-fields">
                    <div class="detail-field"><label>Number</label><span id="fNumber">—</span></div>
                    <div class="detail-field"><label>Office</label><span id="fOffice">—</span></div>
                    <div class="detail-field full"><label>Title</label><span id="fTitle">—</span></div>
                    <div class="detail-field full"><label>Remarks on File</label><span id="fRemarks" style="white-space:pre-line;">—</span></div>
                </div>

                {{-- Document preview (PR: uploaded PDF, AOC: parent PR items + canvass quotations) --}}
                <div id="previewSection" style="display:none;">
                    <div class="preview-toggle" id="previewToggle">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--s500);">Document Preview</div>
                        <i class="ti ti-chevron-down chev"></i>
                    </div>
                    <div class="preview-body" id="previewBody"></div>
                </div>

                <div>
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--s500);margin-bottom:10px;">Signature Routing</div>
                    <div class="sig-timeline" id="sigTimeline"></div>
                    <p id="sigWaitingNote" style="font-size:11px;color:var(--s500);margin-top:8px;"></p>

                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <button class="btn-route btn-route-fwd" id="btnMarkSigned" type="button" disabled>
                            <i class="ti ti-circle-arrow-right" id="btnMarkSignedIcon"></i>
                            <span id="btnMarkSignedLabel">Mark Signed</span>
                        </button>
                    </div>

                    <div class="third-signer-panel" id="thirdSignerPanel" style="display:none;">
                        <p>Who signs 3rd? (Accounting and Vice Chancellor sign in flexible order)</p>
                        <label><input type="radio" name="third_signer" value="accounting"> Accounting first</label>
                        <label><input type="radio" name="third_signer" value="vice_chancellor"> Vice Chancellor first</label>
                    </div>
                </div>

                <div>
                    <label style="font-size:12px;font-weight:700;color:var(--s700);display:block;margin-bottom:6px;">Remarks (optional)</label>
                    <textarea class="remarks-textarea" id="remarksInput" placeholder="Add a remark about this signature…"></textarea>
                </div>

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

<script type="application/json" id="docData">@json($documents)</script>

@push('scripts')
<script>
(function () {
    const allDocs     = JSON.parse(document.getElementById('docData').textContent);
    const rows        = document.querySelectorAll('[data-doc-row]');
    const emptyEl      = document.getElementById('detailEmpty');
    const contentEl    = document.getElementById('detailContent');
    const titleEl       = document.getElementById('detailDocNumber');
    const remarksIn     = document.getElementById('remarksInput');
    const logEl          = document.getElementById('activityLog');
    const toastEl        = document.getElementById('prToast');
    const btnMark         = document.getElementById('btnMarkSigned');
    const btnMarkIcon     = document.getElementById('btnMarkSignedIcon');
    const btnMarkLabel    = document.getElementById('btnMarkSignedLabel');
    const thirdPanel      = document.getElementById('thirdSignerPanel');
    const waitingNote    = document.getElementById('sigWaitingNote');
    const csrfToken       = document.querySelector('meta[name="csrf-token"]').content;
    const docSearch       = document.getElementById('docSearch');
    const docCount        = document.getElementById('docVisibleCount');
    const previewSection  = document.getElementById('previewSection');
    const previewToggle   = document.getElementById('previewToggle');
    const previewBody     = document.getElementById('previewBody');

    let activeDoc = null;
    let saving    = false;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 2800);
    }

    function timelineHtml(doc) {
        return doc.chain.map(step =>
            `<div class="sig-step${step.status === 'done' ? ' done' : ''}${step.status === 'active' ? ' active' : ''}"><div class="sig-dot"></div><span class="sig-label">${step.label}</span></div>`
        ).join('');
    }

    function renderLog(doc) {
        const entries = doc.signatureLogs || [];
        if (!entries.length) {
            logEl.innerHTML = '<p style="font-size:12px;color:var(--s400);padding:8px 0;">No activity recorded yet.</p>';
            return;
        }
        logEl.innerHTML = entries.slice().reverse().map(e => `
            <div class="activity-item">
                <div class="activity-dot"></div>
                <div>
                    <p>${e.display}${e.by !== '—' ? ' — ' + e.by : ''}</p>
                    <time>${e.at}</time>
                    ${remarksHtml(e.remarks)}
                    ${attachmentsHtml(e.attachments)}
                </div>
            </div>`).join('');
    }

    // Why a document was sent back — shown to whoever it now lands with.
    function remarksHtml(remarks) {
        if (!remarks) return '';
        return '<p class="activity-remarks"><i class="ti ti-message-2" style="font-size:11px;margin-right:4px"></i>' + escapeHtml(remarks) + '</p>';
    }

    // Files attached via the mobile app's Take a Photo / Upload flow —
    // opens the signed signature-attachment link in a new tab.
    function attachmentsHtml(attachments) {
        if (!attachments || !attachments.length) return '';
        return '<div class="activity-attachments">' + attachments.map(a => `
            <a href="${a.url}" target="_blank" rel="noopener" class="activity-attachment" title="${escapeHtml(a.filename)}">
                <i class="ti ${a.isImage ? 'ti-photo' : 'ti-file-text'}"></i>
                <span>${escapeHtml(a.filename)}</span>
            </a>`).join('') + '</div>';
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function renderPreview(doc) {
        // Open by default whenever the selected document has something to
        // preview (PR/AOC) — the reviewer shouldn't need an extra click.
        const hasPreview = doc.docType === 'pr' || doc.docType === 'aoc';
        previewToggle.classList.toggle('open', hasPreview);
        previewBody.classList.toggle('open', hasPreview);

        if (doc.docType === 'pr') {
            previewSection.style.display = '';
            previewBody.innerHTML = doc.pdfFile
                ? `<div class="pdf-preview"><iframe src="/storage/${doc.pdfFile}" title="PR Document"></iframe></div>`
                : `<div class="pdf-preview"><div class="pdf-placeholder"><i class="ti ti-file-off"></i><span>No PDF uploaded for this PR</span></div></div>`;
            return;
        }

        if (doc.docType === 'aoc') {
            previewSection.style.display = '';
            const items = doc.prItems || [];
            const quotes = doc.quotations || [];

            let itemsHtml = '<p class="preview-empty-note">No items on file for the parent PR.</p>';
            if (items.length) {
                itemsHtml = `<table class="preview-items-table"><thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Total</th></tr></thead><tbody>`
                    + items.map(i => `<tr><td>${escapeHtml(i.name)}</td><td>${i.quantity}</td><td>${escapeHtml(i.unit)}</td><td>₱${Number(i.unitCost).toLocaleString(undefined,{minimumFractionDigits:2})}</td><td>₱${Number(i.totalCost).toLocaleString(undefined,{minimumFractionDigits:2})}</td></tr>`).join('')
                    + `</tbody><tfoot><tr><td colspan="4">Total</td><td>₱${Number(doc.prTotal || 0).toLocaleString(undefined,{minimumFractionDigits:2})}</td></tr></tfoot></table>`;
            }

            let quotesHtml = '<p class="preview-empty-note">No canvass quotations uploaded yet.</p>';
            if (quotes.length) {
                quotesHtml = `<div class="preview-quotes">` + quotes.map(q => `
                    <div class="preview-quote-row">
                        <span class="qs">${escapeHtml(q.supplier)}</span>
                        <span class="qf">${escapeHtml(q.filename)}</span>
                        <a href="${q.url}" target="_blank" rel="noopener">View</a>
                    </div>`).join('') + `</div>`;
            }

            previewBody.innerHTML = `
                <div style="font-size:10.5px;font-weight:700;color:var(--s600);margin-bottom:6px;">Items (from parent PR)</div>
                ${itemsHtml}
                <div style="font-size:10.5px;font-weight:700;color:var(--s600);margin:12px 0 6px;">Canvass Quotations</div>
                ${quotesHtml}
            `;
            return;
        }

        previewSection.style.display = 'none';
        previewBody.innerHTML = '';
    }

    function renderDetail(doc) {
        document.getElementById('fNumber').textContent  = doc.number;
        document.getElementById('fOffice').textContent  = doc.office;
        document.getElementById('fTitle').textContent   = doc.title;
        document.getElementById('fRemarks').textContent = doc.remarks;
        document.getElementById('sigTimeline').innerHTML = timelineHtml(doc);
        renderPreview(doc);

        btnMark.disabled = !doc.canAct;
        document.querySelectorAll('input[name="third_signer"]').forEach(r => r.checked = false);
        thirdPanel.style.display = (doc.canAct && doc.docType === 'pr' && doc.nextStage === 'at_third_sign') ? 'block' : 'none';

        if (doc.canAct) {
            btnMarkLabel.textContent = doc.stageType === 'routing' ? 'Forward' : 'Mark Signed';
            waitingNote.textContent = '';
        } else {
            btnMarkLabel.textContent = 'Mark Signed';
            waitingNote.textContent = doc.signatoryStage === 'fully_signed'
                ? 'This document is fully signed.'
                : 'This document is not currently at your stage.';
        }

        renderLog(doc);
    }

    function openDoc(doc) {
        activeDoc = doc;
        rows.forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-doc-key="${doc.docType}-${doc.id}"]`)?.classList.add('selected');
        titleEl.textContent = doc.docLabel + ' ' + doc.number;
        remarksIn.value = '';
        renderDetail(doc);
        emptyEl.style.display = 'none';
        contentEl.classList.add('visible');
    }

    rows.forEach(row => {
        row.addEventListener('click', () => {
            const [docType, id] = row.dataset.docKey.split('-');
            const doc = allDocs.find(d => d.docType === docType && String(d.id) === id);
            if (doc) openDoc(doc);
        });
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
        });
    });

    // Arrived via a "document awaiting your signature" notification — open that
    // exact document instead of leaving the generic queue with nothing selected.
    (function openFromNotification() {
        const params  = new URLSearchParams(window.location.search);
        const docType = params.get('docType');
        const id      = params.get('id');
        if (!docType || !id) return;
        const doc = allDocs.find(d => d.docType === docType && String(d.id) === id);
        if (doc) openDoc(doc);
    })();

    previewToggle.addEventListener('click', () => {
        previewToggle.classList.toggle('open');
        previewBody.classList.toggle('open');
    });

    docSearch?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const match = !q || (row.dataset.search ?? '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (docCount) docCount.textContent = visible + (visible === 1 ? ' document' : ' documents');
    });

    /* ── Mark Signed / Forward (no photo — matches the Procurement page's simple flow) ── */
    async function markSigned() {
        if (!activeDoc || saving) return;

        const needsThird = activeDoc.docType === 'pr' && activeDoc.nextStage === 'at_third_sign';
        let thirdSigner = null;
        if (needsThird) {
            const checked = document.querySelector('input[name="third_signer"]:checked');
            if (!checked) { showToast('Choose who signs 3rd before proceeding.', true); return; }
            thirdSigner = checked.value;
        }

        saving = true;
        btnMark.disabled = true;
        btnMarkIcon.className = 'ti ti-loader-2';
        btnMarkIcon.style.animation = 'spin .7s linear infinite';
        btnMarkLabel.textContent = activeDoc.stageType === 'routing' ? 'Forwarding…' : 'Signing…';

        try {
            if (activeDoc.stageType === 'routing') {
                const r = await fetch(activeDoc.signUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ remarks: remarksIn.value || null }),
                });
                const j = await r.json();
                if (r.ok && j.success && j.signatoryStage) {
                    applyAdvance(j);
                } else {
                    showToast(j.error || 'Failed to forward.', true);
                }
                return;
            }

            const r1 = await fetch(activeDoc.signUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: new FormData(),
            });
            const j1 = await r1.json();
            if (!r1.ok || !j1.needsConfirmation) {
                showToast(j1.error || 'Failed to sign.', true);
                return;
            }

            const payload = { remarks: remarksIn.value || null };
            if (thirdSigner) payload.third_signer = thirdSigner;

            const r2 = await fetch(activeDoc.confirmUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const j2 = await r2.json();

            if (r2.ok && j2.success && j2.signatoryStage) {
                applyAdvance(j2);
            } else {
                showToast(j2.error || 'Failed to send.', true);
            }
        } catch {
            showToast('Network error — please try again.', true);
        } finally {
            saving = false;
            btnMarkIcon.className = 'ti ti-circle-arrow-right';
            btnMarkIcon.style.animation = '';
            btnMarkLabel.textContent = activeDoc && activeDoc.stageType === 'routing' ? 'Forward' : 'Mark Signed';
            if (activeDoc) btnMark.disabled = !activeDoc.canAct;
        }
    }

    function applyAdvance(json) {
        activeDoc.signatoryStage = json.signatoryStage;
        activeDoc.signatoryLabel = json.signatoryLabel;
        activeDoc.canAct = false; // this role's part is done until it comes back around
        activeDoc.signatureLogs = activeDoc.signatureLogs || [];
        activeDoc.signatureLogs.push({ display: 'Signed – ' + activeDoc.signatoryLabel, by: '', at: 'just now' });
        if (Array.isArray(json.stageMeta)) {
            // Re-derive chain done/active/pending from the fresh stage list.
            const stages = json.stageMeta.map(m => m.key);
            const idx    = stages.indexOf(json.signatoryStage);
            activeDoc.chain = activeDoc.chain.map(step => {
                const stepIdx = stages.indexOf(step.key);
                return Object.assign({}, step, {
                    status: stepIdx < idx ? 'done' : (stepIdx === idx ? 'active' : 'pending'),
                });
            });
        }

        const badge = document.querySelector(`[data-sig-badge="${activeDoc.docType}-${activeDoc.id}"]`);
        if (badge) {
            badge.textContent = activeDoc.signatoryLabel;
            badge.className   = 'badge ' + (activeDoc.signatoryStage === 'fully_signed' ? 'badge-signed' : 'badge-routing');
        }

        renderDetail(activeDoc);
        showToast((activeDoc.stageType === 'routing' ? 'Forwarded' : 'Signed') + (json.nextStageLabel ? ' — routed to: ' + json.nextStageLabel + '.' : '.'));
    }

    btnMark.addEventListener('click', () => markSigned());

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }

    // ── Auto-refresh ── this page's queue/status/attachments are baked in at
    // load time, so another signatory acting on a document elsewhere wouldn't
    // show up here without a manual reload. Skip the reload while a sign/route
    // action is in flight or a remark is being composed, so it can't wipe
    // in-progress work.
    setInterval(() => {
        if (saving) return;
        const remarks = document.getElementById('remarksInput');
        if (remarks && remarks.value.trim()) return;
        window.location.reload();
    }, 45000);
})();
</script>
@endpush
