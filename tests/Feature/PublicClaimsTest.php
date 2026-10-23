<?php

namespace Tests\Feature;

use App\Support\AppDownload;
use Tests\TestCase;

class PublicClaimsTest extends TestCase
{
    public function test_the_homepage_does_not_claim_an_unverified_farmer_count(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('120K', false)
            ->assertDontSee('120k', false);
    }

    public function test_the_download_page_has_no_apk_link_when_no_build_is_published(): void
    {
        $this->get('/download')
            ->assertOk()
            ->assertDontSee('href="/app/', false)
            ->assertDontSee('href="/storage/', false)
            ->assertSee('halijachapishwa', false);
    }

    public function test_a_non_zip_file_is_not_offered_as_an_apk(): void
    {
        $dir = public_path('app');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.'/not-a-real-build.apk';
        file_put_contents($path, 'this is not an apk');

        try {
            $this->assertNull(AppDownload::android());
            $this->get('/download')
                ->assertOk()
                ->assertDontSee('not-a-real-build.apk', false);
        } finally {
            @unlink($path);
        }
    }

    public function test_a_real_apk_archive_is_offered(): void
    {
        $dir = public_path('app');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.'/phase0-build.apk';
        file_put_contents($path, "PK\x03\x04".'phase0');

        try {
            $build = AppDownload::android();
            $this->assertNotNull($build);
            $this->assertSame('/app/phase0-build.apk', $build['url']);

            $this->get('/download')
                ->assertOk()
                ->assertSee('/app/phase0-build.apk', false);
        } finally {
            @unlink($path);
        }
    }
}
