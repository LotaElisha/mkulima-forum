<?php

namespace App\Models;

use App\Services\Trace\TraceCode;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TracePlot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'participant_id', 'farm_id', 'uuid', 'name', 'area_acres', 'region',
        'district', 'village', 'latitude', 'longitude', 'share_location_with_chain',
    ];

    protected $casts = [
        'area_acres' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'share_location_with_chain' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $plot) {
            $plot->uuid ??= (string) Str::uuid();
            $plot->code ??= TraceCode::unique(self::class);
        });
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'participant_id');
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(TraceSeason::class, 'plot_id');
    }
}
