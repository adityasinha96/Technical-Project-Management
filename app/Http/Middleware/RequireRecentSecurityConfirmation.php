<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRecentSecurityConfirmation
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $confirmedAt =
            (int) $request
                ->session()
                ->get(
                    'security_confirmed_at',
                    0
                );

        $maximumAge =
            (int) config(
                'security.monitoring.security_confirmation_seconds',
                900
            );

        if (
            $confirmedAt === 0
            || now()->timestamp
                - $confirmedAt
                > $maximumAge
        ) {
            $request->session()->put(
                'security_confirmation_intended_url',
                $request->fullUrl()
            );

            return redirect()
                ->route(
                    'security.confirmation.create'
                );
        }

        return $next($request);
    }
}