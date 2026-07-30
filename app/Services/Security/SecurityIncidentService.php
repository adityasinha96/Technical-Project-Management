<?php

namespace App\Services\Security;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\LoginEventType;
use App\Enums\SecurityIncidentStatus;
use App\Enums\SecurityIncidentType;
use App\Models\LoginEvent;
use App\Models\SecurityIncident;
use App\Models\User;
use App\Notifications\SecurityIncidentNotification;
use App\Services\Audit\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SecurityIncidentService
{
    public function __construct(
        private readonly AuditLogService $audit
    ) {
    }

    public function raise(
        SecurityIncidentType $type,
        AuditSeverity $severity,
        string $title,
        string $description,
        string $fingerprintSource,
        ?Model $subject = null,
        ?LoginEvent $loginEvent = null,
        array $metadata = []
    ): SecurityIncident {
        $fingerprint = hash(
            'sha256',
            $type->value
            . '|'
            . $fingerprintSource
        );

        $existing =
            SecurityIncident::query()
                ->where(
                    'fingerprint',
                    $fingerprint
                )
                ->whereIn('status', [
                    SecurityIncidentStatus::Open
                        ->value,

                    SecurityIncidentStatus::Acknowledged
                        ->value,
                ])
                ->first();

        $isNew = !$existing;

        if ($existing) {
            $existing->increment(
                'occurrence_count'
            );

            $existing->update([
                'last_seen_at' => now(),
                'severity' => $severity->value,
                'description' => $description,
                'metadata' => [
                    ...($existing->metadata ?? []),
                    ...$metadata,
                ],
            ]);

            $incident =
                $existing->refresh();
        } else {
            $incident =
                SecurityIncident::create([
                    'incident_uuid' =>
                        (string) Str::uuid(),

                    'incident_type' =>
                        $type->value,

                    'severity' =>
                        $severity->value,

                    'status' =>
                        SecurityIncidentStatus::Open
                            ->value,

                    'fingerprint' =>
                        $fingerprint,

                    'title' =>
                        $title,

                    'description' =>
                        $description,

                    'subject_type' =>
                        $subject?->getMorphClass(),

                    'subject_id' =>
                        $subject?->getKey(),

                    'login_event_id' =>
                        $loginEvent?->id,

                    'detected_at' =>
                        now(),

                    'last_seen_at' =>
                        now(),

                    'metadata' =>
                        $metadata,
                ]);
        }

        $this->audit->record(
            eventType:
                'security.incident_detected',

            category:
                AuditCategory::Security,

            severity:
                $severity,

            auditable:
                $incident,

            metadata: [
                'incident_type' =>
                    $type->value,

                'is_new' => $isNew,
            ]
        );

        if (
            $isNew
            || $severity ===
                AuditSeverity::Critical
        ) {
            $recipients =
                User::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->role('super-admin')
                    ->get();

            Notification::send(
                $recipients,
                new SecurityIncidentNotification(
                    $incident
                )
            );
        }

        return $incident;
    }

    public function evaluateFailedLogin(
        LoginEvent $loginEvent
    ): void {
        $windowMinutes =
            (int) config(
                'security.monitoring.failed_login_window_minutes',
                15
            );

        $threshold =
            (int) config(
                'security.monitoring.failed_login_threshold',
                5
            );

        $query =
            LoginEvent::query()
                ->where(
                    'event_type',
                    LoginEventType::Failed
                        ->value
                )
                ->where(
                    'occurred_at',
                    '>=',
                    now()->subMinutes(
                        $windowMinutes
                    )
                );

        if ($loginEvent->identifier_hash) {
            $query->where(
                'identifier_hash',
                $loginEvent
                    ->identifier_hash
            );
        } else {
            $query->where(
                'ip_address',
                $loginEvent->ip_address
            );
        }

        $count = $query->count();

        if ($count < $threshold) {
            return;
        }

        $this->raise(
            type:
                SecurityIncidentType::RepeatedLoginFailure,

            severity:
                AuditSeverity::High,

            title:
                'Repeated failed login attempts',

            description:
                "{$count} failed login attempts were detected during the last {$windowMinutes} minutes.",

            fingerprintSource:
                $loginEvent->identifier_hash
                ?: (string)
                    $loginEvent->ip_address,

            loginEvent:
                $loginEvent,

            metadata: [
                'attempt_count' =>
                    $count,

                'window_minutes' =>
                    $windowMinutes,

                'ip_address' =>
                    $loginEvent
                        ->ip_address,

                'identifier_masked' =>
                    $loginEvent
                        ->identifier_masked,
            ]
        );
    }

    public function evaluateSuccessfulLogin(
        LoginEvent $loginEvent
    ): void {
        if (
            !$loginEvent
                ->authenticatable_type
            || !$loginEvent
                ->authenticatable_id
            || !$loginEvent->ip_address
        ) {
            return;
        }

        $previousLogin =
            LoginEvent::query()
                ->where(
                    'event_type',
                    LoginEventType::Login
                        ->value
                )
                ->where(
                    'successful',
                    true
                )
                ->where(
                    'authenticatable_type',
                    $loginEvent
                        ->authenticatable_type
                )
                ->where(
                    'authenticatable_id',
                    $loginEvent
                        ->authenticatable_id
                )
                ->where(
                    'id',
                    '<',
                    $loginEvent->id
                )
                ->latest('id')
                ->first();

        if (
            !$previousLogin
            || $previousLogin->ip_address ===
                $loginEvent->ip_address
        ) {
            return;
        }

        $knownAddress =
            LoginEvent::query()
                ->where(
                    'event_type',
                    LoginEventType::Login
                        ->value
                )
                ->where(
                    'successful',
                    true
                )
                ->where(
                    'authenticatable_type',
                    $loginEvent
                        ->authenticatable_type
                )
                ->where(
                    'authenticatable_id',
                    $loginEvent
                        ->authenticatable_id
                )
                ->where(
                    'ip_address',
                    $loginEvent
                        ->ip_address
                )
                ->where(
                    'id',
                    '<',
                    $loginEvent->id
                )
                ->exists();

        if ($knownAddress) {
            return;
        }

        $this->raise(
            type:
                SecurityIncidentType::NewLoginLocation,

            severity:
                AuditSeverity::Notice,

            title:
                'Login from a new IP address',

            description:
                'A successful login was recorded from an IP address not previously used by this account.',

            fingerprintSource:
                implode('|', [
                    $loginEvent
                        ->authenticatable_type,

                    $loginEvent
                        ->authenticatable_id,

                    $loginEvent
                        ->ip_address,
                ]),

            loginEvent:
                $loginEvent,

            metadata: [
                'ip_address' =>
                    $loginEvent
                        ->ip_address,
            ]
        );
    }

    public function raiseLoginLockout(
        LoginEvent $loginEvent
    ): void {
        $this->raise(
            type:
                SecurityIncidentType::LoginLockout,

            severity:
                AuditSeverity::High,

            title:
                'Login rate-limit lockout',

            description:
                'A login request was locked after repeated attempts.',

            fingerprintSource:
                $loginEvent->identifier_hash
                ?: (string)
                    $loginEvent->ip_address,

            loginEvent:
                $loginEvent
        );
    }
}