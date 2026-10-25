<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MarketPrice extends Model
{
    protected $fillable = [
        'uuid',
        'commodity',
        'market',
        'region',
        'price_type',
        'min_price',
        'max_price',
        'avg_price',
        'unit',
        'currency',
        'fx_rate',
        'price_date',
        'source',
        'recorded_by',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'avg_price' => 'decimal:2',
        'fx_rate' => 'decimal:4',
        'price_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($price) {
            if (empty($price->uuid)) {
                $price->uuid = (string) Str::uuid();
            }
        });
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Trend vs the previous recorded price for the same commodity+market.
     */
    public function trend(): string
    {
        $previous = static::where('commodity', $this->commodity)
            ->where('market', $this->market)
            ->when($this->price_type, fn ($query) => $query->where('price_type', $this->price_type))
            ->where('price_date', '<', $this->price_date)
            ->orderByDesc('price_date')
            ->first();

        if (! $previous) {
            return 'stable';
        }

        return match (true) {
            $this->avg_price > $previous->avg_price => 'up',
            $this->avg_price < $previous->avg_price => 'down',
            default => 'stable',
        };
    }
}
