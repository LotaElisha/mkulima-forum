<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerificationScan extends Model
{
    protected $fillable = [
        'uuid', 'scanner_id', 'scan_method', 'raw_input', 'product_id',
        'geo_unit_id', 'is_offline', 'occurred_at',
    ];

    protected $casts = [
        'is_offline' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($scan) {
            if (empty($scan->uuid)) {
                $scan->uuid = (string) Str::uuid();
            }
            if (empty($scan->occurred_at)) {
                $scan->occurred_at = now();
            }
        });
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanner_id');
    }

    public function product()
    {
        return $this->belongsTo(RegulatedProduct::class, 'product_id');
    }

    public function result()
    {
        return $this->hasOne(VerificationResult::class, 'scan_id');
    }
}
