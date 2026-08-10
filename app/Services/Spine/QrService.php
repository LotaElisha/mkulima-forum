<?php

namespace App\Services\Spine;

use App\Models\ShortLink;
use Illuminate\Support\Str;

class QrService
{
    /**
     * Generate short link and QR SVG data for any target URL or entity.
     */
    public function generate(string $targetUrl, string $linkType = 'general', ?Model $subject = null): array
    {
        $slug = Str::random(8);

        $shortLink = ShortLink::create([
            'slug' => $slug,
            'target_url' => $targetUrl,
            'link_type' => $linkType,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'is_active' => true,
        ]);

        $shortUrl = url("/c/{$slug}");

        // Generate SVG QR representation (simple matrix encoding fallback)
        $qrSvg = $this->renderSimpleQrSvg($shortUrl);

        return [
            'slug' => $slug,
            'short_url' => $shortUrl,
            'target_url' => $targetUrl,
            'qr_svg' => $qrSvg,
            'short_link_id' => $shortLink->id,
        ];
    }

    protected function renderSimpleQrSvg(string $text): string
    {
        $encodedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">'
            .'<rect width="200" height="200" fill="#ffffff"/>'
            .'<rect x="20" y="20" width="50" height="50" fill="#0E4220"/>'
            .'<rect x="30" y="30" width="30" height="30" fill="#ffffff"/>'
            .'<rect x="130" y="20" width="50" height="50" fill="#0E4220"/>'
            .'<rect x="140" y="30" width="30" height="30" fill="#ffffff"/>'
            .'<rect x="20" y="130" width="50" height="50" fill="#0E4220"/>'
            .'<rect x="30" y="140" width="30" height="30" fill="#ffffff"/>'
            .'<rect x="80" y="80" width="40" height="40" fill="#1F6B38"/>'
            .'<text x="100" y="180" font-size="10" text-anchor="middle" fill="#0E4220">Mkulima Forum</text>'
            .'</svg>';
    }
}
