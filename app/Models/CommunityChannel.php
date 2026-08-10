<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CommunityChannel extends Model
{
    protected $fillable = [
        'uuid', 'platform', 'channel_type', 'name', 'slug', 'description',
        'url', 'phone_number', 'default_greeting', 'icon', 'language',
        'geo_unit_id', 'crop_id', 'topic_id', 'is_official', 'is_featured',
        'is_active', 'is_alert_channel', 'sort_order', 'provenance',
        'expires_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'description' => 'array',
        'default_greeting' => 'array',
        'is_official' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_alert_channel' => 'boolean',
        'sort_order' => 'integer',
        'expires_at' => 'datetime',
    ];

    public const PLATFORMS = [
        'facebook', 'instagram', 'x_twitter', 'tiktok', 'youtube',
        'linkedin', 'threads', 'telegram', 'whatsapp', 'whatsapp_channel',
        'whatsapp_group', 'whatsapp_community', 'custom',
    ];

    public const CHANNEL_TYPES = [
        'WHATSAPP_BUSINESS', 'WHATSAPP_CHANNEL', 'WHATSAPP_GROUP',
        'WHATSAPP_COMMUNITY', 'SOCIAL', 'CUSTOM',
    ];

    protected static function booted(): void
    {
        static::creating(function ($channel) {
            if (empty($channel->uuid)) {
                $channel->uuid = (string) Str::uuid();
            }
            if (empty($channel->slug)) {
                $channel->slug = Str::slug($channel->name);
            }
            if (empty($channel->provenance)) {
                $channel->provenance = 'PLATFORM';
            }
        });
    }

    /**
     * Auto-generate click-to-chat URL for WHATSAPP_BUSINESS types (B7)
     */
    public function getClickToChatUrlAttribute(): ?string
    {
        if ($this->channel_type === 'WHATSAPP_BUSINESS' && !empty($this->phone_number)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone_number);
            $greeting = $this->default_greeting['sw'] ?? $this->default_greeting['en'] ?? 'Habari Mkulima Forum, nahitaji msaada...';
            return "https://wa.me/{$cleanPhone}?text=" . urlencode($greeting);
        }

        return $this->url;
    }

    public function geoUnit()
    {
        return $this->belongsTo(GeoUnit::class, 'geo_unit_id');
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class, 'crop_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function clicks()
    {
        return $this->hasMany(CommunityChannelClick::class, 'channel_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfficial($query)
    {
        return $query->where('is_official', true);
    }

    public function scopeAlertChannels($query)
    {
        return $query->where('is_alert_channel', true)->where('is_active', true);
    }
}
