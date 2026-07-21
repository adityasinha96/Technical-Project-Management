<?php

namespace App\Services\Payments;

use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly ProjectFinancialService $financialService
    ) {
    }

    public function record(
        Project $project,
        User $createdBy,
        array $data,
        ?int $proofFileId = null
    ): Payment {
        return DB::transaction(
            function () use (
                $project,
                $createdBy,
                $data,
                $proofFileId
            ): Payment {
                $lockedProject = Project::query()
                    ->lockForUpdate()
                    ->findOrFail($project->id);

                $kind = PaymentKind::from(
                    $data['kind']
                );

                $status = PaymentStatus::from(
                    $data['status']
                );

                if (
                    $kind === PaymentKind::Refund &&
                    $status === PaymentStatus::Cleared
                ) {
                    $this->assertRefundIsAllowed(
                        $lockedProject,
                        (float) $data['amount']
                    );
                }

                $payment = $lockedProject
                    ->payments()
                    ->create([
                        ...$data,

                        'client_id' =>
                            $lockedProject->client_id,

                        'proof_file_id' => $proofFileId,
                        'created_by' => $createdBy->id,

                        'cleared_at' =>
                            $status === PaymentStatus::Cleared
                                ? (
                                    $data['cleared_at']
                                    ?? $data['payment_date']
                                )
                                : null,
                    ]);

                $prefix = $kind === PaymentKind::Refund
                    ? 'REF'
                    : 'PAY';

                $payment->forceFill([
                    'payment_number' => sprintf(
                        '%s-%s-%05d',
                        $prefix,
                        $payment->payment_date->format('Y'),
                        $payment->id
                    ),
                ])->saveQuietly();

                $this->financialService
                    ->synchronize($lockedProject);

                return $payment;
            }
        );
    }

    public function changeStatus(
        Payment $payment,
        PaymentStatus $newStatus
    ): void {
        if ($payment->is_voided) {
            throw ValidationException::withMessages([
                'status' =>
                    'A voided payment cannot be updated.',
            ]);
        }

        if ($payment->status !== PaymentStatus::Pending) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only a pending payment can have its status changed.',
            ]);
        }

        DB::transaction(
            function () use (
                $payment,
                $newStatus
            ): void {
                $payment = Payment::query()
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                if (
                    $newStatus === PaymentStatus::Cleared &&
                    $payment->kind === PaymentKind::Refund
                ) {
                    $this->assertRefundIsAllowed(
                        $payment->project,
                        (float) $payment->amount
                    );
                }

                $payment->update([
                    'status' => $newStatus->value,

                    'cleared_at' =>
                        $newStatus === PaymentStatus::Cleared
                            ? today()
                            : null,
                ]);

                $this->financialService
                    ->synchronize($payment->project);
            }
        );
    }

    public function void(
        Payment $payment,
        User $voidedBy,
        string $reason
    ): void {
        if ($payment->is_voided) {
            throw ValidationException::withMessages([
                'void_reason' =>
                    'This payment has already been voided.',
            ]);
        }

        if ($payment->status !== PaymentStatus::Cleared) {
            throw ValidationException::withMessages([
                'void_reason' =>
                    'Only a cleared payment requires voiding. Pending payments should be cancelled instead.',
            ]);
        }

        DB::transaction(
            function () use (
                $payment,
                $voidedBy,
                $reason
            ): void {
                $payment = Payment::query()
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                $payment->update([
                    'voided_by' => $voidedBy->id,
                    'voided_at' => now(),
                    'void_reason' => $reason,
                ]);

                $this->financialService
                    ->synchronize($payment->project);
            }
        );
    }

    private function assertRefundIsAllowed(
        Project $project,
        float $refundAmount
    ): void {
        $this->financialService
            ->synchronize($project);

        $project->refresh();

        if (
            round($refundAmount, 2) >
            round(
                (float) $project->net_received_amount,
                2
            )
        ) {
            throw ValidationException::withMessages([
                'amount' =>
                    'The refund cannot exceed the net payment received for this project.',
            ]);
        }
    }
}