<?php

namespace App\Services\Reports;

use App\Enums\ReportExportStatus;
use App\Enums\ReportType;
use App\Models\ReportExport;
use App\Models\User;
use App\Support\Reports\ReportFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ReportExportService
{
    public function __construct(
        private readonly ProjectAnalyticsService $projects,
        private readonly TeamPerformanceReportService $team,
        private readonly CollectionReportService $collections,
        private readonly ProfitabilityReportService $profitability,
        private readonly TicketSlaReportService $ticketSla
    ) {
    }

    public function stream(
        ReportType $type,
        ReportFilters $filters,
        User $user
    ): StreamedResponse {
        $filename = sprintf(
            '%s-%s.csv',
            $type->filenamePrefix(),
            now()->format(
                'Y-m-d-His'
            )
        );

        $export = ReportExport::create([
            'export_uuid' =>
                (string) Str::uuid(),

            'report_type' =>
                $type->value,

            'format' => 'csv',

            'filters' =>
                $filters->toArray(),

            'filename' =>
                $filename,

            'generated_by' =>
                $user->id,

            'status' =>
                ReportExportStatus::Processing
                    ->value,

            'started_at' => now(),

            'ip_address' =>
                request()->ip(),

            'user_agent' =>
                request()->userAgent(),
        ]);

        return response()->streamDownload(
            function () use (
                $type,
                $filters,
                $export
            ): void {
                $handle = fopen(
                    'php://output',
                    'wb'
                );

                $rowsExported = 0;

                try {
                    /*
                     * UTF-8 BOM helps Microsoft Excel
                     * recognise Unicode text.
                     */
                    fwrite(
                        $handle,
                        "\xEF\xBB\xBF"
                    );

                    [
                        $headers,
                        $rows,
                    ] = $this->dataset(
                        $type,
                        $filters
                    );

                    fputcsv(
                        $handle,
                        $headers
                    );

                    foreach ($rows as $row) {
                        fputcsv(
                            $handle,
                            array_map(
                                fn ($value) =>
                                    $this
                                        ->safeCsvValue(
                                            $value
                                        ),
                                $row
                            )
                        );

                        $rowsExported++;
                    }

                    $export->update([
                        'status' =>
                            ReportExportStatus::Completed
                                ->value,

                        'rows_exported' =>
                            $rowsExported,

                        'completed_at' =>
                            now(),
                    ]);
                } catch (Throwable $exception) {
                    $export->update([
                        'status' =>
                            ReportExportStatus::Failed
                                ->value,

                        'failed_at' =>
                            now(),

                        'error_message' =>
                            str(
                                $exception
                                    ->getMessage()
                            )->limit(5000),
                    ]);

                    report($exception);

                    throw $exception;
                } finally {
                    if (
                        is_resource(
                            $handle
                        )
                    ) {
                        fclose($handle);
                    }
                }
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',

                'Cache-Control' =>
                    'no-store, no-cache',
            ]
        );
    }

    private function dataset(
        ReportType $type,
        ReportFilters $filters
    ): array {
        return match ($type) {
            ReportType::Projects =>
                $this->projectDataset(
                    $filters
                ),

            ReportType::TeamPerformance =>
                $this->teamDataset(
                    $filters
                ),

            ReportType::Collections =>
                $this->collectionDataset(
                    $filters
                ),

            ReportType::Profitability =>
                $this->profitabilityDataset(
                    $filters
                ),

            ReportType::TicketSla =>
                $this->ticketDataset(
                    $filters
                ),
        };
    }

    private function projectDataset(
        ReportFilters $filters
    ): array {
        $headers = [
            'Project',
            'Client',
            'Manager',
            'Status',
            'Priority',
            'Start Date',
            'Expected Delivery',
            'Actual Completion',
            'Official Progress',
            'Internal Progress',
            'Contract Value',
            'Net Collected',
            'Outstanding',
            'Project Expenses',
            'Contract Profit',
            'Collection Percentage',
        ];

        $rows = $this->projects
            ->exportQuery($filters)
            ->lazyById(500)
            ->map(
                fn ($project) => [
                    $project->name,
                    $project->client?->name,
                    $project->manager?->name,
                    $project->status->label(),
                    $project->priority->label(),

                    $project->start_date
                        ?->format('Y-m-d'),

                    $project
                        ->expected_delivery_date
                        ?->format('Y-m-d'),

                    $project
                        ->actual_completion_date
                        ?->format('Y-m-d'),

                    $project
                        ->official_progress,

                    $project
                        ->internal_progress,

                    number_format(
                        (float)
                        $project->project_price,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project
                            ->net_received_amount,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project->pending_amount,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project
                            ->project_expense_amount,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project->project_price
                        - (float)
                        $project
                            ->project_expense_amount,
                        2,
                        '.',
                        ''
                    ),

                    $project
                        ->collection_percentage,
                ]
            );

        return [
            $headers,
            $rows,
        ];
    }

    private function teamDataset(
        ReportFilters $filters
    ): array {
        $headers = [
            'Team Member',
            'Email',
            'Assigned Tasks',
            'Completed Tasks',
            'Overdue Tasks',
            'Task Completion Rate',
            'On-Time Completion Rate',
            'Average Completion Hours',
            'Work Minutes',
            'Billable Minutes',
            'Assigned Tickets',
            'Resolved Tickets',
            'Escalated Tickets',
            'First Response SLA Compliance',
            'Delivery Index',
        ];

        $rows = $this->team
            ->rows($filters)
            ->map(
                fn (array $row) => [
                    $row['name'],
                    $row['email'],
                    $row['total_tasks'],
                    $row['completed_tasks'],
                    $row['overdue_tasks'],
                    $row['completion_rate'],
                    $row['on_time_rate'],
                    $row[
                        'average_completion_hours'
                    ],
                    $row['work_minutes'],
                    $row['billable_minutes'],
                    $row['assigned_tickets'],
                    $row['resolved_tickets'],
                    $row['escalated_tickets'],
                    $row['sla_compliance'],
                    $row['delivery_index'],
                ]
            );

        return [
            $headers,
            $rows,
        ];
    }

    private function collectionDataset(
        ReportFilters $filters
    ): array {
        $headers = [
            'Project',
            'Client',
            'Manager',
            'Contract Value',
            'Net Collected',
            'Outstanding',
            'Collection Percentage',
            'Collection Due Date',
            'Days Overdue',
            'Ageing Bucket',
            'Last Payment Date',
            'Status',
        ];

        $rows = $this->collections
            ->exportQuery($filters)
            ->lazyById(500)
            ->map(
                fn ($project) => [
                    $project->name,
                    $project->client?->name,
                    $project->manager?->name,

                    number_format(
                        (float)
                        $project->project_price,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project
                            ->net_received_amount,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project->pending_amount,
                        2,
                        '.',
                        ''
                    ),

                    $project
                        ->collection_percentage,

                    $project
                        ->effective_collection_due_date
                        ?->format('Y-m-d'),

                    $project
                        ->collection_days_overdue,

                    $project
                        ->collection_ageing_bucket,

                    $project
                        ->last_payment_date
                        ?->format('Y-m-d'),

                    $project->status->label(),
                ]
            );

        return [
            $headers,
            $rows,
        ];
    }

    private function profitabilityDataset(
        ReportFilters $filters
    ): array {
        $headers = [
            'Project',
            'Client',
            'Manager',
            'Contract Value',
            'Project Expenses',
            'Contract Profit',
            'Contract Margin Percentage',
            'Net Collected',
            'Outstanding',
            'Cash Contribution to Date',
            'Status',
        ];

        $rows = $this->profitability
            ->exportQuery($filters)
            ->lazyById(500)
            ->map(
                fn ($project) => [
                    $project->name,
                    $project->client?->name,
                    $project->manager?->name,

                    number_format(
                        (float)
                        $project->project_price,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project
                            ->project_expense_amount,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project
                            ->calculated_profit,
                        2,
                        '.',
                        ''
                    ),

                    round(
                        (float)
                        $project
                            ->calculated_margin,
                        2
                    ),

                    number_format(
                        (float)
                        $project
                            ->net_received_amount,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project->pending_amount,
                        2,
                        '.',
                        ''
                    ),

                    number_format(
                        (float)
                        $project
                            ->net_received_amount
                        - (float)
                        $project
                            ->project_expense_amount,
                        2,
                        '.',
                        ''
                    ),

                    $project->status->label(),
                ]
            );

        return [
            $headers,
            $rows,
        ];
    }

    private function ticketDataset(
        ReportFilters $filters
    ): array {
        $headers = [
            'Ticket Number',
            'Subject',
            'Project',
            'Client',
            'Assignee',
            'Priority',
            'Status',
            'Created At',
            'First Response Due',
            'First Responded At',
            'Response SLA Compliant',
            'Resolution Due',
            'Resolved At',
            'Resolution SLA Compliant',
            'Escalation Level',
            'Reopen Count',
            'SLA Paused Minutes',
        ];

        $rows = $this->ticketSla
            ->exportQuery($filters)
            ->lazyById(500)
            ->map(
                fn ($ticket) => [
                    $ticket->ticket_number,
                    $ticket->subject,
                    $ticket->project?->name,
                    $ticket->client?->name,
                    $ticket
                        ->assignedTo?->name,
                    $ticket->priority->label(),
                    $ticket->status->label(),

                    $ticket->created_at
                        ?->format(
                            'Y-m-d H:i:s'
                        ),

                    $ticket
                        ->first_response_due_at
                        ?->format(
                            'Y-m-d H:i:s'
                        ),

                    $ticket
                        ->first_responded_at
                        ?->format(
                            'Y-m-d H:i:s'
                        ),

                    $ticket
                        ->first_responded_at
                    && $ticket
                        ->first_response_due_at
                    && $ticket
                        ->first_responded_at
                        ->lte(
                            $ticket
                                ->first_response_due_at
                        )
                        ? 'Yes'
                        : 'No',

                    $ticket
                        ->resolution_due_at
                        ?->format(
                            'Y-m-d H:i:s'
                        ),

                    $ticket
                        ->resolved_at
                        ?->format(
                            'Y-m-d H:i:s'
                        ),

                    $ticket->resolved_at
                    && $ticket
                        ->resolution_due_at
                    && $ticket
                        ->resolved_at
                        ->lte(
                            $ticket
                                ->resolution_due_at
                        )
                        ? 'Yes'
                        : 'No',

                    $ticket
                        ->escalation_level,

                    $ticket->reopen_count,

                    $ticket
                        ->sla_paused_minutes,
                ]
            );

        return [
            $headers,
            $rows,
        ];
    }

    private function safeCsvValue(
        mixed $value
    ): mixed {
        if (!is_string($value)) {
            return $value;
        }

        /*
         * Prevent spreadsheet formula
         * execution from user-entered text.
         */
        if (
            preg_match(
                '/^[=\-+@]/',
                ltrim($value)
            )
        ) {
            return "'{$value}";
        }

        return $value;
    }
}