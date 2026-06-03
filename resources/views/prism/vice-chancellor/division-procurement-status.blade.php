@extends('prism.layout')

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Vice Chancellor</p>
            <h1>Division Procurement Status</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Review APP items under the assigned division, inspect activity timelines, and add follow-up notes for offices.</p>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)] grid gap-4 sm:grid-cols-2 xl:grid-cols-3 [&_label]:block [&_label>span]:mb-1.5 [&_label>span]:block [&_label>span]:text-sm [&_label>span]:font-bold [&_label>span]:text-slate-700 [&_input]:h-11 [&_input]:w-full [&_input]:rounded-xl [&_input]:border [&_input]:border-slate-300 [&_input]:bg-white [&_input]:px-4 [&_input]:text-base [&_input]:text-slate-800 [&_select]:h-11 [&_select]:w-full [&_select]:rounded-xl [&_select]:border [&_select]:border-slate-300 [&_select]:bg-white [&_select]:px-4 [&_select]:text-base [&_select]:text-slate-800 [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:px-4 [&_textarea]:py-3 [&_textarea]:text-base [&_textarea]:text-slate-800 [&_input:focus]:border-bsu-maroon [&_input:focus]:outline-none [&_input:focus]:ring-2 [&_input:focus]:ring-bsu-gold/40 [&_select:focus]:border-bsu-maroon [&_select:focus]:outline-none [&_select:focus]:ring-2 [&_select:focus]:ring-bsu-gold/40 [&_textarea:focus]:border-bsu-maroon [&_textarea:focus]:outline-none [&_textarea:focus]:ring-2 [&_textarea:focus]:ring-bsu-gold/40" aria-label="Division procurement filters">
        <label>
            <span>Office</span>
            <select id="viceStatusOfficeFilter">
                <option value="all">All offices</option>
                @foreach ($offices as $office)
                    <option value="{{ $office }}">{{ $office }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>Quarter</span>
            <select id="viceStatusQuarterFilter">
                <option value="all">All quarters</option>
                @foreach ($quarters as $quarter)
                    <option value="{{ $quarter }}">{{ $quarter }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>Status</span>
            <select id="viceStatusStatusFilter">
                <option value="all">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
        </label>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(400px,0.8fr)]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Grouped by office</p>
                    <h2>Division APP Items</h2>
                </div>
                <span class="inline-flex min-h-7 items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200" id="viceStatusVisibleCount">{{ count($divisionItems) }} shown</span>
            </div>
            <div class="max-h-[64vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-inner shadow-slate-100">
                <table class="min-w-full border-separate border-spacing-0 text-left text-sm text-slate-700 [&_th]:sticky [&_th]:top-0 [&_th]:z-10 [&_th]:border-b [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.08em] [&_th]:text-slate-500 [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5 [&_td]:align-top [&_tbody_tr]:transition [&_tbody_tr:hover]:bg-bsu-maroon/5">
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Item</th>
                            <th>Target quarter</th>
                            <th>Current status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($divisionItems as $item)
                            <tr class="{{ $item['currentStatus'] === 'Delayed' ? 'bg-red-50' : '' }}" data-vice-status-row data-item-id="{{ $item['id'] }}" data-office="{{ $item['office'] }}" data-quarter="{{ $item['targetQuarter'] }}" data-status="{{ $item['currentStatus'] }}" tabindex="0">
                                <td>{{ $item['office'] }}</td>
                                <td>{{ $item['item'] }}</td>
                                <td>{{ $item['targetQuarter'] }}</td>
                                <td><x-prism.status-badge :status="$item['currentStatus']" /></td>
                                <td>{{ $item['remarks'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.05)]" aria-live="polite">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between [&_h2]:mt-1.5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-950">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">Procurement activity timeline</p>
                    <h2 id="viceStatusTitle">Select an item</h2>
                </div>
            </div>
            <div id="viceStatusDetails" class="flex min-h-52 flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-base leading-7 text-slate-500 [&_svg]:h-10 [&_svg]:w-10 [&_svg]:text-bsu-maroon/70">
                <i data-lucide="mouse-pointer-click" aria-hidden="true"></i>
                <p>Click an item to view Procurement Office remarks, activity timeline, and Vice Chancellor follow-up notes.</p>
            </div>
        </aside>
    </section>

    <script type="application/json" id="viceDivisionItemData">@json($divisionItems)</script>
@endsection

