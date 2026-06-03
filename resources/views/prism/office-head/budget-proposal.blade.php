@extends('prism.layout')

@php
    $itemCount = count($encodedItems);
    $scopingReferenceCount = collect($encodedItems)->sum(fn ($item) => count($item['scoping']));
    $missingScopingCount = collect($encodedItems)->filter(fn ($item) => empty($item['scoping']))->count();
    $proposalTotal = collect($encodedItems)->sum('totalCost');
@endphp

@section('content')
    <ol class="grid gap-2 border-b border-slate-200 pb-5 sm:grid-cols-2 xl:grid-cols-4" aria-label="Budget proposal workflow">
        <li class="rounded-xl border border-bsu-maroon/20 bg-bsu-maroon/5 px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-wide text-bsu-maroon">Step 1</p>
            <p class="mt-1 text-sm font-bold text-slate-950">Encode proposal items</p>
        </li>
        <li class="rounded-xl border border-bsu-gold/30 bg-bsu-gold/10 px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-wide text-bsu-maroon">Step 2</p>
            <p class="mt-1 text-sm font-bold text-slate-950">Run market scoping</p>
        </li>
        <li class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Step 3</p>
            <p class="mt-1 text-sm font-bold text-slate-950">Review references</p>
        </li>
        <li class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Step 4</p>
            <p class="mt-1 text-sm font-bold text-slate-950">Submit for approval</p>
        </li>
    </ol>

    <section class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="grid gap-5">
            <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-label="Annual budget proposal form">
                <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Proposal Details</h2>
                        <p class="mt-1 text-sm text-slate-500">Basic information for the annual procurement budget proposal.</p>
                    </div>
                    <span class="inline-flex min-h-7 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-600 ring-1 ring-inset ring-slate-200">Draft</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        Office / College
                        <input value="{{ $proposalForm['officeName'] }}" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-[15px] font-semibold text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        Fiscal Year
                        <input value="{{ $proposalForm['fiscalYear'] }}" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-[15px] font-semibold text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        Date Prepared
                        <input type="date" value="{{ $proposalForm['date'] }}" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-[15px] font-semibold text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        Proposed Budget
                        <input value="PHP {{ number_format($proposalForm['totalProposedBudget']) }}" class="rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-[15px] font-bold text-bsu-maroon shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                    </label>
                </div>
            </form>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Procurement Items</h2>
                        <p class="mt-1 text-sm text-slate-500">Encode item details, then run market scoping for price references.</p>
                    </div>
                    <button id="runMarketScopingButton" type="button" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-bsu-maroon px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-maroon/25">
                        <i data-lucide="sparkles" class="h-4 w-4"></i>
                        Run market scoping
                    </button>
                </div>

                <form id="proposalItemForm" class="grid gap-4 lg:grid-cols-12">
                    <input id="itemId" name="itemId" type="hidden">
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 lg:col-span-6">
                        Item Description
                        <input id="itemDescription" name="description" placeholder="e.g. Laptop computer for laboratory use" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15" required>
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 lg:col-span-2">
                        Unit
                        <select id="itemUnit" name="unit" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                            <option>unit</option>
                            <option>set</option>
                            <option>lot</option>
                            <option>piece</option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 lg:col-span-2">
                        Quantity
                        <input id="itemQuantity" name="quantity" type="number" min="1" value="1" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15" required>
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 lg:col-span-2">
                        Unit Cost
                        <input id="itemUnitCost" name="estimatedUnitCost" type="number" min="0" value="0" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15" required>
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 lg:col-span-8">
                        Purpose / Justification
                        <input id="itemJustification" name="justification" placeholder="Short procurement justification" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 lg:col-span-2">
                        Target Quarter
                        <select id="itemQuarter" name="targetQuarter" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                            <option>Q1</option>
                            <option>Q2</option>
                            <option>Q3</option>
                            <option>Q4</option>
                        </select>
                    </label>
                    <div class="flex items-end lg:col-span-2">
                        <button id="saveItemButton" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border border-bsu-maroon bg-white px-4 py-2 text-sm font-bold text-bsu-maroon shadow-sm transition hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-maroon/20">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Add item
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="grid gap-5 xl:sticky xl:top-[96px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Submission Readiness</h2>
                        <p class="mt-1 text-sm text-slate-500">Market scoping must support encoded items.</p>
                    </div>
                    <span id="proposalReadyBadge" class="inline-flex min-h-7 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">Draft</span>
                </div>

                <dl class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Items</dt>
                        <dd id="proposalSummaryItems" class="mt-1 text-2xl font-bold text-slate-950">{{ $itemCount }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">References</dt>
                        <dd id="proposalSummaryReferences" class="mt-1 text-2xl font-bold text-bsu-maroon">{{ $scopingReferenceCount }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Missing scoping</dt>
                        <dd id="proposalSummaryMissing" class="mt-1 text-2xl font-bold text-slate-950">{{ $missingScopingCount }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Encoded amount</dt>
                        <dd id="proposalSummaryTotal" class="mt-1 text-lg font-bold text-slate-950">PHP {{ number_format($proposalTotal) }}</dd>
                    </div>
                </dl>

                <button id="submitProposalButton" type="button" class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-maroon/25">
                    <i data-lucide="send" class="h-4 w-4"></i>
                    Submit proposal
                </button>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-slate-950">Market References</h2>
                    <span id="proposalReferenceTotalBadge" class="rounded-full bg-bsu-gold/15 px-3 py-1 text-xs font-bold text-bsu-maroon ring-1 ring-inset ring-bsu-gold/30">{{ $scopingReferenceCount }} total</span>
                </div>

                <div id="proposalReferenceList" class="grid max-h-[420px] gap-3 overflow-y-auto pr-1">
                    @foreach ($encodedItems as $item)
                        @php
                            $references = collect($item['scoping']);
                            $lowestReference = $references->sortBy('price')->first();
                        @endphp
                        <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-950">{{ $item['description'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item['targetQuarter'] }} · {{ count($item['scoping']) }} references</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-bsu-maroon ring-1 ring-inset ring-slate-200">{{ $item['unit'] }}</span>
                            </div>
                            @if ($lowestReference)
                                <p class="mt-2 text-xs font-semibold text-slate-600">Lowest: {{ $lowestReference['supplierName'] }} · PHP {{ number_format($lowestReference['price']) }}</p>
                            @else
                                <p class="mt-2 text-xs font-semibold text-amber-700">Needs market scoping before submission.</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        </aside>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Proposal Line Items</h2>
                <p class="mt-1 text-sm text-slate-500"><span id="proposalItemCount">{{ $itemCount }}</span> encoded procurement items.</p>
            </div>
            <button type="button" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:border-bsu-maroon hover:text-bsu-maroon">
                <i data-lucide="file-down" class="h-4 w-4"></i>
                Export draft
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Qty / Unit</th>
                            <th class="px-4 py-3">Unit Cost</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Quarter</th>
                            <th class="px-4 py-3">Market Scoping</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="encodedItemsTable" class="divide-y divide-slate-100 bg-white">
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script type="application/json" id="initialProposalItems">@json($encodedItems)</script>
@endsection
