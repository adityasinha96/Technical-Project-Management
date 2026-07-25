<?php

namespace App\Http\Controllers\Reports;

use App\Enums\ReportType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportExportRequest;
use App\Models\ReportExport;
use App\Services\Reports\ReportExportService;
use App\Support\Reports\ReportFilters;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        private readonly ReportExportService $exportService
    ) {
    }

    public function index(): View
    {
        Gate::authorize(
            'reports.view-export-history'
        );

        return view(
            'reports.exports',
            [
                'exports' =>
                    ReportExport::query()
                        ->with('generatedBy')
                        ->latest()
                        ->paginate(30),
            ]
        );
    }

    public function store(
        ReportExportRequest $request
    ): StreamedResponse {
        $type = ReportType::from(
            $request->validated(
                'report_type'
            )
        );

        if ($type->containsFinancialData()) {
            Gate::authorize(
                'reports.view-financial'
            );
        }

        if (
            $type ===
            ReportType::TeamPerformance
        ) {
            Gate::authorize(
                'reports.view-team'
            );
        }

        if (
            $type ===
            ReportType::TicketSla
        ) {
            Gate::authorize(
                'reports.view-ticket-sla'
            );
        }

        $filters = ReportFilters::fromArray(
            $request->validated()
        );

        return $this->exportService->stream(
            type: $type,
            filters: $filters,
            user: $request->user()
        );
    }
}