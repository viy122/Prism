@extends('prism.layout')

@php
    $proposalCollection = collect($proposals);
    $proposalCount = $proposalCollection->count();
    $activeReviewCount = $proposalCollection->whereIn('status', ['Submitted', 'Under Review', 'Endorsed'])->count();
    $returnedCount = $proposalCollection->where('status', 'Returned')->count();
    $approvedAmount = $proposalCollection->where('status', 'Approved')->sum('totalAmount');
@endphp

@section('content')
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Proposals</p>
                    <p class="mt-1 text-2xl font-bold text-slate-950">{{ $proposalCount }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Active Review</p>
                    <p class="mt-1 text-2xl font-bold text-bsu-maroon">{{ $activeReviewCount }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Returned</p>
                    <p class="mt-1 text-2xl font-bold text-slate-950">{{ $returnedCount }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Approved Amount</p>
                    <p class="mt-1 text-lg font-bold text-slate-950">PHP {{ number_format($approvedAmount) }}</p>
                </div>
            </div>

            <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" href="{{ route('office-head.budget-proposal') }}">
                <i data-lucide="file-plus-2" class="h-4 w-4" aria-hidden="true"></i>
                New Budget Proposal
            </a>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2" aria-label="Proposal filters">
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Status
                <select id="proposalStatusFilter" class="h-11 rounded-xl border border-slate-300 bg-white px-3.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                    <option value="all">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Fiscal Year
                <select id="proposalYearFilter" class="h-11 rounded-xl border border-slate-300 bg-white px-3.5 text-[15px] text-slate-950 shadow-sm focus:border-bsu-maroon focus:outline-none focus:ring-2 focus:ring-bsu-maroon/15">
                    <option value="all">All fiscal years</option>
                    @foreach ($fiscalYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,1fr)_400px]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-950">Proposal Queue</h2>
                    <p class="mt-1 text-sm text-slate-500">Select a proposal to review its approval movement.</p>
                </div>
                <span class="inline-flex min-h-7 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200" id="proposalVisibleCount">{{ $proposalCount }} shown</span>
            </div>

            <div class="grid max-h-[68vh] gap-3 overflow-y-auto pr-1" id="proposalRows">
                @foreach ($proposals as $proposal)
                    @php
                        $latestEvent = collect($proposal['timeline'])->last();
                        $isReturned = $proposal['status'] === 'Returned';
                    @endphp
                    <article
                        class="cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-bsu-maroon/30 hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/60"
                        data-proposal-row
                        data-proposal-id="{{ $proposal['id'] }}"
                        data-status="{{ $proposal['status'] }}"
                        data-year="{{ $proposal['fiscalYear'] }}"
                        tabindex="0"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-950">{{ $proposal['title'] }}</h3>
                                <p class="mt-1 text-sm font-semibold text-slate-500">FY {{ $proposal['fiscalYear'] }} · Submitted {{ $proposal['dateSubmitted'] }}</p>
                            </div>
                            <x-prism.status-badge :status="$proposal['status']" />
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Amount</p>
                                <p class="mt-1 text-sm font-bold text-slate-950">PHP {{ number_format($proposal['totalAmount']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Current Step</p>
                                <p class="mt-1 text-sm font-bold text-slate-950">{{ $latestEvent['step'] ?? 'Pending' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Action</p>
                                <p class="mt-1 text-sm font-bold {{ $isReturned ? 'text-red-700' : 'text-slate-950' }}">{{ $isReturned ? 'Revise' : 'Track' }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </article>

        <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:sticky xl:top-[96px]" id="proposalTimelinePanel" aria-live="polite">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="timelineTitle" class="text-xl font-bold text-slate-950">Select a proposal</h2>
                    <p id="timelineMeta" class="mt-1 text-sm font-semibold text-slate-500">Timeline details will appear here.</p>
                </div>
                <span id="timelineStatusBadge" class="inline-flex min-h-7 shrink-0 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">Status</span>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Amount</p>
                    <p id="timelineAmount" class="mt-1 text-sm font-bold text-slate-950">PHP 0</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Next Action</p>
                    <p id="timelineAction" class="mt-1 text-sm font-bold text-slate-950">Select</p>
                </div>
            </div>

            <div id="timelineContent" class="flex min-h-52 flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm leading-6 text-slate-500 [&_svg]:h-10 [&_svg]:w-10 [&_svg]:text-bsu-maroon/70">
                <i data-lucide="mouse-pointer-click" aria-hidden="true"></i>
                <p>Select a proposal to view timestamps, remarks, and revision status.</p>
            </div>
        </aside>
    </section>

    <script type="application/json" id="proposalData">@json($proposals)</script>
@endsection
