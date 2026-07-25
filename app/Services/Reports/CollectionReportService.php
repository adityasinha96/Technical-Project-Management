<?php

namespace App\Services\Reports;

use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Enums\ProjectStatus;
use App\Models\Payment;
use App\Models\Project;
use App\Support\Reports\ReportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CollectionReportService
{
    public function summary(
        ReportFilters $filters
    ): array {
        $paymentQuery =
            $this->paymentQuery(
                $filters
            );

        $receipts =
            (float) (clone $paymentQuery)
                ->where(
                    'kind',
                    PaymentKind::Receipt->value
                )
                ->sum('amount');

        $refunds =
            (float) (clone $paymentQuery)
                ->where(
                    'kind',
                    PaymentKind::Refund->value
                )
                ->sum('amount');

        $netCollections =
            $receipts - $refunds;

        $outstandingQuery =
            $this->outstandingQuery(
                $filters
            );

        $currentOutstanding =
            (float) (clone $outstandingQuery)
                ->sum('pending_amount');

        $contractValue =
            (float) (clone $outstandingQuery)
                ->sum('project_price');

        $lifetimeCollected =
            (float) (clone $outstandingQuery)
                ->sum(
                    'net_received_amount'
                );

        return [
            'period_receipts' =>
                $receipts,

            'period_refunds' =>
                $refunds,

            'period_net_collections' =>
                $netCollections,

            'current_outstanding' =>
                $currentOutstanding,

            'matching_contract_value' =>
                $contractValue,

            'lifetime_collected' =>
                $lifetimeCollected,

            'collection_percentage' =>
                $contractValue > 0
                    ? round(
                        $lifetimeCollected
                        / $contractValue
                        * 100,
                        2
                    )
                    : 0,

            'projects_with_outstanding' =>
                (clone $outstandingQuery)
                    ->where(
                        'pending_amount',
                        '>',
                        0
                    )
                    ->count(),

            'overdue_outstanding' =>
                (float) (clone $outstandingQuery)
                    ->where(
                        'pending_amount',
                        '>',
                        0
                    )
                    ->whereRaw(
                        'COALESCE(collection_due_date, expected_delivery_date) < ?',
                        [
                            today()
                                ->toDateString(),
                        ]
                    )
                    ->sum(
                        'pending_amount'
                    ),
        ];
    }

    public function monthlyTrend(
        ReportFilters $filters
    ): Collection {
        $rows = DB::table('payments')
            ->join(
                'projects',
                'projects.id',
                '=',
                'payments.project_id'
            )
            ->selectRaw(
                "DATE_FORMAT(payment_date, '%Y-%m') as period"
            )
            ->selectRaw(
                'SUM(CASE WHEN kind = ? THEN amount ELSE 0 END) as receipts',
                [
                    PaymentKind::Receipt->value,
                ]
            )
            ->selectRaw(
                'SUM(CASE WHEN kind = ? THEN amount ELSE 0 END) as refunds',
                [
                    PaymentKind::Refund->value,
                ]
            )
            ->where(
                'payments.status',
                PaymentStatus::Cleared->value
            )
            ->whereNull(
                'payments.voided_at'
            )
            ->whereBetween(
                'payments.payment_date',
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
                        'payments.project_id',
                        $filters->projectId
                    )
            )
            ->when(
                $filters->clientId,
                fn ($query) =>
                    $query->where(
                        'projects.client_id',
                        $filters->clientId
                    )
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $trend = collect();

        $cursor = $filters->from
            ->startOfMonth();

        while (
            $cursor->lessThanOrEqualTo(
                $filters->to
            )
        ) {
            $key = $cursor->format('Y-m');
            $row = $rows->get($key);

            $receipts =
                (float) (
                    $row->receipts
                    ?? 0
                );

            $refunds =
                (float) (
                    $row->refunds
                    ?? 0
                );

            $trend->push([
                'period' => $key,

                'label' =>
                    $cursor->format('M Y'),

                'receipts' =>
                    $receipts,

                'refunds' =>
                    $refunds,

                'net' =>
                    $receipts - $refunds,
            ]);

            $cursor = $cursor->addMonth();
        }

        return $trend;
    }

    public function paymentModeBreakdown(
        ReportFilters $filters
    ): Collection {
        return $this->paymentQuery($filters)
            ->select('payment_mode')
            ->selectRaw(
                'SUM(CASE WHEN kind = ? THEN amount ELSE -amount END) as net_amount',
                [
                    PaymentKind::Receipt->value,
                ]
            )
            ->selectRaw(
                'COUNT(*) as transactions'
            )
            ->groupBy('payment_mode')
            ->orderByDesc('net_amount')
            ->get();
    }

    public function ageing(
        ReportFilters $filters
    ): Collection {
        $projects =
            $this->outstandingQuery(
                $filters
            )
                ->where(
                    'pending_amount',
                    '>',
                    0
                )
                ->get();

        $buckets = collect([
            'Current' => 0.0,
            '1–30 Days' => 0.0,
            '31–60 Days' => 0.0,
            '61–90 Days' => 0.0,
            '90+ Days' => 0.0,
        ]);

        foreach ($projects as $project) {
            $bucket =
                $project
                    ->collection_ageing_bucket;

            if ($buckets->has($bucket)) {
                $buckets[$bucket] +=
                    (float)
                    $project->pending_amount;
            }
        }

        return $buckets->map(
            fn (
                float $amount,
                string $bucket
            ) => [
                'bucket' => $bucket,
                'amount' => $amount,
            ]
        )->values();
    }

    public function topOutstanding(
        ReportFilters $filters
    ): Collection {
        return $this->outstandingQuery(
            $filters
        )
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'pending_amount',
                '>',
                0
            )
            ->orderByDesc(
                'pending_amount'
            )
            ->limit(10)
            ->get();
    }

    public function paginatedOutstanding(
        ReportFilters $filters
    ) {
        return $this->outstandingQuery(
            $filters
        )
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'pending_amount',
                '>',
                0
            )
            ->orderByDesc(
                'pending_amount'
            )
            ->paginate(
                $filters->perPage
            )
            ->withQueryString();
    }

    public function exportQuery(
        ReportFilters $filters
    ): Builder {
        return $this->outstandingQuery(
            $filters
        )
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'pending_amount',
                '>',
                0
            )
            ->orderBy('id');
    }

    private function paymentQuery(
        ReportFilters $filters
    ): Builder {
        return Payment::query()
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
                fn (Builder $query) =>
                    $query->where(
                        'project_id',
                        $filters->projectId
                    )
            )
            ->when(
                $filters->clientId,
                fn (Builder $query) =>
                    $query->whereHas(
                        'project',
                        fn (Builder $query) =>
                            $query->where(
                                'client_id',
                                $filters
                                    ->clientId
                            )
                    )
            );
    }

    private function outstandingQuery(
        ReportFilters $filters
    ): Builder {
        return Project::query()
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
}