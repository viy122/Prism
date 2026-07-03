<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'abstract_of_canvass_id',
        'created_by_user_id',
        'po_number',
        'supplier_name',
        'supplier_address',
        'total_amount',
        'status',
        'signatory_stage',
        'issued_at',
        'expected_delivery_date',
        'paid_by_user_id',
        'paid_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'           => 'decimal:2',
            'issued_at'              => 'datetime',
            'expected_delivery_date' => 'date',
            'paid_at'                => 'datetime',
        ];
    }

    public function abstractOfCanvass(): BelongsTo
    {
        return $this->belongsTo(AbstractOfCanvass::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function signatureLogs(): HasMany
    {
        return $this->hasMany(PoSignatureLog::class);
    }

    // ── Signatory chain ──────────────────────────────────────────────────────

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

    public function getSignatoryLabelAttribute(): string
    {
        return match ($this->signatory_stage) {
            'draft'          => 'PO Created',
            'at_end_user'    => 'PO – At End User (1st Signatory)',
            'at_signatory_2' => 'PO – At 2nd Signatory',
            'at_signatory_3' => 'PO – At 3rd Signatory',
            'at_signatory_4' => 'PO – At 4th Signatory',
            'at_chancellor'  => 'PO – At Chancellor',
            'fully_signed'   => 'PO – Fully Signed',
            default          => ucfirst(str_replace('_', ' ', $this->signatory_stage ?? 'draft')),
        };
    }

    // ── Delivery status chain ────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'issued'            => 'PO Issued to Supplier',
            'awaiting_delivery' => 'Awaiting Delivery',
            'partial_delivery'  => 'Partial Delivery',
            'complete_delivery' => 'Complete Delivery',
            'receipt_uploaded'  => 'Delivery Receipt Uploaded',
            'paid'              => 'Payment Made',
            default             => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public static function statusChain(): array
    {
        return ['issued', 'awaiting_delivery', 'partial_delivery', 'complete_delivery', 'receipt_uploaded', 'paid'];
    }

    public function nextStatus(): ?string
    {
        $chain = self::statusChain();
        $idx   = array_search($this->status, $chain);
        return ($idx !== false && $idx < count($chain) - 1) ? $chain[$idx + 1] : null;
    }
}
