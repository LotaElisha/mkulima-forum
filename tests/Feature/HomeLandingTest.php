<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The landing page describes the app, but every promise on it is read from
 * the server: a download button only for a published APK, and the scanner
 * shown as live only when an AI key is configured.
 */
class HomeLandingTest extends TestCase
{
    public function test_the_landing_page_describes_the_app_and_its_services(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Kilimo bora, mfukoni mwako.', false)
            ->assertSee('Kagua mimea kwa picha', false)
            ->assertSee('Pembejeo', false)
            ->assertSee('Wataalamu karibu nawe', false)
            ->assertSee('Kodi zana', false)
            ->assertSee('Usafirishaji', false)
            ->assertSee('Maswali yanayoulizwa mara nyingi', false);
    }

    public function test_no_download_button_without_a_published_build(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="/app/', false)
            ->assertDontSee('Pakua app (', false)
            ->assertSee('App ya Android inakuja hivi karibuni', false)
            ->assertSee('Jisajili sasa', false);
    }

    public function test_the_scanner_is_marked_coming_until_an_ai_key_exists(): void
    {
        config(['services.gemini.api_key' => '']);
        $this->get('/')->assertSee('Inakuja hivi karibuni', false);

        config(['services.gemini.api_key' => 'test-key-not-real']);
        $html = $this->get('/')->assertOk()->getContent();

        // The only "coming soon" badge on the page belongs to the scanner card.
        $this->assertStringNotContainsString('<span class="mk-badge">Inakuja hivi karibuni</span>', $html);
    }
}
