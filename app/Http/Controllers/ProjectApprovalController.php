<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Http\Requests\ReviewProjectApprovalRequest;
use App\Http\Requests\SubmitProjectApprovalRequest;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\ProjectFile;
use App\Services\Projects\ProjectApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProjectApprovalController extends Controller
{
    public function __construct(
        private readonly ProjectApprovalService $approvalService
    ) {
    }

    public function store(
        SubmitProjectApprovalRequest $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validated();

        $stage = ApprovalStage::from(
            $validated['stage']
        );

        /*
         * Validate the workflow before storing
         * any approval-proof file.
         */
        $this->approvalService
            ->assertCanSubmit($project, $stage);

        $projectFile = null;

        try {
            if ($request->hasFile('proof')) {
                $projectFile = $this->storeProofFile(
                    $request,
                    $project,
                    $stage
                );
            }

            $this->approvalService->submit(
                project: $project,
                stage: $stage,
                submittedBy: $request->user(),
                data: $validated,
                proofFileId: $projectFile?->id
            );
        } catch (Throwable $exception) {
            if ($projectFile) {
                Storage::disk($projectFile->disk)
                    ->delete($projectFile->path);

                $projectFile->forceDelete();
            }

            throw $exception;
        }

        return back()->with(
            'success',
            "{$stage->label()} submitted successfully."
        );
    }

    public function review(
        ReviewProjectApprovalRequest $request,
        Project $project,
        ProjectApproval $projectApproval
    ): RedirectResponse {
        abort_unless(
            $projectApproval->project_id === $project->id,
            404
        );

        $status = ApprovalStatus::from(
            $request->validated('status')
        );

        $this->approvalService->review(
            approval: $projectApproval,
            status: $status,
            reviewedBy: $request->user(),
            data: $request->validated()
        );

        return back()->with(
            'success',
            "Approval marked as {$status->label()}."
        );
    }

    private function storeProofFile(
        SubmitProjectApprovalRequest $request,
        Project $project,
        ApprovalStage $stage
    ): ProjectFile {
        $uploadedFile = $request->file('proof');

        $extension = strtolower(
            $uploadedFile->getClientOriginalExtension()
        );

        $storedName = Str::uuid()
            . ($extension ? ".{$extension}" : '');

        $path = $uploadedFile->storeAs(
            "projects/{$project->id}/approvals",
            $storedName,
            'public'
        );

        return $project->files()->create([
            'uploaded_by' => $request->user()->id,
            'category' => 'approval',

            'original_name' =>
                $uploadedFile->getClientOriginalName(),

            'stored_name' => $storedName,
            'path' => $path,
            'disk' => 'public',

            'mime_type' =>
                $uploadedFile->getMimeType(),

            'size' =>
                $uploadedFile->getSize(),

            'description' =>
                "{$stage->label()} submission proof.",
        ]);
    }
}