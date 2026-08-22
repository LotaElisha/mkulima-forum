<?php

namespace App\Console\Commands;

use App\Models\LandingSetting;
use Illuminate\Console\Command;
use Throwable;

/**
 * Renders the real home page to a static file for the Hostinger deploy step.
 *
 * The npm build script used to inline a php -r one-liner that rendered
 * view('landing') — a view nothing has routed to since the site became
 * multi-page. Every deploy therefore shipped a dist/index.html built from a
 * design that was no longer the product. This renders whatever the '/' route
 * actually renders, and fails loudly instead of writing a broken page.
 */
class ExportLandingPage extends Command
{
    protected $signature = 'mkulima:export-landing {path=dist/index.html}';

    protected $description = 'Render the public home page to a static HTML file for deployment';

    public function handle(): int
    {
        $path = $this->argument('path');

        $settings = [];
        try {
            $settings = LandingSetting::pluck('value', 'key')->toArray();
        } catch (Throwable) {
            // A build machine has no database. Defaults below cover it.
            $this->warn('No database available; rendering with default settings.');
        }

        $settings = array_merge([
            'logo_url' => '/images/brand-banner.png',
            'banner_url' => '/images/brand-banner.png',
            'emblem_url' => '/images/logo-icon.jpg',
            'pitch_deck_url' => '/docs/Mkulima_Forum_Pitch_Deck.pdf',
            'brand_motto' => 'SHIRIKI • JIFUNZE • ENDELEA',
            'contact_email' => 'hello@mkulimaforum.app',
            'metric_farmers' => null,
            'metric_regions' => null,
            'metric_scans' => null,
            'metric_queries' => null,
            'metric_markets' => null,
        ], $settings);

        try {
            $html = view('pages.home', compact('settings'))->render();
        } catch (Throwable $e) {
            $this->error('Could not render the home page: '.$e->getMessage());

            return self::FAILURE;
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            $this->error("Could not create {$directory}.");

            return self::FAILURE;
        }

        file_put_contents($path, $html);
        $this->info(sprintf('Wrote %s (%s KB).', $path, number_format(strlen($html) / 1024, 1)));

        return self::SUCCESS;
    }
}
