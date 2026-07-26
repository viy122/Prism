@extends('prism.layouts.app')
@section('title', 'Proposal Review | Budget Office')

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

    .card {
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm);
    }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .btn-primary {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; height: 42px; padding: 0 20px; border-radius: 10px;
        background: var(--crimson); color: #fff;
        font-size: 13px; font-weight: 700; border: none; cursor: pointer;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 2px 10px rgba(139,26,28,.2);
        transition: background .2s; white-space: nowrap;
    }
    .btn-primary:hover { background: var(--crimson-dark); }

    .btn-outline {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; height: 42px; padding: 0 20px; border-radius: 10px;
        background: var(--white); color: var(--crimson);
        font-size: 13px; font-weight: 700; cursor: pointer;
        font-family: 'Poppins', sans-serif;
        border: 1px solid var(--crimson-border);
        transition: background .2s, border-color .2s; white-space: nowrap;
    }
    .btn-outline:hover { background: var(--crimson-mid); border-color: var(--crimson); }

    .field-label { font-size: 13px; font-weight: 700; color: var(--s700); margin-bottom: 7px; display: block; }
    .field-select {
        height: 44px; width: 100%; border-radius: 10px;
        border: 1px solid var(--s300); background-color: var(--white);
        padding: 0 40px 0 14px; font-size: 13.5px; font-weight: 500;
        color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
        transition: border-color .15s, box-shadow .15s;
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .field-select:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }
    .field-textarea {
        width: 100%; border-radius: 10px;
        border: 1px solid var(--s300); background: var(--white);
        padding: 12px 14px; font-size: 13px; font-weight: 500;
        color: var(--s900); font-family: 'Poppins', sans-serif; outline: none;
        resize: vertical; line-height: 1.6;
        transition: border-color .15s, box-shadow .15s;
    }
    .field-textarea:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }
    .field-textarea::placeholder { color: var(--s400); }

    .meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .meta-box { background: var(--s50); border: 1px solid var(--s200); border-radius: 12px; padding: 14px 16px; }
    .meta-box dt { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--s400); margin-bottom: 5px; }
    .meta-box dd { font-size: 13px; font-weight: 700; color: var(--s900); }

    .table-wrap {
        border-radius: 12px; border: 1px solid var(--s200);
        overflow: auto; max-height: 64vh; background: var(--white);
        box-shadow: inset 0 1px 4px rgba(15,23,42,.04);
    }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th {
        position: sticky; top: 0; z-index: 5;
        background: var(--s50); border-bottom: 1px solid var(--s200);
        padding: 11px 16px;
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--s500); white-space: nowrap;
    }
    tbody td { padding: 14px 16px; border-bottom: 1px solid var(--s100); vertical-align: top; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--crimson-mid); }

    .item-name  { font-size: 13px; font-weight: 700; color: var(--s900); }
    .item-sub   { font-size: 12px; color: var(--s500); margin-top: 4px; line-height: 1.6; }
    .amount-val { font-size: 13px; font-weight: 700; color: var(--s900); }

    .scope-toggle {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--white); border: 1px solid var(--s200); border-radius: 8px;
        padding: 7px 11px; cursor: pointer; font-family: 'Poppins', sans-serif;
        font-size: 12px; font-weight: 700; color: var(--crimson); white-space: nowrap;
        transition: background .15s, border-color .15s;
    }
    .scope-toggle:hover { background: var(--crimson-mid); border-color: var(--crimson-border); }
    .scope-chevron { font-size: 13px; transition: transform .2s; flex-shrink: 0; }
    .scope-toggle.open .scope-chevron { transform: rotate(180deg); }
    .scope-list { display: flex; flex-direction: column; gap: 8px; min-width: 220px; margin-top: 8px; }
    .scope-card { border: 1px solid var(--s200); border-radius: 10px; background: var(--white); padding: 11px 13px; box-shadow: var(--sh-sm); }
    .scope-supplier { font-size: 12px; font-weight: 700; color: var(--s900); margin-bottom: 3px; }
    .scope-price    { font-size: 12px; font-weight: 600; color: var(--s600); }
    .scope-link     { font-size: 12px; font-weight: 700; color: var(--crimson); text-decoration: none; }
    .scope-link:hover { text-decoration: underline; }
    .scope-date     { font-size: 11px; color: var(--s400); margin-top: 2px; }

    .action-row { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }

    /* ── Per-item verdict (check / X) ── */
    .fin-verdict { display: flex; flex-direction: column; gap: 8px; }
    .verdict-btns { display: flex; gap: 6px; }
    .btn-verdict { display: inline-flex; align-items: center; gap: 5px; height: 34px; padding: 0 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: 1.5px solid var(--s200); background: var(--white); color: var(--s500); transition: all .15s; }
    .btn-verdict i { font-size: 14px; }
    .btn-ok { border-color: #86efac; color: #166534; }
    .btn-ok:hover { border-color: #16a34a; background: #f0fdf4; }
    .btn-ok.active { border-color: #16a34a; background: #16a34a; color: #fff; }
    .btn-flag { border-color: #fecaca; color: #991b1b; }
    .btn-flag:hover { border-color: #dc2626; background: #fef2f2; }
    .btn-flag.active { border-color: #dc2626; background: #dc2626; color: #fff; }
    .btn-save-remark { display: inline-flex; align-items: center; gap: 5px; height: 32px; padding: 0 12px; margin-top: 6px; border-radius: 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; border: none; background: var(--crimson); color: #fff; transition: background .15s; }
    .btn-save-remark:hover { background: var(--crimson-dark); }
    .btn-save-remark:disabled { opacity: .6; cursor: not-allowed; }
    .verdict-status { font-size: 11px; font-weight: 700; }
    .verdict-status.ok { color: #166534; }
    .verdict-status.err { color: #991b1b; }
    .remark-display { display: flex; flex-direction: column; gap: 6px; background: var(--s50); border: 1px solid var(--s200); border-radius: 8px; padding: 10px 12px; }
    .remark-text { font-size: 12px; color: var(--s700); line-height: 1.5; white-space: pre-wrap; }
    .btn-edit-remark { align-self: flex-start; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: var(--crimson); background: none; border: none; cursor: pointer; padding: 0; font-family: 'Poppins', sans-serif; }
    .btn-edit-remark:hover { text-decoration: underline; }

    /* ── Overall action buttons (endorse / return) ── */
    .btn-endorse { display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 39px; padding: 0 18px; border-radius: 9px; background: #16a34a; color: #fff; font-size: 12.5px; font-weight: 700; border: none; cursor: pointer; font-family: 'Poppins', sans-serif; box-shadow: 0 2px 10px rgba(22,163,74,.25); transition: background .2s; white-space: nowrap; flex: 1; min-width: 160px; }
    .btn-endorse:hover { background: #15803d; }
    .btn-endorse:disabled { opacity: .5; cursor: not-allowed; box-shadow: none; background: #16a34a; }
    .btn-endorse i { font-size: 14px; }
    .btn-return { display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 39px; padding: 0 18px; border-radius: 9px; background: #dc2626; color: #fff; font-size: 12.5px; font-weight: 700; border: none; cursor: pointer; font-family: 'Poppins', sans-serif; box-shadow: 0 2px 10px rgba(220,38,38,.25); transition: background .2s; white-space: nowrap; flex: 1; min-width: 160px; }
    .btn-return:hover { background: #b91c1c; }
    .btn-return i { font-size: 14px; }

    @media (max-width: 1024px) {
        .page-shell { padding: 16px 16px 40px; }
        .meta-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 640px) { .meta-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="page-shell">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-clipboard-check"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Budget Office</p>
            <h1 class="page-hdr-title">Proposal Review</h1>
            <p class="page-hdr-sub">Review office details, encoded procurement items, justifications, and market scoping references.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Budget Office</p>
                <h2 class="card-title">
                    Proposal Review
                    @if($pendingCount > 0)
                        <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:11px;vertical-align:middle;margin-left:6px;">{{ $pendingCount }} awaiting endorsement</span>
                    @endif
                </h2>
                <p class="card-sub">Review office details, encoded procurement items, justifications, target quarters, and AI market scoping references.</p>
            </div>
        </div>
        <div style="max-width:100%;">
            <label class="field-label" for="financeProposalSelector">Select Proposal</label>
            <select class="field-select" id="financeProposalSelector">
                <option value="" @selected(!$selectedProposal)>@if(count($proposals)) — Select a proposal to review — @else No proposals pending review @endif</option>
                @foreach ($proposals as $proposal)
                    <option value="{{ route('finance-office.proposal-review.show', ['proposal' => $proposal['id']]) }}"
                        @selected($selectedProposal && $selectedProposal['id'] === $proposal['id'])>
                        {{ $proposal['status'] === 'Submitted' ? '[Awaiting Endorsement] ' : '' }}{{ $proposal['title'] }} — {{ $proposal['status'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Always rendered regardless of which (if any) proposal ends up selected below —
         after returning/endorsing, the proposal just acted on is no longer "Submitted"
         and often isn't auto-selected again, so this can't live inside that branch or
         the confirmation silently never shows. --}}
    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;color:#166534;display:flex;align-items:center;gap:8px;">
            <i class="ti ti-circle-check-filled" style="font-size:16px;color:#16a34a;"></i>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;color:#991b1b;display:flex;align-items:center;gap:8px;">
            <i class="ti ti-alert-circle" style="font-size:16px;color:#dc2626;"></i>
            {{ $errors->first() }}
        </div>
    @endif

    @if(!$selectedProposal)
    <div class="card" style="padding:40px 22px;text-align:center;color:#64748b;">
        @if(count($proposals))
            <p style="font-size:15px;font-weight:700;">Select a proposal above to view its details.</p>
            <p style="font-size:13px;margin-top:6px;">Line items, justifications, and market scoping references will appear here once you pick one.</p>
        @else
            <p style="font-size:15px;font-weight:700;">No proposals pending review.</p>
            <p style="font-size:13px;margin-top:6px;">Submitted proposals from Office Heads will appear here.</p>
        @endif
    </div>
    @else

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Full proposal details</p>
                <h2 class="card-title">{{ $selectedProposal['title'] }}</h2>
            </div>
            <x-prism.status-badge :status="$selectedProposal['status']" />
        </div>
        <dl class="meta-grid">
            <div class="meta-box">
                <dt>Office</dt>
                <dd>{{ $selectedProposal['office']['name'] }}</dd>
            </div>
            <div class="meta-box">
                <dt>Office Head</dt>
                <dd>{{ $selectedProposal['office']['head'] }}</dd>
            </div>
            <div class="meta-box">
                <dt>Fiscal Year</dt>
                <dd>{{ $selectedProposal['office']['fiscalYear'] }}</dd>
            </div>
            <div class="meta-box">
                <dt>Submitted Date</dt>
                <dd>{{ $selectedProposal['office']['submittedDate'] }}</dd>
            </div>
            <div class="meta-box">
                <dt>Total Amount</dt>
                <dd>PHP {{ number_format($selectedProposal['office']['totalAmount']) }}</dd>
            </div>
        </dl>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Encoded items</p>
                <h2 class="card-title">Line Item Review</h2>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item &amp; Justification</th>
                        <th>Qty</th>
                        <th>Estimated Cost</th>
                        <th>Quarter</th>
                        <th>AI Market Scoping</th>
                        <th>Budget Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedProposal['items'] as $index => $item)
                        <tr>
                            <td>
                                <p class="item-name">{{ $item['description'] }}</p>
                                <p class="item-sub">{{ $item['justification'] }}</p>
                            </td>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">
                                {{ $item['quantity'] }} {{ $item['unit'] }}
                            </td>
                            <td>
                                <p class="amount-val">PHP {{ number_format($item['totalCost']) }}</p>
                                <p class="item-sub">Unit: PHP {{ number_format($item['estimatedUnitCost']) }}</p>
                            </td>
                            <td style="font-size:13px;font-weight:600;color:var(--s600);white-space:nowrap;">
                                {{ $item['targetQuarter'] }}
                            </td>
                            <td>
                                @if(count($item['scoping']) > 0)
                                    <button type="button" class="scope-toggle" onclick="window.toggleScope(this)">
                                        <i class="ti ti-list" style="font-size:13px"></i>
                                        {{ count($item['scoping']) }} reference{{ count($item['scoping']) > 1 ? 's' : '' }}
                                        <i class="ti ti-chevron-down scope-chevron"></i>
                                    </button>
                                    <div class="scope-list" style="display:none">
                                        @foreach ($item['scoping'] as $scope)
                                            <div class="scope-card">
                                                <p class="scope-supplier">{{ $scope['supplierName'] }}</p>
                                                <p class="scope-price">PHP {{ number_format($scope['price']) }}</p>
                                                <a class="scope-link" href="{{ $scope['sourceLink'] }}" target="_blank" rel="noreferrer">Source link ↗</a>
                                                <p class="scope-date">{{ $scope['dateRetrieved'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size:12px;color:var(--s400);">No references</span>
                                @endif
                            </td>
                            <td style="min-width:210px;">
                                <div class="fin-verdict" data-item-id="{{ $item['id'] }}" data-remark-url="{{ $item['remarkUrl'] }}">
                                    @if($selectedProposal['actionable'])
                                    <div class="verdict-btns">
                                        <button type="button" class="btn-verdict btn-ok{{ $item['financeOk'] === true ? ' active' : '' }}" title="Approve this item">
                                            <i class="ti ti-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn-verdict btn-flag{{ $item['financeOk'] === false ? ' active' : '' }}" title="Issue this item">
                                            <i class="ti ti-message-2"></i> Issue
                                        </button>
                                    </div>
                                    @else
                                        @if($item['financeOk'] === true)
                                        <span style="font-size:11px;font-weight:700;color:#166534;"><i class="ti ti-check"></i> Approved</span>
                                        @elseif($item['financeOk'] === false)
                                        <span style="font-size:11px;font-weight:700;color:#991b1b;"><i class="ti ti-message-2"></i> Issued</span>
                                        @else
                                        <span style="font-size:11px;font-weight:600;color:var(--s400);">Not yet reviewed</span>
                                        @endif
                                    @endif
                                    <div class="remark-display" style="{{ $item['financeOk'] === false ? '' : 'display:none;' }}">
                                        <p class="remark-text">{{ $item['financeRemark'] }}</p>
                                        @if($selectedProposal['actionable'])
                                        <button type="button" class="btn-edit-remark"><i class="ti ti-pencil"></i> Edit</button>
                                        @endif
                                    </div>
                                    @if($selectedProposal['actionable'])
                                    <div class="verdict-remark" style="display:none;">
                                        <textarea class="field-textarea remark-input" rows="3"
                                            placeholder="Add remarks for this item (required)">{{ $item['financeRemark'] }}</textarea>
                                        <button type="button" class="btn-save-remark">
                                            <i class="ti ti-device-floppy"></i> Save Remark
                                        </button>
                                    </div>
                                    @endif
                                    <p class="verdict-status" style="display:none;"></p>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Overall proposal</p>
                <h2 class="card-title">Budget Remarks</h2>
            </div>
        </div>
        @if($selectedProposal['returnRemarks'])
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
            <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#991B1B;margin-bottom:4px;">
                Returned by {{ $selectedProposal['returnRemarks']['from'] }} — {{ $selectedProposal['returnRemarks']['by'] }}, {{ $selectedProposal['returnRemarks']['date'] }}
            </p>
            <p style="font-size:13px;font-weight:600;color:#7F1D1D;white-space:pre-wrap;">{{ $selectedProposal['returnRemarks']['text'] ?: 'No remarks were given.' }}</p>
        </div>
        @endif
        <form method="POST" id="financeReviewForm" action="">
            @csrf
            <input type="hidden" name="remarks" id="financeRemarksHidden">
            <label class="field-label" for="financeOverallRemarks">Overall proposal remarks</label>
            <textarea class="field-textarea" id="financeOverallRemarks" rows="5"
                placeholder="Add approval notes or return instructions for the office"></textarea>
            @if($selectedProposal['actionable'])
            @php $anyDisapproved = collect($selectedProposal['items'])->contains('financeOk', false); @endphp
            @if($selectedProposal['status'] === 'Returned')
            <p style="margin-top:6px;font-size:12px;font-weight:600;color:#92400E;background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:9px 12px;">
                <i class="ti ti-alert-triangle"></i> Returned by the Chancellor — reconsider the items and remarks below, then Endorse or Return again.
            </p>
            @endif
            <div class="action-row" style="margin-top:14px;">
                <button id="btnEndorse" class="btn-endorse" type="submit" {{ $anyDisapproved ? 'disabled' : '' }}
                    data-url="{{ route('finance-office.proposal-review.endorse', $selectedProposal['id']) }}"
                    onclick="return submitFinanceForm(this)">
                    <i class="ti ti-circle-check"></i>
                    Endorse
                </button>
                <button class="btn-return" type="submit"
                    data-url="{{ route('finance-office.proposal-review.return', $selectedProposal['id']) }}"
                    onclick="return submitFinanceReturn(this)">
                    <i class="ti ti-arrow-back-up"></i>
                    Return
                </button>
            </div>
            <p id="endorseBlockedMsg" style="margin-top:10px;font-size:12px;font-weight:600;color:#991b1b;{{ $anyDisapproved ? '' : 'display:none;' }}">
                {{ collect($selectedProposal['items'])->where('financeOk', false)->count() }} item(s) issued — return the proposal instead.
            </p>
            @else
            <p style="margin-top:14px;font-size:13px;font-weight:600;color:var(--s500);">
                This proposal has already been <strong>{{ strtolower($selectedProposal['status']) }}</strong> — no further action available.
            </p>
            @endif
        </form>
    </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
document.getElementById('financeProposalSelector')?.addEventListener('change', function () {
    if (this.value) window.location.href = this.value;
});

window.toggleScope = function (btn) {
    const list = btn.nextElementSibling;
    if (!list) return;
    const isOpen = list.style.display !== 'none';
    list.style.display = isOpen ? 'none' : 'flex';
    btn.classList.toggle('open', !isOpen);
};

function submitFinanceForm(btn) {
    document.getElementById('financeRemarksHidden').value = document.getElementById('financeOverallRemarks').value;
    document.getElementById('financeReviewForm').action = btn.dataset.url;
    return true;
}

function submitFinanceReturn(btn) {
    const remarks = document.getElementById('financeOverallRemarks').value.trim();
    document.getElementById('financeRemarksHidden').value = remarks;
    document.getElementById('financeReviewForm').action = btn.dataset.url;
    return true;
}

/* ── Per-item check / X verdicts ── */
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    async function saveVerdict(box, ok, remark) {
        const status = box.querySelector('.verdict-status');
        try {
            const res = await fetch(box.dataset.remarkUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ ok, remark: remark || '' }),
            });
            const json = await res.json();
            status.style.display = '';
            if (res.ok && json.success) {
                status.textContent = ok ? '✓ Marked as budget OK' : '✓ Remarks saved';
                status.className = 'verdict-status ok';
                setTimeout(() => { status.style.display = 'none'; }, 2200);
                return true;
            }
            status.textContent = json.message || 'Could not save.';
            status.className = 'verdict-status err';
            return false;
        } catch {
            status.style.display = '';
            status.textContent = 'Network error.';
            status.className = 'verdict-status err';
            return false;
        }
    }

    function updateEndorseGate() {
        const anyDisapproved = document.querySelectorAll('.btn-flag.active').length > 0;
        const btn = document.getElementById('btnEndorse');
        if (btn) btn.disabled = anyDisapproved;
        const msg = document.getElementById('endorseBlockedMsg');
        if (msg) msg.style.display = anyDisapproved ? '' : 'none';
    }

    document.querySelectorAll('.fin-verdict').forEach(box => {
        const btnOk      = box.querySelector('.btn-ok');
        const btnFlag    = box.querySelector('.btn-flag');
        const display    = box.querySelector('.remark-display');
        const remarkText = box.querySelector('.remark-text');
        const remarkEl   = box.querySelector('.verdict-remark');
        const input      = box.querySelector('.remark-input');
        const btnSave    = box.querySelector('.btn-save-remark');
        const btnEdit    = box.querySelector('.btn-edit-remark');

        // Not actionable (e.g. the proposal is with the Chancellor, or already
        // approved) — verdict controls aren't rendered here at all; nothing to wire.
        if (!btnOk || !btnFlag) return;

        btnOk.addEventListener('click', async () => {
            const wasActive = btnOk.classList.contains('active');
            btnOk.classList.add('active');
            btnFlag.classList.remove('active');
            remarkEl.style.display = 'none';
            display.style.display = 'none';
            if (!wasActive) {
                const saved = await saveVerdict(box, true);
                if (!saved) btnOk.classList.remove('active');
            }
            updateEndorseGate();
        });

        btnFlag.addEventListener('click', () => {
            btnFlag.classList.add('active');
            btnOk.classList.remove('active');
            display.style.display = 'none';
            remarkEl.style.display = '';
            input.focus();
            updateEndorseGate();
        });

        btnEdit.addEventListener('click', () => {
            display.style.display = 'none';
            remarkEl.style.display = '';
            input.focus();
        });

        btnSave.addEventListener('click', async () => {
            const remark = input.value.trim();
            if (!remark) {
                input.style.borderColor = '#dc2626';
                input.focus();
                return;
            }
            input.style.borderColor = '';
            btnSave.disabled = true;
            const saved = await saveVerdict(box, false, remark);
            btnSave.disabled = false;
            if (saved) {
                remarkText.textContent = remark;
                remarkEl.style.display = 'none';
                display.style.display = '';
            }
            updateEndorseGate();
        });
    });

    updateEndorseGate();
})();
</script>
@endpush
