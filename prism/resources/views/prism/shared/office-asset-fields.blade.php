@if($assetSection === 'identity')
    <label>Serial number<input name="serial_number" maxlength="255" value="{{ old('serial_number', $editingAsset?->serial_number) }}"></label>
    <label>Property number<input name="property_number" maxlength="255" value="{{ old('property_number', $editingAsset?->property_number) }}"></label>
@elseif($assetSection === 'allocation')
    <label>Allocation<select name="allocation"><option value="assigned">Assigned</option><option value="unassigned" @selected(old('allocation', $editingAsset && !$editingAsset->assigned_on ? 'unassigned' : 'assigned') === 'unassigned')>Unassigned</option></select></label>
    <label>Room / location<input name="location" maxlength="255" value="{{ old('location', $editingAsset?->location) }}" placeholder="e.g. Dean's Office"></label>
    <label>Accountable person<input name="accountable_person" maxlength="255" value="{{ old('accountable_person', $editingAsset?->accountable_person) }}"></label>
    <label>Assignment date<input type="date" name="assigned_on" max="{{ today()->toDateString() }}" value="{{ old('assigned_on', $editingAsset?->assigned_on?->toDateString()) }}"></label>
    <label>Usage<select name="usage_status" required>@foreach(\App\Models\OfficeAsset::USAGE as $key => $label)<option value="{{ $key }}" @selected(old('usage_status', $editingAsset?->usage_status) === $key)>{{ $label }}</option>@endforeach</select></label>
    <label>Actual usage start date<input type="date" name="usage_started_on" max="{{ today()->toDateString() }}" value="{{ old('usage_started_on', $editingAsset?->usage_started_on?->toDateString()) }}"></label>
    <p class="receiving-note receiving-wide">Assigned units need a location, accountable person and assignment date. In Use also requires the actual usage start date. Assignment does not automatically mark a unit In Use.</p>
@else
    <label>Coverage<select name="warranty_coverage" required>@foreach(['not_recorded' => 'Not Yet Recorded', 'none' => 'No Warranty', 'covered' => 'With Warranty'] as $key => $label)<option value="{{ $key }}" @selected(old('warranty_coverage', $editingAsset?->warranty_coverage) === $key)>{{ $label }}</option>@endforeach</select></label>
    <label>Warranty start date<input type="date" name="warranty_start" value="{{ old('warranty_start', $editingAsset?->warranty_start?->toDateString()) }}"></label>
    <label>Duration in months (optional)<input type="number" name="warranty_months" min="1" max="600" value="{{ old('warranty_months') }}" placeholder="e.g. 12"></label>
    <label>Explicit expiry date<input type="date" name="warranty_end" value="{{ old('warranty_end', $editingAsset?->warranty_end?->toDateString()) }}"></label>
    <label>Supplier / service contact<input name="supplier_contact" maxlength="255" value="{{ old('supplier_contact', $editingAsset?->supplier_contact) }}"></label>
    <label>Coverage notes<textarea name="warranty_notes" maxlength="3000" rows="2">{{ old('warranty_notes', $editingAsset?->warranty_notes) }}</textarea></label>
    <label>Warranty proof (PDF / JPG / PNG, max 10 MB)<input type="file" name="warranty_proof" accept=".pdf,.jpg,.jpeg,.png"></label>
    <p class="receiving-note receiving-wide">Confirm dates against the supplier's terms. Enter an expiry date or duration; an explicit expiry date takes precedence. Coverage includes the expiry date. Expiring Soon means 30 days or less remaining. An existing proof is kept unless replaced.</p>
@endif
<label class="receiving-wide">Reason / reference for this update<textarea name="reason" required maxlength="1000" rows="2">{{ old('reason') }}</textarea></label>
