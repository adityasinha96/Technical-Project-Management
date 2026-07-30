<?php

namespace App\Jobs;

use App\Models\BackupRun;
use App\Services\Backups\SystemBackupService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateSystemBackupJob implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public int $timeout = 1800;
    public int $tries = 1;
    public int $uniqueFor = 3600;

    public function __construct(
        public int $backupRunId
    ) {
        $this->onQueue('backups');
    }

    public function uniqueId(): string
    {
        return 'system-backup';
    }

    public function handle(
        SystemBackupService $service
    ): void {
        $backup =
            BackupRun::query()
                ->findOrFail(
                    $this->backupRunId
                );

        $service->execute(
            $backup
        );
    }
}