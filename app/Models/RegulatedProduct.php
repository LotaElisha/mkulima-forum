<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegulatedProduct extends Model
{
    protected $fillable = [
        'uuid', 'category_id', 'authority_id', 'manufacturer_id', 'registration_number',
        'trade_name', 'active_ingredient', 'formulation', 'permitted_crops', 'target_pests',
        'registration_status', 'expiry_date', 'withdrawn_at', 'provenance', 'confidence', 'as_of',
    ];

    protected $casts = [
        'permitted_crops' => 'array',
        'target_pests' => 'array',
        'expiry_date' => 'date',
        'withdrawn_at' => 'datetime',
        'as_of' => 'datetime',
        'confidence' => 'integer',
    ];

    public const STATUSES = [
        'REGISTERED', 'BANNED', 'WITHDRAWN', 'SUSPENDED', 'EXPIRED',
    ];

    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->uuid)) {
                $product->uuid = (string) Str::uuid();
            }
            if (empty($product->provenance)) {
                $product->provenance = 'REGULATORY';
            }
            if (empty($product->as_of)) {
                $product->as_of = now();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function authority()
    {
        return $this->belongsTo(RegulatoryAuthority::class, 'authority_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class, 'product_id');
    }

    public function recalls()
    {
        return $this->hasMany(RecallNotice::class, 'product_id');
    }
}
