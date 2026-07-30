<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(
        Inspiring::quote()
    );
})->purpose(
    'Display an inspiring quote'
);

/*
|--------------------------------------------------------------------------
| Phase 7 Ticket SLA Monitoring
|--------------------------------------------------------------------------
|
| Run the ticket SLA monitoring command every fifteen minutes.
| withoutOverlapping prevents another SLA scan from starting while the
| previous execution is still running.
|
*/

Schedule::command(
    'tickets:check-sla'
)
    ->everyFifteenMinutes()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Phase 8 Notification Reminder Scanning
|--------------------------------------------------------------------------
|
| Scan active reminder rules every thirty minutes and dispatch any
| notifications that have become due.
|
*/

Schedule::command(
    'notifications:scan-reminders'
)
    ->everyThirtyMinutes()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Phase 8 Daily Notification Summaries
|--------------------------------------------------------------------------
|
| Check every fifteen minutes for users whose configured daily summary
| time has arrived.
|
*/

Schedule::command(
    'notifications:send-daily-summaries'
)
    ->everyFifteenMinutes()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Failed Queue Job Cleanup
|--------------------------------------------------------------------------
|
| Remove failed queue jobs older than seven days. The command runs once
| daily at 02:30 according to the application's configured timezone.
|
*/

Schedule::command(
    'queue:prune-failed --hours=168'
)
    ->dailyAt('02:30')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Phase 11 Scheduled Full Backup
|--------------------------------------------------------------------------
|
| Create a complete system backup every day at 02:00. The command is
| restricted to one server and cannot overlap with a previous run.
|
*/

Schedule::command(
    'system:backup --type=full --trigger=scheduled'
)
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Phase 11 Backup Verification
|--------------------------------------------------------------------------
|
| Verify the integrity and usability of system backups every day at 03:00.
|
*/

Schedule::command(
    'system:backup-verify'
)
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Phase 11 Backup Retention Cleanup
|--------------------------------------------------------------------------
|
| Remove expired backups according to the configured retention policy.
|
*/

Schedule::command(
    'system:backup-prune'
)
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Phase 11 Audit Integrity Verification
|--------------------------------------------------------------------------
|
| Verify the audit-chain integrity every hour and prevent overlapping
| integrity checks.
|
*/

Schedule::command(
    'audit:verify-integrity'
)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Phase 11 Security Evaluation
|--------------------------------------------------------------------------
|
| Evaluate security rules and detect incidents every fifteen minutes.
|
*/

Schedule::command(
    'security:evaluate'
)
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Phase 11 Security History Retention
|--------------------------------------------------------------------------
|
| Prune expired security history records every day at 04:00.
|
*/

Schedule::command(
    'security:prune-history'
)
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer();

