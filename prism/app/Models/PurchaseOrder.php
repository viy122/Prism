<?php

namespace App\Models;

use App\Models\Concerns\HasSignatoryChain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasSignatoryChain, SoftDeletes;

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
        'payment_processing_at',
        'remarks',
        'file_path',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'           => 'decimal:2',
            'issued_at'              => 'datetime',
            'expected_delivery_date' => 'date',
            'paid_at'                => 'datetime',
            'payment_processing_at'  => 'datetime',
            'uploaded_at'            => 'datetime',
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

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(DocumentUpload::class, 'attachable');
    }

    public function signatureLogs(): HasMany
    {
        return $this->hasMany(PoSignatureLog::class);
    }

    // ── Signatory chain ──────────────────────────────────────────────────────

    public const SIGNATORY_DOC_PREFIX = 'PO';

    public const SIGNATORY_STAGES = [
        ['key' => 'draft',         'label' => 'Created',    'type' => 'routing'],
        ['key' => 'at_accounting', 'label' => 'Accounting', 'type' => 'signature', 'role' => 'accounting-office'],
        ['key' => 'at_chancellor', 'label' => 'Chancellor', 'type' => 'signature', 'role' => 'chancellor'],
        ['key' => 'at_supplier',   'label' => 'Supplier',   'type' => 'signature'],
        ['key' => 'fully_signed',  'label' => 'Fully Signed', 'type' => 'signature'],
    ];

    // ── Delivery status chain ────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'issued'            => 'Issued to Supplier',
            'awaiting_delivery' => 'Awaiting Delivery (Supply Office)',
            'partial_delivery'  => 'Partial Delivery (Supply Office)',
            'complete_delivery' => 'Complete Delivery (Supply Office)',
            'processing_payment' => 'At Accounting – Processing Payment',
            'paid'              => 'Paid – Payment Made (Cashier)',
            default             => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public static function statusChain(): array
    {
        return ['issued', 'awaiting_delivery', 'partial_delivery', 'complete_delivery', 'processing_payment', 'paid'];
    }

    public function nextStatus(): ?string
    {
        $chain = self::statusChain();
        $idx   = array_search($this->status, $chain);
        return ($idx !== false && $idx < count($chain) - 1) ? $chain[$idx + 1] : null;
    }
}
