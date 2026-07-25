<?php

namespace App\Services\Reports;

use App\Enums\TicketStatus;
use App\Models\ProjectTicket;
use App\Support\Reports\ReportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketSlaReportService
{
    public function summary(
        ReportFilters $filters
    ): array {
        $query = $this->baseQuery(
            $filters
        );

        $total =
            (clone $query)->count();

        $resolved =
            (clone $query)
                ->whereIn('status', [
                    TicketStatus::Resolved->value,
                    TicketStatus::Closed->value,
                ])
                ->count();

        $open =
            (clone $query)
                ->whereIn(
                    'status',
                    TicketStatus::activeValues()
                )
                ->count();

        $responseEligible =
            (clone $query)
                ->where(function (
                    Builder $query
                ): void {
                    $query
                        ->whereNotNull(
                            'first_responded_at'
                        )
                        ->orWhere(
                            'first_response_due_at',
                            '<',
                            now()
                        );
                })
                ->count();

        $responseCompliant =
            (clone $query)
                ->whereNotNull(
                    'first_responded_at'
                )
                ->whereColumn(
                    'first_responded_at',
                    '<=',
                    'first_response_due_at'
                )
                ->count();

        $resolutionEligible =
            (clone $query)
                ->where(function (
                    Builder $query
                ): void {
                    $query
                        ->whereNotNull(
                            'resolved_at'
                        )
                        ->orWhere(
                            'resolution_due_at',
                            '<',
                            now()
                        );
                })
                ->count();

        $resolutionCompliant =
            (clone $query)
                ->whereNotNull(
                    'resolved_at'
                )
                ->whereColumn(
                    'resolved_at',
                    '<=',
                    'resolution_due_at'
                )
                ->count();

        $averageResponseMinutes =
            (float) (
                (clone $query)
                    ->whereNotNull(
                        'first_responded_at'
                    )
                    ->selectRaw(
                        'AVG(TIMESTAMPDIFF(MINUTE, created_at, first_responded_at)) as average_minutes'
                    )
                    ->value(
                        'average_minutes'
                    )
                ?? 0
            );

        $averageResolutionMinutes =
            (float) (
                (clone $query)
                    ->whereNotNull(
                        'resolved_at'
                    )
                    ->selectRaw(
                        'AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as average_minutes'
                    )
                    ->value(
                        'average_minutes'
                    )
                ?? 0
            );

        return [
            'total_tickets' => $total,
            'open_tickets' => $open,
            'resolved_tickets' => $resolved,

            'resolution_rate' =>
                $total > 0
                    ? round(
                        $resolved
                        / $total
                        * 100,
                        2
                    )
                    : 0,

            'response_sla_compliance' =>
                $responseEligible > 0
                    ? round(
                        $responseCompliant
                        / $responseEligible
                        * 100,
                        2
                    )
                    : 100,

            'resolution_sla_compliance' =>
                $resolutionEligible > 0
                    ? round(
                        $resolutionCompliant
                        / $resolutionEligible
                        * 100,
                        2
                    )
                    : 100,

            'response_breaches' =>
                (clone $query)
                    ->whereNotNull(
                        'response_breached_at'
                    )
                    ->count(),

            'resolution_breaches' =>
                (clone $query)
                    ->whereNotNull(
                        'resolution_breached_at'
                    )
                    ->count(),

            'escalated_tickets' =>
                (clone $query)
                    ->where(
                        'escalation_level',
                        '>',
                        0
                    )
                    ->count(),

            'reopened_tickets' =>
                (clone $query)
                    ->where(
                        'reopen_count',
                        '>',
                        0
                    )
                    ->count(),

            'average_response_minutes' =>
                round(
                    $averageResponseMinutes,
                    1
                ),

            'average_resolution_minutes' =>
                round(
                    $averageResolutionMinutes,
                    1
                ),
        ];
    }

    public function priorityBreakdown(
        ReportFilters $filters
    ): Collection {
        return $this->baseQuery($filters)
            ->select('priority')
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->selectRaw(
                'SUM(CASE WHEN response_breached_at IS NOT NULL THEN 1 ELSE 0 END) as response_breaches'
            )
            ->selectRaw(
                'SUM(CASE WHEN resolution_breached_at IS NOT NULL THEN 1 ELSE 0 END) as resolution_breaches'
            )
            ->selectRaw(
                'SUM(CASE WHEN escalation_level > 0 THEN 1 ELSE 0 END) as escalated'
            )
            ->groupBy('priority')
            ->orderByRaw(
                "
                CASE priority
                    WHEN 'critical' THEN 5
                    WHEN 'urgent' THEN 4
                    WHEN 'high' THEN 3
                    WHEN 'medium' THEN 2
                    ELSE 1
                END DESC
                "
            )
            ->get();
    }

    public function assigneePerformance(
        ReportFilters $filters
    ): Collection {
        return DB::table(
            'project_tickets'
        )
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'project_tickets.assigned_to'
            )
            ->selectRaw(
                "COALESCE(users.name, 'Unassigned') as assignee"
            )
            ->selectRaw(
                'COUNT(project_tickets.id) as total_tickets'
            )
            ->selectRaw(
                'SUM(CASE WHEN project_tickets.status IN (?, ?) THEN 1 ELSE 0 END) as resolved_tickets',
                [
                    TicketStatus::Resolved->value,
                    TicketStatus::Closed->value,
                ]
            )
            ->selectRaw(
                'SUM(CASE WHEN project_tickets.response_breached_at IS NULL AND project_tickets.first_responded_at IS NOT NULL THEN 1 ELSE 0 END) as compliant_responses'
            )
            ->selectRaw(
                'SUM(CASE WHEN project_tickets.first_responded_at IS NOT NULL THEN 1 ELSE 0 END) as responded_tickets'
            )
            ->selectRaw(
                'AVG(CASE WHEN project_tickets.first_responded_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, project_tickets.created_at, project_tickets.first_responded_at) END) as average_response_minutes'
            )
            ->whereNull(
                'project_tickets.deleted_at'
            )
            ->whereBetween(
                'project_tickets.created_at',
                [
                    $filters->from,
                    $filters->to,
                ]
            )
            ->when(
                $filters->userId,
                fn ($query) =>
                    $query->where(
                        'project_tickets.assigned_to',
                        $filters->userId
                    )
            )
            ->groupBy(
                'users.id',
                'users.name'
            )
            ->orderByDesc(
                'resolved_tickets'
            )
            ->get()
            ->map(function ($row): array {
                $responded =
                    (int)
                    $row->responded_tickets;

                return [
                    'assignee' =>
                        $row->assignee,

                    'total_tickets' =>
                        (int)
                        $row->total_tickets,

                    'resolved_tickets' =>
                        (int)
                        $row
                            ->resolved_tickets,

                    'response_compliance' =>
                        $responded > 0
                            ? round(
                                (int)
                                $row
                                    ->compliant_responses
                                / $responded
                                * 100,
                                2
                            )
                            : 100,

                    'average_response_minutes' =>
                        round(
                            (float) (
                                $row
                                    ->average_response_minutes
                                ?? 0
                            ),
                            1
                        ),
                ];
            });
    }

    public function breachTrend(
        ReportFilters $filters
    ): Collection {
        return DB::table(
            'project_tickets'
        )
            ->selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') as period"
            )
            ->selectRaw(
                'COUNT(*) as tickets'
            )
            ->selectRaw(
                'SUM(CASE WHEN response_breached_at IS NOT NULL THEN 1 ELSE 0 END) as response_breaches'
            )
            ->selectRaw(
                'SUM(CASE WHEN resolution_breached_at IS NOT NULL THEN 1 ELSE 0 END) as resolution_breaches'
            )
            ->whereNull('deleted_at')
            ->whereBetween(
                'created_at',
                [
                    $filters->from,
                    $filters->to,
                ]
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function paginatedTickets(
        ReportFilters $filters
    ) {
        return $this->baseQuery($filters)
            ->with([
                'project.client',
                'assignedTo',
                'resolvedBy',
            ])
            ->orderByDesc(
                'escalation_level'
            )
            ->latest('created_at')
            ->paginate(
                $filters->perPage
            )
            ->withQueryString();
    }

    public function exportQuery(
        ReportFilters $filters
    ): Builder {
        return $this->baseQuery($filters)
            ->with([
                'project.client',
                'assignedTo',
                'resolvedBy',
            ])
            ->orderBy('id');
    }

    private function baseQuery(
        ReportFilters $filters
    ): Builder {
        return ProjectTicket::query()
            ->whereBetween(
                'created_at',
                [
                    $filters->from,
                    $filters->to,
                ]
            )
            ->when(
                $filters->projectId,
                fn (Builder $query) =>
                    $query->where(
                        'project_id',
                        $filters->projectId
                    )
            )
            ->when(
                $filters->clientId,
                fn (Builder $query) =>
                    $query->where(
                        'client_id',
                        $filters->clientId
                    )
            )
            ->when(
                $filters->userId,
                fn (Builder $query) =>
                    $query->where(
                        'assigned_to',
                        $filters->userId
                    )
            )
            ->when(
                $filters->ticketStatus,
                fn (Builder $query) =>
                    $query->where(
                        'status',
                        $filters
                            ->ticketStatus
                    )
            )
            ->when(
                $filters->ticketPriority,
                fn (Builder $query) =>
                    $query->where(
                        'priority',
                        $filters
                            ->ticketPriority
                    )
            );
    }
}