<?php

namespace App\Http\Controllers;

use App\Models\{AuditLog, ItemReceipt, Office, OfficeAsset, OfficeAssetTransfer, PurchaseOrder, PurchaseRequestItem};
use App\Services\{ItemReceivingService, OfficeAssetService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\{Rule, ValidationException};

class OfficeAssetController extends Controller
{
    public function register(Request $request, ItemReceipt $receipt, OfficeAssetService $assets, ItemReceivingService $receiving)
    {
        abort_unless($receipt->item?->purchaseRequest && $receiving->canReceive($request->user(), $receipt->item->purchaseRequest), 403);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'equipment_confirmed' => ['accepted'],
            'submission_token' => ['required', 'uuid', 'unique:office_assets,registration_token'],
        ]);
        DB::transaction(function () use ($request, $receipt, $data, $assets) {
            $po = PurchaseOrder::lockForUpdate()->findOrFail($receipt->purchase_order_id);
            PurchaseRequestItem::lockForUpdate()->findOrFail($receipt->purchase_request_item_id);
            $receipt = ItemReceipt::lockForUpdate()->findOrFail($receipt->id);
            $pr = $receipt->item->purchaseRequest;
            if ($po->signatory_stage !== 'fully_signed' || in_array($pr->status, ['cancelled', 'cancelled_system_error', 'pr_denied'], true)
                || (int) $pr->abstractOfCanvass?->purchaseOrder?->id !== $po->id) {
                throw ValidationException::withMessages(['quantity' => 'Registration requires an active, fully signed PO.']);
            }
            $registered = OfficeAsset::where('item_receipt_id', $receipt->id)->count();
            if ((float) $receipt->quantity !== floor((float) $receipt->quantity) || $registered + $data['quantity'] > (int) $receipt->quantity) {
                throw ValidationException::withMessages(['quantity' => 'Register only whole equipment units within the remaining received quantity.']);
            }
            if (OfficeAsset::where('registration_token', $data['submission_token'])->exists()) {
                throw ValidationException::withMessages(['submission_token' => 'These units were already registered. Refresh the page.']);
            }
            for ($i = 1; $i <= $data['quantity']; $i++) {
                $asset = OfficeAsset::create([
                    'item_receipt_id' => $receipt->id, 'office_id' => $pr->office_id,
                    'reference' => 'AST-'.Str::upper((string) Str::ulid()),
                    'registration_token' => $data['submission_token'], 'registration_unit' => $i,
                ]);
                $assets->audit($request, $asset, 'asset_registered', null, 'Registered from receipt '.$receipt->id);
            }
        });
        return redirect()->route('office-head.office-assets', ['item' => $receipt->purchase_request_item_id, 'registration' => $data['submission_token']])->with('receiving_success', $data['quantity'].' equipment units registered. Select the new units below to assign them or record warranty coverage.');
    }

    public function show(Request $request, OfficeAsset $asset, OfficeAssetService $assets)
    {
        $asset = $assets->visible($request->user())->findOrFail($asset->id);
        $history = AuditLog::with('user')->where('auditable_type', OfficeAsset::class)->where('auditable_id', $asset->id)->latest('id')->paginate(20);
        return view('prism.shared.office-asset-detail', [
            'assetDetailLayout' => $request->user()->roles->contains('name', 'Office Head / Dean') ? 'prism.layouts.office-head' : 'prism.layouts.app',
            'activeOfficePage' => 'office-assets',
            'activeRole' => 'office-assets', 'activeModulePage' => 'office-assets',
            'brandHref' => $this->indexUrl($request), 'roleLabel' => 'Equipment register', 'roleInitials' => 'AS',
            'roleNavigation' => \App\Support\PrismNav::roleNavigation(), 'moduleNavLabel' => 'Assets',
            'moduleNavigation' => [['slug' => 'office-assets', 'label' => 'Back to assets', 'href' => $this->indexUrl($request), 'icon' => 'devices']],
            'asset' => $asset, 'assetService' => $assets, 'history' => $history,
            'transferOffices' => Office::where('id', '!=', $asset->office_id)->whereHas('users', fn ($q) => $q->where('account_status', 'active')->whereHas('roles', fn ($r) => $r->where('name', 'Office Head / Dean')))->orderBy('code')->get(),
            'backUrl' => $this->indexUrl($request),
        ]);
    }

    private function indexUrl(Request $request): string
    {
        foreach (['Office Head / Dean' => 'office-head', 'Procurement Office' => 'procurement-office', 'Chancellor' => 'chancellor', 'Vice Chancellor' => 'vice-chancellor'] as $role => $prefix) {
            if ($request->user()->roles->contains('name', $role)) return route($prefix.'.office-assets');
        }
        return route('procurement-office.office-assets');
    }

    public function update(Request $request, OfficeAssetService $assets)
    {
        $data = $request->validate([
            'asset_ids' => ['required', 'array', 'min:1', 'max:100'], 'asset_ids.*' => ['required', 'integer', 'distinct'],
            'versions' => ['required', 'array'], 'versions.*' => ['integer', 'min:1'],
            'section' => ['required', Rule::in(['identity', 'allocation', 'warranty'])],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $values = match ($data['section']) {
            'identity' => $request->validate(['serial_number' => ['nullable', 'string', 'max:255'], 'property_number' => ['nullable', 'string', 'max:255']]),
            'allocation' => $request->validate([
                'allocation' => ['required', Rule::in(['assigned', 'unassigned'])],
                'location' => ['nullable', 'required_if:allocation,assigned', 'string', 'max:255'],
                'accountable_person' => ['nullable', 'required_if:allocation,assigned', 'string', 'max:255'],
                'assigned_on' => ['nullable', 'required_if:allocation,assigned', 'date_format:Y-m-d', 'before_or_equal:today'],
                'usage_status' => ['required', Rule::in(array_keys(OfficeAsset::USAGE))],
                'usage_started_on' => ['nullable', 'required_if:usage_status,in_use', 'date_format:Y-m-d', 'before_or_equal:today'],
            ]),
            'warranty' => $request->validate([
                'warranty_coverage' => ['required', Rule::in(['not_recorded', 'none', 'covered'])],
                'warranty_start' => ['nullable', 'required_if:warranty_coverage,covered', 'date_format:Y-m-d'],
                'warranty_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:warranty_start'],
                'warranty_months' => ['nullable', 'integer', 'min:1', 'max:600'],
                'supplier_contact' => ['nullable', 'string', 'max:255'], 'warranty_notes' => ['nullable', 'string', 'max:3000'],
                'warranty_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            ]),
        };
        if ($data['section'] === 'identity' && count($data['asset_ids']) !== 1) {
            throw ValidationException::withMessages(['asset_ids' => 'Edit serial and property numbers one unit at a time.']);
        }
        if ($data['section'] === 'allocation') {
            if ($values['allocation'] === 'unassigned') {
                if ($values['usage_status'] === 'in_use') throw ValidationException::withMessages(['allocation' => 'Assign a location and accountable person before marking a unit In Use.']);
                $values['location'] = $values['accountable_person'] = $values['assigned_on'] = null;
            }
            unset($values['allocation']);
            $values['usage_started_on'] = $values['usage_started_on'] ?? null;
            if ($values['usage_status'] === 'in_use' && $values['usage_started_on'] < $values['assigned_on']) {
                throw ValidationException::withMessages(['usage_started_on' => 'Usage cannot start before the current assignment.']);
            }
        }
        if ($data['section'] === 'warranty') {
            if ($values['warranty_coverage'] === 'covered') {
                if (empty($values['warranty_end']) && empty($values['warranty_months'])) {
                    throw ValidationException::withMessages(['warranty_end' => 'Enter the expiry date or warranty duration in months.']);
                }
                if (empty($values['warranty_end'])) $values['warranty_end'] = \Carbon\Carbon::parse($values['warranty_start'])->addMonthsNoOverflow((int) $values['warranty_months'])->toDateString();
            } else {
                $values['warranty_start'] = $values['warranty_end'] = null;
            }
            unset($values['warranty_months'], $values['warranty_proof']);
        }
        $storedPath = null;
        try {
            DB::transaction(function () use ($request, $assets, $data, &$values, &$storedPath) {
                // Use the same receipt locks as receipt corrections, always in ID order.
                $receiptIds = OfficeAsset::whereIn('id', $data['asset_ids'])->pluck('item_receipt_id')->unique()->sort();
                ItemReceipt::whereIn('id', $receiptIds)->orderBy('id')->lockForUpdate()->get();
                $records = OfficeAsset::whereIn('id', $data['asset_ids'])->orderBy('id')->lockForUpdate()->get();
                abort_unless($records->count() === count($data['asset_ids']), 404);
                foreach ($records as $asset) {
                    abort_unless($assets->canManage($request->user(), $asset->office_id), 403);
                    $this->assertEditable($asset, $data['versions'][$asset->id] ?? null);
                    if ($data['section'] === 'allocation') {
                        $acceptedAt = $asset->transfers()->where('status', 'accepted')->where('to_office_id', $asset->office_id)->latest('id')->value('updated_at');
                        foreach (['assigned_on', 'usage_started_on'] as $field) {
                            $earliest = max($asset->receipt->arrival_date->toDateString(), $acceptedAt ? substr((string) $acceptedAt, 0, 10) : '');
                            if (!empty($values[$field]) && $values[$field] < $earliest) {
                                throw ValidationException::withMessages([$field => 'Assignment and use cannot precede receipt or acceptance into the current office.']);
                            }
                        }
                    }
                    if ($data['section'] === 'identity' && !empty($values['property_number']) && OfficeAsset::where('property_number', $values['property_number'])->where('id', '!=', $asset->id)->exists()) {
                        throw ValidationException::withMessages(['property_number' => 'This property number is already registered.']);
                    }
                }
                if ($request->hasFile('warranty_proof') && $data['section'] === 'warranty') {
                    $storedPath = $request->file('warranty_proof')->store('asset-warranties', 'local');
                    abort_unless($storedPath, 500, 'The warranty proof could not be stored.');
                    $values['warranty_path'] = $storedPath;
                    $values['warranty_filename'] = $request->file('warranty_proof')->getClientOriginalName();
                }
                foreach ($records as $asset) {
                    $before = $asset->toArray();
                    $asset->update($values + ['version' => $asset->version + 1]);
                    $assets->audit($request, $asset, 'asset_'.$data['section'].'_updated', $before, $data['reason']);
                }
            });
        } catch (\Throwable $e) {
            if ($storedPath) Storage::disk('local')->delete($storedPath);
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException && $data['section'] === 'identity') {
                throw ValidationException::withMessages(['property_number' => 'This property number was just registered by another update. Refresh and use a unique number.']);
            }
            throw $e;
        }
        return back()->with('receiving_success', count($data['asset_ids']).' asset record(s) updated. History preserved.');
    }

    private function assertEditable(OfficeAsset $asset, $version): void
    {
        if ((int) $version !== $asset->version) throw ValidationException::withMessages(['versions' => 'An asset changed since this page opened. Refresh before saving.']);
        if ($asset->transfers()->where('status', 'pending')->exists()) throw ValidationException::withMessages(['asset_ids' => 'Resolve the pending office transfer before editing this asset.']);
    }

    public function transfer(Request $request, OfficeAsset $asset, OfficeAssetService $assets)
    {
        $data = $request->validate(['to_office_id' => ['required', 'integer', 'exists:offices,id'], 'version' => ['required', 'integer'], 'reason' => ['required', 'string', 'max:1000']]);
        DB::transaction(function () use ($request, $asset, $assets, $data) {
            $asset = OfficeAsset::lockForUpdate()->findOrFail($asset->id);
            abort_unless($assets->canManage($request->user(), $asset->office_id), 403);
            $this->assertEditable($asset, $data['version']);
            if ($asset->usage_status === 'retired' || $asset->office_id === (int) $data['to_office_id'] || !Office::whereKey($data['to_office_id'])->whereHas('users', fn ($q) => $q->where('account_status', 'active')->whereHas('roles', fn ($r) => $r->where('name', 'Office Head / Dean')))->exists()) {
                throw ValidationException::withMessages(['to_office_id' => 'Choose another office with an active Office Head. Retired assets cannot be transferred.']);
            }
            $before = $asset->toArray();
            $transfer = $asset->transfers()->create(['from_office_id' => $asset->office_id, 'to_office_id' => $data['to_office_id'], 'reason' => $data['reason']]);
            $asset->increment('version');
            $assets->audit($request, $asset, 'asset_transfer_requested', $before, 'Transfer #'.$transfer->id.' to office '.$data['to_office_id'].': '.$data['reason']);
        });
        return back()->with('receiving_success', 'Transfer requested. Ownership changes only after the receiving office accepts.');
    }

    public function resolveTransfer(Request $request, OfficeAssetTransfer $transfer, OfficeAssetService $assets)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['accepted', 'rejected', 'cancelled'])], 'reason' => ['required', 'string', 'max:1000']]);
        DB::transaction(function () use ($request, $transfer, $assets, $data) {
            $asset = OfficeAsset::lockForUpdate()->findOrFail($transfer->office_asset_id);
            $transfer = OfficeAssetTransfer::lockForUpdate()->findOrFail($transfer->id);
            abort_unless($assets->canManage($request->user(), $data['decision'] === 'cancelled' ? $transfer->from_office_id : $transfer->to_office_id), 403);
            if ($transfer->status !== 'pending' || $asset->office_id !== $transfer->from_office_id) throw ValidationException::withMessages(['decision' => 'This transfer is no longer pending.']);
            $before = $asset->toArray();
            $transfer->update(['status' => $data['decision'], 'resolution_reason' => $data['reason']]);
            if ($data['decision'] === 'accepted') {
                $asset->fill(['office_id' => $transfer->to_office_id, 'location' => null, 'accountable_person' => null, 'assigned_on' => null, 'usage_status' => $asset->usage_status === 'under_repair' ? 'under_repair' : 'not_in_use', 'usage_started_on' => null]);
            }
            $asset->version++;
            $asset->save();
            $assets->audit($request, $asset, 'asset_transfer_'.$data['decision'], $before, 'Transfer #'.$transfer->id.': '.$data['reason']);
        });
        return redirect()->route('office-head.office-assets')->with('receiving_success', 'Transfer '.$data['decision'].'.');
    }

    public function proof(Request $request, OfficeAsset $asset, OfficeAssetService $assets)
    {
        abort_unless($assets->visible($request->user())->whereKey($asset->id)->exists(), 403);
        abort_unless($asset->warranty_path && Storage::disk('local')->exists($asset->warranty_path), 404);
        return Storage::disk('local')->download($asset->warranty_path, $asset->warranty_filename);
    }
}
