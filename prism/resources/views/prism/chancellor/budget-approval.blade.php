@extends('prism.layouts.app')
@section('title', 'Budget Approval | Chancellor')

@push('page-css')
<style>
    .content {
        padding: 28px 32px 56px; flex: 1; display: flex; flex-direction: column; gap: 20px;
        --m: var(--crimson); --gold: #c9a84c; --white: #ffffff;
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .ch-toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(20px); background: #166534; color: #fff; padding: 12px 22px; border-radius: 10px; font-size: 13px; font-weight: 700; opacity: 0; pointer-events: none; transition: opacity .3s, transform .3s; z-index: 9999; white-space: nowrap; }
    .ch-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub   { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head  { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .two-panel { display: grid; grid-template-columns: minmax(0, 1fr) minmax(380px, 0.75fr); gap: 20px; align-items: start; }
    .left-col  { display: flex; flex-direction: column; gap: 20px; min-width: 0; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 44vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; cursor: pointer; }
    tbody tr:hover { background: var(--crimson-mid); }
    tbody tr.selected { background: rgba(139,26,28,.06); }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-endorsed { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-approved { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-returned { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-pending  { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .count-chip.archive { background: var(--s100); color: var(--s700); border-color: var(--s200); }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--s400); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .search-input { height: 40px; width: 100%; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 16px 0 36px; font-size: 13px; font-weight: 500; color: var(--s900); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .search-input::placeholder { color: var(--s400); }
    .search-toolbar { display: flex; align-items: center; gap: 8px; width: 100%; margin-bottom: 14px; }
    .search-toolbar .search-wrap { flex: 1; min-width: 0; margin-bottom: 0; }
    .filter-select { height: 40px; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 30px 0 14px; font-size: 12.5px; font-weight: 600; color: var(--s700); font-family: 'Poppins', sans-serif; outline: none; cursor: pointer; transition: border-color .15s, box-shadow .15s; flex-shrink: 0; }
    .filter-select:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    @media (max-width: 640px) { .search-toolbar { flex-wrap: wrap; } .search-toolbar .search-wrap { flex-basis: 100%; } }

    .detail-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; min-height: 220px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); padding: 32px; text-align: center; }
    .detail-empty svg { width: 36px; height: 36px; stroke: var(--crimson-light); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
    .detail-empty p { font-size: 13px; color: var(--s500); line-height: 1.7; max-width: 260px; }

    .detail-body { display: flex; flex-direction: column; gap: 16px; }

    .detail-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .meta-item { background: var(--s50); border: 1px solid var(--s200); border-radius: 10px; padding: 12px 14px; }
    .meta-item.full { grid-column: 1 / -1; }
    .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s400); margin-bottom: 4px; }
    .meta-value { font-size: 13px; font-weight: 700; color: var(--s900); }

    .remark-box { background: #e6f1fb; border: 1px solid #b5d4f4; border-radius: 10px; padding: 14px 16px; }
    .remark-box .remark-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #185fa5; margin-bottom: 6px; }
    .remark-box p { font-size: 13px; color: #185fa5; line-height: 1.7; font-weight: 500; }

    .items-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s500); margin-bottom: 8px; }
    .item-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); margin-bottom: 6px; }
    .item-row:last-child { margin-bottom: 0; }
    .item-name { font-size: 13px; font-weight: 600; color: var(--s900); }
    .item-qty  { font-size: 12px; color: var(--s500); margin-top: 2px; }
    .item-tags { font-size: 11px; color: var(--s400); margin-top: 3px; }
    .item-cost { font-size: 13px; font-weight: 700; color: var(--m); white-space: nowrap; }

    .btn-view-doc { display: inline-flex; align-items: center; justify-content: center; gap: 7px; width: 100%; height: 40px; padding: 0 16px; border-radius: 9px; font-size: 12.5px; font-weight: 700; border: 1.5px dashed var(--s300); background: var(--s50); color: var(--s500); cursor: not-allowed; font-family: 'Poppins', sans-serif; }

    .action-row { display: flex; gap: 10px; }
    .btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 44px; padding: 0 20px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: opacity .2s, transform .1s; }
    .btn:active { transform: scale(.98); }
    .btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .btn-approve { background: #3b6d11; color: #fff; }
    .btn-approve:hover { opacity: .88; }
    .btn-return  { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .btn-return:hover { background: #f7c1c1; }

    .remarks-area { width: 100%; min-height: 90px; resize: vertical; border: 1px solid var(--s200); border-radius: 10px; padding: 12px 14px; font-size: 13px; font-family: 'Poppins', sans-serif; color: var(--s900); background: var(--white); transition: border-color .2s; outline: none; }
    .remarks-area:focus { border-color: var(--m); }
    .remarks-area::placeholder { color: var(--s400); }

    /* Approval trail — collapsible, same pattern as the PR/AOC/PO activity log */
    .log-toggle { cursor: pointer; user-select: none; background: var(--s50); border: 1px solid var(--s200); border-radius: 10px; padding: 11px 14px; transition: background .15s, border-color .15s; display: flex; align-items: center; justify-content: space-between; }
    .log-toggle:hover { background: var(--s100); border-color: var(--s300); }
    .log-toggle-label { display: flex; align-items: center; gap: 9px; }
    .log-toggle-label i.ti-history { font-size: 16px; color: var(--m); }
    .log-toggle i.chev { font-size: 16px; transition: transform .18s; color: var(--s500); }
    .log-toggle.open i.chev { transform: rotate(180deg); }
    .activity-log { display: none; flex-direction: column; gap: 1px; margin-top: 10px; }
    .activity-log.open { display: flex; }
    .activity-item { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--s100); }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); flex-shrink: 0; margin-top: 5px; }
    .activity-item p { font-size: 12.5px; color: var(--s600); line-height: 1.6; }
    .activity-item time { font-size: 11px; color: var(--s400); display: block; margin-top: 2px; }

    @media (max-width: 1200px) { .two-panel { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-shield-check"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Chancellor</p>
            <h1 class="page-hdr-title">Budget Approval</h1>
            <p class="page-hdr-sub">Review proposals endorsed by Budget Office, inspect item details and market scoping, then approve or return with required remarks.</p>
        </div>
    </div>

<div id="chToast" class="ch-toast"></div>

    <div class="two-panel">

        <div class="left-col">

            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Budget-endorsed proposals</p>
                        <h2 class="card-title">Approval Queue</h2>
                    </div>
                    <span class="count-chip" id="queueCount">{{ count($proposals) }} endorsed</span>
                </div>

                @if(count($proposals) > 0)
                <div class="search-toolbar">
                    <div class="search-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="search-input" type="search" id="queueSearch" placeholder="Search by office, title, or code">
                    </div>
                    <select class="filter-select" id="queueOfficeFilter" title="Filter by office">
                        <option value="">All Offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office['code'] }}">{{ $office['code'] }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="queueSortFilter" title="Sort by date endorsed">
                        <option value="desc">Newest → Oldest</option>
                        <option value="asc">Oldest → Newest</option>
                    </select>
                </div>
                @endif

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Office</th><th>Title</th><th>Total Amount</th><th>Date Endorsed</th><th>Budget Remarks</th></tr>
                        </thead>
                        <tbody id="queueTbody">
                            @foreach ($proposals as $proposal)
                                <tr data-chancellor-proposal-row data-proposal-id="{{ $proposal['id'] }}" data-office="{{ $proposal['office'] }}" data-date-raw="{{ $proposal['dateEndorsedRaw'] }}" data-search="{{ strtolower($proposal['office'].' '.$proposal['title'].' '.$proposal['code']) }}" tabindex="0">
                                    <td style="font-weight:600;color:var(--s600);white-space:nowrap;">{{ $proposal['office'] }}</td>
                                    <td style="color:var(--s900);font-weight:500;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $proposal['title'] }}</td>
                                    <td style="font-weight:700;color:var(--m);white-space:nowrap;">PHP {{ number_format($proposal['totalAmount']) }}</td>
                                    <td style="color:var(--s500);white-space:nowrap;">{{ $proposal['dateEndorsed'] }}</td>
                                    <td style="color:var(--s600);max-width:200px;">{{ $proposal['financeRemarks'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Already decided</p>
                        <h2 class="card-title">Document Archive</h2>
                    </div>
                    <span class="count-chip archive" id="archiveCount">{{ count($archivedProposals) }} records</span>
                </div>

                @if(count($archivedProposals) > 0)
                <div class="search-toolbar">
                    <div class="search-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="search-input" type="search" id="archiveSearch" placeholder="Search by office, title, or code">
                    </div>
                    <select class="filter-select" id="archiveOfficeFilter" title="Filter by office">
                        <option value="">All Offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office['code'] }}">{{ $office['code'] }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="archiveSortFilter" title="Sort by date decided">
                        <option value="desc">Newest → Oldest</option>
                        <option value="asc">Oldest → Newest</option>
                    </select>
                </div>
                @endif

                @if(count($archivedProposals) === 0)
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;min-height:120px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:24px;text-align:center;">
                        <p style="font-size:13px;color:var(--s400);">Approved or returned proposals will show up here.</p>
                    </div>
                @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Office</th><th>Title</th><th>Total Amount</th><th>Status</th><th>Date Decided</th></tr>
                        </thead>
                        <tbody id="archiveTbody">
                            @foreach ($archivedProposals as $proposal)
                                <tr data-chancellor-archive-row data-proposal-id="{{ $proposal['id'] }}" data-office="{{ $proposal['office'] }}" data-date-raw="{{ $proposal['decidedAtRaw'] }}" data-search="{{ strtolower($proposal['office'].' '.$proposal['title'].' '.$proposal['code']) }}" tabindex="0">
                                    <td style="font-weight:600;color:var(--s600);white-space:nowrap;">{{ $proposal['office'] }}</td>
                                    <td style="color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $proposal['title'] }}</td>
                                    <td style="font-weight:700;color:var(--m);white-space:nowrap;">PHP {{ number_format($proposal['totalAmount']) }}</td>
                                    <td><span class="badge {{ $proposal['status'] === 'Approved' ? 'badge-approved' : 'badge-returned' }}">{{ $proposal['status'] }}</span></td>
                                    <td style="color:var(--s500);white-space:nowrap;">{{ $proposal['decidedDate'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>

        <aside class="card" aria-live="polite" style="position:sticky;top:86px;">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Full proposal details</p>
                    <h2 class="card-title" id="chancellorProposalTitle">Select a proposal</h2>
                </div>
            </div>

            <div id="chancellorProposalDetails">
                <div class="detail-empty">
                    <svg viewBox="0 0 24 24"><path d="M15 15l6 6m-11-4a7 7 0 110-14 7 7 0 010 14z"/></svg>
                    <p>Click a proposal row (queue or archive) to view items, costs, justifications, market scoping, Budget remarks, and the approval trail.</p>
                </div>
            </div>

            <div id="chancellorActionArea" style="display:none;flex-direction:column;gap:10px;margin-top:16px;">
                <textarea class="remarks-area" id="chancellorRemarks" placeholder="Enter your remarks (required for return)…"></textarea>
                <div class="action-row">
                    <button class="btn btn-approve" id="btnApprove" type="button">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Approve
                    </button>
                    <button class="btn btn-return" id="btnReturn" type="button">
                        <svg viewBox="0 0 24 24"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 00-4-4H4"/></svg>
                        Return
                    </button>
                </div>
            </div>
        </aside>

    </div>

</div>
@endsection

<script type="application/json" id="chancellorProposalData">@json($proposals)</script>
<script type="application/json" id="chancellorArchiveData">@json($archivedProposals)</script>

@push('scripts')
<script>
(function () {
    const queueProposals   = JSON.parse(document.getElementById('chancellorProposalData').textContent);
    const archiveProposals = JSON.parse(document.getElementById('chancellorArchiveData').textContent);
    let   allProposals     = queueProposals.concat(archiveProposals);

    const queueTbody   = document.getElementById('queueTbody');
    const archiveTbody = document.getElementById('archiveTbody');
    const queueCount    = document.getElementById('queueCount');
    const archiveCountEl = document.getElementById('archiveCount');

    const titleEl     = document.getElementById('chancellorProposalTitle');
    const detailsEl   = document.getElementById('chancellorProposalDetails');
    const actionArea  = document.getElementById('chancellorActionArea');
    const remarksEl   = document.getElementById('chancellorRemarks');
    const btnApprove  = document.getElementById('btnApprove');
    const btnReturn   = document.getElementById('btnReturn');
    let   activeId    = null;

    function php(n) { return 'PHP ' + Number(n).toLocaleString('en-PH'); }
    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function badgeClass(status) {
        const s = (status || '').toLowerCase();
        if (s.includes('approv'))  return 'badge-approved';
        if (s.includes('return'))  return 'badge-returned';
        if (s.includes('endors'))  return 'badge-endorsed';
        return 'badge-pending';
    }

    function renderApprovalLog(entries) {
        if (!entries || !entries.length) {
            return '<p style="font-size:12px;color:var(--s400);padding:8px 0;">No activity recorded yet.</p>';
        }
        return entries.slice().reverse().map(e => `
            <div class="activity-item">
                <div class="activity-dot"></div>
                <div>
                    <p>${escapeHtml(e.text)}</p>
                    <time>${escapeHtml(e.time)}</time>
                </div>
            </div>`).join('');
    }

    function renderDetail(proposal) {
        titleEl.textContent = proposal.office + ' — ' + proposal.title;
        const items = Array.isArray(proposal.items) ? proposal.items : [];

        const itemsHtml = items.length ? items.map(item => `
            <div class="item-row">
                <div>
                    <p class="item-name">${escapeHtml(item.name || item.description || 'Item')}</p>
                    <p class="item-qty">Qty: ${item.quantity ?? '—'} ${escapeHtml(item.unit || '')} ${item.justification ? '&nbsp;|&nbsp; ' + escapeHtml(item.justification) : ''}</p>
                    <p class="item-tags">Target: ${escapeHtml(item.targetQuarter || '—')} &middot; Fund: ${escapeHtml(item.sourceOfFund || '—')} &middot; Class: ${escapeHtml(item.classification || '—')}</p>
                </div>
                <span class="item-cost">${php(item.amount ?? item.cost ?? 0)}</span>
            </div>
        `).join('') : `<p style="font-size:13px;color:var(--s400);padding:8px 0;">No items listed.</p>`;

        detailsEl.innerHTML = `
            <div class="detail-body">
                <div class="detail-meta">
                    <div class="meta-item"><p class="meta-label">Office</p><p class="meta-value">${escapeHtml(proposal.officeName || proposal.office)}</p></div>
                    <div class="meta-item"><p class="meta-label">Fiscal Year</p><p class="meta-value">${escapeHtml(proposal.fiscalYear || '—')}</p></div>
                    <div class="meta-item"><p class="meta-label">Office Head</p><p class="meta-value">${escapeHtml(proposal.officeHead || '—')}</p></div>
                    <div class="meta-item"><p class="meta-label">Submitted</p><p class="meta-value">${escapeHtml(proposal.submittedDate || '—')}</p></div>
                    <div class="meta-item"><p class="meta-label">Total Amount</p><p class="meta-value">${php(proposal.totalAmount ?? 0)}</p></div>
                    <div class="meta-item"><p class="meta-label">Date Endorsed</p><p class="meta-value">${escapeHtml(proposal.dateEndorsed ?? '—')}</p></div>
                    <div class="meta-item"><p class="meta-label">Market Scoping</p><p class="meta-value">${escapeHtml(proposal.marketScoping ?? '—')}</p></div>
                    <div class="meta-item"><p class="meta-label">Status</p><p class="meta-value"><span class="badge ${badgeClass(proposal.status)}">${escapeHtml(proposal.status ?? 'Endorsed')}</span></p></div>
                </div>

                <button class="btn-view-doc" type="button" disabled title="No PPMP document template exists yet — coming soon.">
                    <i class="ti ti-file-text"></i> View Document
                </button>

                <div class="remark-box">
                    <p class="remark-label">Budget Remarks</p>
                    <p>${escapeHtml(proposal.financeRemarks ?? '—')}</p>
                </div>
                <div>
                    <p class="items-label">Proposed Items</p>
                    ${itemsHtml}
                </div>

                <div>
                    <div class="log-toggle" id="approvalTrailToggle">
                        <div class="log-toggle-label">
                            <i class="ti ti-history"></i>
                            <div>
                                <p class="card-eyebrow" style="margin-bottom:1px;">History</p>
                                <h3 class="card-title" style="font-size:13px;">View Approval Trail</h3>
                            </div>
                        </div>
                        <i class="ti ti-chevron-down chev"></i>
                    </div>
                    <div class="activity-log" id="approvalTrailLog">${renderApprovalLog(proposal.approvalTrail)}</div>
                </div>
            </div>
        `;

        const trailToggle = document.getElementById('approvalTrailToggle');
        const trailLog     = document.getElementById('approvalTrailLog');
        trailToggle.addEventListener('click', () => {
            trailToggle.classList.toggle('open');
            trailLog.classList.toggle('open');
        });

        const isActionable = (proposal.status || '').toLowerCase() === 'endorsed';
        actionArea.style.display = isActionable ? 'flex' : 'none';
        remarksEl.value = '';
    }

    function selectRow(id) {
        if (activeId === id) return;
        activeId = id;
        document.querySelectorAll('[data-chancellor-proposal-row], [data-chancellor-archive-row]').forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-proposal-id="${id}"]`)?.classList.add('selected');
        const proposal = allProposals.find(p => String(p.id) === String(id));
        if (proposal) renderDetail(proposal);
    }

    function wireRows(root, selector) {
        root?.querySelectorAll(selector).forEach(row => {
            row.addEventListener('click', function () { selectRow(this.dataset.proposalId); });
            row.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
            });
        });
    }
    wireRows(queueTbody, '[data-chancellor-proposal-row]');
    wireRows(archiveTbody, '[data-chancellor-archive-row]');

    /* ── Search + office filter + sort, shared logic per table ── */
    function setupTableControls({ searchId, officeId, sortId, tbody, rowSelector, countEl, countLabel }) {
        const searchInput = document.getElementById(searchId);
        const officeFilter = document.getElementById(officeId);
        const sortFilter   = document.getElementById(sortId);
        if (!searchInput || !tbody) return;

        function apply() {
            const q      = searchInput.value.trim().toLowerCase();
            const office = officeFilter ? officeFilter.value : '';
            let visible = 0;
            tbody.querySelectorAll(rowSelector).forEach(row => {
                const matchesSearch = !q || (row.dataset.search ?? '').includes(q);
                const matchesOffice = !office || row.dataset.office === office;
                const match = matchesSearch && matchesOffice;
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (countEl) countEl.textContent = visible + ' ' + countLabel;
        }

        function applySort() {
            if (!sortFilter) return;
            const dir  = sortFilter.value;
            const rows = Array.from(tbody.querySelectorAll(rowSelector));
            rows.sort((a, b) => {
                const ta = new Date(a.dataset.dateRaw || 0).getTime();
                const tb = new Date(b.dataset.dateRaw || 0).getTime();
                return dir === 'asc' ? ta - tb : tb - ta;
            });
            rows.forEach(row => tbody.appendChild(row));
        }

        searchInput.addEventListener('input', apply);
        officeFilter?.addEventListener('change', apply);
        sortFilter?.addEventListener('change', applySort);
    }

    setupTableControls({
        searchId: 'queueSearch', officeId: 'queueOfficeFilter', sortId: 'queueSortFilter',
        tbody: queueTbody, rowSelector: '[data-chancellor-proposal-row]',
        countEl: queueCount, countLabel: 'endorsed',
    });
    setupTableControls({
        searchId: 'archiveSearch', officeId: 'archiveOfficeFilter', sortId: 'archiveSortFilter',
        tbody: archiveTbody, rowSelector: '[data-chancellor-archive-row]',
        countEl: archiveCountEl, countLabel: 'records',
    });

    /* ── Approve / Return — moves the row into the Archive in place instead
       of reloading, so an acted-on proposal never just vanishes. ── */
    const csrfToken = document.querySelector('meta[name=csrf-token]').content;
    const toast     = document.getElementById('chToast');

    function showToast(msg, isError) {
        toast.textContent = msg;
        toast.style.background = isError ? '#991b1b' : '#166534';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    async function submitAction(url, remarks) {
        const resp = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ remarks }),
        });
        return resp.json();
    }

    function archiveRowHtml(p) {
        return `<tr data-chancellor-archive-row data-proposal-id="${p.id}" data-office="${escapeHtml(p.office)}" data-date-raw="${p.decidedAtRaw || ''}" data-search="${escapeHtml((p.office + ' ' + p.title + ' ' + p.code).toLowerCase())}" tabindex="0">
            <td style="font-weight:600;color:var(--s600);white-space:nowrap;">${escapeHtml(p.office)}</td>
            <td style="color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(p.title)}</td>
            <td style="font-weight:700;color:var(--m);white-space:nowrap;">${php(p.totalAmount)}</td>
            <td><span class="badge ${p.status === 'Approved' ? 'badge-approved' : 'badge-returned'}">${escapeHtml(p.status)}</span></td>
            <td style="color:var(--s500);white-space:nowrap;">${escapeHtml(p.decidedDate)}</td>
        </tr>`;
    }

    function moveToArchive(updatedProposal) {
        const idx = allProposals.findIndex(p => String(p.id) === String(updatedProposal.id));
        if (idx !== -1) allProposals[idx] = updatedProposal; else allProposals.push(updatedProposal);

        document.querySelector(`[data-chancellor-proposal-row][data-proposal-id="${updatedProposal.id}"]`)?.remove();
        if (queueCount) queueCount.textContent = queueTbody.querySelectorAll('[data-chancellor-proposal-row]').length + ' endorsed';

        if (archiveTbody) {
            archiveTbody.insertAdjacentHTML('afterbegin', archiveRowHtml(updatedProposal));
            wireRows(archiveTbody, `[data-proposal-id="${updatedProposal.id}"]`);
            if (archiveCountEl) archiveCountEl.textContent = archiveTbody.querySelectorAll('[data-chancellor-archive-row]').length + ' records';
        }

        renderDetail(updatedProposal);
        document.querySelectorAll('[data-chancellor-proposal-row], [data-chancellor-archive-row]').forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-proposal-id="${updatedProposal.id}"]`)?.classList.add('selected');
    }

    btnApprove.addEventListener('click', async function () {
        if (!activeId) return;
        const proposal = allProposals.find(p => String(p.id) === String(activeId));
        if (!proposal) return;
        btnApprove.disabled = true;
        const data = await submitAction(proposal.approveUrl, remarksEl.value.trim());
        btnApprove.disabled = false;
        if (data.success) { showToast(data.message); moveToArchive(data.proposal); }
        else showToast(data.message || 'An error occurred.', true);
    });

    btnReturn.addEventListener('click', async function () {
        if (!activeId) return;
        const remarks = remarksEl.value.trim();
        if (!remarks) { remarksEl.focus(); remarksEl.style.borderColor = '#a32d2d'; return; }
        remarksEl.style.borderColor = '';
        const proposal = allProposals.find(p => String(p.id) === String(activeId));
        if (!proposal) return;
        btnReturn.disabled = true;
        const data = await submitAction(proposal.returnUrl, remarks);
        btnReturn.disabled = false;
        if (data.success) { showToast(data.message); moveToArchive(data.proposal); }
        else showToast(data.message || 'An error occurred.', true);
    });

    remarksEl.addEventListener('input', function () { this.style.borderColor = ''; });
})();
</script>
@endpush
