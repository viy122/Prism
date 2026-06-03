@extends('prism.layout')

@php
    $items = collect($purchaseItems);
    $totalApproved = $items->sum('approvedAmount');
    $pendingCount = $items->where('prStatus', 'Pending')->count();
    $inProgressCount = $items->where('prStatus', 'In Progress')->count();
    $delayedCount = $items->where('prStatus', 'Delayed')->count();
    $readyCount = $items->filter(fn ($item) => in_array($item['procurementStatus'], ['Ready for PR', 'Awaiting PR'], true))->count();
@endphp

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-end lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Office Head / Dean</p>
            <h1>Purchase Requests</h1>
            <p class="mt-2 max-w-4xl text-base leading-7 text-slate-600">Upload signed PR PDFs for approved items and monitor procurement remarks and PR status.</p>
        </div>
        <div class="flex flex-wrap gap-2.5 lg:min-w-max lg:justify-end">
            <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button">
                <i data-lucide="upload-cloud" aria-hidden="true"></i>
                Batch Upload
            </button>
            <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-bsu-maroon/35 bg-white px-4 text-sm font-bold text-bsu-maroon shadow-sm transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button">
                <i data-lucide="printer" aria-hidden="true"></i>
                Export Queue
            </button>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Purchase request summary">
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5">
            <span class="relative flex h-11 w-11 items-center justify-center rounded-lg bg-red-50 text-bsu-maroon ring-1 ring-red-100">
                <i class="h-5 w-5" data-lucide="receipt-text" aria-hidden="true"></i>
            </span>
            <p class="relative mt-4 text-sm font-semibold text-slate-500">Approved queue</p>
            <strong class="relative mt-1 block text-3xl font-extrabold text-slate-950">{{ number_format($items->count()) }}</strong>
            <small class="relative mt-2 block text-sm font-semibold text-emerald-600">{{ $readyCount }} ready for PR upload</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5">
            <span class="relative flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                <i class="h-5 w-5" data-lucide="clipboard-check" aria-hidden="true"></i>
            </span>
            <p class="relative mt-4 text-sm font-semibold text-slate-500">Approved amount</p>
            <strong class="relative mt-1 block text-3xl font-extrabold text-slate-950">PHP {{ number_format($totalApproved) }}</strong>
            <small class="relative mt-2 block text-sm font-semibold text-slate-500">Across active PR items</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5">
            <span class="relative flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                <i class="h-5 w-5" data-lucide="history" aria-hidden="true"></i>
            </span>
            <p class="relative mt-4 text-sm font-semibold text-slate-500">Pending upload</p>
            <strong class="relative mt-1 block text-3xl font-extrabold text-slate-950">{{ number_format($pendingCount) }}</strong>
            <small class="relative mt-2 block text-sm font-semibold text-amber-700">Needs signed PDF attachment</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5">
            <span class="relative flex h-11 w-11 items-center justify-center rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-100">
                <i class="h-5 w-5" data-lucide="refresh-cw" aria-hidden="true"></i>
            </span>
            <p class="relative mt-4 text-sm font-semibold text-slate-500">Needs attention</p>
            <strong class="relative mt-1 block text-3xl font-extrabold text-slate-950">{{ number_format($delayedCount) }}</strong>
            <small class="relative mt-2 block text-sm font-semibold text-rose-700">{{ $inProgressCount }} currently in progress</small>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Approved items eligible for PR</p>
                    <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">PR Upload Queue</h2>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <label class="relative min-w-[240px]">
                        <span class="sr-only">Search queue</span>
                        <i class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" data-lucide="search" aria-hidden="true"></i>
                        <input class="h-10 w-full rounded-full border border-slate-200 bg-slate-50 pl-9 pr-4 text-sm font-semibold text-slate-700 placeholder:text-slate-400" type="search" placeholder="Search item or remarks">
                    </label>
                    <span class="inline-flex h-10 items-center justify-center rounded-full bg-slate-100 px-4 text-xs font-extrabold text-slate-700 ring-1 ring-inset ring-slate-200">{{ count($purchaseItems) }} approved items</span>
                </div>
            </div>

            <div class="max-h-[calc(100vh-22rem)] min-h-[420px] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
                <table class="min-w-[1080px] border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-4 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                    <thead>
                        <tr>
                            <th>Item name</th>
                            <th>Approved amount</th>
                            <th>Target quarter</th>
                            <th>Procurement status</th>
                            <th>Signed PR PDF</th>
                            <th>PR status</th>
                            <th>Procurement Office remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseItems as $item)
                            <tr>
                                <td>
                                    <strong class="block max-w-[18rem] text-sm font-extrabold leading-5 text-slate-950">{{ $item['itemName'] }}</strong>
                                    <span class="mt-1 block text-xs font-semibold text-slate-500">{{ $item['id'] }}</span>
                                </td>
                                <td class="font-bold text-slate-950">PHP {{ number_format($item['approvedAmount']) }}</td>
                                <td>{{ $item['targetQuarter'] }}</td>
                                <td>{{ $item['procurementStatus'] }}</td>
                                <td>
                                    <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border border-dashed border-bsu-maroon/40 bg-bsu-maroon/5 px-4 py-2.5 text-sm font-bold text-bsu-maroon transition hover:bg-bsu-maroon/10 hover:shadow-sm [&_input]:hidden">
                                        <i class="h-4 w-4" data-lucide="upload-cloud" aria-hidden="true"></i>
                                        <span class="max-w-[11rem] truncate" data-upload-label="{{ $item['id'] }}">Upload PDF</span>
                                        <input type="file" accept="application/pdf,.pdf" data-pr-upload="{{ $item['id'] }}">
                                    </label>
                                </td>
                                <td><x-prism.status-badge :status="$item['prStatus']" data-pr-status="{{ $item['id'] }}" /></td>
                                <td class="max-w-[24rem] leading-6">{{ $item['remarks'] }}</td>
                            </tr>
                            <tr class="bg-slate-50" data-ocr-row="{{ $item['id'] }}" hidden>
                                <td colspan="7">
                                    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[240px_minmax(0,1fr)] [&_h3]:mt-1.5 [&_h3]:text-base [&_h3]:font-extrabold [&_h3]:text-slate-950 [&_dl]:grid [&_dl]:gap-3 [&_dl]:sm:grid-cols-2 [&_dt]:text-xs [&_dt]:font-extrabold [&_dt]:uppercase [&_dt]:tracking-[0.08em] [&_dt]:text-slate-500 [&_dd]:mt-1 [&_dd]:text-sm [&_dd]:font-bold [&_dd]:text-slate-950">
                                        <div>
                                            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">OCR extracted details</p>
                                            <h3 data-ocr-file="{{ $item['id'] }}">Signed PR PDF</h3>
                                        </div>
                                        <dl>
                                            <div>
                                                <dt>PR number</dt>
                                                <dd data-ocr-pr-number="{{ $item['id'] }}"></dd>
                                            </div>
                                            <div>
                                                <dt>Date</dt>
                                                <dd data-ocr-date="{{ $item['id'] }}"></dd>
                                            </div>
                                            <div>
                                                <dt>Items</dt>
                                                <dd data-ocr-items="{{ $item['id'] }}"></dd>
                                            </div>
                                            <div>
                                                <dt>Amount</dt>
                                                <dd data-ocr-amount="{{ $item['id'] }}"></dd>
                                            </div>
                                        </dl>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="grid gap-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Processing snapshot</p>
                <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">Queue Health</h2>
                <div class="mt-5 grid gap-3">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <span class="text-sm font-bold text-slate-600">Pending</span>
                        <x-prism.status-badge status="Pending">{{ $pendingCount }}</x-prism.status-badge>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <span class="text-sm font-bold text-slate-600">In progress</span>
                        <x-prism.status-badge status="In Progress">{{ $inProgressCount }}</x-prism.status-badge>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-slate-600">Delayed</span>
                        <x-prism.status-badge status="Delayed">{{ $delayedCount }}</x-prism.status-badge>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Upload checklist</p>
                <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">Before Sending</h2>
                <ul class="mt-5 grid gap-3 text-sm leading-6 text-slate-700">
                    <li class="flex gap-3">
                        <i class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" data-lucide="check-circle-2" aria-hidden="true"></i>
                        Signed PR PDF is clear and readable.
                    </li>
                    <li class="flex gap-3">
                        <i class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" data-lucide="check-circle-2" aria-hidden="true"></i>
                        Approved item list is attached.
                    </li>
                    <li class="flex gap-3">
                        <i class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" data-lucide="check-circle-2" aria-hidden="true"></i>
                        Amount and PR number match the approved record.
                    </li>
                </ul>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Recent remarks</p>
                <h2 class="mt-1.5 text-xl font-extrabold text-slate-950">Procurement Notes</h2>
                <div class="mt-5 grid gap-4">
                    @foreach ($items->take(3) as $item)
                        <div class="border-l-2 border-bsu-gold pl-3">
                            <strong class="block text-sm font-extrabold text-slate-950">{{ $item['itemName'] }}</strong>
                            <span class="mt-1 block text-sm leading-6 text-slate-600">{{ $item['remarks'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
        </aside>
    </section>

    <script type="application/json" id="purchaseRequestData">@json($purchaseItems)</script>
@endsection
