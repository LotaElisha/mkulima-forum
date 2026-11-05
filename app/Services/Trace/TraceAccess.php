<?php

namespace App\Services\Trace;

use App\Models\TraceEvent;
use App\Models\TraceLot;
use App\Models\TraceParticipant;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Who may see what of a lot.
 *
 * - full: an admin, or a participant who has held, sent, received or carried
 *   this lot or any lot it came from or went into. Sees the whole timeline
 *   with names. Phone numbers and plot coordinates still need the farmer's
 *   consent unless the viewer is an admin.
 * - participant: any other active participant. Sees the timeline with
 *   participant types and verification, but no names, phones or locations
 *   finer than region.
 * - public: anyone with the QR code. Crop, region, month of harvest, and
 *   how many recorded steps; nothing that identifies a person.
 */
final class TraceAccess
{
    public const FULL = 'full';

    public const PARTICIPANT = 'participant';

    public const PUBLIC = 'public';

    public static function participant(?User $user): ?TraceParticipant
    {
        if ($user === null) {
            return null;
        }

        return TraceParticipant::withoutGlobalScopes()->where('user_id', $user->id)->first();
    }

    public static function isAdmin(?User $user): bool
    {
        return $user !== null && in_array($user->role, ['admin', 'superadmin'], true);
    }

    /** Every lot this one came from, nearest first, without repeats. */
    public static function ancestors(TraceLot $lot, int $depth = 25): Collection
    {
        $seen = collect([$lot->id]);
        $out = collect();
        $frontier = collect([$lot]);
        while ($frontier->isNotEmpty() && $depth-- > 0) {
            $parents = TraceLot::withoutGlobalScopes()
                ->whereIn('id', function ($q) use ($frontier) {
                    $q->select('parent_lot_id')->from('trace_lot_links')->whereIn('child_lot_id', $frontier->pluck('id'));
                })
                ->whereNotIn('id', $seen)
                ->get();
            $seen = $seen->merge($parents->pluck('id'));
            $out = $out->merge($parents);
            $frontier = $parents;
        }

        return $out;
    }

    /** Every lot made from this one, at any depth. */
    public static function descendants(TraceLot $lot, int $depth = 25): Collection
    {
        $seen = collect([$lot->id]);
        $out = collect();
        $frontier = collect([$lot]);
        while ($frontier->isNotEmpty() && $depth-- > 0) {
            $children = TraceLot::withoutGlobalScopes()
                ->whereIn('id', function ($q) use ($frontier) {
                    $q->select('child_lot_id')->from('trace_lot_links')->whereIn('parent_lot_id', $frontier->pluck('id'));
                })
                ->whereNotIn('id', $seen)
                ->get();
            $seen = $seen->merge($children->pluck('id'));
            $out = $out->merge($children);
            $frontier = $children;
        }

        return $out;
    }

    /** Participant ids that appear anywhere in this lot's family. */
    public static function chainParticipantIds(TraceLot $lot): Collection
    {
        $lots = collect([$lot])->merge(self::ancestors($lot))->merge(self::descendants($lot));
        $lotIds = $lots->pluck('id');

        $fromLots = $lots->flatMap(fn (TraceLot $l) => [
            $l->origin_participant_id, $l->holder_participant_id, $l->pending_holder_participant_id,
        ]);
        $fromEvents = TraceEvent::withoutGlobalScopes()->whereIn('lot_id', $lotIds)
            ->get(['recorder_participant_id', 'from_participant_id', 'to_participant_id', 'transporter_participant_id'])
            ->flatMap(fn (TraceEvent $e) => [
                $e->recorder_participant_id, $e->from_participant_id, $e->to_participant_id, $e->transporter_participant_id,
            ]);

        return $fromLots->merge($fromEvents)->filter()->unique()->values();
    }

    public static function tier(?User $user, TraceLot $lot): string
    {
        if (self::isAdmin($user)) {
            return self::FULL;
        }
        $me = self::participant($user);
        if ($me === null || $me->tenant_id !== $lot->tenant_id) {
            return self::PUBLIC;
        }
        if (self::chainParticipantIds($lot)->contains($me->id)) {
            return self::FULL;
        }

        return $me->isActive() ? self::PARTICIPANT : self::PUBLIC;
    }
}
