<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class YieldEstimate extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'crop_type',
        'farm_size_acres',
        'yield_per_acre',
        'estimated_yield_total',
        'yield_unit',
        'price_per_unit',
        'estimated_revenue',
        'method',
    ];

    protected $casts = [
        'farm_size_acres' => 'decimal:2',
        'yield_per_acre' => 'decimal:2',
        'estimated_yield_total' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'estimated_revenue' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($estimate) {
            if (empty($estimate->uuid)) {
                $estimate->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
