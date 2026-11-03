<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EquipmentListing extends Model
{
    /** Category key => Swahili label. Keys are the API contract. */
    public const CATEGORIES = [
        'tractor' => 'Trekta',
        'power_tiller' => 'Power tiller',
        'plough' => 'Jembe la kukokotwa',
        'planter' => 'Mashine ya kupanda',
        'sprayer' => 'Bomba la kunyunyizia',
        'harvester' => 'Mashine ya kuvuna',
        'thresher' => 'Mashine ya kupura',
        'water_pump' => 'Pampu ya maji',
        'trailer' => 'Tela',
        'other' => 'Nyingine',
    ];

    public const STATUSES = ['active', 'paused', 'hidden'];

    protected $fillable = [
        'tenant_id', 'owner_id', 'category', 'title', 'description', 'daily_rate',
        'with_operator', 'region', 'district', 'latitude', 'longitude', 'status',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'with_operator' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentListing $listing) {
            $listing->uuid = $listing->uuid ?: (string) Str::uuid();
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rentals()
    {
        return $this->hasMany(EquipmentRental::class);
    }
}
