<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Agrodealer extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'business_name', 'owner_name', 'business_registration',
        'tin', 'business_licence', 'physical_address', 'geo_unit_id', 'gps_lat',
        'gps_lng', 'regulator_licence_number', 'authority_id', 'licence_expiry',
        'status', 'kyc_documents', 'verified_at', 'expires_at',
    ];

    protected $casts = [
        'gps_lat' => 'float',
        'gps_lng' => 'float',
        'licence_expiry' => 'date',
        'kyc_documents' => 'array',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUSES = [
        'PENDING', 'DOCUMENTS_SUBMITTED', 'MKULIMA_VERIFIED', 'REGULATOR_RECORD_MATCHED',
        'VERIFICATION_FAILED', 'SUSPENDED', 'EXPIRED',
    ];

    protected static function booted(): void
    {
        static::creating(function ($dealer) {
            if (empty($dealer->uuid)) {
                $dealer->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function geoUnit()
    {
        return $this->belongsTo(GeoUnit::class, 'geo_unit_id');
    }

    public function authority()
    {
        return $this->belongsTo(RegulatoryAuthority::class, 'authority_id');
    }
}
