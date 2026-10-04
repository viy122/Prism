@php($receivingReadOnly = $receivingReadOnly ?? false)
<details class="receiving-details" id="receiving-item-{{ $delivery['id'] }}">
    <summary>View Details{{ !$receivingReadOnly && $delivery['canReceive'] && $delivery['remainingQuantity'] > 0 ? ' / Record Receipt' : '' }} — {{ $delivery['item'] }}</summary>
    <dl>
        <div><dt>Procured Date</dt><dd>{{ $delivery['procuredDate'] ?: 'Not recorded' }}</dd></div>
        <div><dt>Expected Delivery</dt><dd>{{ $delivery['expectedDelivery'] ?: 'No target date' }}</dd></div>
        <div><dt>Fully Received On</dt><dd>{{ $delivery['arrivalDate'] ?: 'Not yet recorded' }}</dd></div>
        <div><dt>Procurement to Full Receipt</dt><dd>{{ $delivery['daysToReceive'] !== null ? $delivery['daysToReceive'].' days' : 'Not available yet' }}</dd></div>
        <div><dt>Delivery Timing</dt><dd>{{ $delivery['delayLabel'] }}</dd></div>
        <div><dt>PO / Payment Status</dt><dd>{{ $delivery['paymentStatus'] }}</dd></div>
    </dl>
    @if(!$receivingReadOnly && $delivery['canManageDates'])
        <p class="receiving-note">Procured Date is the actual date the order was confirmed with the supplier after approvals. Expected Delivery is the target arrival at the requesting office.</p>
        <form class="receiving-form" method="POST" action="{{ route('item-receiving.dates', ['po' => $delivery['poId'], 'year' => $delivery['fiscalYear']]) }}">
            @csrf
            <label>Procured Date<input type="date" name="procured_on" required max="{{ now()->toDateString() }}" value="{{ $delivery['procuredDate'] }}"></label>
            <label>Expected Delivery Date<input type="date" name="expected_delivery_date" value="{{ $delivery['expectedDelivery'] }}"></label>
            <label>Reason / Reference for Date Update<textarea name="correction_reason" maxlength="1000" rows="2" @required($delivery['procuredDate'] || $delivery['expectedDelivery'])></textarea></label>
            <button type="submit">Save PO Dates</button>
            <p class="receiving-note receiving-wide">These dates apply to every item on this PO. Date changes are recorded in the audit trail.</p>
        </form>
    @endif
    @if(!$receivingReadOnly && $delivery['canReceive'] && $delivery['remainingQuantity'] > 0)
        <p class="receiving-note">Record the date the requesting office actually received these items. Remaining: {{ $delivery['remainingQuantity'] }} {{ $delivery['unit'] }}.</p>
        @include('prism.shared.receiving-form', ['receiptToEdit' => null])
    @endif
    @forelse($delivery['receipts'] as $receiptEntry)
        <div class="receiving-history">
            <strong>{{ $receiptEntry['arrivalDate'] }} — {{ $receiptEntry['quantity'] }} {{ $delivery['unit'] }}</strong>
            <p>Received by {{ $receiptEntry['receivedBy'] }}</p>
            <p class="receiving-note">Recorded by {{ $receiptEntry['recordedBy'] }} on {{ $receiptEntry['recordedAt'] }}. Last updated {{ $receiptEntry['updatedAt'] }}.</p>
            @if($receiptEntry['remarks'])<p>{{ $receiptEntry['remarks'] }}</p>@endif
            @if($receiptEntry['attachmentName'] && !$receivingReadOnly)
                <a href="{{ route('item-receiving.attachment', ['receipt' => $receiptEntry['id'], 'year' => $delivery['fiscalYear']]) }}">Download {{ $receiptEntry['attachmentName'] }}</a>
            @elseif($receiptEntry['attachmentName'])
                <p>Attachment: {{ $receiptEntry['attachmentName'] }}</p>
            @endif
            @if(!$receivingReadOnly && $delivery['canReceive'])
                <details><summary>Correct this receipt</summary>@include('prism.shared.receiving-form', ['receiptToEdit' => $receiptEntry])</details>
            @endif
        </div>
    @empty
        <p class="receiving-note">Arrival not recorded. Payment or Supply Office delivery status does not confirm receipt by the requesting office.</p>
    @endforelse
</details>
