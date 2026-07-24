@extends('prism.layouts.app')
@section('title', 'Annual Procurement Plan | Budget Office')

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
    .field-select:disabled { background: var(--s100); color: var(--s400); cursor: not-allowed; }
    .field-input {
        height: 44px; width: 100%; border-radius: 10px;
        border: 1px solid var(--s300); background: var(--white);
        padding: 0 14px; font-size: 13px; font-weight: 500;
        color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .field-input:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }
    .field-textarea {
        width: 100%; border-radius: 10px; border: 1px solid var(--s300);
        background: var(--white); padding: 10px 14px; font-size: 12px; font-weight: 500;
        color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
        resize: vertical; min-height: 64px; transition: border-color .15s, box-shadow .15s;
    }
    .field-textarea:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }
    .field-textarea::placeholder { color: var(--s400); }

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

    /* ── Procurement mode cell ── */
    .mode-cell { min-width: 280px; }

    .rec-badge {
        display: flex; align-items: center; gap: 7px;
        background: #EFF6FF; border: 1px solid #BFDBFE;
        border-radius: 9px; padding: 7px 10px; margin-bottom: 8px;
        font-size: 11px; font-weight: 600; color: #1E40AF; line-height: 1.4;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .rec-badge i { font-size: 13px; flex-shrink: 0; }
    .rec-badge strong { font-weight: 800; }

    .override-warning {
        display: flex; align-items: flex-start; gap: 8px;
        background: #FFFBEB; border: 1px solid #FCD34D;
        border-radius: 9px; padding: 8px 10px; margin-bottom: 8px;
    }
    .override-warning i { font-size: 14px; flex-shrink: 0; color: #D97706; margin-top: 1px; }
    .override-warning-title { font-size: 11px; font-weight: 800; color: #92400E; }
    .override-warning-reason { font-size: 11px; font-weight: 500; color: #92400E; margin-top: 2px; line-height: 1.45; }

    .mode-edit-label { font-size: 11px; font-weight: 700; color: var(--s700); margin-bottom: 4px; display: block; }

    .mode-current-row { display: flex; align-items: center; gap: 8px; }
    .badge-mode   { background: #DCFCE7; color: #166534; border-color: #86EFAC; }
    .badge-override { background: #FEF3C7; color: #92400E; border-color: #FCD34D; }

    .btn-override {
        height: 28px; padding: 0 12px; border-radius: 8px;
        border: 1.5px solid var(--crimson-border); background: transparent;
        font-size: 11px; font-weight: 700; color: var(--crimson);
        cursor: pointer; font-family: 'Poppins', sans-serif;
        display: inline-flex; align-items: center; gap: 5px;
        transition: background .15s, color .15s;
    }
    .btn-override:hover { background: var(--crimson); color: #fff; border-color: var(--crimson); }
    .btn-override i { font-size: 12px; }

    .mode-edit { display: flex; flex-direction: column; gap: 8px; }

    .mode-edit-actions { display: flex; gap: 8px; }
    .btn-save {
        height: 34px; padding: 0 16px; border-radius: 8px;
        background: var(--crimson); color: #fff; border: none;
        font-size: 12px; font-weight: 700; cursor: pointer;
        font-family: 'Poppins', sans-serif; transition: background .15s;
    }
    .btn-save:hover { background: var(--crimson-dark); }
    .btn-cancel {
        height: 34px; padding: 0 14px; border-radius: 8px;
        background: transparent; color: var(--s600);
        border: 1.5px solid var(--s300); font-size: 12px; font-weight: 700;
        cursor: pointer; font-family: 'Poppins', sans-serif; transition: border-color .15s;
    }
    .btn-cancel:hover { border-color: var(--s500); }
    .err-msg { font-size: 11px; color: #DC2626; font-weight: 600; display: none; }

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
            <p class="page-hdr-eyebrow">Budget Office</p>
            <h1 class="page-hdr-title">Annual Procurement Plan</h1>
            <p class="page-hdr-sub">System-recommended procurement modes per RA 9184 IRR thresholds. Override requires a written reason.</p>
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

                            {{-- ── Procurement Mode cell ── --}}
                            <td class="mode-cell"
                                data-item-id="{{ $item['itemId'] }}"
                                data-recommended="{{ $item['recommendedMode'] }}"
                                data-current="{{ $item['procurementMode'] }}"
                                data-overridden="{{ $item['isOverridden'] ? 'true' : 'false' }}"
                                data-reason="{{ $item['overrideReason'] }}"
                                data-rationale="{{ $item['rationale'] }}"
                                data-abc="PHP {{ number_format($item['abcAmount']) }}"
                                data-save-url="{{ $item['saveUrl'] }}">

                                {{-- View state --}}
                                <div class="mode-view">
                                    {{-- System recommendation (compact single-line) --}}
                                    <div class="rec-badge" title="{{ $item['rationale'] }}">
                                        <i class="ti ti-cpu"></i>
                                        System Recommendation: <strong>{{ $item['recommendedMode'] }}</strong>
                                    </div>

                                    {{-- Override active warning --}}
                                    @if ($item['isOverridden'])
                                    <div class="override-warning">
                                        <i class="ti ti-alert-triangle"></i>
                                        <div>
                                            <p class="override-warning-title">⚠ Override Active</p>
                                            <p class="override-warning-reason">{{ $item['overrideReason'] }}</p>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Current mode badge + override button --}}
                                    <div class="mode-current-row">
                                        <span class="badge {{ $item['isOverridden'] ? 'badge-override' : 'badge-mode' }}">
                                            {{ $item['procurementMode'] }}
                                        </span>
                                        <button class="btn-override js-override-btn" type="button">
                                            <i class="ti ti-pencil"></i>
                                            {{ $item['isOverridden'] ? 'Edit Override' : 'Override' }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Edit state (hidden by default) --}}
                                <div class="mode-edit" style="display:none">
                                    <select class="field-select js-mode-select" style="height:38px;font-size:13px;">
                                        @foreach ($procurementModes as $mode)
                                            <option value="{{ $mode }}" @selected($mode === $item['procurementMode'])>{{ $mode }}</option>
                                        @endforeach
                                    </select>
                                    <div>
                                        <label class="mode-edit-label">
                                            Reason for Override <span style="color:#DC2626">*</span>
                                        </label>
                                        <textarea class="field-textarea js-reason-input" rows="2"
                                            placeholder="Explain why you are overriding the system recommendation…">{{ $item['overrideReason'] }}</textarea>
                                    </div>
                                    <p class="err-msg js-err-msg">A reason is required when overriding the system recommendation.</p>
                                    <div class="mode-edit-actions">
                                        <button class="btn-save js-save-btn" type="button">Save Override</button>
                                        <button class="btn-cancel js-cancel-btn" type="button">Cancel</button>
                                    </div>
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
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ── Filters ───────────────────────────────────────────────────────────
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

    // ── Procurement mode override ─────────────────────────────────────────
    document.querySelectorAll('.mode-cell').forEach(function (cell) {
        const $view   = cell.querySelector('.mode-view');
        const $edit   = cell.querySelector('.mode-edit');
        const $select = cell.querySelector('.js-mode-select');
        const $reason = cell.querySelector('.js-reason-input');
        const $err    = cell.querySelector('.js-err-msg');
        const $save   = cell.querySelector('.js-save-btn');
        const $cancel = cell.querySelector('.js-cancel-btn');
        const $ovBtn  = cell.querySelector('.js-override-btn');

        const recommended = cell.dataset.recommended;
        const saveUrl     = cell.dataset.saveUrl;

        // Show edit state
        $ovBtn.addEventListener('click', function () {
            $view.style.display = 'none';
            $edit.style.display = 'flex';
            $reason.focus();
        });

        // Cancel — restore view state
        $cancel.addEventListener('click', function () {
            $edit.style.display = 'none';
            $view.style.display = 'block';
            $err.style.display  = 'none';
            // reset to last saved values
            $select.value  = cell.dataset.current;
            $reason.value  = cell.dataset.reason;
        });

        // Save — AJAX POST
        $save.addEventListener('click', async function () {
            const selectedMode = $select.value;
            const reason       = $reason.value.trim();
            const isOverride   = selectedMode !== recommended;

            if (isOverride && !reason) {
                $err.style.display = 'block';
                $reason.focus();
                return;
            }
            $err.style.display = 'none';

            $save.disabled    = true;
            $save.textContent = 'Saving…';

            try {
                const res  = await fetch(saveUrl, {
                    method:  'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'Accept':        'application/json',
                        'X-CSRF-TOKEN':  CSRF,
                    },
                    body: JSON.stringify({ procurement_mode: selectedMode, override_reason: reason }),
                });
                const data = await res.json();

                if (!data.success) {
                    $err.textContent   = data.message || 'Failed to save.';
                    $err.style.display = 'block';
                    return;
                }

                // Update cell data attributes
                cell.dataset.current    = selectedMode;
                cell.dataset.overridden = isOverride ? 'true' : 'false';
                cell.dataset.reason     = reason;

                // Rebuild view state
                rebuildView(cell, selectedMode, isOverride, reason, recommended);

                $edit.style.display = 'none';
                $view.style.display = 'block';

                // Update filter row attribute
                const tr = cell.closest('tr');
                if (tr) tr.dataset.mode = selectedMode;

            } catch {
                $err.textContent   = 'Network error. Please try again.';
                $err.style.display = 'block';
            } finally {
                $save.disabled    = false;
                $save.textContent = 'Save Override';
            }
        });
    });

    function rebuildView(cell, currentMode, isOverridden, reason, recommended) {
        const $view  = cell.querySelector('.mode-view');
        const $badge = $view.querySelector('.badge');
        const $btn   = $view.querySelector('.js-override-btn');

        // Update or remove override warning
        let $warning = $view.querySelector('.override-warning');
        if (isOverridden) {
            if (!$warning) {
                $warning = document.createElement('div');
                $warning.className = 'override-warning';
                $warning.innerHTML =
                    '<i class="ti ti-alert-triangle"></i>' +
                    '<div>' +
                        '<p class="override-warning-title">⚠ Override Active</p>' +
                        '<p class="override-warning-reason"></p>' +
                    '</div>';
                $view.querySelector('.rec-badge').insertAdjacentElement('afterend', $warning);
            }
            $warning.querySelector('.override-warning-reason').textContent = reason;
        } else if ($warning) {
            $warning.remove();
        }

        // Update mode badge
        $badge.textContent = currentMode;
        $badge.className   = 'badge ' + (isOverridden ? 'badge-override' : 'badge-mode');

        // Update override button label
        $btn.innerHTML = '<i class="ti ti-pencil"></i> ' + (isOverridden ? 'Edit Override' : 'Override');
    }

    function escHtml(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
})();
</script>
@endpush
