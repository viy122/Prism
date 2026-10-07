@php
    $equipmentQuery = app(\App\Services\OfficeAssetService::class)->visible(auth()->user());
    $equipment = $equipmentQuery->whereHas('receipt', fn ($q) => $q->where('purchase_request_item_id', $delivery['id']))->get();
    $assetPagePrefix = auth()->user()->roles->contains('name', 'Office Head / Dean') ? 'office-head' : 'procurement-office';
@endphp
<div class="asset-panel" data-receiving-panel="allocation" hidden>
    <p class="receiving-note">Register individual equipment units after actual receipt. Consumables and fractional quantities use the receiving record and are not equipment units.</p>
    @if($delivery['canReceive'])
        @foreach($delivery['receipts'] as $receiptEntry)
            @php($registeredCount = \App\Models\OfficeAsset::where('item_receipt_id', $receiptEntry['id'])->count())
            @php($availableUnits = (int) $receiptEntry['quantity'] - $registeredCount)
            @if($availableUnits > 0 && floor($receiptEntry['quantity']) == $receiptEntry['quantity'])
            <form class="receiving-form" method="POST" action="{{ route('office-assets.register', ['receipt' => $receiptEntry['id']]) }}">@csrf
                <input type="hidden" name="submission_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <p class="receiving-wide">Receipt: {{ $receiptEntry['arrivalDate'] }} · Received {{ $receiptEntry['quantity'] }} · Registered {{ $registeredCount }} · Available {{ $availableUnits }}</p>
                <label>Units to register<input type="number" name="quantity" min="1" max="{{ min(500, $availableUnits) }}" value="{{ min(500, $availableUnits) }}" required></label>
                <label><span><input type="checkbox" name="equipment_confirmed" value="1" required style="width:auto"> These are individual equipment units, not consumables.</span></label>
                <button type="submit">Register Received Units</button>
            </form>
            @endif
        @endforeach
    @endif
    <p><strong>{{ $equipment->count() }}</strong> accessible units · <strong>{{ $equipment->whereNotNull('assigned_on')->count() }}</strong> assigned · <strong>{{ $equipment->where('usage_status', 'in_use')->count() }}</strong> in use</p>
    <p><a href="{{ route($assetPagePrefix.'.office-assets', ['item' => $delivery['id']]) }}">Open these units in Office Assets / bulk assignment</a></p>
    @forelse($equipment as $unit)
        <div class="receiving-history"><a href="{{ route('office-assets.show', $unit) }}">{{ $unit->reference }}</a><p>{{ $unit->location ?: 'Unassigned' }} · {{ $unit->accountable_person ?: 'No accountable person' }} · {{ \App\Models\OfficeAsset::USAGE[$unit->usage_status] }}</p></div>
    @empty<p class="receiving-note">No equipment units registered in your accessible offices yet.</p>@endforelse
</div>
<div class="asset-panel" data-receiving-panel="warranty" hidden>
    <p class="receiving-note">Record supplier-confirmed warranty dates per unit, or apply the same terms to selected units in Office Assets.</p>
    <a href="{{ route($assetPagePrefix.'.office-assets', ['item' => $delivery['id']]) }}">Open units / bulk warranty entry</a>
    @forelse($equipment as $unit)
        <div class="receiving-history"><a href="{{ route('office-assets.show', $unit) }}">{{ $unit->reference }}</a><p>{{ $unit->warrantyStatus() }}{{ $unit->warranty_end ? ' · Expires '.$unit->warranty_end->toDateString() : '' }}</p></div>
    @empty<p class="receiving-note">Register the received equipment units first.</p>@endforelse
</div>
