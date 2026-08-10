<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    protected $fillable = [
        'product_id', 'batch_id', 'gtin', 'internal_serial', 'manufacturer_serial',
        'current_holder_type', 'current_holder_id', 'is_duplicate_detected',
    ];

    protected $casts = [
        'is_duplicate_detected' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(RegulatedProduct::class, 'product_id');
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
