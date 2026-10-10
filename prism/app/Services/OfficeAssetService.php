<?php

namespace App\Services;

use App\Models\{AuditLog, ItemReceipt, Office, OfficeAsset, OfficeAssetTransfer, User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OfficeAssetService
{
    public function canManage(User $user, int $officeId): bool
    {
        return (int) $user->office_id === $officeId && $user->roles->contains('name', 'Office Head / Dean');
    }

    public function scopeFor(Builder $query, User $user): void
    {
        if ($user->roles->whereIn('name', ['Procurement Office', 'Chancellor', 'System Administrator'])->isNotEmpty()) return;
        if ($user->roles->contains('name', 'Vice Chancellor')) {
            $query->whereHas('office', fn ($q) => $q->whereIn('code', match ($user->vc_type) {
                'vcaa' => ['CICS', 'COE', 'CBA', 'CAS', 'CCJE', 'CHS', 'CTE', 'LS', 'RS', 'SDS', 'LIB', 'NSTP', 'CULT', 'SPORTS', 'SCHOL', 'HLTHO', 'SCILAB'],
                'vcaf' => ['HRMO', 'ACCT', 'BUD', 'CASH', 'PFM', 'PROC', 'PS', 'RMO', 'GS', 'EMU'],
                default => [],
            }));
            return;
        }
        if ($this->canManage($user, (int) $user->office_id) && $user->office_id) {
            $query->where('office_id', $user->office_id);
            return;
        }
        $query->whereRaw('1 = 0');
    }

    public function visible(User $user): Builder
    {
        $query = OfficeAsset::with(['office', 'receipt.item.purchaseRequest.office', 'receipt.purchaseOrder', 'transfers' => fn ($q) => $q->where('status', 'pending')]);
        $this->scopeFor($query, $user);
        return $query;
    }

    public function pageData(Request $request): array
    {
        $assets = $this->visible($request->user())->orderByDesc('id')->get();
        $offices = $assets->pluck('office')->filter()->unique('id')->sortBy('code');
        $years = $assets->map(fn ($a) => $a->receipt?->item?->purchaseRequest?->fiscal_year)->filter()->unique()->sortDesc();
        $assets = $assets->filter(function ($asset) use ($request) {
            $search = mb_strtolower(trim((string) $request->query('search', '')));
            $haystack = mb_strtolower(implode(' ', [$asset->reference, $asset->receipt?->item?->name, $asset->serial_number, $asset->property_number, $asset->location, $asset->accountable_person]));
            return (!$search || str_contains($haystack, $search))
                && (!$request->filled('office') || (string) $asset->office_id === (string) $request->query('office'))
                && (!$request->filled('acquisition_year') || (string) $asset->receipt?->item?->purchaseRequest?->fiscal_year === (string) $request->query('acquisition_year'))
                && (!$request->filled('usage') || $asset->usage_status === $request->query('usage'))
                && (!$request->filled('warranty') || $asset->warrantyStatus() === $request->query('warranty'))
                && (!$request->filled('allocation') || ($asset->assigned_on ? 'assigned' : 'unassigned') === $request->query('allocation'))
                && (!$request->filled('item') || (int) $asset->receipt?->purchase_request_item_id === (int) $request->query('item'))
                && (!$request->filled('registration') || $asset->registration_token === $request->query('registration'));
        });
        $groups = [
            'Registered Units' => $assets, 'Unassigned' => $assets->whereNull('assigned_on'),
            'In Use' => $assets->where('usage_status', 'in_use'), 'Under Repair' => $assets->where('usage_status', 'under_repair'),
            'Warranty Expiring Soon' => $assets->filter(fn ($a) => $a->warrantyStatus() === 'Expiring Soon'),
        ];
        $summary = collect($groups)->map(fn ($units) => $units->count())->all();
        $summaryDetails = collect($groups)->map(fn ($units) => $units->take(12)->values()->map(fn ($asset) => [
            'id' => $asset->id, 'reference' => $asset->reference,
            'item' => $asset->receipt?->item?->name ?? 'Equipment',
            'office' => $asset->office?->code ?? 'No office',
            'year' => $asset->receipt?->item?->purchaseRequest?->fiscal_year,
            'source' => $asset->receipt?->item?->purchaseRequest?->number ?? 'No PR reference',
            'location' => $asset->location ?: 'Unassigned',
            'person' => $asset->accountable_person ?: 'No accountable person',
            'warranty' => $asset->warranty_end?->toDateString(),
        ])->all())->all();
        $perPage = 25;
        $page = max(1, (int) $request->query('page', 1));
        $assets = new \Illuminate\Pagination\LengthAwarePaginator($assets->forPage($page, $perPage)->values(), $assets->count(), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
        $incoming = OfficeAssetTransfer::with(['asset.receipt.item', 'fromOffice', 'toOffice'])
            ->where('status', 'pending')->where('to_office_id', $request->user()->office_id)
            ->when(!$this->canManage($request->user(), (int) $request->user()->office_id), fn ($q) => $q->whereRaw('1 = 0'))->get();
        return compact('assets', 'summary', 'summaryDetails', 'offices', 'years', 'incoming') + ['assetService' => $this];
    }

    public function receivedPageData(Request $request): array
    {
        $ready = $this->registrationCandidates($request->user());
        $readyUnitCount = $ready->sum(fn ($receipt) => (int) $receipt->quantity - $receipt->office_assets_count);
        $readyPage = max(1, (int) $request->query('ready_page', 1));
        $readyReceipts = new \Illuminate\Pagination\LengthAwarePaginator($ready->forPage($readyPage, 10)->values(), $ready->count(), 10, $readyPage, [
            'path' => $request->url(), 'query' => $request->query(), 'pageName' => 'ready_page', 'fragment' => 'received-items',
        ]);
        return compact('readyReceipts', 'readyUnitCount');
    }

    public function registrationCandidates(User $user): \Illuminate\Support\Collection
    {
        if (!$user->office_id || !$this->canManage($user, (int) $user->office_id)) return collect();

        return ItemReceipt::with(['item.purchaseRequest.office', 'item.purchaseRequest.abstractOfCanvass.purchaseOrder', 'purchaseOrder'])
            ->withCount('officeAssets')
            ->whereHas('item.purchaseRequest', fn ($q) => $q->where('office_id', $user->office_id)
                ->whereNotIn('status', ['cancelled', 'cancelled_system_error', 'pr_denied']))
            ->whereHas('purchaseOrder', fn ($q) => $q->where('signatory_stage', 'fully_signed'))
            ->orderByDesc('arrival_date')->orderByDesc('id')->get()
            ->filter(fn ($receipt) => (float) $receipt->quantity === floor((float) $receipt->quantity)
                && (int) $receipt->quantity > $receipt->office_assets_count
                && (int) $receipt->item->purchaseRequest->abstractOfCanvass?->purchaseOrder?->id === (int) $receipt->purchase_order_id)
            ->values();
    }

    public function audit(Request $request, OfficeAsset $asset, string $action, ?array $before, string $reason): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id, 'action' => $action,
            'auditable_type' => OfficeAsset::class, 'auditable_id' => $asset->id,
            'old_values_json' => $before, 'new_values_json' => $asset->fresh()->toArray(),
            'metadata_json' => ['reason' => $reason], 'ip_address' => $request->ip(),
        ]);
    }
}
