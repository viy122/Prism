<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbstractOfCanvass extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_request_id',
        'created_by_user_id',
        'code',
        'signatory_stage',
        'remarks',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function signatureLogs(): HasMany
    {
        return $this->hasMany(AocSignatureLog::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function getSignatoryLabelAttribute(): string
    {
        return match ($this->signatory_stage) {
            'draft'           => 'AOC Created',
            'at_end_user'     => 'AOC – At End User (1st Signatory)',
            'at_signatory_2'  => 'AOC – At 2nd Signatory',
            'at_signatory_3'  => 'AOC – At 3rd Signatory',
            'at_signatory_4'  => 'AOC – At 4th Signatory',
            'at_chancellor'   => 'AOC – At Chancellor',
            'fully_signed'    => 'AOC – Fully Signed',
            default           => ucfirst(str_replace('_', ' ', $this->signatory_stage)),
        };
    }

    public static function signatoryStages(): array
    {
        return ['draft', 'at_end_user', 'at_signatory_2', 'at_signatory_3', 'at_signatory_4', 'at_chancellor', 'fully_signed'];
    }

    public function nextSignatoryStage(): ?string
    {
        $stages = self::signatoryStages();
        $idx    = array_search($this->signatory_stage, $stages);
        return ($idx !== false && $idx < count($stages) - 1) ? $stages[$idx + 1] : null;
    }
}
