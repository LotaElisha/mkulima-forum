<?php

namespace App\Support;

/**
 * One place that decides what a user is allowed to upload.
 *
 * Why this exists: Laravel's `image` rule accepts SVG. Every avatar, evidence
 * photo and counterfeit-report picture on this platform was validated with a
 * bare `image` rule and then written to the *public* disk, so any farmer
 * account could upload an SVG containing a <script> tag and get back a
 * first-party URL that runs it — stored XSS against anyone who opened the
 * profile. Service-booking media was worse still: `file|max:5120` with no type
 * check at all.
 *
 * The rules below whitelist by both extension and sniffed MIME type. Checking
 * only the extension trusts the filename; checking only the MIME type lets a
 * `.phtml` through behind an `image/jpeg` header on a misconfigured host.
 */
final class UploadRules
{
    /** Raster image formats that browsers render inertly. No SVG: it is a document. */
    public const RASTER_EXTENSIONS = 'jpeg,jpg,png,webp';

    public const RASTER_MIMETYPES = 'image/jpeg,image/png,image/webp';

    /** Documents accepted as supporting evidence alongside photos. */
    public const DOCUMENT_EXTENSIONS = 'pdf';

    public const DOCUMENT_MIMETYPES = 'application/pdf';

    /**
     * Photo upload: profile pictures, crop photos, evidence images.
     *
     * @param  int  $maxKilobytes  Hard size ceiling. Keep these small — the
     *                             median user is on metered mobile data.
     * @return array<int, string>
     */
    public static function raster(int $maxKilobytes = 4096): array
    {
        return [
            'file',
            'image',
            'mimes:'.self::RASTER_EXTENSIONS,
            'mimetypes:'.self::RASTER_MIMETYPES,
            'max:'.$maxKilobytes,
        ];
    }

    /**
     * Photo or PDF: booking attachments and counterfeit-report evidence, where
     * a receipt or lab result is as useful as a picture.
     *
     * @return array<int, string>
     */
    public static function rasterOrDocument(int $maxKilobytes = 5120): array
    {
        return [
            'file',
            'mimes:'.self::RASTER_EXTENSIONS.','.self::DOCUMENT_EXTENSIONS,
            'mimetypes:'.self::RASTER_MIMETYPES.','.self::DOCUMENT_MIMETYPES,
            'max:'.$maxKilobytes,
        ];
    }

    /**
     * Brand assets uploaded by administrators.
     *
     * SVG is still refused even for admins: the logo is rendered inside
     * first-party pages, so a compromised or careless admin account would
     * otherwise be a stored-XSS vector against every visitor.
     *
     * @return array<int, string>
     */
    public static function brandAsset(int $maxKilobytes = 2048): array
    {
        return self::raster($maxKilobytes);
    }

    /**
     * Distributable binaries (the Android APK on the download page).
     *
     * Deliberately separate from every other rule set, and intended only for
     * administrator-only routes.
     *
     * @return array<int, string>
     */
    public static function distributable(int $maxKilobytes = 262144): array
    {
        return [
            'file',
            'mimes:apk,pdf,pptx',
            'max:'.$maxKilobytes,
        ];
    }
}
