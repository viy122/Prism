<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>{{ $number }}</title>
    <style>
        @page { margin: 0.65in; }
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #111827; }
        h1 { margin: 0 0 4px; text-align: center; font-size: 17pt; }
        .subtitle { text-align: center; margin: 0 0 22px; color: #4b5563; }
        .details { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .details td { border: 1px solid #cbd5e1; padding: 7px 9px; }
        .details .label { width: 22%; font-weight: bold; background: #f1f5f9; }
        .items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items th, .items td { border: 1px solid #94a3b8; padding: 6px; }
        .items th { background: #e2e8f0; }
        .number { text-align: right; white-space: nowrap; }
        .total td { font-weight: bold; }
        h2 { margin: 20px 0 7px; font-size: 12pt; }
    </style>
</head>
<body>
    <h1>{{ $docType === 'pr' ? 'PURCHASE REQUEST' : 'ABSTRACT OF CANVASS' }}</h1>
    <p class="subtitle">{{ $number }}</p>

    <table class="details">
        <tr><td class="label">Office</td><td>{{ $purchaseRequest->office?->name ?? $purchaseRequest->office?->code ?? '—' }}</td></tr>
        <tr><td class="label">Title</td><td>{{ $purchaseRequest->title ?? '—' }}</td></tr>
        <tr><td class="label">Fiscal Year</td><td>{{ $purchaseRequest->fiscal_year ?? '—' }}</td></tr>
        <tr><td class="label">Current Stage</td><td>{{ $document->signatory_label }}</td></tr>
        @if($docType === 'aoc')
            <tr><td class="label">Winning Supplier</td><td>{{ $document->winning_supplier_name ?: '—' }}</td></tr>
        @endif
        <tr><td class="label">Remarks</td><td>{{ $document->remarks ?: ($purchaseRequest->remarks ?: '—') }}</td></tr>
    </table>

    <h2>Items</h2>
    <table class="items">
        <thead>
            <tr><th>Item</th><th>Description</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Total</th></tr>
        </thead>
        <tbody>
            @forelse($purchaseRequest->items as $item)
                <tr>
                    <td>{{ $item->name }}</td><td>{{ $item->description ?: '—' }}</td>
                    <td class="number">{{ number_format((float) $item->quantity, 2) }}</td><td>{{ $item->unit }}</td>
                    <td class="number">PHP {{ number_format((float) $item->estimated_unit_cost, 2) }}</td>
                    <td class="number">PHP {{ number_format((float) $item->estimated_total_cost, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No items on file.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr class="total"><td colspan="5">Total</td><td class="number">PHP {{ number_format((float) $purchaseRequest->total_amount, 2) }}</td></tr></tfoot>
    </table>
</body>
</html>
