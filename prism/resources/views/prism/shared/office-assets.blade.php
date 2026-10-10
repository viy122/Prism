@extends($assetLayout)
@section('title', $pageTitle)
@include('prism.shared.office-asset-styles')
@section('content')
<main class="asset-page">
    <header class="asset-page-header">
        <div><p class="asset-eyebrow">{{ $roleLabel }} · Equipment monitoring</p><h1>{{ $pageTitle }}</h1>
        <p class="asset-subtitle">Monitor equipment assignments, actual use and warranty coverage across acquisition years.</p></div>
        @if($assetPageRole === 'office-head')<a class="asset-button" href="{{ route('office-head.office-assets.received') }}"><i class="ti ti-receipt" aria-hidden="true"></i> Received Items</a>@endif
    </header>
    @if($assetPageRole !== 'office-head')
        <p class="receiving-note">Current asset monitoring. Finalized procurement report snapshots remain unchanged.</p>
    @endif
    @if(session('receiving_success'))<p class="receiving-message" role="status">{{ session('receiving_success') }}</p>@endif
    @if($errors->any())<div class="receiving-message error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @php($assetStatIcons = ['Registered Units' => 'devices', 'Unassigned' => 'user-question', 'In Use' => 'circle-check', 'Under Repair' => 'tool', 'Warranty Expiring Soon' => 'shield-exclamation'])
    <div class="asset-summary" aria-label="Equipment summary">
        @foreach($summary as $label => $count)
        <div class="asset-stat-wrap">
            <button type="button" class="asset-stat" aria-label="View {{ $label }} breakdown" aria-expanded="false" aria-controls="assetKpi{{ $loop->index }}">
                <span class="asset-stat-icon"><i class="ti ti-{{ $assetStatIcons[$label] }}" aria-hidden="true"></i></span><span class="asset-stat-label">{{ $label }}</span><strong>{{ number_format($count) }}</strong><span class="asset-stat-hint">{{ $label === 'Warranty Expiring Soon' ? 'Within the next 30 days' : 'Matching current filters' }}</span>
            </button>
            <section class="asset-kpi-popover" id="assetKpi{{ $loop->index }}" aria-label="{{ $label }} breakdown" hidden>
                <p class="asset-eyebrow">Equipment breakdown</p><h3>{{ $label }}</h3>
                <p class="asset-kpi-lead">{{ number_format($count) }} units matching current filters. Each record below represents one registered unit.</p>
                <div class="asset-kpi-rows">
                @forelse($summaryDetails[$label] as $unit)
                    <a class="asset-kpi-row" href="{{ route('office-assets.show', $unit['id']) }}">
                        <span class="asset-kpi-name">{{ $unit['item'] }}</span>
                        <span>{{ $unit['reference'] }}</span>
                        <span>{{ $unit['office'] }} · FY {{ $unit['year'] }} · {{ $unit['source'] }}</span>
                        <span>{{ $unit['location'] }} · {{ $unit['person'] }}</span>
                        @if($label === 'Warranty Expiring Soon')<span>Warranty expires {{ $unit['warranty'] }}</span>@endif
                    </a>
                @empty
                    <p class="asset-kpi-empty">No units match this category and the current filters.</p>
                @endforelse
                </div>
                @if($count > count($summaryDetails[$label]))<p class="asset-kpi-lead">Showing the latest {{ count($summaryDetails[$label]) }} of {{ number_format($count) }} units. Use the equipment register below for the full list.</p>@endif
            </section>
        </div>
        @endforeach
    </div>
    <section class="asset-card asset-filter-card" aria-labelledby="assetFilterTitle">
    <div class="asset-card-head"><div><p class="asset-eyebrow">Find equipment</p><h2 id="assetFilterTitle">Search &amp; filters</h2></div><a class="asset-text-action" href="{{ route($assetPageRole.'.office-assets') }}"><i class="ti ti-refresh" aria-hidden="true"></i> Reset filters</a></div>
    <form method="GET" class="receiving-form asset-filters" aria-label="Asset filters">
        <label class="asset-search">Search equipment<input name="search" value="{{ request('search') }}" maxlength="255" placeholder="Item, reference, serial, room or person"></label>
        <label>Office<select name="office"><option value="">All accessible offices</option>@foreach($offices as $office)<option value="{{ $office->id }}" @selected(request('office') == $office->id)>{{ $office->code }}</option>@endforeach</select></label>
        <label>Acquisition FY<select name="acquisition_year"><option value="">All years</option>@foreach($years as $year)<option value="{{ $year }}" @selected(request('acquisition_year') == $year)>{{ $year }}</option>@endforeach</select></label>
        <label>Allocation<select name="allocation"><option value="">All</option><option value="assigned" @selected(request('allocation') === 'assigned')>Assigned</option><option value="unassigned" @selected(request('allocation') === 'unassigned')>Unassigned</option></select></label>
        <label>Usage<select name="usage"><option value="">All</option>@foreach(\App\Models\OfficeAsset::USAGE as $value => $label)<option value="{{ $value }}" @selected(request('usage') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Warranty<select name="warranty"><option value="">All</option>@foreach(['Not Yet Recorded', 'No Warranty', 'Not Yet Active', 'Under Warranty', 'Expiring Soon', 'Warranty Expired'] as $status)<option @selected(request('warranty') === $status)>{{ $status }}</option>@endforeach</select></label>
        @if(request('item'))<input type="hidden" name="item" value="{{ request('item') }}">@endif
        @if(request('registration'))<input type="hidden" name="registration" value="{{ request('registration') }}">@endif
        <button type="submit"><i class="ti ti-filter" aria-hidden="true"></i> Apply filters</button>
    </form>
    </section>
    @if($incoming->isNotEmpty())
    <section class="asset-card"><div class="asset-card-head"><div><p class="asset-eyebrow">For your action</p><h2>Incoming office transfers</h2></div><span class="asset-count">{{ $incoming->count() }} pending</span></div>
        @foreach($incoming as $transfer)
        <div class="receiving-history"><strong>{{ $transfer->asset->reference }} — {{ $transfer->asset->receipt?->item?->name }}</strong><p>From {{ $transfer->fromOffice?->code }}: {{ $transfer->reason }}</p>
            <form class="receiving-form" method="POST" action="{{ route('office-assets.resolve-transfer', $transfer) }}">@csrf
                <label>Decision<select name="decision"><option value="accepted">Accept receipt into my office</option><option value="rejected">Reject transfer</option></select></label>
                <label>Reason / receipt confirmation<textarea name="reason" required maxlength="1000"></textarea></label><button type="submit">Confirm decision</button>
            </form>
        </div>
        @endforeach
    </section>
    @endif
    <section class="asset-card"><div class="asset-card-head"><div><p class="asset-eyebrow">Allocation &amp; warranty</p><h2>Equipment register</h2><p class="receiving-note">Select units on this page to update assignment or warranty details together.</p>@if(request('registration'))<p class="receiving-note">Showing the units from this registration. <a href="{{ route($assetPageRole.'.office-assets') }}">View all registered units</a></p>@endif</div><span class="asset-count">{{ $assets->total() }} matching units</span></div>
        <div class="receiving-scroll"><table class="receiving-table"><thead><tr><th>Select</th><th>Asset / Item</th><th>Office / FY</th><th>Location / Person</th><th>Usage</th><th>Warranty</th><th>Actions</th></tr></thead><tbody>
        @forelse($assets as $asset)
            <tr>
                <td>@if($assetService->canManage(auth()->user(), $asset->office_id) && $asset->transfers->isEmpty())<input class="asset-check" type="checkbox" data-asset-select value="{{ $asset->id }}" data-version="{{ $asset->version }}" aria-label="Select {{ $asset->reference }}">@endif</td>
                <td><strong>{{ $asset->receipt?->item?->name }}</strong><small>{{ $asset->reference }}</small><small>Serial: {{ $asset->serial_number ?: 'Not recorded' }}</small><small>Property: {{ $asset->property_number ?: 'Not recorded' }}</small></td>
                <td>{{ $asset->office?->code }}<small>FY {{ $asset->receipt?->item?->purchaseRequest?->fiscal_year }}</small></td>
                <td>{{ $asset->location ?: 'Unassigned' }}<small>{{ $asset->accountable_person ?: 'No accountable person' }}</small>@if($asset->transfers->isNotEmpty())<small>Office transfer pending</small>@endif</td>
                <td><span class="asset-pill" data-status="{{ \App\Models\OfficeAsset::USAGE[$asset->usage_status] }}">{{ \App\Models\OfficeAsset::USAGE[$asset->usage_status] }}</span></td>
                <td><span class="asset-pill" data-status="{{ $asset->warrantyStatus() }}">{{ $asset->warrantyStatus() }}</span><small>{{ $asset->warranty_end ? 'Expires '.$asset->warranty_end->toDateString() : '' }}</small></td>
                <td><a class="asset-button asset-button-small" href="{{ route('office-assets.show', $asset) }}"><i class="ti ti-eye" aria-hidden="true"></i> View / {{ $assetService->canManage(auth()->user(), $asset->office_id) ? 'Update' : 'History' }}</a></td>
            </tr>
        @empty<tr><td colspan="7" class="asset-empty"><i class="ti ti-devices" aria-hidden="true"></i><strong>No registered equipment matches these filters.</strong><p>@if($assetPageRole === 'office-head') Open <a href="{{ route('office-head.office-assets.received') }}">Received Items</a> to register equipment for allocation.@else Record an actual receipt and register its equipment units to begin.@endif</p></td></tr>@endforelse
        </tbody></table></div>
        <div class="asset-pagination"><span>Page {{ $assets->currentPage() }} of {{ $assets->lastPage() }}</span><div class="asset-actions">@if($assets->previousPageUrl())<a href="{{ $assets->previousPageUrl() }}">Previous</a>@endif @if($assets->nextPageUrl())<a href="{{ $assets->nextPageUrl() }}">Next</a>@endif</div></div>
        @if($assetPageRole === 'office-head')
        <div class="asset-bulk-heading"><i class="ti ti-list-check" aria-hidden="true"></i><p id="assetSelectionCount" role="status">0 units selected</p></div>
        @foreach(['allocation' => 'Assign / update selected units', 'warranty' => 'Set warranty for selected units'] as $assetSection => $label)
            <details class="asset-disclosure"><summary>{{ $label }}<i class="ti ti-chevron-down" aria-hidden="true"></i></summary><form data-asset-bulk class="receiving-form" method="POST" enctype="multipart/form-data" action="{{ route('office-assets.update') }}">@csrf<input type="hidden" name="section" value="{{ $assetSection }}"><div data-asset-inputs hidden></div>
                @include('prism.shared.office-asset-fields', ['editingAsset' => null])
                <button type="submit" disabled>Save selected units</button>
            </form></details>
        @endforeach
        @endif
    </section>
</main>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.asset-stat-wrap').forEach(wrap => {
    const button = wrap.querySelector('.asset-stat'), panel = wrap.querySelector('.asset-kpi-popover');
    let pinned = false;
    const show = () => {
        panel.hidden = false; button.setAttribute('aria-expanded', 'true');
        if (window.innerWidth > 600) {
            panel.style.maxHeight = Math.max(100, window.innerHeight - panel.getBoundingClientRect().top - 16) + 'px';
            panel.style.overflowY = 'auto';
        }
    };
    const hide = () => { panel.hidden = true; button.setAttribute('aria-expanded', 'false'); };
    wrap.addEventListener('mouseenter', show);
    wrap.addEventListener('mouseleave', () => { if (!pinned && !wrap.contains(document.activeElement)) hide(); });
    wrap.addEventListener('focusin', show);
    wrap.addEventListener('focusout', event => { if (!wrap.contains(event.relatedTarget)) { pinned = false; hide(); } });
    button.addEventListener('click', () => { pinned = !pinned; pinned ? show() : hide(); });
    wrap.addEventListener('keydown', event => { if (event.key === 'Escape') { pinned = false; hide(); button.focus(); } });
    document.addEventListener('click', event => { if (!wrap.contains(event.target)) { pinned = false; hide(); } });
});
document.addEventListener('change', event => {
    if (!event.target.matches('[data-asset-select]')) return;
    const selected = [...document.querySelectorAll('[data-asset-select]:checked')];
    const count = document.getElementById('assetSelectionCount');
    if (count) count.textContent = selected.length + ' units selected';
    document.querySelectorAll('[data-asset-bulk]').forEach(form => {
        const inputs = form.querySelector('[data-asset-inputs]'); inputs.replaceChildren();
        selected.forEach(check => {
            for (const [name, value] of [['asset_ids[]', check.value], ['versions[' + check.value + ']', check.dataset.version]]) {
                const input = document.createElement('input'); input.type = 'hidden'; input.name = name; input.value = value; inputs.append(input);
            }
        });
        form.querySelector('button[type="submit"]').disabled = !selected.length;
    });
});
</script>
@endpush
