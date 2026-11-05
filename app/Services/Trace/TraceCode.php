<?php

namespace App\Services\Trace;

use Illuminate\Database\Eloquent\Model;

/**
 * The opaque token printed in a QR code.
 *
 * Ten characters from an alphabet without look-alikes (no 0/O, 1/I/L), so a
 * code can be read out over the phone. 31^10 is about 8 x 10^14: guessing a
 * live code is not practical. Phones may generate codes offline with the
 * same alphabet; the server checks the format and uniqueness.
 */
final class TraceCode
{
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public const LENGTH = 10;

    public const PATTERN = '/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{10}$/';

    public static function make(): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }

    /** @param  class-string<Model>  $model */
    public static function unique(string $model): string
    {
        do {
            $code = self::make();
        } while ($model::withoutGlobalScopes()->where('code', $code)->exists());

        return $code;
    }

    public static function isValid(?string $code): bool
    {
        return is_string($code) && preg_match(self::PATTERN, $code) === 1;
    }

    /** Accepts a bare code or a scanned link ending in /t/{code}. */
    public static function fromScan(string $scanned): ?string
    {
        $scanned = trim($scanned);
        if (preg_match('#/t/([23456789A-Z]{10})(?:[/?\#]|$)#', $scanned, $m)) {
            return $m[1];
        }
        $upper = strtoupper($scanned);

        return self::isValid($upper) ? $upper : null;
    }

    public static function url(string $code): string
    {
        return rtrim((string) config('app.url'), '/').'/t/'.$code;
    }
}
