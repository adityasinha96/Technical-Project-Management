<?php

namespace App\Console\Commands;

use App\Enums\BackupStatus;
use App\Models\BackupRun;
use App\Services\Backups\BackupVerificationService;
use Illuminate\Console\Command;

class VerifySystemBackups extends Command
{
    protected $signature =
        'system:backup-verify';

    protected $description =
        'Verify stored backup checksums';

    public function handle(
        BackupVerificationService $service
    ): int {
        $valid = 0;
        $invalid = 0;

        BackupRun::query()
            ->where(
                'status',
                BackupStatus::Completed->value
            )
            ->whereNotNull('path')
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get()
            ->each(
                function (
                    BackupRun $backup
                ) use (
                    $service,
                    &$valid,
                    &$invalid
                ): void {
                    if (
                        $service->verify(
                            $backup
                        )
                    ) {
                        $valid++;
                    } else {
                        $invalid++;
                    }
                }
            );

        $this->info(
            "{$valid} valid, {$invalid} invalid."
        );

        return $invalid > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}