<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Advisory extends Model
{
    protected $fillable = [
        'uuid', 'type', 'title', 'body', 'geo_unit_ids', 'crop_ids', 'topic_ids',
        'farmer_type_filter', 'channel_targets', 'status', 'composed_by',
        'approved_by', 'scheduled_at', 'sent_at',
    ];

    protected $casts = [
        'title' => 'array',
        'body' => 'array',
        'geo_unit_ids' => 'array',
        'crop_ids' => 'array',
        'topic_ids' => 'array',
        'channel_targets' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($advisory) {
            if (empty($advisory->uuid)) {
                $advisory->uuid = (string) Str::uuid();
            }
        });
    }

    public function composer()
    {
        return $this->belongsTo(User::class, 'composed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deliveries()
    {
        return $this->hasMany(AdvisoryDelivery::class, 'advisory_id');
    }
}
