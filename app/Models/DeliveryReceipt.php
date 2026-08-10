<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryReceipt extends Model
{
    protected $fillable = [
        'message_id', 'user_id', 'recipient_identifier', 'channel_driver',
        'status', 'external_id', 'details', 'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(OutboundMessage::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
