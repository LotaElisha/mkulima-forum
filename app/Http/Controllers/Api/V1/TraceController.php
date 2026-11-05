<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Api\V1Response;
use App\Http\Controllers\Controller;
use App\Models\DiseaseScan;
use App\Models\TraceEvent;
use App\Models\TraceLot;
use App\Models\TraceParticipant;
use App\Models\TracePlot;
use App\Models\TraceSeason;
use App\Services\Trace\TraceAccess;
use App\Services\Trace\TraceCode;
use App\Services\Trace\TraceException;
use App\Services\Trace\TracePresenter;
use App\Services\Trace\TraceRecorder;
use App\Services\Trace\Units;
use App\Support\UploadRules;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Validation\Rule;

/**
 * Mkulima Trace — Shamba hadi Soko. Follow a crop lot from the plot to the
 * buyer. Rules live in TraceRecorder; who sees what lives in TracePresenter.
 *
 * Every write takes a UUID made on the phone, so a request replayed from the
 * offline queue returns the row it already wrote, with meta.duplicate = true.
 */
class TraceController extends Controller implements HasMiddleware
{
    public function __construct(private TraceRecorder $recorder) {}

    public static function middleware(): array
    {
        return [
            // Swahili unless the app asks for English, as the other Mkulima APIs do.
            function (Request $request, Closure $next) {
                app()->setLocale(str_starts_with((string) $request->header('Accept-Language', 'sw'), 'en') ? 'en' : 'sw');

                return $next($request);
            },
        ];
    }

    // ── Me, registration and participant lookup ─────────────────────────

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $me = TraceAccess::participant($user);
        $view = new TracePresenter($user);

        $pending = $me ? TraceEvent::where('confirmation_status', 'pending')
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('to_participant_id', $me->id)->whereNull('receiver_confirmed_at'))
                ->orWhere(fn ($q) => $q->where('from_participant_id', $me->id)->whereNull('sender_confirmed_at')))
            ->count() : 0;

        return V1Response::ok([
            'participant' => $me ? $view->participant($me, self: true) : null,
            'pending_confirmations' => $pending,
            'units' => Units::keys(),
            'types' => TraceParticipant::TYPES,
            'payment_methods' => TraceEvent::PAYMENT_METHODS,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(TraceParticipant::TYPES)],
            'organisation_name' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => ! in_array($request->input('type'), ['farmer', 'transporter'], true))],
            'region' => ['nullable', 'string', 'max:64'],
            'share_contact_with_chain' => ['nullable', 'boolean'],
        ]);

        return $this->run(function () use ($request, $data) {
            $user = $request->user();
            $p = $this->recorder->register($user, $data);
            if (array_key_exists('share_contact_with_chain', $data)) {
                $p->share_contact_with_chain = (bool) $data['share_contact_with_chain'];
                $p->save();
            }

            return V1Response::ok((new TracePresenter($user))->participant($p, self: true), [], 201);
        });
    }

    /** Check a code before handing a lot over. Shows type and verification only. */
    public function participant(Request $request, string $code): JsonResponse
    {
        $p = TraceParticipant::where('code', strtoupper($code))->firstOrFail();

        return V1Response::ok((new TracePresenter($request->user()))->participantLookup($p));
    }

    // ── Plots, seasons and Plant AI observations ────────────────────────

    public function plots(Request $request): JsonResponse
    {
        $me = TraceAccess::participant($request->user());
        $plots = $me ? TracePlot::where('participant_id', $me->id)->with('seasons.observations')->latest()->get() : collect();
        $view = new TracePresenter($request->user());

        return V1Response::ok($plots->map(fn (TracePlot $p) => $view->plot($p))->values());
    }

    public function storePlot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:120'],
            'area_acres' => ['nullable', 'numeric', 'min:0.01', 'max:100000'],
            'region' => ['required', 'string', 'max:64'],
            'district' => ['nullable', 'string', 'max:64'],
            'village' => ['nullable', 'string', 'max:64'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'share_location_with_chain' => ['nullable', 'boolean'],
        ]);

        return $this->run(fn () => $this->created(
            (new TracePresenter($request->user()))->plot($this->recorder->createPlot($request->user(), $data)->load('seasons'))
        ));
    }

    public function storeSeason(Request $request, string $uuid): JsonResponse
    {
        $plot = TracePlot::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'crop' => ['required', 'string', 'max:64'],
            'season_label' => ['required', 'string', 'max:64'],
            'planted_on' => ['nullable', 'date', 'before_or_equal:today'],
            'expected_harvest_on' => ['nullable', 'date'],
        ]);

        return $this->run(fn () => $this->created(
            (new TracePresenter($request->user()))->season($this->recorder->createSeason($request->user(), $plot, $data))
        ));
    }

    public function linkObservation(Request $request, string $uuid): JsonResponse
    {
        $season = TraceSeason::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate([
            'scan_uuid' => ['required', 'uuid'],
            'consent' => ['required', 'boolean'],
        ]);
        $scan = DiseaseScan::where('uuid', $data['scan_uuid'])->firstOrFail();

        return $this->run(function () use ($request, $season, $scan, $data) {
            $this->recorder->linkObservation($request->user(), $season, $scan, (bool) $data['consent']);

            return V1Response::ok((new TracePresenter($request->user()))->season($season->load('observations')));
        });
    }

    // ── Lots ────────────────────────────────────────────────────────────

    /** Lots I hold, lots waiting for me, and (?scope=history) lots I once held or grew. */
    public function lots(Request $request): JsonResponse
    {
        $data = $request->validate(['scope' => ['nullable', Rule::in(['current', 'history'])]]);
        $me = TraceAccess::participant($request->user());
        if ($me === null) {
            return V1Response::ok([]);
        }

        $query = TraceLot::query()->latest('updated_at');
        if (($data['scope'] ?? 'current') === 'history') {
            $query->where(fn ($q) => $q
                ->where('origin_participant_id', $me->id)
                ->orWhereIn('id', TraceEvent::select('lot_id')->where(fn ($e) => $e
                    ->where('from_participant_id', $me->id)
                    ->orWhere('to_participant_id', $me->id)
                    ->orWhere('recorder_participant_id', $me->id)
                    ->orWhere('transporter_participant_id', $me->id))));
        } else {
            $query->where(fn ($q) => $q->where('holder_participant_id', $me->id)->orWhere('pending_holder_participant_id', $me->id))
                ->whereIn('status', TraceLot::OPEN_STATUSES);
        }

        $page = $query->paginate(30);
        $view = new TracePresenter($request->user());

        return V1Response::ok(
            collect($page->items())->map(fn (TraceLot $l) => $view->lotSummary($l))->values(),
            ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        );
    }

    public function harvest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'season_uuid' => ['required', 'uuid'],
            'uuid' => ['required', 'uuid'],
            'event_uuid' => ['nullable', 'uuid'],
            'code' => ['nullable', 'string', $this->codeRule()],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:100000000'],
            'unit' => ['required', Rule::in(Units::keys())],
            'harvested_on' => ['nullable', 'date', 'before_or_equal:today'],
        ] + $this->commonRules());
        $season = TraceSeason::where('uuid', $data['season_uuid'])->firstOrFail();

        return $this->run(fn () => $this->lotResponse($request, $this->recorder->harvest($request->user(), $season, $data)));
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $lot = TraceLot::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        // Signed-in viewers see more; the route itself does not need a token.
        return V1Response::ok((new TracePresenter($request->user('sanctum')))->lot($lot));
    }

    /** What a scanned QR code leads to: a lot, a plot or a participant. */
    public function scan(Request $request, string $code): JsonResponse
    {
        $code = TraceCode::fromScan($code) ?? abort(404);
        $view = new TracePresenter($request->user('sanctum'));

        if ($lot = TraceLot::withoutGlobalScopes()->where('code', $code)->first()) {
            return V1Response::ok(['kind' => 'lot', 'lot' => $view->lot($lot)]);
        }
        if ($plot = TracePlot::withoutGlobalScopes()->where('code', $code)->first()) {
            return V1Response::ok(['kind' => 'plot', 'plot' => [
                'code' => $plot->code,
                'region' => $plot->region,
                'crops' => $plot->seasons()->withoutGlobalScopes()->pluck('crop')->unique()->values(),
            ]]);
        }
        $p = TraceParticipant::withoutGlobalScopes()->where('code', $code)->firstOrFail();

        return V1Response::ok(['kind' => 'participant', 'participant' => $view->participantLookup($p)]);
    }

    public function recordEvent(Request $request, string $uuid): JsonResponse
    {
        $lot = $this->lot($uuid);
        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'type' => ['required', Rule::in(TraceEvent::HOLDER_TYPES)],
            'quantity' => ['nullable', 'required_if:type,loss', 'numeric', 'gt:0'],
            'unit' => ['nullable', 'required_with:quantity', Rule::in(Units::keys())],
            'loss_reason' => ['nullable', 'string', 'max:255'],
            'transporter_code' => ['nullable', 'string', $this->codeRule()],
            'warehouse_uuid' => ['nullable', 'uuid'],
            'freight_uuid' => ['nullable', 'uuid'],
            'final' => ['nullable', 'boolean'],
        ] + $this->commonRules());

        return $this->run(fn () => $this->eventResponse($request, $this->recorder->holderEvent($request->user(), $lot, $data)));
    }

    public function handoff(Request $request, string $uuid): JsonResponse
    {
        $lot = $this->lot($uuid);
        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'to_code' => ['nullable', 'string', $this->codeRule()],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', Rule::in(Units::keys())],
            'loss_reason' => ['nullable', 'string', 'max:255'],
            'price_per_kg' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_method' => ['nullable', Rule::in(TraceEvent::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:64'],
            'freight_uuid' => ['nullable', 'uuid'],
        ] + $this->commonRules());

        return $this->run(fn () => $this->eventResponse($request, $this->recorder->handoff($request->user(), $lot, $data)));
    }

    public function split(Request $request, string $uuid): JsonResponse
    {
        $lot = $this->lot($uuid);
        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'parts' => ['required', 'array', 'min:2', 'max:20'],
            'parts.*.uuid' => ['required', 'uuid', 'distinct'],
            'parts.*.code' => ['nullable', 'string', 'distinct', $this->codeRule()],
            'parts.*.quantity' => ['required', 'numeric', 'gt:0'],
            'parts.*.unit' => ['required', Rule::in(Units::keys())],
            'parts.*.grade' => ['nullable', 'string', 'max:32'],
            'loss_reason' => ['nullable', 'string', 'max:255'],
        ] + $this->commonRules());

        return $this->run(function () use ($request, $lot, $data) {
            $children = $this->recorder->split($request->user(), $lot, $data);
            $view = new TracePresenter($request->user());

            return V1Response::ok(
                collect($children)->map(fn (TraceLot $c) => $view->lotSummary($c))->values(),
                $this->recorder->duplicate ? ['duplicate' => true] : [],
                $this->recorder->duplicate ? 200 : 201,
            );
        });
    }

    public function merge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'event_uuid' => ['nullable', 'uuid'],
            'code' => ['nullable', 'string', $this->codeRule()],
            'lot_uuids' => ['required', 'array', 'min:2', 'max:50'],
            'lot_uuids.*' => ['required', 'uuid', 'distinct'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', Rule::in(Units::keys())],
            'loss_reason' => ['nullable', 'string', 'max:255'],
        ] + $this->commonRules());

        return $this->run(fn () => $this->lotResponse($request, $this->recorder->merge($request->user(), $data)));
    }

    public function process(Request $request, string $uuid): JsonResponse
    {
        $lot = $this->lot($uuid);
        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'output_uuid' => ['required', 'uuid', 'different:uuid'],
            'output_code' => ['nullable', 'string', $this->codeRule()],
            'product' => ['required', 'string', 'max:120'],
            'output_quantity' => ['required', 'numeric', 'gt:0'],
            'output_unit' => ['required', Rule::in(Units::keys())],
            'loss_reason' => ['nullable', 'string', 'max:255'],
        ] + $this->commonRules());

        return $this->run(fn () => $this->lotResponse($request, $this->recorder->process($request->user(), $lot, $data)));
    }

    // ── Confirmations ───────────────────────────────────────────────────

    /** Hand-offs waiting for me to confirm. */
    public function pending(Request $request): JsonResponse
    {
        $me = TraceAccess::participant($request->user());
        if ($me === null) {
            return V1Response::ok([]);
        }
        $events = TraceEvent::where('confirmation_status', 'pending')
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('to_participant_id', $me->id)->whereNull('receiver_confirmed_at'))
                ->orWhere(fn ($q) => $q->where('from_participant_id', $me->id)->whereNull('sender_confirmed_at')))
            ->with(['lot', 'fromParticipant.user', 'toParticipant.user', 'recorderParticipant.user', 'transporter.user'])
            ->latest('occurred_at')->limit(100)->get();
        $view = new TracePresenter($request->user());

        return V1Response::ok($events->map(fn (TraceEvent $e) => $view->event($e) + [
            'lot' => $view->lotSummary($e->lot),
        ])->values());
    }

    public function confirm(Request $request, string $uuid): JsonResponse
    {
        $event = TraceEvent::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate([
            'received_quantity' => ['nullable', 'numeric', 'min:0'],
            'received_unit' => ['nullable', 'required_with:received_quantity', Rule::in(Units::keys())],
            'loss_reason' => ['nullable', 'string', 'max:255'],
        ]);
        if ($event->confirmation_status === 'confirmed' && $this->isParty($request, $event)) {
            // A replayed confirmation from the offline queue.
            return $this->eventResponse($request, $event, duplicate: true);
        }

        return $this->run(fn () => $this->eventResponse($request, $this->recorder->confirm($request->user(), $event, $data), updated: true));
    }

    public function dispute(Request $request, string $uuid): JsonResponse
    {
        $event = TraceEvent::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);
        if ($event->confirmation_status === 'disputed' && $this->isParty($request, $event)) {
            return $this->eventResponse($request, $event, duplicate: true);
        }

        return $this->run(fn () => $this->eventResponse($request, $this->recorder->dispute($request->user(), $event, $data['reason']), updated: true));
    }

    /** A photo of a weighbridge ticket, delivery note or receipt. Kept private. */
    public function evidence(Request $request, string $uuid): JsonResponse
    {
        $event = TraceEvent::where('uuid', $uuid)->firstOrFail();
        $request->validate(['file' => ['required', ...UploadRules::rasterOrDocument()]]);

        return $this->run(function () use ($request, $event) {
            if ($event->recorded_by !== $request->user()->id) {
                throw new TraceException(__('trace.not_holder'), 403);
            }
            $path = $request->file('file')->store('trace-evidence/'.$event->tenant_id, 'local');

            return $this->eventResponse($request, $this->recorder->attachEvidence($request->user(), $event, $path), updated: true);
        });
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    private function run(Closure $fn): JsonResponse
    {
        try {
            return $fn();
        } catch (TraceException $e) {
            return V1Response::error($e->getMessage(), $e->errors, $e->status);
        }
    }

    private function lot(string $uuid): TraceLot
    {
        return TraceLot::where('uuid', $uuid)->firstOrFail();
    }

    private function isParty(Request $request, TraceEvent $event): bool
    {
        $me = TraceAccess::participant($request->user());

        return $me !== null && in_array($me->id, [$event->from_participant_id, $event->to_participant_id], true);
    }

    private function created(array $data): JsonResponse
    {
        return $this->recorder->duplicate
            ? V1Response::ok($data, ['duplicate' => true])
            : V1Response::ok($data, [], 201);
    }

    private function lotResponse(Request $request, TraceLot $lot): JsonResponse
    {
        return $this->created((new TracePresenter($request->user()))->lot($lot->fresh()));
    }

    private function eventResponse(Request $request, TraceEvent $event, bool $duplicate = false, bool $updated = false): JsonResponse
    {
        $event = $event->fresh(['lot', 'fromParticipant.user', 'toParticipant.user', 'recorderParticipant.user', 'transporter.user']);
        $view = new TracePresenter($request->user());
        $data = $view->event($event) + ['lot' => $view->lotSummary($event->lot)];

        if ($duplicate) {
            return V1Response::ok($data, ['duplicate' => true]);
        }

        return $updated ? V1Response::ok($data) : $this->created($data);
    }

    private function codeRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! TraceCode::isValid(is_string($value) ? strtoupper($value) : null)) {
                $fail(__('trace.bad_code'));
            }
        };
    }

    private function commonRules(): array
    {
        return [
            'grade' => ['nullable', 'string', 'max:32'],
            'moisture_percent' => ['nullable', 'numeric', 'between:0,100'],
            'quality_notes' => ['nullable', 'string', 'max:500'],
            'location_label' => ['nullable', 'string', 'max:120'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
