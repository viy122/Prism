@extends('prism.layout')

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Chancellor</p>
            <h1>Budget Approval</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Review proposals endorsed by Finance Office, inspect item details and market scoping, then approve or return with required remarks.</p>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(400px,0.8fr)]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Finance-endorsed proposals</p>
                    <h2>Approval Queue</h2>
                </div>
                <x-prism.status-badge status="Endorsed">{{ count($proposals) }} endorsed</x-prism.status-badge>
            </div>
            <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
                <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Total amount</th>
                            <th>Date endorsed</th>
                            <th>Finance remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proposals as $proposal)
                            <tr data-chancellor-proposal-row data-proposal-id="{{ $proposal['id'] }}" tabindex="0">
                                <td>{{ $proposal['office'] }}</td>
                                <td>PHP {{ number_format($proposal['totalAmount']) }}</td>
                                <td>{{ $proposal['dateEndorsed'] }}</td>
                                <td>{{ $proposal['financeRemarks'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]" aria-live="polite">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Full proposal details</p>
                    <h2 id="chancellorProposalTitle">Select a proposal</h2>
                </div>
            </div>
            <div id="chancellorProposalDetails" class="flex min-h-52 flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-base leading-7 text-slate-500 [&_svg]:h-10 [&_svg]:w-10 [&_svg]:text-bsu-maroon/70">
                <i data-lucide="mouse-pointer-click" aria-hidden="true"></i>
                <p>Click a proposal row to view items, costs, justifications, market scoping, Finance remarks, and approval trail.</p>
            </div>
        </aside>
    </section>

    <script type="application/json" id="chancellorProposalData">@json($proposals)</script>
@endsection

