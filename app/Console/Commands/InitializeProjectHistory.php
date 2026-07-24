<?php

namespace App\Console\Commands;

use App\Enums\ActivityVisibility;
use App\Models\Project;
use App\Services\Projects\ProjectActivityService;
use Illuminate\Console\Command;

class InitializeProjectHistory extends Command
{
    protected $signature =
        'projects:initialize-history
        {--project= : Initialize one project ID}';

    protected $description =
        'Create a Phase 6 baseline activity for existing projects';

    public function handle(
        ProjectActivityService $activityService
    ): int {
        $query = Project::query();

        if ($this->option('project')) {
            $query->whereKey(
                $this->option('project')
            );
        }

        $count = 0;

        $query
            ->orderBy('id')
            ->chunkById(
                100,
                function ($projects) use (
                    $activityService,
                    &$count
                ): void {
                    foreach ($projects as $project) {
                        if (
                            $project
                                ->activities()
                                ->where(
                                    'event',
                                    'history_initialized'
                                )
                                ->exists()
                        ) {
                            continue;
                        }

                        $activityService->logCustom(
                            project: $project,

                            event:
                                'history_initialized',

                            title:
                                'Project history baseline initialized',

                            description:
                                'This baseline represents the current project state when Phase 6 history tracking was activated.',

                            metadata: [
                                'project_name' =>
                                    $project->name,

                                'project_status' =>
                                    $project->status->value,

                                'project_price' =>
                                    $project->project_price,

                                'official_progress' =>
                                    $project
                                        ->official_progress,

                                'internal_progress' =>
                                    $project
                                        ->internal_progress,

                                'net_received_amount' =>
                                    $project
                                        ->net_received_amount,

                                'pending_amount' =>
                                    $project
                                        ->pending_amount,

                                'project_expense_amount' =>
                                    $project
                                        ->project_expense_amount,
                            ],

                            visibility:
                                ActivityVisibility::Management,

                            actorId: null
                        );

                        $count++;
                    }
                }
            );

        $this->info(
            "{$count} project history baseline record(s) created."
        );

        return self::SUCCESS;
    }
}