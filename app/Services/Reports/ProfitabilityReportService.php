<?php

namespace App\Services\Reports;

use App\Enums\ExpenseScope;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Carbon;

class ProfitabilityReportService
{
    public function summary(): array
    {
        $totalCollection = $this->netCollection();

        $projectExpenses = (float) Expense::query()
            ->effective()
            ->projectExpenses()
            ->sum('amount');

        $businessExpenses = (float) Expense::query()
            ->effective()
            ->businessExpenses()
            ->sum('amount');

        $totalExpenses =
            $projectExpenses +
            $businessExpenses;

        return [
            'contracted_value' =>
                (float) Project::query()
                    ->sum('project_price'),

            'total_collection' =>
                $totalCollection,

            'market_outstanding' =>
                (float) Project::query()
                    ->sum('pending_amount'),

            'project_expenses' =>
                $projectExpenses,

            'business_expenses' =>
                $businessExpenses,

            'total_expenses' =>
                $totalExpenses,

            'contracted_project_profit' =>
                (float) Project::query()
                    ->sum('actual_profit_amount'),

            'business_cash_position' =>
                round(
                    $totalCollection -
                    $totalExpenses,
                    2
                ),

            'loss_making_projects' =>
                Project::query()
                    ->where(
                        'actual_profit_amount',
                        '<',
                        0
                    )
                    ->count(),

            'cash_negative_projects' =>
                Project::query()
                    ->where(
                        'cash_position_amount',
                        '<',
                        0
                    )
                    ->count(),
        ];
    }

    public function monthly(
        int $months = 12
    ): array {
        $rows = [];

        for (
            $offset = $months - 1;
            $offset >= 0;
            $offset--
        ) {
            $month = now()
                ->startOfMonth()
                ->subMonths($offset);

            $start = $month
                ->copy()
                ->startOfMonth();

            $end = $month
                ->copy()
                ->endOfMonth();

            $collection = $this->netCollection(
                $start,
                $end
            );

            $projectExpenses = (float) Expense::query()
                ->effective()
                ->projectExpenses()
                ->whereBetween('paid_at', [
                    $start->toDateString(),
                    $end->toDateString(),
                ])
                ->sum('amount');

            $businessExpenses = (float) Expense::query()
                ->effective()
                ->businessExpenses()
                ->whereBetween('paid_at', [
                    $start->toDateString(),
                    $end->toDateString(),
                ])
                ->sum('amount');

            $totalExpenses =
                $projectExpenses +
                $businessExpenses;

            $bookedValue = (float) Project::query()
                ->whereBetween('created_at', [
                    $start,
                    $end,
                ])
                ->sum('project_price');

            $rows[] = [
                'key' => $month->format('Y-m'),
                'label' => $month->format('M Y'),

                'booked_value' =>
                    round($bookedValue, 2),

                'collection' =>
                    round($collection, 2),

                'project_expenses' =>
                    round($projectExpenses, 2),

                'business_expenses' =>
                    round($businessExpenses, 2),

                'total_expenses' =>
                    round($totalExpenses, 2),

                'cash_profit' =>
                    round(
                        $collection -
                        $totalExpenses,
                        2
                    ),
            ];
        }

        $maximum = collect($rows)
            ->flatMap(
                fn (array $row): array => [
                    abs($row['collection']),
                    abs($row['total_expenses']),
                    abs($row['cash_profit']),
                ]
            )
            ->max() ?: 1;

        return [
            'rows' => $rows,
            'maximum' => (float) $maximum,
        ];
    }

    public function monthSummary(
        ?Carbon $month = null
    ): array {
        $month ??= now();

        $start = $month
            ->copy()
            ->startOfMonth();

        $end = $month
            ->copy()
            ->endOfMonth();

        $collection = $this->netCollection(
            $start,
            $end
        );

        $projectExpenses = (float) Expense::query()
            ->effective()
            ->projectExpenses()
            ->whereBetween('paid_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('amount');

        $businessExpenses = (float) Expense::query()
            ->effective()
            ->businessExpenses()
            ->whereBetween('paid_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('amount');

        $totalExpenses =
            $projectExpenses +
            $businessExpenses;

        return [
            'month' => $month->format('F Y'),
            'collection' => round($collection, 2),

            'project_expenses' =>
                round($projectExpenses, 2),

            'business_expenses' =>
                round($businessExpenses, 2),

            'total_expenses' =>
                round($totalExpenses, 2),

            'cash_profit' =>
                round(
                    $collection -
                    $totalExpenses,
                    2
                ),
        ];
    }

    private function netCollection(
        ?Carbon $start = null,
        ?Carbon $end = null
    ): float {
        $receiptsQuery = Payment::query()
            ->effective()
            ->receipts();

        $refundsQuery = Payment::query()
            ->effective()
            ->refunds();

        if ($start && $end) {
            $range = [
                $start->toDateString(),
                $end->toDateString(),
            ];

            $receiptsQuery
                ->whereBetween(
                    'payment_date',
                    $range
                );

            $refundsQuery
                ->whereBetween(
                    'payment_date',
                    $range
                );
        }

        return round(
            (float) $receiptsQuery->sum('amount') -
            (float) $refundsQuery->sum('amount'),
            2
        );
    }
}