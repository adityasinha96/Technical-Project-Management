<?php

namespace App\Services\Security;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\LoginEventType;
use App\Models\SecuritySession;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SecuritySessionService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly LoginHistoryService $loginHistory
    ) {
    }

    public function revoke(
        SecuritySession $securitySession,
        User $revokedBy,
        string $reason
    ): void {
        $securitySession->update([
            'revoked_at' => now(),
            'revoked_by' =>
                $revokedBy->id,

            'revoke_reason' =>
                $reason,
        ]);

        $this->removeFrameworkSession(
            $securitySession->session_id
        );

        $actor =
            $securitySession->actor;

        $this->loginHistory->record(
            eventType:
                LoginEventType::SessionRevoked,

            authenticatable:
                $actor,

            guard:
                $securitySession->guard,

            identifier:
                $actor?->email,

            successful: true,

            metadata: [
                'security_session_uuid' =>
                    $securitySession
                        ->session_uuid,

                'revoked_by' =>
                    $revokedBy->id,

                'reason' =>
                    $reason,
            ]
        );

        $this->audit->record(
            eventType:
                'security.session_revoked',

            category:
                AuditCategory::Security,

            severity:
                AuditSeverity::High,

            auditable:
                $securitySession,

            metadata: [
                'reason' => $reason,
                'session_actor' =>
                    $actor?->email,
            ],

            actor:
                $revokedBy,

            guard: 'web'
        );
    }

    private function removeFrameworkSession(
        string $sessionId
    ): void {
        $driver = config(
            'session.driver'
        );

        if ($driver === 'database') {
            DB::table(
                config(
                    'session.table',
                    'sessions'
                )
            )
                ->where(
                    'id',
                    $sessionId
                )
                ->delete();

            return;
        }

        if ($driver === 'file') {
            File::delete(
                storage_path(
                    "framework/sessions/{$sessionId}"
                )
            );
        }
    }
}