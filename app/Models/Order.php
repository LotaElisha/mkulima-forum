<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Order extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'buyer_id',
        'seller_id',
        'uuid',
        'status',
        'subtotal',
        'delivery_fee',
        'total',
        'currency',
        'delivery_address',
        'delivery_phone',
        'notes',
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'delivery_address' => 'array',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function escrow()
    {
        return $this->hasOne(Escrow::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['paid', 'cancelled'],
            'paid' => ['shipped', 'refunded'],
            'shipped' => ['delivered', 'disputed'],
            'delivered' => ['finalized', 'disputed'],
            'disputed' => ['refunded', 'arbitrated'],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }
}
