@extends('prism.layout')

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Finance Office</p>
            <h1>Proposal Review</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Review office details, encoded procurement items, justifications, target quarters, and AI market scoping references.</p>
        </div>
        <div class="flex flex-wrap gap-2.5 lg:min-w-max lg:justify-end">
            <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button" data-finance-review-action="endorse">
                <i data-lucide="check-circle-2" aria-hidden="true"></i>
                Endorse
            </button>
            <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-bsu-maroon/35 bg-white px-4 text-sm font-bold text-bsu-maroon shadow-sm transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button" data-finance-review-action="return">
                <i data-lucide="undo-2" aria-hidden="true"></i>
                Return with Remarks
            </button>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] grid gap-4 sm:grid-cols-2 [&_label]:block [&_label>span]:mb-1.5 [&_label>span]:block [&_label>span]:text-sm [&_label>span]:font-bold [&_label>span]:text-slate-700 [&_input]:h-11 [&_input]:w-full [&_input]:rounded-xl [&_input]:border [&_input]:border-slate-300 [&_input]:bg-white [&_input]:px-4 [&_input]:text-base [&_input]:text-slate-800 [&_select]:h-11 [&_select]:w-full [&_select]:rounded-xl [&_select]:border [&_select]:border-slate-300 [&_select]:bg-white [&_select]:px-4 [&_select]:text-base [&_select]:text-slate-800 [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:px-4 [&_textarea]:py-3 [&_textarea]:text-base [&_textarea]:text-slate-800 [&_input:focus]:border-bsu-maroon [&_input:focus]:outline-none [&_input:focus]:ring-2 [&_input:focus]:ring-bsu-gold/40 [&_select:focus]:border-bsu-maroon [&_select:focus]:outline-none [&_select:focus]:ring-2 [&_select:focus]:ring-bsu-gold/40 [&_textarea:focus]:border-bsu-maroon [&_textarea:focus]:outline-none [&_textarea:focus]:ring-2 [&_textarea:focus]:ring-bsu-gold/40" aria-label="Proposal selector">
        <label>
            <span>Proposal</span>
            <select id="financeProposalSelector">
                @foreach ($proposals as $proposal)
                    <option value="{{ route('finance-office.proposal-review.show', ['proposal' => $proposal['id']]) }}" @selected($selectedProposal['id'] === $proposal['id'])>
                        {{ $proposal['title'] }}
                    </option>
                @endforeach
            </select>
        </label>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Full proposal details</p>
                <h2>{{ $selectedProposal['title'] }}</h2>
            </div>
            <x-prism.status-badge :status="$selectedProposal['status']" />
        </div>
        <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 [&_div]:rounded-2xl [&_div]:border [&_div]:border-slate-200/80 [&_div]:bg-slate-50 [&_div]:p-4 [&_dt]:text-xs [&_dt]:font-extrabold [&_dt]:uppercase [&_dt]:tracking-[0.08em] [&_dt]:text-slate-500 [&_dd]:mt-1.5 [&_dd]:text-sm [&_dd]:font-bold [&_dd]:text-slate-950">
            <div>
                <dt>Office</dt>
                <dd>{{ $selectedProposal['office']['name'] }}</dd>
            </div>
            <div>
                <dt>Office head</dt>
                <dd>{{ $selectedProposal['office']['head'] }}</dd>
            </div>
            <div>
                <dt>Fiscal year</dt>
                <dd>{{ $selectedProposal['office']['fiscalYear'] }}</dd>
            </div>
            <div>
                <dt>Submitted date</dt>
                <dd>{{ $selectedProposal['office']['submittedDate'] }}</dd>
            </div>
            <div>
                <dt>Total amount</dt>
                <dd>PHP {{ number_format($selectedProposal['office']['totalAmount']) }}</dd>
            </div>
            <div>
                <dt>Fund source</dt>
                <dd>{{ $selectedProposal['office']['fundSource'] }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Encoded items</p>
                <h2>Line Item Review</h2>
            </div>
        </div>
        <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
            <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                <thead>
                    <tr>
                        <th>Item and justification</th>
                        <th>Qty</th>
                        <th>Estimated costs</th>
                        <th>Target quarter</th>
                        <th>AI market scoping</th>
                        <th>Finance remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedProposal['items'] as $index => $item)
                        <tr>
                            <td>
                                <strong>{{ $item['description'] }}</strong>
                                <span class="mt-1.5 block text-sm leading-6 text-slate-500">{{ $item['justification'] }}</span>
                            </td>
                            <td>{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                            <td>
                                <strong>PHP {{ number_format($item['totalCost']) }}</strong>
                                <span class="mt-1.5 block text-sm leading-6 text-slate-500">Unit cost: PHP {{ number_format($item['estimatedUnitCost']) }}</span>
                            </td>
                            <td>{{ $item['targetQuarter'] }}</td>
                            <td>
                                <div class="grid min-w-60 gap-2.5">
                                    @foreach ($item['scoping'] as $scope)
                                        <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm leading-6 shadow-sm [&_strong]:block [&_strong]:font-bold [&_strong]:text-slate-950 [&_span]:block [&_span]:text-slate-600 [&_a]:font-bold [&_a]:text-bsu-maroon [&_a:hover]:underline">
                                            <strong>{{ $scope['supplierName'] }}</strong>
                                            <span>PHP {{ number_format($scope['price']) }}</span>
                                            <a href="{{ $scope['sourceLink'] }}" target="_blank" rel="noreferrer">Source link</a>
                                            <span>{{ $scope['dateRetrieved'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <label class="block [&>span]:mb-1.5 [&>span]:block [&>span]:text-sm [&>span]:font-bold [&>span]:text-slate-700 [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:px-4 [&_textarea]:py-3 [&_textarea]:text-base [&_textarea]:text-slate-800 [&_textarea:focus]:border-bsu-maroon [&_textarea:focus]:outline-none [&_textarea:focus]:ring-2 [&_textarea:focus]:ring-bsu-gold/40">
                                    <span>Remarks for item {{ $index + 1 }}</span>
                                    <textarea rows="4" placeholder="Add item-level review remarks"></textarea>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] [&_label]:block [&_label>span]:mb-1.5 [&_label>span]:block [&_label>span]:text-sm [&_label>span]:font-bold [&_label>span]:text-slate-700 [&_input]:h-11 [&_input]:w-full [&_input]:rounded-xl [&_input]:border [&_input]:border-slate-300 [&_input]:bg-white [&_input]:px-4 [&_input]:text-base [&_input]:text-slate-800 [&_select]:h-11 [&_select]:w-full [&_select]:rounded-xl [&_select]:border [&_select]:border-slate-300 [&_select]:bg-white [&_select]:px-4 [&_select]:text-base [&_select]:text-slate-800 [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:px-4 [&_textarea]:py-3 [&_textarea]:text-base [&_textarea]:text-slate-800 [&_input:focus]:border-bsu-maroon [&_input:focus]:outline-none [&_input:focus]:ring-2 [&_input:focus]:ring-bsu-gold/40 [&_select:focus]:border-bsu-maroon [&_select:focus]:outline-none [&_select:focus]:ring-2 [&_select:focus]:ring-bsu-gold/40 [&_textarea:focus]:border-bsu-maroon [&_textarea:focus]:outline-none [&_textarea:focus]:ring-2 [&_textarea:focus]:ring-bsu-gold/40">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Overall proposal</p>
                <h2>Finance Remarks</h2>
            </div>
        </div>
        <label>
            <span>Overall proposal remarks</span>
            <textarea id="financeOverallRemarks" rows="5" placeholder="Add endorsement notes or return instructions for the office"></textarea>
        </label>
        <div class="mt-3 flex flex-wrap gap-2">
            <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button" data-finance-review-action="endorse">
                <i data-lucide="check-circle-2" aria-hidden="true"></i>
                Endorse
            </button>
            <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-bsu-maroon/35 bg-white px-4 text-sm font-bold text-bsu-maroon shadow-sm transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button" data-finance-review-action="return">
                <i data-lucide="undo-2" aria-hidden="true"></i>
                Return with Remarks
            </button>
        </div>
    </section>
@endsection

