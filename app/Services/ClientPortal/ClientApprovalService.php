<?php

namespace App\Services\ClientPortal;

use App\Enums\ActivityVisibility;
use App\Enums\ApprovalStatus;
use App\Enums\ClientApprovalDecision;
use App\Models\ClientUser;
use App\Models\ProjectApproval;
use App\Services\Projects\ProjectActivityService;
use App\Services\Projects\ProjectProgressService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientApprovalService
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService,
        private readonly ProjectProgressService $progressService,
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function decide(
        ProjectApproval $approval,
        ClientUser $clientUser,
        ClientApprovalDecision $decision,
        ?string $feedback
    ): ProjectApproval {
        return DB::transaction(
            function () use (
                $approval,
                $clientUser,
                $decision,
                $feedback
            ): ProjectApproval {
                $approval =
                    ProjectApproval::query()
                        ->with('project')
                        ->lockForUpdate()
                        ->findOrFail(
                            $approval->id
                        );

                $this->accessService
                    ->accessFor(
                        clientUser:
                            $clientUser,

                        project:
                            $approval->project,

                        capability:
                            'approve'
                    );

                if (
                    !$approval->is_client_visible
                    || !$approval
                        ->submitted_to_client_at
                ) {
                    throw ValidationException::withMessages([
                        'decision' =>
                            'This approval is not currently available for client review.',
                    ]);
                }

                if (
                    $approval->client_decision !==
                    ClientApprovalDecision::Pending
                ) {
                    throw ValidationException::withMessages([
                        'decision' =>
                            'A client decision has already been recorded.',
                    ]);
                }

                $approval->forceFill([
                    'client_decision' =>
                        $decision->value,

                    'client_feedback' =>
                        $feedback,

                    'client_decided_at' =>
                        now(),

                    'client_decided_by' =>
                        $clientUser->id,

                    /*
                     * Keep Phase 3 official progress
                     * connected to the actual client decision.
                     */
                    'status' =>
                        $decision ===
                        ClientApprovalDecision::Approved
                            ? ApprovalStatus::Approved
                                ->value
                            : ApprovalStatus::ChangesRequested
                                ->value,
                ])->saveQuietly();

                $this->progressService
                    ->synchronizeOfficialProgress(
                        $approval->project
                    );

                $this->activityService->logCustom(
                    project:
                        $approval->project,

                    event:
                        $decision ===
                        ClientApprovalDecision::Approved
                            ? 'client_approval_approved'
                            : 'client_approval_changes_requested',

                    title:
                        "{$approval->stage->label()} {$decision->label()}",

                    description:
                        $feedback,

                    subject:
                        $approval,

                    metadata: [
                        'client_user_id' =>
                            $clientUser->id,

                        'client_user_name' =>
                            $clientUser->name,

                        'decision' =>
                            $decision->value,
                    ],

                    visibility:
                        ActivityVisibility::Team,

                    actorId: null
                );

                return $approval->refresh();
            }
        );
    }
}