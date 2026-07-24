<?php

namespace App\Http\Controllers;

use App\Enums\ActivityVisibility;
use App\Http\Requests\StoreProjectAttachmentRequest;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\Attachments\ProjectAttachmentService;
use App\Services\Projects\ProjectActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectAttachmentController extends Controller
{
    public function __construct(
        private readonly ProjectAttachmentService $attachmentService,
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function store(
        StoreProjectAttachmentRequest $request,
        Project $project
    ): RedirectResponse {
        $this->attachmentService->storeMany(
            project: $project,
            attachable: $project,

            files:
                $request->file('attachments'),

            uploadedBy:
                $request->user(),

            category: 'general'
        );

        return redirect()
            ->route('projects.show', [
                'project' => $project,
                'tab' => 'attachments',
            ])
            ->with(
                'success',
                'Project attachments uploaded successfully.'
            );
    }

    public function download(
        Project $project,
        ProjectFile $projectFile
    ): StreamedResponse {
        abort_unless(
            $projectFile->project_id ===
                $project->id,
            404
        );

        abort_unless(
            $projectFile->isAccessibleBy(
                request()->user()
            ),
            403
        );

        abort_unless(
            Storage::disk(
                $projectFile->disk
            )->exists(
                $projectFile->path
            ),
            404,
            'The attachment file could not be found.'
        );

        $projectFile->increment(
            'download_count'
        );

        $projectFile->forceFill([
            'last_downloaded_at' => now(),
        ])->saveQuietly();

        $this->activityService->logCustom(
            project: $project,
            event: 'attachment_downloaded',

            title:
                "Attachment downloaded: {$projectFile->original_name}",

            metadata: [
                'project_file_id' =>
                    $projectFile->id,
            ],

            visibility:
                $this->activityVisibilityForFile(
                    $projectFile
                )
        );

        return Storage::disk(
            $projectFile->disk
        )->download(
            $projectFile->path,
            $projectFile->original_name
        );
    }

    public function destroy(
        Project $project,
        ProjectFile $projectFile
    ): RedirectResponse {
        abort_unless(
            $projectFile->project_id ===
                $project->id,
            404
        );

        $user = request()->user();

        $canDelete =
            $user->hasRole('super-admin')
            || $user->can('attachments.delete')
            || (
                $projectFile->uploaded_by ===
                    $user->id
                && in_array(
                    $projectFile->category,
                    [
                        'general',
                        'note',
                        'work_log',
                    ],
                    true
                )
            );

        abort_unless($canDelete, 403);

        $this->attachmentService->delete(
            $projectFile,
            $user
        );

        return back()->with(
            'success',
            'Attachment deleted successfully.'
        );
    }

    private function activityVisibilityForFile(
        ProjectFile $projectFile
    ): ActivityVisibility {
        return match ($projectFile->category) {
            'payment',
            'expense' =>
                ActivityVisibility::Financial,

            'approval' =>
                ActivityVisibility::Management,

            default =>
                ActivityVisibility::Team,
        };
    }
}