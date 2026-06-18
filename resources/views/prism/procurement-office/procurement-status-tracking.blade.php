@extends('prism.layouts.app')
@section('title', 'Procurement Status Tracking | Procurement Office')

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

    .filters-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .filter-group { display: flex; flex-direction: column; gap: 6px; }
    .filter-group label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s500); }
    .filter-select { height: 42px; width: 100%; padding: 0 14px; border-radius: 10px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 13px; font-weight: 500; font-family: 'Poppins', sans-serif; cursor: pointer; outline: none; transition: border-color .2s; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
    .filter-select:focus { border-color: var(--m); box-shadow: 0 0 0 3px var(--crimson-mid); }

    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 65vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 12px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background .12s; }
    tbody tr:hover { background: var(--crimson-mid); }
    tbody tr.row-delayed { background: #fff5f5; }
    tbody tr.row-delayed:hover { background: #fee2e2; }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-completed   { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
    .badge-in-progress { background: #e6f1fb; color: #185fa5; border: 1px solid #b5d4f4; }
    .badge-pending     { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-delayed     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    .badge-overdue     { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }

    .update-cell { display: flex; flex-direction: column; gap: 7px; min-width: 200px; }

    .inline-select { height: 36px; width: 100%; padding: 0 10px; border-radius: 8px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 12px; font-weight: 500; font-family: 'Poppins', sans-serif; outline: none; cursor: pointer; transition: border-color .2s; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
    .inline-select:focus { border-color: var(--m); }

    .inline-textarea { width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--s200); background: var(--white); color: var(--s700); font-size: 12px; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 60px; outline: none; transition: border-color .2s; line-height: 1.5; }
    .inline-textarea:focus { border-color: var(--m); }
    .inline-textarea::placeholder { color: var(--s300); }

    .btn-save-inline { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 8px; background: var(--white); color: var(--m); font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: 1px solid rgba(139,26,28,.3); transition: background .15s, border-color .15s; white-space: nowrap; }
    .btn-save-inline:hover { background: rgba(139,26,28,.05); border-color: var(--m); }
    .btn-save-inline svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .btn-save-inline.saved { background: #eaf3de; color: #3b6d11; border-color: #c0dd97; }

    .remarks-text { font-size: 12px; color: var(--s500); line-height: 1.6; max-width: 200px; }
    .remarks-text:empty::before { content: '—'; color: var(--s300); }

    @media (max-width: 1024px) { .content { padding: 16px 16px 40px; } .filters-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 640px) { .filters-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-list-check"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Procurement Office</p>
            <h1 class="page-hdr-title">Procurement Status Tracking</h1>
            <p class="page-hdr-sub">Monitor approved items being processed, update statuses, and record remarks for requesting offices.</p>
        </div>
    </div>

    <div class="card">
        <div class="filters-grid">
            <div class="filter-group">
                <label for="filterOffice">Office</label>
                <select class="filter-select" id="filterOffice">
                    <option value="all">All offices</option>
                    @foreach ($offices as $office)
                        <option value="{{ $office }}">{{ $office }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="filterQuarter">Quarter</label>
                <select class="filter-select" id="filterQuarter">
                    <option value="all">All quarters</option>
                    @foreach ($quarters as $quarter)
                        <option value="{{ $quarter }}">{{ $quarter }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="filterStatus">Status</label>
                <select class="filter-select" id="filterStatus">
                    <option value="all">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Approved items being processed</p>
                <h2 class="card-title">Procurement Item Tracker</h2>
            </div>
            <span class="count-chip" id="visibleCount">{{ count($trackingItems) }} shown</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Item</th>
                        <th>Approved Amount</th>
                        <th>Target Quarter</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody id="trackerBody">
                    @foreach ($trackingItems as $item)
                        <tr
                            data-procurement-track-row
                            data-office="{{ $item['office'] }}"
                            data-quarter="{{ $item['targetQuarter'] }}"
                            data-status="{{ $item['currentStatus'] }}"
                            class="{{ strtolower($item['currentStatus']) === 'delayed' ? 'row-delayed' : '' }}"
                        >
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $item['office'] }}</td>
                            <td style="font-size:13px;color:var(--s900);font-weight:500;max-width:200px;">{{ $item['item'] }}</td>
                            <td style="font-size:13px;font-weight:600;color:var(--s700);white-space:nowrap;">PHP {{ number_format($item['approvedAmount']) }}</td>
                            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $item['targetQuarter'] }}</td>
                            <td>
                                @php
                                    $slug = match(strtolower($item['currentStatus'])) {
                                        'completed'   => 'badge-completed',
                                        'in progress' => 'badge-in-progress',
                                        'pending'     => 'badge-pending',
                                        'delayed'     => 'badge-delayed',
                                        default       => 'badge-overdue',
                                    };
                                @endphp
                                <span class="badge {{ $slug }}" data-track-status-pill>{{ $item['currentStatus'] }}</span>
                            </td>
                            <td>
                                <p class="remarks-text" data-track-remarks-display>{{ $item['remarks'] }}</p>
                            </td>
                            <td>
                                <div class="update-cell">
                                    <select class="inline-select" data-track-status-select aria-label="Update status for {{ $item['item'] }}">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected($status === $item['currentStatus'])>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <textarea class="inline-textarea" rows="2" data-track-remarks-input placeholder="Add a remark…">{{ $item['remarks'] }}</textarea>
                                    <button class="btn-save-inline" type="button" data-track-update>
                                        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                        Save
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const filterOffice  = document.getElementById('filterOffice');
    const filterQuarter = document.getElementById('filterQuarter');
    const filterStatus  = document.getElementById('filterStatus');
    const visibleCount  = document.getElementById('visibleCount');
    const rows          = document.querySelectorAll('[data-procurement-track-row]');

    function applyFilters() {
        const office  = filterOffice.value;
        const quarter = filterQuarter.value;
        const status  = filterStatus.value;
        let visible   = 0;

        rows.forEach(row => {
            const match =
                (office  === 'all' || row.dataset.office  === office)  &&
                (quarter === 'all' || row.dataset.quarter === quarter) &&
                (status  === 'all' || row.dataset.status  === status);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        visibleCount.textContent = visible + ' shown';
    }

    filterOffice.addEventListener('change',  applyFilters);
    filterQuarter.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change',  applyFilters);

    function getBadgeClass(status) {
        const s = status.toLowerCase();
        if (s === 'completed')   return 'badge-completed';
        if (s === 'in progress') return 'badge-in-progress';
        if (s === 'pending')     return 'badge-pending';
        if (s === 'delayed')     return 'badge-delayed';
        return 'badge-overdue';
    }

    document.querySelectorAll('[data-track-update]').forEach(btn => {
        btn.addEventListener('click', function () {
            const row       = btn.closest('[data-procurement-track-row]');
            const sel       = row.querySelector('[data-track-status-select]');
            const textarea  = row.querySelector('[data-track-remarks-input]');
            const pill      = row.querySelector('[data-track-status-pill]');
            const display   = row.querySelector('[data-track-remarks-display]');
            const newStatus = sel.value;

            pill.textContent = newStatus;
            pill.className   = 'badge ' + getBadgeClass(newStatus);
            display.textContent = textarea.value;
            row.dataset.status  = newStatus;
            row.classList.toggle('row-delayed', newStatus.toLowerCase() === 'delayed');

            btn.classList.add('saved');
            btn.innerHTML = `<svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="20 6 9 17 4 12"/></svg> Saved`;
            setTimeout(() => {
                btn.classList.remove('saved');
                btn.innerHTML = `<svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save`;
            }, 2000);

            applyFilters();
        });
    });
})();
</script>
@endpush
