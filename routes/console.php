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