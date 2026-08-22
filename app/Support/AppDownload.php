<?php

namespace App\Support;

use App\Models\LandingSetting;

/**
 * Resolves what the /download page is actually allowed to offer.
 *
 * The page used to hardcode an APK filename, a hardcoded "171 MB", and a link
 * to /app/web/ — a directory that does not exist, so the "Fungua Web App"
 * button was a guaranteed 404 for every visitor. Everything here is derived
 * from what is really on disk, so the page can never advertise a build that
 * is not there.
 */
final class AppDownload
{
    /**
     * Newest Android build in public/app, or null when none is published.
     *
     * @return array{url: string, filename: string, bytes: int, human: string}|null
     */
    public static function android(): ?array
    {
        $configured = LandingSetting::query()->where('key', 'app_apk_path')->value('value');
        if ($configured && is_file(public_path('storage/'.$configured))) {
            return self::describe(public_path('storage/'.$configured), '/storage/'.$configured);
        }

        $candidates = glob(public_path('app/*.apk')) ?: [];
        if ($candidates === []) {
            return null;
        }

        // Newest by modification time — old test builds linger in this folder.
        usort($candidates, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $path = $candidates[0];

        return self::describe($path, '/app/'.basename($path));
    }

    /**
     * Whether a Flutter web build has actually been deployed.
     *
     * Gate the "open the web app" button on this rather than assuming.
     */
    public static function hasWebBuild(): bool
    {
        return is_file(public_path('app/web/index.html'));
    }

    public static function webUrl(): string
    {
        return '/app/web/';
    }

    /**
     * @return array{url: string, filename: string, bytes: int, human: string}
     */
    private static function describe(string $absolutePath, string $url): array
    {
        $bytes = (int) filesize($absolutePath);

        return [
            'url' => $url,
            'filename' => basename($absolutePath),
            'bytes' => $bytes,
            'human' => self::humanBytes($bytes),
        ];
    }

    private static function humanBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 1).' GB';
        }

        return max(1, (int) round($bytes / 1048576)).' MB';
    }
}
