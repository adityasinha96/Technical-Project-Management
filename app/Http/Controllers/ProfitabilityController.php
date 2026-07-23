<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Services\Reports\ProfitabilityReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfitabilityController extends Controller
{
    public function __construct(
        private readonly ProfitabilityReportService $reportService
    ) {
    }

    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->search(
                $request->string('search')->toString()
            )
            ->when(
                $request->string('health')->toString()
                    === 'loss',
                fn ($query) =>
                    $query->where(
                        'actual_profit_amount',
                        '<',
                        0
                    )
            )
            ->when(
                $request->string('health')->toString()
                    === 'low_margin',
                fn ($query) =>
                    $query
                        ->where(
                            'actual_profit_amount',
                            '>=',
                            0
                        )
                        ->where(
                            'profit_margin_percentage',
                            '<',
                            10
                        )
            )
            ->when(
                $request->string('health')->toString()
                    === 'cash_negative',
                fn ($query) =>
                    $query->where(
                        'cash_position_amount',
                        '<',
                        0
                    )
            )
            ->when(
                $request->string('sort')->toString()
                    === 'lowest_margin',
                fn ($query) =>
                    $query->orderBy(
                        'profit_margin_percentage'
                    )
            )
            ->when(
                $request->string('sort')->toString()
                    !== 'lowest_margin',
                fn ($query) =>
                    $query->orderByDesc(
                        'actual_profit_amount'
                    )
            )
            ->paginate(20)
            ->withQueryString();

        return view(
            'profitability.index',
            [
                'projects' => $projects,

                'summary' =>
                    $this->reportService->summary(),

                'monthly' =>
                    $this->reportService->monthly(12),

                'monthSummary' =>
                    $this->reportService
                        ->monthSummary(),

                'expenseCategories' =>
                    ExpenseCategory::query()
                        ->active()
                        ->get(),
            ]
        );
    }
}