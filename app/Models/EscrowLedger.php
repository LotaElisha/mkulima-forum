<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscrowLedger extends Model
{
    use HasFactory;

    protected $table = 'escrow_ledger';

    protected $fillable = [
        'escrow_id',
        'from_status',
        'to_status',
        'triggered_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function escrow()
    {
        return $this->belongsTo(Escrow::class);
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
