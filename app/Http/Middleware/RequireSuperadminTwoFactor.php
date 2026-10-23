<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Filament\Auth\MultiFactor\MultiFactorChallenge;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

/**
 * Filament decides whether to attach the 2FA middleware when routes are
 * registered, before anyone is signed in. A closure that checks the role at
 * that moment is always false, so the middleware never lands on the route.
 *
 * The panel therefore requires the middleware for every staff session, and
 * this class lets an admin through. A superadmin who has not enrolled an
 * authenticator is sent to the setup page.
 */
class RequireSuperadminTwoFactor
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User || $user->role !== 'superadmin') {
            return $next($request);
        }

        if (MultiFactorChallenge::make()->hasEnabledProviders($user)) {
            return $next($request);
        }

        return redirect()->guest(Filament::getSetUpRequiredMultiFactorAuthenticationUrl());
    }
}
