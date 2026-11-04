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

    public function test_the_version_label_comes_from_the_sidecar_beside_the_apk(): void
    {
        config(['app.version' => '9.9.9']);
        $dir = public_path('app');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.'/versioned-build.apk';
        file_put_contents($path, "PK\x03\x04".'versioned');
        touch($path, time() + 60);
        file_put_contents($path.'.json', json_encode(['version' => '1.4.2', 'build' => '17']));

        try {
            $this->assertSame('1.4.2', AppDownload::android()['version']);
            $this->get('/download')
                ->assertOk()
                ->assertSee('Toleo 1.4.2', false)
                ->assertDontSee('9.9.9', false);
        } finally {
            @unlink($path);
            @unlink($path.'.json');
        }
    }

    public function test_no_version_label_is_invented_when_the_version_is_unknown(): void
    {
        config(['app.version' => null]);
        $dir = public_path('app');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.'/unversioned-build.apk';
        file_put_contents($path, "PK\x03\x04".'unversioned');
        touch($path, time() + 60);
        file_put_contents($path.'.json', '{"version":"<script>"}');

        try {
            $this->assertNull(AppDownload::android()['version']);
            $this->get('/download')
                ->assertOk()
                ->assertSee('/app/unversioned-build.apk', false)
                ->assertDontSee('Toleo', false);
        } finally {
            @unlink($path);
            @unlink($path.'.json');
        }
    }

    public function test_the_health_endpoint_reports_the_configured_version(): void
    {
        config(['app.version' => '2.0.1']);

        $this->getJson('/api/health')->assertOk()->assertJsonPath('version', '2.0.1');
    }
}
