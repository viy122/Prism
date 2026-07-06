<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketPriceSnapshot extends Model
{
    protected $fillable = [
        'product_name',
        'price',
        'original_price',
        'source_site',
        'product_url',
        'department',
        'query_used',
        'scraped_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'scraped_at' => 'datetime',
        ];
    }
}
