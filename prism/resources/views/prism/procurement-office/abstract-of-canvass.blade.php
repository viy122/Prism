@extends('prism.layouts.app')
@section('title', 'Abstract of Canvass | Procurement Office')

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

    .eligible-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; }
    .eligible-card { border: 1.5px solid var(--s200); border-radius: 12px; padding: 14px 18px; display: flex; flex-direction: column; gap: 8px; }
    .eligible-card h4 { font-size: 13px; font-weight: 700; color: var(--s900); }
    .eligible-card p { font-size: 12px; color: var(--s500); }

    .aoc-grid { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 20px; align-items: start; }

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

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--s400); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .search-input { height: 40px; width: 100%; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 16px 0 36px; font-size: 13px; font-weight: 500; color: var(--s900); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    .search-input::placeholder { color: var(--s400); }

    /* Detail panel */
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

    /* Uploaded AOC PDF — the scanned, physically-signed document */
    .pdf-preview { border-radius: 12px; border: 1px solid var(--s200); background: var(--s50); overflow: hidden; aspect-ratio: 8.5 / 11; display: flex; align-items: center; justify-content: center; position: relative; }
    .pdf-preview iframe { width: 100%; height: 100%; border: none; }
    .pdf-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 100%; color: var(--s400); }
    .pdf-placeholder i { font-size: 42px; color: var(--s300); }
    .pdf-placeholder span { font-size: 12px; font-weight: 600; }
    .upload-pr-label { display: inline-flex; align-items: center; gap: 7px; border: 1.5px dashed var(--s300); border-radius: 9px; background: var(--s50); padding: 7px 14px; font-size: 12px; font-weight: 700; color: var(--s600); cursor: pointer; transition: background .15s; white-space: nowrap; width: 100%; justify-content: center; margin-top: 8px; box-sizing: border-box; }
    .upload-pr-label:hover { background: var(--s100); }
    .upload-pr-label i { font-size: 14px; }
    .upload-pr-label input { display: none; }

    /* Canvass details — items from the parent PR + the quotations gathered for it */
    .preview-toggle { display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; }
    .preview-toggle i.chev { transition: transform .18s; color: var(--s400); }
    .preview-toggle.open i.chev { transform: rotate(180deg); }
    .preview-body { display: none; margin-top: 10px; }
    .preview-body.open { display: block; }
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
    .sig-step.routing .sig-dot { border-style: dashed; }
    .sig-step.routing.done .sig-dot { border-style: solid; }
    .sig-label { font-size: 9px; font-weight: 700; text-align: center; color: var(--s400); margin-top: 5px; line-height: 1.3; max-width: 84px; }
    .sig-step.done .sig-label, .sig-step.active .sig-label { color: var(--s700); }
    /* 6 signatory steps wrap onto a second row after "VC" (5th step) at this
       panel's width, dropping "Chancellor" to its own line below — the
       connector line assumes a same-row next step, so it dangles rightward
       into nothing at the wrap point. Cut just that one line. */
    #sigTimeline .sig-step:nth-child(5)::after { display: none; }

    .btn-route { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; }
    .btn-route-fwd { background: #3b6d11; color: #fff; }
    .btn-route-fwd:hover:not(:disabled) { background: #2e560d; }
    .btn-route-ret { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
    .btn-route-ret:hover:not(:disabled) { background: var(--s200); }
    .btn-route:disabled { opacity: .5; cursor: not-allowed; }

    .remarks-textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 13px; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 60px; outline: none; transition: border-color .2s; line-height: 1.6; box-sizing: border-box; }
    .remarks-textarea:focus { border-color: var(--m); }
    .remarks-textarea::placeholder { color: var(--s300); }

    /* Inline Issue PO form — shown once the AOC is fully signed */
    .issue-po-box { border: 1.5px solid #c0dd97; background: #f3fbea; border-radius: 12px; padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; }
    .issue-po-box h4 { font-size: 12.5px; font-weight: 800; color: #2e560d; display: flex; align-items: center; gap: 6px; }
    .issue-po-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .issue-po-row .full { grid-column: 1 / -1; }
    .issue-po-box label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #475569; display: block; margin-bottom: 4px; }
    .issue-po-input { width: 100%; height: 38px; padding: 0 12px; border-radius: 8px; border: 1px solid var(--s200); font-size: 12.5px; font-family: 'Poppins', sans-serif; color: var(--s700); outline: none; box-sizing: border-box; }
    .issue-po-input:focus { border-color: var(--m); }
    .po-done-note { display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 700; color: #166534; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; }
    .po-done-note a { margin-left: auto; font-size: 11px; font-weight: 700; color: #1d4ed8; text-decoration: none; }

    /* Activity log */
    .activity-log { display: flex; flex-direction: column; gap: 1px; }
    .activity-item { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--s100); }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); flex-shrink: 0; margin-top: 5px; }
    .activity-item p { font-size: 12.5px; color: var(--s600); line-height: 1.6; }
    .activity-item time { font-size: 11px; color: var(--s400); display: block; margin-top: 2px; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 1200px) { .aoc-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-file-text"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Abstract of Canvass</h1>
            <p class="page-hdr-sub">Create AOCs from fully-signed PRs, route them through the signature chain, and issue the PO once fully signed.</p>
        </div>
    </div>

    {{-- ── Eligible PRs (ready for AOC creation) ── --}}
    @if(count($eligiblePrs) > 0)
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Ready for AOC</p>
                <h2 class="card-title">Fully-Signed PRs — Create AOC</h2>
            </div>
            <span class="count-chip">{{ count($eligiblePrs) }} PR{{ count($eligiblePrs) !== 1 ? 's' : '' }} waiting</span>
        </div>
        <div class="eligible-grid">
            @foreach($eligiblePrs as $pr)
            <div class="eligible-card">
                <div>
                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--s400);">{{ $pr['office'] }}</span>
                    <h4>{{ $pr['prNumber'] }}</h4>
                    <p>{{ Str::limit($pr['title'], 60) }}</p>
                </div>
                <button class="btn-route btn-route-fwd btn-create-aoc" data-url="{{ $pr['createUrl'] }}" data-pr-id="{{ $pr['id'] }}">
                    <i class="ti ti-file-plus"></i> For AOC
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="aoc-grid">

        {{-- ── Left: AOC list ── --}}
        <div class="card" style="padding-bottom: 22px;">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">AOC Tracker</p>
                    <h2 class="card-title">Abstract of Canvass List</h2>
                </div>
                <span class="count-chip" id="aocVisibleCount">{{ count($aocs) }} AOC{{ count($aocs) !== 1 ? 's' : '' }}</span>
            </div>

            @if(count($aocs) > 0)
                <div class="search-wrap" style="margin-bottom:14px;">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="search" id="aocSearch" placeholder="Search by AOC code, PR number, office, or title">
                </div>
            @endif

            @if(count($aocs) === 0)
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:180px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:32px;text-align:center;">
                    <i class="ti ti-file-off" style="font-size:38px;color:var(--s300);"></i>
                    <p style="font-size:13px;color:var(--s400);max-width:240px;line-height:1.6;">No AOCs created yet. Fully-signed PRs will appear above.</p>
                </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>AOC Code</th>
                            <th>PR No.</th>
                            <th>Description</th>
                            <th>Signatory Stage</th>
                            <th>PO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aocs as $aoc)
                            @php
                                $stageBadge = match($aoc['signatoryStage']) {
                                    'fully_signed' => 'badge-signed',
                                    'draft'        => 'badge-draft',
                                    default        => 'badge-routing',
                                };
                            @endphp
                            <tr data-aoc-row data-aoc-id="{{ $aoc['id'] }}" data-search="{{ strtolower($aoc['code'] . ' ' . $aoc['prNumber'] . ' ' . $aoc['office'] . ' ' . $aoc['title']) }}" tabindex="0">
                                <td style="font-size:12px;font-weight:600;color:var(--s600);white-space:nowrap;max-width:120px;overflow:hidden;text-overflow:ellipsis;">{{ $aoc['office'] }}</td>
                                <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $aoc['code'] }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $aoc['prNumber'] }}</td>
                                <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $aoc['title'] }}</td>
                                <td><span class="badge {{ $stageBadge }}" data-aoc-stage-badge="{{ $aoc['id'] }}">{{ $aoc['signatoryLabel'] }}</span></td>
                                <td>
                                    @if($aoc['hasPo'])
                                        <span class="badge badge-signed">{{ $aoc['poNumber'] }}</span>
                                    @else
                                        <span style="font-size:11px;color:var(--s400);">—</span>
                                    @endif
                                </td>
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
                    <p class="card-eyebrow">AOC Details</p>
                    <h2 class="card-title" id="detailAocCode">Select an AOC</h2>
                </div>
            </div>

            <div class="detail-empty" id="detailEmpty">
                <i class="ti ti-arrow-left"></i>
                <p>Select an AOC from the list to view details, route it forward, and issue the PO once fully signed.</p>
            </div>

            <div class="detail-content" id="detailContent">

                {{-- Uploaded AOC PDF preview --}}
                <div class="pdf-preview" id="pdfPreview">
                    <div class="pdf-placeholder">
                        <i class="ti ti-file-off"></i>
                        <span>No PDF attached</span>
                    </div>
                </div>
                <label class="upload-pr-label" id="uploadAocLabel">
                    <i class="ti ti-upload"></i>
                    <span id="uploadAocText">Upload Signed AOC PDF</span>
                    <input type="file" id="uploadAocInput" accept="application/pdf,.pdf">
                </label>

                <div class="detail-fields">
                    <div class="detail-field"><label>AOC Code</label><span id="fCode">—</span></div>
                    <div class="detail-field"><label>PR Number</label><span id="fPrNumber">—</span></div>
                    <div class="detail-field"><label>Office</label><span id="fOffice">—</span></div>
                    <div class="detail-field"><label>Estimated Amount</label><span id="fAmount">—</span></div>
                    <div class="detail-field full"><label>Title</label><span id="fTitle">—</span></div>
                    <div class="detail-field full"><label>Remarks on File</label><span id="fRemarks" style="white-space:pre-line;">—</span></div>
                </div>

                {{-- Canvass details: parent PR items + canvass quotations --}}
                <div id="previewSection">
                    <div class="preview-toggle open" id="previewToggle">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--s500);">Canvass Items &amp; Quotations</div>
                        <i class="ti ti-chevron-down chev"></i>
                    </div>
                    <div class="preview-body open" id="previewBody"></div>
                </div>

                {{-- Signatory timeline --}}
                <div>
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--s500);margin-bottom:10px;">Signature Routing</div>
                    <div class="sig-timeline" id="sigTimeline"></div>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <button class="btn-route btn-route-fwd" id="btnAdvance" type="button" disabled>
                            <i class="ti ti-circle-arrow-right"></i>
                            Route Forward
                        </button>
                        <button class="btn-route btn-route-ret" id="btnReturn" type="button" disabled>
                            <i class="ti ti-circle-arrow-left"></i>
                            Return
                        </button>
                    </div>
                    <div id="returnRemarks" style="display:none;margin-top:8px;">
                        <textarea class="remarks-textarea" id="returnRemarksInput" placeholder="Reason for returning (required)…" style="min-height:60px;"></textarea>
                        <button class="btn-route btn-route-ret" id="btnConfirmReturn" type="button" style="margin-top:6px;background:#a32d2d;color:#fff;">
                            <i class="ti ti-send"></i> Confirm Return
                        </button>
                    </div>
                </div>

                {{-- Issue PO — appears once the AOC is fully signed --}}
                <div id="issuePoSection" style="display:none;"></div>

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

<div class="pr-toast" id="aocToast"></div>

@endsection

<script type="application/json" id="aocData">@json($aocs)</script>
<script type="application/json" id="stagesData">@json($stageMeta)</script>
<script type="application/json" id="purchaseOrdersUrlData">@json(route('procurement-office.purchase-orders'))</script>

@push('scripts')
<script>
(function () {
    const allAocs      = JSON.parse(document.getElementById('aocData').textContent);
    const pageStageMeta= JSON.parse(document.getElementById('stagesData').textContent);
    const poListUrl    = JSON.parse(document.getElementById('purchaseOrdersUrlData').textContent);
    const rows          = document.querySelectorAll('[data-aoc-row]');
    const emptyEl        = document.getElementById('detailEmpty');
    const contentEl      = document.getElementById('detailContent');
    const titleEl        = document.getElementById('detailAocCode');
    const logEl           = document.getElementById('activityLog');
    const toastEl         = document.getElementById('aocToast');
    const btnAdvance      = document.getElementById('btnAdvance');
    const btnReturn       = document.getElementById('btnReturn');
    const returnRemarks   = document.getElementById('returnRemarks');
    const returnIn        = document.getElementById('returnRemarksInput');
    const btnConfirmRet   = document.getElementById('btnConfirmReturn');
    const previewSection  = document.getElementById('previewSection');
    const previewToggle   = document.getElementById('previewToggle');
    const previewBody     = document.getElementById('previewBody');
    const issuePoSection  = document.getElementById('issuePoSection');
    const csrfToken       = document.querySelector('meta[name="csrf-token"]').content;
    const aocSearch       = document.getElementById('aocSearch');
    const aocCount        = document.getElementById('aocVisibleCount');
    const uploadAocInput  = document.getElementById('uploadAocInput');
    const uploadAocText   = document.getElementById('uploadAocText');

    const logs = {};
    let activeAoc = null;
    let saving = false;

    function metaOf(aoc) { return pageStageMeta; }
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

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function renderPreview(aoc) {
        const items  = aoc.prItems || [];
        const quotes = aoc.quotations || [];

        let itemsHtml = '<p class="preview-empty-note">No items on file for the parent PR.</p>';
        if (items.length) {
            itemsHtml = `<table class="preview-items-table"><thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Total</th></tr></thead><tbody>`
                + items.map(i => `<tr><td>${escapeHtml(i.name)}</td><td>${i.quantity}</td><td>${escapeHtml(i.unit)}</td><td>₱${Number(i.unitCost).toLocaleString(undefined,{minimumFractionDigits:2})}</td><td>₱${Number(i.totalCost).toLocaleString(undefined,{minimumFractionDigits:2})}</td></tr>`).join('')
                + `</tbody><tfoot><tr><td colspan="4">Total</td><td>₱${Number(aoc.prTotal || 0).toLocaleString(undefined,{minimumFractionDigits:2})}</td></tr></tfoot></table>`;
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
    }

    function renderIssuePo(aoc) {
        if (aoc.signatoryStage !== 'fully_signed') {
            issuePoSection.style.display = 'none';
            issuePoSection.innerHTML = '';
            return;
        }

        issuePoSection.style.display = '';

        if (aoc.hasPo) {
            issuePoSection.innerHTML = `
                <div class="po-done-note">
                    <i class="ti ti-circle-check"></i> PO Issued — ${escapeHtml(aoc.poNumber || '')}
                    <a href="${poListUrl}">View in Purchase Orders →</a>
                </div>`;
            return;
        }

        issuePoSection.innerHTML = `
            <div class="issue-po-box">
                <h4><i class="ti ti-shopping-cart"></i> Fully Signed — Issue Purchase Order</h4>
                <div class="issue-po-row">
                    <div class="full">
                        <label>Supplier Name *</label>
                        <input type="text" class="issue-po-input" id="poSupplierName" placeholder="Enter supplier name…" value="${escapeHtml(aoc.supplierName || '')}">
                    </div>
                    <div class="full">
                        <label>Supplier Address</label>
                        <input type="text" class="issue-po-input" id="poSupplierAddr" placeholder="Optional address…">
                    </div>
                    <div>
                        <label>Total Amount (₱) *</label>
                        <input type="number" class="issue-po-input" id="poAmount" step="0.01" min="0" value="${aoc.prTotal || ''}">
                    </div>
                    <div>
                        <label>Expected Delivery Date</label>
                        <input type="date" class="issue-po-input" id="poDeliveryDate">
                    </div>
                </div>
                <button class="btn-route btn-route-fwd" id="btnIssuePo" type="button">
                    <i class="ti ti-file-invoice"></i> For PO
                </button>
            </div>`;

        document.getElementById('btnIssuePo').addEventListener('click', async () => {
            const supplierName = document.getElementById('poSupplierName').value.trim();
            const totalAmount  = document.getElementById('poAmount').value;
            if (!supplierName || !totalAmount) { showToast('Supplier name and amount are required.', true); return; }

            const btn = document.getElementById('btnIssuePo');
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Issuing…';

            try {
                const resp = await fetch(aoc.issuePoUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        supplier_name:          supplierName,
                        supplier_address:       document.getElementById('poSupplierAddr').value.trim(),
                        total_amount:           parseFloat(totalAmount),
                        expected_delivery_date: document.getElementById('poDeliveryDate').value || null,
                    }),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    aoc.hasPo = true;
                    const badge = document.querySelector(`[data-aoc-row][data-aoc-id="${aoc.id}"]`)?.querySelector('td:last-child');
                    if (badge) badge.innerHTML = '<span class="badge badge-signed">PO Issued</span>';
                    showToast('Purchase Order issued — routing continues on the PO.');
                    renderIssuePo(aoc);
                } else {
                    showToast(json.error || 'Failed to issue PO.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-file-invoice"></i> For PO';
                }
            } catch {
                showToast('Network error.', true);
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-file-invoice"></i> For PO';
            }
        });
    }

    function renderLog(aocId) {
        const entries = logs[aocId] || [];
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

    function updateTimeline(aoc) {
        // Excludes 'draft' (not a signing step) and the terminal 'fully_signed'
        // marker (not a real step either) — without dropping the latter,
        // activeIdx lands on it once reached and that last dot renders 'active'
        // (in-progress) forever instead of 'done' like the rest.
        const timeline = document.getElementById('sigTimeline');
        const meta     = metaOf(aoc).filter(m => !['draft', 'fully_signed'].includes(m.key));
        const activeIdx = meta.findIndex(m => m.key === aoc.signatoryStage);
        const isFullySigned = aoc.signatoryStage === 'fully_signed';
        timeline.innerHTML = meta.map((m, i) => {
            const state   = (isFullySigned || i < activeIdx) ? ' done' : (i === activeIdx ? ' active' : '');
            const routing = m.type === 'routing' ? ' routing' : '';
            return `<div class="sig-step${routing}${state}"><div class="sig-dot"></div><span class="sig-label">${m.label}</span></div>`;
        }).join('');
    }

    function advanceBtnHtml(aoc) {
        if (aoc.signatoryStage === 'draft') {
            const first = metaOf(aoc)[1];
            return `<i class="ti ti-circle-arrow-right"></i> Route to ${first ? first.label : 'End User'}`;
        }
        const meta = metaOf(aoc).find(m => m.key === aoc.signatoryStage);
        return meta && meta.type === 'routing'
            ? '<i class="ti ti-circle-arrow-right"></i> Mark Forwarded'
            : '<i class="ti ti-circle-arrow-right"></i> Mark Signed';
    }

    function updateRoutingButtons(aoc) {
        const isDraft  = aoc.signatoryStage === 'draft';
        const isSigned = aoc.signatoryStage === 'fully_signed';
        btnAdvance.disabled  = isSigned;
        btnAdvance.innerHTML = advanceBtnHtml(aoc);
        btnReturn.disabled   = isDraft || isSigned;
        returnRemarks.style.display = 'none';
        returnIn.value = '';
    }

    function updateStageBadge(aocId, label, stage) {
        const badge = document.querySelector(`[data-aoc-stage-badge="${aocId}"]`);
        if (!badge) return;
        badge.textContent = label;
        badge.className = 'badge ' + (stage === 'fully_signed' ? 'badge-signed' : stage === 'draft' ? 'badge-draft' : 'badge-routing');
    }

    function openAoc(aoc) {
        activeAoc = aoc;
        rows.forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-aoc-id="${aoc.id}"]`)?.classList.add('selected');

        titleEl.textContent = aoc.code;
        document.getElementById('fCode').textContent     = aoc.code;
        document.getElementById('fPrNumber').textContent = aoc.prNumber;
        document.getElementById('fOffice').textContent   = aoc.office;
        document.getElementById('fAmount').textContent   = '₱' + Number(aoc.prTotal || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('fTitle').textContent    = aoc.title;
        document.getElementById('fRemarks').textContent  = aoc.remarks;

        const pdfEl = document.getElementById('pdfPreview');
        pdfEl.innerHTML = aoc.pdfFile
            ? `<iframe src="/storage/${aoc.pdfFile}" title="AOC Document"></iframe>`
            : `<div class="pdf-placeholder"><i class="ti ti-file-off"></i><span>No PDF attached</span></div>`;
        uploadAocText.textContent = aoc.pdfFile ? 'Re-upload PDF' : 'Upload Signed AOC PDF';

        renderPreview(aoc);

        if (!logs[aoc.id]) {
            logs[aoc.id] = (aoc.signatureLogs || []).map(l => ({
                text: `<strong>${l.display}</strong>` + (l.by && l.by !== '—' ? ` by ${l.by}` : '') + (l.remarks ? ` &mdash; ${l.remarks}` : ''),
                time: l.at,
            }));
        }

        updateTimeline(aoc);
        updateRoutingButtons(aoc);
        renderIssuePo(aoc);

        emptyEl.style.display = 'none';
        contentEl.classList.add('visible');
        renderLog(aoc.id);
    }

    rows.forEach(row => {
        row.addEventListener('click', () => {
            const aoc = allAocs.find(a => String(a.id) === row.dataset.aocId);
            if (aoc) openAoc(aoc);
        });
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
        });
    });

    previewToggle.addEventListener('click', () => {
        previewToggle.classList.toggle('open');
        previewBody.classList.toggle('open');
    });

    aocSearch?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const match = !q || (row.dataset.search ?? '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (aocCount) aocCount.textContent = visible + (visible === 1 ? ' AOC' : ' AOCs');
    });

    /* ── Route Forward ── */
    async function doAdvance() {
        if (!activeAoc || saving) return;
        saving = true;
        btnAdvance.disabled = true;
        btnAdvance.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Routing…';

        try {
            const resp = await fetch(activeAoc.advanceUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({}),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activeAoc.signatoryStage = json.signatoryStage;
                activeAoc.signatoryLabel = json.signatoryLabel;
                updateTimeline(activeAoc);
                updateRoutingButtons(activeAoc);
                updateStageBadge(activeAoc.id, json.signatoryLabel, json.signatoryStage);
                renderIssuePo(activeAoc);
                logs[activeAoc.id].push({ text: `Routed forward → <strong>${json.signatoryLabel}</strong>`, time: nowStr() });
                renderLog(activeAoc.id);
                showToast(json.currentStageType === 'routing' ? 'AOC forwarded.' : 'AOC signed and routed forward.');
            } else {
                showToast(json.error || 'Failed to route AOC.', true);
            }
        } catch { showToast('Network error.', true); }
        finally {
            saving = false;
            if (activeAoc) {
                btnAdvance.disabled  = activeAoc.signatoryStage === 'fully_signed';
                btnAdvance.innerHTML = advanceBtnHtml(activeAoc);
            }
        }
    }

    btnAdvance.addEventListener('click', doAdvance);

    /* ── Return (toggle panel) ── */
    btnReturn.addEventListener('click', () => {
        returnRemarks.style.display = returnRemarks.style.display === 'none' ? '' : 'none';
    });

    btnConfirmRet.addEventListener('click', async () => {
        if (!activeAoc || saving) return;
        const reason = returnIn.value.trim();
        if (!reason) { showToast('Please provide a reason for returning.', true); return; }
        saving = true;
        btnConfirmRet.disabled = true;

        try {
            const resp = await fetch(activeAoc.returnUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ remarks: reason }),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activeAoc.signatoryStage = json.signatoryStage;
                activeAoc.signatoryLabel = json.signatoryLabel;
                updateTimeline(activeAoc);
                updateRoutingButtons(activeAoc);
                updateStageBadge(activeAoc.id, json.signatoryLabel, json.signatoryStage);
                renderIssuePo(activeAoc);
                returnRemarks.style.display = 'none';
                returnIn.value = '';
                logs[activeAoc.id].push({ text: `<strong>Returned to ${json.signatoryLabel}</strong> &mdash; ${reason}`, time: nowStr() });
                renderLog(activeAoc.id);
                showToast('AOC returned one step — now at ' + json.signatoryLabel + '.');
            } else {
                showToast(json.error || 'Failed to return AOC.', true);
            }
        } catch { showToast('Network error.', true); }
        finally { saving = false; btnConfirmRet.disabled = false; }
    });

    /* ── Create AOC (from eligible PRs) ── */
    document.querySelectorAll('.btn-create-aoc').forEach(btn => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Creating…';
            try {
                const resp = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast('AOC created. Reloading…');
                    setTimeout(() => location.reload(), 900);
                } else {
                    showToast(json.error || 'Failed to create AOC.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-file-plus"></i> For AOC';
                }
            } catch { showToast('Network error.', true); btn.disabled = false; btn.innerHTML = '<i class="ti ti-file-plus"></i> For AOC'; }
        });
    });

    /* ── Upload signed AOC PDF ── */
    uploadAocInput.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file || !activeAoc) return;
        const origText = uploadAocText.textContent;
        uploadAocText.textContent = 'Uploading…';
        this.disabled = true;
        const fd = new FormData();
        fd.append('file', file);
        try {
            const resp = await fetch(activeAoc.uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activeAoc.pdfFile = json.filePath;
                document.getElementById('pdfPreview').innerHTML =
                    `<iframe src="/storage/${json.filePath}" title="AOC Document"></iframe>`;
                uploadAocText.textContent = 'Re-upload PDF';
                showToast('AOC PDF uploaded successfully.');
            } else {
                uploadAocText.textContent = origText;
                showToast(json.message || 'Upload failed.', true);
            }
        } catch {
            uploadAocText.textContent = origText;
            showToast('Network error during upload.', true);
        }
        this.disabled = false;
        this.value = '';
    });

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }

    // ── Auto-refresh ── another signatory's action (mobile or elsewhere on
    // web) otherwise wouldn't show up here without a manual reload. Skip while
    // an upload is in flight or a return remark is being typed.
    setInterval(() => {
        if (saving) return;
        const remarks = document.getElementById('returnRemarksInput');
        if (remarks && remarks.value.trim()) return;
        window.location.reload();
    }, 45000);
})();
</script>
@endpush
