<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    protected $primaryKey = 'year';
    public $incrementing = false;
    protected $guarded = [];
    protected function casts(): array
    {
        return ['year' => 'integer', 'is_active' => 'boolean', 'locked_at' => 'datetime'];
    }
}
