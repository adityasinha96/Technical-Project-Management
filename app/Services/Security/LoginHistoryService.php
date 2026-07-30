<?php

namespace App\Services\Security;

use App\Enums\LoginEventType;
use App\Models\LoginEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginHistoryService
{
    public function record(
        LoginEventType $eventType,
        ?Authenticatable $authenticatable,
        ?string $guard,
        ?string $identifier,
        bool $successful,
        ?string $failureReason = null,
        array $metadata = [],
        ?Request $request = null
    ): LoginEvent {
        $request ??=
            app()->bound('request')
                ? request()
                : null;

        $normalisedIdentifier =
            $identifier
                ? Str::lower(
                    trim($identifier)
                )
                : null;

        $sessionId =
            $request?->hasSession()
                ? $request
                    ->session()
                    ->getId()
                : null;

        $userAgent =
            (string)
            $request?->userAgent();

        $model =
            $authenticatable instanceof Model
                ? $authenticatable
                : null;

        return LoginEvent::query()->create([
            'event_uuid' =>
                (string) Str::uuid(),

            'event_type' =>
                $eventType->value,

            'guard' =>
                $guard,

            'authenticatable_type' =>
                $model?->getMorphClass(),

            'authenticatable_id' =>
                $model?->getKey(),

            'identifier_hash' =>
                $normalisedIdentifier
                    ? hash(
                        'sha256',
                        $normalisedIdentifier
                    )
                    : null,

            'identifier_masked' =>
                $this->maskIdentifier(
                    $normalisedIdentifier
                ),

            'successful' =>
                $successful,

            'ip_address' =>
                $request?->ip(),

            'user_agent' =>
                str($userAgent)
                    ->limit(2000),

            'device_fingerprint' =>
                hash(
                    'sha256',
                    implode('|', [
                        $request?->ip(),
                        $userAgent,
                    ])
                ),

            'session_id_hash' =>
                $sessionId
                    ? hash(
                        'sha256',
                        $sessionId
                    )
                    : null,

            'risk_score' =>
                0,

            'failure_reason' =>
                $failureReason,

            'metadata' =>
                $metadata,

            'occurred_at' =>
                now(),
        ]);
    }

    private function maskIdentifier(
        ?string $identifier
    ): ?string {
        if (!$identifier) {
            return null;
        }

        if (
            filter_var(
                $identifier,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            [$local, $domain] =
                explode(
                    '@',
                    $identifier,
                    2
                );

            return str($local)
                ->substr(0, 2)
                ->append('***@')
                ->append($domain)
                ->toString();
        }

        return str($identifier)
            ->substr(0, 3)
            ->append('***')
            ->toString();
    }
}