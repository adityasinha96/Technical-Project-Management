<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\StoreProjectTaskRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\Projects\ProjectProgressService;
use Illuminate\Http\RedirectResponse;

class ProjectTaskController extends Controller
{
    public function __construct(
        private readonly ProjectProgressService $progressService
    ) {
    }

    public function store(
        StoreProjectTaskRequest $request,
        Project $project
    ): RedirectResponse {
        $data = $this->normaliseTaskData(
            $request->validated()
        );

        $project->tasks()->create([
            ...$data,
            'created_by' => $request->user()->id,
            'sort_order' => (
                (int) $project
                    ->tasks()
                    ->max('sort_order')
            ) + 1,
        ]);

        $this->progressService
            ->recalculateInternalProgress($project);

        return back()->with(
            'success',
            'Project task created successfully.'
        );
    }

    public function update(
        UpdateProjectTaskRequest $request,
        Project $project,
        ProjectTask $projectTask
    ): RedirectResponse {
        abort_unless(
            $projectTask->project_id === $project->id,
            404
        );

        $data = $this->normaliseTaskData(
            $request->validated(),
            $projectTask
        );

        $projectTask->update($data);

        $this->progressService
            ->recalculateInternalProgress($project);

        return back()->with(
            'success',
            'Project task updated successfully.'
        );
    }

    public function destroy(
        Project $project,
        ProjectTask $projectTask
    ): RedirectResponse {
        abort_unless(
            $projectTask->project_id === $project->id,
            404
        );

        $projectTask->delete();

        $this->progressService
            ->recalculateInternalProgress($project);

        return back()->with(
            'success',
            'Project task removed successfully.'
        );
    }

    private function normaliseTaskData(
        array $data,
        ?ProjectTask $task = null
    ): array {
        $status = TaskStatus::from(
            $data['status']
        );

        $progress = (int) $data['progress'];

        if ($status === TaskStatus::Completed) {
            $progress = 100;

            $data['completed_at'] =
                $task?->completed_at ?: now();
        } elseif ($progress >= 100) {
            $status = TaskStatus::Completed;
            $progress = 100;

            $data['completed_at'] =
                $task?->completed_at ?: now();
        } elseif (
            $progress > 0 &&
            $status === TaskStatus::NotStarted
        ) {
            $status = TaskStatus::InProgress;
            $data['completed_at'] = null;
        } else {
            $data['completed_at'] = null;
        }

        if ($status === TaskStatus::Cancelled) {
            $progress = 0;
            $data['completed_at'] = null;
        }

        $data['status'] = $status->value;
        $data['progress'] = $progress;

        if ($status !== TaskStatus::Blocked) {
            $data['blocked_reason'] = null;
        }

        return $data;
    }
}