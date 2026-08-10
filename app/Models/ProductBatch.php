<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id', 'batch_number', 'manufacturing_date', 'expiry_date', 'quantity', 'status',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(RegulatedProduct::class, 'product_id');
    }

    public function serials()
    {
        return $this->hasMany(ProductSerial::class, 'batch_id');
    }
}
