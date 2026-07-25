<?php

namespace App\Services\Reports;

use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Support\Reports\ReportFilters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeamPerformanceReportService
{
    public function rows(
        ReportFilters $filters
    ): Collection {
        $tasks = DB::table(
            'project_tasks'
        )
            ->select('assigned_to')
            ->selectRaw(
                'COUNT(*) as total_tasks'
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_tasks',
                [
                    TaskStatus::Completed->value,
                ]
            )
            ->selectRaw(
                'SUM(CASE WHEN due_date < ? AND status NOT IN (?, ?) THEN 1 ELSE 0 END) as overdue_tasks',
                [
                    today()->toDateString(),
                    TaskStatus::Completed->value,
                    TaskStatus::Cancelled->value,
                ]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? AND (due_date IS NULL OR completed_at <= due_date) THEN 1 ELSE 0 END) as on_time_completed_tasks',
                [
                    TaskStatus::Completed->value,
                ]
            )
            ->selectRaw(
                'AVG(CASE WHEN completed_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, created_at, completed_at) END) as average_completion_hours'
            )
            ->selectRaw(
                'AVG(progress) as average_task_progress'
            )
            ->whereNull('deleted_at')
            ->whereBetween(
                'created_at',
                [
                    $filters->from,
                    $filters->to,
                ]
            )
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to');

        $workLogs = DB::table(
            'project_work_logs'
        )
            ->select('logged_by')
            ->selectRaw(
                'SUM(duration_minutes) as work_minutes'
            )
            ->selectRaw(
                'SUM(CASE WHEN is_billable = 1 THEN duration_minutes ELSE 0 END) as billable_minutes'
            )
            ->selectRaw(
                'COUNT(*) as work_log_count'
            )
            ->whereNull('deleted_at')
            ->whereBetween(
                'work_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->groupBy('logged_by');

        $tickets = DB::table(
            'project_tickets'
        )
            ->select('assigned_to')
            ->selectRaw(
                'COUNT(*) as assigned_tickets'
            )
            ->selectRaw(
                'SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as resolved_tickets',
                [
                    TicketStatus::Resolved->value,
                    TicketStatus::Closed->value,
                ]
            )
            ->selectRaw(
                'SUM(CASE WHEN escalation_level > 0 THEN 1 ELSE 0 END) as escalated_tickets'
            )
            ->selectRaw(
                'SUM(CASE WHEN first_responded_at IS NOT NULL AND first_responded_at <= first_response_due_at THEN 1 ELSE 0 END) as compliant_first_responses'
            )
            ->selectRaw(
                'SUM(CASE WHEN first_responded_at IS NOT NULL THEN 1 ELSE 0 END) as responded_tickets'
            )
            ->whereNull('deleted_at')
            ->whereBetween(
                'created_at',
                [
                    $filters->from,
                    $filters->to,
                ]
            )
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to');

        $query = DB::table('users')
            ->leftJoinSub(
                $tasks,
                'task_metrics',
                'task_metrics.assigned_to',
                '=',
                'users.id'
            )
            ->leftJoinSub(
                $workLogs,
                'work_metrics',
                'work_metrics.logged_by',
                '=',
                'users.id'
            )
            ->leftJoinSub(
                $tickets,
                'ticket_metrics',
                'ticket_metrics.assigned_to',
                '=',
                'users.id'
            )
            ->where(
                'users.status',
                'active'
            )
            ->when(
                $filters->userId,
                fn ($query) =>
                    $query->where(
                        'users.id',
                        $filters->userId
                    )
            )
            ->select([
                'users.id',
                'users.name',
                'users.email',
            ])
            ->selectRaw(
                'COALESCE(task_metrics.total_tasks, 0) as total_tasks'
            )
            ->selectRaw(
                'COALESCE(task_metrics.completed_tasks, 0) as completed_tasks'
            )
            ->selectRaw(
                'COALESCE(task_metrics.overdue_tasks, 0) as overdue_tasks'
            )
            ->selectRaw(
                'COALESCE(task_metrics.on_time_completed_tasks, 0) as on_time_completed_tasks'
            )
            ->selectRaw(
                'COALESCE(task_metrics.average_completion_hours, 0) as average_completion_hours'
            )
            ->selectRaw(
                'COALESCE(task_metrics.average_task_progress, 0) as average_task_progress'
            )
            ->selectRaw(
                'COALESCE(work_metrics.work_minutes, 0) as work_minutes'
            )
            ->selectRaw(
                'COALESCE(work_metrics.billable_minutes, 0) as billable_minutes'
            )
            ->selectRaw(
                'COALESCE(work_metrics.work_log_count, 0) as work_log_count'
            )
            ->selectRaw(
                'COALESCE(ticket_metrics.assigned_tickets, 0) as assigned_tickets'
            )
            ->selectRaw(
                'COALESCE(ticket_metrics.resolved_tickets, 0) as resolved_tickets'
            )
            ->selectRaw(
                'COALESCE(ticket_metrics.escalated_tickets, 0) as escalated_tickets'
            )
            ->selectRaw(
                'COALESCE(ticket_metrics.compliant_first_responses, 0) as compliant_first_responses'
            )
            ->selectRaw(
                'COALESCE(ticket_metrics.responded_tickets, 0) as responded_tickets'
            )
            ->orderBy('users.name');

        return $query
            ->get()
            ->map(function ($row): array {
                $totalTasks =
                    (int) $row->total_tasks;

                $completedTasks =
                    (int) $row
                        ->completed_tasks;

                $onTimeCompleted =
                    (int) $row
                        ->on_time_completed_tasks;

                $assignedTickets =
                    (int) $row
                        ->assigned_tickets;

                $resolvedTickets =
                    (int) $row
                        ->resolved_tickets;

                $respondedTickets =
                    (int) $row
                        ->responded_tickets;

                $completionRate =
                    $totalTasks > 0
                        ? $completedTasks
                            / $totalTasks
                            * 100
                        : 0;

                $onTimeRate =
                    $completedTasks > 0
                        ? $onTimeCompleted
                            / $completedTasks
                            * 100
                        : 0;

                $ticketResolutionRate =
                    $assignedTickets > 0
                        ? $resolvedTickets
                            / $assignedTickets
                            * 100
                        : 0;

                $slaCompliance =
                    $respondedTickets > 0
                        ? (int) $row
                            ->compliant_first_responses
                            / $respondedTickets
                            * 100
                        : 100;

                $deliveryIndex = round(
                    (
                        $completionRate * 0.50
                    )
                    + (
                        $onTimeRate * 0.25
                    )
                    + (
                        $ticketResolutionRate
                        * 0.15
                    )
                    + (
                        $slaCompliance * 0.10
                    ),
                    2
                );

                return [
                    'user_id' =>
                        (int) $row->id,

                    'name' => $row->name,
                    'email' => $row->email,

                    'total_tasks' =>
                        $totalTasks,

                    'completed_tasks' =>
                        $completedTasks,

                    'overdue_tasks' =>
                        (int)
                        $row->overdue_tasks,

                    'completion_rate' =>
                        round(
                            $completionRate,
                            2
                        ),

                    'on_time_rate' =>
                        round(
                            $onTimeRate,
                            2
                        ),

                    'average_completion_hours' =>
                        round(
                            (float)
                            $row
                                ->average_completion_hours,
                            1
                        ),

                    'average_task_progress' =>
                        round(
                            (float)
                            $row
                                ->average_task_progress,
                            2
                        ),

                    'work_minutes' =>
                        (int)
                        $row->work_minutes,

                    'billable_minutes' =>
                        (int)
                        $row
                            ->billable_minutes,

                    'work_log_count' =>
                        (int)
                        $row
                            ->work_log_count,

                    'assigned_tickets' =>
                        $assignedTickets,

                    'resolved_tickets' =>
                        $resolvedTickets,

                    'escalated_tickets' =>
                        (int)
                        $row
                            ->escalated_tickets,

                    'sla_compliance' =>
                        round(
                            $slaCompliance,
                            2
                        ),

                    'delivery_index' =>
                        $deliveryIndex,
                ];
            })
            ->sortByDesc(
                'delivery_index'
            )
            ->values();
    }

    public function summary(
        ReportFilters $filters
    ): array {
        $rows = $this->rows($filters);

        return [
            'active_users' =>
                $rows->count(),

            'total_tasks' =>
                $rows->sum(
                    'total_tasks'
                ),

            'completed_tasks' =>
                $rows->sum(
                    'completed_tasks'
                ),

            'overdue_tasks' =>
                $rows->sum(
                    'overdue_tasks'
                ),

            'work_minutes' =>
                $rows->sum(
                    'work_minutes'
                ),

            'billable_minutes' =>
                $rows->sum(
                    'billable_minutes'
                ),

            'assigned_tickets' =>
                $rows->sum(
                    'assigned_tickets'
                ),

            'resolved_tickets' =>
                $rows->sum(
                    'resolved_tickets'
                ),

            'average_delivery_index' =>
                round(
                    (float)
                    $rows->avg(
                        'delivery_index'
                    ),
                    2
                ),
        ];
    }
}