<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event', 'actor_id', 'anon_id', 'subject_type', 'subject_id',
        'geo_unit_id', 'crop_id', 'channel', 'provenance', 'metadata', 'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public const PROVENANCES = ['REGULATORY', 'PLATFORM', 'AI', 'COMMUNITY'];

    protected static function booted(): void
    {
        static::creating(function ($event) {
            if (empty($event->provenance)) {
                $event->provenance = 'PLATFORM';
            }
            if (empty($event->occurred_at)) {
                $event->occurred_at = now();
            }
        });

        static::updating(function () {
            throw new \LogicException('AnalyticsEvent records are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new \LogicException('AnalyticsEvent records are append-only and cannot be deleted.');
        });
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function geoUnit()
    {
        return $this->belongsTo(GeoUnit::class, 'geo_unit_id');
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class, 'crop_id');
    }
}
