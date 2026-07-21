<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\Payments\ProjectFinancialService;

class ProjectObserver
{
    public function created(Project $project): void
    {
        app(ProjectFinancialService::class)
            ->synchronize($project);
    }

    public function updated(Project $project): void
    {
        if (
            $project->wasChanged([
                'project_price',
            ])
        ) {
            app(ProjectFinancialService::class)
                ->synchronize($project);
        }
    }
}