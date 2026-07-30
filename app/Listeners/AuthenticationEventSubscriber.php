<?php

namespace App\Listeners;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\LoginEventType;
use App\Services\Audit\AuditLogService;
use App\Services\Security\LoginHistoryService;
use App\Services\Security\SecurityIncidentService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class AuthenticationEventSubscriber
{
    public function __construct(
        private readonly LoginHistoryService $loginHistory,
        private readonly AuditLogService $audit,
        private readonly SecurityIncidentService $incidents
    ) {
    }

    public function handleLogin(
        Login $event
    ): void {
        $loginEvent =
            $this->loginHistory->record(
                eventType:
                    LoginEventType::Login,

                authenticatable:
                    $event->user,

                guard:
                    $event->guard,

                identifier:
                    $event->user->email
                    ?? null,

                successful: true
            );

        $this->audit->record(
            eventType:
                'authentication.login',

            category:
                AuditCategory::Authentication,

            severity:
                AuditSeverity::Info,

            auditable:
                $event->user,

            actor:
                $event->user,

            guard:
                $event->guard,

            metadata: [
                'login_event_uuid' =>
                    $loginEvent
                        ->event_uuid,
            ]
        );

        $this->incidents
            ->evaluateSuccessfulLogin(
                $loginEvent
            );
    }

    public function handleFailed(
        Failed $event
    ): void {
        $identifier =
            $event->credentials['email']
            ?? $event->credentials[
                'username'
            ]
            ?? null;

        $loginEvent =
            $this->loginHistory->record(
                eventType:
                    LoginEventType::Failed,

                authenticatable:
                    $event->user,

                guard:
                    $event->guard,

                identifier:
                    is_string($identifier)
                        ? $identifier
                        : null,

                successful: false,

                failureReason:
                    'Invalid credentials'
            );

        $this->audit->record(
            eventType:
                'authentication.failed',

            category:
                AuditCategory::Authentication,

            severity:
                AuditSeverity::Warning,

            auditable:
                $event->user,

            guard:
                $event->guard,

            metadata: [
                'login_event_uuid' =>
                    $loginEvent
                        ->event_uuid,

                'identifier_masked' =>
                    $loginEvent
                        ->identifier_masked,
            ]
        );

        $this->incidents
            ->evaluateFailedLogin(
                $loginEvent
            );
    }

    public function handleLogout(
        Logout $event
    ): void {
        $this->loginHistory->record(
            eventType:
                LoginEventType::Logout,

            authenticatable:
                $event->user,

            guard:
                $event->guard,

            identifier:
                $event->user?->email,

            successful: true
        );

        $this->audit->record(
            eventType:
                'authentication.logout',

            category:
                AuditCategory::Authentication,

            severity:
                AuditSeverity::Info,

            auditable:
                $event->user,

            actor:
                $event->user,

            guard:
                $event->guard
        );
    }

    public function handleLockout(
        Lockout $event
    ): void {
        $identifier =
            $event->request->input(
                'email'
            );

        $loginEvent =
            $this->loginHistory->record(
                eventType:
                    LoginEventType::Lockout,

                authenticatable:
                    null,

                guard:
                    null,

                identifier:
                    is_string($identifier)
                        ? $identifier
                        : null,

                successful: false,

                failureReason:
                    'Rate-limit lockout',

                request:
                    $event->request
            );

        $this->incidents
            ->raiseLoginLockout(
                $loginEvent
            );
    }

    public function handlePasswordReset(
        PasswordReset $event
    ): void {
        if (
            property_exists(
                $event->user,
                'password_changed_at'
            )
        ) {
            $event->user->forceFill([
                'password_changed_at' =>
                    now(),

                'force_password_change' =>
                    false,
            ])->saveQuietly();
        }

        $this->loginHistory->record(
            eventType:
                LoginEventType::PasswordReset,

            authenticatable:
                $event->user,

            guard: null,

            identifier:
                $event->user->email
                ?? null,

            successful: true
        );

        $this->audit->record(
            eventType:
                'authentication.password_reset',

            category:
                AuditCategory::Authentication,

            severity:
                AuditSeverity::High,

            auditable:
                $event->user,

            actor:
                $event->user
        );
    }

    public function subscribe(
        Dispatcher $events
    ): array {
        return [
            Login::class =>
                'handleLogin',

            Failed::class =>
                'handleFailed',

            Logout::class =>
                'handleLogout',

            Lockout::class =>
                'handleLockout',

            PasswordReset::class =>
                'handlePasswordReset',
        ];
    }
}