<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectWorkLogRequest;
use App\Http\Requests\UpdateProjectWorkLogRequest;
use App\Models\Project;
use App\Models\ProjectWorkLog;
use App\Services\Attachments\ProjectAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectWorkLogController extends Controller
{
    public function __construct(
        private readonly ProjectAttachmentService $attachmentService
    ) {
    }

    public function store(
        StoreProjectWorkLogRequest $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(
            function () use (
                $request,
                $project,
                $validated
            ): void {
                $workLog = $project
                    ->workLogs()
                    ->create([
                        'project_task_id' =>
                            $validated[
                                'project_task_id'
                            ] ?? null,

                        'logged_by' =>
                            $request->user()->id,

                        'work_date' =>
                            $validated['work_date'],

                        'work_type' =>
                            $validated['work_type'],

                        'status' =>
                            $validated['status'],

                        'title' =>
                            $validated['title'],

                        'details' =>
                            $validated['details']
                            ?? null,

                        'outcome' =>
                            $validated['outcome']
                            ?? null,

                        'blocker' =>
                            $validated['blocker']
                            ?? null,

                        'duration_minutes' =>
                            $validated[
                                'duration_minutes'
                            ],

                        'is_billable' =>
                            $validated['is_billable'],
                    ]);

                if ($request->hasFile('attachments')) {
                    $this->attachmentService
                        ->storeMany(
                            project: $project,
                            attachable: $workLog,

                            files: $request->file(
                                'attachments'
                            ),

                            uploadedBy:
                                $request->user(),

                            category: 'work_log'
                        );
                }
            }
        );

        return redirect()
            ->route('projects.show', [
                'project' => $project,
                'tab' => 'work-logs',
            ])
            ->with(
                'success',
                'Work log recorded successfully.'
            );
    }

    public function update(
        UpdateProjectWorkLogRequest $request,
        Project $project,
        ProjectWorkLog $projectWorkLog
    ): RedirectResponse {
        $this->ensureWorkLog(
            $project,
            $projectWorkLog
        );

        abort_unless(
            $projectWorkLog->canBeManagedBy(
                $request->user()
            ),
            403
        );

        $validated = $request->validated();

        DB::transaction(
            function () use (
                $request,
                $project,
                $projectWorkLog,
                $validated
            ): void {
                $projectWorkLog->update([
                    'project_task_id' =>
                        $validated[
                            'project_task_id'
                        ] ?? null,

                    'work_date' =>
                        $validated['work_date'],

                    'work_type' =>
                        $validated['work_type'],

                    'status' =>
                        $validated['status'],

                    'title' =>
                        $validated['title'],

                    'details' =>
                        $validated['details']
                        ?? null,

                    'outcome' =>
                        $validated['outcome']
                        ?? null,

                    'blocker' =>
                        $validated['blocker']
                        ?? null,

                    'duration_minutes' =>
                        $validated[
                            'duration_minutes'
                        ],

                    'is_billable' =>
                        $validated['is_billable'],
                ]);

                if ($request->hasFile('attachments')) {
                    $this->attachmentService
                        ->storeMany(
                            project: $project,
                            attachable:
                                $projectWorkLog,

                            files: $request->file(
                                'attachments'
                            ),

                            uploadedBy:
                                $request->user(),

                            category: 'work_log'
                        );
                }
            }
        );

        return back()->with(
            'success',
            'Work log updated successfully.'
        );
    }

    public function destroy(
        Project $project,
        ProjectWorkLog $projectWorkLog
    ): RedirectResponse {
        $this->ensureWorkLog(
            $project,
            $projectWorkLog
        );

        abort_unless(
            $projectWorkLog->canBeManagedBy(
                request()->user()
            ),
            403
        );

        $this->attachmentService
            ->deleteForAttachable(
                $projectWorkLog,
                request()->user()
            );

        $projectWorkLog->delete();

        return back()->with(
            'success',
            'Work log deleted.'
        );
    }

    private function ensureWorkLog(
        Project $project,
        ProjectWorkLog $projectWorkLog
    ): void {
        abort_unless(
            $projectWorkLog->project_id ===
                $project->id,
            404
        );
    }
}