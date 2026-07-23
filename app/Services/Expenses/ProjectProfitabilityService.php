<?php

namespace App\Services\Expenses;

use App\Enums\ExpenseScope;
use App\Enums\ExpenseStatus;
use App\Models\Project;

class ProjectProfitabilityService
{
    public function synchronize(
        Project $project
    ): array {
        $projectExpenseAmount = round(
            (float) $project
                ->expenses()
                ->where(
                    'scope',
                    ExpenseScope::Project->value
                )
                ->where(
                    'status',
                    ExpenseStatus::Paid->value
                )
                ->whereNull('voided_at')
                ->sum('amount'),
            2
        );

        $projectPrice = round(
            (float) $project->project_price,
            2
        );

        $actualProfit = round(
            $projectPrice - $projectExpenseAmount,
            2
        );

        $profitMargin = $projectPrice > 0
            ? round(
                ($actualProfit / $projectPrice) * 100,
                2
            )
            : 0;

        $cashPosition = round(
            (float) $project->net_received_amount -
            $projectExpenseAmount,
            2
        );

        $project->forceFill([
            'project_expense_amount' =>
                $projectExpenseAmount,

            'actual_profit_amount' =>
                $actualProfit,

            'profit_margin_percentage' =>
                $profitMargin,

            'cash_position_amount' =>
                $cashPosition,
        ])->saveQuietly();

        return [
            'project_expense_amount' =>
                $projectExpenseAmount,

            'actual_profit_amount' =>
                $actualProfit,

            'profit_margin_percentage' =>
                $profitMargin,

            'cash_position_amount' =>
                $cashPosition,
        ];
    }
}