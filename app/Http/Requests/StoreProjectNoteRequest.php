<?php

namespace App\Http\Controllers;

use App\Enums\ActivityVisibility;
use App\Enums\ProjectNoteVisibility;
use App\Http\Requests\StoreProjectNoteRequest;
use App\Http\Requests\UpdateProjectNoteRequest;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Services\Attachments\ProjectAttachmentService;
use App\Services\Projects\ProjectActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectNoteController extends Controller
{
    public function __construct(
        private readonly ProjectAttachmentService $attachmentService,
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function store(
        StoreProjectNoteRequest $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validated();

        $note = DB::transaction(
            function () use (
                $request,
                $project,
                $validated
            ): ProjectNote {
                $note = $project->notes()->create([
                    'title' =>
                        $validated['title'] ?? null,

                    'note_type' =>
                        $validated['note_type'],

                    'visibility' =>
                        $validated['visibility'],

                    'content' =>
                        $validated['content'],

                    'is_pinned' =>
                        $validated['is_pinned']
                        && $request
                            ->user()
                            ->can('notes.pin'),

                    'pinned_at' =>
                        $validated['is_pinned']
                        && $request
                            ->user()
                            ->can('notes.pin')
                            ? now()
                            : null,

                    'pinned_by' =>
                        $validated['is_pinned']
                        && $request
                            ->user()
                            ->can('notes.pin')
                            ? $request->user()->id
                            : null,

                    'created_by' =>
                        $request->user()->id,

                    'updated_by' =>
                        $request->user()->id,
                ]);

                if ($request->hasFile('attachments')) {
                    $visibility =
                        $note->activityVisibility();

                    $this->attachmentService
                        ->storeMany(
                            project: $project,
                            attachable: $note,

                            files: $request->file(
                                'attachments'
                            ),

                            uploadedBy:
                                $request->user(),

                            category: 'note',
                            visibility: $visibility,

                            visibleToUserId:
                                $note
                                    ->activityVisibleToUserId()
                        );
                }

                return $note;
            }
        );

        return redirect()
            ->route('projects.show', [
                'project' => $project,
                'tab' => 'notes',
            ])
            ->with(
                'success',
                'Project note created successfully.'
            );
    }

    public function update(
        UpdateProjectNoteRequest $request,
        Project $project,
        ProjectNote $projectNote
    ): RedirectResponse {
        $this->ensureProjectNote(
            $project,
            $projectNote
        );

        abort_unless(
            $projectNote->canBeManagedBy(
                $request->user()
            ),
            403
        );

        $validated = $request->validated();

        DB::transaction(
            function () use (
                $request,
                $project,
                $projectNote,
                $validated
            ): void {
                $projectNote->update([
                    'title' =>
                        $validated['title'] ?? null,

                    'note_type' =>
                        $validated['note_type'],

                    'visibility' =>
                        $validated['visibility'],

                    'content' =>
                        $validated['content'],

                    'updated_by' =>
                        $request->user()->id,
                ]);

                if ($request->hasFile('attachments')) {
                    $this->attachmentService
                        ->storeMany(
                            project: $project,
                            attachable: $projectNote,

                            files: $request->file(
                                'attachments'
                            ),

                            uploadedBy:
                                $request->user(),

                            category: 'note',

                            visibility:
                                $projectNote
                                    ->activityVisibility(),

                            visibleToUserId:
                                $projectNote
                                    ->activityVisibleToUserId()
                        );
                }
            }
        );

        return back()->with(
            'success',
            'Project note updated successfully.'
        );
    }

    public function togglePin(
        Project $project,
        ProjectNote $projectNote
    ): RedirectResponse {
        $this->ensureProjectNote(
            $project,
            $projectNote
        );

        abort_unless(
            request()->user()->can('notes.pin'),
            403
        );

        $newPinnedState =
            !$projectNote->is_pinned;

        $projectNote->forceFill([
            'is_pinned' => $newPinnedState,

            'pinned_at' =>
                $newPinnedState
                    ? now()
                    : null,

            'pinned_by' =>
                $newPinnedState
                    ? request()->user()->id
                    : null,

            'updated_by' =>
                request()->user()->id,
        ])->saveQuietly();

        $visibility =
            $projectNote->activityVisibility();

        $this->activityService->logCustom(
            project: $project,

            event:
                $newPinnedState
                    ? 'pinned'
                    : 'unpinned',

            title:
                $newPinnedState
                    ? 'Project note pinned'
                    : 'Project note unpinned',

            description:
                $projectNote->title,

            subject:
                $projectNote,

            visibility:
                $visibility,

            visibleToUserId:
                $projectNote
                    ->activityVisibleToUserId()
        );

        return back()->with(
            'success',
            $newPinnedState
                ? 'Note pinned successfully.'
                : 'Note removed from pinned information.'
        );
    }

    public function destroy(
        Project $project,
        ProjectNote $projectNote,
        ProjectAttachmentService $attachmentService
    ): RedirectResponse {
        $this->ensureProjectNote(
            $project,
            $projectNote
        );

        abort_unless(
            $projectNote->canBeManagedBy(
                request()->user()
            ),
            403
        );

        $attachmentService
            ->deleteForAttachable(
                $projectNote,
                request()->user()
            );

        $projectNote->delete();

        return back()->with(
            'success',
            'Project note deleted.'
        );
    }

    private function ensureProjectNote(
        Project $project,
        ProjectNote $projectNote
    ): void {
        abort_unless(
            $projectNote->project_id ===
                $project->id,
            404
        );

        abort_unless(
            $projectNote->isVisibleTo(
                request()->user()
            ),
            403
        );
    }
}