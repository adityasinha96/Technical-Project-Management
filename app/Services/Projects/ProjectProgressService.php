<?php

namespace App\Services\Projects;

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;

class ProjectProgressService
{
    public function recalculateInternalProgress(
        Project $project
    ): int {
        $tasks = $project
            ->tasks()
            ->where(
                'status',
                '!=',
                TaskStatus::Cancelled->value
            )
            ->get([
                'weight',
                'progress',
            ]);

        if ($tasks->isEmpty()) {
            $project->forceFill([
                'internal_progress' => 0,
            ])->saveQuietly();

            return 0;
        }

        $totalWeight = $tasks->sum(
            fn ($task): float =>
                max((float) $task->weight, 0)
        );

        if ($totalWeight <= 0) {
            $progress = (int) round(
                $tasks->avg('progress') ?: 0
            );
        } else {
            $weightedProgress = $tasks->sum(
                fn ($task): float =>
                    (float) $task->weight *
                    (int) $task->progress
            );

            $progress = (int) round(
                $weightedProgress / $totalWeight
            );
        }

        $progress = max(0, min(100, $progress));

        $project->forceFill([
            'internal_progress' => $progress,
        ])->saveQuietly();

        return $progress;
    }

    public function synchronizeOfficialProgress(
        Project $project
    ): int {
        $frontendApproved = $project
            ->approvals()
            ->where(
                'stage',
                ApprovalStage::Frontend->value
            )
            ->where(
                'status',
                ApprovalStatus::Approved->value
            )
            ->exists();

        $backendApproved = $project
            ->approvals()
            ->where(
                'stage',
                ApprovalStage::Backend->value
            )
            ->where(
                'status',
                ApprovalStatus::Approved->value
            )
            ->exists();

        $progress = match (true) {
            $backendApproved => 100,
            $frontendApproved => 50,
            default => 0,
        };

        $updates = [
            'official_progress' => $progress,
        ];

        if ($backendApproved) {
            $updates['status'] =
                ProjectStatus::Completed->value;

            $updates['actual_completion_date'] =
                $project->actual_completion_date ?: today();
        }

        $project->forceFill($updates)->saveQuietly();

        return $progress;
    }
}