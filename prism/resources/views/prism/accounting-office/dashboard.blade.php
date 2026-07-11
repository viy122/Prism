@extends('prism.layouts.app')
@section('title', 'Accounting Office | PRISM')

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
        --m: var(--crimson); --m-dk: var(--crimson-dark);
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    .stat-card { background: var(--s50); border: 1px solid var(--s200); border-radius: 14px; padding: 18px 20px; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .14em; color: var(--s500); margin-bottom: 6px; }
    .stat-value { font-size: 26px; font-weight: 900; color: var(--s900); letter-spacing: -.5px; }
    .stat-value.green { color: #3b6d11; }
    .stat-value.crimson { color: var(--m); }
    .stat-value.amber { color: #854f0b; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }

    .badge { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-delivered  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-processing { background: #faeeda; color: #854f0b; border: 1px solid #fac775; }
    .badge-paid       { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }

    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .2s; white-space: nowrap; }
    .btn-process { background: #854f0b; color: #fff; }
    .btn-process:hover:not(:disabled) { background: #6e410a; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 160px; border-radius: 12px; border: 1.5px dashed var(--s300); background: var(--s50); padding: 28px; text-align: center; }
    .empty-state i { font-size: 36px; color: var(--s300); }
    .empty-state p { font-size: 13px; color: var(--s400); max-width: 240px; line-height: 1.6; }

    .pr-toast { position: fixed; bottom: 28px; right: 28px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,.18); opacity: 0; pointer-events: none; transition: opacity .28s, transform .28s; transform: translateY(8px); }
    .pr-toast.visible { opacity: 1; transform: translateY(0); }
    .pr-toast.success { background: #166534; }
    .pr-toast.error   { background: #a32d2d; }

    @media (max-width: 900px) { .stat-grid { grid-template-columns: 1fr 1fr; } .content { padding: 16px 16px 40px; } }
    @media (max-width: 580px) { .stat-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-cash"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">Accounting Office</p>
            <h1 class="page-hdr-title">Payment Processing</h1>
            <p class="page-hdr-sub">Start payment processing for delivered Purchase Orders. The Cashier uploads the receipt to complete payment.</p>
        </div>
    </div>

    {{-- ── Summary stats ── --}}
    <div class="stat-grid">
        <div class="stat-card">
            <p class="stat-label">Delivered — For Processing</p>
            <p class="stat-value crimson">{{ $summary['forProcessing'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Processing — At Cashier</p>
            <p class="stat-value amber">{{ $summary['awaitingCashier'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total POs Paid</p>
            <p class="stat-value green">{{ $summary['totalPaid'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Amount Released</p>
            <p class="stat-value">₱{{ number_format($summary['totalAmount'], 0) }}</p>
        </div>
    </div>

    {{-- ── Delivered, waiting to start processing ── --}}
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Action Required</p>
                <h2 class="card-title">Delivered POs — Start Payment Processing</h2>
            </div>
            @if(count($forProcessing) > 0)
            <span style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:#faeeda;color:#854f0b;border:1px solid #fac775;">{{ count($forProcessing) }} pending</span>
            @endif
        </div>

        @if(count($forProcessing) === 0)
            <div class="empty-state">
                <i class="ti ti-checks"></i>
                <p>No delivered POs are waiting for payment processing.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Office</th>
                        <th>Description</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Issued</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forProcessing as $po)
                    <tr id="po-row-{{ $po['id'] }}">
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $po['poNumber'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['office'] }}</td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['title'] }}</td>
                        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['supplier'] }}</td>
                        <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                        <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $po['issuedAt'] }}</td>
                        <td><span class="badge badge-delivered">Complete Delivery</span></td>
                        <td>
                            <button class="btn btn-process btn-start-processing"
                                data-url="{{ $po['processUrl'] }}"
                                data-po-id="{{ $po['id'] }}"
                                data-po-number="{{ $po['poNumber'] }}">
                                <i class="ti ti-player-play"></i>
                                Start Processing
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Processing, waiting for the Cashier ── --}}
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">In Progress</p>
                <h2 class="card-title">Processing Payment — Awaiting Cashier Receipt</h2>
            </div>
        </div>

        @if(count($awaitingCashier) === 0)
            <div class="empty-state">
                <i class="ti ti-hourglass-empty"></i>
                <p>No POs are currently in payment processing.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Office</th>
                        <th>Description</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Processing Since</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($awaitingCashier as $po)
                    <tr>
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $po['poNumber'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);">{{ $po['office'] }}</td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['title'] }}</td>
                        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $po['supplier'] }}</td>
                        <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                        <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $po['processingAt'] }}</td>
                        <td><span class="badge badge-processing">Processing Payment</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Recently Paid ── --}}
    @if(count($recentlyPaid) > 0)
    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">History</p>
                <h2 class="card-title">Recently Paid Purchase Orders</h2>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Office</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Paid Date</th>
                        <th>Paid By (Cashier)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentlyPaid as $po)
                    <tr>
                        <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $po['poNumber'] }}</td>
                        <td style="font-size:12px;font-weight:600;color:var(--s600);">{{ $po['office'] }}</td>
                        <td>{{ $po['supplier'] }}</td>
                        <td style="font-weight:600;white-space:nowrap;">₱{{ number_format($po['totalAmount'], 2) }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['paidAt'] }}</td>
                        <td style="font-size:12px;color:var(--s500);">{{ $po['paidBy'] }}</td>
                        <td><span class="badge badge-paid">Payment Made ✓</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<div class="pr-toast" id="aoToast"></div>

@endsection

@push('scripts')
<script>
(function () {
    const toastEl   = document.getElementById('aoToast');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.className   = 'pr-toast visible ' + (isError ? 'error' : 'success');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.className = 'pr-toast'; }, 3000);
    }

    document.querySelectorAll('.btn-start-processing').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm(`Start payment processing for ${btn.dataset.poNumber}?`)) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Starting…';

            try {
                const resp = await fetch(btn.dataset.url, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body:    JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    showToast(`${btn.dataset.poNumber} is now processing payment. The Cashier will upload the receipt.`);
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast(json.error || 'Failed to start processing.', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-player-play"></i> Start Processing';
                }
            } catch {
                showToast('Network error. Please try again.', true);
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-player-play"></i> Start Processing';
            }
        });
    });

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
