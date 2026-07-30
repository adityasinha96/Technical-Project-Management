<?php

namespace App\Console\Commands;

use App\Enums\AuditSeverity;
use App\Enums\BackupStatus;
use App\Enums\SecurityIncidentType;
use App\Models\BackupRun;
use App\Services\Security\SecurityIncidentService;
use Illuminate\Console\Command;

class EvaluateSecurityIncidents extends Command
{
    protected $signature =
        'security:evaluate';

    protected $description =
        'Evaluate backup and security health conditions';

    public function handle(
        SecurityIncidentService $incidents
    ): int {
        $maximumAge =
            (int) config(
                'security.monitoring.backup_maximum_age_hours',
                30
            );

        $lastSuccessfulBackup =
            BackupRun::query()
                ->where(
                    'status',
                    BackupStatus::Completed
                        ->value
                )
                ->latest(
                    'completed_at'
                )
                ->first();

        if (
            !$lastSuccessfulBackup
            || $lastSuccessfulBackup
                ->completed_at
                ->lt(
                    now()->subHours(
                        $maximumAge
                    )
                )
        ) {
            $incidents->raise(
                type:
                    SecurityIncidentType::BackupOverdue,

                severity:
                    AuditSeverity::Critical,

                title:
                    'System backup is overdue',

                description:
                    "No successful system backup has completed during the last {$maximumAge} hours.",

                fingerprintSource:
                    now()->format(
                        'Y-m-d'
                    ),

                subject:
                    $lastSuccessfulBackup
            );
        }

        $this->info(
            'Security conditions evaluated.'
        );

        return self::SUCCESS;
    }
}