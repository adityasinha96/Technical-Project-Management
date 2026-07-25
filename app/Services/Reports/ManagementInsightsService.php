<?php

namespace App\Services\Reports;

use App\Enums\NotificationSeverity;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTicket;
use App\Support\Reports\ReportFilters;
use Illuminate\Support\Collection;

class ManagementInsightsService
{
    public function build(
        ReportFilters $filters,
        array $profitabilitySummary
    ): Collection {
        $insights = collect();

        $overdueProjects =
            Project::query()
                ->whereNotIn('status', [
                    ProjectStatus::Completed->value,
                    ProjectStatus::Cancelled->value,
                ])
                ->whereDate(
                    'expected_delivery_date',
                    '<',
                    today()
                )
                ->count();

        $overdueOutstanding =
            (float) Project::query()
                ->where(
                    'pending_amount',
                    '>',
                    0
                )
                ->whereRaw(
                    'COALESCE(collection_due_date, expected_delivery_date) < ?',
                    [
                        today()
                            ->toDateString(),
                    ]
                )
                ->sum('pending_amount');

        $lossMakingProjects =
            Project::query()
                ->whereColumn(
                    'project_expense_amount',
                    '>',
                    'project_price'
                )
                ->count();

        $criticalTickets =
            ProjectTicket::query()
                ->whereIn(
                    'status',
                    TicketStatus::activeValues()
                )
                ->where(function ($query): void {
                    $query
                        ->where(
                            'priority',
                            TicketPriority::Critical->value
                        )
                        ->orWhere(
                            'escalation_level',
                            '>=',
                            3
                        );
                })
                ->count();

        $unassignedTickets =
            ProjectTicket::query()
                ->whereIn(
                    'status',
                    TicketStatus::activeValues()
                )
                ->whereNull('assigned_to')
                ->count();

        $overdueTasks =
            ProjectTask::query()
                ->whereNotIn('status', [
                    TaskStatus::Completed->value,
                    TaskStatus::Cancelled->value,
                ])
                ->whereDate(
                    'due_date',
                    '<',
                    today()
                )
                ->count();

        $inactiveProjects =
            Project::query()
                ->whereNotIn('status', [
                    ProjectStatus::Completed->value,
                    ProjectStatus::Cancelled->value,
                ])
                ->where(function ($query): void {
                    $query
                        ->where(
                            'last_activity_at',
                            '<',
                            now()->subDays(3)
                        )
                        ->orWhereNull(
                            'last_activity_at'
                        );
                })
                ->count();

        if ($overdueProjects > 0) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Danger,

                'title' =>
                    'Projects are beyond delivery date',

                'message' =>
                    "{$overdueProjects} active project(s) have passed their expected delivery date.",

                'metric' =>
                    $overdueProjects,

                'url' =>
                    route(
                        'reports.projects',
                        [
                            ...$filters->toArray(),

                            'project_status' =>
                                null,
                        ]
                    ),
            ]);
        }

        if ($overdueOutstanding > 0) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Critical,

                'title' =>
                    'Overdue client collections require action',

                'message' =>
                    '₹'
                    . number_format(
                        $overdueOutstanding,
                        2
                    )
                    . ' is overdue based on collection due dates.',

                'metric' =>
                    $overdueOutstanding,

                'url' =>
                    route(
                        'reports.collections',
                        $filters->toArray()
                    ),
            ]);
        }

        if ($lossMakingProjects > 0) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Critical,

                'title' =>
                    'Projects with negative contract margin',

                'message' =>
                    "{$lossMakingProjects} project(s) currently have recorded project expenses above contract value.",

                'metric' =>
                    $lossMakingProjects,

                'url' =>
                    route(
                        'reports.profitability',
                        $filters->toArray()
                    ),
            ]);
        }

        if (
            $profitabilitySummary[
                'cash_contribution'
            ] < 0
        ) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Danger,

                'title' =>
                    'Negative cash contribution during period',

                'message' =>
                    'Paid expenses exceeded cleared client collections by ₹'
                    . number_format(
                        abs(
                            $profitabilitySummary[
                                'cash_contribution'
                            ]
                        ),
                        2
                    )
                    . '.',

                'metric' =>
                    $profitabilitySummary[
                        'cash_contribution'
                    ],

                'url' =>
                    route(
                        'reports.profitability',
                        $filters->toArray()
                    ),
            ]);
        }

        if ($criticalTickets > 0) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Critical,

                'title' =>
                    'Critical ticket workload',

                'message' =>
                    "{$criticalTickets} active ticket(s) are critical or at Level 3 escalation.",

                'metric' =>
                    $criticalTickets,

                'url' =>
                    route(
                        'reports.ticket-sla',
                        $filters->toArray()
                    ),
            ]);
        }

        if ($unassignedTickets > 0) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Warning,

                'title' =>
                    'Open tickets remain unassigned',

                'message' =>
                    "{$unassignedTickets} open ticket(s) do not currently have an owner.",

                'metric' =>
                    $unassignedTickets,

                'url' =>
                    route(
                        'tickets.index',
                        [
                            'unassigned' => 1,
                        ]
                    ),
            ]);
        }

        if ($overdueTasks > 0) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Warning,

                'title' =>
                    'Overdue task backlog',

                'message' =>
                    "{$overdueTasks} active task(s) have passed their due date.",

                'metric' =>
                    $overdueTasks,

                'url' =>
                    route(
                        'reports.team',
                        $filters->toArray()
                    ),
            ]);
        }

        if ($inactiveProjects > 0) {
            $insights->push([
                'severity' =>
                    NotificationSeverity::Warning,

                'title' =>
                    'Projects without recent activity',

                'message' =>
                    "{$inactiveProjects} active project(s) have no recorded activity during the last three days.",

                'metric' =>
                    $inactiveProjects,

                'url' =>
                    route('dashboard'),
            ]);
        }

        return $insights
            ->sortByDesc(
                fn (array $item) =>
                    match (
                        $item['severity']
                    ) {
                        NotificationSeverity::Critical => 5,
                        NotificationSeverity::Danger => 4,
                        NotificationSeverity::Warning => 3,
                        NotificationSeverity::Success => 2,
                        NotificationSeverity::Info => 1,
                    }
            )
            ->values();
    }
}