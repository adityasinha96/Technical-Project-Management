<?php

namespace App\Http\Controllers\Client;

use App\Enums\ClientApprovalDecision;
use App\Enums\PaymentStatus;
use App\Enums\TicketCommentVisibility;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientPortal\ClientPortalAccessService;
use Illuminate\Contracts\View\View;

class ClientProjectController extends Controller
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService
    ) {
    }

    public function show(
        Project $project
    ): View {
        $clientUser =
            auth('client')->user();

        $access =
            $this->accessService
                ->accessFor(
                    $clientUser,
                    $project,
                    'view'
                );

        $project->load([
            'client',
            'manager',
            'category',
        ]);

        $approvals =
            $project->approvals()
                ->where(
                    'is_client_visible',
                    true
                )
                ->with([
                    'clientDecidedBy',
                ])
                ->latest()
                ->get();

        $payments =
            $access->can_view_financials
                ? $project->payments()
                    ->where(
                        'status',
                        PaymentStatus::Cleared
                            ->value
                    )
                    ->whereNull(
                        'voided_at'
                    )
                    ->latest(
                        'payment_date'
                    )
                    ->get()
                : collect();

        $tickets =
            $project->tickets()
                ->where(
                    'client_visible',
                    true
                )
                ->with([
                    'assignedTo',
                ])
                ->latest()
                ->get();

        $files =
            $access->can_view_files
                ? $project->files()
                    ->where(
                        'client_visible',
                        true
                    )
                    ->latest(
                        'shared_with_client_at'
                    )
                    ->get()
                : collect();

        $communications =
            $access->can_communicate
                ? $project
                    ->clientCommunications()
                    ->with([
                        'clientUser',
                        'user',
                        'fileLinks.file',
                    ])
                    ->oldest()
                    ->get()
                : collect();

        $pendingApprovalCount =
            $approvals
                ->where(
                    'client_decision',
                    ClientApprovalDecision::Pending
                )
                ->count();

        return view(
            'client.projects.show',
            compact(
                'clientUser',
                'project',
                'access',
                'approvals',
                'payments',
                'tickets',
                'files',
                'communications',
                'pendingApprovalCount'
            )
        );
    }
}