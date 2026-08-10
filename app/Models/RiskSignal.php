<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskSignal extends Model
{
    protected $fillable = [
        'product_id', 'scan_id', 'signal_type', 'value', 'weight', 'provenance', 'occurred_at',
    ];

    protected $casts = [
        'value' => 'float',
        'weight' => 'float',
        'occurred_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(RegulatedProduct::class, 'product_id');
    }

    public function scan()
    {
        return $this->belongsTo(VerificationScan::class, 'scan_id');
    }
}
