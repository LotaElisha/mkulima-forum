<?php

namespace App\Services\Trace;

use App\Models\AuditLog;
use App\Models\DiseaseScan;
use App\Models\FreightRequest;
use App\Models\TraceEvent;
use App\Models\TraceLot;
use App\Models\TraceParticipant;
use App\Models\TracePlot;
use App\Models\TraceSeason;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every write in Mkulima Trace goes through here, so the rules live in one
 * place: who may record what, how quantities reconcile, and that each change
 * leaves an audit entry.
 *
 * Writes are idempotent on the UUID the phone made. Replaying a queued
 * request returns the row already written (and `duplicate` = true) instead of
 * writing it twice. A UUID already used by somebody else is refused.
 */
final class TraceRecorder
{
    /** Set by each write: true when the UUID had already been recorded. */
    public bool $duplicate = false;

    // ── Participants, plots and seasons ─────────────────────────────────

    public function register(User $user, array $data): TraceParticipant
    {
        $existing = TraceAccess::participant($user);
        $type = $data['type'];

        if ($existing) {
            if ($existing->type !== $type) {
                $hasLots = TraceLot::withoutGlobalScopes()->where('holder_participant_id', $existing->id)
                    ->orWhere('origin_participant_id', $existing->id)->exists();
                if ($hasLots) {
                    throw TraceException::field('type', __('trace.type_locked'));
                }
            }
            $before = $existing->only(['type', 'organisation_name', 'region', 'status']);
            $existing->fill([
                'type' => $type,
                'organisation_name' => $data['organisation_name'] ?? $existing->organisation_name,
                'region' => $data['region'] ?? $existing->region,
            ]);
            if ($existing->isDirty('type')) {
                // A new capacity is a new claim; it needs checking again.
                $existing->status = $type === 'farmer' ? 'active' : 'pending';
                $existing->verified_at = null;
                $existing->verified_by = null;
            }
            $existing->save();
            $this->audit($existing, $user, 'trace.participant.updated', $before, $existing->only(array_keys($before)));

            return $existing;
        }

        $participant = new TraceParticipant([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'type' => $type,
            'organisation_name' => $data['organisation_name'] ?? null,
            'region' => $data['region'] ?? null,
        ]);
        $participant->status = $type === 'farmer' ? 'active' : 'pending';
        $participant->save();
        $this->audit($participant, $user, 'trace.participant.registered', null, $participant->only(['type', 'status']));

        return $participant;
    }

    public function createPlot(User $user, array $data): TracePlot
    {
        $me = $this->farmer($user);
        if ($plot = $this->existing(TracePlot::class, $data['uuid'] ?? null, fn (TracePlot $p) => $p->participant_id === $me->id)) {
            return $plot;
        }

        $plot = TracePlot::create([
            'tenant_id' => $me->tenant_id,
            'participant_id' => $me->id,
            'farm_id' => $data['farm_id'] ?? null,
            'uuid' => $data['uuid'] ?? null,
            'name' => $data['name'],
            'area_acres' => $data['area_acres'] ?? null,
            'region' => $data['region'],
            'district' => $data['district'] ?? null,
            'village' => $data['village'] ?? null,
            'latitude' => $data['lat'] ?? null,
            'longitude' => $data['lng'] ?? null,
            'share_location_with_chain' => (bool) ($data['share_location_with_chain'] ?? false),
        ]);
        $this->audit($plot, $user, 'trace.plot.created', null, $plot->only(['name', 'region', 'area_acres']));

        return $plot;
    }

    public function createSeason(User $user, TracePlot $plot, array $data): TraceSeason
    {
        $me = $this->farmer($user);
        if ($plot->participant_id !== $me->id) {
            throw new TraceException(__('trace.not_your_plot'), 403);
        }
        if ($season = $this->existing(TraceSeason::class, $data['uuid'] ?? null, fn (TraceSeason $s) => $s->plot_id === $plot->id)) {
            return $season;
        }

        $season = TraceSeason::create([
            'tenant_id' => $plot->tenant_id,
            'plot_id' => $plot->id,
            'uuid' => $data['uuid'] ?? null,
            'crop' => $data['crop'],
            'season_label' => $data['season_label'],
            'planted_on' => $data['planted_on'] ?? null,
            'expected_harvest_on' => $data['expected_harvest_on'] ?? null,
        ]);
        $this->audit($season, $user, 'trace.season.created', null, $season->only(['crop', 'season_label']));

        return $season;
    }

    /** Attach a Plant AI scan to a season. Only the farmer, only their own scan, only with consent. */
    public function linkObservation(User $user, TraceSeason $season, DiseaseScan $scan, bool $consent): void
    {
        $me = $this->farmer($user);
        if ($season->plot->participant_id !== $me->id) {
            throw new TraceException(__('trace.not_your_plot'), 403);
        }
        if ($scan->user_id !== $user->id) {
            throw new TraceException(__('trace.scan_not_yours'), 403);
        }
        if (! $consent) {
            throw TraceException::field('consent', __('trace.consent_required'));
        }
        $season->observations()->syncWithoutDetaching([
            $scan->id => ['linked_by' => $user->id, 'consented_at' => now()],
        ]);
        $this->audit($season, $user, 'trace.season.observation_linked', null, ['disease_scan' => $scan->uuid]);
    }

    // ── Lots ────────────────────────────────────────────────────────────

    /** A harvest creates a lot held by the farmer who grew it. */
    public function harvest(User $user, TraceSeason $season, array $data): TraceLot
    {
        $me = $this->farmer($user);
        $plot = $season->plot;
        if ($plot->participant_id !== $me->id) {
            throw new TraceException(__('trace.not_your_plot'), 403);
        }
        if ($lot = $this->existingLot($data['uuid'], $user)) {
            return $lot;
        }

        return DB::transaction(function () use ($user, $me, $season, $plot, $data) {
            $kg = Units::toKg((float) $data['quantity'], $data['unit']);
            $lot = $this->newLot($user, $data, [
                'crop' => $season->crop,
                'season_id' => $season->id,
                'plot_id' => $plot->id,
                'origin_participant_id' => $me->id,
                'holder_participant_id' => $me->id,
                'quantity' => $data['quantity'],
                'unit' => $data['unit'],
                'quantity_kg' => $kg,
                'grade' => $data['grade'] ?? null,
                'origin' => 'harvest',
                'harvested_on' => $data['harvested_on'] ?? now()->toDateString(),
                'anomaly_flags' => $this->yieldFlags($season, $plot, $kg),
            ]);

            $this->event($user, $me, $lot, $data['event_uuid'] ?? null, [
                'type' => 'harvest',
                'to_participant_id' => $me->id,
                'quantity' => $data['quantity'],
                'unit' => $data['unit'],
                'quantity_kg' => $kg,
                'grade' => $data['grade'] ?? null,
                'moisture_percent' => $data['moisture_percent'] ?? null,
                'location_label' => $data['location_label'] ?? null,
                'notes' => $data['notes'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? null,
            ]);

            return $lot;
        });
    }

    /** Storage, grading, quality checks, dispatch, delivery, losses and notes. */
    public function holderEvent(User $user, TraceLot $lot, array $data): TraceEvent
    {
        if ($event = $this->existingEvent($data['uuid'], $user)) {
            return $event;
        }
        $me = $this->active($user);
        $type = $data['type'];

        // A named transporter may log dispatch and delivery on a lot they carry.
        $isCarrier = in_array($type, ['dispatch', 'delivery'], true)
            && $me->type === 'transporter'
            && $lot->events()->where('type', 'dispatch')->where('transporter_participant_id', $me->id)->exists();
        if (! $isCarrier) {
            $this->assertHolder($me, $lot);
        } elseif (! $lot->isOpen()) {
            throw new TraceException(__('trace.lot_closed'), 409);
        }

        return DB::transaction(function () use ($user, $me, $lot, $data, $type) {
            $attrs = $this->common($data) + ['type' => $type];

            if ($type === 'loss') {
                $lossKg = Units::toKg((float) $data['quantity'], $data['unit']);
                if ($lossKg > $lot->quantity_kg + Units::tolerance($lot->quantity_kg)) {
                    throw TraceException::field('quantity', __('trace.too_much'));
                }
                if (blank($data['loss_reason'] ?? null)) {
                    throw TraceException::field('loss_reason', __('trace.loss_reason_required', ['kg' => $lossKg]));
                }
                $attrs += [
                    'quantity' => $data['quantity'], 'unit' => $data['unit'], 'quantity_kg' => $lossKg,
                    'loss_kg' => $lossKg, 'loss_reason' => $data['loss_reason'],
                ];
                $this->setQuantity($lot, max(0, $lot->quantity_kg - $lossKg), $user, 'loss');
            }

            if ($type === 'dispatch' && ! empty($data['transporter_code'])) {
                $attrs['transporter_participant_id'] = $this->counterparty($data['transporter_code'], $me, 'transporter_code')->id;
            }
            if (! empty($data['freight_uuid'])) {
                $attrs['freight_request_id'] = $this->freight($user, $data['freight_uuid'])->id;
            }
            if (! empty($data['warehouse_uuid'])) {
                $attrs['warehouse_id'] = Warehouse::where('uuid', $data['warehouse_uuid'])->firstOrFail()->id;
            }
            if ($type === 'grading' && ! empty($data['grade'])) {
                $before = $lot->grade;
                $lot->grade = $data['grade'];
                $lot->save();
                $this->audit($lot, $user, 'trace.lot.graded', ['grade' => $before], ['grade' => $lot->grade]);
            }
            if ($type === 'delivery' && ! empty($data['final'])) {
                $lot->status = 'delivered';
                $lot->save();
            }

            return $this->event($user, $me, $lot, $data['uuid'], $attrs);
        });
    }

    /**
     * Start a hand-off. The holder records it naming the receiver, or the
     * receiver records it after taking the goods. Custody moves only once the
     * other party confirms: until then the lot is "in transfer".
     */
    public function handoff(User $user, TraceLot $lot, array $data): TraceEvent
    {
        if ($event = $this->existingEvent($data['uuid'], $user)) {
            return $event;
        }
        $me = $this->active($user);
        if (! $lot->isOpen()) {
            throw new TraceException(__('trace.lot_closed'), 409);
        }
        if ($lot->status === 'in_transfer') {
            throw new TraceException(__('trace.lot_in_transfer'), 409);
        }

        if ($lot->holder_participant_id === $me->id) {
            $to = $this->counterparty($data['to_code'] ?? '', $me, 'to_code');
            $from = $me;
            $side = 'sender';
        } else {
            // The receiver records it. They must know the lot's code (they
            // scanned it) and name themselves as receiver implicitly.
            $from = $lot->holder;
            $to = $me;
            $side = 'receiver';
        }
        if ($from->id === $to->id) {
            throw TraceException::field('to_code', __('trace.self_handoff'));
        }

        $sentKg = Units::toKg((float) $data['quantity'], $data['unit']);
        if ($sentKg > $lot->quantity_kg + Units::tolerance($lot->quantity_kg)) {
            throw TraceException::field('quantity', __('trace.too_much'));
        }
        $shortfall = $lot->quantity_kg - $sentKg;
        if ($shortfall > Units::tolerance($lot->quantity_kg) && blank($data['loss_reason'] ?? null)) {
            // Handing over less than the lot holds: say why, or split first.
            throw TraceException::field('loss_reason', __('trace.loss_reason_required', ['kg' => round($shortfall, 3)]));
        }

        return DB::transaction(function () use ($user, $me, $lot, $data, $from, $to, $side, $sentKg, $shortfall) {
            $attrs = $this->common($data) + [
                'type' => 'handoff',
                'from_participant_id' => $from->id,
                'to_participant_id' => $to->id,
                'quantity' => $data['quantity'],
                'unit' => $data['unit'],
                'quantity_kg' => $sentKg,
                'price_per_kg' => $data['price_per_kg'] ?? null,
                'currency' => isset($data['price_per_kg']) ? ($data['currency'] ?? 'TZS') : null,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_reference' => $data['payment_reference'] ?? null,
                'confirmation_status' => 'pending',
            ];
            if ($shortfall > Units::tolerance($lot->quantity_kg)) {
                $attrs['loss_kg'] = round($shortfall, 3);
                $attrs['loss_reason'] = $data['loss_reason'];
            }
            if ($side === 'receiver') {
                $attrs['received_kg'] = $sentKg;
            }
            if (! empty($data['freight_uuid'])) {
                $attrs['freight_request_id'] = $this->freight($user, $data['freight_uuid'])->id;
            }

            $event = $this->event($user, $me, $lot, $data['uuid'], $attrs, confirmSide: $side);

            $lot->status = 'in_transfer';
            $lot->pending_holder_participant_id = $to->id;
            $lot->save();

            return $event;
        });
    }

    /** The other party confirms a hand-off. Custody moves here. */
    public function confirm(User $user, TraceEvent $event, array $data): TraceEvent
    {
        $me = $this->active($user);
        $side = $this->awaitingSide($event, $me);
        $lot = $event->lot;

        return DB::transaction(function () use ($user, $event, $data, $side, $lot) {
            $before = $event->only(['confirmation_status', 'received_kg']);

            if ($side === 'receiver') {
                $receivedKg = isset($data['received_quantity'])
                    ? Units::toKg((float) $data['received_quantity'], $data['received_unit'] ?? $event->unit)
                    : (float) $event->quantity_kg;
                $event->received_kg = $receivedKg;
                $event->receiver_confirmed_at = now();
                $diff = round((float) $event->quantity_kg - $receivedKg, 3);
                if (Units::differs((float) $event->quantity_kg, $receivedKg)) {
                    if (filled($data['loss_reason'] ?? null)) {
                        $event->loss_kg = round(($event->loss_kg ?? 0) + max(0, $diff), 3);
                        $event->loss_reason = trim(($event->loss_reason ? $event->loss_reason.'; ' : '').$data['loss_reason']);
                    } else {
                        // Accepted, but nobody has explained the gap: an admin will see it.
                        $event->discrepancy_kg = $diff;
                        $this->flag($lot, 'unexplained_discrepancy', ['event' => $event->uuid, 'kg' => $diff]);
                    }
                }
            } else {
                $event->sender_confirmed_at = now();
            }

            $event->confirmation_status = 'confirmed';
            $event->save();

            $lot->holder_participant_id = $event->to_participant_id;
            $lot->pending_holder_participant_id = null;
            $lot->status = 'active';
            $lot->save();
            $this->setQuantity($lot, (float) ($event->received_kg ?? $event->quantity_kg), $user, 'handoff');

            $this->audit($event, $user, 'trace.event.confirmed', $before, $event->only(['confirmation_status', 'received_kg', 'discrepancy_kg']));

            return $event;
        });
    }

    /** The other party says the hand-off did not happen as recorded. Custody stays put. */
    public function dispute(User $user, TraceEvent $event, string $reason): TraceEvent
    {
        $me = $this->active($user);
        $this->awaitingSide($event, $me);

        return DB::transaction(function () use ($user, $event, $reason) {
            $event->confirmation_status = 'disputed';
            $event->dispute_reason = $reason;
            $event->save();

            $lot = $event->lot;
            $lot->status = 'active';
            $lot->pending_holder_participant_id = null;
            $lot->save();
            $this->flag($lot, 'disputed_handoff', ['event' => $event->uuid]);

            $this->audit($event, $user, 'trace.event.disputed', ['confirmation_status' => 'pending'], ['confirmation_status' => 'disputed', 'reason' => $reason]);

            return $event;
        });
    }

    /**
     * Split one lot into several. The parts plus any recorded loss must add
     * up to the parent; the parent is kept, marked split, with its history.
     *
     * @return list<TraceLot>
     */
    public function split(User $user, TraceLot $lot, array $data): array
    {
        if ($this->existingEvent($data['uuid'], $user)) {
            return $this->childrenOf($data['uuid']);
        }
        $me = $this->active($user);
        $this->assertHolder($me, $lot);

        $parts = collect($data['parts'])->map(fn ($p) => $p + ['kg' => Units::toKg((float) $p['quantity'], $p['unit'])]);
        $this->reconcile($lot->quantity_kg, $parts->sum('kg'), $data['loss_reason'] ?? null);

        return DB::transaction(function () use ($user, $me, $lot, $data, $parts) {
            $lossKg = max(0, round($lot->quantity_kg - $parts->sum('kg'), 3));
            $event = $this->event($user, $me, $lot, $data['uuid'], $this->common($data) + [
                'type' => 'split',
                'quantity' => $lot->quantity,
                'unit' => $lot->unit,
                'quantity_kg' => $lot->quantity_kg,
                'loss_kg' => $lossKg > 0 ? $lossKg : null,
                'loss_reason' => $lossKg > 0 ? $data['loss_reason'] : null,
            ]);

            $children = [];
            foreach ($parts as $part) {
                $child = $this->newLot($user, $part, [
                    'crop' => $lot->crop,
                    'product' => $lot->product,
                    'season_id' => $lot->season_id,
                    'plot_id' => $lot->plot_id,
                    'origin_participant_id' => $lot->origin_participant_id,
                    'holder_participant_id' => $me->id,
                    'quantity' => $part['quantity'],
                    'unit' => $part['unit'],
                    'quantity_kg' => $part['kg'],
                    'grade' => $part['grade'] ?? $lot->grade,
                    'origin' => 'split',
                    'harvested_on' => $lot->harvested_on,
                ]);
                $child->parents()->attach($lot->id, ['relation' => 'split', 'quantity_kg' => $part['kg'], 'event_id' => $event->id]);
                $children[] = $child;
            }

            $this->close($lot, 'split', $user);

            return $children;
        });
    }

    /** Combine several lots of one crop into a new lot. The sources are kept, marked merged. */
    public function merge(User $user, array $data): TraceLot
    {
        if ($lot = $this->existingLot($data['uuid'], $user)) {
            return $lot;
        }
        $me = $this->active($user);
        $sources = TraceLot::whereIn('uuid', $data['lot_uuids'])->get();
        if ($sources->count() !== count(array_unique($data['lot_uuids']))
            || $sources->pluck('crop')->unique()->count() !== 1
            || $sources->contains(fn (TraceLot $l) => $l->holder_participant_id !== $me->id || $l->status !== 'active')) {
            throw TraceException::field('lot_uuids', __('trace.merge_mismatch'));
        }

        $inKg = round($sources->sum('quantity_kg'), 3);
        $outKg = Units::toKg((float) $data['quantity'], $data['unit']);
        $this->reconcile($inKg, $outKg, $data['loss_reason'] ?? null);

        return DB::transaction(function () use ($user, $me, $data, $sources, $inKg, $outKg) {
            $merged = $this->newLot($user, $data, [
                'crop' => $sources->first()->crop,
                'product' => $sources->first()->product,
                'origin_participant_id' => $sources->pluck('origin_participant_id')->unique()->count() === 1
                    ? $sources->first()->origin_participant_id : null,
                'holder_participant_id' => $me->id,
                'quantity' => $data['quantity'],
                'unit' => $data['unit'],
                'quantity_kg' => $outKg,
                'grade' => $data['grade'] ?? null,
                'origin' => 'merge',
            ]);
            $lossKg = max(0, round($inKg - $outKg, 3));
            $event = $this->event($user, $me, $merged, $data['event_uuid'] ?? null, $this->common($data) + [
                'type' => 'merge',
                'quantity' => $data['quantity'],
                'unit' => $data['unit'],
                'quantity_kg' => $outKg,
                'loss_kg' => $lossKg > 0 ? $lossKg : null,
                'loss_reason' => $lossKg > 0 ? $data['loss_reason'] : null,
            ]);
            foreach ($sources as $source) {
                $merged->parents()->attach($source->id, ['relation' => 'merge', 'quantity_kg' => $source->quantity_kg, 'event_id' => $event->id]);
                $this->close($source, 'merged', $user);
            }

            return $merged;
        });
    }

    /** Processing turns a lot into a new product lot. Mass lost on the way needs a reason. */
    public function process(User $user, TraceLot $lot, array $data): TraceLot
    {
        if ($existing = $this->existingLot($data['output_uuid'], $user)) {
            return $existing;
        }
        $me = $this->active($user);
        $this->assertHolder($me, $lot);

        $outKg = Units::toKg((float) $data['output_quantity'], $data['output_unit']);
        $this->reconcile($lot->quantity_kg, $outKg, $data['loss_reason'] ?? null);

        return DB::transaction(function () use ($user, $me, $lot, $data, $outKg) {
            $lossKg = max(0, round($lot->quantity_kg - $outKg, 3));
            $event = $this->event($user, $me, $lot, $data['uuid'], $this->common($data) + [
                'type' => 'processing',
                'quantity' => $lot->quantity,
                'unit' => $lot->unit,
                'quantity_kg' => $lot->quantity_kg,
                'loss_kg' => $lossKg > 0 ? $lossKg : null,
                'loss_reason' => $lossKg > 0 ? $data['loss_reason'] : null,
            ]);
            $output = $this->newLot($user, ['uuid' => $data['output_uuid'], 'code' => $data['output_code'] ?? null], [
                'crop' => $lot->crop,
                'product' => $data['product'],
                'season_id' => $lot->season_id,
                'plot_id' => $lot->plot_id,
                'origin_participant_id' => $lot->origin_participant_id,
                'holder_participant_id' => $me->id,
                'quantity' => $data['output_quantity'],
                'unit' => $data['output_unit'],
                'quantity_kg' => $outKg,
                'grade' => $data['grade'] ?? null,
                'origin' => 'processing',
                'harvested_on' => $lot->harvested_on,
            ]);
            $output->parents()->attach($lot->id, ['relation' => 'processing', 'quantity_kg' => $lot->quantity_kg, 'event_id' => $event->id]);
            $this->close($lot, 'processed', $user);

            return $output;
        });
    }

    public function attachEvidence(User $user, TraceEvent $event, string $path): TraceEvent
    {
        if ($event->recorded_by !== $user->id) {
            throw new TraceException(__('trace.not_holder'), 403);
        }
        $event->evidence_path = $path;
        $event->save();
        $this->audit($event, $user, 'trace.event.evidence_added', null, ['evidence' => true]);

        return $event;
    }

    public function reviewAnomaly(User $admin, TraceLot $lot): void
    {
        $lot->anomaly_reviewed_at = now();
        $lot->anomaly_reviewed_by = $admin->id;
        $lot->save();
        $this->audit($lot, $admin, 'trace.lot.anomaly_reviewed', null, ['flags' => $lot->anomaly_flags], 'admin');
    }

    public function decideParticipant(User $admin, TraceParticipant $p, string $decision, ?string $note = null): void
    {
        $before = $p->only(['status']);
        $p->status = match ($decision) {
            'approve' => 'active',
            'reject' => 'rejected',
            'suspend' => 'suspended',
        };
        if ($decision === 'approve') {
            $p->verified_at = now();
            $p->verified_by = $admin->id;
        }
        $p->review_note = $note;
        $p->save();
        $this->audit($p, $admin, 'trace.participant.'.$decision, $before, $p->only(['status']), 'admin');
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    private function active(User $user): TraceParticipant
    {
        $me = TraceAccess::participant($user);
        if ($me === null) {
            throw new TraceException(__('trace.not_participant'), 403);
        }
        if (! $me->isActive()) {
            throw new TraceException(__('trace.not_active'), 403);
        }

        return $me;
    }

    private function farmer(User $user): TraceParticipant
    {
        $me = $this->active($user);
        if ($me->type !== 'farmer') {
            throw new TraceException(__('trace.farmers_only'), 403);
        }

        return $me;
    }

    private function assertHolder(TraceParticipant $me, TraceLot $lot): void
    {
        if ($lot->holder_participant_id !== $me->id) {
            throw new TraceException(__('trace.not_holder'), 403);
        }
        if ($lot->status === 'in_transfer') {
            throw new TraceException(__('trace.lot_in_transfer'), 409);
        }
        if (! $lot->isOpen()) {
            throw new TraceException(__('trace.lot_closed'), 409);
        }
    }

    private function counterparty(string $code, TraceParticipant $me, string $field): TraceParticipant
    {
        $other = TraceParticipant::where('code', strtoupper(trim($code)))->first();
        if ($other === null) {
            throw TraceException::field($field, __('trace.unknown_participant'));
        }
        if (! $other->isActive()) {
            throw TraceException::field($field, __('trace.participant_inactive'));
        }
        if ($other->id === $me->id) {
            throw TraceException::field($field, __('trace.self_handoff'));
        }

        return $other;
    }

    private function freight(User $user, string $uuid): FreightRequest
    {
        $freight = FreightRequest::where('uuid', $uuid)->with('transporter')->first();
        $involved = $freight && ($freight->requester_id === $user->id || $freight->transporter?->user_id === $user->id);
        if (! $involved) {
            throw TraceException::field('freight_uuid', __('trace.freight_not_yours'));
        }

        return $freight;
    }

    /** Which side still has to confirm, if it is this participant. */
    private function awaitingSide(TraceEvent $event, TraceParticipant $me): string
    {
        if ($event->confirmation_status !== 'pending') {
            throw new TraceException(__('trace.already_settled'), 409);
        }
        if ($event->receiver_confirmed_at === null && $event->to_participant_id === $me->id) {
            return 'receiver';
        }
        if ($event->sender_confirmed_at === null && $event->from_participant_id === $me->id) {
            return 'sender';
        }
        throw new TraceException(__('trace.not_your_confirmation'), 403);
    }

    private function reconcile(float $inKg, float $outKg, ?string $lossReason): void
    {
        if ($outKg > $inKg + Units::tolerance($inKg)) {
            throw TraceException::field('quantity', __('trace.too_much'));
        }
        $loss = round($inKg - $outKg, 3);
        if ($loss > Units::tolerance($inKg) && blank($lossReason)) {
            throw TraceException::field('loss_reason', __('trace.loss_reason_required', ['kg' => $loss]));
        }
    }

    private function newLot(User $user, array $ids, array $attrs): TraceLot
    {
        $code = isset($ids['code']) ? strtoupper($ids['code']) : null;
        if ($code !== null && TraceLot::withoutGlobalScopes()->where('code', $code)->exists()) {
            throw TraceException::field('code', __('trace.code_taken'), 409);
        }

        $lot = TraceLot::create($attrs + [
            'tenant_id' => $user->tenant_id,
            'uuid' => $ids['uuid'],
            'code' => $code ?? TraceCode::unique(TraceLot::class),
            'status' => 'active',
            'created_by' => $user->id,
        ]);
        $this->audit($lot, $user, 'trace.lot.created', null, $lot->only(['code', 'crop', 'quantity_kg', 'origin']));

        return $lot;
    }

    private function event(User $user, TraceParticipant $me, TraceLot $lot, ?string $uuid, array $attrs, ?string $confirmSide = null): TraceEvent
    {
        $event = new TraceEvent($attrs + [
            'tenant_id' => $lot->tenant_id,
            'lot_id' => $lot->id,
            'recorded_by' => $user->id,
            'recorder_participant_id' => $me->id,
        ]);
        $event->uuid = $uuid ?? (string) Str::uuid();
        $event->occurred_at = isset($attrs['occurred_at']) ? Carbon::parse($attrs['occurred_at'])->min(now()) : now();
        if ($confirmSide === 'sender') {
            $event->sender_confirmed_at = now();
        } elseif ($confirmSide === 'receiver') {
            $event->receiver_confirmed_at = now();
        }
        $event->save();
        $this->audit($event, $user, 'trace.event.recorded', null, $event->only(['type', 'quantity_kg', 'confirmation_status']));

        return $event;
    }

    /** Fields every event type may carry. */
    private function common(array $data): array
    {
        return array_filter([
            'grade' => $data['grade'] ?? null,
            'moisture_percent' => $data['moisture_percent'] ?? null,
            'quality_notes' => $data['quality_notes'] ?? null,
            'location_label' => $data['location_label'] ?? null,
            'latitude' => $data['lat'] ?? null,
            'longitude' => $data['lng'] ?? null,
            'notes' => $data['notes'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? null,
        ], fn ($v) => $v !== null);
    }

    private function setQuantity(TraceLot $lot, float $kg, User $user, string $why): void
    {
        $before = ['quantity' => $lot->quantity, 'quantity_kg' => $lot->quantity_kg];
        $lot->quantity_kg = round($kg, 3);
        $lot->quantity = Units::fromKg($kg, $lot->unit);
        $lot->save();
        if ($before['quantity_kg'] != $lot->quantity_kg) {
            $this->audit($lot, $user, 'trace.lot.quantity_changed', $before, ['quantity' => $lot->quantity, 'quantity_kg' => $lot->quantity_kg, 'because' => $why]);
        }
    }

    private function close(TraceLot $lot, string $status, User $user): void
    {
        $before = $lot->status;
        $lot->status = $status;
        $lot->save();
        $this->audit($lot, $user, 'trace.lot.status_changed', ['status' => $before], ['status' => $status]);
    }

    private function flag(TraceLot $lot, string $flag, array $detail): void
    {
        $flags = $lot->anomaly_flags ?? [];
        $flags[] = ['flag' => $flag, 'at' => now()->toIso8601String()] + $detail;
        $lot->anomaly_flags = $flags;
        $lot->anomaly_reviewed_at = null;
        $lot->save();
    }

    /**
     * Flag, never block, a harvest that looks too large for the plot. Yields
     * vary by crop, irrigation and how the acreage was measured, so a hard cap
     * would refuse honest farmers. An admin reviews the flag instead.
     */
    private function yieldFlags(TraceSeason $season, TracePlot $plot, float $newKg): ?array
    {
        if (! $plot->area_acres || $plot->area_acres <= 0) {
            return null;
        }
        $limit = (float) setting('trace.review_kg_per_acre', 8000);
        $seasonKg = (float) TraceLot::where('season_id', $season->id)->where('origin', 'harvest')->sum('quantity_kg') + $newKg;
        $perAcre = round($seasonKg / $plot->area_acres, 1);

        return $perAcre > $limit
            ? [['flag' => 'high_yield_per_acre', 'kg_per_acre' => $perAcre, 'review_above' => $limit, 'at' => now()->toIso8601String()]]
            : null;
    }

    /**
     * @template T of Model
     *
     * @param  class-string<T>  $model
     * @return T|null
     */
    private function existing(string $model, ?string $uuid, callable $mine): ?Model
    {
        if ($uuid === null) {
            return null;
        }
        $row = $model::withoutGlobalScopes()->where('uuid', $uuid)->first();
        if ($row === null) {
            return null;
        }
        if (! $mine($row)) {
            throw TraceException::field('uuid', __('trace.duplicate_owner'), 409);
        }
        $this->duplicate = true;

        return $row;
    }

    private function existingLot(string $uuid, User $user): ?TraceLot
    {
        return $this->existing(TraceLot::class, $uuid, fn (TraceLot $l) => $l->created_by === $user->id);
    }

    private function existingEvent(string $uuid, User $user): ?TraceEvent
    {
        return $this->existing(TraceEvent::class, $uuid, fn (TraceEvent $e) => $e->recorded_by === $user->id);
    }

    /** @return list<TraceLot> */
    private function childrenOf(string $eventUuid): array
    {
        $event = TraceEvent::withoutGlobalScopes()->where('uuid', $eventUuid)->firstOrFail();

        return TraceLot::withoutGlobalScopes()
            ->whereIn('id', DB::table('trace_lot_links')->where('event_id', $event->id)->pluck('child_lot_id'))
            ->get()->all();
    }

    private function audit(Model $model, User $actor, string $event, ?array $before, ?array $after, string $source = 'api'): void
    {
        AuditLog::create([
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'actor_id' => $actor->id,
            'event' => $event,
            'before' => $before,
            'after' => $after,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 255),
            'source' => $source,
        ]);
    }
}
