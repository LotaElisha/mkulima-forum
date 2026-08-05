<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transporter extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'uuid', 'vehicle_type', 'plate_number',
        'capacity_kg', 'base_region', 'verification_status', 'rating',
        'rating_count', 'is_available',
    ];

    protected $casts = [
        'capacity_kg' => 'decimal:2',
        'rating' => 'decimal:2',
        'rating_count' => 'integer',
        'is_available' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($transporter) {
            if (empty($transporter->uuid)) {
                $transporter->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function freightRequests()
    {
        return $this->hasMany(FreightRequest::class);
    }
}
