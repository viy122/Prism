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
    .sig-timeline { display: flex; align-items: flex-start; gap: 0; margin-bottom: 4px; }
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
    .pr-ppmp-nav { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--s50); border: 1px solid var(--s200); border-radius: 10px; padding: 8px 10px; margin-top: -6px; }
    .pr-ppmp-nav-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--s200); background: var(--white); color: var(--s600); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; }
    .pr-ppmp-nav-btn:hover:not(:disabled) { background: var(--crimson-mid); color: var(--m); border-color: var(--crimson-border); }
    .pr-ppmp-nav-btn:disabled { opacity: .4; cursor: not-allowed; }
    #ppmpNavLabel { font-size: 11.5px; font-weight: 700; color: var(--s600); text-align: center; flex: 1; }
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

    .items-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .items-table th { padding: 9px 14px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--s400); border-bottom: 1px solid var(--s200); background: var(--white); white-space: nowrap; }
    .items-table td { padding: 9px 14px; border-bottom: 1px solid var(--s200); color: var(--s600); vertical-align: middle; }
    .items-table tbody tr:last-child td { border-bottom: none; }
    .items-table .item-name-cell { font-weight: 600; color: var(--s900); }
    .items-table .num-cell   { font-weight: 700; color: var(--s700); text-align: right; }
    .items-table .total-cell { font-weight: 700; color: var(--m); text-align: right; }

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
    .log-toggle { cursor: pointer; user-select: none; background: var(--s50); border: 1px solid var(--s200); border-radius: 10px; padding: 11px 14px; transition: background .15s, border-color .15s; }
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
    .activity-attachments { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .activity-attachment { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: var(--m); background: var(--s50); border: 1px solid var(--s100); border-radius: 6px; padding: 3px 8px; text-decoration: none; max-width: 160px; }
    .activity-attachment span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .activity-attachment:hover { background: var(--s100); }

    /* Toast */
    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 1200px) { .pr-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } }

    .search-toolbar { display: flex; align-items: center; gap: 8px; width: 100%; margin-bottom: 14px; }
    .search-toolbar .search-wrap { flex: 1; min-width: 0; margin-bottom: 0; }
    .filter-select { height: 40px; border-radius: 99px; border: 1px solid var(--s200); background: var(--s50); padding: 0 30px 0 14px; font-size: 12.5px; font-weight: 600; color: var(--s700); font-family: 'Poppins', sans-serif; outline: none; cursor: pointer; transition: border-color .15s, box-shadow .15s; flex-shrink: 0; }
    .filter-select:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
    @media (max-width: 640px) { .search-toolbar { flex-wrap: wrap; } .search-toolbar .search-wrap { flex-basis: 100%; } }

    .btn-primary {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; height: 42px; padding: 0 20px; border-radius: 10px;
        background: var(--crimson); color: #fff;
        font-size: 13px; font-weight: 700; border: none; cursor: pointer;
        font-family: 'Poppins', sans-serif; box-shadow: 0 2px 10px rgba(139,26,28,.2);
        transition: background .2s; white-space: nowrap;
    }
    .btn-primary:hover { background: var(--crimson-dark); }

    /* This modal sits outside .content as a sibling, so the --s and --m
       aliases .content defines for this page don't reach it. Everything
       below uses either a real :root global (--crimson, --white, --s200)
       or a hardcoded fallback instead. */
    .pr-modal-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(15,23,42,.55); display: none; align-items: center; justify-content: center; padding: 20px; }
    .pr-modal-overlay.open { display: flex; }
    .pr-modal { background: #fff; border-radius: 18px; width: 100%; max-width: 640px; max-height: 92vh; overflow-y: auto; padding: 24px 28px; box-shadow: 0 24px 60px rgba(0,0,0,.25); font-family: 'Poppins', sans-serif; }
    .pr-modal-title { font-size: 17px; font-weight: 800; color: #0f172a; }
    .pr-modal-sub { font-size: 12.5px; color: #64748b; margin-top: 3px; }
    .pr-modal-close { position: absolute; top: 20px; right: 24px; background: none; border: none; font-size: 22px; color: #94a3b8; cursor: pointer; line-height: 1; }
    .pr-modal-close:hover { color: #334155; }
    .pr-modal-body { position: relative; }

    .pr-step { margin-top: 18px; }
    .pr-step-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #64748b; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }

    .pr-ppmp-list { border: 1px solid #e2e8f0; border-radius: 10px; max-height: 220px; overflow-y: auto; }
    .pr-ppmp-row { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background .12s; }
    .pr-ppmp-row:last-child { border-bottom: none; }
    .pr-ppmp-row:hover { background: #f8fafc; }
    .pr-ppmp-row.selected { background: rgba(139,26,28,.06); border-left: 3px solid var(--crimson); padding-left: 11px; }
    .pr-ppmp-row-main { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; }
    .pr-ppmp-code { font-size: 12.5px; font-weight: 800; color: #0f172a; }
    .pr-ppmp-office { display: inline-flex; align-items: center; height: 20px; padding: 0 8px; border-radius: 20px; background: #f1f5f9; color: #334155; font-size: 10px; font-weight: 700; }
    .pr-ppmp-title { font-size: 12px; color: #475569; margin-bottom: 4px; }
    .pr-ppmp-row-meta { display: flex; justify-content: space-between; gap: 10px; font-size: 11px; color: #94a3b8; }
    .pr-ppmp-missing-count { font-weight: 700; color: #854f0b; }

    .pr-missing-box { border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; padding: 12px 14px; max-height: 160px; overflow-y: auto; }
    .pr-missing-empty { font-size: 12.5px; color: #94a3b8; }
    .pr-missing-row { display: flex; justify-content: space-between; gap: 10px; font-size: 12.5px; padding: 5px 0; border-bottom: 1px solid #f1f5f9; }
    .pr-missing-row:last-child { border-bottom: none; }
    .pr-missing-row .name { color: #334155; font-weight: 600; }
    .pr-missing-row .qty { color: #64748b; white-space: nowrap; }

    .pr-file-picker { display: flex; align-items: center; gap: 10px; }
    .pr-choose-btn { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 16px; border-radius: 9px; border: 1.5px dashed #cbd5e1; background: #f8fafc; color: #475569; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .pr-choose-btn:hover { border-color: var(--crimson); color: var(--crimson); }
    .pr-file-name { font-size: 12px; color: #475569; font-weight: 600; }
    .pr-extract-status { font-size: 12px; color: #64748b; margin-top: 8px; display: none; }
    .pr-extract-status.show { display: flex; align-items: center; gap: 6px; }
    .pr-extract-warn { font-size: 11.5px; color: #92400E; background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 8px; padding: 8px 10px; margin-top: 8px; display: none; }
    .pr-extract-warn.show { display: block; }

    .pr-review { display: none; }
    .pr-review.show { display: block; }
    .pr-review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 4px; }
    .pr-field label { font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 5px; display: block; }
    .pr-field input { width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 11px; font-size: 12.5px; font-family: 'Poppins', sans-serif; color: #0f172a; outline: none; }
    .pr-field input:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }

    .pr-items-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
    .pr-items-table th { text-align: left; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; padding: 6px 6px; border-bottom: 1px solid #e2e8f0; }
    .pr-items-table td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; }
    .pr-items-table input { width: 100%; height: 32px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px; font-size: 12px; font-family: 'Poppins', sans-serif; }
    .pr-items-table input.pr-item-name { min-width: 160px; }
    .pr-items-table input.pr-item-num { text-align: right; width: 80px; }
    .pr-row-remove { background: none; border: none; color: #DC2626; cursor: pointer; font-size: 15px; padding: 4px; }
    .pr-add-item-btn { margin-top: 8px; background: none; border: 1.5px dashed #cbd5e1; border-radius: 8px; color: #475569; font-size: 12px; font-weight: 700; padding: 6px 12px; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .pr-add-item-btn:hover { border-color: var(--crimson); color: var(--crimson); }
    .pr-items-total { text-align: right; font-size: 12.5px; font-weight: 700; color: #0f172a; margin-top: 8px; }

    .pr-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
    .pr-btn-cancel { height: 40px; padding: 0 18px; border-radius: 9px; background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .pr-status-msg { font-size: 12px; font-weight: 600; border-radius: 8px; padding: 9px 12px; margin-top: 12px; display: none; }
    .pr-status-msg.error { display: block; background: #fee2e2; color: #b91c1c; }
    .pr-status-msg.success { display: block; background: #dcfce7; color: #166534; }

    /* Quarter picker */
    .pr-quarter-select { height: 38px; width: 100%; border-radius: 9px; border: 1px solid #cbd5e1; background: #fff; padding: 0 12px; font-size: 12.5px; font-weight: 600; color: #334155; font-family: 'Poppins', sans-serif; }
    .pr-quarter-hint { font-size: 10.5px; color: #94a3b8; margin-top: 5px; }

    /* Content-validation result */
    .pr-validation { border-radius: 9px; padding: 10px 13px; margin-bottom: 12px; }
    .pr-validation.pass { background: #dcfce7; border: 1px solid #bbf7d0; }
    .pr-validation.fail { background: #fee2e2; border: 1px solid #fecaca; }
    .pr-validation-summary { font-size: 12px; font-weight: 700; }
    .pr-validation.pass .pr-validation-summary { color: #166534; }
    .pr-validation.fail .pr-validation-summary { color: #b91c1c; }
    .pr-validation-warnings { margin: 6px 0 0 16px; padding: 0; }
    .pr-validation-warnings li { font-size: 11px; color: #854f0b; line-height: 1.5; }

    .pr-items-table tr.row-fail td { background: #fef2f2; }
    .pr-item-verdict { display: block; font-size: 10.5px; font-weight: 700; margin-top: 3px; line-height: 1.4; }
    .pr-item-verdict.ok   { color: #166534; }
    .pr-item-verdict.bad  { color: #dc2626; }
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
        <button type="button" class="btn-primary" id="btnOpenUploadPr"><i class="ti ti-upload"></i> Upload Purchase Request</button>
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
                    <span class="count-chip" id="prVisibleCount">{{ count($purchaseRequests) }} PR{{ count($purchaseRequests) !== 1 ? 's' : '' }}</span>
                </div>
            </div>

            @if(count($purchaseRequests) > 0)
                <div class="search-toolbar">
                    <div class="search-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="search-input" type="search" id="prSearch" placeholder="Search by PR number, item, office, or remarks">
                    </div>
                    <select class="filter-select" id="prOfficeFilter" title="Filter by office">
                        <option value="">All Offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office['code'] }}">{{ $office['code'] }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="prStatusFilter" title="Filter by signatory status">
                        <option value="">All Statuses</option>
                        <option value="fully_signed">Fully Signed</option>
                        <option value="in_progress">In Progress</option>
                        <option value="pending">Pending</option>
                    </select>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseRequests as $pr)
                            @continue(!$pr['isTableRow'])
                            @php
                                $stageBadge = match($pr['signatoryStage']) {
                                    'fully_signed' => 'badge-signed',
                                    'draft'        => 'badge-draft',
                                    default        => 'badge-routing',
                                };
                            @endphp
                            <tr data-pr-row data-pr-id="{{ $pr['id'] }}" data-office="{{ $pr['office'] }}" data-status-bucket="{{ $pr['statusBucket'] }}" data-created-at="{{ $pr['createdAt'] }}" data-search="{{ strtolower($pr['prNumber'] . ' ' . $pr['item'] . ' ' . $pr['office'] . ' ' . $pr['remarks'] . ' ' . $pr['signatoryLabel']) }}" tabindex="0">
                                <td style="font-size:12px;font-weight:600;color:var(--s600);white-space:nowrap;max-width:120px;overflow:hidden;text-overflow:ellipsis;">{{ $pr['office'] }}</td>
                                <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">
                                    {{ $pr['prNumber'] }}
                                    @if($pr['budgetProposalId'])
                                        <span class="count-chip" style="height:18px;padding:0 7px;font-size:9.5px;margin-left:4px;" title="{{ $pr['siblingCount'] }} {{ $pr['siblingCount'] === 1 ? 'PR' : 'PRs' }} against {{ $pr['budgetProposalCode'] }}{{ $pr['siblingCount'] > 1 ? ' — open this one, then use Next to see the rest' : '' }}">
                                            {{ $pr['budgetProposalCode'] }}{{ $pr['siblingCount'] > 1 ? ' · ' . $pr['siblingCount'] . ' PRs' : '' }}
                                        </span>
                                    @endif
                                </td>
                                <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $pr['item'] }}
                                    @if($pr['itemCount'] > 1)
                                        <span class="count-chip" style="height:17px;padding:0 6px;font-size:9px;margin-left:4px;" title="{{ $pr['itemCount'] }} items bundled in this PR">{{ $pr['itemCount'] }} items</span>
                                    @endif
                                </td>
                                <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $pr['dateSubmitted'] }}</td>
                                <td>
                                    <span class="badge {{ $stageBadge }}" data-sig-badge="{{ $pr['id'] }}">{{ $pr['signatoryLabel'] }}</span>
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

            {{-- Only shown when this PR is linked to a PPMP with more than one PR —
                 those are usually uploaded at different times, not all at once. --}}
            <div class="pr-ppmp-nav" id="ppmpNav" style="display:none;">
                <button type="button" class="pr-ppmp-nav-btn" id="ppmpPrevBtn" title="Previous PR from this PPMP"><i class="ti ti-chevron-left"></i></button>
                <span id="ppmpNavLabel"></span>
                <button type="button" class="pr-ppmp-nav-btn" id="ppmpNextBtn" title="Next PR from this PPMP"><i class="ti ti-chevron-right"></i></button>
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
                    <span id="uploadPrText">Upload PR PDF</span>
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
                    <div class="detail-field full" id="fItemsField" style="display:none;padding:0;overflow:hidden;">
                        <table class="items-table" id="fItemsTable">
                            <thead>
                                <tr>
                                    <th style="width:40%">Item Description</th>
                                    <th class="num-cell">Quantity</th>
                                    <th>Unit</th>
                                    <th class="num-cell">Unit Cost</th>
                                    <th class="num-cell">Total</th>
                                </tr>
                            </thead>
                            <tbody id="fItemsBody"></tbody>
                        </table>
                    </div>
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
                    <div class="card-head log-toggle" id="logToggle">
                        <div class="log-toggle-label">
                            <i class="ti ti-history"></i>
                            <div>
                                <p class="card-eyebrow" style="margin-bottom:1px;">History</p>
                                <h3 class="card-title" style="font-size:14px;">View Activity Log</h3>
                            </div>
                        </div>
                        <i class="ti ti-chevron-down chev"></i>
                    </div>
                    <div class="activity-log" id="activityLog"></div>
                </div>

            </div>
        </div>

    </div>

</div>

{{-- Toast --}}
<div class="pr-toast" id="prToast"></div>

{{-- ── Upload Purchase Request modal ── --}}
<div class="pr-modal-overlay" id="prModalOverlay">
    <div class="pr-modal">
        <div class="pr-modal-body">
            <button type="button" class="pr-modal-close" id="prModalCloseBtn" aria-label="Close">&times;</button>
            <p class="pr-modal-title">Upload Purchase Request</p>
            <p class="pr-modal-sub">Pick the approved PPMP this PR is for, then upload the signed document — its contents get read automatically for you to review before creating it.</p>

            <div class="pr-step">
                <p class="pr-step-label">1. Approved PPMP <span style="text-transform:none;font-weight:600;color:#94a3b8;">(newest first)</span></p>
                <div class="pr-ppmp-list" id="prPpmpList">
                    @forelse ($approvedPpmps as $p)
                    <div class="pr-ppmp-row" data-ppmp-id="{{ $p['id'] }}" data-office-code="{{ $p['officeCode'] }}" data-missing="{{ json_encode($p['missingItems']) }}">
                        <div class="pr-ppmp-row-main">
                            <span class="pr-ppmp-code">{{ $p['code'] }}</span>
                            <span class="pr-ppmp-office">{{ $p['officeCode'] }}</span>
                        </div>
                        <p class="pr-ppmp-title">{{ $p['title'] }}</p>
                        <div class="pr-ppmp-row-meta">
                            <span>Approved {{ $p['approvedAt'] }}</span>
                            <span class="pr-ppmp-missing-count">{{ count($p['missingItems']) }} {{ Str::plural('item', count($p['missingItems'])) }} still need a PR</span>
                        </div>
                    </div>
                    @empty
                    <p class="pr-missing-empty">No approved PPMPs currently have items still waiting on a Purchase Request.</p>
                    @endforelse
                </div>
                <div class="pr-missing-box" id="prMissingBox" style="margin-top:10px;display:none;"></div>

                <div id="prQuarterWrap" style="margin-top:12px;display:none;">
                    <label class="pr-step-label" for="prQuarterSelect" style="display:block;margin-bottom:6px;">
                        Target quarter to check against
                    </label>
                    <select id="prQuarterSelect" class="pr-quarter-select">
                        <option value="">All quarters (check the whole PPMP)</option>
                        <option value="Q1">Q1 (Jan–Mar)</option>
                        <option value="Q2">Q2 (Apr–Jun)</option>
                        <option value="Q3">Q3 (Jul–Sep)</option>
                        <option value="Q4">Q4 (Oct–Dec)</option>
                    </select>
                    <p class="pr-quarter-hint">
                        This is the <strong>Target Quarter the requesting office set on each PPMP item</strong> — not a date on the PR itself.
                        Pick one to compare only that quarter's items; leave it on "All quarters" if you're unsure.
                    </p>
                </div>
            </div>

            <div class="pr-step" id="prUploadStep" style="display:none;">
                <p class="pr-step-label">2. PR Document (PDF)</p>
                <div class="pr-file-picker">
                    <button type="button" class="pr-choose-btn" id="prChooseFileBtn"><i class="ti ti-file-upload"></i> Choose PDF</button>
                    <span class="pr-file-name" id="prFileName"></span>
                </div>
                <input type="file" id="prFileInput" accept="application/pdf,.pdf" style="display:none;">
                <div class="pr-extract-status" id="prExtractStatus"><i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Reading document…</div>
                <div class="pr-extract-warn" id="prExtractWarn"></div>
            </div>

            <div class="pr-step pr-review" id="prReview">
                <p class="pr-step-label">3. Review Before Creating</p>
                <div class="pr-review-grid">
                    <div class="pr-field"><label>PR Number</label><input type="text" id="prNumberInput"></div>
                    <div class="pr-field"><label>Title (optional)</label><input type="text" id="prTitleInput" placeholder="e.g. project/purpose"></div>
                </div>

                {{-- Content check against the selected PPMP — filled in by the
                     extract call, and re-checked server-side on submit. --}}
                <div class="pr-validation" id="prValidation" style="display:none;">
                    <p class="pr-validation-summary" id="prValidationSummary"></p>
                    <ul class="pr-validation-warnings" id="prValidationWarnings"></ul>
                </div>

                <table class="pr-items-table">
                    <thead>
                        <tr><th>Item</th><th>Unit</th><th>Qty</th><th>Unit Cost</th><th>Total</th><th></th></tr>
                    </thead>
                    <tbody id="prItemsBody"></tbody>
                </table>
                <button type="button" class="pr-add-item-btn" id="prAddItemBtn"><i class="ti ti-plus"></i> Add item row</button>
                <p class="pr-items-total" id="prItemsTotal">Total: ₱0.00</p>
            </div>

            <div class="pr-status-msg" id="prStatusMsg"></div>

            <div class="pr-modal-actions">
                <button type="button" class="pr-btn-cancel" id="prCancelBtn">Cancel</button>
                <button type="button" class="btn-primary" id="prSubmitBtn" disabled><i class="ti ti-check"></i> Create Purchase Request</button>
            </div>
        </div>
    </div>
</div>

@endsection

<script type="application/json" id="prData">@json($purchaseRequests)</script>
<script type="application/json" id="stagesData">@json($stageMeta)</script>
<script type="application/json" id="refreshUrlData">@json(route('procurement-office.purchase-request-management.refresh'))</script>
<script type="application/json" id="extractPrUrlData">@json($extractPrUrl)</script>
<script type="application/json" id="createPrUrlData">@json($createPrUrl)</script>

@push('scripts')
<script>
(function () {
    let allPrs          = JSON.parse(document.getElementById('prData').textContent);
    const refreshUrl    = JSON.parse(document.getElementById('refreshUrlData').textContent);
    const tbody          = document.querySelector('.table-wrap table tbody');
    function getRows() { return tbody ? tbody.querySelectorAll('[data-pr-row]') : []; }
    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }
    const emptyEl      = document.getElementById('detailEmpty');
    const contentEl    = document.getElementById('detailContent');
    const titleEl      = document.getElementById('detailPrNumber');
    const officeChip   = document.getElementById('detailPrOffice');
    const statusSel    = document.getElementById('statusSelect');
    const remarksIn    = document.getElementById('remarksInput');
    const btnSave      = document.getElementById('btnSave');
    const logEl        = document.getElementById('activityLog');
    const logToggle    = document.getElementById('logToggle');
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
    // True once the user touches the status dropdown themselves, so a
    // background refresh doesn't silently revert an unsaved selection.
    // Cleared whenever the value is set programmatically (open / save / refresh).
    let statusDirty = false;
    statusSel.addEventListener('change', () => { statusDirty = true; });

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

    // Oldest first — callers that want newest-first (the log display) reverse
    // this. Falls back to array order for any entry missing a raw timestamp.
    // Needed here specifically because this page merges two separate sources
    // (status-update activityLog + signatureLogs) that aren't already in a
    // shared chronological order once simply concatenated.
    function sortEntriesAsc(entries) {
        return entries.slice().sort((a, b) => {
            const ta = a.atRaw ? new Date(a.atRaw).getTime() : NaN;
            const tb = b.atRaw ? new Date(b.atRaw).getTime() : NaN;
            if (isNaN(ta) || isNaN(tb)) return 0;
            return ta - tb;
        });
    }

    function renderLog(prId) {
        const entries = sortEntriesAsc(logs[prId] || []);
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
                    ${attachmentsHtml(e.attachments)}
                </div>
            </div>`).join('');
    }

    // Files a signatory attached via the mobile app's Take a Photo / Upload
    // flow — opens the signed signature-attachment link in a new tab.
    function attachmentsHtml(attachments) {
        if (!attachments || !attachments.length) return '';
        return '<div class="activity-attachments">' + attachments.map(a => `
            <a href="${a.url}" class="activity-attachment" data-preview-name="${escapeHtml(a.filename)}" data-preview-image="${a.isImage ? '1' : '0'}" title="${escapeHtml(a.filename)}">
                <i class="ti ${a.isImage ? 'ti-photo' : 'ti-file-text'}"></i>
                <span>${escapeHtml(a.filename)}</span>
            </a>`).join('') + '</div>';
    }

    // Preview an attachment inside PRISM instead of navigating away to it.
    logEl.addEventListener('click', e => {
        const link = e.target.closest('.activity-attachment');
        if (!link) return;
        e.preventDefault();
        const url  = link.getAttribute('href');
        const name = link.dataset.previewName;
        const body = link.dataset.previewImage === '1'
            ? `<img src="${url}" alt="${escapeHtml(name)}" style="max-width:100%;border-radius:10px;display:block;margin:0 auto;">`
            : `<iframe src="${url}" style="width:100%;height:60vh;border:none;border-radius:8px;"></iframe>`;
        window.prismInfoModal({
            title: name,
            bodyHtml: body + `<p style="margin-top:10px;font-size:11px;"><a href="${url}" target="_blank" rel="noopener">Open in new tab ↗</a></p>`,
        });
    });

    function updateTimeline(pr) {
        // Timeline shows every real stage — excludes 'draft' (not a signing step)
        // and the terminal 'fully_signed' marker (not a real step either, just
        // means "done"). Without excluding the latter, activeIdx would land on
        // it once reached and that last dot would render 'active' (in-progress)
        // forever instead of 'done' like the rest.
        const timeline = document.getElementById('sigTimeline');
        const meta     = metaOf(pr).filter(m => !['draft', 'fully_signed'].includes(m.key));
        const activeIdx = meta.findIndex(m => m.key === pr.signatoryStage);
        const isFullySigned = pr.signatoryStage === 'fully_signed';
        timeline.innerHTML = meta.map((m, i) => {
            const state = (isFullySigned || i < activeIdx) ? ' done' : (i === activeIdx ? ' active' : '');
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
    // PRs against the same PPMP are usually uploaded at different times (see
    // Upload Purchase Request), not all in one sitting — this lets someone
    // reviewing one of them jump straight to the others instead of hunting
    // through the whole queue for the matching budget_proposal_id.
    function updatePpmpNav(pr) {
        const nav = document.getElementById('ppmpNav');
        if (!pr.budgetProposalId) { nav.style.display = 'none'; return; }

        const siblings = allPrs
            .filter(p => p.budgetProposalId === pr.budgetProposalId)
            .sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
        if (siblings.length <= 1) { nav.style.display = 'none'; return; }

        const idx = siblings.findIndex(p => p.id === pr.id);
        document.getElementById('ppmpNavLabel').textContent =
            `PR ${idx + 1} of ${siblings.length} for ${pr.budgetProposalCode || 'this PPMP'}`;

        const prevBtn = document.getElementById('ppmpPrevBtn');
        const nextBtn = document.getElementById('ppmpNextBtn');
        prevBtn.disabled = idx === 0;
        nextBtn.disabled = idx === siblings.length - 1;
        prevBtn.onclick = () => { if (idx > 0) openPr(siblings[idx - 1]); };
        nextBtn.onclick = () => { if (idx < siblings.length - 1) openPr(siblings[idx + 1]); };
        nav.style.display = 'flex';
    }

    function openPr(pr) {
        activePr = pr;

        getRows().forEach(r => r.classList.remove('selected'));
        tbody?.querySelector(`[data-pr-id="${pr.id}"]`)?.classList.add('selected');
        logToggle.classList.remove('open');
        logEl.classList.remove('open');

        titleEl.textContent      = pr.prNumber;
        officeChip.textContent   = pr.office;
        officeChip.style.display = '';
        updatePpmpNav(pr);

        document.getElementById('fOffice').textContent   = pr.office;
        document.getElementById('fPrNumber').textContent = pr.prNumber;
        document.getElementById('fItem').textContent     = pr.item;
        document.getElementById('fDate').textContent     = pr.dateSubmitted;
        document.getElementById('fSigLabel').textContent = pr.signatoryLabel;
        document.getElementById('fRemarks').textContent  = pr.remarks !== '—' ? pr.remarks : '—';

        const fItemsField = document.getElementById('fItemsField');
        const fItemsBody  = document.getElementById('fItemsBody');
        if (pr.items && pr.items.length) {
            fItemsBody.innerHTML = pr.items.map(it => `
                <tr>
                    <td class="item-name-cell">${escapeHtml(it.name)}</td>
                    <td class="num-cell">${it.quantity}</td>
                    <td>${escapeHtml(it.unit || '')}</td>
                    <td class="num-cell">${money(it.unitCost)}</td>
                    <td class="total-cell">${money(it.totalCost)}</td>
                </tr>
            `).join('');
            fItemsField.style.display = '';
        } else {
            fItemsBody.innerHTML = '';
            fItemsField.style.display = 'none';
        }

        statusSel.value = pr.currentStatus ?? 'new';
        statusDirty = false;
        remarksIn.value = '';

        const pdfEl = document.getElementById('pdfPreview');
        pdfEl.innerHTML = pr.pdfFile
            ? `<iframe src="/storage/${pr.pdfFile}#toolbar=0" title="PR Document"></iframe>`
            : `<div class="pdf-placeholder"><i class="ti ti-file-off"></i><span>No PDF attached</span></div>`;
        uploadPrText.textContent = pr.pdfFile ? 'Re-upload PDF' : 'Upload PR PDF';

        if (!logs[pr.id]) {
            logs[pr.id] = (pr.activityLog || []).map(e => ({
                text: `Status: <strong>${e.status}</strong>` + (e.remarks && e.remarks !== '—' ? ` &mdash; ${e.remarks}` : ''),
                time: e.timestamp,
                atRaw: e.timestampRaw,
            }));
            (pr.signatureLogs || []).forEach(l => {
                let text = `<strong>${l.display}</strong>` + (l.by && l.by !== '—' ? ` by ${l.by}` : '') + (l.remarks ? ` &mdash; ${l.remarks}` : '');
                if (l.photoUrl) {
                    text += `<br><a href="${l.photoUrl}" target="_blank" rel="noopener"><img src="${l.photoUrl}" alt="Signed document (signature blurred)" style="margin-top:6px;max-width:120px;border-radius:8px;border:1px solid #e2e8f0;"></a> <span style="font-size:10px;color:#64748b;">signature blurred for privacy</span>`;
                } else if (l.photoStatus === 'pending' || l.photoStatus === 'failed') {
                    text += `<br><span style="font-size:11px;color:#854f0b;">photo withheld — processing</span>`
                        + (l.reprocessUrl ? ` <button type="button" class="sig-reprocess-btn" data-url="${l.reprocessUrl}" style="font-size:10px;font-weight:700;border:1px solid #fac775;background:#fdf7ec;color:#854f0b;border-radius:6px;padding:2px 8px;cursor:pointer;">Reprocess</button>` : '');
                }
                logs[pr.id].push({ text, time: l.at, atRaw: l.atRaw, attachments: l.attachments || [] });
            });
        }

        updateTimeline(pr);
        updateRoutingButtons(pr);

        emptyEl.style.display = 'none';
        contentEl.classList.add('visible');
        renderLog(pr.id);
    }

    /* ── Row click ── */
    // Delegated so rows appended later (by the background refresh) work
    // without needing to re-bind listeners after every poll.
    tbody?.addEventListener('click', e => {
        const row = e.target.closest('[data-pr-row]');
        if (!row) return;
        const pr = allPrs.find(p => String(p.id) === row.dataset.prId);
        if (pr) openPr(pr);
    });
    tbody?.addEventListener('keydown', e => {
        const row = e.target.closest('[data-pr-row]');
        if (!row) return;
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
    });

    /* ── Search (PR number, item, office, remarks, signatory stage) + filters ── */
    const prSearchInput = document.getElementById('prSearch');
    const prCountChip    = document.getElementById('prVisibleCount');
    const prOfficeFilter = document.getElementById('prOfficeFilter');
    const prStatusFilter = document.getElementById('prStatusFilter');
    function applyPrSearchFilter() {
        if (!prSearchInput) return;
        const q      = prSearchInput.value.trim().toLowerCase();
        const office = prOfficeFilter ? prOfficeFilter.value : '';
        const status = prStatusFilter ? prStatusFilter.value : '';
        let visible = 0;
        getRows().forEach(row => {
            const matchesSearch = !q || (row.dataset.search ?? '').includes(q);
            const matchesOffice = !office || row.dataset.office === office;
            const matchesStatus = !status || row.dataset.statusBucket === status;
            const match = matchesSearch && matchesOffice && matchesStatus;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (prCountChip) prCountChip.textContent = visible + (visible === 1 ? ' PR' : ' PRs');
    }
    prSearchInput?.addEventListener('input', applyPrSearchFilter);
    prOfficeFilter?.addEventListener('change', applyPrSearchFilter);
    prStatusFilter?.addEventListener('change', applyPrSearchFilter);

    logToggle.addEventListener('click', () => {
        logToggle.classList.toggle('open');
        logEl.classList.toggle('open');
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
                logs[activePr.id].push({ text: `Routed forward → <strong>${json.signatoryLabel}</strong>`, time: nowStr(), atRaw: new Date().toISOString() });
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
                logs[activePr.id].push({ text: `<strong>Returned to ${json.signatoryLabel}</strong> &mdash; ${reason}`, time: nowStr(), atRaw: new Date().toISOString() });
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
                logs[pr.id].push({ text: logText, time: nowStr(), atRaw: new Date().toISOString() });
                remarksIn.value = '';
                statusDirty = false;
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
                    `<iframe src="/storage/${json.filePath}#toolbar=0" title="PR Document"></iframe>`;
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

    // ── Auto-refresh ── another signatory's action (mobile or elsewhere on
    // web) otherwise wouldn't show up here without a manual reload. Poll for
    // fresh data and patch the list/detail panel in place instead of a full
    // page reload. Skipped entirely (data fetched but discarded) while an
    // upload is in flight, a remark/status field is being edited, the return
    // panel is open, or the third-signer choice hasn't been submitted yet —
    // so it can't wipe in-progress work.
    function rowHtml(pr) {
        const stageBadge = pr.signatoryStage === 'fully_signed' ? 'badge-signed'
            : (pr.signatoryStage === 'draft' ? 'badge-draft' : 'badge-routing');
        const search = (pr.prNumber + ' ' + pr.item + ' ' + pr.office + ' ' + pr.remarks + ' ' + pr.signatoryLabel).toLowerCase();
        const siblingBadge = pr.budgetProposalId
            ? ` <span class="count-chip" style="height:18px;padding:0 7px;font-size:9.5px;margin-left:4px;" title="${pr.siblingCount} ${pr.siblingCount === 1 ? 'PR' : 'PRs'} against ${escapeHtml(pr.budgetProposalCode || '')}${pr.siblingCount > 1 ? ' — open this one, then use Next to see the rest' : ''}">${escapeHtml(pr.budgetProposalCode || '')}${pr.siblingCount > 1 ? ' · ' + pr.siblingCount + ' PRs' : ''}</span>`
            : '';
        const itemCountBadge = pr.itemCount > 1
            ? ` <span class="count-chip" style="height:17px;padding:0 6px;font-size:9px;margin-left:4px;" title="${pr.itemCount} items bundled in this PR">${pr.itemCount} items</span>`
            : '';
        return `<tr data-pr-row data-pr-id="${pr.id}" data-office="${escapeHtml(pr.office)}" data-status-bucket="${pr.statusBucket || ''}" data-created-at="${pr.createdAt || ''}" data-search="${escapeHtml(search)}" tabindex="0">
            <td style="font-size:12px;font-weight:600;color:var(--s600);white-space:nowrap;max-width:120px;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(pr.office)}</td>
            <td style="font-size:12px;font-weight:700;color:var(--s500);white-space:nowrap;">${escapeHtml(pr.prNumber)}${siblingBadge}</td>
            <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(pr.item)}${itemCountBadge}</td>
            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">${escapeHtml(pr.dateSubmitted)}</td>
            <td><span class="badge ${stageBadge}" data-sig-badge="${pr.id}">${escapeHtml(pr.signatoryLabel)}</span></td>
        </tr>`;
    }

    function handleRefresh(json) {
        if (saving || statusDirty) return;
        if (remarksIn.value.trim()) return;
        if (returnIn.value.trim() || returnRemarks.style.display !== 'none') return;
        if (thirdSignerPanel.style.display !== 'none') return;

        const fresh = json.purchaseRequests || [];
        allPrs = fresh;

        if (tbody) {
            const freshById = new Map(fresh.map(p => [String(p.id), p]));
            const existingIds = new Set();

            getRows().forEach(row => {
                const id = row.dataset.prId;
                existingIds.add(id);
                const pr = freshById.get(id);
                if (!pr) return; // don't remove rows missing from the fresh data
                updateSigBadge(pr.id, pr.signatoryLabel, pr.signatoryStage);
                if (pr.statusBucket) row.dataset.statusBucket = pr.statusBucket;
            });

            fresh.forEach(pr => {
                if (pr.isTableRow && !existingIds.has(String(pr.id))) tbody.insertAdjacentHTML('beforeend', rowHtml(pr));
            });

            if (activePr) tbody.querySelector(`[data-pr-id="${activePr.id}"]`)?.classList.add('selected');

            applyPrSearchFilter();
        }

        if (activePr) {
            const freshActive = fresh.find(p => p.id === activePr.id);
            if (freshActive) {
                activePr = freshActive;
                document.getElementById('fSigLabel').textContent = activePr.signatoryLabel;
                statusSel.value = activePr.currentStatus ?? 'new';
                statusDirty = false;
                updateTimeline(activePr);
                updateRoutingButtons(activePr);
                logs[activePr.id] = (activePr.activityLog || []).map(e => ({
                    text: `Status: <strong>${e.status}</strong>` + (e.remarks && e.remarks !== '—' ? ` &mdash; ${e.remarks}` : ''),
                    time: e.timestamp,
                    atRaw: e.timestampRaw,
                }));
                (activePr.signatureLogs || []).forEach(l => {
                    let text = `<strong>${l.display}</strong>` + (l.by && l.by !== '—' ? ` by ${l.by}` : '') + (l.remarks ? ` &mdash; ${l.remarks}` : '');
                    if (l.photoUrl) {
                        text += `<br><a href="${l.photoUrl}" target="_blank" rel="noopener"><img src="${l.photoUrl}" alt="Signed document (signature blurred)" style="margin-top:6px;max-width:120px;border-radius:8px;border:1px solid #e2e8f0;"></a> <span style="font-size:10px;color:#64748b;">signature blurred for privacy</span>`;
                    } else if (l.photoStatus === 'pending' || l.photoStatus === 'failed') {
                        text += `<br><span style="font-size:11px;color:#854f0b;">photo withheld — processing</span>`
                            + (l.reprocessUrl ? ` <button type="button" class="sig-reprocess-btn" data-url="${l.reprocessUrl}" style="font-size:10px;font-weight:700;border:1px solid #fac775;background:#fdf7ec;color:#854f0b;border-radius:6px;padding:2px 8px;cursor:pointer;">Reprocess</button>` : '');
                    }
                    logs[activePr.id].push({ text, time: l.at, atRaw: l.atRaw, attachments: l.attachments || [] });
                });
                renderLog(activePr.id);
            }
        }
    }

    // app.js loads as a module script, which the spec always defers until
    // after the document finishes parsing — this classic inline script runs
    // immediately as the parser reaches it, before that, so calling
    // window.prismAutoRefresh here directly throws (ReferenceError) and
    // aborts every statement after it in this IIFE, including the Upload
    // Purchase Request wiring below. The 'load' event fires only once all
    // deferred module scripts have already run, so it's defined by then.
    if (refreshUrl) {
        window.addEventListener('load', () => window.prismAutoRefresh(refreshUrl, handleRefresh));
    }

    // ── Upload Purchase Request ──────────────────────────────────────────
    const extractUrl = JSON.parse(document.getElementById('extractPrUrlData').textContent);
    const createUrl  = JSON.parse(document.getElementById('createPrUrlData').textContent);

    const prOverlay       = document.getElementById('prModalOverlay');
    const prPpmpList      = document.getElementById('prPpmpList');
    const prMissingBox    = document.getElementById('prMissingBox');
    const prUploadStep    = document.getElementById('prUploadStep');
    const prChooseFileBtn = document.getElementById('prChooseFileBtn');
    const prFileInput     = document.getElementById('prFileInput');
    const prFileNameEl    = document.getElementById('prFileName');
    const prExtractStatus = document.getElementById('prExtractStatus');
    const prExtractWarn   = document.getElementById('prExtractWarn');
    const prReview        = document.getElementById('prReview');
    const prNumberInput   = document.getElementById('prNumberInput');
    const prTitleInput    = document.getElementById('prTitleInput');
    const prItemsBody     = document.getElementById('prItemsBody');
    const prItemsTotalEl  = document.getElementById('prItemsTotal');
    const prStatusMsg     = document.getElementById('prStatusMsg');
    const prSubmitBtn     = document.getElementById('prSubmitBtn');
    const prQuarterWrap   = document.getElementById('prQuarterWrap');
    const prQuarterSelect = document.getElementById('prQuarterSelect');
    const prValidation        = document.getElementById('prValidation');
    const prValidationSummary = document.getElementById('prValidationSummary');
    const prValidationWarnings = document.getElementById('prValidationWarnings');

    let selectedPpmpId = null;
    let selectedPpmpOfficeCode = null;
    let lastValidation = null;

    function money(n) {
        return '₱' + (Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function resetPrModal() {
        selectedPpmpId = null;
        selectedPpmpOfficeCode = null;
        lastValidation = null;
        prPpmpList.querySelectorAll('.pr-ppmp-row.selected').forEach(r => r.classList.remove('selected'));
        prMissingBox.style.display = 'none';
        prMissingBox.innerHTML = '';
        if (prQuarterWrap) prQuarterWrap.style.display = 'none';
        if (prQuarterSelect) prQuarterSelect.value = '';
        if (prValidation) prValidation.style.display = 'none';
        prUploadStep.style.display = 'none';
        prFileInput.value = '';
        prFileNameEl.textContent = '';
        prExtractStatus.classList.remove('show');
        prExtractWarn.classList.remove('show');
        prReview.classList.remove('show');
        prItemsBody.innerHTML = '';
        prNumberInput.value = '';
        prTitleInput.value = '';
        prStatusMsg.className = 'pr-status-msg';
        prSubmitBtn.disabled = true;
        recalcPrItemsTotal();
    }

    document.getElementById('btnOpenUploadPr').addEventListener('click', () => {
        resetPrModal();
        prOverlay.classList.add('open');
    });
    function closePrModal() { prOverlay.classList.remove('open'); }
    document.getElementById('prModalCloseBtn').addEventListener('click', closePrModal);
    document.getElementById('prCancelBtn').addEventListener('click', closePrModal);
    prOverlay.addEventListener('click', (e) => { if (e.target === prOverlay) closePrModal(); });

    // Selecting a specific PPMP row is what actually ties the new PR back to
    // that exact PPMP (budget_proposal_id) — not just "some approved PPMP for
    // this office" the way office-only matching would leave ambiguous.
    prPpmpList.querySelectorAll('.pr-ppmp-row').forEach(row => {
        row.addEventListener('click', () => {
            prPpmpList.querySelectorAll('.pr-ppmp-row.selected').forEach(r => r.classList.remove('selected'));
            row.classList.add('selected');
            selectedPpmpId = row.dataset.ppmpId;
            selectedPpmpOfficeCode = row.dataset.officeCode;

            const missing = JSON.parse(row.dataset.missing || '[]');
            prMissingBox.style.display = '';
            prMissingBox.innerHTML = missing.length
                ? missing.map(it => `
                    <div class="pr-missing-row">
                        <span class="name">${escapeHtml(it.name)}</span>
                        <span class="qty">${it.quantity}${it.unit ? ' ' + escapeHtml(it.unit) : ''}</span>
                    </div>`).join('')
                : '<p class="pr-missing-empty">Every item in this PPMP already has a Purchase Request.</p>';

            prQuarterWrap.style.display = '';
            prUploadStep.style.display = '';
            prReview.classList.remove('show');
            prSubmitBtn.disabled = true;
        });
    });

    // Changing the quarter changes which PPMP items the document is checked
    // against, so re-run the check on whatever file is already chosen.
    prQuarterSelect?.addEventListener('change', () => {
        if (prFileInput.files[0]) extractAndValidate();
    });

    prChooseFileBtn.addEventListener('click', () => prFileInput.click());

    function addPrItemRow(item) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" class="pr-item-name" value="${escapeHtml(item?.name ?? '')}"></td>
            <td><input type="text" class="pr-item-unit" style="width:70px;" value="${escapeHtml(item?.unit ?? '')}"></td>
            <td><input type="number" class="pr-item-num pr-item-qty" min="0.01" step="0.01" value="${item?.quantity ?? ''}"></td>
            <td><input type="number" class="pr-item-num pr-item-cost" min="0" step="0.01" value="${item?.unitCost ?? ''}"></td>
            <td class="pr-item-row-total" style="text-align:right;font-weight:700;white-space:nowrap;">${money((item?.quantity || 0) * (item?.unitCost || 0))}</td>
            <td><button type="button" class="pr-row-remove" title="Remove row"><i class="ti ti-trash"></i></button></td>`;
        prItemsBody.appendChild(tr);

        const qtyInput  = tr.querySelector('.pr-item-qty');
        const costInput = tr.querySelector('.pr-item-cost');
        const totalCell = tr.querySelector('.pr-item-row-total');
        function updateRowTotal() {
            totalCell.textContent = money((parseFloat(qtyInput.value) || 0) * (parseFloat(costInput.value) || 0));
            recalcPrItemsTotal();
        }
        qtyInput.addEventListener('input', updateRowTotal);
        costInput.addEventListener('input', updateRowTotal);
        tr.querySelector('.pr-row-remove').addEventListener('click', () => { tr.remove(); recalcPrItemsTotal(); });
    }

    function recalcPrItemsTotal() {
        let total = 0;
        prItemsBody.querySelectorAll('tr').forEach(tr => {
            const qty  = parseFloat(tr.querySelector('.pr-item-qty')?.value) || 0;
            const cost = parseFloat(tr.querySelector('.pr-item-cost')?.value) || 0;
            total += qty * cost;
        });
        prItemsTotalEl.textContent = 'Total: ' + money(total);
        // A document whose contents don't match the approved PPMP can't be
        // submitted — the server refuses it anyway, so don't invite the trip.
        const blocked = lastValidation && lastValidation.verdict !== 'passed';
        prSubmitBtn.disabled = prItemsBody.children.length === 0 || !!blocked;
    }

    document.getElementById('prAddItemBtn').addEventListener('click', () => addPrItemRow(null));

    /**
     * Paints the content-check result: a summary banner, per-row verdicts, and
     * the submit gate. A failing check disables submission outright — the
     * server refuses it too, this just says so before the round-trip.
     */
    function renderValidation(validation) {
        lastValidation = validation || null;

        prItemsBody.querySelectorAll('tr').forEach(tr => {
            tr.classList.remove('row-fail');
            tr.querySelector('.pr-item-verdict')?.remove();
        });

        if (!validation) {
            prValidation.style.display = 'none';
            return;
        }

        const passed = validation.verdict === 'passed';
        prValidation.style.display = '';
        prValidation.className = 'pr-validation ' + (passed ? 'pass' : 'fail');
        prValidationSummary.textContent = (passed ? '✓ ' : '✕ ') + (validation.summary || '');

        prValidationWarnings.innerHTML = (validation.warnings || [])
            .map(w => `<li>${escapeHtml(w)}</li>`).join('');

        // Verdicts are positional — the extract response and the rendered rows
        // come from the same list in the same order.
        const rows = [...prItemsBody.querySelectorAll('tr')];
        (validation.items || []).forEach((res, i) => {
            const tr = rows[i];
            if (!tr) return;
            const ok = res.verdict === 'passed';
            if (!ok) tr.classList.add('row-fail');
            const cell = tr.querySelector('td');
            if (!cell) return;
            const note = document.createElement('span');
            note.className = 'pr-item-verdict ' + (ok ? 'ok' : 'bad');
            note.textContent = (ok ? '✓ ' : '✕ ') + (res.reason || '');
            cell.appendChild(note);
        });

        recalcPrItemsTotal();
    }

    async function extractAndValidate() {
        const file = prFileInput.files[0];
        if (!file) return;

        prFileNameEl.textContent = file.name;
        prExtractWarn.classList.remove('show');
        prExtractStatus.classList.add('show');
        prReview.classList.remove('show');
        prSubmitBtn.disabled = true;
        renderValidation(null);

        try {
            const fd = new FormData();
            fd.append('file', file);
            // Sending these lets the server check the document's contents
            // against the chosen PPMP in the same round-trip.
            if (selectedPpmpId) fd.append('budget_proposal_id', selectedPpmpId);
            if (prQuarterSelect?.value) fd.append('quarter', prQuarterSelect.value);

            const resp = await fetch(extractUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();
            prExtractStatus.classList.remove('show');

            if (!resp.ok || !json.success) {
                prExtractWarn.textContent = 'Could not read the document automatically — enter the items manually below.';
                prExtractWarn.classList.add('show');
            } else {
                // The office is already known from the PPMP row picked in
                // step 1 — the document's own office field is only useful
                // here as a sanity check that the right file was uploaded.
                if (json.officeCode && selectedPpmpOfficeCode && json.officeCode !== selectedPpmpOfficeCode) {
                    prExtractWarn.textContent = `Heads up — this document looks like it's for "${json.officeCode}", but the PPMP you selected is for "${selectedPpmpOfficeCode}". Double-check before creating.`;
                    prExtractWarn.classList.add('show');
                }
                if (!json.items || !json.items.length) {
                    prExtractWarn.textContent = (prExtractWarn.classList.contains('show') ? prExtractWarn.textContent + ' ' : '') + 'No item rows were recognized — add them manually below.';
                    prExtractWarn.classList.add('show');
                }
                prNumberInput.value = json.prNumber || '';
                prTitleInput.value = json.projectName || '';
            }

            prItemsBody.innerHTML = '';
            (json.items || []).forEach(it => addPrItemRow(it));
            if (!json.items || !json.items.length) addPrItemRow(null);
            recalcPrItemsTotal();
            prReview.classList.add('show');
            renderValidation(json.validation);
        } catch {
            prExtractStatus.classList.remove('show');
            prExtractWarn.textContent = 'Network error while reading the document — you can still enter the items manually below.';
            prExtractWarn.classList.add('show');
            prItemsBody.innerHTML = '';
            addPrItemRow(null);
            recalcPrItemsTotal();
            prReview.classList.add('show');
        }
    }

    prFileInput.addEventListener('change', extractAndValidate);

    prSubmitBtn.addEventListener('click', async () => {
        prStatusMsg.className = 'pr-status-msg';

        if (!selectedPpmpId) {
            prStatusMsg.className = 'pr-status-msg error'; prStatusMsg.textContent = 'Select an approved PPMP first.'; return;
        }
        if (!prFileInput.files[0]) {
            prStatusMsg.className = 'pr-status-msg error'; prStatusMsg.textContent = 'Attach the PR document.'; return;
        }
        const prRows = [...prItemsBody.querySelectorAll('tr')];
        if (!prRows.length) {
            prStatusMsg.className = 'pr-status-msg error'; prStatusMsg.textContent = 'Add at least one item.'; return;
        }
        const items = prRows.map(tr => ({
            name: tr.querySelector('.pr-item-name').value.trim(),
            unit: tr.querySelector('.pr-item-unit').value.trim(),
            quantity: tr.querySelector('.pr-item-qty').value,
            unit_cost: tr.querySelector('.pr-item-cost').value,
        }));
        if (items.some(it => !it.name || !it.quantity || it.unit_cost === '')) {
            prStatusMsg.className = 'pr-status-msg error'; prStatusMsg.textContent = 'Every item needs a name, quantity, and unit cost.'; return;
        }

        prSubmitBtn.disabled = true;
        prSubmitBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Creating…';

        try {
            const fd = new FormData();
            fd.append('budget_proposal_id', selectedPpmpId);
            fd.append('pr_number', prNumberInput.value.trim());
            fd.append('title', prTitleInput.value.trim());
            if (prQuarterSelect?.value) fd.append('quarter', prQuarterSelect.value);
            fd.append('file', prFileInput.files[0]);
            items.forEach((it, i) => {
                fd.append(`items[${i}][name]`, it.name);
                fd.append(`items[${i}][unit]`, it.unit);
                fd.append(`items[${i}][quantity]`, it.quantity);
                fd.append(`items[${i}][unit_cost]`, it.unit_cost);
            });

            const resp = await fetch(createUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();
            if (resp.ok && json.success) {
                showToast(`${json.prNumber} created.`);
                closePrModal();
                setTimeout(() => window.location.reload(), 700);
            } else {
                prStatusMsg.className = 'pr-status-msg error';
                prStatusMsg.textContent = json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Could not create the Purchase Request.');
                // The server re-checks the reviewed list, which the user may
                // have edited after the file was read — show its verdict.
                if (json.validation) renderValidation(json.validation);
            }
        } catch {
            prStatusMsg.className = 'pr-status-msg error';
            prStatusMsg.textContent = 'Network error — please try again.';
        } finally {
            prSubmitBtn.innerHTML = '<i class="ti ti-check"></i> Create Purchase Request';
            recalcPrItemsTotal();   // owns the disabled state, incl. the validation gate
        }
    });
})();

</script>
@endpush
