<?php

namespace App\Services\Reports;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Support\Reports\ReportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectAnalyticsService
{
    public function summary(
        ReportFilters $filters
    ): array {
        $query = $this->baseQuery(
            $filters
        );

        $totalProjects =
            (clone $query)->count();

        $completedProjects =
            (clone $query)
                ->where(
                    'status',
                    ProjectStatus::Completed->value
                )
                ->count();

        $activeProjects =
            (clone $query)
                ->whereNotIn('status', [
                    ProjectStatus::Completed->value,
                    ProjectStatus::Cancelled->value,
                ])
                ->count();

        $delayedProjects =
            (clone $query)
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

        $onTimeCompleted =
            (clone $query)
                ->where(
                    'status',
                    ProjectStatus::Completed->value
                )
                ->whereNotNull(
                    'actual_completion_date'
                )
                ->whereColumn(
                    'actual_completion_date',
                    '<=',
                    'expected_delivery_date'
                )
                ->count();

        $contractValue =
            (float) (clone $query)
                ->sum('project_price');

        $collected =
            (float) (clone $query)
                ->sum(
                    'net_received_amount'
                );

        $outstanding =
            (float) (clone $query)
                ->sum('pending_amount');

        $projectCosts =
            (float) (clone $query)
                ->sum(
                    'project_expense_amount'
                );

        $contractProfit =
            $contractValue - $projectCosts;

        $averageDuration =
            (float) (
                (clone $query)
                    ->whereNotNull(
                        'actual_completion_date'
                    )
                    ->get([
                        'start_date',
                        'actual_completion_date',
                    ])
                    ->avg(
                        static function (
                            Project $project
                        ): float {
                            if (
                                !$project->start_date
                                || !$project
                                    ->actual_completion_date
                            ) {
                                return 0.0;
                            }

                            return (float) $project
                                ->start_date
                                ->diffInDays(
                                    $project
                                        ->actual_completion_date,
                                    false
                                );
                        }
                    )
                ?? 0
            );

        return [
            'total_projects' =>
                $totalProjects,

            'active_projects' =>
                $activeProjects,

            'completed_projects' =>
                $completedProjects,

            'delayed_projects' =>
                $delayedProjects,

            'completion_rate' =>
                $totalProjects > 0
                    ? round(
                        $completedProjects
                        / $totalProjects
                        * 100,
                        2
                    )
                    : 0,

            'on_time_completion_rate' =>
                $completedProjects > 0
                    ? round(
                        $onTimeCompleted
                        / $completedProjects
                        * 100,
                        2
                    )
                    : 0,

            'average_completion_days' =>
                round(
                    $averageDuration,
                    1
                ),

            'contract_value' =>
                $contractValue,

            'collected' =>
                $collected,

            'outstanding' =>
                $outstanding,

            'collection_percentage' =>
                $contractValue > 0
                    ? round(
                        $collected
                        / $contractValue
                        * 100,
                        2
                    )
                    : 0,

            'project_costs' =>
                $projectCosts,

            'contract_profit' =>
                $contractProfit,

            'contract_margin' =>
                $contractValue > 0
                    ? round(
                        $contractProfit
                        / $contractValue
                        * 100,
                        2
                    )
                    : 0,
        ];
    }

    public function monthlyTrend(
        ReportFilters $filters
    ): Collection {
        $created = DB::table('projects')
            ->selectRaw(
                "DATE_FORMAT(start_date, '%Y-%m') as period"
            )
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->whereBetween(
                'start_date',
                [
                    $filters->from->toDateString(),
                    $filters->to->toDateString(),
                ]
            )
            ->groupBy('period')
            ->pluck('total', 'period');

        $completed = DB::table('projects')
            ->selectRaw(
                "DATE_FORMAT(actual_completion_date, '%Y-%m') as period"
            )
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->whereNotNull(
                'actual_completion_date'
            )
            ->whereBetween(
                'actual_completion_date',
                [
                    $filters->from->toDateString(),
                    $filters->to->toDateString(),
                ]
            )
            ->groupBy('period')
            ->pluck('total', 'period');

        $months = collect();

        $cursor = $filters->from
            ->startOfMonth();

        while (
            $cursor->lessThanOrEqualTo(
                $filters->to
            )
        ) {
            $key = $cursor->format('Y-m');

            $months->push([
                'period' => $key,

                'label' =>
                    $cursor->format('M Y'),

                'started' =>
                    (int) (
                        $created[$key]
                        ?? 0
                    ),

                'completed' =>
                    (int) (
                        $completed[$key]
                        ?? 0
                    ),
            ]);

            $cursor = $cursor->addMonth();
        }

        return $months;
    }

    public function statusDistribution(
        ReportFilters $filters
    ): Collection {
        return $this->baseQuery($filters)
            ->select('status')
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'status' =>
                    $row->status instanceof ProjectStatus
                        ? $row->status->label()
                        : ProjectStatus::tryFrom(
                            (string) $row->status
                        )?->label()
                            ?? str(
                                $row->status
                            )->headline(),

                'total' =>
                    (int) $row->total,
            ]);
    }

    public function priorityDistribution(
        ReportFilters $filters
    ): Collection {
        return $this->baseQuery($filters)
            ->select('priority')
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->groupBy('priority')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'priority' =>
                    $row->priority instanceof ProjectPriority
                        ? $row->priority->label()
                        : ProjectPriority::tryFrom(
                            (string) $row->priority
                        )?->label()
                            ?? str(
                                $row->priority
                            )->headline(),

                'total' =>
                    (int) $row->total,
            ]);
    }

    public function riskProjects(
        ReportFilters $filters
    ): Collection {
        return $this->baseQuery($filters)
            ->with([
                'client',
                'manager',
            ])
            ->whereNotIn('status', [
                ProjectStatus::Completed->value,
                ProjectStatus::Cancelled->value,
            ])
            ->get()
            ->map(function (
                Project $project
            ): array {
                $score = 0;
                $reasons = [];

                if (
                    $project
                        ->expected_delivery_date
                    && $project
                        ->expected_delivery_date
                        ->isPast()
                ) {
                    $score += 40;
                    $reasons[] =
                        'Delivery deadline overdue';
                }

                if (
                    in_array(
                        $project->priority,
                        [
                            ProjectPriority::High,
                            ProjectPriority::Urgent,
                        ],
                        true
                    )
                ) {
                    $score += 15;
                    $reasons[] =
                        'High project priority';
                }

                if (
                    (float)
                    $project
                        ->collection_percentage
                    < 50
                    && (float)
                    $project->pending_amount
                    > 0
                ) {
                    $score += 15;
                    $reasons[] =
                        'Low collection percentage';
                }

                if (
                    $project
                        ->expected_delivery_date
                    && $project
                        ->expected_delivery_date
                        ->between(
                            today(),
                            today()->addDays(7)
                        )
                    && (float)
                    $project
                        ->internal_progress
                    < 70
                ) {
                    $score += 20;
                    $reasons[] =
                        'Low progress near deadline';
                }

                if (
                    $project->last_activity_at
                    && $project
                        ->last_activity_at
                        ->lt(
                            now()->subDays(3)
                        )
                ) {
                    $score += 10;
                    $reasons[] =
                        'No recent activity';
                }

                return [
                    'project' => $project,
                    'risk_score' => min(
                        100,
                        $score
                    ),
                    'reasons' => $reasons,
                ];
            })
            ->filter(
                fn (array $item) =>
                    $item['risk_score'] > 0
            )
            ->sortByDesc('risk_score')
            ->take(10)
            ->values();
    }

    public function paginatedProjects(
        ReportFilters $filters,
        ?string $requestedSort,
        ?string $requestedDirection
    ) {
        $allowedSorts = [
            'name',
            'start_date',
            'expected_delivery_date',
            'project_price',
            'official_progress',
            'internal_progress',
            'net_received_amount',
            'pending_amount',
            'project_expense_amount',
        ];

        $sort = in_array(
            $requestedSort,
            $allowedSorts,
            true
        )
            ? $requestedSort
            : 'start_date';

        $direction =
            $requestedDirection === 'asc'
                ? 'asc'
                : 'desc';

        return $this->baseQuery($filters)
            ->with([
                'client',
                'manager',
            ])
            ->orderBy(
                $sort,
                $direction
            )
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
                'client',
                'manager',
            ])
            ->orderBy('id');
    }

    private function baseQuery(
        ReportFilters $filters
    ): Builder {
        return Project::query()
            ->whereBetween(
                'start_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->when(
                $filters->projectId,
                fn (Builder $query) =>
                    $query->whereKey(
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
                $filters->projectStatus,
                fn (Builder $query) =>
                    $query->where(
                        'status',
                        $filters
                            ->projectStatus
                    )
            )
            ->when(
                $filters->projectPriority,
                fn (Builder $query) =>
                    $query->where(
                        'priority',
                        $filters
                            ->projectPriority
                    )
            );
    }
}

