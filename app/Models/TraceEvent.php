<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraceEvent extends Model
{
    use BelongsToTenant;

    /** Events the current holder records on a lot without creating new lots. */
    public const HOLDER_TYPES = ['storage', 'grading', 'quality_check', 'dispatch', 'delivery', 'loss', 'note'];

    /** Every type, including the ones with their own endpoints. */
    public const TYPES = ['harvest', 'handoff', 'storage', 'grading', 'quality_check', 'processing',
        'split', 'merge', 'dispatch', 'delivery', 'loss', 'note'];

    public const PAYMENT_METHODS = ['mobile_money', 'bank', 'cash', 'other'];

    protected $fillable = [
        'tenant_id', 'uuid', 'lot_id', 'type', 'recorded_by', 'recorder_participant_id',
        'from_participant_id', 'to_participant_id', 'transporter_participant_id',
        'quantity', 'unit', 'quantity_kg', 'received_kg', 'loss_kg', 'loss_reason', 'discrepancy_kg',
        'grade', 'moisture_percent', 'quality_notes', 'price_per_kg', 'currency',
        'payment_method', 'payment_reference', 'location_label', 'latitude', 'longitude',
        'warehouse_id', 'freight_request_id', 'notes', 'confirmation_status', 'occurred_at',
    ];

    protected $casts = [
        'quantity' => 'float',
        'quantity_kg' => 'float',
        'received_kg' => 'float',
        'loss_kg' => 'float',
        'discrepancy_kg' => 'float',
        'moisture_percent' => 'float',
        'price_per_kg' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'occurred_at' => 'datetime',
        'sender_confirmed_at' => 'datetime',
        'receiver_confirmed_at' => 'datetime',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(TraceLot::class, 'lot_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function recorderParticipant(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'recorder_participant_id');
    }

    public function fromParticipant(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'from_participant_id');
    }

    public function toParticipant(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'to_participant_id');
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(TraceParticipant::class, 'transporter_participant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function freightRequest(): BelongsTo
    {
        return $this->belongsTo(FreightRequest::class);
    }
}
