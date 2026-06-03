@extends('prism.layout')

@php
    $totalAppItems = collect($performanceRows)->sum('totalAppItems');
    $totalProcured = collect($performanceRows)->sum('procured');
    $totalPending = collect($performanceRows)->sum('pending');
    $averageUtilization = round(collect($performanceRows)->avg('utilization'));
    $statCardClass = 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500';
@endphp

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Vice Chancellor</p>
            <h1>Division Performance Report</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Compare utilization rates, APP item accomplishment, pending items, and performance extremes within the division.</p>
        </div>
        <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button" id="printViceReportButton">
            <i data-lucide="printer" aria-hidden="true"></i>
            Export to PDF or Print
        </button>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Division performance summary">
        <article class="{{ $statCardClass }}">
            <span>Total APP items</span>
            <strong>{{ number_format($totalAppItems) }}</strong>
            <small>Division-wide approved procurement items</small>
        </article>
        <article class="{{ $statCardClass }}">
            <span>Procured</span>
            <strong>{{ number_format($totalProcured) }}</strong>
            <small>Completed items across the division</small>
        </article>
        <article class="{{ $statCardClass }}">
            <span>Pending</span>
            <strong>{{ number_format($totalPending) }}</strong>
            <small>Items requiring follow-up or completion</small>
        </article>
        <article class="{{ $statCardClass }}">
            <span>Average utilization</span>
            <strong>{{ $averageUtilization }}%</strong>
            <small>Mean utilization rate across offices</small>
        </article>
    </section>

    <section class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <article class="relative overflow-hidden rounded-2xl border border-green-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-green-500 after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-green-200 after:bg-green-50 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-green-700 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-slate-950 [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Best-performing office</span>
            <strong>{{ $bestOffice }}</strong>
            <small>Highest utilization and procurement accomplishment in the division</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-red-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-red-500 after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-red-200 after:bg-red-50 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-red-700 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-slate-950 [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Lowest-performing office</span>
            <strong>{{ $lowestOffice }}</strong>
            <small>Needs follow-up due to lower utilization and pending procurement items</small>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Office utilization rates</p>
                <h2>Division Performance Summary</h2>
            </div>
        </div>
        <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
            <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                <thead>
                    <tr>
                        <th>Office name</th>
                        <th>Total APP items</th>
                        <th>Procured</th>
                        <th>Pending</th>
                        <th>Utilization</th>
                        <th>Highlight</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($performanceRows as $row)
                        <tr class="{{ $row['performance'] === 'best' ? 'bg-green-50' : ($row['performance'] === 'lowest' ? 'bg-red-50' : '') }}">
                            <td>{{ $row['office'] }}</td>
                            <td>{{ $row['totalAppItems'] }}</td>
                            <td>{{ $row['procured'] }}</td>
                            <td>{{ $row['pending'] }}</td>
                            <td>
                                <div class="min-w-36 space-y-2 [&>span]:text-sm [&>span]:font-bold [&>span]:text-slate-700">
                                    <span>{{ $row['utilization'] }}%</span>
                                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 ring-1 ring-inset ring-slate-200 [&>span]:block [&>span]:h-full [&>span]:rounded-full [&>span]:bg-bsu-maroon">
                                        <span style="width: {{ $row['utilization'] }}%"></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($row['performance'] === 'best')
                                    <x-prism.status-badge status="On Track">Best-performing</x-prism.status-badge>
                                @elseif ($row['performance'] === 'lowest')
                                    <x-prism.status-badge status="Critical">Lowest-performing</x-prism.status-badge>
                                @else
                                    <span class="inline-flex min-h-7 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">Steady</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection

