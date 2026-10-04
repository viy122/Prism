<section class="receiving-section" aria-label="Item delivery and receiving">
    @include('prism.shared.receiving-assets')
    <h2>Item Delivery &amp; Receiving</h2>
    <p class="receiving-note">Actual receipt by the requesting office. Partial deliveries remain open until all ordered quantities are received. Dates use YYYY-MM-DD.</p>
    @if(isset($reportVersion) && !isset($deliveryRows))
        <p class="receiving-note">Receiving data was not captured in this report version.</p>
    @else
    <div class="receiving-scroll">
        <table class="receiving-table">
            <thead><tr>
                <th scope="col">Office / FY</th><th scope="col">Item / PO</th><th scope="col">Procured Date</th>
                <th scope="col">Expected Delivery</th><th scope="col">Arrival Date</th><th scope="col">Received / Ordered</th>
                <th scope="col">Receiving Status</th><th scope="col">Delivery Duration</th><th scope="col">Delay</th>
            </tr></thead>
            <tbody>
            @forelse($deliveryRows ?? [] as $delivery)
                <tr>
                    <td>{{ $delivery['office'] }}<br>FY {{ $delivery['fiscalYear'] }}</td>
                    <td><strong>{{ $delivery['item'] }}</strong><br>{{ $delivery['poNumber'] ?: 'PO number pending' }}<br>{{ $delivery['prNumber'] }}</td>
                    <td>{{ $delivery['procuredDate'] ?: 'Not recorded' }}</td>
                    <td>{{ $delivery['expectedDelivery'] ?: 'No target date' }}</td>
                    <td data-receiving-item="{{ $delivery['id'] }}" data-receiving-field="arrival">{{ $delivery['arrivalDate'] ?: ($delivery['lastArrivalDate'] ? 'Latest partial: '.$delivery['lastArrivalDate'] : 'Arrival not recorded') }}</td>
                    <td data-receiving-item="{{ $delivery['id'] }}" data-receiving-field="quantity">{{ $delivery['receivedQuantity'] }} / {{ $delivery['quantity'] }} {{ $delivery['unit'] }}</td>
                    <td><span class="receiving-status" data-receiving-item="{{ $delivery['id'] }}" data-receiving-field="status">{{ $delivery['receivingStatus'] }}</span></td>
                    <td data-receiving-item="{{ $delivery['id'] }}" data-receiving-field="duration">{{ $delivery['daysToReceive'] !== null ? $delivery['daysToReceive'].' days' : '—' }}</td>
                    <td data-receiving-item="{{ $delivery['id'] }}" data-receiving-field="delay" class="{{ $delivery['daysDelayed'] > 0 ? 'receiving-late' : '' }}">{{ $delivery['delayLabel'] }}</td>
                </tr>
                <tr class="receiving-actions-row"><td colspan="9">@include('prism.shared.receiving-details')</td></tr>
            @empty
                <tr><td colspan="9">No purchase-order items found for this view.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @endif
</section>
