<?php

namespace App\Http\Controllers\Client;

use App\Enums\ClientApprovalDecision;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ClientApprovalDecisionRequest;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Services\ClientPortal\ClientApprovalService;
use Illuminate\Http\RedirectResponse;

class ClientApprovalController extends Controller
{
    public function __construct(
        private readonly ClientApprovalService $approvalService
    ) {
    }

    public function update(
        ClientApprovalDecisionRequest $request,
        Project $project,
        ProjectApproval $approval
    ): RedirectResponse {
        abort_unless(
            $approval->project_id ===
                $project->id,
            404
        );

        $decision =
            ClientApprovalDecision::from(
                $request->validated(
                    'decision'
                )
            );

        $this->approvalService->decide(
            approval: $approval,
            clientUser:
                auth('client')->user(),

            decision: $decision,

            feedback:
                $request->validated(
                    'feedback'
                )
        );

        return back()->with(
            'success',
            $decision ===
                ClientApprovalDecision::Approved
                    ? 'Approval recorded successfully.'
                    : 'Your requested changes have been sent to the project team.'
        );
    }
}