<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\Payments\ProjectFinancialService;
use Illuminate\Console\Command;

class RecalculateProjectFinancials extends Command
{
    protected $signature =
        'projects:recalculate-financials
        {--project= : Recalculate a specific project ID}';

    protected $description =
        'Recalculate project received amounts, pending balances and collection percentages';

    public function handle(
        ProjectFinancialService $financialService
    ): int {
        $projectId = $this->option('project');

        if ($projectId) {
            $project = Project::query()
                ->findOrFail($projectId);

            $financialService->synchronize($project);

            $this->info(
                "Financial summary recalculated for {$project->name}."
            );

            return self::SUCCESS;
        }

        $count = 0;

        Project::query()
            ->orderBy('id')
            ->chunkById(
                100,
                function ($projects) use (
                    $financialService,
                    &$count
                ): void {
                    foreach ($projects as $project) {
                        $financialService
                            ->synchronize($project);

                        $count++;
                    }
                }
            );

        $this->info(
            "{$count} project financial summaries recalculated."
        );

        return self::SUCCESS;
    }
}