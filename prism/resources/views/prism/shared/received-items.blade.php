@extends('prism.layouts.office-head')
@section('title', 'Received Items')
@include('prism.shared.office-asset-styles')
@section('content')
<main class="asset-page">
    <header class="asset-page-header">
        <div><p class="asset-eyebrow">Office Assets · Receiving &amp; registration</p><h1>Received Items</h1><p class="asset-subtitle">Received equipment awaiting registration, across all acquisition years.</p></div>
        <a class="asset-button" href="{{ route('office-head.office-assets') }}"><i class="ti ti-devices" aria-hidden="true"></i> Asset Register</a>
    </header>
    @if(session('receiving_success'))<p class="receiving-message" role="status">{{ session('receiving_success') }}</p>@endif
    @if($errors->any())<div class="receiving-message error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="asset-card" id="received-items" aria-labelledby="receivedItemsTitle">
        <div class="asset-card-head"><div><p class="asset-eyebrow">Receiving &amp; registration</p><h2 id="receivedItemsTitle">Received items ready for allocation</h2>
        <p class="receiving-note">Register received equipment here, then assign its location and accountable person in Asset Register.</p></div><span class="asset-count"><i class="ti ti-package" aria-hidden="true"></i> {{ $readyUnitCount }} received units</span></div>
        <div class="receiving-scroll"><table class="receiving-table"><thead><tr><th>Received item</th><th>PR / PO / FY</th><th>Arrival</th><th>Received</th><th>Already registered</th><th>Ready to register</th><th>Next step</th></tr></thead><tbody>
        @forelse($readyReceipts as $readyReceipt)
            @php($remainingUnits = (int) $readyReceipt->quantity - $readyReceipt->office_assets_count)
            <tr data-ready-receipt="{{ $readyReceipt->id }}">
                <td><strong>{{ $readyReceipt->item->name }}</strong><small>{{ $readyReceipt->item->purchaseRequest->office?->code }} · {{ $readyReceipt->item->unit }}</small></td>
                <td>{{ $readyReceipt->item->purchaseRequest->number }}<small>{{ $readyReceipt->purchaseOrder->po_number }} · FY {{ $readyReceipt->item->purchaseRequest->fiscal_year }}</small></td>
                <td>{{ $readyReceipt->arrival_date->toDateString() }}</td><td>{{ (int) $readyReceipt->quantity }}</td><td>{{ $readyReceipt->office_assets_count }}</td><td><strong>{{ $remainingUnits }}</strong></td>
                <td><details class="asset-registration" @if(old('receipt_id') == $readyReceipt->id) open @endif><summary><i class="ti ti-plus" aria-hidden="true"></i> Register for allocation</summary>
                    <form class="receiving-form" method="POST" action="{{ route('office-assets.register', $readyReceipt) }}">@csrf
                        <input type="hidden" name="receipt_id" value="{{ $readyReceipt->id }}">
                        <input type="hidden" name="submission_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                        <label>Equipment units<input type="number" name="quantity" min="1" max="{{ min(500, $remainingUnits) }}" value="{{ min(500, $remainingUnits) }}" required></label>
                        <label><span><input style="width:auto" type="checkbox" name="equipment_confirmed" value="1" required> These are individual equipment units, not consumables.</span></label>
                        <button type="submit">Register units</button>
                        <p class="receiving-note receiving-wide">After registration, you will go to Asset Register to assign these units and record warranty coverage.</p>
                    </form>
                </details></td>
            </tr>
        @empty
            <tr><td colspan="7" class="asset-empty">No unregistered whole-unit receipts are available in your office. Registered units are available in Asset Register. For newly delivered items, record the actual receipt first.</td></tr>
        @endforelse
        </tbody></table></div>
        <div class="asset-pagination"><a href="{{ route('office-head.purchase-requests') }}">Record / review item receipts</a><div class="asset-actions">@if($readyReceipts->previousPageUrl())<a href="{{ $readyReceipts->previousPageUrl() }}">Previous receipts</a>@endif @if($readyReceipts->nextPageUrl())<a href="{{ $readyReceipts->nextPageUrl() }}">More receipts</a>@endif</div></div>
    </section>
</main>
@endsection
