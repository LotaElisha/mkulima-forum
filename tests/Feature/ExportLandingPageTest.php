<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportLandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_static_home_page_renders_when_the_database_is_empty(): void
    {
        $path = storage_path('framework/testing/landing.html');

        $this->artisan('mkulima:export-landing', ['path' => $path])
            ->assertSuccessful();

        $html = file_get_contents($path);
        $this->assertIsString($html);
        $this->assertStringContainsString('Bei za soko', $html);
        $this->assertStringContainsString('Bado hakuna bei', $html);
        $this->assertStringContainsString('Dar es Salaam', $html);

        @unlink($path);
    }
}
