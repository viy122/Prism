@extends('prism.layouts.office-head')
@section('title', 'My PPMPs')

@php
    $proposalCollection   = collect($proposals);
    $proposalCount        = $proposalCollection->count();
    $activeReviewCount    = $proposalCollection->whereIn('status', ['Submitted', 'Under Review', 'Endorsed'])->count();
    $returnedCount        = $proposalCollection->where('status', 'Returned')->count();
    $approvedAmount       = $proposalCollection->where('status', 'Approved')->sum('totalAmount');
@endphp

@push('page-css')
<style>
        :root {
            --m:     #681012;
            --m-dk:  #4e0c0e;
            --white: #ffffff;
            --s50:   #f8fafc;
            --s100:  #f1f5f9;
            --s200:  #e2e8f0;
            --s300:  #cbd5e1;
            --s400:  #94a3b8;
            --s500:  #64748b;
            --s600:  #475569;
            --s700:  #334155;
            --s900:  #0f172a;
            --sh:    0 2px 8px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.04);
            --sh-md: 0 4px 20px rgba(15,23,42,.09), 0 1px 4px rgba(15,23,42,.04);
        }

        .content {
            padding: 32px 32px 64px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ─── Card ─── */
        .card {
            background: var(--white);
            border: 1px solid var(--s200);
            border-radius: 18px;
            padding: 26px;
            box-shadow: var(--sh);
        }
        .card-eyebrow {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--m);
            margin-bottom: 4px;
        }
        .card-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--s900);
            letter-spacing: -.2px;
        }
        .card-sub {
            font-size: 13px;
            color: var(--s500);
            margin-top: 4px;
        }
        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        /* ─── Stats bar ─── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .stat-box {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 14px;
            padding: 18px 20px;
        }
        .stat-box dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 6px;
        }
        .stat-box dd {
            font-size: 28px;
            font-weight: 800;
            color: var(--s900);
            line-height: 1;
        }
        .stat-box dd.maroon { color: var(--m); }
        .stat-box dd.sm { font-size: 17px; }

        /* ─── Filters row ─── */
        .filters-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 16px;
            align-items: end;
        }
        .field-group { display: flex; flex-direction: column; gap: 7px; }
        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--s700);
        }
        .field-select {
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--s300);
            background: var(--white);
            padding: 0 14px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--s900);
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-select:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.08);
        }

        /* ─── Buttons ─── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 20px;
            border-radius: 10px;
            background: var(--m);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(104,16,18,.2);
            transition: background .2s;
            white-space: nowrap;
        }
        .btn-primary:hover { background: var(--m-dk); }
        .btn-primary svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Pill ─── */
        .pill {
            display: inline-flex;
            align-items: center;
            height: 28px;
            padding: 0 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .pill-gray   { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
        .pill-maroon { background: rgba(104,16,18,.08); color: var(--m); border: 1px solid rgba(104,16,18,.15); }

        /* ─── 2-col layout ─── */
        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 400px;
            gap: 24px;
            align-items: start;
        }
        .col-sticky {
            position: sticky;
            top: 86px;
        }

        /* ─── Proposal queue ─── */
        .queue-scroll {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 68vh;
            overflow-y: auto;
            padding-right: 2px;
        }
        .proposal-row {
            border: 1px solid var(--s200);
            border-radius: 14px;
            background: var(--white);
            padding: 18px 20px;
            cursor: pointer;
            transition: border-color .15s, background .15s, box-shadow .15s;
            outline: none;
        }
        .proposal-row:hover {
            border-color: rgba(104,16,18,.28);
            background: rgba(104,16,18,.03);
            box-shadow: var(--sh);
        }
        .proposal-row:focus {
            border-color: var(--m);
            box-shadow: 0 0 0 3px rgba(104,16,18,.1);
        }
        .proposal-row-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .proposal-row-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--s900);
            line-height: 1.4;
        }
        .proposal-row-meta {
            font-size: 12px;
            font-weight: 600;
            color: var(--s500);
            margin-top: 3px;
        }
        .proposal-row-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .proposal-stat-item dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 4px;
        }
        .proposal-stat-item dd {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
        }
        .proposal-stat-item dd.danger { color: #991b1b; }

        /* ─── Timeline panel ─── */
        .timeline-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .timeline-meta-box {
            background: var(--s50);
            border: 1px solid var(--s200);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .timeline-meta-box dt {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--s400);
            margin-bottom: 5px;
        }
        .timeline-meta-box dd {
            font-size: 13px;
            font-weight: 700;
            color: var(--s900);
        }

        /* ─── Timeline empty state ─── */
        .timeline-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 200px;
            border: 1.5px dashed var(--s300);
            border-radius: 14px;
            background: var(--s50);
            padding: 32px;
            text-align: center;
            color: var(--s500);
            font-size: 13px;
            line-height: 1.65;
        }
        .timeline-empty svg {
            width: 40px; height: 40px;
            stroke: rgba(104,16,18,.5); fill: none;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }

        /* ─── Responsive ─── */
        @media (max-width: 1280px) {
            .two-col { grid-template-columns: minmax(0,1fr) 340px; }
            .stats-bar { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 1024px) {
            .content { padding: 20px 20px 48px; gap: 20px; }
            .two-col { grid-template-columns: 1fr; }
            .col-sticky { position: static; }
            .filters-row { grid-template-columns: 1fr 1fr; }
            .filters-row .btn-primary { grid-column: span 2; }
        }
        @media (max-width: 640px) {
            .stats-bar { grid-template-columns: 1fr 1fr; }
            .filters-row { grid-template-columns: 1fr; }
            .filters-row .btn-primary { grid-column: span 1; }
        }

        /* ── Success toast ── */
        .mp-toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); z-index: 300; display: flex; align-items: center; gap: 10px; background: #166534; color: #fff; font-size: 13px; font-weight: 700; border-radius: 99px; padding: 12px 24px; box-shadow: 0 4px 20px rgba(0,0,0,.2); white-space: nowrap; opacity: 0; transition: opacity .3s; pointer-events: none; }
        .mp-toast.show { opacity: 1; pointer-events: auto; }
        .mp-toast i { font-size: 17px; }
</style>
@endpush

@section('content')
    <div class="content">

        {{-- Stats + Filters card --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Overview</p>
                    <h2 class="card-title">PPMP Summary</h2>
                </div>
                <a class="btn-primary" href="{{ route('office-head.budget-proposal') }}">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    New PPMP
                </a>
            </div>

            <dl class="stats-bar">
                <div class="stat-box">
                    <dt>Proposals</dt>
                    <dd>{{ $proposalCount }}</dd>
                </div>
                <div class="stat-box">
                    <dt>Active Review</dt>
                    <dd class="maroon">{{ $activeReviewCount }}</dd>
                </div>
                <div class="stat-box">
                    <dt>Returned</dt>
                    <dd>{{ $returnedCount }}</dd>
                </div>
                <div class="stat-box">
                    <dt>Approved Amount</dt>
                    <dd class="sm">PHP {{ number_format($approvedAmount) }}</dd>
                </div>
            </dl>

            <div class="filters-row" aria-label="Proposal filters">
                <div class="field-group">
                    <label class="field-label" for="proposalStatusFilter">Status</label>
                    <select id="proposalStatusFilter" class="field-select">
                        <option value="all">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label class="field-label" for="proposalYearFilter">Fiscal Year</label>
                    <select id="proposalYearFilter" class="field-select">
                        <option value="all">All fiscal years</option>
                        @foreach ($fiscalYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- 2-col: queue + timeline --}}
        <div class="two-col">

            {{-- Proposal queue --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Proposal queue</p>
                        <h2 class="card-title">All Proposals</h2>
                        <p class="card-sub">Select a proposal to review its approval movement.</p>
                    </div>
                    <span class="pill pill-gray" id="proposalVisibleCount">{{ $proposalCount }} shown</span>
                </div>

                <div class="queue-scroll" id="proposalRows">
                    @foreach ($proposals as $proposal)
                        @php
                            $latestEvent = collect($proposal['timeline'])->last();
                            $isReturned  = $proposal['status'] === 'Returned';
                        @endphp
                        <article
                            class="proposal-row"
                            data-proposal-row
                            data-proposal-id="{{ $proposal['id'] }}"
                            data-status="{{ $proposal['status'] }}"
                            data-year="{{ $proposal['fiscalYear'] }}"
                            tabindex="0"
                            aria-label="{{ $proposal['title'] }}"
                        >
                            <div class="proposal-row-head">
                                <div class="min-w-0">
                                    <p class="proposal-row-title">{{ $proposal['title'] }}</p>
                                    <p class="proposal-row-meta">FY {{ $proposal['fiscalYear'] }} &middot; Submitted {{ $proposal['dateSubmitted'] }}</p>
                                </div>
                                <x-prism.status-badge :status="$proposal['status']" />
                            </div>

                            <dl class="proposal-row-stats">
                                <div class="proposal-stat-item">
                                    <dt>Amount</dt>
                                    <dd>PHP {{ number_format($proposal['totalAmount']) }}</dd>
                                </div>
                                <div class="proposal-stat-item">
                                    <dt>Current Step</dt>
                                    <dd>{{ $latestEvent['step'] ?? 'Pending' }}</dd>
                                </div>
                                <div class="proposal-stat-item">
                                    <dt>Action</dt>
                                    <dd class="{{ $isReturned ? 'danger' : '' }}">{{ $isReturned ? 'Revise' : 'Track' }}</dd>
                                </div>
                            </dl>
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Timeline panel --}}
            <div class="card col-sticky" id="proposalTimelinePanel" aria-live="polite">
                <div class="card-head">
                    <div class="min-w-0" style="flex:1;">
                        <p class="card-eyebrow">Approval movement</p>
                        <h2 class="card-title" id="timelineTitle" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Select a proposal</h2>
                        <p class="card-sub" id="timelineMeta">Timeline details will appear here.</p>
                    </div>
                    <span class="pill pill-gray" id="timelineStatusBadge">Status</span>
                </div>

                <div class="timeline-meta-grid">
                    <div class="timeline-meta-box">
                        <dt>Total Amount</dt>
                        <dd id="timelineAmount">PHP 0</dd>
                    </div>
                    <div class="timeline-meta-box">
                        <dt>Next Action</dt>
                        <dd id="timelineAction">Select</dd>
                    </div>
                </div>

                <div id="timelineContent" class="timeline-empty">
                    <svg viewBox="0 0 24 24"><path d="M3 3h18v18H3z" rx="2" /><path d="M9 9h6M9 12h6M9 15h4"/><circle cx="17" cy="17" r="3"/><path d="M19.5 19.5L21 21"/></svg>
                    <p>Select a proposal to view timestamps, remarks, and revision status.</p>
                </div>
            </div>

        </div>{{-- /two-col --}}

    </div>{{-- /content --}}

    <div id="mpToast" class="mp-toast" role="status" aria-live="polite">
        <i class="ti ti-circle-check"></i>
        <span id="mpToastMsg"></span>
    </div>
@endsection

@push('scripts')
<script type="application/json" id="proposalData">@json($proposals)</script>
<script>
(function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('submitted') === '1') {
        const toast = document.getElementById('mpToast');
        document.getElementById('mpToastMsg').textContent = 'Your proposal has been submitted successfully.';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
        history.replaceState({}, '', window.location.pathname);
    }
})();
</script>
@endpush
