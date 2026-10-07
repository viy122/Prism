@extends($assetDetailLayout)
@section('title', 'Asset '.$asset->reference)
@include('prism.shared.office-asset-styles')
@section('content')
<main class="asset-page">
    <nav class="asset-actions" aria-label="Breadcrumb"><span>Office Assets</span><span aria-hidden="true">/</span><a class="asset-text-action" href="{{ $backUrl }}">Asset Register</a><span aria-hidden="true">/</span><span aria-current="page">{{ $asset->reference }}</span></nav>
    <header class="asset-page-header"><div><p class="asset-eyebrow">Office assets · Equipment record</p><h1>{{ $asset->receipt?->item?->name }}</h1><p class="asset-subtitle">{{ $asset->reference }}</p></div><span class="asset-pill" data-status="{{ \App\Models\OfficeAsset::USAGE[$asset->usage_status] }}">{{ \App\Models\OfficeAsset::USAGE[$asset->usage_status] }}</span></header>
    @if(session('receiving_success'))<p class="receiving-message" role="status">{{ session('receiving_success') }}</p>@endif
    @if($errors->any())<div class="receiving-message error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @php($canManageAsset = $assetService->canManage(auth()->user(), $asset->office_id))
    <section class="asset-card">
        <div class="asset-card-head"><div><p class="asset-eyebrow">Asset overview</p><h2>Equipment details</h2></div></div>
        <dl class="asset-detail-grid">
            <div><dt>Current office</dt><dd>{{ $asset->office?->name }}</dd></div>
            <div><dt>Received</dt><dd>{{ $asset->receipt?->arrival_date?->toDateString() }}</dd></div>
            <div><dt>Originating PR / FY</dt><dd>{{ $asset->receipt?->item?->purchaseRequest?->number }} / {{ $asset->receipt?->item?->purchaseRequest?->fiscal_year }}</dd></div>
            <div><dt>Purchase Order</dt><dd>{{ $asset->receipt?->purchaseOrder?->po_number }}</dd></div>
            <div><dt>Serial / property number</dt><dd>{{ $asset->serial_number ?: 'Not recorded' }} / {{ $asset->property_number ?: 'Not recorded' }}</dd></div>
            <div><dt>Allocation</dt><dd>{{ $asset->location ?: 'Unassigned' }} — {{ $asset->accountable_person ?: 'No accountable person' }}</dd></div>
            <div><dt>Assigned on</dt><dd>{{ $asset->assigned_on?->toDateString() ?: 'Not assigned' }}</dd></div>
            <div><dt>Usage</dt><dd>{{ \App\Models\OfficeAsset::USAGE[$asset->usage_status] }}<small>Started: {{ $asset->usage_started_on?->toDateString() ?: 'Not recorded' }}</small></dd></div>
            <div><dt>Warranty</dt><dd><span class="asset-pill" data-status="{{ $asset->warrantyStatus() }}">{{ $asset->warrantyStatus() }}</span><small>{{ $asset->warranty_start?->toDateString() }} {{ $asset->warranty_end ? 'to '.$asset->warranty_end->toDateString() : '' }}</small></dd></div>
        </dl>
        <p>Supplier / service contact: {{ $asset->supplier_contact ?: 'Not recorded' }}</p><p>{{ $asset->warranty_notes }}</p>
        @if($asset->warranty_path)<p><a href="{{ route('office-assets.proof', $asset) }}">Download {{ $asset->warranty_filename }}</a></p>@endif
    </section>
    @if($asset->transfers->isNotEmpty())
    <section class="asset-card"><h2>Office transfer pending</h2>
        @foreach($asset->transfers as $transfer)
            <p>Requested transfer to {{ $transfer->toOffice?->name }}. Reason: {{ $transfer->reason }}</p>
            <p class="receiving-note">Editing is paused until the destination office accepts or rejects, or the originating office cancels.</p>
            @if($canManageAsset)<form class="receiving-form" method="POST" action="{{ route('office-assets.resolve-transfer', $transfer) }}">@csrf<input type="hidden" name="decision" value="cancelled"><label>Cancellation reason<textarea name="reason" required maxlength="1000"></textarea></label><button type="submit">Cancel transfer</button></form>@endif
        @endforeach
    </section>
    @elseif($canManageAsset)
    <section class="asset-card"><h2>Update this unit</h2>
        @foreach(['identity' => 'Serial & property number', 'allocation' => 'Allocation & usage', 'warranty' => 'Warranty'] as $assetSection => $label)
        <details class="asset-disclosure" @if(old('section') === $assetSection) open @endif><summary>{{ $label }}<i class="ti ti-chevron-down" aria-hidden="true"></i></summary>
            <form class="receiving-form" method="POST" enctype="multipart/form-data" action="{{ route('office-assets.update') }}">@csrf
                <input type="hidden" name="asset_ids[]" value="{{ $asset->id }}"><input type="hidden" name="versions[{{ $asset->id }}]" value="{{ $asset->version }}"><input type="hidden" name="section" value="{{ $assetSection }}">
                @include('prism.shared.office-asset-fields', ['editingAsset' => $asset])
                <button type="submit">Save {{ strtolower($label) }}</button>
            </form>
        </details>
        @endforeach
    </section>
    @if($asset->usage_status !== 'retired')
    <section class="asset-card"><h2>Transfer to another office</h2><p class="receiving-note">The destination Office Head must accept before ownership changes. The receipt, warranty and history stay with the asset.</p>
        <form class="receiving-form" method="POST" action="{{ route('office-assets.transfer', $asset) }}">@csrf<input type="hidden" name="version" value="{{ $asset->version }}">
            <label>Receiving office<select name="to_office_id" required><option value="">Choose office</option>@foreach($transferOffices as $office)<option value="{{ $office->id }}">{{ $office->code }} — {{ $office->name }}</option>@endforeach</select></label>
            <label>Transfer reason<textarea name="reason" required maxlength="1000"></textarea></label><button type="submit">Request transfer</button>
        </form>
    </section>
    @endif
    @endif
    <section class="asset-card"><h2>Asset history</h2>
        @foreach($history as $event)
        <div class="asset-history"><strong>{{ str_replace('_', ' ', ucfirst($event->action)) }}</strong><small>{{ $event->created_at }} · {{ $event->user?->name ?: 'Former user' }}</small><p>{{ $event->metadata_json['reason'] ?? '' }}</p>
            <details><summary>View changes</summary><table><thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead><tbody>
            @foreach(['office_id', 'serial_number', 'property_number', 'location', 'accountable_person', 'assigned_on', 'usage_status', 'usage_started_on', 'warranty_coverage', 'warranty_start', 'warranty_end', 'supplier_contact', 'warranty_notes', 'warranty_filename'] as $field)
                @if(($event->old_values_json[$field] ?? null) !== ($event->new_values_json[$field] ?? null))<tr><td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td><td>{{ $event->old_values_json[$field] ?? '—' }}</td><td>{{ $event->new_values_json[$field] ?? '—' }}</td></tr>@endif
            @endforeach
            </tbody></table></details>
        </div>
        @endforeach
        <div class="asset-pagination"><span>Page {{ $history->currentPage() }} of {{ $history->lastPage() }}</span><div class="asset-actions">@if($history->previousPageUrl())<a href="{{ $history->previousPageUrl() }}">Newer</a>@endif @if($history->nextPageUrl())<a href="{{ $history->nextPageUrl() }}">Older</a>@endif</div></div>
    </section>
</main>
@endsection
