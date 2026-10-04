@php($editingReceipt = $receiptToEdit ?? null)
<form class="receiving-form" data-receipt-form method="POST" enctype="multipart/form-data"
      action="{{ $editingReceipt ? route('item-receiving.update', ['receipt' => $editingReceipt['id'], 'year' => $delivery['fiscalYear']]) : route('item-receiving.store', ['item' => $delivery['id'], 'year' => $delivery['fiscalYear']]) }}">
    @csrf
    @if($editingReceipt)
        @method('PUT')
    @else
        <input type="hidden" name="submission_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
    @endif
    <label>Actual Arrival Date
        <input type="date" name="arrival_date" required max="{{ now()->toDateString() }}" min="{{ $delivery['procuredDate'] ?? '' }}" value="{{ $editingReceipt['arrivalDate'] ?? '' }}">
    </label>
    <label>Quantity Received ({{ $delivery['unit'] }})
        <input type="number" name="quantity" required min="0.01" step="0.01" max="{{ $delivery['remainingQuantity'] + ($editingReceipt['quantity'] ?? 0) }}" value="{{ $editingReceipt['quantity'] ?? '' }}">
    </label>
    <label>Received By
        <input name="received_by_name" required maxlength="255" value="{{ $editingReceipt['receivedBy'] ?? auth()->user()->name }}">
    </label>
    <label>Remarks (optional)
        <textarea name="remarks" maxlength="2000" rows="2">{{ $editingReceipt['remarks'] ?? '' }}</textarea>
    </label>
    <label>{{ $editingReceipt ? 'Replace attachment (optional)' : 'Delivery receipt (optional)' }}
        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
        <span class="receiving-note">PDF, JPG or PNG; up to 10 MB.</span>
    </label>
    @if($editingReceipt)
        <label>Reason for Correction
            <textarea name="correction_reason" required maxlength="1000" rows="2"></textarea>
        </label>
    @endif
    <div class="receiving-message error receiving-wide" data-receipt-errors role="alert" tabindex="-1" hidden></div>
    <button type="submit">{{ $editingReceipt ? 'Save Correction' : 'Record Receipt' }}</button>
</form>
