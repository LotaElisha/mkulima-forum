<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceBooking extends Model
{
    protected $fillable = [
        'tenant_id', 'service_provider_id', 'farmer_id', 'uuid', 'booking_type',
        'description', 'media', 'scheduled_at', 'location', 'fee', 'status',
        'provider_notes', 'results', 'farmer_rating', 'farmer_review',
    ];

    protected $casts = [
        'media' => 'array',
        'results' => 'array',
        'scheduled_at' => 'datetime',
        'fee' => 'decimal:2',
        'farmer_rating' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($booking) {
            if (empty($booking->uuid)) {
                $booking->uuid = (string) Str::uuid();
            }
        });
    }

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }
}
