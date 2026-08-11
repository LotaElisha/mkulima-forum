<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('admin_token') ?: $request->cookie('user_token');
        if (is_string($token) && $token !== '') {
            $request->headers->set('Authorization', 'Bearer '.$token);
        }

        return $next($request);
    }
}
