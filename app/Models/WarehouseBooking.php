<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WarehouseBooking extends Model
{
    protected $fillable = [
        'tenant_id', 'warehouse_id', 'farmer_id', 'uuid', 'produce_type',
        'quantity_tons', 'start_date', 'end_date', 'total_cost', 'status',
    ];

    protected $casts = [
        'quantity_tons' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($booking) {
            if (empty($booking->uuid)) {
                $booking->uuid = (string) Str::uuid();
            }
        });
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }
}
