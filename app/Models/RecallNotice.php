<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecallNotice extends Model
{
    protected $fillable = [
        'product_id', 'authority_id', 'recall_type', 'reason', 'affected_batches',
        'geo_scope', 'issued_at', 'expires_at', 'status', 'provenance',
    ];

    protected $casts = [
        'affected_batches' => 'array',
        'geo_scope' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(RegulatedProduct::class, 'product_id');
    }

    public function authority()
    {
        return $this->belongsTo(RegulatoryAuthority::class, 'authority_id');
    }
}
