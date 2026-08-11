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

    public function test_admin_spa_deep_links_return_the_admin_entrypoint(): void
    {
        $this->assertFileExists(public_path('admin/index.html'));
        $this->get('/admin/settings')->assertOk();
    }
}
