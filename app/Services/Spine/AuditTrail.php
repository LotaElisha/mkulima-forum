<?php

namespace App\Services\Spine;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditTrail
{
    /**
     * Record an immutable audit log entry for any Eloquent model or system action.
     */
    public function record(
        Model $subject,
        string $event,
        ?array $before = null,
        ?array $after = null,
        ?User $actor = null,
        string $source = 'web'
    ): AuditLog {
        $actorId = $actor ? $actor->id : auth()->id();

        $log = new AuditLog([
            'auditable_type' => get_class($subject),
            'auditable_id' => $subject->getKey(),
            'actor_id' => $actorId,
            'event' => $event,
            'before' => $before,
            'after' => $after,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'source' => $source,
            'occurred_at' => now(),
        ]);

        $log->save();

        return $log;
    }
}
