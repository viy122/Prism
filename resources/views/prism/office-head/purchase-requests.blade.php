@extends('prism.layouts.office-head')
@section('title', 'Purchase Requests')

@php
    $items          = collect($purchaseItems);
    $totalApproved  = $items->sum('approvedAmount');
    $pendingCount   = $items->where('prStatus', 'Pending')->count();
    $inProgressCount= $items->where('prStatus', 'In Progress')->count();
    $delayedCount   = $items->where('prStatus', 'Delayed')->count();
    $readyCount     = $items->filter(fn($i) => in_array($i['procurementStatus'], ['Ready for PR', 'Awaiting PR'], true))->count();
@endphp

@push('page-css')
<style>
        :root {
            --m:     #681012;
            --m-dk:  #4e0c0e;
            --gold:  #c9a84c;
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

        .content { padding: 32px 32px 64px; flex: 1; display: flex; flex-direction: column; gap: 24px; }

        /* ─── Card ─── */
        .card {
            background: var(--white); border: 1px solid var(--s200);
            border-radius: 18px; padding: 26px; box-shadow: var(--sh);
        }
        .card-eyebrow {
            font-size: 9px; font-weight: 700; letter-spacing: .18em;
            text-transform: uppercase; color: var(--m); margin-bottom: 4px;
        }
        .card-title { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
        .card-sub { font-size: 13px; color: var(--s500); margin-top: 4px; }
        .card-head {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 12px; margin-bottom: 22px; flex-wrap: wrap;
        }

        /* ─── Stats bar ─── */
        .stats-bar { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; }
        .stat-box {
            background: var(--s50); border: 1px solid var(--s200);
            border-radius: 14px; padding: 18px 20px;
        }
        .stat-box dt {
            font-size: 9px; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; color: var(--s400); margin-bottom: 6px;
        }
        .stat-box dd { font-size: 26px; font-weight: 800; color: var(--s900); line-height: 1; }
        .stat-box dd.maroon { color: var(--m); }
        .stat-box dd.amber  { color: #92400e; }
        .stat-box dd.rose   { color: #9f1239; }
        .stat-box dd.sm     { font-size: 16px; }
        .stat-box small {
            display: block; margin-top: 6px;
            font-size: 11px; font-weight: 600; color: var(--s400);
        }
        .stat-box small.green  { color: #15803d; }
        .stat-box small.amber  { color: #92400e; }
        .stat-box small.rose   { color: #9f1239; }

        /* ─── Buttons ─── */
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 40px; padding: 0 18px; border-radius: 10px;
            background: var(--m); color: #fff; font-size: 13px; font-weight: 700;
            border: none; cursor: pointer; font-family: 'Poppins', sans-serif;
            text-decoration: none; box-shadow: 0 2px 10px rgba(104,16,18,.2);
            transition: background .2s; white-space: nowrap;
        }
        .btn-primary:hover { background: var(--m-dk); }
        .btn-primary svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .btn-outline {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; height: 40px; padding: 0 18px; border-radius: 10px;
            background: #fff; color: var(--m); font-size: 13px; font-weight: 700;
            border: 1px solid rgba(104,16,18,.3); cursor: pointer;
            font-family: 'Poppins', sans-serif; text-decoration: none;
            transition: background .2s, border-color .2s; white-space: nowrap;
        }
        .btn-outline:hover { background: rgba(104,16,18,.05); border-color: var(--m); }
        .btn-outline svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Pill ─── */
        .pill {
            display: inline-flex; align-items: center;
            height: 26px; padding: 0 12px; border-radius: 99px;
            font-size: 11px; font-weight: 700; white-space: nowrap; flex-shrink: 0;
        }
        .pill-gray   { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }
        .pill-maroon { background: rgba(104,16,18,.08); color: var(--m); border: 1px solid rgba(104,16,18,.15); }

        /* ─── 2-col layout ─── */
        .two-col { display: grid; grid-template-columns: minmax(0,1fr) 360px; gap: 24px; align-items: start; }
        .col-sticky { position: sticky; top: 86px; }

        /* ─── Search bar ─── */
        .search-wrap { position: relative; }
        .search-wrap svg {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            width: 15px; height: 15px; stroke: var(--s400); fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            pointer-events: none;
        }
        .search-input {
            height: 40px; width: 100%; border-radius: 99px;
            border: 1px solid var(--s200); background: var(--s50);
            padding: 0 16px 0 36px; font-size: 13px; font-weight: 500;
            color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .search-input:focus { border-color: var(--m); box-shadow: 0 0 0 3px rgba(104,16,18,.08); }
        .search-input::placeholder { color: var(--s400); }

        /* ─── Table ─── */
        .table-wrap {
            border-radius: 14px; border: 1px solid var(--s200);
            overflow: auto; max-height: calc(100vh - 22rem); min-height: 420px;
            background: var(--white);
        }
        table {
            width: 100%; min-width: 1040px; border-collapse: collapse;
            font-size: 13px; color: var(--s700); text-align: left;
        }
        thead th {
            position: sticky; top: 0; z-index: 5;
            background: var(--s50); border-bottom: 1px solid var(--s200);
            padding: 12px 16px;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: var(--s500); white-space: nowrap;
        }
        tbody td { padding: 14px 16px; border-bottom: 1px solid var(--s100); vertical-align: top; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .12s; }
        tbody tr:hover { background: rgba(104,16,18,.03); }
        .item-name { font-size: 13px; font-weight: 700; color: var(--s900); line-height: 1.4; max-width: 220px; }
        .item-id   { font-size: 11px; font-weight: 600; color: var(--s400); margin-top: 3px; }
        .amount    { font-size: 13px; font-weight: 700; color: var(--s900); }

        /* ─── Upload label ─── */
        .upload-label {
            display: inline-flex; align-items: center; gap: 7px;
            border: 1.5px dashed rgba(104,16,18,.35);
            border-radius: 10px; background: rgba(104,16,18,.04);
            padding: 8px 14px; font-size: 12px; font-weight: 700;
            color: var(--m); cursor: pointer; transition: background .15s, box-shadow .15s;
            white-space: nowrap;
        }
        .upload-label:hover { background: rgba(104,16,18,.09); box-shadow: var(--sh); }
        .upload-label svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .upload-label input { display: none; }

        /* ─── OCR row ─── */
        .ocr-row td { background: var(--s50); padding: 0; }
        .ocr-inner {
            display: grid; grid-template-columns: 220px minmax(0,1fr);
            gap: 16px; border-radius: 12px; border: 1px solid var(--s200);
            background: var(--s50); padding: 16px; margin: 8px 16px 12px;
        }
        .ocr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
        .ocr-title   { font-size: 14px; font-weight: 800; color: var(--s900); }
        .ocr-dl      { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .ocr-dl dt   { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s400); margin-bottom: 3px; }
        .ocr-dl dd   { font-size: 13px; font-weight: 700; color: var(--s900); }

        /* ─── Sidebar panels ─── */
        .side-panels { display: flex; flex-direction: column; gap: 20px; }
        .health-row  { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--s100); }
        .health-row:last-child { border-bottom: none; padding-bottom: 0; }
        .health-label { font-size: 13px; font-weight: 600; color: var(--s600); }
        .checklist    { display: flex; flex-direction: column; gap: 12px; margin-top: 4px; }
        .check-item   { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: var(--s600); line-height: 1.55; }
        .check-item svg { width: 16px; height: 16px; stroke: #16a34a; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; margin-top: 2px; }
        .remark-item  { border-left: 3px solid var(--gold); padding-left: 12px; }
        .remark-name  { font-size: 13px; font-weight: 700; color: var(--s900); }
        .remark-text  { font-size: 12px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
        .remarks-list { display: flex; flex-direction: column; gap: 14px; margin-top: 4px; }

        /* ─── Status badges ─── */
        .badge {
            display: inline-flex; align-items: center;
            height: 24px; padding: 0 10px; border-radius: 99px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-pending    { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-progress   { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-delayed    { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-approved   { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-default    { background: var(--s100); color: var(--s700); border: 1px solid var(--s200); }

        /* ─── Responsive ─── */
        @media (max-width: 1280px) {
            .two-col { grid-template-columns: minmax(0,1fr) 320px; }
            .stats-bar { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 1024px) {
            .content { padding: 20px 20px 48px; gap: 20px; }
            .two-col { grid-template-columns: 1fr; }
            .col-sticky { position: static; }
        }
        @media (max-width: 640px) {
            .stats-bar { grid-template-columns: 1fr 1fr; }
        }
</style>
@endpush

@section('content')
    <div class="content">

        {{-- Stats --}}
        <dl class="stats-bar">
            <div class="stat-box">
                <dt>Approved Queue</dt>
                <dd>{{ $items->count() }}</dd>
                <small class="green">{{ $readyCount }} ready for upload</small>
            </div>
            <div class="stat-box">
                <dt>Approved Amount</dt>
                <dd class="sm">PHP {{ number_format($totalApproved) }}</dd>
                <small>Across active items</small>
            </div>
            <div class="stat-box">
                <dt>Pending Upload</dt>
                <dd class="amber">{{ $pendingCount }}</dd>
                <small class="amber">Needs signed PDF</small>
            </div>
            <div class="stat-box">
                <dt>Needs Attention</dt>
                <dd class="rose">{{ $delayedCount }}</dd>
                <small class="rose">{{ $inProgressCount }} in progress</small>
            </div>
        </dl>

        {{-- 2-col: table + sidebar panels --}}
        <div class="two-col">

            {{-- PR Upload Queue --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <p class="card-eyebrow">Approved items eligible for PR</p>
                        <h2 class="card-title">PR Upload Queue</h2>
                        <p class="card-sub">Upload signed PR PDFs and monitor procurement status.</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <div class="search-wrap" style="min-width:220px;">
                            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input class="search-input" type="search" placeholder="Search item or remarks">
                        </div>
                        <span class="pill pill-gray">{{ count($purchaseItems) }} items</span>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Amount</th>
                                <th>Quarter</th>
                                <th>Procurement</th>
                                <th>Signed PR PDF</th>
                                <th>PR Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseItems as $item)
                                <tr>
                                    <td>
                                        <p class="item-name">{{ $item['itemName'] }}</p>
                                        <p class="item-id">{{ $item['id'] }}</p>
                                    </td>
                                    <td><span class="amount">PHP {{ number_format($item['approvedAmount']) }}</span></td>
                                    <td style="font-size:13px;font-weight:600;color:var(--s600);">{{ $item['targetQuarter'] }}</td>
                                    <td style="font-size:13px;font-weight:600;color:var(--s600);">{{ $item['procurementStatus'] }}</td>
                                    <td>
                                        <label class="upload-label">
                                            <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                                            <span data-upload-label="{{ $item['id'] }}">Upload PDF</span>
                                            <input type="file" accept="application/pdf,.pdf"
                                                   data-pr-upload="{{ $item['id'] }}"
                                                   data-upload-url="{{ $item['uploadUrl'] }}">
                                        </label>
                                    </td>
                                    <td><x-prism.status-badge :status="$item['prStatus']" data-pr-status="{{ $item['id'] }}" /></td>
                                    <td style="font-size:12px;color:var(--s500);max-width:220px;line-height:1.6;">{{ $item['remarks'] }}</td>
                                </tr>
                                <tr class="ocr-row" data-ocr-row="{{ $item['id'] }}" hidden>
                                    <td colspan="7">
                                        <div class="ocr-inner">
                                            <div>
                                                <p class="ocr-eyebrow">OCR extracted</p>
                                                <p class="ocr-title" data-ocr-file="{{ $item['id'] }}">Signed PR PDF</p>
                                            </div>
                                            <dl class="ocr-dl">
                                                <div><dt>PR number</dt><dd data-ocr-pr-number="{{ $item['id'] }}"></dd></div>
                                                <div><dt>Date</dt><dd data-ocr-date="{{ $item['id'] }}"></dd></div>
                                                <div><dt>Items</dt><dd data-ocr-items="{{ $item['id'] }}"></dd></div>
                                                <div><dt>Amount</dt><dd data-ocr-amount="{{ $item['id'] }}"></dd></div>
                                            </dl>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sidebar panels --}}
            <div class="col-sticky side-panels">

                {{-- Queue Health --}}
                <div class="card">
                    <p class="card-eyebrow">Processing snapshot</p>
                    <h2 class="card-title" style="margin-bottom:18px;">Queue Health</h2>
                    <div>
                        <div class="health-row">
                            <span class="health-label">Pending</span>
                            <x-prism.status-badge status="Pending">{{ $pendingCount }}</x-prism.status-badge>
                        </div>
                        <div class="health-row">
                            <span class="health-label">In Progress</span>
                            <x-prism.status-badge status="In Progress">{{ $inProgressCount }}</x-prism.status-badge>
                        </div>
                        <div class="health-row">
                            <span class="health-label">Delayed</span>
                            <x-prism.status-badge status="Delayed">{{ $delayedCount }}</x-prism.status-badge>
                        </div>
                    </div>
                </div>

                {{-- Upload Checklist --}}
                <div class="card">
                    <p class="card-eyebrow">Upload checklist</p>
                    <h2 class="card-title" style="margin-bottom:16px;">Before Sending</h2>
                    <ul class="checklist">
                        <li class="check-item">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Signed PR PDF is clear and readable.
                        </li>
                        <li class="check-item">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Approved item list is attached.
                        </li>
                        <li class="check-item">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Amount and PR number match the approved record.
                        </li>
                    </ul>
                </div>

                {{-- Recent Remarks --}}
                <div class="card">
                    <p class="card-eyebrow">Recent remarks</p>
                    <h2 class="card-title" style="margin-bottom:16px;">Procurement Notes</h2>
                    <div class="remarks-list">
                        @foreach ($items->take(3) as $item)
                            <div class="remark-item">
                                <p class="remark-name">{{ $item['itemName'] }}</p>
                                <p class="remark-text">{{ $item['remarks'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>{{-- /content --}}
@endsection

@push('scripts')
<script type="application/json" id="purchaseRequestData">@json($purchaseItems)</script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── toast ── */
    function toast(msg, type) {
        let el = document.getElementById('pr-upload-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'pr-upload-toast';
            el.style.cssText = [
                'position:fixed;bottom:24px;right:24px;z-index:9999',
                'padding:12px 20px;border-radius:12px',
                'font-size:13px;font-weight:700;color:#fff',
                'opacity:0;transition:opacity .3s;pointer-events:none',
                'font-family:Poppins,sans-serif;max-width:320px;line-height:1.4',
            ].join(';');
            document.body.appendChild(el);
        }
        el.textContent = msg;
        el.style.background = type === 'error' ? '#991B1B' : '#166534';
        el.style.opacity = '1';
        clearTimeout(el._t);
        el._t = setTimeout(() => { el.style.opacity = '0'; }, 3500);
    }

    /* ── badge updater ── */
    const tones = {
        'pending':     'bg-amber-50 text-amber-700 ring-amber-200',
        'in progress': 'bg-blue-50 text-blue-700 ring-blue-200',
        'completed':   'bg-green-50 text-green-700 ring-green-200',
        'delayed':     'bg-red-50 text-red-700 ring-red-200',
    };

    function updateBadge(itemId, statusLabel) {
        const badge = document.querySelector(`[data-pr-status="${itemId}"]`);
        if (!badge) return;
        const tone = tones[statusLabel.toLowerCase()] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
        badge.className = `inline-flex min-h-7 items-center rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset ${tone}`;
        badge.textContent = statusLabel;
    }

    /* ── file inputs ── */
    document.querySelectorAll('input[data-pr-upload]').forEach(function (input) {
        input.addEventListener('change', async function () {
            const file = input.files[0];
            if (!file) return;

            const itemId    = input.dataset.prUpload;
            const uploadUrl = input.dataset.uploadUrl;
            const labelEl   = input.closest('label')?.querySelector('[data-upload-label]');
            const origText  = labelEl?.textContent ?? 'Upload PDF';

            if (labelEl) labelEl.textContent = 'Uploading…';
            input.disabled = true;

            const fd = new FormData();
            fd.append('file', file);

            try {
                const resp = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: fd,
                });
                const data = await resp.json();

                if (resp.ok && data.success) {
                    if (labelEl) labelEl.textContent = file.name.length > 28 ? file.name.slice(0, 25) + '…' : file.name;
                    updateBadge(itemId, data.status);
                    toast('PDF uploaded successfully.', 'success');
                } else {
                    if (labelEl) labelEl.textContent = origText;
                    toast(data.message ?? 'Upload failed. Please try again.', 'error');
                }
            } catch {
                if (labelEl) labelEl.textContent = origText;
                toast('Upload failed. Check your connection and try again.', 'error');
            }

            input.disabled = false;
            input.value = '';
        });
    });

    /* ── search filter ── */
    const searchInput = document.querySelector('.search-input');
    const tbody = document.querySelector('tbody');
    if (searchInput && tbody) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            tbody.querySelectorAll('tr:not(.ocr-row)').forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.hidden = q && !text.includes(q);
            });
        });
    }
})();
</script>
@endpush
