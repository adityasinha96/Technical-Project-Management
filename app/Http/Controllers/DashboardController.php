<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStatus;
use App\Enums\ClientStatus;
use App\Enums\PaymentFollowupStatus;
use App\Enums\PaymentKind;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentFollowup;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectApproval;
use App\Models\ProjectNote;
use App\Models\ProjectTask;
use App\Models\ProjectTicket;
use App\Models\ProjectWorkLog;
use App\Models\SystemSetting;
use App\Services\Reports\ProfitabilityReportService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ProfitabilityReportService $profitabilityReportService
    ) {
    }

    public function index(): View
    {
        $user = request()->user();

        /*
        |--------------------------------------------------------------------------
        | Phase 6 Dashboard Configuration
        |--------------------------------------------------------------------------
        */

        $projectInactivityDays = max(
            1,
            (int) (
                SystemSetting::query()
                    ->where(
                        'key',
                        'project_inactivity_days'
                    )
                    ->value('value')
                ?: 3
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Profitability Report Summaries
        |--------------------------------------------------------------------------
        */

        $profitabilitySummary =
            $this->profitabilityReportService
                ->summary();

        $currentMonthFinancials =
            $this->profitabilityReportService
                ->monthSummary();

        $monthlyFinancials =
            $this->profitabilityReportService
                ->monthly(6);

        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            /*
            |--------------------------------------------------------------------------
            | Client Statistics
            |--------------------------------------------------------------------------
            */

            'total_clients' => Client::query()
                ->count(),

            'active_clients' => Client::query()
                ->where(
                    'status',
                    ClientStatus::Active->value
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Project Statistics
            |--------------------------------------------------------------------------
            */

            'total_projects' => Project::query()
                ->count(),

            'active_projects' => Project::query()
                ->open()
                ->count(),

            'completed_projects' => Project::query()
                ->where(
                    'status',
                    ProjectStatus::Completed->value
                )
                ->count(),

            'delayed_projects' => Project::query()
                ->open()
                ->whereRaw(
                    'COALESCE(
                        revised_delivery_date,
                        expected_delivery_date
                    ) < ?',
                    [
                        today()->toDateString(),
                    ]
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Project Financial Statistics
            |--------------------------------------------------------------------------
            */

            'total_project_value' => Project::query()
                ->sum('project_price'),

            'estimated_profit' => Project::query()
                ->selectRaw(
                    'COALESCE(
                        SUM(project_price - estimated_cost),
                        0
                    ) AS total'
                )
                ->value('total') ?? 0,

            /*
            |--------------------------------------------------------------------------
            | Phase 3 Workflow Statistics
            |--------------------------------------------------------------------------
            */

            'pending_approvals' => ProjectApproval::query()
                ->where(
                    'status',
                    ApprovalStatus::Submitted->value
                )
                ->count(),

            'overdue_tasks' => ProjectTask::query()
                ->whereNotNull('due_date')
                ->whereNotIn(
                    'status',
                    [
                        TaskStatus::Completed->value,
                        TaskStatus::Cancelled->value,
                    ]
                )
                ->whereDate(
                    'due_date',
                    '<',
                    today()
                )
                ->count(),

            'blocked_tasks' => ProjectTask::query()
                ->where(
                    'status',
                    TaskStatus::Blocked->value
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Phase 4 Payment Statistics
            |--------------------------------------------------------------------------
            */

            'total_received' => Project::query()
                ->sum('net_received_amount'),

            'market_outstanding' => Project::query()
                ->sum('pending_amount'),

            'projects_with_pending' => Project::query()
                ->where(
                    'pending_amount',
                    '>',
                    0
                )
                ->count(),

            'current_month_collection' =>
                (
                    (float) Payment::query()
                        ->effective()
                        ->receipts()
                        ->whereBetween(
                            'payment_date',
                            [
                                now()
                                    ->startOfMonth()
                                    ->toDateString(),

                                now()
                                    ->endOfMonth()
                                    ->toDateString(),
                            ]
                        )
                        ->sum('amount')
                )
                -
                (
                    (float) Payment::query()
                        ->effective()
                        ->refunds()
                        ->whereBetween(
                            'payment_date',
                            [
                                now()
                                    ->startOfMonth()
                                    ->toDateString(),

                                now()
                                    ->endOfMonth()
                                    ->toDateString(),
                            ]
                        )
                        ->sum('amount')
                ),

            'overdue_payment_followups' =>
                PaymentFollowup::query()
                    ->open()
                    ->whereRaw(
                        'COALESCE(
                            next_followup_at,
                            followup_at
                        ) < ?',
                        [
                            now(),
                        ]
                    )
                    ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Delayed Projects
        |--------------------------------------------------------------------------
        */

        $delayedProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->open()
            ->whereRaw(
                'COALESCE(
                    revised_delivery_date,
                    expected_delivery_date
                ) < ?',
                [
                    today()->toDateString(),
                ]
            )
            ->orderByRaw(
                'COALESCE(
                    revised_delivery_date,
                    expected_delivery_date
                ) ASC'
            )
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Projects
        |--------------------------------------------------------------------------
        */

        $recentProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->latest()
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Projects With Highest Outstanding Balances
        |--------------------------------------------------------------------------
        */

        $outstandingProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'pending_amount',
                '>',
                0
            )
            ->orderByDesc('pending_amount')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Overdue Payment Follow-ups
        |--------------------------------------------------------------------------
        */

        $overduePaymentFollowups =
            PaymentFollowup::query()
                ->with([
                    'project',
                    'client',
                    'assignedTo',
                ])
                ->open()
                ->whereRaw(
                    'COALESCE(
                        next_followup_at,
                        followup_at
                    ) < ?',
                    [
                        now(),
                    ]
                )
                ->orderByRaw(
                    'COALESCE(
                        next_followup_at,
                        followup_at
                    ) ASC'
                )
                ->limit(6)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Loss-Making Projects
        |--------------------------------------------------------------------------
        */

        $lossMakingProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'actual_profit_amount',
                '<',
                0
            )
            ->orderBy('actual_profit_amount')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Cash-Negative Projects
        |--------------------------------------------------------------------------
        */

        $cashNegativeProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'cash_position_amount',
                '<',
                0
            )
            ->orderBy('cash_position_amount')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Visible Project Activities
        |--------------------------------------------------------------------------
        */

        $recentActivities = ProjectActivity::query()
            ->visibleTo($user)
            ->with([
                'project',
                'actor',
            ])
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Inactive Projects
        |--------------------------------------------------------------------------
        */

        $inactiveProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->whereNotIn(
                'status',
                [
                    ProjectStatus::Completed->value,
                    ProjectStatus::Cancelled->value,
                ]
            )
            ->where(
                function ($query) use (
                    $projectInactivityDays
                ): void {
                    $cutoff = now()
                        ->subDays(
                            $projectInactivityDays
                        );

                    $query
                        ->where(
                            'last_activity_at',
                            '<',
                            $cutoff
                        )
                        ->orWhere(
                            function ($query) use ($cutoff): void {
                                $query
                                    ->whereNull(
                                        'last_activity_at'
                                    )
                                    ->where(
                                        'created_at',
                                        '<',
                                        $cutoff
                                    );
                            }
                        );
                }
            )
            ->orderByRaw(
                'COALESCE(last_activity_at, created_at) ASC'
            )
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Today Work Log Statistics
        |--------------------------------------------------------------------------
        */

        $todayWorkMinutes = ProjectWorkLog::query()
            ->whereDate(
                'work_date',
                today()
            )
            ->sum('duration_minutes');

        $myTodayWorkMinutes = ProjectWorkLog::query()
            ->whereDate(
                'work_date',
                today()
            )
            ->where(
                'logged_by',
                $user->id
            )
            ->sum('duration_minutes');

        /*
        |--------------------------------------------------------------------------
        | Pinned Project Notes
        |--------------------------------------------------------------------------
        */

        $pinnedNoteCount = ProjectNote::query()
            ->visibleTo($user)
            ->where('is_pinned', true)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Phase 7 Ticket Statistics
        |--------------------------------------------------------------------------
        */

        $ticketStats = [
            'open' =>
                ProjectTicket::query()
                    ->open()
                    ->count(),

            'assigned_to_me' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'assigned_to',
                        $user->id
                    )
                    ->count(),

            'unassigned' =>
                ProjectTicket::query()
                    ->open()
                    ->whereNull('assigned_to')
                    ->count(),

            'escalated' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'escalation_level',
                        '>',
                        0
                    )
                    ->count(),

            'critical' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'priority',
                        \App\Enums\TicketPriority::Critical
                            ->value
                    )
                    ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Phase 7 SLA Risk Tickets
        |--------------------------------------------------------------------------
        */

        $slaRiskTickets = ProjectTicket::query()
            ->with([
                'project.client',
                'assignedTo',
            ])
            ->open()
            ->whereNotIn(
                'status',
                [
                    TicketStatus::PendingClient->value,
                    TicketStatus::OnHold->value,
                ]
            )
            ->where(
                function ($query): void {
                    $query
                        ->where(
                            'escalation_level',
                            '>',
                            0
                        )
                        ->orWhere(
                            'resolution_due_at',
                            '<=',
                            now()->addHours(4)
                        );
                }
            )
            ->orderByDesc('escalation_level')
            ->orderBy('resolution_due_at')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Phase 7 Recent Tickets
        |--------------------------------------------------------------------------
        */

        $recentTickets = ProjectTicket::query()
            ->with([
                'project',
                'assignedTo',
            ])
            ->latest('created_at')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view('dashboard', [
            'stats' => $stats,

            'delayedProjects' =>
                $delayedProjects,

            'recentProjects' =>
                $recentProjects,

            'outstandingProjects' =>
                $outstandingProjects,

            'overduePaymentFollowups' =>
                $overduePaymentFollowups,

            'profitabilitySummary' =>
                $profitabilitySummary,

            'currentMonthFinancials' =>
                $currentMonthFinancials,

            'monthlyFinancials' =>
                $monthlyFinancials,

            'lossMakingProjects' =>
                $lossMakingProjects,

            'cashNegativeProjects' =>
                $cashNegativeProjects,

            'recentActivities' =>
                $recentActivities,

            'inactiveProjects' =>
                $inactiveProjects,

            'todayWorkMinutes' =>
                $todayWorkMinutes,

            'myTodayWorkMinutes' =>
                $myTodayWorkMinutes,

            'pinnedNoteCount' =>
                $pinnedNoteCount,

            'projectInactivityDays' =>
                $projectInactivityDays,

            'ticketStats' =>
                $ticketStats,

            'slaRiskTickets' =>
                $slaRiskTickets,

            'recentTickets' =>
                $recentTickets,
        ]);
    }
}

