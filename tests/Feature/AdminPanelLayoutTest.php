<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemConfiguration;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Support\Enums\Width;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The admin panel is Swahili-first and uses the full screen width. These
 * checks keep English navigation labels and the narrow centred column from
 * creeping back in.
 */
class AdminPanelLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'admin'): User
    {
        Tenant::firstOrCreate(['country_code' => 'tz'], ['name' => 'Tanzania', 'currency' => 'TZS']);

        return User::factory()->create(['role' => $role, 'status' => 'active']);
    }

    public function test_panel_uses_full_width_content(): void
    {
        $this->assertSame(Width::Full, Filament::getPanel('admin')->getMaxContentWidth());
    }

    public function test_dashboard_shows_swahili_navigation_and_platform_summary(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin')->assertOk();

        foreach (['Huduma', 'Maudhui', 'Wataalamu wa kilimo', 'Vifaa vya kukodi', 'Viungo vya jamii', 'Bei za soko'] as $label) {
            $response->assertSee($label);
        }
        foreach (['Services', 'Equipment', 'Community links', 'Market prices', 'Landing copy'] as $english) {
            $response->assertDontSee('>'.$english.'<', false);
        }

        $response->assertSee('Mkulima Forum')->assertSee('Usimamizi');
    }

    public function test_empty_lists_explain_what_belongs_there(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/equipment-listings')->assertOk()
            ->assertSee('Bado hakuna kifaa cha kukodi');
        $this->actingAs($admin)->get('/admin/agronomists')->assertOk()
            ->assertSee('Bado hakuna mtaalamu wa kilimo');
        $this->actingAs($admin)->get('/admin/market-prices')->assertOk()
            ->assertSee('Bado hakuna bei za soko')->assertSee('Ongeza bei');
        $this->actingAs($admin)->get('/admin/community-channels')->assertOk()
            ->assertSee('Bado hakuna kiungo cha jamii');
        $this->actingAs($admin)->get('/admin/landing-settings')->assertOk()
            ->assertSee('Ukurasa wa mwanzo unatumia maandishi ya kawaida');
    }

    public function test_configuration_page_renders_grouped_swahili_sections(): void
    {
        $super = $this->admin('superadmin');

        Livewire::actingAs($super)->test(SystemConfiguration::class)
            ->assertOk()
            ->assertSee('Jumla')
            ->assertSee('Barua pepe (SMTP)')
            ->assertSee('Nenosiri la SMTP')
            ->assertSee('Hifadhi');
    }
}
