@extends('prism.layouts.office-head')
@section('title', 'Market Scoping')

@push('page-css')
<style>
    /* ══ TOP CARD ══ */
    .top-card { background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); overflow: hidden; }
    .top-card-title { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 22px 16px; }
    .top-card-title-left { display: flex; align-items: center; gap: 12px; }
    .top-card-title-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .top-card-title-icon i { font-size: 20px; color: var(--crimson); }
    .top-card-title h1 { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .top-card-title p  { font-size: 12px; color: var(--txt3); font-weight: 500; margin-top: 2px; }
    .search-bar { display: flex; align-items: center; gap: 10px; padding: 0 22px 18px; }
    .search-wrap { position: relative; flex: 1; }
    .search-wrap i.si-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; color: var(--txt3); pointer-events: none; }
    .search-input { height: 42px; width: 100%; border-radius: var(--r-sm); border: 1.5px solid var(--border2); background: var(--bg); padding: 0 14px 0 38px; font-size: 13px; font-weight: 500; color: var(--txt); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); background: var(--white); }
    .search-input::placeholder { color: var(--txt3); }
    .btn-run { display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 42px; padding: 0 22px; border-radius: var(--r-sm); background: var(--crimson); color: #fff; border: none; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; box-shadow: 0 3px 12px rgba(139,26,28,.28); transition: all .18s; white-space: nowrap; flex-shrink: 0; }
    .btn-run:hover { background: var(--crimson-dark); transform: translateY(-1px); }
    .btn-run:disabled { background: var(--s400); box-shadow: none; cursor: not-allowed; transform: none; }
    .btn-run i { font-size: 15px; }

    /* ══ MAIN GRID ══ */
    .ms-grid { display: grid; grid-template-columns: minmax(0,1fr) 320px; gap: 14px; align-items: start; min-width: 0; }

    /* ── Results panel ── */
    .results-panel { background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); overflow: hidden; min-width: 0; max-width: 100%; }
    .results-panel-head { padding: 14px 18px 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--border2); }
    .results-head-left { display: flex; align-items: center; gap: 10px; }
    .results-title { font-size: 15px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .pill { display: inline-flex; align-items: center; gap: 4px; height: 22px; padding: 0 10px; border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .pill-gray  { background: var(--bg2); color: var(--txt2); border: 1px solid var(--border2); }
    .pill-green { background: var(--green-bg); color: var(--green); }
    .pill-amber { background: var(--amber-bg); color: var(--amber); }

    .smart-alert { margin: 12px 18px; display: flex; align-items: flex-start; gap: 8px; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: var(--r-sm); padding: 9px 13px; font-size: 12px; font-weight: 600; color: #92400E; }
    .smart-alert i { font-size: 15px; color: #F59E0B; flex-shrink: 0; margin-top: 1px; }
    .smart-alert span { flex: 1; }
    .smart-alert .alert-close { background: none; border: 0; cursor: pointer; padding: 0; display: flex; flex-shrink: 0; }
    .smart-alert .alert-close i { font-size: 13px; color: #92400E; margin-top: 2px; }

    .info-note { margin: 12px 18px 0; display: flex; align-items: center; gap: 7px; background: var(--bg2); border: 1px solid var(--border2); border-radius: var(--r-sm); padding: 8px 13px; font-size: 12px; font-weight: 500; color: var(--txt3); }
    .info-note i { font-size: 14px; flex-shrink: 0; }
    #noticeArea .smart-alert + .info-note, #noticeArea .info-note { margin-bottom: 0; }

    .results-list { padding: 0 18px 18px; display: flex; flex-direction: column; gap: 10px; max-height: calc(100vh - 320px); overflow-y: auto; }

    /* ── Reference card ── */
    .ref-card { display: flex; align-items: flex-start; gap: 12px; background: var(--white); border: 1.5px solid var(--border2); border-radius: var(--r-sm); padding: 14px; transition: all .18s; }
    .ref-card:hover { border-color: var(--crimson-border); box-shadow: var(--sh); }
    .ref-card.ref-attached { background: #FFF8F8; border-color: var(--crimson-border); }

    .ref-logo { width: 64px; height: 48px; border-radius: 8px; background: #F8F8F8; border: 1px solid var(--border2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; position: relative; }
    .ref-logo i { font-size: 22px; color: var(--txt3); }
    .ref-logo img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ref-logo.has-detail { cursor: pointer; }
    .ref-logo.has-detail .ref-logo-hover {
        position: absolute; inset: 0; background: rgba(20,20,20,.68); color: #fff;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity .15s; pointer-events: none;
    }
    .ref-logo.has-detail .ref-logo-hover i { color: #fff; font-size: 17px; }
    .ref-logo.has-detail:hover .ref-logo-hover { opacity: 1; }

    .ref-body { flex: 1; min-width: 0; overflow: hidden; }
    .ref-name { font-size: 13px; font-weight: 700; color: var(--txt); margin-bottom: 2px; line-height: 1.35; word-break: break-word; overflow-wrap: break-word; }
    .ref-supplier { font-size: 11.5px; color: var(--txt3); font-weight: 500; margin-bottom: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ref-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 6px; }
    .ref-tag { display: inline-flex; align-items: center; height: 20px; padding: 0 8px; border-radius: 5px; font-size: 10.5px; font-weight: 600; }
    .ref-tag.t-green  { background: var(--green-bg);  color: var(--green); }
    .ref-tag.t-blue   { background: var(--blue-bg);   color: var(--blue); }
    .ref-tag.t-amber  { background: var(--amber-bg);  color: var(--amber); }
    .ref-tag.t-purple { background: var(--purple-bg); color: var(--purple); }
    .ref-tag.t-gray   { background: var(--bg2); color: var(--txt2); }
    .ref-date { font-size: 11px; color: var(--txt3); font-weight: 500; }
    .ref-rating { display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--txt2); font-weight: 600; margin-bottom: 4px; }
    .ref-rating i { font-size: 12px; color: var(--amber); }

    .ref-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; min-width: 110px; }
    .ref-price { font-size: 20px; font-weight: 800; color: var(--crimson); letter-spacing: -.5px; line-height: 1; }
    .ref-actions { display: flex; gap: 6px; margin-top: 4px; }

    .btn-view-src { display: inline-flex; align-items: center; justify-content: center; gap: 4px; height: 32px; padding: 0 13px; border-radius: 8px; border: 1px solid var(--border2); background: var(--white); font-size: 12px; font-weight: 700; color: var(--txt2); cursor: pointer; font-family: 'Poppins', sans-serif; transition: all .15s; white-space: nowrap; text-decoration: none; }
    .btn-view-src:hover { border-color: var(--crimson); color: var(--crimson); }

    .btn-del-ref { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; border: 1px solid var(--border2); background: var(--white); color: var(--txt3); cursor: pointer; transition: all .15s; flex-shrink: 0; }
    .btn-del-ref:hover { border-color: var(--red); background: var(--red-bg); color: var(--red); }
    .btn-del-ref i { font-size: 13px; pointer-events: none; }

    .btn-attach-card { display: inline-flex; align-items: center; justify-content: center; gap: 4px; height: 32px; padding: 0 13px; border-radius: 8px; background: var(--crimson); color: #fff; border: none; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; box-shadow: 0 2px 8px rgba(139,26,28,.28); transition: all .18s; white-space: nowrap; }
    .btn-attach-card:hover { background: var(--crimson-dark); transform: translateY(-1px); }
    .btn-attach-card.is-attached { background: var(--green); box-shadow: 0 2px 8px rgba(22,101,52,.2); }
    .btn-attach-card.is-attached:hover { background: #14532d; transform: none; }
    .btn-attach-card i { font-size: 12px; }

    /* Empty state */
    .state-box { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 52px 20px; text-align: center; }
    .state-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .state-icon.si-init { background: var(--crimson-mid); border: 1px solid var(--crimson-border); }
    .state-icon.si-load { background: #DBEAFE; border: 1px solid #BFDBFE; }
    .state-icon.si-error { background: var(--red-bg); border: 1px solid #FECACA; }
    .state-icon i { font-size: 26px; }
    .state-icon.si-init i { color: var(--crimson); }
    .state-icon.si-load i { color: var(--blue); animation: spin .9s linear infinite; }
    .state-icon.si-error i { color: var(--red); }
    @keyframes spin { to { transform: rotate(360deg); } }
    .state-title { font-size: 14px; font-weight: 800; color: var(--txt); }
    .state-sub   { font-size: 12px; font-weight: 500; color: var(--txt3); max-width: 320px; line-height: 1.6; }

    /* ══ RIGHT PANEL ══ */
    .right-col { display: flex; flex-direction: column; gap: 12px; position: sticky; top: 20px; }
    .rp-card { background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); overflow: hidden; }
    .rp-head { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px 11px; border-bottom: 1px solid var(--border2); }
    .rp-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--crimson); }
    .rp-title { font-size: 13px; font-weight: 800; color: var(--txt); margin-top: 2px; }

    /* Attached table */
    .attached-wrap { overflow-x: auto; }
    .attached-table { width: 100%; border-collapse: collapse; font-size: 12px; color: var(--txt); }
    .attached-table th { padding: 9px 12px; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--txt3); border-bottom: 1px solid var(--border2); text-align: left; white-space: nowrap; background: var(--bg); }
    .attached-table td { padding: 10px 12px; border-bottom: 1px solid var(--border2); vertical-align: middle; }
    .attached-table tbody tr:last-child td { border-bottom: none; }
    .attached-table tbody tr { transition: background .12s; }
    .td-supplier { font-weight: 700; color: var(--txt); }
    .td-price { font-weight: 800; color: var(--crimson); white-space: nowrap; }
    .td-remove-btn { background: none; border: none; cursor: pointer; color: var(--txt3); padding: 4px; line-height: 1; border-radius: 6px; transition: background .12s, color .12s; }
    .td-remove-btn:hover { background: var(--red-bg, #fee2e2); color: var(--red, #991b1b); }
    .td-remove-btn i { font-size: 13px; display: block; }

    .table-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 28px 16px; text-align: center; }
    .table-empty i { font-size: 32px; color: #D1D5DB; }
    .table-empty p { font-size: 12px; color: var(--txt3); font-weight: 600; line-height: 1.6; }

    /* Save row */
    .rp-save-row { padding: 12px 16px 16px; border-top: 1px solid var(--border2); display: flex; flex-direction: column; gap: 8px; }
    .rp-save-hint { font-size: 11px; font-weight: 600; color: var(--txt3); text-align: center; }
    .rp-save-hint.hint-ok  { color: var(--green); }
    .rp-save-hint.hint-err { color: var(--red); }

    .btn-save-ref { display: flex; align-items: center; justify-content: center; gap: 7px; height: 42px; width: 100%; border-radius: var(--r-sm); background: var(--crimson); color: #fff; border: none; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; box-shadow: 0 3px 12px rgba(139,26,28,.28); transition: all .18s; }
    .btn-save-ref:disabled { background: var(--s400); box-shadow: none; cursor: not-allowed; }
    .btn-save-ref:not(:disabled):hover { background: var(--crimson-dark); transform: translateY(-1px); }
    .btn-save-ref.saved { background: var(--green); box-shadow: 0 3px 12px rgba(22,101,52,.2); }
    .btn-save-ref.saved:not(:disabled):hover { background: #14532d; transform: none; }
    .btn-save-ref i { font-size: 15px; }

    /* ── Modal ── */
    .modal-overlay { position: fixed; inset: 0; z-index: 200; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; padding: 20px; }
    .modal-card { background: var(--white); border-radius: var(--r-lg); box-shadow: var(--sh2); width: 100%; max-width: 480px; overflow: hidden; }
    .modal-head { display: flex; align-items: flex-start; gap: 14px; padding: 20px 22px 18px; border-bottom: 1px solid var(--border2); background: var(--crimson-mid); }
    .modal-head-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--white); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .modal-head-icon i { font-size: 20px; color: var(--crimson); }
    .modal-title { font-size: 15px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .modal-sub { font-size: 12px; color: var(--txt3); margin-top: 3px; line-height: 1.5; }
    .modal-body { padding: 18px 22px; display: flex; flex-direction: column; gap: 12px; }
    .modal-error { background: var(--red-bg); border: 1px solid #FECACA; border-radius: var(--r-sm); padding: 8px 12px; font-size: 12px; font-weight: 600; color: var(--red); }
    .modal-field { display: flex; flex-direction: column; gap: 5px; }
    .modal-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .modal-label { font-size: 9px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--txt3); }
    .modal-req { color: var(--crimson); }
    .modal-input { height: 40px; border-radius: var(--r-sm); border: 1px solid var(--border2); background: var(--bg); padding: 0 12px; font-size: 13px; font-weight: 500; color: var(--txt); font-family: 'Poppins', sans-serif; outline: none; width: 100%; transition: border-color .15s, box-shadow .15s; }
    .modal-input:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); background: var(--white); }
    .modal-footer { padding: 14px 22px 18px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border2); }
    .btn-modal-skip { height: 40px; padding: 0 20px; border-radius: var(--r-sm); border: 1.5px solid var(--border2); background: transparent; font-size: 13px; font-weight: 700; color: var(--txt2); cursor: pointer; font-family: 'Poppins', sans-serif; transition: all .15s; }
    .btn-modal-skip:hover { border-color: var(--crimson); color: var(--crimson); }
    .btn-modal-add { display: inline-flex; align-items: center; gap: 7px; height: 40px; padding: 0 22px; border-radius: var(--r-sm); background: var(--crimson); color: #fff; border: none; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; box-shadow: 0 3px 12px rgba(139,26,28,.28); transition: all .18s; }
    .btn-modal-add:hover { background: var(--crimson-dark); }
    .btn-modal-add:disabled { background: var(--s400); box-shadow: none; cursor: not-allowed; }
    .btn-modal-add i { font-size: 15px; }

    /* ── Toast ── */
    .ms-toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); z-index: 300; background: #166534; color: #fff; font-size: 13px; font-weight: 700; border-radius: 99px; padding: 10px 24px; box-shadow: 0 4px 20px rgba(0,0,0,.18); white-space: nowrap; }

    @media (max-width: 1100px) { .ms-grid { grid-template-columns: 1fr; } .right-col { position: static; } }

    /* ── Budget input ── */
    .budget-wrap { position: relative; display: flex; align-items: center; flex-shrink: 0; }
    .budget-prefix { position: absolute; left: 11px; font-size: 13px; font-weight: 700; color: var(--txt3); pointer-events: none; z-index: 1; }
    .budget-input { height: 42px; width: 170px; border-radius: var(--r-sm); border: 1.5px solid var(--border2); background: var(--bg); padding: 0 12px 0 24px; font-size: 13px; font-weight: 500; color: var(--txt); font-family: 'Poppins', sans-serif; outline: none; transition: border-color .15s, box-shadow .15s; }
    .budget-input:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); background: var(--white); }
    .budget-input::placeholder { color: var(--txt3); font-size: 12px; }

    /* ── Advantageous reason ── */
    .advantageous-reason { font-size: 11px; font-weight: 500; color: #92400E; margin-top: 5px; line-height: 1.5; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 5px; padding: 5px 8px; }

    /* ── Autocomplete dropdown ── */
    .suggest-dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r-sm); box-shadow: 0 8px 24px rgba(0,0,0,.12); z-index: 50; overflow: hidden; display: none; }
    .suggest-dropdown.open { display: block; }
    .suggest-item { display: flex; align-items: center; gap: 9px; padding: 10px 14px; font-size: 13px; font-weight: 500; color: var(--txt); cursor: pointer; transition: background .1s; }
    .suggest-item:hover, .suggest-item.active { background: var(--crimson-mid); }
    .suggest-item i { font-size: 14px; color: var(--txt3); flex-shrink: 0; }
    .suggest-item .sg-type { margin-left: auto; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--txt3); }

    /* ── Filter / sort bar ── */
    .filter-bar { display: flex; align-items: center; gap: 8px; padding: 10px 18px; border-bottom: 1px solid var(--border2); background: var(--bg); flex-wrap: wrap; }
    .filter-bar label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--txt3); }
    .filter-sel { height: 32px; border-radius: 8px; border: 1px solid var(--border2); background: var(--white); padding: 0 10px; font-size: 12px; font-weight: 600; color: var(--txt); font-family: 'Poppins', sans-serif; outline: none; cursor: pointer; }
    .filter-sel:focus { border-color: var(--crimson); }
    .filter-price { height: 32px; width: 90px; border-radius: 8px; border: 1px solid var(--border2); background: var(--white); padding: 0 8px; font-size: 12px; font-weight: 500; color: var(--txt); font-family: 'Poppins', sans-serif; outline: none; }
    .filter-price:focus { border-color: var(--crimson); }
    .filter-clear { height: 32px; padding: 0 12px; border-radius: 8px; border: 1px solid var(--border2); background: var(--white); font-size: 11px; font-weight: 700; color: var(--txt2); cursor: pointer; font-family: 'Poppins', sans-serif; transition: all .15s; }
    .filter-clear:hover { border-color: var(--crimson); color: var(--crimson); }

    /* ── Did you mean ── */
    .dym-box { margin: 12px 18px 0; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--r-sm); padding: 10px 14px; font-size: 12.5px; font-weight: 600; color: #1d4ed8; }
    .dym-box a { color: #1d4ed8; text-decoration: underline; cursor: pointer; }

    /* ── MPS button in header ── */
    .btn-mps-doc { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 8px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--border2); background: var(--white); color: var(--txt2); text-decoration: none; font-family: 'Poppins', sans-serif; transition: all .15s; flex-shrink: 0; }
    .btn-mps-doc:hover { border-color: var(--crimson); color: var(--crimson); }
    .btn-mps-doc-done { border-color: #86efac; background: #f0fdf4; color: #166534; }
    .btn-mps-doc-done:hover { border-color: #4ade80; }
</style>
@endpush

@section('content')
<div class="page-shell">

    {{-- TOP CARD --}}
    <div class="top-card">
        <div class="top-card-title">
            <div class="top-card-title-left">
                <div class="top-card-title-icon"><i class="ti ti-search"></i></div>
                <div>
                    <h1>Market Scoping</h1>
                    <p>Search market prices and attach references to your proposal items.</p>
                </div>
            </div>
            <a href="{{ route('office-head.market-scoping.mps', $proposalId ? ['proposal' => $proposalId] : []) }}" class="btn-mps-doc{{ $survey ? ' btn-mps-doc-done' : '' }}">
                <i class="ti {{ $survey ? 'ti-circle-check' : 'ti-file-description' }}"></i>
                {{ $survey ? 'View Submitted Market Study' : 'View Market Study' }}
            </a>
        </div>
        <div class="search-bar">
            <div class="search-wrap">
                <i class="ti ti-search si-icon"></i>
                <input id="marketQueryInput" class="search-input" type="search" autocomplete="off"
                       placeholder="Search a new item (e.g. Laptop Intel i7 16GB RAM)…">
                <div id="suggestDropdown" class="suggest-dropdown"></div>
            </div>
            <div class="budget-wrap">
                <span class="budget-prefix">₱</span>
                <input id="marketBudgetInput" class="budget-input" type="number" min="0" step="0.01"
                       placeholder="Budget (optional)">
            </div>
            <button id="runMarketBtn" class="btn-run" type="button" title="Searches live prices and flags items that look like good value for your budget">
                <i class="ti ti-brand-google"></i>Run Market Scoping
            </button>
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div class="ms-grid">

        {{-- LEFT: Reference cards --}}
        <div class="results-panel">
            <div class="results-panel-head">
                <div class="results-head-left">
                    <span class="results-title">Market References</span>
                    <span id="refCount" class="pill pill-gray">0 results</span>
                </div>
            </div>

            {{-- Service notices (quota / AI matcher availability) --}}
            <div id="noticeArea"></div>

            {{-- Filter / Sort bar --}}
            <div class="filter-bar" id="filterBar">
                <label>Sort</label>
                <select id="sortSel" class="filter-sel">
                    <option value="match">Best Match</option>
                    <option value="score_desc">Match Score: High → Low</option>
                    <option value="price_asc">Price: Low → High</option>
                    <option value="price_desc">Price: High → Low</option>
                    <option value="store_az">Store: A → Z</option>
                    <option value="reviews_desc">Most Reviewed → Least Reviewed</option>
                    <option value="rating_desc">Highest Rated → Lowest Rated</option>
                </select>

                <label>Price</label>
                <input id="priceMin" class="filter-price" type="number" min="0" placeholder="Min ₱">
                <span style="color:var(--txt3);font-size:11px;">–</span>
                <input id="priceMax" class="filter-price" type="number" min="0" placeholder="Max ₱">
                <button type="button" class="filter-clear" id="filterClear">Clear</button>
            </div>

            <div class="results-list" id="resultsContainer">
                <div id="searchPrompt" class="state-box">
                    <div class="state-icon si-init"><i class="ti ti-search"></i></div>
                    <p class="state-title">Search for market prices</p>
                    <p class="state-sub">Type an item name above and click <strong>Run Market Scoping</strong> to fetch live price references.</p>
                </div>
                <div id="dynamicResults"></div>

            </div>
        </div>

        {{-- RIGHT: Attached references + detail --}}
        <div class="right-col">
            <div class="rp-card">
                <div class="rp-head">
                    <div>
                        <p class="rp-eyebrow">Proposal Reference List</p>
                        <p class="rp-title">Attached References</p>
                    </div>
                    <span id="attachedCount" class="pill pill-gray">0 / 3</span>
                </div>

                <div class="attached-wrap">
                    <table class="attached-table">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Price / unit</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="attachedTableBody">
                            <tr id="tableEmptyRow">
                                <td colspan="3">
                                    <div class="table-empty">
                                        <i class="ti ti-clipboard-list"></i>
                                        <p>No references attached yet.<br>Click <strong>Attach</strong> on a card.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rp-save-row">
                    <p class="rp-save-hint" id="saveHint">Attach exactly 3 references to continue.</p>
                    <button class="btn-save-ref" id="saveRefBtn" type="button" disabled>
                        <i class="ti ti-link"></i>Attach to PPMP
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ── Add Item Modal ── --}}
<div id="addItemModal" class="modal-overlay" style="display:none" role="dialog" aria-modal="true">
    <div class="modal-card">
        <div class="modal-head">
            <div class="modal-head-icon"><i class="ti ti-file-plus"></i></div>
            <div>
                <h2 class="modal-title">Add Item to Proposal</h2>
                <p class="modal-sub">This item isn't in your proposal yet. Fill in the details to add it with the 3 attached references.</p>
            </div>
        </div>
        <div class="modal-body">
            <p id="modalError" class="modal-error" style="display:none"></p>
            <div class="modal-field">
                <label class="modal-label">Item Name</label>
                <input id="modalItemName" class="modal-input" type="text" placeholder="Item name">
            </div>
            <div class="modal-grid2">
                <div class="modal-field">
                    <label class="modal-label">Unit</label>
                    <input id="modalUnit" class="modal-input" type="text" value="unit">
                </div>
                <div class="modal-field">
                    <label class="modal-label">Quantity <span class="modal-req">*</span></label>
                    <input id="modalQty" class="modal-input" type="number" min="1" placeholder="0">
                </div>
            </div>
            <div class="modal-grid2">
                <div class="modal-field">
                    <label class="modal-label">Estimated Unit Cost</label>
                    <input id="modalCost" class="modal-input" type="number" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="modal-field">
                    <label class="modal-label">Target Quarter <span class="modal-req">*</span></label>
                    <select id="modalQuarter" class="modal-input">
                        <option value="Q1">Q1 (Jan–Mar)</option>
                        <option value="Q2">Q2 (Apr–Jun)</option>
                        <option value="Q3">Q3 (Jul–Sep)</option>
                        <option value="Q4">Q4 (Oct–Dec)</option>
                    </select>
                </div>
            </div>
            <div class="modal-field">
                <label class="modal-label">Category <span class="modal-req">*</span></label>
                <select id="modalCategory" class="modal-input">
                    <option value="Sched 9 - Supplies">Sched 9 — Supplies and Materials</option>
                    <option value="Sched 16 - Capital Outlay">Sched 16 — Capital Outlay</option>
                    <option value="ICT Equipment">ICT Equipment</option>
                    <option value="Office Equipment">Office Equipment</option>
                    <option value="Laboratory Equipment">Laboratory Equipment</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button id="modalSkipBtn" class="btn-modal-skip" type="button">Skip</button>
            <button id="modalAddBtn" class="btn-modal-add" type="button">
                <i class="ti ti-circle-plus"></i>Add to Proposal
            </button>
        </div>
    </div>
</div>

{{-- ── Toast ── --}}
<div id="msToast" class="ms-toast" style="display:none"></div>
@endsection

@push('scripts')
<script type="application/json" id="msProposalItems">@json($proposalItems)</script>
<script type="application/json" id="msSelectedRefs">@json($selectedRefs)</script>
<script type="application/json" id="msSurveyLocked">@json(isset($survey) && $survey !== null)</script>
<script type="application/json" id="msExistingItemRefs">@json($existingItemRefs ?? [])</script>

<script>
(function () {

    const proposalId     = @json($proposalId ?? null);
    const existingItemId = @json($existingItemId ?? null);
    const budgetProposalUrl = '{{ route("office-head.budget-proposal") }}' + (proposalId ? ('?proposal=' + proposalId) : '');
    const resolveSourceUrl  = '{{ route("office-head.market-scoping.resolve-source") }}';
    const productDetailsUrl = '{{ route("office-head.market-scoping.product-details") }}';

    // For Google Shopping results (page_token present), route the "Source" click through
    // the backend so it resolves to the actual merchant page instead of a Google Shopping
    // intermediary link. Results without a page_token (e.g. the local price aggregator)
    // are already direct store links, so they're used as-is.
    function sourceHrefFor(url, pageToken) {
        if (!pageToken) return url;
        const params = new URLSearchParams({ page_token: pageToken, fallback: url });
        return resolveSourceUrl + '?' + params.toString();
    }

    const attached   = {};   /* refId → ref data */
    const MAX_REFS   = 3;
    const mpsLocked  = JSON.parse(document.getElementById('msSurveyLocked').textContent);

    // Arrived via an item's "Add reference" link — that item's already-saved
    // references must start out attached, not blank, or finishing this flow would
    // silently unselect them in favor of whatever gets picked next.
    (function seedExistingRefs() {
        if (!existingItemId) return;
        let refs = [];
        try { refs = JSON.parse(document.getElementById('msExistingItemRefs').textContent || '[]'); } catch { refs = []; }
        refs.forEach(ref => {
            attached[ref.id] = ref;
            addTableRow(ref.id, ref);
        });
        if (refs.length) updateCount();
    })();

    /* ── Lock UI when MPS has been submitted ── */
    if (mpsLocked) {
        document.addEventListener('DOMContentLoaded', () => {
            const lockBanner = document.createElement('div');
            lockBanner.style.cssText = 'display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:10px 16px;margin-bottom:14px;font-size:12px;font-weight:600;color:#166534;';
            lockBanner.innerHTML = '<i class="ti ti-lock" style="font-size:16px;color:#16a34a;"></i> Market Study has been submitted. References are locked &mdash; no changes allowed.';
            const grid = document.querySelector('.ms-grid');
            if (grid) grid.parentNode.insertBefore(lockBanner, grid);

            document.querySelectorAll('[data-delete-ref], .btn-attach-card, #runMarketBtn, #saveRefBtn').forEach(el => {
                el.setAttribute('disabled', 'disabled');
                el.style.opacity = '.4';
                el.style.pointerEvents = 'none';
                el.style.cursor = 'not-allowed';
            });
            const searchInput = document.getElementById('marketQueryInput');
            if (searchInput) { searchInput.disabled = true; searchInput.style.opacity = '.5'; }
        });
    }

    /* ── Attach / detach ──────────────────────────────────────────────────────
       Shared by: live search result cards, pre-loaded DB reference cards, AND
       refs pre-loaded from an item's already-saved selection (which have no
       corresponding card at all — only the summary table row's own × button). */
    window.detachRef = function (refId) {
        delete attached[refId];
        removeTableRow(refId);

        const card = document.getElementById('card-' + refId);
        if (card) {
            card.classList.remove('ref-attached');
            const btn = card.querySelector('.btn-attach-card');
            if (btn) { btn.innerHTML = '<i class="ti ti-paperclip"></i>Attach'; btn.classList.remove('is-attached'); }
        }
        updateCount();
    };

    window.attachRef = function (refId) {
        const card = document.getElementById('card-' + refId);
        if (!card) return;

        if (attached[refId]) {
            window.detachRef(refId);
            return;
        }

        /* block if already at max */
        if (Object.keys(attached).length >= MAX_REFS) {
            const hint = document.getElementById('saveHint');
            hint.textContent = 'Maximum of 3 references reached. Remove one first.';
            hint.className = 'rp-save-hint hint-err';
            setTimeout(() => updateCount(), 2000);
            return;
        }
        /* attach */
        const d = card.dataset;
        attached[refId] = {
            id:       refId,
            name:     d.name,
            price:    parseFloat(d.priceRaw),
            priceStr: d.price,
            supplier: d.supplier,
            source:   d.source,
            date:     d.date,
            url:      d.url,
            pageToken: d.pageToken,
            specs:    d.specs,
            itemId:   d.itemId,
        };
        card.classList.add('ref-attached');
        const btn = card.querySelector('.btn-attach-card[data-ref-id="' + refId + '"]');
        if (btn) { btn.innerHTML = '<i class="ti ti-check"></i>Attached'; btn.classList.add('is-attached'); }
        addTableRow(refId, attached[refId]);

        updateCount();
    };

    /* ── Table helpers ── */
    function addTableRow(refId, ref) {
        const emptyRow = document.getElementById('tableEmptyRow');
        if (emptyRow) emptyRow.style.display = 'none';

        const tbody = document.getElementById('attachedTableBody');
        const tr = document.createElement('tr');
        tr.id = 'tr-' + refId;
        tr.innerHTML =
            '<td class="td-supplier">' + esc(ref.supplier) + '</td>' +
            '<td class="td-price">₱' + esc(ref.priceStr) + '</td>' +
            '<td><button type="button" class="td-remove-btn" onclick="window.detachRef(\'' + esc(String(refId)) + '\')" title="Remove"><i class="ti ti-x"></i></button></td>';
        tbody.appendChild(tr);
    }

    function removeTableRow(refId) {
        const tr = document.getElementById('tr-' + refId);
        if (tr) tr.remove();
        if (Object.keys(attached).length === 0) {
            document.getElementById('tableEmptyRow').style.display = '';
        }
    }

    function updateCount() {
        const n    = Object.keys(attached).length;
        const hint = document.getElementById('saveHint');
        const btn  = document.getElementById('saveRefBtn');

        document.getElementById('attachedCount').textContent = n + ' / 3';

        if (n === 0) {
            hint.textContent = 'Attach exactly 3 references to continue.';
            hint.className   = 'rp-save-hint';
            btn.disabled = true;
        } else if (n < MAX_REFS) {
            hint.textContent = (MAX_REFS - n) + ' more reference' + (MAX_REFS - n > 1 ? 's' : '') + ' needed.';
            hint.className   = 'rp-save-hint';
            btn.disabled = true;
        } else {
            hint.textContent = '3 references ready — click to attach to proposal.';
            hint.className   = 'rp-save-hint hint-ok';
            btn.disabled = false;
        }
    }

    let pendingRefsData      = null;
    // Set below (from ?unit=&quantity=&quarter=&justification=) only when arriving
    // via the PPMP "Run Market Scoping" shortcut with a not-yet-added item — lets
    // "Attach to PPMP" create the item straight away instead of popping up
    // "Add Item to Proposal" and asking the office head to retype what they
    // already filled in on the PPMP tab.
    let carriedNewItemFields = null;

    /* ── Attach to Proposal button ── */
    document.getElementById('saveRefBtn').addEventListener('click', function () {
        if (Object.keys(attached).length < MAX_REFS) return;

        const query = (queryInput?.value || '').trim();

        /* No search done — refs already saved, just go to proposal */
        if (!query) {
            window.location.href = budgetProposalUrl;
            return;
        }

        this.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite"></i>Checking…';
        this.disabled  = true;

        fetch('{{ route("office-head.market-scoping.attach") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ query, refs: Object.values(attached), proposal_id: proposalId, item_id: existingItemId }),
        })
        .then(r => r.json())
        .then(data => {
            this.innerHTML = '<i class="ti ti-link"></i>Attach to PPMP';
            this.disabled  = false;

            if (!data.success) {
                document.getElementById('saveHint').textContent = data.message || 'Something went wrong.';
                document.getElementById('saveHint').className   = 'rp-save-hint hint-err';
                return;
            }

            if (data.item_exists) {
                showMsToast('References attached to "' + data.item_name + '".');
                document.getElementById('saveHint').textContent = 'Redirecting to PPMP…';
                document.getElementById('saveHint').className   = 'rp-save-hint hint-ok';
                setTimeout(() => { window.location.href = budgetProposalUrl; }, 1400);
            } else if (carriedNewItemFields) {
                autoAddItemWithCarriedFields(data);
            } else {
                openAddItemModal(data);
            }
        })
        .catch(() => {
            this.innerHTML = '<i class="ti ti-alert-circle"></i>Error';
            this.disabled  = false;
            document.getElementById('saveHint').textContent = 'Network error. Try again.';
            document.getElementById('saveHint').className   = 'rp-save-hint hint-err';
        });
    });

    function openAddItemModal(data) {
        pendingRefsData = data.refs_data;
        document.getElementById('modalItemName').value = data.query || '';
        document.getElementById('modalCost').value     = data.average_price ? parseFloat(data.average_price).toFixed(2) : '';
        document.getElementById('modalUnit').value     = 'unit';
        document.getElementById('modalQty').value      = '';
        document.getElementById('modalQuarter').value  = 'Q1';
        document.getElementById('modalCategory').value = 'Sched 9 - Supplies';
        document.getElementById('modalError').style.display = 'none';
        document.getElementById('addItemModal').style.display = 'flex';
        setTimeout(() => document.getElementById('modalQty').focus(), 80);
    }

    async function autoAddItemWithCarriedFields(data) {
        const hint = document.getElementById('saveHint');
        hint.textContent = 'Adding item to proposal…';
        hint.className   = 'rp-save-hint hint-ok';

        try {
            const res  = await fetch('{{ route("office-head.market-scoping.add-item-with-refs") }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({
                    description:       data.query || '',
                    unit:              carriedNewItemFields.unit,
                    quantity:          parseFloat(carriedNewItemFields.quantity),
                    estimatedUnitCost: data.average_price ? parseFloat(data.average_price) : 0,
                    targetQuarter:     carriedNewItemFields.quarter,
                    // Not collected on the PPMP tab's Add Item form (nor asked
                    // there), same default the "Add Item to Proposal" modal itself
                    // starts on — office head can still edit it after redirect.
                    category:          'Sched 9 - Supplies',
                    refs:              data.refs_data,
                    proposal_id:       proposalId,
                }),
            });
            const json = await res.json();
            if (json.success) {
                showMsToast('Item added to proposal!');
                hint.textContent = 'Redirecting to PPMP…';
                setTimeout(() => { window.location.href = json.redirect; }, 1000);
            } else {
                // Couldn't add automatically (e.g. no draft proposal to attach to)
                // — fall back to the modal, pre-filled, rather than losing the
                // 3 already-attached references.
                openAddItemModal(data);
                document.getElementById('modalItemName').value = data.query || '';
                document.getElementById('modalUnit').value     = carriedNewItemFields.unit || 'unit';
                document.getElementById('modalQty').value       = carriedNewItemFields.quantity || '';
                document.getElementById('modalQuarter').value   = carriedNewItemFields.quarter || 'Q1';
                document.getElementById('modalError').textContent = json.message || 'Could not add automatically — please confirm the details.';
                document.getElementById('modalError').style.display = '';
                hint.textContent = '3 references ready — click to attach to proposal.';
                hint.className   = 'rp-save-hint hint-ok';
            }
        } catch {
            openAddItemModal(data);
            document.getElementById('modalError').textContent = 'Network error — please confirm the details.';
            document.getElementById('modalError').style.display = '';
            hint.textContent = '3 references ready — click to attach to proposal.';
            hint.className   = 'rp-save-hint hint-ok';
        }
    }

    document.getElementById('modalSkipBtn').addEventListener('click', function () {
        document.getElementById('addItemModal').style.display = 'none';
        pendingRefsData = null;
    });

    document.getElementById('addItemModal').addEventListener('click', function (e) {
        if (e.target === this) { this.style.display = 'none'; pendingRefsData = null; }
    });

    document.getElementById('modalAddBtn').addEventListener('click', async function () {
        const name    = document.getElementById('modalItemName').value.trim();
        const unit    = document.getElementById('modalUnit').value.trim() || 'unit';
        const qty     = parseFloat(document.getElementById('modalQty').value);
        const cost    = parseFloat(document.getElementById('modalCost').value);
        const quarter = document.getElementById('modalQuarter').value;
        const cat     = document.getElementById('modalCategory').value;
        const errEl   = document.getElementById('modalError');

        if (!name || !qty || isNaN(qty) || !cost || isNaN(cost) || !quarter || !cat) {
            errEl.textContent = 'Please fill in all required fields.';
            errEl.style.display = '';
            return;
        }
        errEl.style.display = 'none';

        this.disabled = true;
        this.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite"></i>Adding…';

        try {
            const res  = await fetch('{{ route("office-head.market-scoping.add-item-with-refs") }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({ description: name, unit, quantity: qty, estimatedUnitCost: cost, targetQuarter: quarter, category: cat, refs: pendingRefsData, proposal_id: proposalId }),
            });
            const json = await res.json();
            if (json.success) {
                document.getElementById('addItemModal').style.display = 'none';
                showMsToast('Item added to proposal!');
                setTimeout(() => { window.location.href = json.redirect; }, 1000);
            } else {
                errEl.textContent = json.message || 'Failed to save.';
                errEl.style.display = '';
                this.disabled = false;
                this.innerHTML = '<i class="ti ti-circle-plus"></i>Add to Proposal';
            }
        } catch {
            errEl.textContent = 'Network error. Try again.';
            errEl.style.display = '';
            this.disabled = false;
            this.innerHTML = '<i class="ti ti-circle-plus"></i>Add to Proposal';
        }
    });

    function showMsToast(msg) {
        const t = document.getElementById('msToast');
        t.textContent = msg;
        t.style.display = 'flex';
        setTimeout(() => { t.style.display = 'none'; }, 3000);
    }

    /* ── Live search ── */
    const runBtn     = document.getElementById('runMarketBtn');
    const queryInput = document.getElementById('marketQueryInput');
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const runUrl     = '{{ route("office-head.market-scoping.run") }}';

    async function runSearch() {
        const query = (queryInput?.value || '').trim();
        if (!query) { queryInput?.focus(); return; }

        /* Extract individual keywords as specs for semantic filtering */
        const STOP  = new Set(['for','the','and','with','of','per','mga','ang','na','ng','is','an','a']);
        const specs = query.split(/[\s,]+/).map(w => w.toLowerCase().trim()).filter(w => w.length >= 3 && !STOP.has(w));
        const budget = parseFloat(document.getElementById('marketBudgetInput')?.value || 0) || 0;

        /* Hide search prompt once user starts a real search */
        const sp = document.getElementById('searchPrompt');
        if (sp) sp.style.display = 'none';

        runBtn.disabled = true;
        runBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite"></i>Fetching…';

        const dynDiv = document.getElementById('dynamicResults');
        dynDiv.innerHTML = '<div class="state-box"><div class="state-icon si-load"><i class="ti ti-loader-2"></i></div><p class="state-title">Fetching market prices…</p><p class="state-sub">Searching for <strong>' + esc(query) + '</strong>.</p></div>';
        renderNotices({});   /* clear notices from the previous search */

        try {
            const res  = await fetch(runUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({ item_id: 'manual', query, specs, budget }),
            });
            const data = await res.json();

            renderNotices(data);

            if (!res.ok || !data.success) {
                let html = '<div class="state-box"><div class="state-icon si-error"><i class="ti ti-alert-triangle"></i></div><p class="state-title">No results found</p><p class="state-sub">' + esc(data.message || 'Try different keywords.') + '</p></div>';
                if (data.suggestion) {
                    html = '<div class="dym-box">Did you mean: <a onclick="document.getElementById(\'marketQueryInput\').value=this.textContent;window.msRunSearch();">' + esc(data.suggestion) + '</a>?</div>' + html;
                }
                dynDiv.innerHTML = html;
                lastResults = [];
                return;
            }

            lastResults = data.results;
            applyFilters();
            saveSearchState(query, budget);

        } catch (e) {
            dynDiv.innerHTML = '<div class="state-box"><div class="state-icon si-error"><i class="ti ti-alert-triangle"></i></div><p class="state-title">Network error</p><p class="state-sub">Check your connection and try again.</p></div>';
        } finally {
            runBtn.disabled = false;
            runBtn.innerHTML = '<i class="ti ti-brand-google"></i>Run Market Scoping';
        }
    }

    function renderNotices(data) {
        const area = document.getElementById('noticeArea');
        if (!area) return;
        area.innerHTML = '';

        /* Strict checks: older cached responses without these keys show nothing */
        if (data.quota_exhausted === true) {
            const alert = document.createElement('div');
            alert.className = 'smart-alert';
            alert.innerHTML =
                '<i class="ti ti-alert-triangle"></i>' +
                '<span>Live Google Shopping prices are temporarily unavailable (search quota reached). Results may be limited to the local price database or cached data.</span>' +
                '<button type="button" class="alert-close" aria-label="Dismiss notice"><i class="ti ti-x"></i></button>';
            alert.querySelector('.alert-close').addEventListener('click', () => alert.remove());
            area.appendChild(alert);
        }

        if (data.matcher_available === false) {
            const note = document.createElement('div');
            note.className = 'info-note';
            note.innerHTML = '<i class="ti ti-info-circle"></i>AI match scoring unavailable — showing unranked results.';
            area.appendChild(note);
        }
    }

    window.msRefLogoError = function (img) {
        // Replace only the broken <img>, not the whole .ref-logo — a sibling
        // .ref-logo-hover overlay (see showProductDetails) must survive this.
        const icon = document.createElement('i');
        icon.className = 'ti ti-shopping-bag';
        img.replaceWith(icon);
    };

    window.showProductDetails = async function (pageToken, name) {
        window.prismInfoModal({
            title: name || 'Product Details',
            bodyHtml: '<p style="font-size:13px;color:var(--txt3);text-align:center;padding:20px 0;"><i class="ti ti-loader-2" style="animation:spin .7s linear infinite;font-size:18px;display:block;margin:0 auto 8px;"></i>Loading details…</p>',
        });

        try {
            const res  = await fetch(productDetailsUrl + '?' + new URLSearchParams({ page_token: pageToken }), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                document.getElementById('prismInfoBody').innerHTML =
                    '<p style="font-size:13px;color:var(--txt3);text-align:center;padding:20px 0;">' +
                    esc(data.message || 'Details are not available for this item.') + '</p>';
                return;
            }

            const d = data.details;
            const thumbHtml = d.thumbnail
                ? '<img src="' + esc(d.thumbnail) + '" alt="" style="width:100%;max-height:160px;object-fit:contain;border-radius:8px;background:#F8F8F8;border:1px solid var(--border2);margin-bottom:12px;">'
                : '';
            const brandHtml = d.brand
                ? '<p style="font-size:12px;color:var(--txt3);font-weight:600;margin-bottom:4px;">' + esc(d.brand) + '</p>'
                : '';
            const priceHtml = d.price_range
                ? '<p style="font-size:14px;font-weight:800;color:var(--crimson);margin-bottom:10px;">' + esc(d.price_range) + '</p>'
                : '';
            const featuresHtml = d.features && d.features.length
                ? '<dl style="display:grid;grid-template-columns:auto 1fr;gap:6px 12px;font-size:12.5px;">' +
                    d.features.map(f =>
                        '<dt style="font-weight:700;color:var(--txt2);">' + esc(f.title) + '</dt>' +
                        '<dd style="color:var(--txt);">' + esc(f.value) + '</dd>'
                    ).join('') +
                  '</dl>'
                : '<p style="font-size:12.5px;color:var(--txt3);">No additional specs available for this item.</p>';
            const storeCountHtml = d.store_count
                ? '<p style="font-size:11px;color:var(--txt3);margin-top:12px;">Available from ' + d.store_count + ' seller' + (d.store_count > 1 ? 's' : '') + '.</p>'
                : '';

            document.getElementById('prismInfoBody').innerHTML =
                thumbHtml + brandHtml + priceHtml + featuresHtml + storeCountHtml;
        } catch {
            document.getElementById('prismInfoBody').innerHTML =
                '<p style="font-size:13px;color:var(--txt3);text-align:center;padding:20px 0;">Network error. Please try again.</p>';
        }
    };

    function renderDynamic(results, container) {
        container.innerHTML = '';
        results.forEach((item, idx) => {
            const id       = 'live-' + idx;
            const name     = item.name || 'Unknown product';
            const price    = item.price || 0;
            const priceFmt = item.price_formatted || ('₱' + price.toLocaleString());
            const source   = item.source || 'Google Shopping';
            const date     = item.date_retrieved || '—';
            const url      = item.source_url || '#';

            const card = document.createElement('div');
            card.className = 'ref-card';
            card.id = 'card-' + id;
            card.dataset.refId    = id;
            card.dataset.name     = name;
            card.dataset.price    = priceFmt.replace('₱','').replace(/,/g,'');
            card.dataset.priceRaw = price;
            card.dataset.supplier = source;
            card.dataset.source   = source;
            card.dataset.date     = date;
            card.dataset.url      = url;
            card.dataset.pageToken = item.page_token || '';
            card.dataset.specs    = item.snippet || '';
            card.dataset.itemId   = '';
            const imgUrl = item.image_url || item.source_icon || '';
            const logoHtml = imgUrl
                ? '<img src="' + esc(imgUrl) + '" alt="" loading="lazy" onerror="window.msRefLogoError(this)">'
                : '<i class="ti ti-shopping-bag"></i>';

            // Only Google Shopping results (page_token present) have a details endpoint
            // to fetch from — separate from the "Source" button, which sends the user
            // to the actual merchant site instead of showing info inside the app.
            const hasDetail = !!item.page_token;
            const logoOverlay = hasDetail
                ? '<div class="ref-logo-hover"><i class="ti ti-info-circle"></i></div>'
                : '';

            const isAdv      = item.is_advantageous === true;
            const advReason  = item.reason || '';
            const rating     = parseFloat(item.rating);

            const sourceTag = '<span class="ref-tag ' + (item.is_official === true ? 't-green' : 't-blue') + '">' + esc(source) + '</span>';
            const cachedTag = item.cached === true
                ? '<span class="ref-tag t-gray"><i class="ti ti-clock" style="font-size:10px;margin-right:3px"></i>Cached</span>'
                : '';
            const ratingHtml = !isNaN(rating) && rating > 0
                ? '<div class="ref-rating"><i class="ti ti-star-filled"></i>' + rating.toFixed(1) +
                  (item.reviews ? '<span style="color:var(--txt3);font-weight:500">(' + Number(item.reviews).toLocaleString() + ' reviews)</span>' : '') +
                  '</div>'
                : '';

            const advBlock = isAdv && advReason
                ? '<p class="advantageous-reason"><i class="ti ti-bulb" style="font-size:11px;margin-right:4px"></i>' + esc(advReason) + '</p>'
                : '';

            card.innerHTML =
                '<div class="ref-logo' + (hasDetail ? ' has-detail' : '') + '"' +
                    (hasDetail ? ' title="Click to see product details" onclick="window.showProductDetails(\'' + esc(item.page_token) + '\', \'' + esc(name).replace(/'/g, '&#39;') + '\')"' : '') +
                    '>' + logoHtml + logoOverlay + '</div>' +
                '<div class="ref-body">' +
                    '<p class="ref-name">' + esc(name) + '</p>' +
                    '<p class="ref-supplier">' + esc(source) + '</p>' +
                    '<div class="ref-tags">' + sourceTag + cachedTag + '</div>' +
                    ratingHtml +
                    advBlock +
                    '<p class="ref-date">Retrieved: ' + esc(date) + '</p>' +
                '</div>' +
                '<div class="ref-right">' +
                    '<p class="ref-price">' + esc(priceFmt) + '</p>' +
                    '<div class="ref-actions">' +
                        '<a class="btn-view-src" href="' + esc(sourceHrefFor(url, item.page_token)) + '" target="_blank" rel="noopener"><i class="ti ti-external-link" style="font-size:12px"></i>Source</a>' +
                        '<button class="btn-attach-card" type="button" data-ref-id="' + id + '" onclick="window.attachLive(\'' + id + '\')"><i class="ti ti-paperclip"></i>Attach</button>' +
                    '</div>' +
                '</div>';
            container.appendChild(card);
        });
    }

    window.attachLive = function(id) {
        const card = document.getElementById('card-' + id);
        if (!card) return;

        if (attached[id]) {
            window.detachRef(id);
            return;
        }

        if (Object.keys(attached).length >= MAX_REFS) {
            const hint = document.getElementById('saveHint');
            hint.textContent = 'Maximum of 3 references reached. Remove one first.';
            hint.className = 'rp-save-hint hint-err';
            setTimeout(() => updateCount(), 2000);
            return;
        }
        const d = card.dataset;
        attached[id] = { id, name: d.name, price: parseFloat(d.priceRaw), priceStr: parseFloat(d.priceRaw).toLocaleString('en-PH', {minimumFractionDigits:2}), supplier: d.supplier, source: d.source, date: d.date, url: d.url, pageToken: d.pageToken, specs: d.specs, itemId: d.itemId };
        card.classList.add('ref-attached');
        card.querySelector('.btn-attach-card').innerHTML = '<i class="ti ti-check"></i>Attached';
        card.querySelector('.btn-attach-card').classList.add('is-attached');
        addTableRow(id, attached[id]);
        updateCount();
    };

    /* Delegate clicks on pre-loaded DB ref buttons (attach + delete) */
    const resultsContainer = document.getElementById('resultsContainer');
    resultsContainer && resultsContainer.addEventListener('click', function (e) {
        const attachBtn = e.target.closest('.btn-attach-card[data-ref-id]');
        if (attachBtn) { window.attachRef(parseInt(attachBtn.dataset.refId, 10)); return; }

        const delBtn = e.target.closest('.btn-del-ref');
        if (!delBtn) return;

        const card = delBtn.closest('.ref-card');
        const url  = delBtn.dataset.deleteUrl;
        if (!url || !card) return;

        delBtn.disabled = true;
        delBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite"></i>';

        fetch(url, {
            method:  'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                card.style.transition = 'opacity .2s';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 200);
                /* also update the ref count pill */
                const pill = document.getElementById('refCount');
                if (pill) {
                    const m = pill.textContent.match(/\d+/);
                    if (m) pill.textContent = (parseInt(m[0]) - 1) + ' references found';
                }
                window.prismToast('Reference removed.');
            } else {
                delBtn.disabled = false;
                delBtn.innerHTML = '<i class="ti ti-x"></i>';
            }
        })
        .catch(() => { delBtn.disabled = false; delBtn.innerHTML = '<i class="ti ti-x"></i>'; });
    });

    runBtn    && runBtn.addEventListener('click', runSearch);
    window.msRunSearch = runSearch;

    /* ── Filters + sort (client-side on lastResults) ── */
    let lastResults = [];

    function applyFilters() {
        const dynDiv = document.getElementById('dynamicResults');
        const sortBy   = document.getElementById('sortSel').value;
        const min      = parseFloat(document.getElementById('priceMin').value) || 0;
        const max      = parseFloat(document.getElementById('priceMax').value) || Infinity;

        let list = lastResults.filter(r => {
            const p = parseFloat(r.price) || 0;
            if (p < min || p > max) return false;
            return true;
        });

        if (sortBy === 'price_asc')   list = list.slice().sort((a, b) => (a.price || 0) - (b.price || 0));
        if (sortBy === 'price_desc')  list = list.slice().sort((a, b) => (b.price || 0) - (a.price || 0));
        if (sortBy === 'score_desc')  list = list.slice().sort((a, b) => (b.match_score || 0) - (a.match_score || 0));
        if (sortBy === 'store_az')    list = list.slice().sort((a, b) => (a.source || '').localeCompare(b.source || ''));
        if (sortBy === 'reviews_desc') list = list.slice().sort((a, b) => (parseFloat(b.reviews) || 0) - (parseFloat(a.reviews) || 0));
        if (sortBy === 'rating_desc')  list = list.slice().sort((a, b) => (parseFloat(b.rating) || 0) - (parseFloat(a.rating) || 0));
        /* 'match' keeps server order (already ranked by match score) */

        if (list.length === 0) {
            dynDiv.innerHTML = '<div class="state-box"><div class="state-icon si-init"><i class="ti ti-filter-off"></i></div><p class="state-title">No results match your filters</p><p class="state-sub">Adjust the price range or clear the filters.</p></div>';
        } else {
            renderDynamic(list, dynDiv);
        }
        document.getElementById('refCount').textContent = list.length + ' of ' + lastResults.length + ' result' + (lastResults.length !== 1 ? 's' : '');
    }

    ['sortSel', 'priceMin', 'priceMax'].forEach(id => {
        const el = document.getElementById(id);
        el && el.addEventListener(el.tagName === 'SELECT' || el.type === 'checkbox' ? 'change' : 'input', applyFilters);
    });
    document.getElementById('filterClear')?.addEventListener('click', () => {
        document.getElementById('sortSel').value  = 'match';
        document.getElementById('priceMin').value = '';
        document.getElementById('priceMax').value = '';
        applyFilters();
    });

    /* ── Autocomplete suggestions ── */
    const suggestUrl = '{{ route("office-head.market-scoping.suggestions") }}';
    const dropdown   = document.getElementById('suggestDropdown');
    let suggestTimer = null;
    let activeIdx    = -1;

    function closeSuggest() { dropdown.classList.remove('open'); dropdown.innerHTML = ''; activeIdx = -1; }

    function renderSuggest(items) {
        if (!items.length) { closeSuggest(); return; }
        dropdown.innerHTML = items.map((s, i) =>
            '<div class="suggest-item" data-idx="' + i + '" data-text="' + esc(s.text) + '">' +
            '<i class="ti ' + (s.fuzzy ? 'ti-sparkles' : (s.type === 'item' ? 'ti-clipboard-list' : 'ti-history')) + '"></i>' +
            esc(s.text) +
            '<span class="sg-type">' + (s.fuzzy ? 'Did you mean?' : (s.type === 'item' ? 'My item' : 'Past search')) + '</span>' +
            '</div>').join('');
        dropdown.classList.add('open');
        activeIdx = -1;

        dropdown.querySelectorAll('.suggest-item').forEach(el => {
            el.addEventListener('mousedown', e => {
                e.preventDefault();
                queryInput.value = el.dataset.text;
                closeSuggest();
                runSearch();
            });
        });
    }

    queryInput && queryInput.addEventListener('input', function () {
        clearTimeout(suggestTimer);
        const q = this.value.trim();
        if (q.length < 2) { closeSuggest(); return; }
        suggestTimer = setTimeout(async () => {
            try {
                const res  = await fetch(suggestUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                renderSuggest(json.suggestions || []);
            } catch { closeSuggest(); }
        }, 250);
    });

    queryInput && queryInput.addEventListener('keydown', e => {
        const items = dropdown.querySelectorAll('.suggest-item');
        if (dropdown.classList.contains('open') && items.length) {
            if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, items.length - 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); }
            else if (e.key === 'Escape') { closeSuggest(); return; }
            else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                queryInput.value = items[activeIdx].dataset.text;
                closeSuggest();
                runSearch();
                return;
            }
            items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') return;
        }
        if (e.key === 'Enter') { closeSuggest(); runSearch(); }
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.search-wrap')) closeSuggest();
    });

    /* ── Auto-search when arriving from the PPMP "Run scoping →" link ── */
    (function () {
        const params = new URLSearchParams(window.location.search);
        const q      = params.get('q');
        const budget = params.get('budget');
        const itemId = params.get('item');

        // Item's already-encoded Unit Cost carried over as the scoping budget —
        // only present when the PPMP item was encoded before scoping was run.
        const budgetInput = document.getElementById('marketBudgetInput');
        if (budget && budgetInput) budgetInput.value = budget;

        // Only set when there's no ?item= — an itemId means the item already
        // exists in the proposal table, so the normal "attach to existing item"
        // path applies and there's nothing here to carry into a new-item add.
        const unitParam     = params.get('unit');
        const quantityParam = params.get('quantity');
        const quarterParam  = params.get('quarter');
        if (!itemId && unitParam && quantityParam && quarterParam) {
            carriedNewItemFields = {
                unit: unitParam,
                quantity: quantityParam,
                quarter: quarterParam,
                justification: params.get('justification') || '',
            };
        }

        if (q && queryInput) {
            queryInput.value = q;
            setTimeout(runSearch, 280);
        } else {
            restoreSearchState();
        }

        /* Adaptive save row: arriving from the PPMP tab (?q= or ?from=ppmp)
           keeps "Attach to PPMP" primary; standalone scoping leads with
           "Save Market Study as File" instead. */
        const fromPpmp = q !== null || params.get('from') === 'ppmp';
        if (!fromPpmp) {
            const saveRow  = document.querySelector('.rp-save-row');
            const saveLink = saveRow?.querySelector('a[href*="mps"]');
            const hint     = document.getElementById('saveHint');
            if (saveRow && saveLink) {
                saveRow.insertBefore(saveLink, saveRow.querySelector('#saveRefBtn'));
                saveLink.style.background = 'var(--crimson)';
                saveLink.style.color = '#fff';
                saveLink.style.borderColor = 'var(--crimson)';
                saveLink.onmouseover = null;
                saveLink.onmouseout = null;
            }
            if (hint) hint.textContent = 'Save this scoping as a Market Study file, or attach 3 references directly to a PPMP item.';
        }
    })();

    function esc(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Persist in-progress search across navigation (e.g. "Save Market Study"
       and back) — sessionStorage survives a full page reload within the tab,
       so results don't disappear just because the user stepped away. ── */
    const SEARCH_STATE_KEY = 'prismMarketScopingState';

    function saveSearchState(query, budget) {
        try {
            sessionStorage.setItem(SEARCH_STATE_KEY, JSON.stringify({ query, budget, results: lastResults }));
        } catch { /* storage full/unavailable — not critical, just skip persisting */ }
    }

    function restoreSearchState() {
        let saved;
        try { saved = JSON.parse(sessionStorage.getItem(SEARCH_STATE_KEY) || 'null'); } catch { saved = null; }
        if (!saved || !Array.isArray(saved.results) || !saved.results.length) return false;

        if (queryInput) queryInput.value = saved.query || '';
        const budgetInput = document.getElementById('marketBudgetInput');
        if (budgetInput && saved.budget) budgetInput.value = saved.budget;

        const sp = document.getElementById('searchPrompt');
        if (sp) sp.style.display = 'none';

        lastResults = saved.results;
        applyFilters();
        return true;
    }

})();
</script>
@endpush
