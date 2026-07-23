<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\Expenses\ProjectProfitabilityService;
use Illuminate\Console\Command;

class RecalculateProjectProfitability extends Command
{
    protected $signature =
        'projects:recalculate-profitability
        {--project= : Recalculate one project ID}';

    protected $description =
        'Recalculate project expense, profit, margin and cash position summaries';

    public function handle(
        ProjectProfitabilityService $service
    ): int {
        $projectId = $this->option('project');

        if ($projectId) {
            $project = Project::query()
                ->findOrFail($projectId);

            $service->synchronize($project);

            $this->info(
                "Profitability recalculated for {$project->name}."
            );

            return self::SUCCESS;
        }

        $count = 0;

        Project::query()
            ->orderBy('id')
            ->chunkById(
                100,
                function ($projects) use (
                    $service,
                    &$count
                ): void {
                    foreach ($projects as $project) {
                        $service->synchronize(
                            $project
                        );

                        $count++;
                    }
                }
            );

        $this->info(
            "{$count} project profitability summaries recalculated."
        );

        return self::SUCCESS;
    }
}