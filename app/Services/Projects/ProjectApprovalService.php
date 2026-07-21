<?php

namespace App\Services\Projects;

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectApprovalService
{
    public function __construct(
        private readonly ProjectProgressService $progressService
    ) {
    }

    public function assertCanSubmit(
        Project $project,
        ApprovalStage $stage
    ): void {
        $alreadyApproved = $project
            ->approvals()
            ->where('stage', $stage->value)
            ->where(
                'status',
                ApprovalStatus::Approved->value
            )
            ->exists();

        if ($alreadyApproved) {
            throw ValidationException::withMessages([
                'stage' =>
                    "{$stage->label()} has already been approved.",
            ]);
        }

        $pendingSubmission = $project
            ->approvals()
            ->where('stage', $stage->value)
            ->where(
                'status',
                ApprovalStatus::Submitted->value
            )
            ->exists();

        if ($pendingSubmission) {
            throw ValidationException::withMessages([
                'stage' =>
                    "A pending {$stage->label()} submission already exists.",
            ]);
        }

        if (
            $stage === ApprovalStage::Backend &&
            !$project->hasApprovedStage(
                ApprovalStage::Frontend
            )
        ) {
            throw ValidationException::withMessages([
                'stage' =>
                    'Frontend approval must be recorded before backend approval can be submitted.',
            ]);
        }
    }

    public function submit(
        Project $project,
        ApprovalStage $stage,
        User $submittedBy,
        array $data,
        ?int $proofFileId = null
    ): ProjectApproval {
        $this->assertCanSubmit($project, $stage);

        return DB::transaction(
            function () use (
                $project,
                $stage,
                $submittedBy,
                $data,
                $proofFileId
            ): ProjectApproval {
                $submissionNumber = (
                    (int) $project
                        ->approvals()
                        ->where('stage', $stage->value)
                        ->max('submission_number')
                ) + 1;

                $approval = $project
                    ->approvals()
                    ->create([
                        'stage' => $stage->value,
                        'submission_number' =>
                            $submissionNumber,

                        'status' =>
                            ApprovalStatus::Submitted->value,

                        'submitted_by' => $submittedBy->id,
                        'submitted_at' => now(),

                        'submission_notes' =>
                            $data['submission_notes']
                            ?? null,

                        'internal_remarks' =>
                            $data['internal_remarks']
                            ?? null,

                        'proof_file_id' => $proofFileId,
                    ]);

                $project->update([
                    'status' => match ($stage) {
                        ApprovalStage::Frontend =>
                            ProjectStatus::FrontendSubmitted->value,

                        ApprovalStage::Backend =>
                            ProjectStatus::BackendSubmitted->value,
                    },

                    'updated_by' => $submittedBy->id,
                ]);

                return $approval;
            }
        );
    }

    public function review(
        ProjectApproval $approval,
        ApprovalStatus $status,
        User $reviewedBy,
        array $data
    ): void {
        if ($approval->status !== ApprovalStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only a submitted approval can be reviewed.',
            ]);
        }

        if (!$status->isFinal()) {
            throw ValidationException::withMessages([
                'status' =>
                    'Select approved, changes requested or rejected.',
            ]);
        }

        $approval->loadMissing('project');

        $project = $approval->project;

        if (
            $approval->stage === ApprovalStage::Backend &&
            $status === ApprovalStatus::Approved &&
            !$project->hasApprovedStage(
                ApprovalStage::Frontend
            )
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'Backend cannot be approved before frontend approval.',
            ]);
        }

        DB::transaction(
            function () use (
                $approval,
                $status,
                $reviewedBy,
                $data,
                $project
            ): void {
                $approval->update([
                    'status' => $status->value,

                    'reviewed_by' => $reviewedBy->id,
                    'reviewed_at' => now(),

                    'client_reviewer_name' =>
                        $data['client_reviewer_name']
                        ?? null,

                    'client_reviewer_email' =>
                        $data['client_reviewer_email']
                        ?? null,

                    'client_reviewer_phone' =>
                        $data['client_reviewer_phone']
                        ?? null,

                    'client_remarks' =>
                        $data['client_remarks']
                        ?? null,

                    'internal_remarks' =>
                        $data['internal_remarks']
                        ?? $approval->internal_remarks,
                ]);

                $projectStatus = match ([
                    $approval->stage,
                    $status,
                ]) {
                    [
                        ApprovalStage::Frontend,
                        ApprovalStatus::Approved,
                    ] =>
                        ProjectStatus::FrontendApproved->value,

                    [
                        ApprovalStage::Frontend,
                        ApprovalStatus::ChangesRequested,
                    ],
                    [
                        ApprovalStage::Frontend,
                        ApprovalStatus::Rejected,
                    ] =>
                        ProjectStatus::FrontendRevision->value,

                    [
                        ApprovalStage::Backend,
                        ApprovalStatus::Approved,
                    ] =>
                        ProjectStatus::BackendApproved->value,

                    [
                        ApprovalStage::Backend,
                        ApprovalStatus::ChangesRequested,
                    ],
                    [
                        ApprovalStage::Backend,
                        ApprovalStatus::Rejected,
                    ] =>
                        ProjectStatus::BackendRevision->value,

                    default => $project->status->value,
                };

                $project->update([
                    'status' => $projectStatus,
                    'updated_by' => $reviewedBy->id,
                ]);

                $this->progressService
                    ->synchronizeOfficialProgress($project);
            }
        );
    }
}