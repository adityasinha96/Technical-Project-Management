<?php

namespace App\Console\Commands;

use App\Enums\BackupStatus;
use App\Models\BackupRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneSystemBackups extends Command
{
    protected $signature =
        'system:backup-prune';

    protected $description =
        'Delete expired backups while retaining the latest successful backup';

    public function handle(): int
    {
        $latestSuccessfulId =
            BackupRun::query()
                ->where(
                    'status',
                    BackupStatus::Completed->value
                )
                ->latest('completed_at')
                ->value('id');

        $deleted = 0;

        BackupRun::query()
            ->where(
                'status',
                BackupStatus::Completed->value
            )
            ->whereNotNull(
                'retention_until'
            )
            ->where(
                'retention_until',
                '<',
                now()
            )
            ->when(
                $latestSuccessfulId,
                fn ($query) =>
                    $query->where(
                        'id',
                        '!=',
                        $latestSuccessfulId
                    )
            )
            ->chunkById(
                100,
                function ($backups) use (
                    &$deleted
                ): void {
                    foreach ($backups as $backup) {
                        if (
                            $backup->path
                            && Storage::disk(
                                $backup->disk
                            )->exists(
                                $backup->path
                            )
                        ) {
                            Storage::disk(
                                $backup->disk
                            )->delete(
                                $backup->path
                            );
                        }

                        $backup->update([
                            'status' =>
                                BackupStatus::Deleted
                                    ->value,

                            'path' => null,
                        ]);

                        $deleted++;
                    }
                }
            );

        $this->info(
            "{$deleted} expired backup(s) deleted."
        );

        return self::SUCCESS;
    }
}