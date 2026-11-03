<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FreightRequest extends Model
{
    /** What is being moved. Keys are the API contract. */
    public const CARGO_TYPES = ['produce', 'inputs', 'equipment', 'livestock', 'other'];

    /** Same values as transporters.vehicle_type. */
    public const VEHICLE_TYPES = ['bodaboda', 'bajaji', 'pickup', 'canter', 'lorry', 'refrigerated'];

    protected $fillable = [
        'tenant_id', 'requester_id', 'transporter_id', 'order_id', 'uuid',
        'pickup_location', 'dropoff_location', 'pickup_coords', 'dropoff_coords',
        'cargo_weight_kg', 'cargo_description', 'cargo_type', 'vehicle_type', 'quoted_fare', 'pickup_at',
        'status', 'requester_rating', 'requester_review',
    ];

    protected $casts = [
        'pickup_coords' => 'array',
        'dropoff_coords' => 'array',
        'cargo_weight_kg' => 'decimal:2',
        'quoted_fare' => 'decimal:2',
        'pickup_at' => 'datetime',
        'requester_rating' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($freight) {
            if (empty($freight->uuid)) {
                $freight->uuid = (string) Str::uuid();
            }
        });
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
