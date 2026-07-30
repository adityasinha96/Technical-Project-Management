<?php

namespace App\Http\Middleware;

use App\Enums\ClientUserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientUserIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $clientUser =
            Auth::guard('client')->user();

        if (
            !$clientUser
            || $clientUser->status !==
                ClientUserStatus::Active
        ) {
            Auth::guard('client')->logout();

            $request->session()
                ->invalidate();

            $request->session()
                ->regenerateToken();

            return redirect()
                ->route('client.login')
                ->withErrors([
                    'email' =>
                        'Your client portal account is not active.',
                ]);
        }

        return $next($request);
    }
}