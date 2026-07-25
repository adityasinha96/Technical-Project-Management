<?php

namespace App\Http\Controllers\Reports;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\Reports\CollectionReportService;
use App\Services\Reports\ManagementInsightsService;
use App\Services\Reports\ProfitabilityReportService;
use App\Services\Reports\ProjectAnalyticsService;
use App\Services\Reports\ReportCacheService;
use App\Services\Reports\TeamPerformanceReportService;
use App\Services\Reports\TicketSlaReportService;
use App\Support\Reports\ReportFilters;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportCacheService $cache,
        private readonly ProjectAnalyticsService $projects,
        private readonly TeamPerformanceReportService $team,
        private readonly CollectionReportService $collections,
        private readonly ProfitabilityReportService $profitability,
        private readonly TicketSlaReportService $ticketSla,
        private readonly ManagementInsightsService $insights
    ) {
    }

    public function index(
        ReportFilterRequest $request
    ): View {
        $filters = ReportFilters::fromArray(
            $request->validated()
        );

        $data = $this->cache->remember(
            'executive',
            $filters,
            function () use ($filters): array {
                $projectSummary =
                    $this->projects
                        ->summary($filters);

                $profitabilitySummary =
                    $this->profitability
                        ->summary($filters);

                return [
                    'projectSummary' =>
                        $projectSummary,

                    'projectTrend' =>
                        $this->projects
                            ->monthlyTrend(
                                $filters
                            ),

                    'riskProjects' =>
                        $this->projects
                            ->riskProjects(
                                $filters
                            ),

                    'teamSummary' =>
                        $this->team
                            ->summary($filters),

                    'collectionSummary' =>
                        $this->collections
                            ->summary($filters),

                    'profitabilitySummary' =>
                        $profitabilitySummary,

                    'ticketSummary' =>
                        $this->ticketSla
                            ->summary($filters),

                    'insights' =>
                        $this->insights
                            ->build(
                                $filters,
                                $profitabilitySummary
                            ),
                ];
            }
        );

        return view(
            'reports.index',
            [
                ...$this->commonData(
                    $filters
                ),
                ...$data,

                'recentExports' =>
                    ReportExport::query()
                        ->with(
                            'generatedBy'
                        )
                        ->latest()
                        ->limit(8)
                        ->get(),
            ]
        );
    }

    public function projects(
        ReportFilterRequest $request
    ): View {
        $filters = ReportFilters::fromArray(
            $request->validated()
        );

        $summary = $this->cache->remember(
            'projects-summary',
            $filters,
            fn () => [
                'summary' =>
                    $this->projects
                        ->summary($filters),

                'trend' =>
                    $this->projects
                        ->monthlyTrend(
                            $filters
                        ),

                'statusDistribution' =>
                    $this->projects
                        ->statusDistribution(
                            $filters
                        ),

                'priorityDistribution' =>
                    $this->projects
                        ->priorityDistribution(
                            $filters
                        ),

                'riskProjects' =>
                    $this->projects
                        ->riskProjects(
                            $filters
                        ),
            ]
        );

        return view(
            'reports.projects',
            [
                ...$this->commonData(
                    $filters
                ),
                ...$summary,

                'projectRows' =>
                    $this->projects
                        ->paginatedProjects(
                            $filters,
                            $request->string(
                                'sort'
                            )->toString(),
                            $request->string(
                                'direction'
                            )->toString()
                        ),
            ]
        );
    }

    public function team(
        ReportFilterRequest $request
    ): View {
        Gate::authorize(
            'reports.view-team'
        );

        $filters = ReportFilters::fromArray(
            $request->validated()
        );

        $data = $this->cache->remember(
            'team-performance',
            $filters,
            fn () => [
                'summary' =>
                    $this->team
                        ->summary($filters),

                'teamRows' =>
                    $this->team
                        ->rows($filters),
            ]
        );

        return view(
            'reports.team',
            [
                ...$this->commonData(
                    $filters
                ),
                ...$data,
            ]
        );
    }

    public function collections(
        ReportFilterRequest $request
    ): View {
        Gate::authorize(
            'reports.view-financial'
        );

        $filters = ReportFilters::fromArray(
            $request->validated()
        );

        $data = $this->cache->remember(
            'collections-summary',
            $filters,
            fn () => [
                'summary' =>
                    $this->collections
                        ->summary($filters),

                'trend' =>
                    $this->collections
                        ->monthlyTrend(
                            $filters
                        ),

                'modeBreakdown' =>
                    $this->collections
                        ->paymentModeBreakdown(
                            $filters
                        ),

                'ageing' =>
                    $this->collections
                        ->ageing($filters),

                'topOutstanding' =>
                    $this->collections
                        ->topOutstanding(
                            $filters
                        ),
            ]
        );

        return view(
            'reports.collections',
            [
                ...$this->commonData(
                    $filters
                ),
                ...$data,

                'outstandingRows' =>
                    $this->collections
                        ->paginatedOutstanding(
                            $filters
                        ),
            ]
        );
    }

    public function profitability(
        ReportFilterRequest $request
    ): View {
        Gate::authorize(
            'reports.view-financial'
        );

        $filters = ReportFilters::fromArray(
            $request->validated()
        );

        $data = $this->cache->remember(
            'profitability-summary',
            $filters,
            fn () => [
                'summary' =>
                    $this->profitability
                        ->summary($filters),

                'cashTrend' =>
                    $this->profitability
                        ->monthlyCashMovement(
                            $filters
                        ),

                'categoryBreakdown' =>
                    $this->profitability
                        ->expenseCategoryBreakdown(
                            $filters
                        ),
            ]
        );

        return view(
            'reports.profitability',
            [
                ...$this->commonData(
                    $filters
                ),
                ...$data,

                'projectRows' =>
                    $this->profitability
                        ->paginatedProjects(
                            $filters
                        ),
            ]
        );
    }

    public function ticketSla(
        ReportFilterRequest $request
    ): View {
        Gate::authorize(
            'reports.view-ticket-sla'
        );

        $filters = ReportFilters::fromArray(
            $request->validated()
        );

        $data = $this->cache->remember(
            'ticket-sla-summary',
            $filters,
            fn () => [
                'summary' =>
                    $this->ticketSla
                        ->summary($filters),

                'priorityBreakdown' =>
                    $this->ticketSla
                        ->priorityBreakdown(
                            $filters
                        ),

                'assigneePerformance' =>
                    $this->ticketSla
                        ->assigneePerformance(
                            $filters
                        ),

                'breachTrend' =>
                    $this->ticketSla
                        ->breachTrend(
                            $filters
                        ),
            ]
        );

        return view(
            'reports.ticket-sla',
            [
                ...$this->commonData(
                    $filters
                ),
                ...$data,

                'ticketRows' =>
                    $this->ticketSla
                        ->paginatedTickets(
                            $filters
                        ),
            ]
        );
    }

    private function commonData(
        ReportFilters $filters
    ): array {
        return [
            'filters' => $filters,

            'projects' =>
                Project::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

            'clients' =>
                Client::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

            'users' =>
                User::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

            'projectStatuses' =>
                ProjectStatus::cases(),

            'projectPriorities' =>
                ProjectPriority::cases(),

            'ticketStatuses' =>
                TicketStatus::cases(),

            'ticketPriorities' =>
                TicketPriority::cases(),
        ];
    }
}