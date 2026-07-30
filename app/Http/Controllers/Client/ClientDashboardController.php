<?php

namespace App\Http\Controllers\Client;

use App\Enums\ClientApprovalDecision;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\ProjectApproval;
use App\Models\ProjectTicket;
use Illuminate\Contracts\View\View;

class ClientDashboardController extends Controller
{
    public function __invoke(): View
    {
        $clientUser =
            auth('client')->user();

        $projects =
            $clientUser->projects()
                ->wherePivot(
                    'is_active',
                    true
                )
                ->wherePivot(
                    'can_view_project',
                    true
                )
                ->where(
                    'client_portal_enabled',
                    true
                )
                ->with([
                    'client',
                    'manager',
                ])
                ->withCount([
                    'tickets' =>
                        fn ($query) =>
                            $query
                                ->where(
                                    'client_visible',
                                    true
                                )
                                ->whereIn(
                                    'status',
                                    TicketStatus::activeValues()
                                ),
                ])
                ->orderByDesc(
                    'projects.updated_at'
                )
                ->get();

        $projectIds =
            $projects->pluck('id');

        $summary = [
            'projects' =>
                $projects->count(),

            'pending_approvals' =>
                ProjectApproval::query()
                    ->whereIn(
                        'project_id',
                        $projectIds
                    )
                    ->where(
                        'is_client_visible',
                        true
                    )
                    ->where(
                        'client_decision',
                        ClientApprovalDecision::Pending
                            ->value
                    )
                    ->count(),

            'open_tickets' =>
                ProjectTicket::query()
                    ->whereIn(
                        'project_id',
                        $projectIds
                    )
                    ->where(
                        'client_visible',
                        true
                    )
                    ->whereIn(
                        'status',
                        TicketStatus::activeValues()
                    )
                    ->count(),

            'unread_notifications' =>
                $clientUser
                    ->unreadNotifications()
                    ->count(),
        ];

        return view(
            'client.dashboard',
            compact(
                'clientUser',
                'projects',
                'summary'
            )
        );
    }
}