@extends('prism.layouts.office-head')
@section('title', 'Market Study')

@push('page-css')
<style>
    .mps-wrap {
        max-width: 900px;
        margin: 0 auto;
        padding: 28px 32px 64px;
    }

    /* ── Action bar (hidden on print) ── */
    .mps-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .mps-back { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 8px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--border2); background: var(--white); color: var(--txt2); cursor: pointer; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all .15s; }
    .mps-back:hover { border-color: var(--crimson); color: var(--crimson); }
    .mps-action-spacer { flex: 1; }
    .btn-mps { display: inline-flex; align-items: center; gap: 7px; height: 38px; padding: 0 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; transition: all .18s; }
    .btn-mps-print  { background: var(--blue-bg); color: var(--blue); border: 1.5px solid #BFDBFE; }
    .btn-mps-print:hover { background: #BFDBFE; }
    .btn-mps-submit { background: var(--crimson); color: #fff; box-shadow: 0 3px 12px rgba(139,26,28,.25); }
    .btn-mps-submit:hover { background: var(--crimson-dark); }
    .btn-mps-submit:disabled { background: var(--s300); box-shadow: none; cursor: not-allowed; }

    /* ── Submitted banner ── */
    .mps-submitted-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 20px;
        font-size: 13px;
        font-weight: 600;
        color: #166534;
    }
    .mps-submitted-banner i { font-size: 18px; color: #16a34a; }

    /* ── No items notice ── */
    .mps-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--txt3);
    }
    .mps-empty i { font-size: 48px; display: block; margin-bottom: 14px; }
    .mps-empty p { font-size: 14px; font-weight: 600; margin-bottom: 6px; color: var(--txt2); }
    .mps-empty small { font-size: 12px; }

    /* ── Document ── */
    .mps-document {
        background: #fff;
        border: 1px solid var(--border2);
        border-radius: 14px;
        box-shadow: 0 2px 16px rgba(15,23,42,.07);
        overflow: hidden;
    }

    /* Header */
    .mps-doc-header {
        border-bottom: 2px solid #8b1a1c;
        padding: 28px 40px 20px;
        text-align: center;
        background: #fff;
    }
    .mps-doc-agency { font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #8b1a1c; margin-bottom: 4px; }
    .mps-doc-title  { font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -.4px; margin-bottom: 2px; }
    .mps-doc-sub    { font-size: 12px; color: #64748b; }
    .mps-doc-meta   { display: flex; justify-content: center; gap: 40px; margin-top: 14px; }
    .mps-doc-meta-item { text-align: center; }
    .mps-doc-meta-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: #94a3b8; }
    .mps-doc-meta-value { font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 2px; }

    /* Item sections */
    .mps-doc-body { padding: 32px 40px; display: flex; flex-direction: column; gap: 28px; }

    .mps-item-block { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    .mps-item-head {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .mps-item-num { width: 26px; height: 26px; border-radius: 50%; background: #8b1a1c; color: #fff; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .mps-item-name { font-size: 14px; font-weight: 700; color: #0f172a; flex: 1; }
    .mps-item-qty  { font-size: 12px; color: #64748b; font-weight: 600; }

    .mps-ref-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .mps-ref-table thead th { background: #f1f5f9; padding: 9px 16px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #64748b; border-bottom: 1px solid #e2e8f0; }
    .mps-ref-table tbody td { padding: 10px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: top; }
    .mps-ref-table tbody tr:last-child td { border-bottom: none; }
    .mps-ref-table tbody tr:nth-child(even) td { background: #fafafa; }
    .mps-ref-source-link { color: #3b5bdb; text-decoration: none; font-size: 11px; }
    .mps-ref-source-link:hover { text-decoration: underline; }
    .mps-ref-price { font-weight: 700; color: #2f9e44; }

    .mps-item-avg {
        padding: 10px 18px;
        background: #f0fdf4;
        border-top: 1px solid #bbf7d0;
        font-size: 12px;
        color: #166534;
        font-weight: 600;
        text-align: right;
    }

    .mps-no-refs { padding: 14px 18px; font-size: 12px; color: #94a3b8; font-style: italic; }

    /* Footer */
    .mps-doc-footer {
        border-top: 2px solid #e2e8f0;
        padding: 28px 40px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }
    .mps-sig-block { }
    .mps-sig-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #94a3b8; margin-bottom: 30px; }
    .mps-sig-line  { border-top: 1.5px solid #334155; padding-top: 6px; }
    .mps-sig-name  { font-size: 13px; font-weight: 700; color: #0f172a; }
    .mps-sig-pos   { font-size: 11px; color: #64748b; }

    /* Print overrides */
    @media print {
        .mps-actions, .no-print { display: none !important; }
        body { background: #fff !important; }
        .mps-wrap { padding: 0; max-width: 100%; }
        .mps-document { border: none; border-radius: 0; box-shadow: none; }
        .mps-item-block { break-inside: avoid; }
    }
</style>
@endpush

@section('content')
<div class="mps-wrap">

    {{-- Action bar --}}
    <div class="mps-actions no-print">
        <a href="{{ route('office-head.market-scoping') }}" class="mps-back">
            <i class="ti ti-arrow-left"></i> Back to Market Scoping
        </a>
        <span class="mps-action-spacer"></span>
        <button class="btn-mps btn-mps-print" onclick="window.print()" type="button">
            <i class="ti ti-printer"></i> Print / Save as PDF
        </button>
        @if (!$survey)
            @if ($items->isNotEmpty())
                <button class="btn-mps btn-mps-submit" id="btnSubmitMps" type="button">
                    <i class="ti ti-send"></i> Submit Market Study
                </button>
            @else
                <button class="btn-mps btn-mps-submit" disabled type="button" title="Attach at least one item with 3 references first">
                    <i class="ti ti-send"></i> Submit Market Study
                </button>
            @endif
        @endif
    </div>

    {{-- Submitted banner --}}
    @if ($survey)
    <div class="mps-submitted-banner no-print">
        <i class="ti ti-circle-check-filled"></i>
        Market Study submitted &mdash; <strong>{{ $survey->ref_number }}</strong>
        &nbsp;&middot;&nbsp; {{ $survey->submitted_at?->format('F j, Y') }}
        @if ($survey->submittedBy) &nbsp;&middot;&nbsp; by {{ $survey->submittedBy->name }} @endif
    </div>
    @endif

    @if ($items->isEmpty())
        <div class="mps-document">
            <div class="mps-empty">
                <i class="ti ti-clipboard-off"></i>
                <p>No items with references yet</p>
                <small>Attach at least 3 price references to a proposal item on the Market Scoping page, then return here.</small>
            </div>
        </div>
    @else

    {{-- Document --}}
    <div class="mps-document" id="mpsPrintArea">

        <div class="mps-doc-header">
            <div class="mps-doc-agency">Batangas State University — ARASOF Nasugbu Campus</div>
            <div class="mps-doc-title">MARKET STUDY</div>
            <div class="mps-doc-sub">Budget Proposal &mdash; {{ $proposal->title }}</div>
            <div class="mps-doc-meta">
                <div class="mps-doc-meta-item">
                    <div class="mps-doc-meta-label">Reference No.</div>
                    <div class="mps-doc-meta-value">{{ $survey?->ref_number ?? 'DRAFT' }}</div>
                </div>
                <div class="mps-doc-meta-item">
                    <div class="mps-doc-meta-label">Date</div>
                    <div class="mps-doc-meta-value">{{ ($survey?->submitted_at ?? now())->format('F j, Y') }}</div>
                </div>
                <div class="mps-doc-meta-item">
                    <div class="mps-doc-meta-label">Fiscal Year</div>
                    <div class="mps-doc-meta-value">{{ $proposal->fiscal_year }}</div>
                </div>
                <div class="mps-doc-meta-item">
                    <div class="mps-doc-meta-label">Office</div>
                    <div class="mps-doc-meta-value">{{ $proposal->office?->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="mps-doc-body">
            @foreach ($items as $idx => $item)
            <div class="mps-item-block">
                <div class="mps-item-head">
                    <div class="mps-item-num">{{ $idx + 1 }}</div>
                    <div class="mps-item-name">{{ $item->name }}</div>
                    <div class="mps-item-qty">{{ $item->quantity }} {{ $item->unit }}</div>
                </div>
                @if ($item->marketReferences->isNotEmpty())
                <table class="mps-ref-table">
                    <thead>
                        <tr>
                            <th style="width:40%">Source / Supplier</th>
                            <th style="width:20%">Unit Price</th>
                            <th style="width:20%">Date Retrieved</th>
                            <th style="width:20%">Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item->marketReferences as $ref)
                        <tr>
                            <td>{{ $ref->supplier_name ?? $ref->title ?? '—' }}</td>
                            <td class="mps-ref-price">₱{{ number_format((float) $ref->price, 2) }}</td>
                            <td>{{ $ref->date_accessed?->format('M d, Y') ?? now()->format('M d, Y') }}</td>
                            <td>
                                @if ($ref->source_url)
                                    <a href="{{ $ref->source_url }}" target="_blank" rel="noopener" class="mps-ref-source-link">View source</a>
                                @else
                                    &mdash;
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @php
                    $prices = $item->marketReferences->pluck('price')->map(fn ($p) => (float) $p);
                    $avg    = $prices->isNotEmpty() ? $prices->avg() : 0;
                @endphp
                <div class="mps-item-avg">
                    Average Market Price: <strong>₱{{ number_format($avg, 2) }}</strong>
                    &nbsp;&middot;&nbsp;
                    Estimated Unit Cost: <strong>₱{{ number_format((float) $item->estimated_unit_cost, 2) }}</strong>
                </div>
                @else
                <div class="mps-no-refs">No references attached for this item.</div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="mps-doc-footer">
            <div class="mps-sig-block">
                <div class="mps-sig-label">Prepared by</div>
                <div class="mps-sig-line">
                    <div class="mps-sig-name">{{ $survey?->submittedBy?->name ?? auth()->user()?->name ?? '&nbsp;' }}</div>
                    <div class="mps-sig-pos">Office Head / Dean</div>
                </div>
            </div>
            <div class="mps-sig-block">
                <div class="mps-sig-label">Noted by</div>
                <div class="mps-sig-line">
                    <div class="mps-sig-name">&nbsp;</div>
                    <div class="mps-sig-pos">Procurement Officer</div>
                </div>
            </div>
        </div>

    </div>
    @endif

</div>
@endsection

<script type="application/json" id="mpsSubmitUrl">@json(route('office-head.market-scoping.mps.submit'))</script>

@push('scripts')
<script>
(function () {
    const btnSubmit = document.getElementById('btnSubmitMps');
    if (!btnSubmit) return;

    const submitUrl  = JSON.parse(document.getElementById('mpsSubmitUrl').textContent);
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;

    btnSubmit.addEventListener('click', async () => {
        if (btnSubmit.disabled) return;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Submitting…';

        try {
            const resp = await fetch(submitUrl, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const json = await resp.json();

            if (resp.ok && json.success) {
                location.reload();
            } else {
                alert(json.error || 'Submission failed. Please try again.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="ti ti-send"></i> Submit Market Study';
            }
        } catch {
            alert('Network error. Please try again.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="ti ti-send"></i> Submit MPS';
        }
    });
})();
</script>
@endpush
