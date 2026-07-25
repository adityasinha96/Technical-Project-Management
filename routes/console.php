<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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

