<?php

namespace App\Services\Projects;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectTemplateService
{
    public function __construct(
        private readonly ProjectProgressService $progressService
    ) {
    }

    public function apply(
        Project $project,
        ProjectTemplate $template,
        int $createdBy
    ): void {
        if ($project->tasks()->exists()) {
            throw ValidationException::withMessages([
                'template' =>
                    'A template cannot be applied because this project already has tasks.',
            ]);
        }

        $template->load('tasks');

        if ($template->tasks->isEmpty()) {
            throw ValidationException::withMessages([
                'template' =>
                    'The selected template does not contain any tasks.',
            ]);
        }

        DB::transaction(
            function () use (
                $project,
                $template,
                $createdBy
            ): void {
                $currentDate = $project->start_date
                    ? $project->start_date->copy()
                    : today();

                $projectDeadline = $project->deadline;

                foreach ($template->tasks as $index => $templateTask) {
                    $duration = max(
                        1,
                        (int) (
                            $templateTask->default_duration_days
                            ?: 1
                        )
                    );

                    $taskStartDate = $currentDate->copy();

                    if (
                        $projectDeadline &&
                        $taskStartDate->isAfter($projectDeadline)
                    ) {
                        $taskStartDate =
                            $projectDeadline->copy();
                    }

                    $taskDueDate = $taskStartDate
                        ->copy()
                        ->addDays($duration - 1);

                    if (
                        $projectDeadline &&
                        $taskDueDate->isAfter($projectDeadline)
                    ) {
                        $taskDueDate =
                            $projectDeadline->copy();
                    }

                    $project->tasks()->create([
                        'project_template_task_id' =>
                            $templateTask->id,

                        'created_by' => $createdBy,

                        'title' => $templateTask->title,
                        'description' =>
                            $templateTask->description,

                        'phase' =>
                            $templateTask->phase->value,

                        'priority' =>
                            $templateTask->priority->value,

                        'status' =>
                            TaskStatus::NotStarted->value,

                        'weight' => $templateTask->weight,

                        'estimated_hours' =>
                            $templateTask->estimated_hours,

                        'progress' => 0,

                        'start_date' =>
                            $taskStartDate->toDateString(),

                        'due_date' =>
                            $taskDueDate->toDateString(),

                        'sort_order' => $index + 1,
                    ]);

                    $currentDate = $taskDueDate
                        ->copy()
                        ->addDay();
                }

                $project->forceFill([
                    'project_template_id' => $template->id,
                    'internal_progress' => 0,
                ])->saveQuietly();
            }
        );

        $this->progressService
            ->recalculateInternalProgress($project);
    }
}