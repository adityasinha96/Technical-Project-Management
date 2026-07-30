<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientApprovalDecision;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Notifications\ClientPortalAlertNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ClientPortalApprovalController extends Controller
{
    public function store(
        Project $project,
        ProjectApproval $approval
    ): RedirectResponse {
        abort_unless(
            request()->user()->can(
                'client-portal.manage'
            ),
            403
        );

        abort_unless(
            $approval->project_id ===
                $project->id,
            404
        );

        DB::transaction(
            function () use (
                $project,
                $approval
            ): void {
                $approval->update([
                    'is_client_visible' =>
                        true,

                    'submitted_to_client_at' =>
                        now(),

                    'submitted_to_client_by' =>
                        request()->user()->id,

                    'client_decision' =>
                        ClientApprovalDecision::Pending
                            ->value,

                    'client_feedback' =>
                        null,

                    'client_decided_at' =>
                        null,

                    'client_decided_by' =>
                        null,
                ]);

                $clientUsers =
                    $project->clientUsers()
                        ->wherePivot(
                            'is_active',
                            true
                        )
                        ->wherePivot(
                            'can_approve',
                            true
                        )
                        ->get();

                foreach (
                    $clientUsers
                    as $clientUser
                ) {
                    $clientUser->notify(
                        new ClientPortalAlertNotification(
                            title:
                                'Project approval required',

                            message:
                                "{$project->name} has a {$approval->stage->label()} approval awaiting your review.",

                            url:
                                route(
                                    'client.projects.show',
                                    $project
                                ),

                            severity:
                                'warning'
                        )
                    );
                }
            }
        );

        return back()->with(
            'success',
            'Approval submitted to authorised client contacts.'
        );
    }
}