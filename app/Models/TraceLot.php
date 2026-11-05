<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One traceable quantity of a crop. Splits, merges and processing make new
 * lots linked to their parents through trace_lot_links; a parent lot and its
 * events are kept as they were.
 */
class TraceLot extends Model
{
    use BelongsToTenant;

    /** Lots in these states can still have things recorded against them. */
    public const OPEN_STATUSES = ['active', 'in_transfer'];

    protected $fillable = [
        'tenant_id', 'uuid', 'code', 'crop', 'product', 'season_id', 'plot_id',
        'origin_participant_id', 'holder_participant_id', 'pending_holder_participant_id',
        'quantity', 'unit', 'quantity_kg', 'grade', 'origin', 'status', 'harvested_on',
        'anomaly_flags', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
        'quantity_kg' => 'float',
        'harvested_on' => 'date',
        'anomaly_flags' => 'array',
        'anomaly_reviewed_at' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(TraceSeason::class, 'season_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(TracePlot::class, 'plot_id');
    }

    /** The farmer who grew it. Not `origin`: that column says how the lot was made. */
    public function grower(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'origin_participant_id');
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'holder_participant_id');
    }

    public function pendingHolder(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'pending_holder_participant_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TraceEvent::class, 'lot_id')->orderBy('occurred_at')->orderBy('id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'trace_lot_links', 'child_lot_id', 'parent_lot_id')
            ->withPivot(['relation', 'quantity_kg']);
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'trace_lot_links', 'parent_lot_id', 'child_lot_id')
            ->withPivot(['relation', 'quantity_kg']);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function hasOpenAnomaly(): bool
    {
        return ! empty($this->anomaly_flags) && $this->anomaly_reviewed_at === null;
    }
}
