<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeAsset extends Model
{
    protected $guarded = ['id'];

    public const USAGE = ['not_in_use' => 'Not Yet In Use', 'in_use' => 'In Use', 'under_repair' => 'Under Repair', 'retired' => 'Retired'];

    protected function casts(): array
    {
        return array_fill_keys(['assigned_on', 'usage_started_on', 'warranty_start', 'warranty_end'], 'date') + ['version' => 'integer'];
    }

    public function receipt() { return $this->belongsTo(ItemReceipt::class, 'item_receipt_id'); }
    public function office() { return $this->belongsTo(Office::class); }
    public function transfers() { return $this->hasMany(OfficeAssetTransfer::class); }

    public function warrantyStatus(): string
    {
        if ($this->warranty_coverage === 'none') return 'No Warranty';
        if ($this->warranty_coverage !== 'covered' || !$this->warranty_start || !$this->warranty_end) return 'Not Yet Recorded';
        $today = today();
        if ($this->warranty_start->gt($today)) return 'Not Yet Active';
        if ($this->warranty_end->lt($today)) return 'Warranty Expired';
        return $this->warranty_end->lte($today->addDays(30)) ? 'Expiring Soon' : 'Under Warranty';
    }
}
