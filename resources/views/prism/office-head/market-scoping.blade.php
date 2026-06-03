@extends('prism.layout')

@php
    $sources = collect($supplierReferences)->pluck('sourceType')->unique()->sort()->values();
    $brands = collect($supplierReferences)->pluck('brand')->unique()->sort()->values();
    $categories = collect($supplierReferences)->pluck('category')->unique()->sort()->values();
    $availabilityOptions = collect($supplierReferences)->pluck('availability')->unique()->sort()->values();
    $initialItem = collect($proposalItems)->first();
@endphp

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Office Head / Dean</p>
            <h1>Market Scoping</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Compare supplier price references, screen weak matches, and attach valid sources to the proposal.</p>
        </div>
        <div class="flex flex-wrap gap-2.5 lg:min-w-max lg:justify-end">
            <span class="inline-flex h-10 items-center rounded-md bg-bsu-maroon/10 px-3 text-sm font-bold text-bsu-maroon ring-1 ring-inset ring-bsu-maroon/15">Minimum 3 valid references</span>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[300px_minmax(0,1fr)_360px] 2xl:grid-cols-[320px_minmax(0,1fr)_380px]">
        <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] xl:sticky xl:top-[96px] xl:self-start">
            <div class="mb-5">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Market study input</p>
                <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">Reference Search</h2>
            </div>

            <div class="grid gap-4 [&_label>span]:mb-1.5 [&_label>span]:block [&_label>span]:text-xs [&_label>span]:font-extrabold [&_label>span]:uppercase [&_label>span]:tracking-[0.07em] [&_label>span]:text-slate-500">
                <label>
                    <span>Budget proposal item</span>
                    <select id="marketItemSelect" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-800">
                        @foreach ($proposalItems as $item)
                            <option value="{{ $item['id'] }}">{{ $item['itemName'] }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-extrabold uppercase tracking-[0.08em] text-bsu-maroon" id="marketItemCode">{{ $initialItem['proposalCode'] ?? '' }}</p>
                    <h3 class="mt-1 text-base font-extrabold text-slate-950" id="marketItemTitle">{{ $initialItem['itemName'] ?? '' }}</h3>
                    <dl class="mt-3 grid gap-2 text-sm leading-6">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Estimated unit cost</dt>
                            <dd class="font-extrabold text-slate-950" id="marketItemBudget">PHP {{ number_format($initialItem['estimatedUnitCost'] ?? 0) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Quantity</dt>
                            <dd class="font-bold text-slate-700" id="marketItemQuantity">{{ $initialItem['quantity'] ?? 0 }} {{ $initialItem['unit'] ?? '' }}</dd>
                        </div>
                    </dl>
                    <p class="mt-3 text-sm leading-6 text-slate-600" id="marketItemSpecs">{{ $initialItem['specification'] ?? '' }}</p>
                </div>

                <label>
                    <span>Item details search</span>
                    <div class="relative">
                        <i class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" data-lucide="search" aria-hidden="true"></i>
                        <input id="marketSearchInput" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3 text-sm font-medium text-slate-800" type="search" placeholder="Item, brand, size, specs, supplier">
                    </div>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <label>
                        <span>Min price</span>
                        <input id="marketMinPrice" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800" type="number" min="0" placeholder="0">
                    </label>
                    <label>
                        <span>Max price</span>
                        <input id="marketMaxPrice" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800" type="number" min="0" placeholder="Any">
                    </label>
                </div>

                <label>
                    <span>Supplier/source type</span>
                    <select id="marketSourceFilter" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800">
                        <option value="all">All source types</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source }}">{{ $source }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Brand</span>
                    <select id="marketBrandFilter" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800">
                        <option value="all">All brands</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand }}">{{ $brand }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Category</span>
                    <select id="marketCategoryFilter" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800">
                        <option value="all">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Availability</span>
                    <select id="marketAvailabilityFilter" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800">
                        <option value="all">All availability statuses</option>
                        @foreach ($availabilityOptions as $availability)
                            <option value="{{ $availability }}">{{ $availability }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Specification keywords</span>
                    <input id="marketKeywordFilter" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800" type="text" placeholder="e.g. warranty, HDMI, calibration">
                </label>

                <button id="marketResetFilters" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-bsu-maroon/35 bg-white px-4 text-sm font-bold text-bsu-maroon shadow-sm transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button">
                    <i class="h-4 w-4" data-lucide="refresh-cw" aria-hidden="true"></i>
                    Reset Filters
                </button>
            </div>
        </aside>

        <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Supplier price references</p>
                    <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">Supplier References</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span id="marketResultCount" class="inline-flex h-9 items-center rounded-full bg-slate-100 px-3 text-xs font-extrabold text-slate-700 ring-1 ring-inset ring-slate-200">0 references</span>
                    <span id="marketSelectedCount" class="inline-flex h-9 items-center rounded-full bg-bsu-maroon/10 px-3 text-xs font-extrabold text-bsu-maroon ring-1 ring-inset ring-bsu-maroon/20">0 selected</span>
                </div>
            </div>

            <div id="marketSmartMessages" class="mb-5 grid gap-2"></div>
            <div id="marketResultsGrid" class="grid grid-cols-1 gap-4 2xl:grid-cols-2"></div>
        </section>

        <aside class="grid gap-5 xl:sticky xl:top-[96px] xl:self-start">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Selected references</p>
                        <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">Reference Shortlist</h2>
                    </div>
                    <span id="marketValidCount" class="inline-flex min-h-7 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">0 valid</span>
                </div>
                <div id="marketShortlist" class="grid gap-3"></div>
                <button id="marketAttachButton" class="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button" disabled>
                    <i class="h-4 w-4" data-lucide="file-text" aria-hidden="true"></i>
                    Attach to Proposal
                </button>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                <div class="mb-4">
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Comparison summary</p>
                    <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">Budget Validation</h2>
                </div>
                <div id="marketSummary" class="grid gap-3"></div>
            </section>
        </aside>
    </section>

    <div id="marketReferenceDrawer" class="fixed inset-0 z-[90] hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/45" data-market-details-close></div>
        <aside class="absolute right-0 top-0 flex h-full w-full max-w-2xl flex-col overflow-hidden bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-bsu-maroon">Supplier reference details</p>
                    <h2 id="marketDetailsTitle" class="mt-1 line-clamp-2 text-xl font-extrabold leading-7 text-slate-950">Reference details</h2>
                </div>
                <button class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white text-sm font-extrabold text-slate-600 hover:border-bsu-maroon hover:text-bsu-maroon" type="button" data-market-details-close aria-label="Close reference details">X</button>
            </div>
            <div id="marketDetailsContent" class="min-h-0 flex-1 overflow-y-auto px-5 py-5"></div>
        </aside>
    </div>

    <script type="application/json" id="marketScopingItems">@json($proposalItems)</script>
    <script type="application/json" id="marketSupplierReferences">@json($supplierReferences)</script>
@endsection
