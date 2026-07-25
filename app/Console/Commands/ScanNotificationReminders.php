<?php

namespace App\Console\Commands;

use App\Services\Notifications\ReminderScannerService;
use Illuminate\Console\Command;

class ScanNotificationReminders extends Command
{
    protected $signature =
        'notifications:scan-reminders';

    protected $description =
        'Scan projects, tasks and payment follow-ups for notification reminders';

    public function handle(
        ReminderScannerService $scanner
    ): int {
        $result = $scanner->scan();

        $this->info(
            sprintf(
                '%d rule(s) scanned, %d subject(s) processed, %d recipient notification(s) created.',
                $result['rules'],
                $result['subjects'],
                $result['recipients']
            )
        );

        return self::SUCCESS;
    }
}