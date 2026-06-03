@extends('prism.layout')

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Finance Office</p>
            <h1>Dashboard</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Monitor proposal review workload, office submission status, and the campus-wide proposed budget.</p>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Finance dashboard totals">
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Awaiting review</span>
            <strong>{{ number_format($summary['awaitingReview']) }}</strong>
            <small>Submitted proposals pending Finance action</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Endorsed this month</span>
            <strong>{{ number_format($summary['endorsedThisMonth']) }}</strong>
            <small>Forwarded for Chancellor approval</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Returned</span>
            <strong>{{ number_format($summary['returned']) }}</strong>
            <small>Returned to offices with remarks</small>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:border-bsu-gold/50 hover:shadow-[0_12px_28px_rgba(15,23,42,0.07)] before:absolute before:left-0 before:top-5 before:h-10 before:w-1 before:rounded-r-full before:bg-bsu-gold after:absolute after:right-4 after:top-4 after:h-9 after:w-9 after:rounded-xl after:border after:border-bsu-maroon/10 after:bg-bsu-maroon/5 [&>span]:relative [&>span]:text-xs [&>span]:font-extrabold [&>span]:uppercase [&>span]:tracking-[0.07em] [&>span]:text-slate-500 [&>strong]:relative [&>strong]:mt-3 [&>strong]:block [&>strong]:text-[1.55rem] 2xl:[&>strong]:text-[1.8rem] [&>strong]:font-extrabold [&>strong]:tracking-tight [&>strong]:text-bsu-maroon [&>small]:relative [&>small]:mt-2 [&>small]:block [&>small]:text-sm [&>small]:leading-6 [&>small]:text-slate-500">
            <span>Total proposed budget</span>
            <strong>PHP {{ number_format($summary['totalCampusBudget']) }}</strong>
            <small>Campus-wide total across active submissions</small>
        </article>
    </section>

    <section class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Grouped by office</p>
                    <h2>Proposals by Status</h2>
                </div>
            </div>
            <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
                <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Pending</th>
                            <th>Endorsed</th>
                            <th>Returned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($officeStatusGroups as $office)
                            <tr>
                                <td>{{ $office['office'] }}</td>
                                <td><x-prism.status-badge status="Pending">{{ $office['pending'] }}</x-prism.status-badge></td>
                                <td><x-prism.status-badge status="Endorsed">{{ $office['endorsed'] }}</x-prism.status-badge></td>
                                <td><x-prism.status-badge status="Returned">{{ $office['returned'] }}</x-prism.status-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Recent submissions</p>
                    <h2>Review Queue</h2>
                </div>
            </div>
            <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
                <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                    <thead>
                        <tr>
                            <th>Office name</th>
                            <th>Submitted date</th>
                            <th>Total amount</th>
                            <th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSubmissions as $submission)
                            <tr>
                                <td>{{ $submission['office'] }}</td>
                                <td>{{ $submission['submittedDate'] }}</td>
                                <td>PHP {{ number_format($submission['totalAmount']) }}</td>
                                <td>
                                    <a class="inline-flex h-9 items-center rounded-xl border border-bsu-maroon/35 bg-white px-3 text-sm font-bold text-bsu-maroon shadow-sm transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" href="{{ route('finance-office.proposal-review.show', ['proposal' => $submission['proposalId']]) }}">Review</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection

