@extends('prism.layout')

@php
    $quarterTotal = $summary['purchasedThisQuarter'] + $summary['unpurchasedThisQuarter'];
    $purchasedPercent = $quarterTotal > 0 ? round(($summary['purchasedThisQuarter'] / $quarterTotal) * 100) : 0;
@endphp

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Office Head / Dean</p>
            <h1>Dashboard</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Track proposed budgets, approvals, procurement movement, and PR readiness for your office.</p>
        </div>
        <div class="flex flex-wrap gap-2.5 lg:min-w-max lg:justify-end">
            <a class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" href="{{ route('office-head.budget-proposal') }}">
                <i data-lucide="file-plus-2" aria-hidden="true"></i>
                New Budget Proposal
            </a>
            <a class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-bsu-maroon/35 bg-white px-4 text-sm font-bold text-bsu-maroon shadow-sm transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" href="{{ route('office-head.purchase-requests') }}">
                <i data-lucide="upload" aria-hidden="true"></i>
                Upload PR
            </a>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Dashboard totals">
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Total proposed items</span>
            <strong>{{ number_format($summary['totalProposedItems']) }}</strong>
            <small>Across active fiscal year proposals</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Total proposed budget</span>
            <strong>PHP {{ number_format($summary['totalProposedBudget']) }}</strong>
            <small>Submitted and draft items combined</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Items approved</span>
            <strong>{{ number_format($summary['approvedItems']) }}</strong>
            <small>Eligible for PR preparation</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Pending approval</span>
            <strong>{{ number_format($summary['pendingItems']) }}</strong>
            <small>Under Finance or Chancellor review</small>
        </article>
    </section>

    <section class="mt-5 grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] 2xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.1fr)_minmax(320px,0.75fr)]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">This quarter</p>
                    <h2>Own Office Procurement Progress</h2>
                </div>
                <span class="inline-flex min-h-7 items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-200">{{ $purchasedPercent }}% purchased</span>
            </div>
            <div class="grid grid-cols-2 gap-4 [&>div]:rounded-2xl [&>div]:border [&>div]:border-slate-200/80 [&>div]:bg-slate-50 [&>div]:p-4 [&_strong]:block [&_strong]:text-[1.65rem] 2xl:[&_strong]:text-3xl [&_strong]:font-extrabold [&_strong]:tracking-tight [&_strong]:text-bsu-maroon [&_span]:text-sm [&_span]:font-bold [&_span]:text-slate-500">
                <div>
                    <strong>{{ $summary['purchasedThisQuarter'] }}</strong>
                    <span>Purchased</span>
                </div>
                <div>
                    <strong>{{ $summary['unpurchasedThisQuarter'] }}</strong>
                    <span>Unpurchased</span>
                </div>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100 ring-1 ring-inset ring-slate-200 [&>span]:block [&>span]:h-full [&>span]:rounded-full [&>span]:bg-bsu-maroon" aria-label="Purchased versus unpurchased">
                <span style="width: {{ $purchasedPercent }}%"></span>
            </div>
            <p class="mt-4 rounded-2xl bg-bsu-maroon/5 px-4 py-3 text-sm leading-6 text-slate-600">
                {{ $summary['unpurchasedThisQuarter'] }} item{{ $summary['unpurchasedThisQuarter'] === 1 ? '' : 's' }} still need procurement movement this quarter.
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Activity</p>
                    <h2>Recent Status Updates</h2>
                </div>
            </div>
            <div class="grid gap-2">
                @foreach ($recentUpdates as $update)
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 transition hover:border-bsu-gold/40 hover:bg-white hover:shadow-sm sm:flex-row sm:items-start sm:justify-between [&_strong]:block [&_strong]:text-sm [&_strong]:font-bold [&_strong]:text-slate-950 [&_span]:mt-1 [&_span]:block [&_span]:text-sm [&_span]:leading-6 [&_span]:text-slate-600">
                        <div>
                            <strong>{{ $update['title'] }}</strong>
                            <span>{{ $update['details'] }}</span>
                        </div>
                        <div class="flex shrink-0 items-center gap-2 sm:flex-col sm:items-end">
                            <x-prism.status-badge :status="$update['status']" />
                            <small>{{ $update['time'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] xl:col-span-2 2xl:col-span-1">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Next actions</p>
                    <h2>PR Readiness</h2>
                </div>
                <x-prism.status-badge status="Pending">{{ $summary['pendingItems'] }} pending</x-prism.status-badge>
            </div>
            <div class="grid gap-3">
                <a class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-bsu-gold/50 hover:bg-white hover:shadow-sm" href="{{ route('office-head.budget-proposal') }}">
                    <span class="block text-sm font-extrabold text-slate-950">Prepare budget proposal</span>
                    <small class="mt-1 block text-sm leading-6 text-slate-600">Encode items and run market scoping before submission.</small>
                </a>
                <a class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-bsu-gold/50 hover:bg-white hover:shadow-sm" href="{{ route('office-head.my-proposals') }}">
                    <span class="block text-sm font-extrabold text-slate-950">Review proposal status</span>
                    <small class="mt-1 block text-sm leading-6 text-slate-600">Check Finance and Chancellor remarks for returned items.</small>
                </a>
                <a class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-bsu-gold/50 hover:bg-white hover:shadow-sm" href="{{ route('office-head.purchase-requests') }}">
                    <span class="block text-sm font-extrabold text-slate-950">Upload signed PR</span>
                    <small class="mt-1 block text-sm leading-6 text-slate-600">Attach PR PDFs for approved items ready for processing.</small>
                </a>
            </div>
        </article>
    </section>
@endsection

