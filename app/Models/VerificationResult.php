<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationResult extends Model
{
    protected $fillable = [
        'scan_id', 'status', 'provenance', 'confidence', 'reasons',
        'recommended_action', 'risk_score', 'as_of',
    ];

    protected $casts = [
        'confidence' => 'integer',
        'risk_score' => 'integer',
        'reasons' => 'array',
        'recommended_action' => 'array',
        'as_of' => 'datetime',
    ];

    public const STATUSES = [
        'VERIFIED',
        'REGISTERED_SOURCE_CONFIRMED',
        'COMMUNITY_SUPPLIER_RECORD',
        'UNVERIFIED',
        'SUSPICIOUS',
        'RECALLED',
        'SUSPENDED',
        'EXPIRED',
    ];

    public function scan()
    {
        return $this->belongsTo(VerificationScan::class, 'scan_id');
    }
}
