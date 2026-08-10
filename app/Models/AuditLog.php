<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'auditable_type', 'auditable_id', 'actor_id', 'event',
        'before', 'after', 'ip_address', 'user_agent', 'source', 'occurred_at',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($log) {
            if (empty($log->occurred_at)) {
                $log->occurred_at = now();
            }
        });

        static::updating(function () {
            throw new \LogicException('AuditLog entries are immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new \LogicException('AuditLog entries are immutable and cannot be deleted.');
        });
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
