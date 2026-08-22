<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(18));
        View::share('cspNonce', $nonce);

        $response = $next($request);
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://accounts.google.com https://appleid.cdn-apple.com",
            "script-src-attr 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://accounts.google.com https://appleid.apple.com",
            "frame-src 'self' https://accounts.google.com https://appleid.apple.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]));
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Camera stays available to same-origin pages: Mkulima Verify scans a
        // pack label and the disease scanner photographs a leaf, both from
        // first-party pages. Blanket camera=() would silently break them.
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(self), interest-cohort=()');

        // HSTS, once, over HTTPS only. Sending it over plain HTTP is ignored by
        // browsers and sending it in local development would pin 127.0.0.1 to
        // HTTPS in the developer's browser for a year.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
