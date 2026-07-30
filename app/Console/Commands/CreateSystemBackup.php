<?php

namespace App\Console\Commands;

use App\Enums\BackupStatus;
use App\Enums\BackupTrigger;
use App\Enums\BackupType;
use App\Enums\BackupVerificationStatus;
use App\Jobs\CreateSystemBackupJob;
use App\Models\BackupRun;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateSystemBackup extends Command
{
    protected $signature =
        'system:backup
        {--type=full : database, files or full}
        {--trigger=scheduled : scheduled, manual, pre_deployment or pre_migration}
        {--sync : Run immediately instead of queueing}';

    protected $description =
        'Create a secure system backup';

    public function handle(): int
    {
        $type =
            BackupType::tryFrom(
                (string)
                $this->option('type')
            );

        $trigger =
            BackupTrigger::tryFrom(
                (string)
                $this->option('trigger')
            );

        if (!$type || !$trigger) {
            $this->error(
                'Invalid backup type or trigger.'
            );

            return self::FAILURE;
        }

        $backup =
            BackupRun::create([
                'backup_uuid' =>
                    (string) Str::uuid(),

                'backup_type' =>
                    $type->value,

                'trigger' =>
                    $trigger->value,

                'status' =>
                    BackupStatus::Queued
                        ->value,

                'verification_status' =>
                    BackupVerificationStatus::Pending
                        ->value,

                'disk' =>
                    config(
                        'system-backup.disk',
                        'backups'
                    ),

                'queued_at' =>
                    now(),

                'retention_until' =>
                    now()->addDays(
                        (int) config(
                            'system-backup.retention.days',
                            30
                        )
                    ),
            ]);

        if ($this->option('sync')) {
            app(
                \App\Services\Backups\SystemBackupService::class
            )->execute($backup);
        } else {
            CreateSystemBackupJob::dispatch(
                $backup->id
            );
        }

        $this->info(
            "Backup {$backup->backup_uuid} created."
        );

        return self::SUCCESS;
    }
}