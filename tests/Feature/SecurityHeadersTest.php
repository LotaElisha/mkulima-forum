<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_public_pages_use_nonce_based_script_policy(): void
    {
        $response = $this->get('/verify')->assertOk();
        $policy = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self' 'nonce-", $policy);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
    }

    public function test_admin_is_the_filament_panel_not_a_static_spa(): void
    {
        $this->assertFileDoesNotExist(public_path('admin/index.html'));

        // Guests are sent to the panel login. A path the panel does not
        // define is a 404, not the old React bundle.
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/settings')->assertNotFound();
    }
}
