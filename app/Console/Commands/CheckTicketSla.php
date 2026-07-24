<?php

namespace App\Console\Commands;

use App\Models\ProjectTicket;
use App\Services\Tickets\TicketSlaService;
use Illuminate\Console\Command;

class CheckTicketSla extends Command
{
    protected $signature =
        'tickets:check-sla
        {--ticket= : Check a specific ticket ID}';

    protected $description =
        'Check active tickets and create required SLA escalations';

    public function handle(
        TicketSlaService $slaService
    ): int {
        $query = ProjectTicket::query()
            ->open()
            ->orderBy('id');

        if ($this->option('ticket')) {
            $query->whereKey(
                $this->option('ticket')
            );
        }

        $checked = 0;
        $escalated = 0;

        $query->chunkById(
            100,
            function ($tickets) use (
                $slaService,
                &$checked,
                &$escalated
            ): void {
                foreach ($tickets as $ticket) {
                    $previousLevel =
                        $ticket
                            ->escalation_level;

                    $newLevel =
                        $slaService
                            ->checkAndEscalate(
                                $ticket
                            );

                    if (
                        $newLevel >
                        $previousLevel
                    ) {
                        $escalated++;
                    }

                    $checked++;
                }
            }
        );

        $this->info(
            "{$checked} ticket(s) checked; {$escalated} newly escalated."
        );

        return self::SUCCESS;
    }
}