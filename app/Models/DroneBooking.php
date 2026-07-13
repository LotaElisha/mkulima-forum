<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DroneBooking extends Model
{
    protected $fillable = [
        'uuid',
        'tenant_id',
        'user_id',
        'service_id',
        'farm_location',
        'farm_size_acres',
        'preferred_date',
        'contact_phone',
        'notes',
        'total_cost',
        'status',
    ];

    protected $casts = [
        'farm_size_acres' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'preferred_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($booking) {
            if (empty($booking->uuid)) {
                $booking->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
