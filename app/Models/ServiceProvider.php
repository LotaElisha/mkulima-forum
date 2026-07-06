<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceProvider extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'uuid', 'service_type', 'business_name', 'bio',
        'specializations', 'region', 'district', 'license_number',
        'verification_status', 'consultation_fee', 'visit_fee', 'availability',
        'rating', 'rating_count', 'is_active',
    ];

    protected $casts = [
        'specializations' => 'array',
        'availability' => 'array',
        'consultation_fee' => 'decimal:2',
        'visit_fee' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($provider) {
            if (empty($provider->uuid)) {
                $provider->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }
}
