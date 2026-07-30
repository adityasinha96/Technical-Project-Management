<?php

namespace App\Http\Middleware;

use App\Models\SecuritySession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackSecuritySession
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        [$actor, $guard] =
            $this->resolveActor();

        if (
            !$actor
            || !$request->hasSession()
        ) {
            return $next($request);
        }

        $sessionId =
            $request
                ->session()
                ->getId();

        $sessionHash =
            hash(
                'sha256',
                $sessionId
            );

        $securitySession =
            SecuritySession::query()
                ->where(
                    'session_id_hash',
                    $sessionHash
                )
                ->first();

        if (
            $securitySession
                ?->revoked_at
        ) {
            Auth::guard($guard)
                ->logout();

            $request->session()
                ->invalidate();

            $request->session()
                ->regenerateToken();

            return redirect()
                ->route(
                    $guard === 'client'
                        ? 'client.login'
                        : 'login'
                )
                ->withErrors([
                    'email' =>
                        'This session has been revoked by an administrator.',
                ]);
        }

        $response = $next($request);

        $cacheKey =
            "security-session-touch:{$sessionHash}";

        if (
            Cache::add(
                $cacheKey,
                true,
                now()->addMinutes(5)
            )
        ) {
            SecuritySession::query()
                ->updateOrCreate(
                    [
                        'session_id_hash' =>
                            $sessionHash,
                    ],
                    [
                        'session_uuid' =>
                            $securitySession
                                ?->session_uuid
                            ?: (string)
                                Str::uuid(),

                        'guard' =>
                            $guard,

                        'actor_type' =>
                            $actor
                                ->getMorphClass(),

                        'actor_id' =>
                            $actor->getKey(),

                        'session_id' =>
                            $sessionId,

                        'ip_address' =>
                            $request->ip(),

                        'user_agent' =>
                            str(
                                (string)
                                $request
                                    ->userAgent()
                            )->limit(2000),

                        'device_fingerprint' =>
                            hash(
                                'sha256',
                                implode('|', [
                                    $request->ip(),

                                    $request
                                        ->userAgent(),
                                ])
                            ),

                        'logged_in_at' =>
                            $securitySession
                                ?->logged_in_at
                            ?: now(),

                        'last_seen_at' =>
                            now(),

                        'logged_out_at' =>
                            null,
                    ]
                );

            if (
                property_exists(
                    $actor,
                    'last_seen_at'
                )
            ) {
                $actor->forceFill([
                    'last_seen_at' =>
                        now(),
                ])->saveQuietly();
            }
        }

        return $response;
    }

    private function resolveActor(): array
    {
        if (
            Auth::guard('client')
                ->check()
        ) {
            return [
                Auth::guard('client')
                    ->user(),

                'client',
            ];
        }

        if (
            Auth::guard('web')
                ->check()
        ) {
            return [
                Auth::guard('web')
                    ->user(),

                'web',
            ];
        }

        return [null, null];
    }
}