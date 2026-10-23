<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShortLinkHostTest extends TestCase
{
    public function test_www_hosts_are_on_the_default_allowlist(): void
    {
        $hosts = config('services.short_links.allowed_hosts');

        $this->assertContains('www.mkulimaforum.app', $hosts);
        $this->assertContains('www.mkulimaforum.com', $hosts);
        $this->assertContains('mkulimaforum.app', $hosts);
        $this->assertContains('mkulimaforum.com', $hosts);
    }
}
