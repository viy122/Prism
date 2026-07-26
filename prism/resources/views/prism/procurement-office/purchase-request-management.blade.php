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
    .badge-completed    { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-in-progress  { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-pending      { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-delayed      { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-signed       { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-routing      { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-draft        { background: var(--s100); color: var(--s600); border: 1px solid var(--s200); }

    /* Signatory timeline */
    .sig-timeline { display: flex; align-items: center; gap: 0; margin-bottom: 4px; }
    .sig-step { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
    .sig-step:not(:last-child)::after { content: ''; position: absolute; top: 10px; left: 50%; width: 100%; height: 2px; background: var(--s200); z-index: 0; }
    .sig-step.done::after { background: #3b6d11; }
    .sig-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--s300); background: var(--white); z-index: 1; position: relative; transition: all .2s; }
    .sig-step.done .sig-dot { background: #3b6d11; border-color: #3b6d11; }
    .sig-step.active .sig-dot { background: var(--m); border-color: var(--m); box-shadow: 0 0 0 3px rgba(139,26,28,.2); }
    .sig-label { font-size: 9px; font-weight: 700; text-align: center; color: var(--s400); margin-top: 5px; line-height: 1.3; max-width: 84px; }
    .sig-step.done .sig-label, .sig-step.active .sig-label { color: var(--s700); }
    .sig-step.routing .sig-dot { border-style: dashed; }
    .sig-step.routing.done .sig-dot { border-style: solid; }

    .btn-route { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; }
    .btn-route-fwd { background: #3b6d11; color: #fff; }
    .btn-route-fwd:hover:not(:disabled) { background: #2e560d; }
    .btn-route-ret { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
    .btn-route-ret:hover:not(:disabled) { background: var(--s200); }
    .btn-route:disabled { opacity: .5; cursor: not-allowed; }

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

    /* PDF preview */
    .pdf-preview { border-radius: 12px; border: 1px solid var(--s200); background: var(--s50); overflow: hidden; aspect-ratio: 8.5 / 11; display: flex; align-items: center; justify-content: center; position: relative; }
    .pdf-preview iframe { width: 100%; height: 100%; border: none; }
    .pdf-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 100%; color: var(--s400); }
    .pdf-placeholder i { font-size: 42px; color: var(--s300); }
    .pdf-placeholder span { font-size: 12px; font-weight: 600; }

    /* PDF upload */
    .upload-pr-label { display: inline-flex; align-items: center; gap: 7px; border: 1.5px dashed var(--s300); border-radius: 9px; background: var(--s50); padding: 7px 14px; font-size: 12px; font-weight: 700; color: var(--s600); cursor: pointer; transition: background .15s; white-space: nowrap; width: 100%; justify-content: center; margin-top: 8px; }
    .upload-pr-label:hover { background: var(--s100); }
    .upload-pr-label i { font-size: 14px; }
    .upload-pr-label input { display: none; }

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

    /* Import BSU PDF button */
    .btn-import-bsu { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; background: #0f5288; color: #fff; transition: background .15s; white-space: nowrap; }
    .btn-import-bsu:hover { background: #0a3d68; }
    .btn-import-bsu i { font-size: 15px; }

    /* Tracking status hover tooltip (full text, since the select itself truncates) */
    .tracking-tooltip-wrap { position: relative; display: inline-block; max-width: 220px; }
    .tracking-tooltip-text {
        display: none; position: absolute; bottom: calc(100% + 6px); left: 0; z-index: 60;
        background: #0f172a; color: #fff; padding: 6px 10px; border-radius: 6px;
        font-size: 11px; font-weight: 600; white-space: nowrap; box-shadow: 0 4px 14px rgba(15,23,42,.25);
        pointer-events: none;
    }
    .tracking-tooltip-wrap:hover .tracking-tooltip-text { display: block; }

    /* Import modal overlay */
    .import-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 24px; }
    .import-modal-overlay.hidden { display: none; }
    .import-modal { background: #fff; border-radius: 18px; width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 64px rgba(15,23,42,.22); display: flex; flex-direction: column; }
    .import-modal-hdr { display: flex; align-items: center; justify-content: space-between; padding: 22px 26px 0; }
    .import-modal-hdr h2 { font-size: 17px; font-weight: 800; color: #0f172a; letter-spacing: -.2px; }
    .import-modal-hdr p { font-size: 12px; color: #64748b; margin-top: 3px; }
    .import-modal-body { padding: 20px 26px 26px; display: flex; flex-direction: column; gap: 16px; }
    .import-close { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0; }
    .import-close:hover { background: #e2e8f0; }

    /* Drop zone */
    .import-drop { border: 2px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; padding: 32px 24px; display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; }
    .import-drop:hover, .import-drop.drag-over { border-color: #0f5288; background: #e8f4fd; }
    .import-drop i { font-size: 36px; color: #94a3b8; }
    .import-drop p { font-size: 13px; color: #64748b; line-height: 1.5; }
    .import-drop span { font-size: 11px; color: #94a3b8; }
    .import-drop input { display: none; }

    .import-field { display: flex; flex-direction: column; gap: 5px; }
    .import-field label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: #64748b; }
    .import-input { height: 40px; padding: 0 12px; border: 1px solid #e2e8f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #334155; outline: none; width: 100%; box-sizing: border-box; background: #fff; transition: border-color .15s; }
    .import-input:focus { border-color: #0f5288; }
    .import-textarea { padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #334155; outline: none; width: 100%; box-sizing: border-box; resize: vertical; min-height: 72px; line-height: 1.5; transition: border-color .15s; }
    .import-textarea:focus { border-color: #0f5288; }
    .import-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .import-divider { border: none; border-top: 1px solid #e2e8f0; margin: 0; }

    /* Items table in modal */
    .import-items-hdr { display: flex; align-items: center; justify-content: space-between; }
    .import-items-hdr span { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: #64748b; }
    .btn-add-item { display: inline-flex; align-items: center; gap: 5px; height: 30px; padding: 0 12px; border-radius: 7px; font-size: 11px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: 1px solid #0f5288; background: #e8f4fd; color: #0f5288; }
    .btn-add-item:hover { background: #cce4f7; }
    .import-items-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .import-items-table th { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #94a3b8; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .import-items-table td { padding: 5px 4px; vertical-align: middle; }
    .import-items-table .item-input { height: 34px; padding: 0 8px; border: 1px solid #e2e8f0; border-radius: 7px; font-size: 12px; font-family: 'Poppins', sans-serif; color: #334155; outline: none; width: 100%; box-sizing: border-box; }
    .import-items-table .item-input:focus { border-color: #0f5288; }
    .btn-del-item { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #fecaca; background: #fef2f2; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #ef4444; flex-shrink: 0; }
    .btn-del-item:hover { background: #fee2e2; }

    .import-no-text-warn { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 9px; padding: 12px 14px; font-size: 12px; color: #9a3412; line-height: 1.5; }

    .btn-confirm-import { display: flex; align-items: center; justify-content: center; gap: 8px; height: 44px; border-radius: 10px; background: #0f5288; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: background .15s; width: 100%; }
    .btn-confirm-import:hover:not(:disabled) { background: #0a3d68; }
    .btn-confirm-import:disabled { opacity: .6; cursor: not-allowed; }
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
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <button type="button" class="btn-import-bsu" id="btnOpenImport">
                        <i class="ti ti-file-import"></i> Import from BSU PDF
                    </button>
                    <span class="count-chip" id="prVisibleCount">{{ count($purchaseRequests) }} PR{{ count($purchaseRequests) !== 1 ? 's' : '' }}</span>
                </div>
            </div>

            @if(count($purchaseRequests) > 0)
                <div class="search-wrap" style="margin-bottom:14px;">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="search" id="prSearch" placeholder="Search by PR number, item, office, or remarks">
                </div>
            @endif

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
                            <th>Signatory Stage</th>
                            <th>Tracking Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseRequests as $pr)
                            @php
                                $stageBadge = match($pr['signatoryStage']) {
                                    'fully_signed' => 'badge-signed',
                                    'draft'        => 'badge-draft',
                                    default        => 'badge-routing',
                                };
                            @endphp
                            <tr data-pr-row data-pr-id="{{ $pr['id'] }}" data-search="{{ strtolower($pr['prNumber'] . ' ' . $pr['item'] . ' ' . $pr['office'] . ' ' . $pr['remarks'] . ' ' . $pr['signatoryLabel']) }}" tabindex="0">
                                <td style="font-size:12px;font-weight:600;color:var(--s600);white-space:nowrap;max-width:120px;overflow:hidden;text-overflow:ellipsis;">{{ $pr['office'] }}</td>
                                <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">{{ $pr['prNumber'] }}</td>
                                <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $pr['item'] }}</td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $pr['dateSubmitted'] }}</td>
                                <td>
                                    <span class="badge {{ $stageBadge }}" data-sig-badge="{{ $pr['id'] }}">{{ $pr['signatoryLabel'] }}</span>
                                </td>
                                <td onclick="event.stopPropagation()">
                                    <div class="tracking-tooltip-wrap">
                                        <select class="tracking-status-select" data-tracking-select="{{ $pr['id'] }}" data-tracking-url="{{ $pr['trackingStatusUrl'] }}" data-prev-value="{{ !empty($pr['trackingStatus']['override']) ? $pr['trackingStatus']['key'] : '' }}" style="font-size:12px;font-weight:600;padding:5px 8px;border-radius:8px;border:1px solid var(--s300);background:var(--s50);color:var(--s700);max-width:220px;">
                                            <option value="" @selected(empty($pr['trackingStatus']['override']))>{{ $pr['trackingStatusAuto']['label'] }}</option>
                                            @foreach ($trackingStageOptions as $opt)
                                                <option value="{{ $opt['key'] }}" @selected(!empty($pr['trackingStatus']['override']) && $pr['trackingStatus']['key'] === $opt['key'])>{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <span class="tracking-tooltip-text">{{ $pr['trackingStatus']['label'] }}</span>
                                    </div>
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
                <label class="upload-pr-label" id="uploadPrLabel">
                    <i class="ti ti-upload"></i>
                    <span id="uploadPrText">Upload Signed PR PDF</span>
                    <input type="file" id="uploadPrInput" accept="application/pdf,.pdf">
                </label>

                {{-- Extracted fields --}}
                <div class="detail-fields">
                    <div class="detail-field"><label>Office</label><span id="fOffice">—</span></div>
                    <div class="detail-field"><label>PR Number</label><span id="fPrNumber">—</span></div>
                    <div class="detail-field full"><label>Description / Item</label><span id="fItem">—</span></div>
                    <div class="detail-field"><label>Date Submitted</label><span id="fDate">—</span></div>
                    <div class="detail-field"><label>Signatory Stage</label><span id="fSigLabel">—</span></div>
                    <div class="detail-field full"><label>Remarks on File</label><span id="fRemarks" style="white-space:pre-line;">—</span></div>
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
                        <button class="btn-save" id="btnConfirmReturn" type="button" style="margin-top:6px;background:#a32d2d;">
                            <i class="ti ti-send"></i> Confirm Return
                        </button>
                    </div>
                    {{-- 3rd/4th signers are Accounting + Vice Chancellor in flexible order --}}
                    <div id="thirdSignerPanel" style="display:none;margin-top:8px;">
                        <p style="font-size:11px;font-weight:700;color:var(--s500);margin-bottom:6px;">Who signed as the 3rd signatory?</p>
                        <div style="display:flex;gap:8px;">
                            <button class="btn-route btn-route-fwd" id="btnThirdAcct" type="button"><i class="ti ti-calculator"></i> Accounting</button>
                            <button class="btn-route btn-route-fwd" id="btnThirdVc" type="button"><i class="ti ti-user-check"></i> Vice Chancellor</button>
                        </div>
                    </div>
                </div>

                {{-- Update controls --}}
                <div class="status-control">
                    <label>Update Processing Status</label>
                    <select class="status-select" id="statusSelect">
                        <optgroup label="Receiving">
                            <option value="new">New</option>
                            <option value="approved_pr_received">Approved PR Received</option>
                            <option value="forwarded_to_bac">Approved PR Received – Forwarded to BAC</option>
                            <option value="forwarded_to_rgo">Approved PR Received – Forwarded to RGO</option>
                            <option value="forwarded_to_end_user">Approved PR Received – Forwarded to End-User</option>
                        </optgroup>
                        <optgroup label="Processing">
                            <option value="canvassing">Canvassing</option>
                            <option value="abstract_of_canvass_made">Abstract of Canvass Made</option>
                            <option value="for_po">For PO</option>
                            <option value="po_made">PO Made</option>
                            <option value="po_confirmed">PO Confirmed</option>
                            <option value="for_alobs">For ALOBS</option>
                        </optgroup>
                        <optgroup label="Special">
                            <option value="for_reimbursement">For Reimbursement</option>
                            <option value="for_consolidation">For CONSOLIDATION</option>
                        </optgroup>
                        <optgroup label="Closed">
                            <option value="pr_denied">PR Denied</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="cancelled_system_error">Cancelled – System Error</option>
                        </optgroup>
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

{{-- Import BSU PDF Modal --}}
<div class="import-modal-overlay hidden" id="importModalOverlay">
    <div class="import-modal">
        <div class="import-modal-hdr">
            <div>
                <h2>Import PR from BSU PDF</h2>
                <p>Upload a PR PDF from the BSU central system to extract and import its data.</p>
            </div>
            <button type="button" class="import-close" id="btnCloseImport"><i class="ti ti-x"></i></button>
        </div>
        <div class="import-modal-body">

            {{-- Step 1: Upload --}}
            <div id="importStep1">
                <label class="import-drop" id="importDropZone">
                    <i class="ti ti-file-upload"></i>
                    <p><strong>Click to select</strong> or drag and drop a PR PDF</p>
                    <span>BatStateU-FO-PRO-02 format &middot; Max 20 MB</span>
                    <input type="file" id="importFileInput" accept="application/pdf,.pdf">
                </label>
                <div id="importFileName" style="font-size:12px;color:#64748b;margin-top:8px;text-align:center;display:none;"></div>
                <button type="button" class="btn-confirm-import" id="btnExtract" style="margin-top:14px;" disabled>
                    <i class="ti ti-scan"></i> Extract Fields from PDF
                </button>
            </div>

            {{-- Step 2: Review (hidden until extraction done) --}}
            <div id="importStep2" style="display:none;">
                <div id="importNoTextWarn" class="import-no-text-warn" style="display:none;">
                    <strong>Could not read text from this PDF.</strong> It may be a scanned/image PDF.
                    Please fill in the fields below manually before confirming the import.
                </div>

                <hr class="import-divider">

                <div class="import-row">
                    <div class="import-field">
                        <label>PR Number</label>
                        <input type="text" class="import-input" id="impPrNumber" placeholder="e.g. PR-2024-001">
                    </div>
                    <div class="import-field">
                        <label>Date</label>
                        <input type="text" class="import-input" id="impDate" placeholder="e.g. January 15, 2024">
                    </div>
                </div>

                <div class="import-field">
                    <label>Requesting Office / Department</label>
                    <select class="import-input" id="impOfficeId">
                        <option value="">— Select Office —</option>
                        @foreach($offices as $office)
                            <option value="{{ $office['id'] }}">{{ $office['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="import-field">
                    <label>Project Title / Name of Project</label>
                    <input type="text" class="import-input" id="impTitle" placeholder="Describe the procurement project">
                </div>

                <div class="import-field">
                    <label>Purpose</label>
                    <textarea class="import-textarea" id="impPurpose" placeholder="State the purpose of this purchase request…"></textarea>
                </div>

                <div class="import-field">
                    <label>Total Amount (₱)</label>
                    <input type="number" class="import-input" id="impTotal" placeholder="0.00" min="0" step="0.01">
                </div>

                <hr class="import-divider">

                <div>
                    <div class="import-items-hdr" style="margin-bottom:10px;">
                        <span>Items</span>
                        <button type="button" class="btn-add-item" id="btnAddItem">
                            <i class="ti ti-plus"></i> Add Row
                        </button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="import-items-table">
                            <thead>
                                <tr>
                                    <th style="width:38%;">Item Description</th>
                                    <th style="width:12%;">Qty</th>
                                    <th style="width:15%;">Unit</th>
                                    <th style="width:18%;">Unit Cost (₱)</th>
                                    <th style="width:12%;"></th>
                                </tr>
                            </thead>
                            <tbody id="importItemsBody"></tbody>
                        </table>
                    </div>
                    <p style="font-size:11px;color:#94a3b8;margin-top:6px;">Add the items from the PR. These will be used for canvassing and AOC later.</p>
                </div>

                <hr class="import-divider">

                <button type="button" class="btn-confirm-import" id="btnConfirmImport">
                    <i class="ti ti-check"></i> Confirm Import
                </button>
                <button type="button" id="btnBackToStep1" style="display:block;margin-top:8px;width:100%;height:38px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#64748b;font-size:12px;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif;">
                    &larr; Back / Upload Different PDF
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Toast --}}
<div class="pr-toast" id="prToast"></div>

@endsection

<script type="application/json" id="prData">@json($purchaseRequests)</script>
<script type="application/json" id="stagesData">@json($stageMeta)</script>
<script type="application/json" id="importPdfUrlData">@json($importPdfUrl)</script>
<script type="application/json" id="importConfirmUrlData">@json($importConfirmUrl)</script>

@push('scripts')
<script>
(function () {
    const allPrs       = JSON.parse(document.getElementById('prData').textContent);
    const rows         = document.querySelectorAll('[data-pr-row]');
    const emptyEl      = document.getElementById('detailEmpty');
    const contentEl    = document.getElementById('detailContent');
    const titleEl      = document.getElementById('detailPrNumber');
    const officeChip   = document.getElementById('detailPrOffice');
    const statusSel    = document.getElementById('statusSelect');
    const remarksIn    = document.getElementById('remarksInput');
    const btnSave      = document.getElementById('btnSave');
    const logEl        = document.getElementById('activityLog');
    const toastEl      = document.getElementById('prToast');
    const btnAdvance       = document.getElementById('btnAdvance');
    const btnReturn        = document.getElementById('btnReturn');
    const returnRemarks    = document.getElementById('returnRemarks');
    const returnIn         = document.getElementById('returnRemarksInput');
    const btnConfirmRet    = document.getElementById('btnConfirmReturn');
    const uploadPrInput    = document.getElementById('uploadPrInput');
    const uploadPrText     = document.getElementById('uploadPrText');
    const csrfToken        = document.querySelector('meta[name="csrf-token"]').content;

    const pageStageMeta    = JSON.parse(document.getElementById('stagesData').textContent);
    const thirdSignerPanel = document.getElementById('thirdSignerPanel');

    /* Stage-meta helpers — per-PR meta carries resolved 3rd/4th signer labels */
    function metaOf(pr)     { return pr.stageMeta || pageStageMeta; }
    function stageMetaFor(pr, key) { return metaOf(pr).find(m => m.key === key) || null; }
    function nextStageKey(pr) {
        const keys = metaOf(pr).map(m => m.key);
        const i    = keys.indexOf(pr.signatoryStage);
        return (i >= 0 && i < keys.length - 1) ? keys[i + 1] : null;
    }

    const logs = {};
    let activePr = null;
    let saving = false;

    /* ── Helpers ── */
    function displayStatus(val) {
        const map = {
            new: 'New',
            approved_pr_received: 'Approved PR Received',
            forwarded_to_bac: 'Approved PR Received – Forwarded to BAC',
            forwarded_to_rgo: 'Approved PR Received – Forwarded to RGO',
            forwarded_to_end_user: 'Approved PR Received – Forwarded to End-User',
            canvassing: 'Canvassing',
            abstract_of_canvass_made: 'Abstract of Canvass Made',
            for_po: 'For PO',
            po_made: 'PO Made',
            po_confirmed: 'PO Confirmed',
            for_alobs: 'For ALOBS',
            for_reimbursement: 'For Reimbursement',
            for_consolidation: 'For CONSOLIDATION',
            pr_denied: 'PR Denied',
            cancelled: 'Cancelled',
            cancelled_system_error: 'Cancelled – System Error',
        };
        return map[val] ?? val;
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

    function updateTimeline(pr) {
        // Timeline shows every stage after 'draft'; routing steps get dashed dots
        const timeline = document.getElementById('sigTimeline');
        const meta     = metaOf(pr).slice(1);
        const activeIdx = meta.findIndex(m => m.key === pr.signatoryStage);
        timeline.innerHTML = meta.map((m, i) => {
            const state = i < activeIdx ? ' done' : (i === activeIdx ? ' active' : '');
            const routing = m.type === 'routing' ? ' routing' : '';
            return `<div class="sig-step${routing}${state}"><div class="sig-dot"></div><span class="sig-label">${m.label}</span></div>`;
        }).join('');
    }

    function advanceBtnHtml(pr) {
        if (pr.signatoryStage === 'draft') {
            const first = metaOf(pr)[1];
            return `<i class="ti ti-circle-arrow-right"></i> Route to ${first ? first.label : 'End User'}`;
        }
        const meta = stageMetaFor(pr, pr.signatoryStage);
        return meta && meta.type === 'routing'
            ? '<i class="ti ti-circle-arrow-right"></i> Mark Forwarded'
            : '<i class="ti ti-circle-arrow-right"></i> Mark Signed';
    }

    function updateRoutingButtons(pr) {
        const isDraft    = pr.signatoryStage === 'draft';
        const isSigned   = pr.signatoryStage === 'fully_signed';
        btnAdvance.disabled = isSigned;
        btnAdvance.innerHTML = advanceBtnHtml(pr);
        btnReturn.disabled  = isDraft || isSigned;
        returnRemarks.style.display = 'none';
        returnIn.value = '';
        thirdSignerPanel.style.display = 'none';
    }

    function updateSigBadge(prId, label, stage) {
        const badge = document.querySelector(`[data-sig-badge="${prId}"]`);
        if (!badge) return;
        badge.textContent = label;
        badge.className = 'badge ' + (stage === 'fully_signed' ? 'badge-signed' : stage === 'draft' ? 'badge-draft' : 'badge-routing');
    }

    /* ── Open PR ── */
    function openPr(pr) {
        activePr = pr;

        rows.forEach(r => r.classList.remove('selected'));
        document.querySelector(`[data-pr-id="${pr.id}"]`).classList.add('selected');

        titleEl.textContent      = pr.prNumber;
        officeChip.textContent   = pr.office;
        officeChip.style.display = '';

        document.getElementById('fOffice').textContent   = pr.office;
        document.getElementById('fPrNumber').textContent = pr.prNumber;
        document.getElementById('fItem').textContent     = pr.item;
        document.getElementById('fDate').textContent     = pr.dateSubmitted;
        document.getElementById('fSigLabel').textContent = pr.signatoryLabel;
        document.getElementById('fRemarks').textContent  = pr.remarks !== '—' ? pr.remarks : '—';

        statusSel.value = pr.currentStatus ?? 'new';
        remarksIn.value = '';

        const pdfEl = document.getElementById('pdfPreview');
        pdfEl.innerHTML = pr.pdfFile
            ? `<iframe src="/storage/${pr.pdfFile}" title="PR Document"></iframe>`
            : `<div class="pdf-placeholder"><i class="ti ti-file-off"></i><span>No PDF attached</span></div>`;
        uploadPrText.textContent = pr.pdfFile ? 'Re-upload PDF' : 'Upload Signed PR PDF';

        if (!logs[pr.id]) {
            logs[pr.id] = (pr.activityLog || []).map(e => ({
                text: `Status: <strong>${e.status}</strong>` + (e.remarks && e.remarks !== '—' ? ` &mdash; ${e.remarks}` : ''),
                time: e.timestamp,
            }));
            (pr.signatureLogs || []).forEach(l => {
                let text = `<strong>${l.display}</strong>` + (l.by && l.by !== '—' ? ` by ${l.by}` : '') + (l.remarks ? ` &mdash; ${l.remarks}` : '');
                if (l.photoUrl) {
                    text += `<br><a href="${l.photoUrl}" target="_blank" rel="noopener"><img src="${l.photoUrl}" alt="Signed document (signature blurred)" style="margin-top:6px;max-width:120px;border-radius:8px;border:1px solid #e2e8f0;"></a> <span style="font-size:10px;color:#64748b;">signature blurred for privacy</span>`;
                } else if (l.photoStatus === 'pending' || l.photoStatus === 'failed') {
                    text += `<br><span style="font-size:11px;color:#854f0b;">photo withheld — processing</span>`
                        + (l.reprocessUrl ? ` <button type="button" class="sig-reprocess-btn" data-url="${l.reprocessUrl}" style="font-size:10px;font-weight:700;border:1px solid #fac775;background:#fdf7ec;color:#854f0b;border-radius:6px;padding:2px 8px;cursor:pointer;">Reprocess</button>` : '');
                }
                logs[pr.id].push({ text, time: l.at });
            });
        }

        updateTimeline(pr);
        updateRoutingButtons(pr);

        emptyEl.style.display = 'none';
        contentEl.classList.add('visible');
        renderLog(pr.id);
    }

    /* ── Row click ── */
    rows.forEach(row => {
        row.addEventListener('click', () => {
            const pr = allPrs.find(p => String(p.id) === row.dataset.prId);
            if (pr) openPr(pr);
        });
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
        });
    });

    /* ── Search (PR number, item, office, remarks, signatory stage) ── */
    const prSearchInput = document.getElementById('prSearch');
    const prCountChip    = document.getElementById('prVisibleCount');
    prSearchInput?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const match = !q || (row.dataset.search ?? '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (prCountChip) prCountChip.textContent = visible + (visible === 1 ? ' PR' : ' PRs');
    });

    /* ── Reprocess a pending/failed signature photo ── */
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.sig-reprocess-btn');
        if (!btn) return;
        btn.disabled = true;
        btn.textContent = 'Reprocessing…';
        try {
            const resp = await fetch(btn.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({}),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                btn.textContent = json.detection === 'detected' ? 'Processed ✓ (reload to view)' : 'No signature found';
            } else {
                btn.textContent = json.error || 'Failed';
                btn.disabled = false;
            }
        } catch {
            btn.textContent = 'Network error';
            btn.disabled = false;
        }
    });

    /* ── Tracking Status (auto by default, manually overridable) ── */
    document.addEventListener('change', async (e) => {
        const sel = e.target.closest('[data-tracking-select]');
        if (!sel) return;
        const url  = sel.dataset.trackingUrl;
        const prev = sel.dataset.prevValue ?? '';
        sel.disabled = true;
        try {
            const resp = await fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ trackingStatus: sel.value }),
            });
            const json = await resp.json().catch(() => null);
            if (resp.ok && json?.success) {
                sel.dataset.prevValue = sel.value;
                const tip = sel.closest('.tracking-tooltip-wrap')?.querySelector('.tracking-tooltip-text');
                if (tip) tip.textContent = sel.options[sel.selectedIndex].text;
                showToast(sel.value ? 'Tracking status overridden.' : 'Tracking status reset.');
            } else {
                sel.value = prev;
                showToast(json?.error || 'Failed to update tracking status.', true);
            }
        } catch {
            sel.value = prev;
            showToast('Network error.', true);
        } finally {
            sel.disabled = false;
        }
    });

    /* ── Route Forward ── */
    async function doAdvance(body) {
        if (!activePr || saving) return;
        saving = true;
        btnAdvance.disabled = true;
        btnAdvance.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Routing…';

        try {
            const resp = await fetch(activePr.advanceUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body || {}),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activePr.signatoryStage  = json.signatoryStage;
                activePr.signatoryLabel  = json.signatoryLabel;
                activePr.nextStage       = json.signatoryStage !== 'fully_signed' ? true : null;
                if (json.stageMeta)   activePr.stageMeta   = json.stageMeta;
                if (json.thirdSigner) activePr.thirdSigner = json.thirdSigner;
                document.getElementById('fSigLabel').textContent = json.signatoryLabel;
                updateTimeline(activePr);
                updateRoutingButtons(activePr);
                updateSigBadge(activePr.id, json.signatoryLabel, json.signatoryStage);
                logs[activePr.id].push({ text: `Routed forward → <strong>${json.signatoryLabel}</strong>`, time: nowStr() });
                renderLog(activePr.id);
                showToast('PR routed forward.');
            } else {
                showToast(json.error || 'Failed to route PR.', true);
            }
        } catch { showToast('Network error.', true); }
        finally {
            saving = false;
            if (activePr) {
                btnAdvance.disabled  = activePr.signatoryStage === 'fully_signed';
                btnAdvance.innerHTML = advanceBtnHtml(activePr);
            }
        }
    }

    btnAdvance.addEventListener('click', () => {
        if (!activePr || saving) return;
        // Entering the flexible 3rd slot — ask who actually signed first
        if (nextStageKey(activePr) === 'at_third_sign') {
            thirdSignerPanel.style.display = thirdSignerPanel.style.display === 'none' ? '' : 'none';
            return;
        }
        doAdvance({});
    });

    document.getElementById('btnThirdAcct').addEventListener('click', () => doAdvance({ third_signer: 'accounting' }));
    document.getElementById('btnThirdVc').addEventListener('click', () => doAdvance({ third_signer: 'vice_chancellor' }));

    /* ── Return (toggle panel) ── */
    btnReturn.addEventListener('click', () => {
        returnRemarks.style.display = returnRemarks.style.display === 'none' ? '' : 'none';
    });

    /* ── Confirm Return ── */
    btnConfirmRet.addEventListener('click', async () => {
        if (!activePr || saving) return;
        const reason = returnIn.value.trim();
        if (!reason) { showToast('Please provide a reason for returning.', true); return; }
        saving = true;
        btnConfirmRet.disabled = true;

        try {
            const resp = await fetch(activePr.returnUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ remarks: reason }),
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activePr.signatoryStage = json.signatoryStage;
                activePr.signatoryLabel = json.signatoryLabel;
                activePr.thirdSigner    = json.thirdSigner;
                if (json.stageMeta) activePr.stageMeta = json.stageMeta;
                document.getElementById('fSigLabel').textContent = json.signatoryLabel;
                updateTimeline(activePr);
                updateRoutingButtons(activePr);
                updateSigBadge(activePr.id, json.signatoryLabel, json.signatoryStage);
                returnRemarks.style.display = 'none';
                returnIn.value = '';
                logs[activePr.id].push({ text: `<strong>Returned to ${json.signatoryLabel}</strong> &mdash; ${reason}`, time: nowStr() });
                renderLog(activePr.id);
                showToast('PR returned one step — now at ' + json.signatoryLabel + '.');
            } else {
                showToast(json.error || 'Failed to return PR.', true);
            }
        } catch { showToast('Network error.', true); }
        finally { saving = false; btnConfirmRet.disabled = false; }
    });

    /* ── Save processing status ── */
    btnSave.addEventListener('click', async () => {
        if (!activePr || saving) return;
        const pr         = activePr;
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
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ status: statusVal, remarks }),
            });
            if (resp.ok) {
                pr.currentStatus = statusDisp;
                if (remarks) pr.remarks = remarks;
                if (remarks) document.getElementById('fRemarks').textContent = remarks;
                let logText = `Processing status → <strong>${statusDisp}</strong>`;
                if (remarks) logText += ` &mdash; ${remarks}`;
                logs[pr.id].push({ text: logText, time: nowStr() });
                remarksIn.value = '';
                renderLog(pr.id);
                showToast('Saved successfully.');
            } else {
                const json = await resp.json().catch(() => null);
                showToast(json?.message || 'Save failed.', true);
            }
        } catch { showToast('Network error.', true); }
        finally { saving = false; btnSave.disabled = false; btnSave.innerHTML = origHtml; }
    });

    /* ── Upload PR PDF ── */
    uploadPrInput.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file || !activePr) return;
        const origText = uploadPrText.textContent;
        uploadPrText.textContent = 'Uploading…';
        this.disabled = true;
        const fd = new FormData();
        fd.append('file', file);
        try {
            const resp = await fetch(activePr.uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                activePr.pdfFile = json.filePath;
                document.getElementById('pdfPreview').innerHTML =
                    `<iframe src="/storage/${json.filePath}" title="PR Document"></iframe>`;
                uploadPrText.textContent = 'Re-upload PDF';
                showToast('PR PDF uploaded successfully.');
            } else {
                uploadPrText.textContent = origText;
                showToast(json.message || 'Upload failed.', true);
            }
        } catch {
            uploadPrText.textContent = origText;
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
})();

/* ── BSU PDF Import Modal ── */
(function () {
    const importPdfUrl     = JSON.parse(document.getElementById('importPdfUrlData').textContent);
    const importConfirmUrl = JSON.parse(document.getElementById('importConfirmUrlData').textContent);
    const csrfToken        = document.querySelector('meta[name="csrf-token"]').content;

    const overlay       = document.getElementById('importModalOverlay');
    const btnOpen       = document.getElementById('btnOpenImport');
    const btnClose      = document.getElementById('btnCloseImport');
    const step1         = document.getElementById('importStep1');
    const step2         = document.getElementById('importStep2');
    const fileInput     = document.getElementById('importFileInput');
    const dropZone      = document.getElementById('importDropZone');
    const fileNameEl    = document.getElementById('importFileName');
    const btnExtract    = document.getElementById('btnExtract');
    const btnConfirm    = document.getElementById('btnConfirmImport');
    const btnBack       = document.getElementById('btnBackToStep1');
    const btnAddItem    = document.getElementById('btnAddItem');
    const itemsBody     = document.getElementById('importItemsBody');
    const noTextWarn    = document.getElementById('importNoTextWarn');
    const toastEl       = document.getElementById('prToast');

    let storedFilePath = null;
    let importing = false;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 2800);
    }

    function openModal() {
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        resetModal();
    }

    function resetModal() {
        step1.style.display = '';
        step2.style.display = 'none';
        fileInput.value = '';
        fileNameEl.style.display = 'none';
        fileNameEl.textContent = '';
        btnExtract.disabled = true;
        storedFilePath = null;
        noTextWarn.style.display = 'none';
        itemsBody.innerHTML = '';
        ['impPrNumber','impDate','impTitle','impPurpose','impTotal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const offSel = document.getElementById('impOfficeId');
        if (offSel) offSel.value = '';
    }

    btnOpen.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);
    btnBack.addEventListener('click', resetModal);

    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeModal();
    });

    /* File selection */
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        fileNameEl.textContent  = file.name;
        fileNameEl.style.display = '';
        btnExtract.disabled = false;
    });

    /* Drag and drop */
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file && file.type === 'application/pdf') {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            fileNameEl.textContent   = file.name;
            fileNameEl.style.display = '';
            btnExtract.disabled = false;
        }
    });

    /* Extract fields */
    btnExtract.addEventListener('click', async function () {
        const file = fileInput.files[0];
        if (!file || importing) return;
        importing = true;
        btnExtract.disabled = true;
        btnExtract.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Extracting…';

        try {
            const fd = new FormData();
            fd.append('file', file);
            const resp = await fetch(importPdfUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();

            if (!resp.ok || !json.success) {
                showToast(json.message || 'Could not process the PDF.', true);
                btnExtract.disabled = false;
                btnExtract.innerHTML = '<i class="ti ti-scan"></i> Extract Fields from PDF';
                return;
            }

            storedFilePath = json.filePath;
            noTextWarn.style.display = json.hasText ? 'none' : '';

            const ex = json.extracted || {};
            document.getElementById('impPrNumber').value = ex.pr_number || '';
            document.getElementById('impDate').value     = ex.date      || '';
            document.getElementById('impTitle').value    = ex.title     || '';
            document.getElementById('impPurpose').value  = ex.purpose   || '';
            document.getElementById('impTotal').value    = ex.total     || '';

            // Auto-select the requesting office if the extracted text matches one by name.
            if (ex.office) {
                const officeSel = document.getElementById('impOfficeId');
                const needle = ex.office.trim().toLowerCase();
                const match = Array.from(officeSel.options).find(o =>
                    o.value && (o.text.toLowerCase() === needle || o.text.toLowerCase().includes(needle) || needle.includes(o.text.toLowerCase()))
                );
                if (match) officeSel.value = match.value;
            }

            // Pre-fill the items table with whatever rows were detected in the PDF.
            itemsBody.innerHTML = '';
            if (Array.isArray(ex.items) && ex.items.length) {
                ex.items.forEach(item => addItemRow(item.name, item.qty, item.unit, item.unit_cost));
            } else {
                addItemRow();
            }

            step1.style.display = 'none';
            step2.style.display = '';
        } catch {
            showToast('Network error during extraction.', true);
            btnExtract.disabled = false;
        }

        btnExtract.innerHTML = '<i class="ti ti-scan"></i> Extract Fields from PDF';
        importing = false;
    });

    /* Add item row */
    function addItemRow(name = '', qty = '', unit = '', unitCost = '') {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text"   class="item-input" placeholder="Item description" value="${name}"></td>
            <td><input type="number" class="item-input" placeholder="1"   min="0" step="any" value="${qty}"     style="width:60px;"></td>
            <td><input type="text"   class="item-input" placeholder="pc"  value="${unit}"    style="width:56px;"></td>
            <td><input type="number" class="item-input" placeholder="0.00" min="0" step="0.01" value="${unitCost}"></td>
            <td><button type="button" class="btn-del-item"><i class="ti ti-trash" style="font-size:13px;"></i></button></td>`;
        tr.querySelector('.btn-del-item').addEventListener('click', () => tr.remove());
        itemsBody.appendChild(tr);
    }

    btnAddItem.addEventListener('click', () => addItemRow());

    /* Confirm import */
    btnConfirm.addEventListener('click', async function () {
        if (importing) return;

        const officeId = document.getElementById('impOfficeId').value;
        const title    = document.getElementById('impTitle').value.trim();

        if (!officeId) { showToast('Please select the requesting office.', true); return; }
        if (!title)    { showToast('Please enter a project title.', true); return; }

        const items = [];
        itemsBody.querySelectorAll('tr').forEach(tr => {
            const inputs = tr.querySelectorAll('input');
            if (inputs[0]?.value.trim()) {
                items.push({
                    name:      inputs[0].value.trim(),
                    qty:       inputs[1]?.value || 1,
                    unit:      inputs[2]?.value || 'pc',
                    unit_cost: inputs[3]?.value || 0,
                });
            }
        });

        importing = true;
        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Importing…';

        try {
            const resp = await fetch(importConfirmUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    office_id:    officeId,
                    number:       document.getElementById('impPrNumber').value.trim(),
                    title,
                    purpose:      document.getElementById('impPurpose').value.trim(),
                    total_amount: document.getElementById('impTotal').value || 0,
                    file_path:    storedFilePath,
                    items,
                }),
            });
            const json = await resp.json();

            if (resp.ok && json.success) {
                showToast('PR imported successfully from BSU system!');
                closeModal();
                setTimeout(() => window.location.reload(), 800);
            } else {
                const msg = json.message || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Import failed.');
                showToast(msg, true);
            }
        } catch {
            showToast('Network error during import.', true);
        }

        importing = false;
        btnConfirm.disabled = false;
        btnConfirm.innerHTML = '<i class="ti ti-check"></i> Confirm Import';
    });
})();
</script>
@endpush
