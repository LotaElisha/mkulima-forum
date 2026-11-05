<?php

namespace App\Services\Trace;

use App\Models\TraceEvent;
use App\Models\TraceLot;
use App\Models\TraceParticipant;
use App\Models\TracePlot;
use App\Models\TraceSeason;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Turns trace rows into what one viewer may see. The privacy rules live here:
 *
 * - The public sees crop, region, month of harvest and how many steps were
 *   recorded; never a name, phone, code of a person, price or coordinates.
 * - Other participants see the steps with participant types and whether an
 *   admin has verified them, but no names, prices or locations below region.
 * - People in the lot's chain see the whole history. A farmer's name is shown
 *   only to the farmer, an admin, or someone who dealt with them directly; a
 *   phone number and plot coordinates only with the farmer's consent.
 *
 * Payment details are what a participant typed. They are always labelled as
 * recorded, never as paid: Mkulima Trace does not move or check money.
 */
final class TracePresenter
{
    private ?TraceParticipant $me;

    private bool $admin;

    /** @var array<int, Collection<int, int>> participant id => ids they dealt with directly */
    private array $counterparties = [];

    public function __construct(private ?User $user)
    {
        $this->me = TraceAccess::participant($user);
        $this->admin = TraceAccess::isAdmin($user);
    }

    public function participant(TraceParticipant $p, bool $self = false): array
    {
        $out = [
            'uuid' => $p->uuid,
            'code' => $p->code,
            'type' => $p->type,
            'status' => $p->status,
            'verified' => $p->isVerified(),
            'region' => $p->region,
            'organisation_name' => $p->organisation_name,
            'name' => $p->displayName(),
            'qr_url' => TraceCode::url($p->code),
        ];
        if ($self) {
            $out['share_contact_with_chain'] = $p->share_contact_with_chain;
            $out['review_note'] = $p->status === 'active' ? null : $p->review_note;
        }

        return $out;
    }

    /** What anyone may learn from a participant code before handing them a lot. */
    public function participantLookup(TraceParticipant $p): array
    {
        return [
            'code' => $p->code,
            'type' => $p->type,
            'verified' => $p->isVerified(),
            'active' => $p->isActive(),
            'region' => $p->region,
            // A business name helps the sender check they typed the right code;
            // a farmer's personal name stays private.
            'name' => $p->type === 'farmer' ? null : $p->organisation_name,
        ];
    }

    public function plot(TracePlot $plot): array
    {
        return [
            'uuid' => $plot->uuid,
            'code' => $plot->code,
            'name' => $plot->name,
            'area_acres' => $plot->area_acres,
            'region' => $plot->region,
            'district' => $plot->district,
            'village' => $plot->village,
            'lat' => $plot->latitude,
            'lng' => $plot->longitude,
            'share_location_with_chain' => $plot->share_location_with_chain,
            'qr_url' => TraceCode::url($plot->code),
            'seasons' => $plot->relationLoaded('seasons')
                ? $plot->seasons->map(fn (TraceSeason $s) => $this->season($s))->values()
                : [],
        ];
    }

    public function season(TraceSeason $s): array
    {
        return [
            'uuid' => $s->uuid,
            'crop' => $s->crop,
            'season_label' => $s->season_label,
            'planted_on' => $s->planted_on?->toDateString(),
            'expected_harvest_on' => $s->expected_harvest_on?->toDateString(),
            'status' => $s->status,
            'observations' => $s->relationLoaded('observations') ? $s->observations->count() : null,
        ];
    }

    /** A row in a list of lots the viewer holds or once held. */
    public function lotSummary(TraceLot $lot): array
    {
        return [
            'uuid' => $lot->uuid,
            'code' => $lot->code,
            'crop' => $lot->crop,
            'product' => $lot->product,
            'quantity' => $lot->quantity,
            'unit' => $lot->unit,
            'quantity_kg' => $lot->quantity_kg,
            'grade' => $lot->grade,
            'origin' => $lot->origin,
            'status' => $lot->status,
            'harvested_on' => $lot->harvested_on?->toDateString(),
            'i_hold' => $this->me !== null && $lot->holder_participant_id === $this->me->id,
            'awaiting_me' => $this->me !== null && $lot->pending_holder_participant_id === $this->me->id,
            'needs_review' => $lot->hasOpenAnomaly(),
            'qr_url' => TraceCode::url($lot->code),
            'updated_at' => $lot->updated_at?->toIso8601String(),
        ];
    }

    /** The whole record of a lot, cut to the viewer's tier. */
    public function lot(TraceLot $lot): array
    {
        $tier = TraceAccess::tier($this->user, $lot);
        if ($tier === TraceAccess::PUBLIC) {
            return ['tier' => $tier] + $this->publicSummary($lot);
        }
        $full = $tier === TraceAccess::FULL;
        $lot->loadMissing(['plot', 'season', 'holder.user', 'pendingHolder.user', 'grower.user']);

        $ancestors = TraceAccess::ancestors($lot);
        $chainLots = collect([$lot])->merge($ancestors);
        $events = TraceEvent::withoutGlobalScopes()
            ->whereIn('lot_id', $chainLots->pluck('id'))
            ->with(['fromParticipant.user', 'toParticipant.user', 'transporter.user', 'recorderParticipant.user', 'lot:id,code,uuid'])
            ->orderBy('occurred_at')->orderBy('id')
            ->get();
        if ($full) {
            $this->loadCounterparties($events);
        }

        $plot = $lot->plot;

        return [
            'tier' => $tier,
            'uuid' => $lot->uuid,
            'code' => $lot->code,
            'qr_url' => TraceCode::url($lot->code),
            'crop' => $lot->crop,
            'product' => $lot->product,
            'quantity' => $lot->quantity,
            'unit' => $lot->unit,
            'quantity_kg' => $lot->quantity_kg,
            'grade' => $lot->grade,
            'origin' => $lot->origin,
            'status' => $lot->status,
            'harvested_on' => $lot->harvested_on?->toDateString(),
            'season' => $full && $lot->season ? [
                'uuid' => $lot->season->uuid,
                'crop' => $lot->season->crop,
                'season_label' => $lot->season->season_label,
            ] : null,
            'plot' => $plot ? $this->plotForChain($plot, $full) : null,
            'grower' => $this->party($lot->grower, $full),
            'holder' => $this->party($lot->holder, $full),
            'pending_holder' => $this->party($lot->pendingHolder, $full),
            'i_hold' => $this->me !== null && $lot->holder_participant_id === $this->me->id,
            'awaiting_me' => $this->me !== null && $lot->pending_holder_participant_id === $this->me->id,
            'parents' => $lot->parents()->withoutGlobalScopes()->get()->map(fn (TraceLot $p) => $this->link($p))->values(),
            'children' => $lot->children()->withoutGlobalScopes()->get()->map(fn (TraceLot $c) => $this->link($c))->values(),
            'timeline' => $events->map(fn (TraceEvent $e) => $this->event($e, $full, $e->lot_id !== $lot->id))->values(),
            'anomalies' => $full ? $this->anomalies($lot) : null,
            'notice' => __('trace.record_notice'),
        ];
    }

    /** What the QR code shows to anyone. Nothing in it identifies a person. */
    public function publicSummary(TraceLot $lot): array
    {
        $lot->loadMissing('plot');
        $chain = collect([$lot])->merge(TraceAccess::ancestors($lot));
        $steps = TraceEvent::withoutGlobalScopes()->whereIn('lot_id', $chain->pluck('id'))->get(['type', 'confirmation_status']);
        $handoffs = $steps->where('type', 'handoff');

        return [
            'code' => $lot->code,
            'crop' => $lot->crop,
            'product' => $lot->product,
            'grade' => $lot->grade,
            'region' => $lot->plot?->region,
            'harvest_month' => $lot->harvested_on?->format('Y-m'),
            'status' => $lot->status,
            'steps' => $steps->count(),
            'handoffs' => $handoffs->count(),
            'handoffs_confirmed' => $handoffs->where('confirmation_status', 'confirmed')->count(),
            'notice' => __('trace.record_notice'),
        ];
    }

    public function event(TraceEvent $e, bool $full = true, bool $inherited = false): array
    {
        $base = [
            'uuid' => $e->uuid,
            'type' => $e->type,
            'lot_code' => $e->lot?->code,
            'inherited' => $inherited,
            'occurred_at' => $e->occurred_at?->toIso8601String(),
            'recorded_at' => $e->created_at?->toIso8601String(),
            'quantity' => $e->quantity,
            'unit' => $e->unit,
            'quantity_kg' => $e->quantity_kg,
            'received_kg' => $e->received_kg,
            'loss_kg' => $e->loss_kg,
            'loss_reason' => $full ? $e->loss_reason : null,
            'discrepancy_kg' => $e->discrepancy_kg,
            'grade' => $e->grade,
            'moisture_percent' => $e->moisture_percent,
            'confirmation_status' => $e->confirmation_status,
            'sender_confirmed' => $e->sender_confirmed_at !== null,
            'receiver_confirmed' => $e->receiver_confirmed_at !== null,
            'recorded_by' => $this->party($e->recorderParticipant, $full),
            'from' => $this->party($e->fromParticipant, $full),
            'to' => $this->party($e->toParticipant, $full),
            'transporter' => $this->party($e->transporter, $full),
            'awaiting_me' => $this->awaitingMe($e),
        ];
        if (! $full) {
            return $base;
        }

        return $base + [
            'quality_notes' => $e->quality_notes,
            'location_label' => $e->location_label,
            'notes' => $e->notes,
            'dispute_reason' => $e->dispute_reason,
            'has_evidence' => $e->evidence_path !== null,
            'price_per_kg' => $e->price_per_kg,
            'currency' => $e->currency,
            // Typed by a participant. Shown as recorded, never as paid.
            'payment' => $e->payment_method || $e->payment_reference ? [
                'method' => $e->payment_method,
                'reference' => $e->payment_reference,
                'verified' => false,
                'note' => __('trace.payment_recorded_only'),
            ] : null,
            'freight_uuid' => $e->freight_request_id ? $e->freightRequest?->uuid : null,
        ];
    }

    private function awaitingMe(TraceEvent $e): bool
    {
        if ($this->me === null || $e->confirmation_status !== 'pending') {
            return false;
        }

        return ($e->to_participant_id === $this->me->id && $e->receiver_confirmed_at === null)
            || ($e->from_participant_id === $this->me->id && $e->sender_confirmed_at === null);
    }

    /** A participant as one viewer may see them. Null in, null out. */
    private function party(?TraceParticipant $p, bool $full): ?array
    {
        if ($p === null) {
            return null;
        }
        $out = [
            'type' => $p->type,
            'verified' => $p->isVerified(),
            'region' => $p->region,
        ];
        if (! $full) {
            return $out;
        }

        $self = $this->me !== null && $this->me->id === $p->id;
        $showName = $p->type !== 'farmer' || $this->admin || $self || $this->dealtWith($p);
        $out['code'] = $p->code;
        $out['name'] = $showName ? $p->displayName() : null;
        $out['phone'] = ($this->admin || $self || ($p->share_contact_with_chain && $showName))
            ? $p->user?->phone : null;

        return $out;
    }

    private function plotForChain(TracePlot $plot, bool $full): array
    {
        $showPoint = $this->admin
            || ($this->me !== null && $plot->participant_id === $this->me->id)
            || ($full && $plot->share_location_with_chain);

        return [
            'region' => $plot->region,
            'district' => $full ? $plot->district : null,
            'village' => $full && $showPoint ? $plot->village : null,
            'area_acres' => $full ? $plot->area_acres : null,
            'lat' => $showPoint ? $plot->latitude : null,
            'lng' => $showPoint ? $plot->longitude : null,
        ];
    }

    private function link(TraceLot $l): array
    {
        return [
            'uuid' => $l->uuid,
            'code' => $l->code,
            'crop' => $l->crop,
            'product' => $l->product,
            'quantity_kg' => $l->quantity_kg,
            'status' => $l->status,
            'relation' => $l->pivot?->relation,
            'link_kg' => $l->pivot ? (float) $l->pivot->quantity_kg : null,
        ];
    }

    private function anomalies(TraceLot $lot): ?array
    {
        if (empty($lot->anomaly_flags)) {
            return null;
        }

        return [
            'flags' => collect($lot->anomaly_flags)->pluck('flag')->unique()->values(),
            'reviewed' => $lot->anomaly_reviewed_at !== null,
        ];
    }

    /** Who in these events dealt directly with whom (sender, receiver, carrier). */
    private function loadCounterparties(Collection $events): void
    {
        foreach ($events as $e) {
            $ids = collect([$e->from_participant_id, $e->to_participant_id, $e->transporter_participant_id])->filter();
            foreach ($ids as $id) {
                $this->counterparties[$id] = ($this->counterparties[$id] ?? collect())->merge($ids)->unique();
            }
        }
    }

    private function dealtWith(TraceParticipant $p): bool
    {
        return $this->me !== null && ($this->counterparties[$p->id] ?? collect())->contains($this->me->id);
    }
}
