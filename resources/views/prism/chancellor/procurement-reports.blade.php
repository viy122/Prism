@extends('prism.layout')

@php
    $totalTargeted = collect($accomplishmentRows)->sum('targeted');
    $totalProcured = collect($accomplishmentRows)->sum('procured');
    $campusCompletion = $totalTargeted > 0 ? round(($totalProcured / $totalTargeted) * 100) : 0;
    $delayedCount = collect($delayedByOffice)->flatten(1)->count();
    $statCardClass = 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500';
@endphp

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Chancellor</p>
            <h1>Procurement Reports</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Review campus-wide procurement accomplishment, year-end utilization, and delayed items grouped by office.</p>
        </div>
        <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" type="button" id="printChancellorReportButton">
            <i data-lucide="printer" aria-hidden="true"></i>
            Export to PDF or Print
        </button>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Chancellor procurement report summary">
        <article class="{{ $statCardClass }}">
            <span>Campus targets</span>
            <strong>{{ number_format($totalTargeted) }}</strong>
            <small>Procurement targets across monitored offices</small>
        </article>
        <article class="{{ $statCardClass }}">
            <span>Items procured</span>
            <strong>{{ number_format($totalProcured) }}</strong>
            <small>Completed procurement items across campus</small>
        </article>
        <article class="{{ $statCardClass }}">
            <span>Completion rate</span>
            <strong>{{ $campusCompletion }}%</strong>
            <small>Campus-wide procurement accomplishment</small>
        </article>
        <article class="{{ $statCardClass }}">
            <span>Delayed items</span>
            <strong>{{ $delayedCount }}</strong>
            <small>Risk items grouped by office for follow-up</small>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Campus-wide accomplishment</p>
                <h2>Items Targeted vs Procured per Office</h2>
            </div>
            <span class="inline-flex min-h-7 items-center rounded-full bg-bsu-maroon/5 px-3 py-1 text-xs font-bold text-bsu-maroon ring-1 ring-inset ring-bsu-maroon/10">{{ $campusCompletion }}% campus completion</span>
        </div>
        <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
            <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Items targeted</th>
                        <th>Items procured</th>
                        <th>Completion rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accomplishmentRows as $row)
                        <tr>
                            <td>{{ $row['office'] }}</td>
                            <td>{{ $row['targeted'] }}</td>
                            <td>{{ $row['procured'] }}</td>
                            <td>
                                <div class="min-w-36 space-y-2 [&>span]:text-sm [&>span]:font-bold [&>span]:text-slate-700">
                                    <span>{{ $row['completionRate'] }}%</span>
                                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 ring-1 ring-inset ring-slate-200 [&>span]:block [&>span]:h-full [&>span]:rounded-full [&>span]:bg-bsu-maroon">
                                        <span style="width: {{ $row['completionRate'] }}%"></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Year-end budget utilization</p>
                    <h2>Summary by Office</h2>
                </div>
            </div>
            <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
                <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Budget</th>
                            <th>Utilized</th>
                            <th>Forecast</th>
                            <th>Risk flag</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($utilizationSummary as $row)
                            <tr>
                                <td>{{ $row['office'] }}</td>
                                <td>PHP {{ number_format($row['budget']) }}</td>
                                <td>PHP {{ number_format($row['utilized']) }}</td>
                                <td>{{ $row['forecast'] }}%</td>
                                <td><x-prism.status-badge :status="$row['risk']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Delayed items grouped by office</p>
                    <h2>Delay Reasons</h2>
                </div>
            </div>
            <div class="grid gap-2">
                @foreach ($delayedByOffice as $office => $items)
                    <div class="flex flex-col gap-3 rounded-2xl border border-red-200 bg-red-50/70 p-4 transition hover:bg-white hover:shadow-sm sm:flex-row sm:items-start sm:justify-between [&_strong]:block [&_strong]:text-sm [&_strong]:font-bold [&_strong]:text-slate-950 [&_span]:mt-1 [&_span]:block [&_span]:text-sm [&_span]:leading-6 [&_span]:text-slate-600">
                        <div>
                            <strong>{{ $office }}</strong>
                            @foreach ($items as $item)
                                <span>{{ $item['item'] }} - {{ $item['prNumber'] }}: {{ $item['remarks'] }}</span>
                            @endforeach
                        </div>
                        <x-prism.status-badge status="Delayed">{{ count($items) }} delayed</x-prism.status-badge>
                    </div>
                @endforeach
            </div>
        </article>
    </section>
@endsection

