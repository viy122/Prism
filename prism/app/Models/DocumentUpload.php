<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by_user_id',
        'attachable_type',
        'attachable_id',
        'document_type',
        'title',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'remarks',
        'extracted_fields_json',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'extracted_fields_json' => 'array',
            'uploaded_at' => 'datetime',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
