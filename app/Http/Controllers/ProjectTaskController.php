<?php

namespace App\Http\Controllers;

use App\Enums\NotificationSeverity;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreProjectTaskRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Projects\ProjectProgressService;
use Illuminate\Http\RedirectResponse;

class ProjectTaskController extends Controller
{
    public function __construct(
        private readonly ProjectProgressService $progressService,
        private readonly NotificationDispatcher $notificationDispatcher
    ) {
    }

    public function store(
        StoreProjectTaskRequest $request,
        Project $project
    ): RedirectResponse {
        $data = $this->normaliseTaskData(
            $request->validated()
        );

        $task = $project->tasks()->create([
            ...$data,
            'created_by' => $request->user()->id,
            'sort_order' => (
                (int) $project
                    ->tasks()
                    ->max('sort_order')
            ) + 1,
        ]);

        $task->loadMissing(
            'assignee',
            'project'
        );

        if ($task->assignee) {
            $this->notificationDispatcher->send(
                recipients: $task->assignee,
                eventKey: 'task.assigned',
                title: 'New project task assigned',

                message:
                    "{$task->title} has been assigned to you for {$task->project->name}.",

                url: route(
                    'projects.show',
                    [
                        'project' =>
                            $task->project_id,

                        'tab' => 'tasks',
                    ]
                ),

                severity:
                    NotificationSeverity::Info,

                subject: $task,

                context: [
                    'project_id' =>
                        $task->project_id,

                    'task_id' => $task->id,
                ],

                dedupeBucket:
                    "task-assigned:{$task->id}:{$task->assigned_to}"
            );
        }

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

        $oldAssignedTo = $projectTask->assigned_to;

        $data = $this->normaliseTaskData(
            $request->validated(),
            $projectTask
        );

        $projectTask->update($data);

        if (
            $projectTask->assigned_to
            && $oldAssignedTo !==
                $projectTask->assigned_to
        ) {
            $projectTask->loadMissing(
                'assignee',
                'project'
            );

            $this->notificationDispatcher->send(
                recipients:
                    $projectTask->assignee,

                eventKey:
                    'task.assigned',

                title:
                    'Project task reassigned',

                message:
                    "{$projectTask->title} has been assigned to you.",

                url: route(
                    'projects.show',
                    [
                        'project' =>
                            $projectTask
                                ->project_id,

                        'tab' => 'tasks',
                    ]
                ),

                severity:
                    NotificationSeverity::Info,

                subject:
                    $projectTask,

                dedupeBucket:
                    "task-reassigned:{$projectTask->id}:{$projectTask->assigned_to}:{$projectTask->updated_at->timestamp}"
            );
        }

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

