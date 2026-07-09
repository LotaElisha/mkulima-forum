<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Warehouse extends Model
{
    protected $fillable = [
        'tenant_id', 'operator_id', 'uuid', 'name', 'storage_type', 'region',
        'location', 'capacity_tons', 'available_tons', 'price_per_ton_month',
        'features', 'verification_status', 'is_active',
    ];

    protected $casts = [
        'capacity_tons' => 'decimal:2',
        'available_tons' => 'decimal:2',
        'price_per_ton_month' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($warehouse) {
            if (empty($warehouse->uuid)) {
                $warehouse->uuid = (string) Str::uuid();
            }
        });
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function bookings()
    {
        return $this->hasMany(WarehouseBooking::class);
    }
}
