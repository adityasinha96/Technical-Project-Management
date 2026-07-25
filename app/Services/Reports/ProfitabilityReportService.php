<?php

namespace App\Services\Reports;

use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Support\Reports\ReportFilters;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfitabilityReportService
{
    public function summary(
        ?ReportFilters $filters = null
    ): array {
        if ($filters === null) {
            return $this->dashboardSummary();
        }

        $projectQuery =
            $this->projectQuery(
                $filters
            );

        $contractValue =
            (float) (clone $projectQuery)
                ->sum('project_price');

        $projectExpenses =
            (float) (clone $projectQuery)
                ->sum(
                    'project_expense_amount'
                );

        $contractProfit =
            $contractValue
            - $projectExpenses;

        $generalExpenses =
            $this->generalExpenses(
                $filters
            );

        $netCollections =
            $this->periodNetCollections(
                $filters
            );

        $periodPaidExpenses =
            $this->periodPaidExpenses(
                $filters
            );

        return [
            'contract_value' =>
                $contractValue,

            'project_expenses' =>
                $projectExpenses,

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

            'general_business_expenses' =>
                $generalExpenses,

            'contract_profit_after_general_expenses' =>
                $contractProfit
                - $generalExpenses,

            'period_net_collections' =>
                $netCollections,

            'period_paid_expenses' =>
                $periodPaidExpenses,

            'cash_contribution' =>
                $netCollections
                - $periodPaidExpenses,

            'profitable_projects' =>
                (clone $projectQuery)
                    ->whereColumn(
                        'project_price',
                        '>',
                        'project_expense_amount'
                    )
                    ->count(),

            'loss_making_projects' =>
                (clone $projectQuery)
                    ->whereColumn(
                        'project_expense_amount',
                        '>',
                        'project_price'
                    )
                    ->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard Compatibility Summaries
    |--------------------------------------------------------------------------
    |
    | Phase 5 dashboard code calls summary(), monthSummary() and monthly()
    | without report filters. These methods remain available while Phase 9
    | controllers can continue passing ReportFilters to the filtered APIs.
    |
    */

    public function monthSummary(
        ?CarbonInterface $month = null
    ): array {
        $period = $month
            ? $month->copy()
            : now();

        $from = $period
            ->copy()
            ->startOfMonth();

        $to = $period
            ->copy()
            ->endOfMonth();

        return $this->dashboardPeriodSummary(
            $from,
            $to
        );
    }

    public function monthly(
        int $months = 6
    ): Collection {
        $months = max(
            1,
            $months
        );

        $summaries = collect();

        for (
            $offset = $months - 1;
            $offset >= 0;
            $offset--
        ) {
            $month = now()
                ->copy()
                ->subMonths($offset)
                ->startOfMonth();

            $summaries->push(
                $this->monthSummary(
                    $month
                )
            );
        }

        return $summaries;
    }

    public function monthlyCashMovement(
        ReportFilters $filters
    ): Collection {
        $collections = DB::table('payments')
            ->selectRaw(
                "DATE_FORMAT(payment_date, '%Y-%m') as period"
            )
            ->selectRaw(
                'SUM(CASE WHEN kind = ? THEN amount ELSE -amount END) as net_collection',
                [
                    PaymentKind::Receipt->value,
                ]
            )
            ->where(
                'status',
                PaymentStatus::Cleared->value
            )
            ->whereNull('voided_at')
            ->whereBetween(
                'payment_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->when(
                $filters->projectId,
                fn ($query) =>
                    $query->where(
                        'project_id',
                        $filters->projectId
                    )
            )
            ->groupBy('period')
            ->pluck(
                'net_collection',
                'period'
            );

        $expenses = DB::table('expenses')
            ->selectRaw(
                "DATE_FORMAT(expense_date, '%Y-%m') as period"
            )
            ->selectRaw(
                'SUM(amount) as total_expense'
            )
            ->whereNull('voided_at')
            ->whereNotNull('paid_at')
            ->whereBetween(
                'expense_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->when(
                $filters->projectId,
                fn ($query) =>
                    $query->where(
                        'project_id',
                        $filters->projectId
                    )
            )
            ->groupBy('period')
            ->pluck(
                'total_expense',
                'period'
            );

        $trend = collect();

        $cursor = $filters->from
            ->startOfMonth();

        while (
            $cursor->lessThanOrEqualTo(
                $filters->to
            )
        ) {
            $key = $cursor->format('Y-m');

            $collection =
                (float) (
                    $collections[$key]
                    ?? 0
                );

            $expense =
                (float) (
                    $expenses[$key]
                    ?? 0
                );

            $trend->push([
                'period' => $key,

                'label' =>
                    $cursor->format('M Y'),

                'collections' =>
                    $collection,

                'expenses' =>
                    $expense,

                'cash_contribution' =>
                    $collection - $expense,
            ]);

            $cursor = $cursor->addMonth();
        }

        return $trend;
    }

    public function expenseCategoryBreakdown(
        ReportFilters $filters
    ): Collection {
        return DB::table('expenses')
            ->leftJoin(
                'expense_categories',
                'expense_categories.id',
                '=',
                'expenses.expense_category_id'
            )
            ->selectRaw(
                "COALESCE(expense_categories.name, 'Uncategorised') as category"
            )
            ->selectRaw(
                'SUM(expenses.amount) as total_amount'
            )
            ->selectRaw(
                'COUNT(expenses.id) as expense_count'
            )
            ->whereNull(
                'expenses.voided_at'
            )
            ->whereNotNull(
                'expenses.paid_at'
            )
            ->whereBetween(
                'expenses.expense_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->when(
                $filters->projectId,
                fn ($query) =>
                    $query->where(
                        'expenses.project_id',
                        $filters->projectId
                    )
            )
            ->groupBy(
                'expense_categories.name'
            )
            ->orderByDesc(
                'total_amount'
            )
            ->get();
    }

    public function paginatedProjects(
        ReportFilters $filters
    ) {
        return $this->projectQuery(
            $filters
        )
            ->with([
                'client',
                'manager',
            ])
            ->select('projects.*')
            ->selectRaw(
                '(project_price - project_expense_amount) as calculated_profit'
            )
            ->selectRaw(
                'CASE
                    WHEN project_price > 0
                    THEN ((project_price - project_expense_amount) / project_price) * 100
                    ELSE 0
                END as calculated_margin'
            )
            ->orderByDesc(
                'calculated_profit'
            )
            ->paginate(
                $filters->perPage
            )
            ->withQueryString();
    }

    public function exportQuery(
        ReportFilters $filters
    ): Builder {
        return $this->projectQuery(
            $filters
        )
            ->with([
                'client',
                'manager',
            ])
            ->select('projects.*')
            ->selectRaw(
                '(project_price - project_expense_amount) as calculated_profit'
            )
            ->selectRaw(
                'CASE
                    WHEN project_price > 0
                    THEN ((project_price - project_expense_amount) / project_price) * 100
                    ELSE 0
                END as calculated_margin'
            )
            ->orderBy('projects.id');
    }

    private function dashboardSummary(): array
    {
        $contractValue =
            (float) Project::query()
                ->sum('project_price');

        $projectExpenses =
            (float) Project::query()
                ->sum(
                    'project_expense_amount'
                );

        $contractProfit =
            $contractValue
            - $projectExpenses;

        $netCollections =
            (float) Project::query()
                ->sum(
                    'net_received_amount'
                );

        $paidExpenses =
            (float) DB::table('expenses')
                ->whereNull('voided_at')
                ->whereNotNull('paid_at')
                ->sum('amount');

        $generalExpenses =
            (float) DB::table('expenses')
                ->whereNull('project_id')
                ->whereNull('voided_at')
                ->whereNotNull('paid_at')
                ->sum('amount');

        return [
            /*
            |--------------------------------------------------------------------------
            | Phase 9 summary keys
            |--------------------------------------------------------------------------
            */

            'contract_value' =>
                $contractValue,

            'project_expenses' =>
                $projectExpenses,

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

            'general_business_expenses' =>
                $generalExpenses,

            'contract_profit_after_general_expenses' =>
                $contractProfit
                - $generalExpenses,

            'period_net_collections' =>
                $netCollections,

            'period_paid_expenses' =>
                $paidExpenses,

            'cash_contribution' =>
                $netCollections
                - $paidExpenses,

            'profitable_projects' =>
                Project::query()
                    ->whereColumn(
                        'project_price',
                        '>',
                        'project_expense_amount'
                    )
                    ->count(),

            'loss_making_projects' =>
                Project::query()
                    ->whereColumn(
                        'project_expense_amount',
                        '>',
                        'project_price'
                    )
                    ->count(),

            /*
            |--------------------------------------------------------------------------
            | Existing Phase 5 dashboard keys
            |--------------------------------------------------------------------------
            */

            'total_project_value' =>
                $contractValue,

            'total_project_expenses' =>
                $projectExpenses,

            'actual_project_profit' =>
                $contractProfit,

            'total_received' =>
                $netCollections,

            'total_paid_expenses' =>
                $paidExpenses,

            'business_cash_position' =>
                $netCollections
                - $paidExpenses,
        ];
    }

    private function dashboardPeriodSummary(
        CarbonInterface $from,
        CarbonInterface $to
    ): array {
        $collectionRow =
            DB::table('payments')
                ->selectRaw(
                    'SUM(CASE WHEN kind = ? THEN amount ELSE -amount END) as net_amount',
                    [
                        PaymentKind::Receipt
                            ->value,
                    ]
                )
                ->where(
                    'status',
                    PaymentStatus::Cleared
                        ->value
                )
                ->whereNull('voided_at')
                ->whereBetween(
                    'payment_date',
                    [
                        $from->toDateString(),
                        $to->toDateString(),
                    ]
                )
                ->first();

        $collection =
            (float) (
                $collectionRow
                    ?->net_amount
                ?? 0
            );

        $totalExpenses =
            (float) DB::table('expenses')
                ->whereNull('voided_at')
                ->whereNotNull('paid_at')
                ->whereBetween(
                    'expense_date',
                    [
                        $from->toDateString(),
                        $to->toDateString(),
                    ]
                )
                ->sum('amount');

        return [
            'period' =>
                $from->format('Y-m'),

            'label' =>
                $from->format('M Y'),

            'from' =>
                $from->toDateString(),

            'to' =>
                $to->toDateString(),

            'collection' =>
                $collection,

            'total_expenses' =>
                $totalExpenses,

            'cash_profit' =>
                $collection
                - $totalExpenses,
        ];
    }

    private function projectQuery(
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
            ->whereNotIn('status', [
                ProjectStatus::Cancelled->value,
            ])
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
            );
    }

    private function generalExpenses(
        ReportFilters $filters
    ): float {
        return (float) DB::table('expenses')
            ->whereNull('project_id')
            ->whereNull('voided_at')
            ->whereNotNull('paid_at')
            ->whereBetween(
                'expense_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->sum('amount');
    }

    private function periodPaidExpenses(
        ReportFilters $filters
    ): float {
        return (float) DB::table('expenses')
            ->whereNull('voided_at')
            ->whereNotNull('paid_at')
            ->whereBetween(
                'expense_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->when(
                $filters->projectId,
                fn ($query) =>
                    $query->where(
                        'project_id',
                        $filters->projectId
                    )
            )
            ->sum('amount');
    }

    private function periodNetCollections(
        ReportFilters $filters
    ): float {
        $row = DB::table('payments')
            ->selectRaw(
                'SUM(CASE WHEN kind = ? THEN amount ELSE -amount END) as net_amount',
                [
                    PaymentKind::Receipt->value,
                ]
            )
            ->where(
                'status',
                PaymentStatus::Cleared->value
            )
            ->whereNull('voided_at')
            ->whereBetween(
                'payment_date',
                [
                    $filters->from
                        ->toDateString(),

                    $filters->to
                        ->toDateString(),
                ]
            )
            ->when(
                $filters->projectId,
                fn ($query) =>
                    $query->where(
                        'project_id',
                        $filters->projectId
                    )
            )
            ->first();

        return (float) (
            $row?->net_amount
            ?? 0
        );
    }
}

