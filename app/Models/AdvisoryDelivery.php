<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvisoryDelivery extends Model
{
    protected $fillable = [
        'advisory_id', 'channel_driver', 'recipient_count', 'status', 'sent_at',
    ];

    protected $casts = [
        'recipient_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function advisory()
    {
        return $this->belongsTo(Advisory::class, 'advisory_id');
    }
}
