<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_tokens_feed_the_web_and_the_app(): void
    {
        $tokens = json_decode(file_get_contents(base_path('design/tokens.json')), true, 512, JSON_THROW_ON_ERROR);
        $primary = $tokens['color']['primary']['value'];

        $this->assertStringContainsString($primary, file_get_contents(public_path('css/mk-tokens.css')));
        $this->assertStringContainsString(
            '0xFF'.strtoupper(ltrim($primary, '#')),
            file_get_contents(base_path('mkulima_app/lib/core/mk_tokens.dart'))
        );
        $this->assertStringContainsString('MkTokens.colorPrimary', file_get_contents(base_path('mkulima_app/lib/core/theme.dart')));
    }

    public function test_the_home_page_leads_with_live_tools_and_marks_the_rest(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bei za soko', false)
            ->assertSee('Hali ya hewa', false)
            ->assertSee('Inakuja hivi karibuni', false)
            ->assertSee('Jukwaa', false)
            ->assertDontSee('120K', false);
    }

    public function test_forum_read_and_sign_in_use_the_same_shell(): void
    {
        $this->get('/forum')->assertOk()->assertSee('Kuandika swali kitapatiwa baadaye', false);
        $this->get('/login')->assertOk()->assertSee('Ingia', false);
        $this->get('/register')->assertOk()->assertSee('mk-btn', false);
        $this->get('/forgot-password')->assertOk()->assertSee('Tuma kiungo', false);
        $this->get('/reset-password/example-token')->assertOk()->assertSee('Hifadhi nenosiri', false);
        $this->get('/email/verify')->assertOk()->assertSee('Thibitisha barua pepe', false);
        $this->get('/contact')->assertOk()->assertSee('Tuma ujumbe', false);
        $this->get('/about')->assertOk()->assertSee('Soko, malipo, utambuzi wa magonjwa', false);
        $this->get('/solutions')->assertOk()->assertSee('Soko, malipo, utambuzi wa magonjwa', false);
        $this->get('/partners')->assertOk()->assertSee('Soko, malipo, utambuzi wa magonjwa', false);
        $this->get('/technology')->assertOk()->assertSee('Soko, malipo, utambuzi wa magonjwa', false);
        $this->get('/pitch-deck')->assertOk()->assertSee('Soko, malipo, utambuzi wa magonjwa', false);
    }
}
