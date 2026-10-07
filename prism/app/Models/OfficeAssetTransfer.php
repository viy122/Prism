<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeAssetTransfer extends Model
{
    protected $guarded = ['id'];
    public function asset() { return $this->belongsTo(OfficeAsset::class, 'office_asset_id'); }
    public function fromOffice() { return $this->belongsTo(Office::class, 'from_office_id'); }
    public function toOffice() { return $this->belongsTo(Office::class, 'to_office_id'); }
}
