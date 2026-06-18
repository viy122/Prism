@extends('prism.layouts.app')
@section('title', 'Annual Procurement Plan | Finance Office')

@push('page-css')
<style>
    :root {
        --s50:   #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400:  #94a3b8; --s500: #64748b; --s600: #475569;
        --s700:  #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
        --sh-md: 0 4px 16px rgba(15,23,42,.08), 0 1px 4px rgba(15,23,42,.04);
    }

    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .btn-primary {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; height: 42px; padding: 0 20px; border-radius: 10px;
        background: var(--crimson); color: #fff;
        font-size: 13px; font-weight: 700; border: none; cursor: pointer;
        font-family: 'Poppins', sans-serif; box-shadow: 0 2px 10px rgba(139,26,28,.2);
        transition: background .2s; white-space: nowrap;
    }
    .btn-primary:hover { background: var(--crimson-dark); }

    .filters-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .field-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 7px; display: block; }
    .field-select {
        height: 44px; width: 100%; border-radius: 10px;
        border: 1px solid var(--s300); background: var(--white);
        padding: 0 14px; font-size: 13.5px; font-weight: 500;
        color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .field-select:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }
    .field-input {
        height: 44px; width: 100%; border-radius: 10px;
        border: 1px solid var(--s300); background: var(--white);
        padding: 0 14px; font-size: 13px; font-weight: 500;
        color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .field-input:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }
    .field-input::placeholder { color: var(--s400); }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; max-height: 64vh; background: var(--white); box-shadow: inset 0 1px 4px rgba(15,23,42,.04); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { position: sticky; top: 0; z-index: 5; background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 14px 16px; border-bottom: 1px solid var(--s100); vertical-align: top; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--crimson-mid); }

    .item-name  { font-size: 13px; font-weight: 700; color: var(--s900); }
    .amount-val { font-size: 13px; font-weight: 700; color: var(--s900); }
    .badge { display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s600); border: 1px solid var(--s200); white-space: nowrap; }
    .count-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
    .override-cell { display: grid; gap: 8px; min-width: 220px; }

    @media (max-width: 1024px) {
        .page-shell { padding: 16px 16px 40px; }
        .filters-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 640px) { .filters-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="page-shell">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-file-stack"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Finance Office</p>
            <h1 class="page-hdr-title">Annual Procurement Plan</h1>
            <p class="page-hdr-sub">Endorsed procurement items grouped by office, with recommended procurement mode and ABC amounts.</p>
        </div>
    </div>

    <div class="card">
        <div class="filters-grid">
            <div>
                <label class="field-label" for="appOfficeFilter">Office</label>
                <select class="field-select" id="appOfficeFilter">
                    <option value="all">All offices</option>
                    @foreach ($offices as $office)
                        <option value="{{ $office }}">{{ $office }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="appQuarterFilter">Quarter</label>
                <select class="field-select" id="appQuarterFilter">
                    <option value="all">All quarters</option>
                    @foreach ($quarters as $quarter)
                        <option value="{{ $quarter }}">{{ $quarter }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="appModeFilter">Procurement mode</label>
                <select class="field-select" id="appModeFilter">
                    <option value="all">All modes</option>
                    @foreach ($procurementModes as $mode)
                        <option value="{{ $mode }}">{{ $mode }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Consolidated approved items</p>
                <h2 class="card-title">APP Item Matrix</h2>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="count-chip" id="appVisibleCount">{{ count($appItems) }} shown</span>
                <button class="btn-primary" type="button" id="printAppButton">
                    <i class="ti ti-printer"></i>
                    Export or Print APP
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>ABC Amount</th>
                        <th>Quarter</th>
                        <th>Procurement Mode</th>
                        <th>Recommendation</th>
                        <th>Override Mode &amp; Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appItems as $item)
                        <tr data-app-row
                            data-office="{{ $item['office'] }}"
                            data-quarter="{{ $item['targetQuarter'] }}"
                            data-mode="{{ $item['procurementMode'] }}">
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $item['office'] }}</td>
                            <td><p class="item-name">{{ $item['item'] }}</p></td>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $item['quantity'] }}</td>
                            <td><p class="amount-val">PHP {{ number_format($item['abcAmount']) }}</p></td>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">{{ $item['targetQuarter'] }}</td>
                            <td><span class="badge">{{ $item['procurementMode'] }}</span></td>
                            <td style="font-size:13px;color:var(--s600);max-width:180px;">{{ $item['recommendation'] }}</td>
                            <td>
                                <div class="override-cell">
                                    <select class="field-select" style="height:40px;font-size:13px;" aria-label="Override procurement mode for {{ $item['item'] }}">
                                        @foreach ($procurementModes as $mode)
                                            <option value="{{ $mode }}" @selected($mode === $item['procurementMode'])>{{ $mode }}</option>
                                        @endforeach
                                    </select>
                                    <input class="field-input" style="height:40px;font-size:13px;" type="text" placeholder="Reason for override">
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
    const officeEl  = document.getElementById('appOfficeFilter');
    const quarterEl = document.getElementById('appQuarterFilter');
    const modeEl    = document.getElementById('appModeFilter');
    const countEl   = document.getElementById('appVisibleCount');
    const rows      = document.querySelectorAll('[data-app-row]');

    function applyFilters() {
        const office  = officeEl.value;
        const quarter = quarterEl.value;
        const mode    = modeEl.value;
        let visible   = 0;

        rows.forEach(row => {
            const match =
                (office  === 'all' || row.dataset.office  === office)  &&
                (quarter === 'all' || row.dataset.quarter === quarter) &&
                (mode    === 'all' || row.dataset.mode    === mode);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        countEl.textContent = visible + ' shown';
    }

    officeEl.addEventListener('change',  applyFilters);
    quarterEl.addEventListener('change', applyFilters);
    modeEl.addEventListener('change',    applyFilters);
})();
</script>
@endpush
