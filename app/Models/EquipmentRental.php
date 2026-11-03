<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EquipmentRental extends Model
{
    public const STATUSES = ['requested', 'accepted', 'declined', 'cancelled', 'completed'];

    /** Statuses that hold the equipment's dates. */
    public const BLOCKING = ['accepted'];

    protected $fillable = [
        'tenant_id', 'equipment_listing_id', 'renter_id', 'start_date', 'end_date',
        'quoted_total', 'message', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'quoted_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentRental $rental) {
            $rental->uuid = $rental->uuid ?: (string) Str::uuid();
        });
    }

    public function listing()
    {
        return $this->belongsTo(EquipmentListing::class, 'equipment_listing_id');
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }
}
