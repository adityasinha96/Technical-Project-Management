<?php

namespace App\Services\Payments;

use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Models\Project;
use App\Services\Expenses\ProjectProfitabilityService;

class ProjectFinancialService
{
    public function __construct(
        private readonly ProjectProfitabilityService $profitabilityService
    ) {
    }

    public function synchronize(
        Project $project
    ): array {
        /*
         * The payments relationship is normally ordered by
         * payment_date and id for display purposes.
         *
         * This aggregate query must remove that inherited ordering,
         * otherwise MySQL rejects the SUM/MAX query when
         * ONLY_FULL_GROUP_BY mode is enabled.
         */
        $totals = $project
            ->payments()
            ->where(
                'status',
                PaymentStatus::Cleared->value
            )
            ->whereNull('voided_at')
            ->reorder()
            ->selectRaw(
                '
                COALESCE(
                    SUM(
                        CASE
                            WHEN kind = ? THEN amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_receipts,

                COALESCE(
                    SUM(
                        CASE
                            WHEN kind = ? THEN amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_refunds,

                MAX(payment_date) AS last_payment_date
                ',
                [
                    PaymentKind::Receipt->value,
                    PaymentKind::Refund->value,
                ]
            )
            ->first();

        $totalReceipts = round(
            (float) ($totals?->total_receipts ?? 0),
            2
        );

        $totalRefunds = round(
            (float) ($totals?->total_refunds ?? 0),
            2
        );

        $netReceived = max(
            0,
            round(
                $totalReceipts - $totalRefunds,
                2
            )
        );

        $projectPrice = max(
            0,
            round(
                (float) $project->project_price,
                2
            )
        );

        $pendingAmount = max(
            0,
            round(
                $projectPrice - $netReceived,
                2
            )
        );

        $collectionPercentage = $projectPrice > 0
            ? round(
                ($netReceived / $projectPrice) * 100,
                2
            )
            : ($netReceived > 0 ? 100 : 0);

        $project->forceFill([
            'net_received_amount' => $netReceived,

            'pending_amount' => $pendingAmount,

            'collection_percentage' =>
                $collectionPercentage,

            'last_payment_date' =>
                $totals?->last_payment_date,
        ])->saveQuietly();

        /*
         * Recalculate project profitability after payment totals
         * have changed. This keeps the cash position synchronized
         * after payments, refunds and void actions.
         */
        $this->profitabilityService
            ->synchronize($project);

        return [
            'total_receipts' => $totalReceipts,

            'total_refunds' => $totalRefunds,

            'net_received' => $netReceived,

            'pending_amount' => $pendingAmount,

            'collection_percentage' =>
                $collectionPercentage,

            'last_payment_date' =>
                $totals?->last_payment_date,
        ];
    }
}