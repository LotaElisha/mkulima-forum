<?php

namespace Tests\Feature;

use App\Models\SellerApplication;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Becoming a seller, and not accidentally being offered it.
 *
 * The bug these cover: the app decided whether to show the Seller Dashboard
 * from `role == 'farmer' || role == 'agrodealer'`, which is true for every
 * farmer on the platform, and the endpoint answered 403. The contract now runs
 * the other way - the server states whether the account can sell, and the app
 * draws that - so these tests pin the statement, not the drawing.
 */
class SellerOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private User $farmer;

    private User $admin;

    private User $dealer;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::create([
            'name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS',
        ]);

        $this->farmer = User::provision([
            'tenant_id' => $tenant->id, 'name' => 'Mkulima',
            'phone' => '255710000101', 'role' => Roles::FARMER, 'status' => 'active',
        ]);
        $this->dealer = User::provision([
            'tenant_id' => $tenant->id, 'name' => 'Agrovet',
            'phone' => '255710000102', 'role' => Roles::AGRODEALER, 'status' => 'active',
        ]);
        $this->admin = User::provision([
            'tenant_id' => $tenant->id, 'name' => 'Msimamizi',
            'phone' => '255710000103', 'role' => Roles::ADMIN, 'status' => 'active',
        ]);
    }

    private function validApplication(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Njombe Agrovet',
            'business_type' => 'agrodealer',
            'region' => 'Njombe',
            'contact_phone' => '255710000101',
        ], $overrides);
    }

    public function test_a_plain_farmer_is_told_they_cannot_sell_and_may_apply(): void
    {
        Sanctum::actingAs($this->farmer);

        $this->getJson('/api/seller/status')
            ->assertOk()
            ->assertJson(['seller' => [
                'state' => 'none',
                'can_sell' => false,
                'can_apply' => true,
            ]]);
    }

    public function test_an_agrodealer_can_sell_without_ever_applying(): void
    {
        // Accounts created by an administrator never fill in an application.
        // Role, not the application row, is the authority on selling.
        Sanctum::actingAs($this->dealer);

        $this->getJson('/api/seller/status')
            ->assertOk()
            ->assertJson(['seller' => ['state' => 'approved', 'can_sell' => true]]);

        $this->getJson('/api/seller/dashboard')->assertOk();
    }

    public function test_the_seller_dashboard_refuses_a_farmer_and_says_what_to_do(): void
    {
        Sanctum::actingAs($this->farmer);

        // Still 403 - the client hiding a button is never the enforcement.
        // What changed is that the body now carries the state, so the app can
        // explain it instead of printing a DioException.
        $this->getJson('/api/seller/dashboard')
            ->assertForbidden()
            ->assertJsonPath('seller.state', 'none')
            ->assertJsonPath('seller.can_apply', true);
    }

    public function test_seller_products_and_orders_are_gated_too(): void
    {
        Sanctum::actingAs($this->farmer);

        // These two had no authorization check at all and answered 200 with an
        // empty list, which is indistinguishable from "you have no products".
        $this->getJson('/api/seller/products')->assertForbidden();
        $this->getJson('/api/seller/orders')->assertForbidden();
    }

    public function test_a_farmer_can_apply_and_lands_in_pending(): void
    {
        Sanctum::actingAs($this->farmer);

        $this->postJson('/api/seller/application', $this->validApplication())
            ->assertCreated()
            ->assertJsonPath('seller.state', 'pending')
            ->assertJsonPath('seller.can_apply', false);

        $this->assertDatabaseHas('seller_applications', [
            'user_id' => $this->farmer->id,
            'status' => SellerApplication::PENDING,
        ]);

        // Pending is not permission.
        $this->getJson('/api/seller/dashboard')->assertForbidden();
    }

    public function test_a_second_application_while_one_is_pending_is_refused(): void
    {
        Sanctum::actingAs($this->farmer);

        $this->postJson('/api/seller/application', $this->validApplication())->assertCreated();
        $this->postJson('/api/seller/application', $this->validApplication())
            ->assertStatus(409)
            ->assertJsonPath('seller.state', 'pending');

        $this->assertSame(1, SellerApplication::where('user_id', $this->farmer->id)->count());
    }

    public function test_an_applicant_cannot_approve_themselves(): void
    {
        Sanctum::actingAs($this->farmer);
        $this->postJson('/api/seller/application', $this->validApplication())->assertCreated();

        $application = SellerApplication::where('user_id', $this->farmer->id)->firstOrFail();

        $this->postJson("/api/seller/applications/{$application->uuid}/review", [
            'decision' => 'approve',
        ])->assertForbidden();

        $this->assertSame(Roles::FARMER, $this->farmer->fresh()->role);
    }

    public function test_status_is_not_mass_assignable_on_the_application(): void
    {
        Sanctum::actingAs($this->farmer);

        $this->postJson('/api/seller/application', $this->validApplication([
            'status' => SellerApplication::APPROVED,
        ]))->assertCreated();

        $this->assertSame(
            SellerApplication::PENDING,
            SellerApplication::where('user_id', $this->farmer->id)->value('status'),
        );
    }

    public function test_admin_approval_grants_the_role_and_opens_the_dashboard(): void
    {
        Sanctum::actingAs($this->farmer);
        $this->postJson('/api/seller/application', $this->validApplication())->assertCreated();
        $application = SellerApplication::where('user_id', $this->farmer->id)->firstOrFail();

        Sanctum::actingAs($this->admin);
        $this->postJson("/api/seller/applications/{$application->uuid}/review", [
            'decision' => 'approve',
            'grant_role' => Roles::SELLER,
        ])->assertOk()->assertJsonPath('seller.state', 'approved');

        // Approval and the role must not come apart - an approved application
        // whose user is still a farmer reproduces the original 403 exactly.
        $this->assertSame(Roles::SELLER, $this->farmer->fresh()->role);

        Sanctum::actingAs($this->farmer->fresh());
        $this->getJson('/api/seller/dashboard')->assertOk();
    }

    public function test_rejection_explains_itself_and_allows_reapplying(): void
    {
        Sanctum::actingAs($this->farmer);
        $this->postJson('/api/seller/application', $this->validApplication())->assertCreated();
        $application = SellerApplication::where('user_id', $this->farmer->id)->firstOrFail();

        Sanctum::actingAs($this->admin);
        $this->postJson("/api/seller/applications/{$application->uuid}/review", [
            'decision' => 'reject',
            'reason' => 'Leseni ya biashara haikuambatanishwa.',
        ])->assertOk();

        Sanctum::actingAs($this->farmer->fresh());
        $this->getJson('/api/seller/status')
            ->assertOk()
            ->assertJsonPath('seller.state', 'rejected')
            ->assertJsonPath('seller.can_apply', true)
            ->assertJsonPath('seller.application.rejection_reason', 'Leseni ya biashara haikuambatanishwa.');

        $this->postJson('/api/seller/application', $this->validApplication())
            ->assertCreated()
            ->assertJsonPath('seller.state', 'pending');
    }

    public function test_rejection_requires_a_reason(): void
    {
        Sanctum::actingAs($this->farmer);
        $this->postJson('/api/seller/application', $this->validApplication())->assertCreated();
        $application = SellerApplication::where('user_id', $this->farmer->id)->firstOrFail();

        Sanctum::actingAs($this->admin);
        $this->postJson("/api/seller/applications/{$application->uuid}/review", [
            'decision' => 'reject',
        ])->assertStatus(422);
    }

    public function test_the_authenticated_user_payload_carries_the_seller_state(): void
    {
        // The app reads this on every login and /me call. If it is missing the
        // client has to fall back to guessing from the role string, which is
        // the failure this whole change removes.
        Sanctum::actingAs($this->farmer);

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.seller.can_sell', false)
            ->assertJsonPath('user.seller.state', 'none');
    }
}
