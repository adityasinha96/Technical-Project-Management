<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditSeverity;
use App\Enums\BackupStatus;
use App\Enums\LoginEventType;
use App\Enums\SecurityIncidentStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BackupRun;
use App\Models\LoginEvent;
use App\Models\PermissionChangeLog;
use App\Models\SecurityIncident;
use App\Models\SecuritySession;
use App\Services\Audit\AuditIntegrityService;
use Illuminate\Contracts\View\View;

class SecurityControlCenterController extends Controller
{
    public function __invoke(
        AuditIntegrityService $integrity
    ): View {
        $lastBackup =
            BackupRun::query()
                ->where(
                    'status',
                    BackupStatus::Completed->value
                )
                ->latest(
                    'completed_at'
                )
                ->first();

        $statistics = [
            'open_incidents' =>
                SecurityIncident::query()
                    ->whereIn('status', [
                        SecurityIncidentStatus::Open
                            ->value,

                        SecurityIncidentStatus::Acknowledged
                            ->value,
                    ])
                    ->count(),

            'critical_incidents' =>
                SecurityIncident::query()
                    ->whereIn('status', [
                        SecurityIncidentStatus::Open
                            ->value,

                        SecurityIncidentStatus::Acknowledged
                            ->value,
                    ])
                    ->where(
                        'severity',
                        AuditSeverity::Critical->value
                    )
                    ->count(),

            'failed_logins_24h' =>
                LoginEvent::query()
                    ->where(
                        'event_type',
                        LoginEventType::Failed->value
                    )
                    ->where(
                        'occurred_at',
                        '>=',
                        now()->subDay()
                    )
                    ->count(),

            'active_sessions' =>
                SecuritySession::query()
                    ->whereNull(
                        'logged_out_at'
                    )
                    ->whereNull(
                        'revoked_at'
                    )
                    ->where(
                        'last_seen_at',
                        '>=',
                        now()->subHours(2)
                    )
                    ->count(),

            'audit_entries' =>
                AuditLog::query()->count(),

            'permission_changes_30d' =>
                PermissionChangeLog::query()
                    ->where(
                        'occurred_at',
                        '>=',
                        now()->subDays(30)
                    )
                    ->count(),
        ];

        return view(
            'admin.security.index',
            [
                'statistics' =>
                    $statistics,

                'auditIntegrity' =>
                    $integrity->verify(),

                'lastBackup' =>
                    $lastBackup,

                'recentIncidents' =>
                    SecurityIncident::query()
                        ->with('assignedTo')
                        ->latest(
                            'detected_at'
                        )
                        ->limit(8)
                        ->get(),

                'recentLogins' =>
                    LoginEvent::query()
                        ->with(
                            'authenticatable'
                        )
                        ->latest(
                            'occurred_at'
                        )
                        ->limit(10)
                        ->get(),

                'recentAuditLogs' =>
                    AuditLog::query()
                        ->latest(
                            'sequence'
                        )
                        ->limit(10)
                        ->get(),
            ]
        );
    }
}