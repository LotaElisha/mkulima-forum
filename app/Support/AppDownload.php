<?php

namespace App\Support;

use App\Models\LandingSetting;
use Throwable;

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
     * @return array{url: string, filename: string, bytes: int, human: string, version: ?string}|null
     */
    public static function android(): ?array
    {
        $configured = null;
        try {
            $configured = LandingSetting::query()->where('key', 'app_apk_path')->value('value');
        } catch (Throwable) {
            $configured = null;
        }

        // A configured path that is missing or not an APK must not fall through
        // to some other file, and must not become a link.
        if (is_string($configured) && $configured !== '' && ! str_contains($configured, '..')) {
            return self::describeIfApk(public_path('storage/'.ltrim($configured, '/')), '/storage/'.ltrim($configured, '/'));
        }

        if (is_string($configured) && $configured !== '') {
            return null;
        }

        $candidates = glob(public_path('app/*.apk')) ?: [];
        usort($candidates, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        foreach ($candidates as $path) {
            $described = self::describeIfApk($path, '/app/'.basename($path));
            if ($described !== null) {
                return $described;
            }
        }

        return null;
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
     * @return array{url: string, filename: string, bytes: int, human: string, version: ?string}|null
     */
    private static function describeIfApk(string $absolutePath, string $url): ?array
    {
        $real = realpath($absolutePath);
        $public = realpath(public_path());
        if ($real === false || $public === false || ! str_starts_with($real, $public.DIRECTORY_SEPARATOR)) {
            return null;
        }

        $handle = fopen($real, 'rb');
        if ($handle === false) {
            return null;
        }
        $magic = fread($handle, 4);
        fclose($handle);

        // An APK is a zip. Anything else (an empty file, a placeholder, a
        // broken upload) must not be offered as a download.
        if ($magic !== "PK\x03\x04") {
            return null;
        }

        return self::describe($real, $url);
    }

    /**
     * @return array{url: string, filename: string, bytes: int, human: string, version: ?string}
     */
    private static function describe(string $absolutePath, string $url): array
    {
        $bytes = (int) filesize($absolutePath);

        return [
            'url' => $url,
            'filename' => basename($absolutePath),
            'bytes' => $bytes,
            'human' => self::humanBytes($bytes),
            'version' => self::version($absolutePath),
        ];
    }

    /**
     * The version of this specific build, or null when it cannot be known.
     *
     * scripts/build-mobile.sh writes "<apk>.json" beside the APK with the
     * versionName from pubspec.yaml. That is preferred over config because a
     * config value can drift from whatever file was last copied to the server;
     * the sidecar travels with the file. Anything that does not look like a
     * version is ignored rather than printed.
     */
    private static function version(string $apkPath): ?string
    {
        $sidecar = $apkPath.'.json';
        if (is_file($sidecar)) {
            $data = json_decode((string) file_get_contents($sidecar), true);
            $fromSidecar = is_array($data) ? self::cleanVersion($data['version'] ?? null) : null;
            if ($fromSidecar !== null) {
                return $fromSidecar;
            }
        }

        return self::cleanVersion(config('app.version'));
    }

    private static function cleanVersion(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d+\.\d+\.\d+$/', $value) === 1 ? $value : null;
    }

    private static function humanBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 1).' GB';
        }

        return max(1, (int) round($bytes / 1048576)).' MB';
    }
}
