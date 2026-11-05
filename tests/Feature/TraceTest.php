<?php

namespace Tests\Feature;

use App\Filament\Resources\TraceLotResource\Pages\ListTraceLots;
use App\Filament\Resources\TraceParticipantResource\Pages\ListTraceParticipants;
use App\Models\AuditLog;
use App\Models\DiseaseScan;
use App\Models\Tenant;
use App\Models\TraceEvent;
use App\Models\TraceLot;
use App\Models\TraceParticipant;
use App\Models\User;
use App\Services\Trace\TraceCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class TraceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $farmer;

    private User $collector;

    private User $processor;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
        $this->farmer = $this->user('Asha Mkulima', '255711000001');
        $this->collector = $this->user('Juma Mkusanyaji', '255711000002');
        $this->processor = $this->user('Neema Msindikaji', '255711000003');
        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id, 'role' => 'admin', 'status' => 'active']);
    }

    private function user(string $name, string $phone): User
    {
        return User::provision(['tenant_id' => $this->tenant->id, 'name' => $name, 'phone' => $phone, 'role' => 'farmer']);
    }

    private function join(User $user, string $type, ?string $org = null): TraceParticipant
    {
        Sanctum::actingAs($user);
        $this->postJson('/api/v1/trace/participants', array_filter([
            'type' => $type, 'organisation_name' => $org, 'region' => 'Morogoro',
        ]))->assertCreated();

        return TraceParticipant::where('user_id', $user->id)->firstOrFail();
    }

    private function approve(TraceParticipant $p): void
    {
        Livewire::actingAs($this->admin)->test(ListTraceParticipants::class)
            ->callTableAction('approve', $p, ['note' => null]);
    }

    /** A farmer with a plot, a season and one harvested lot. */
    private function harvested(float $bags = 10, ?float $acres = 2): TraceLot
    {
        $this->join($this->farmer, 'farmer');
        $plotUuid = (string) Str::uuid();
        $this->postJson('/api/v1/trace/plots', [
            'uuid' => $plotUuid, 'name' => 'Shamba la Mto', 'area_acres' => $acres, 'region' => 'Morogoro',
            'village' => 'Mkambarani', 'lat' => -6.82, 'lng' => 37.66,
        ])->assertCreated();
        $seasonUuid = (string) Str::uuid();
        $this->postJson("/api/v1/trace/plots/{$plotUuid}/seasons", [
            'uuid' => $seasonUuid, 'crop' => 'mahindi', 'season_label' => 'Masika 2026',
        ])->assertCreated();
        $lotUuid = (string) Str::uuid();
        $this->postJson('/api/v1/trace/lots', [
            'season_uuid' => $seasonUuid, 'uuid' => $lotUuid, 'quantity' => $bags, 'unit' => 'bag_90kg', 'grade' => 'A',
        ])->assertCreated();

        return TraceLot::where('uuid', $lotUuid)->firstOrFail();
    }

    private function activeCollector(): TraceParticipant
    {
        $c = $this->join($this->collector, 'collector', 'Ushirika wa Mkambarani');
        $this->approve($c);

        return $c->fresh();
    }

    public function test_farmers_record_at_once_but_businesses_wait_for_an_admin(): void
    {
        $farmer = $this->join($this->farmer, 'farmer');
        $this->assertSame('active', $farmer->status);
        $this->assertNull($farmer->verified_at, 'Registering is not verification.');
        $this->assertTrue(TraceCode::isValid($farmer->code));

        $collector = $this->join($this->collector, 'collector', 'Ushirika wa Mkambarani');
        $this->assertSame('pending', $collector->status);

        // A pending collector cannot receive a lot.
        $lot = $this->harvested();
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", [
            'uuid' => (string) Str::uuid(), 'to_code' => $collector->code, 'quantity' => 10, 'unit' => 'bag_90kg',
        ])->assertStatus(422)->assertJsonValidationErrors('to_code');

        $this->approve($collector);
        $collector->refresh();
        $this->assertSame('active', $collector->status);
        $this->assertNotNull($collector->verified_at);
        $this->assertSame($this->admin->id, $collector->verified_by);
    }

    public function test_a_lot_moves_only_when_the_receiver_confirms(): void
    {
        $collector = $this->activeCollector();
        $lot = $this->harvested();
        $this->assertSame(900.0, $lot->quantity_kg);

        $handoffUuid = (string) Str::uuid();
        $payload = [
            'uuid' => $handoffUuid, 'to_code' => $collector->code, 'quantity' => 10, 'unit' => 'bag_90kg',
            'price_per_kg' => 850, 'payment_method' => 'mobile_money', 'payment_reference' => 'QK12AB34CD',
        ];
        Sanctum::actingAs($this->farmer);
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", $payload)
            ->assertCreated()
            ->assertJsonPath('data.confirmation_status', 'pending')
            ->assertJsonPath('data.payment.verified', false);

        // The offline queue replays the same request: nothing is written twice.
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", $payload)
            ->assertOk()->assertJsonPath('meta.duplicate', true);
        $this->assertSame(1, TraceEvent::where('type', 'handoff')->count());

        $lot->refresh();
        $this->assertSame('in_transfer', $lot->status);
        $this->assertSame($this->participantOf($this->farmer)->id, $lot->holder_participant_id);

        // While in transfer the farmer cannot record anything else on it.
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/events", ['uuid' => (string) Str::uuid(), 'type' => 'storage'])
            ->assertStatus(409);

        // The collector sees it waiting and confirms what the scale showed.
        Sanctum::actingAs($this->collector);
        $this->getJson('/api/v1/trace/pending')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $handoffUuid);
        $this->postJson("/api/v1/trace/events/{$handoffUuid}/confirm", ['received_quantity' => 880, 'received_unit' => 'kg'])
            ->assertOk()
            ->assertJsonPath('data.confirmation_status', 'confirmed')
            ->assertJsonPath('data.discrepancy_kg', 20);

        $lot->refresh();
        $this->assertSame('active', $lot->status);
        $this->assertSame($collector->id, $lot->holder_participant_id);
        $this->assertSame(880.0, $lot->quantity_kg);
        // 20 kg nobody explained: accepted, but flagged for an admin.
        $this->assertTrue($lot->hasOpenAnomaly());
        $this->assertSame('unexplained_discrepancy', $lot->anomaly_flags[0]['flag']);

        // A replayed confirmation is harmless.
        $this->postJson("/api/v1/trace/events/{$handoffUuid}/confirm", [])->assertOk()->assertJsonPath('meta.duplicate', true);

        $this->assertTrue(AuditLog::where('event', 'trace.event.confirmed')->exists());
    }

    public function test_handing_over_less_than_the_lot_needs_a_reason(): void
    {
        $collector = $this->activeCollector();
        $lot = $this->harvested();
        Sanctum::actingAs($this->farmer);

        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", [
            'uuid' => (string) Str::uuid(), 'to_code' => $collector->code, 'quantity' => 8, 'unit' => 'bag_90kg',
        ])->assertStatus(422)->assertJsonValidationErrors('loss_reason');

        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", [
            'uuid' => (string) Str::uuid(), 'to_code' => $collector->code, 'quantity' => 11, 'unit' => 'bag_90kg',
        ])->assertStatus(422)->assertJsonValidationErrors('quantity');

        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", [
            'uuid' => (string) Str::uuid(), 'to_code' => $collector->code, 'quantity' => 8, 'unit' => 'bag_90kg',
            'loss_reason' => 'Magunia 2 yaliharibiwa na mvua',
        ])->assertCreated()->assertJsonPath('data.loss_kg', 180);
    }

    public function test_a_disputed_handoff_leaves_the_lot_with_the_sender(): void
    {
        $collector = $this->activeCollector();
        $lot = $this->harvested();
        $uuid = (string) Str::uuid();
        Sanctum::actingAs($this->farmer);
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", [
            'uuid' => $uuid, 'to_code' => $collector->code, 'quantity' => 10, 'unit' => 'bag_90kg',
        ])->assertCreated();

        // The farmer cannot confirm their own hand-off.
        $this->postJson("/api/v1/trace/events/{$uuid}/confirm")->assertForbidden();

        Sanctum::actingAs($this->collector);
        $this->postJson("/api/v1/trace/events/{$uuid}/dispute", ['reason' => 'Sikupokea mzigo huu'])
            ->assertOk()->assertJsonPath('data.confirmation_status', 'disputed');

        $lot->refresh();
        $this->assertSame('active', $lot->status);
        $this->assertSame($this->participantOf($this->farmer)->id, $lot->holder_participant_id);
        $this->assertNull($lot->pending_holder_participant_id);
    }

    public function test_split_merge_and_processing_keep_the_parents_and_their_history(): void
    {
        $collector = $this->activeCollector();
        $lot = $this->harvested();
        $this->handOver($lot, $this->farmer, $this->collector, $collector->code, 10);

        Sanctum::actingAs($this->collector);
        // Parts that do not add up need a reason.
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/split", [
            'uuid' => (string) Str::uuid(),
            'parts' => [['uuid' => (string) Str::uuid(), 'quantity' => 400, 'unit' => 'kg'], ['uuid' => (string) Str::uuid(), 'quantity' => 400, 'unit' => 'kg']],
        ])->assertStatus(422)->assertJsonValidationErrors('loss_reason');

        $a = (string) Str::uuid();
        $b = (string) Str::uuid();
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/split", [
            'uuid' => (string) Str::uuid(),
            'parts' => [['uuid' => $a, 'quantity' => 500, 'unit' => 'kg', 'grade' => 'A'], ['uuid' => $b, 'quantity' => 400, 'unit' => 'kg', 'grade' => 'B']],
        ])->assertCreated()->assertJsonCount(2, 'data');

        $lot->refresh();
        $this->assertSame('split', $lot->status);
        $this->assertSame(900.0, $lot->quantity_kg, 'The parent keeps its recorded quantity.');
        $this->assertSame(['harvest', 'handoff', 'split'], $lot->events()->pluck('type')->all(), 'Earlier steps are kept.');

        $merged = (string) Str::uuid();
        $this->postJson('/api/v1/trace/lots/merge', [
            'uuid' => $merged, 'lot_uuids' => [$a, $b], 'quantity' => 900, 'unit' => 'kg',
        ])->assertCreated()->assertJsonPath('data.origin', 'merge');

        // Processing 900 kg of maize into 700 kg of flour: the 200 kg needs a reason.
        $this->postJson("/api/v1/trace/lots/{$merged}/process", [
            'uuid' => (string) Str::uuid(), 'output_uuid' => (string) Str::uuid(),
            'product' => 'Unga wa mahindi', 'output_quantity' => 700, 'output_unit' => 'kg',
        ])->assertStatus(422)->assertJsonValidationErrors('loss_reason');

        $flour = (string) Str::uuid();
        $response = $this->postJson("/api/v1/trace/lots/{$merged}/process", [
            'uuid' => (string) Str::uuid(), 'output_uuid' => $flour,
            'product' => 'Unga wa mahindi', 'output_quantity' => 700, 'output_unit' => 'kg',
            'loss_reason' => 'Pumba na unyevu uliopotea wakati wa kusaga',
        ])->assertCreated();

        // The flour's timeline reaches back to the harvest through every parent.
        $types = collect($response->json('data.timeline'))->pluck('type');
        $this->assertSame(['harvest', 'handoff', 'split', 'merge', 'processing'], $types->all());
        $this->assertSame('processing', $response->json('data.parents.0.relation'));
        $this->assertSame('merged', TraceLot::where('uuid', $a)->value('status'));
    }

    public function test_the_qr_shows_the_public_only_a_summary(): void
    {
        $collector = $this->activeCollector();
        $lot = $this->harvested();
        $this->handOver($lot, $this->farmer, $this->collector, $collector->code, 10, price: 850);

        // No account: crop, region, month and counts. No names, phones, prices or coordinates.
        $this->app['auth']->forgetGuards();
        $scan = $this->getJson('/api/v1/trace/scan/'.strtolower($lot->code))->assertOk();
        $this->assertSame('public', $scan->json('data.lot.tier'));
        $this->assertSame('Morogoro', $scan->json('data.lot.region'));
        $this->assertSame(1, $scan->json('data.lot.handoffs_confirmed'));
        $json = $scan->getContent();
        foreach (['Asha', 'Juma', '255711', '-6.82', '850', $collector->code] as $secret) {
            $this->assertStringNotContainsString($secret, $json);
        }

        $page = $this->get('/t/'.$lot->code)->assertOk()->assertSee('Mkulima Trace')->assertSee('mahindi');
        foreach (['Asha', '255711', '-6.82', 'Mkambarani'] as $secret) {
            $page->assertDontSee($secret);
        }
        $page->assertSee('does not prove certification');

        $this->get('/t/'.$lot->code.'/label')->assertOk()->assertSee('<svg', false)->assertSee($lot->code);
        $this->get('/t/ZZZZZZZZZZ')->assertNotFound();
    }

    public function test_people_in_the_chain_see_more_than_other_participants(): void
    {
        $collector = $this->activeCollector();
        $lot = $this->harvested();
        $this->handOver($lot, $this->farmer, $this->collector, $collector->code, 10, price: 850);

        // The collector dealt with the farmer: name and price, but no phone
        // or plot coordinates, because the farmer has not agreed to share them.
        Sanctum::actingAs($this->collector);
        $full = $this->getJson("/api/v1/trace/lots/{$lot->uuid}")->assertOk();
        $this->assertSame('full', $full->json('data.tier'));
        $this->assertSame('Asha Mkulima', $full->json('data.grower.name'));
        $this->assertNull($full->json('data.grower.phone'));
        $this->assertNull($full->json('data.plot.lat'));
        $this->assertEquals(850, $full->json('data.timeline.1.price_per_kg'));
        $this->assertFalse($full->json('data.timeline.1.payment') !== null && $full->json('data.timeline.1.payment.verified'));

        // An active participant outside the chain sees types only.
        $this->join($this->processor, 'processor', 'Kiwanda cha Unga');
        $this->approve($this->participantOf($this->processor));
        Sanctum::actingAs($this->processor);
        $other = $this->getJson("/api/v1/trace/lots/{$lot->uuid}")->assertOk();
        $this->assertSame('participant', $other->json('data.tier'));
        $this->assertSame('farmer', $other->json('data.grower.type'));
        $this->assertArrayNotHasKey('name', $other->json('data.grower'));
        $this->assertArrayNotHasKey('price_per_kg', $other->json('data.timeline.1'));
        $this->assertStringNotContainsString('Asha', $other->getContent());

        // With the farmer's consent, the chain sees the phone and the plot point.
        Sanctum::actingAs($this->farmer);
        $this->postJson('/api/v1/trace/participants', ['type' => 'farmer', 'share_contact_with_chain' => true])->assertCreated();
        Sanctum::actingAs($this->collector);
        $this->getJson("/api/v1/trace/lots/{$lot->uuid}")->assertJsonPath('data.grower.phone', '255711000001');
    }

    public function test_a_uuid_or_code_used_by_someone_else_is_refused(): void
    {
        $lot = $this->harvested();
        $this->join($this->collector, 'farmer');
        Sanctum::actingAs($this->collector);
        $plot = (string) Str::uuid();
        $this->postJson('/api/v1/trace/plots', ['uuid' => $plot, 'name' => 'Shamba', 'region' => 'Pwani'])->assertCreated();
        $season = (string) Str::uuid();
        $this->postJson("/api/v1/trace/plots/{$plot}/seasons", ['uuid' => $season, 'crop' => 'mpunga', 'season_label' => '2026'])->assertCreated();

        $this->postJson('/api/v1/trace/lots', ['season_uuid' => $season, 'uuid' => $lot->uuid, 'quantity' => 1, 'unit' => 'kg'])
            ->assertStatus(409);
        $this->postJson('/api/v1/trace/lots', ['season_uuid' => $season, 'uuid' => (string) Str::uuid(), 'code' => $lot->code, 'quantity' => 1, 'unit' => 'kg'])
            ->assertStatus(409)->assertJsonValidationErrors('code');
        $this->postJson('/api/v1/trace/lots', ['season_uuid' => $season, 'uuid' => (string) Str::uuid(), 'code' => 'BAD-CODE01', 'quantity' => 1, 'unit' => 'kg'])
            ->assertStatus(422)->assertJsonValidationErrors('code');
    }

    public function test_a_large_harvest_is_flagged_for_review_not_refused(): void
    {
        // 200 bags of 90 kg on half an acre is 36,000 kg per acre.
        $lot = $this->harvested(bags: 200, acres: 0.5);
        $this->assertSame('high_yield_per_acre', $lot->anomaly_flags[0]['flag']);
        $this->assertTrue($lot->hasOpenAnomaly());

        Livewire::actingAs($this->admin)->test(ListTraceLots::class)->callTableAction('mark_reviewed', $lot);
        $this->assertFalse($lot->fresh()->hasOpenAnomaly());
    }

    public function test_a_plant_scan_links_to_a_season_only_with_consent(): void
    {
        $this->harvested();
        $season = TraceLot::first()->season;
        $scan = DiseaseScan::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->farmer->id, 'image_path' => 'scans/a.jpg', 'status' => 'completed']);
        $theirs = DiseaseScan::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->collector->id, 'image_path' => 'scans/b.jpg', 'status' => 'completed']);

        Sanctum::actingAs($this->farmer);
        $this->postJson("/api/v1/trace/seasons/{$season->uuid}/observations", ['scan_uuid' => $scan->uuid, 'consent' => false])
            ->assertStatus(422)->assertJsonValidationErrors('consent');
        $this->postJson("/api/v1/trace/seasons/{$season->uuid}/observations", ['scan_uuid' => $theirs->uuid, 'consent' => true])
            ->assertForbidden();
        $this->postJson("/api/v1/trace/seasons/{$season->uuid}/observations", ['scan_uuid' => $scan->uuid, 'consent' => true])
            ->assertOk()->assertJsonPath('data.observations', 1);
    }

    public function test_only_farmers_register_harvests_and_lists_are_scoped_to_the_holder(): void
    {
        $collector = $this->activeCollector();
        Sanctum::actingAs($this->collector);
        $this->postJson('/api/v1/trace/plots', ['uuid' => (string) Str::uuid(), 'name' => 'X', 'region' => 'Pwani'])
            ->assertForbidden();

        $lot = $this->harvested();
        Sanctum::actingAs($this->farmer);
        $this->getJson('/api/v1/trace/lots')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.i_hold', true);
        $this->handOver($lot, $this->farmer, $this->collector, $collector->code, 10);

        Sanctum::actingAs($this->farmer);
        $this->getJson('/api/v1/trace/lots')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/trace/lots?scope=history')->assertOk()->assertJsonCount(1, 'data');
        Sanctum::actingAs($this->collector);
        $this->getJson('/api/v1/trace/lots')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_signing_up_requires_an_account_and_errors_are_in_swahili(): void
    {
        $this->postJson('/api/v1/trace/participants', ['type' => 'farmer'])->assertUnauthorized();
        Sanctum::actingAs($this->farmer);
        $this->withHeader('Accept-Language', 'sw')
            ->postJson('/api/v1/trace/plots', ['uuid' => (string) Str::uuid(), 'name' => 'X', 'region' => 'Pwani'])
            ->assertForbidden()->assertJsonPath('message', 'Jisajili kwanza kwenye Mkulima Trace.');
        $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/v1/trace/plots', ['uuid' => (string) Str::uuid(), 'name' => 'X', 'region' => 'Pwani'])
            ->assertForbidden()->assertJsonPath('message', 'Register for Mkulima Trace first.');
    }

    private function handOver(TraceLot $lot, User $from, User $to, string $toCode, float $bags, ?float $price = null): void
    {
        $uuid = (string) Str::uuid();
        Sanctum::actingAs($from);
        $this->postJson("/api/v1/trace/lots/{$lot->uuid}/handoff", array_filter([
            'uuid' => $uuid, 'to_code' => $toCode, 'quantity' => $bags, 'unit' => 'bag_90kg', 'price_per_kg' => $price,
        ]))->assertCreated();
        Sanctum::actingAs($to);
        $this->postJson("/api/v1/trace/events/{$uuid}/confirm")->assertOk();
    }

    private function participantOf(User $user): TraceParticipant
    {
        return TraceParticipant::withoutGlobalScopes()->where('user_id', $user->id)->firstOrFail();
    }
}
