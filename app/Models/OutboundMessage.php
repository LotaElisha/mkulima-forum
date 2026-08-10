<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OutboundMessage extends Model
{
    protected $fillable = [
        'uuid', 'channel_driver', 'audience_filter', 'payload', 'status',
        'recipient_count', 'retry_count', 'scheduled_at', 'sent_at', 'error_message',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'payload' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($msg) {
            if (empty($msg->uuid)) {
                $msg->uuid = (string) Str::uuid();
            }
        });
    }

    public function receipts()
    {
        return $this->hasMany(DeliveryReceipt::class, 'message_id');
    }
}
