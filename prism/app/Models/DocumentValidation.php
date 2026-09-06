<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * The result of checking one document's contents against the document it came
 * from — an audit trail of every cross-document check, and what the routing
 * gate in SignatoryActionService reads to decide whether a document may move
 * forward. See DocumentValidationService for how these are produced.
 */
class DocumentValidation extends Model
{
    use HasFactory;

    // verdict values
    const PASSED     = 'passed';
    const FAILED     = 'failed';
    const UNREADABLE = 'unreadable';

    // pair values — one per link in the procurement chain
    const PAIR_PPMP_PR       = 'ppmp_pr';
    const PAIR_PR_CANVASS    = 'pr_canvass';
    const PAIR_CANVASS_AOC   = 'canvass_aoc';
    const PAIR_AOC_PO        = 'aoc_po';
    const PAIR_PO_ACCOUNTING = 'po_accounting';
    const PAIR_PO_RECEIPT    = 'po_receipt';

    protected $fillable = [
        'validatable_type',
        'validatable_id',
        'source_type',
        'source_id',
        'pair',
        'verdict',
        'score',
        'scope',
        'details_json',
        'validated_by_user_id',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'score'        => 'integer',
            'details_json' => 'array',
            'validated_at' => 'datetime',
        ];
    }

    public function validatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }

    /** Only 'passed' clears a document to move forward. */
    public function blocksRouting(): bool
    {
        return $this->verdict !== self::PASSED;
    }

    /** Short, user-facing reason a document is being held back. */
    public function blockReason(): string
    {
        return match ($this->verdict) {
            self::UNREADABLE => 'The uploaded document could not be read (it looks like a scanned image rather than a real PDF), so its contents could not be checked against the approved PPMP. Re-upload a text-based PDF.',
            self::FAILED     => 'This document did not pass content validation against the document it came from: '
                . implode('; ', array_slice($this->failureReasons(), 0, 3)),
            default          => '',
        };
    }

    /** @return list<string> */
    public function failureReasons(): array
    {
        return collect($this->details_json['items'] ?? [])
            ->where('verdict', self::FAILED)
            ->pluck('reason')
            ->filter()
            ->values()
            ->all();
    }
}
