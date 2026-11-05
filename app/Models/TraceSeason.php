<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TraceSeason extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'plot_id', 'uuid', 'crop', 'season_label', 'planted_on', 'expected_harvest_on', 'status',
    ];

    protected $casts = [
        'planted_on' => 'date',
        'expected_harvest_on' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $season) {
            $season->uuid ??= (string) Str::uuid();
        });
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(TracePlot::class, 'plot_id');
    }

    public function lots(): HasMany
    {
        return $this->hasMany(TraceLot::class, 'season_id');
    }

    /** Plant AI scans the farmer chose to attach. */
    public function observations(): BelongsToMany
    {
        return $this->belongsToMany(DiseaseScan::class, 'trace_season_observations', 'season_id', 'disease_scan_id')
            ->withPivot(['linked_by', 'consented_at'])
            ->withTimestamps();
    }
}
