<?php

namespace App\Services\Expenses;

use App\Enums\ExpenseScope;
use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        private readonly ProjectProfitabilityService $profitabilityService
    ) {
    }

    public function record(
        User $createdBy,
        array $data,
        array $receiptData = []
    ): Expense {
        return DB::transaction(
            function () use (
                $createdBy,
                $data,
                $receiptData
            ): Expense {
                $scope = ExpenseScope::from(
                    $data['scope']
                );

                $status = ExpenseStatus::from(
                    $data['status']
                );

                $project = null;

                if ($scope === ExpenseScope::Project) {
                    $project = Project::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $data['project_id']
                        );
                }

                $expense = Expense::create([
                    ...$data,
                    ...$receiptData,

                    'project_id' =>
                        $scope === ExpenseScope::Project
                            ? $project?->id
                            : null,

                    'paid_at' =>
                        $status === ExpenseStatus::Paid
                            ? (
                                $data['paid_at']
                                ?? $data['expense_date']
                            )
                            : null,

                    'created_by' => $createdBy->id,
                ]);

                $expense->forceFill([
                    'expense_number' => sprintf(
                        'EXP-%s-%05d',
                        $expense->expense_date->format('Y'),
                        $expense->id
                    ),
                ])->saveQuietly();

                if ($project) {
                    $this->profitabilityService
                        ->synchronize($project);
                }

                return $expense;
            }
        );
    }

    public function updatePending(
        Expense $expense,
        array $data,
        array $receiptData = []
    ): Expense {
        if ($expense->is_voided) {
            throw ValidationException::withMessages([
                'expense' =>
                    'A voided expense cannot be updated.',
            ]);
        }

        if ($expense->status !== ExpenseStatus::Pending) {
            throw ValidationException::withMessages([
                'expense' =>
                    'Only a pending expense can be edited.',
            ]);
        }

        return DB::transaction(
            function () use (
                $expense,
                $data,
                $receiptData
            ): Expense {
                $expense = Expense::query()
                    ->lockForUpdate()
                    ->findOrFail($expense->id);

                $oldProjectId = $expense->project_id;

                $scope = ExpenseScope::from(
                    $data['scope']
                );

                $expense->update([
                    ...$data,
                    ...$receiptData,

                    'project_id' =>
                        $scope === ExpenseScope::Project
                            ? $data['project_id']
                            : null,

                    'paid_at' => null,
                ]);

                $affectedProjectIds = collect([
                    $oldProjectId,
                    $expense->project_id,
                ])->filter()->unique();

                foreach ($affectedProjectIds as $projectId) {
                    $project = Project::query()
                        ->find($projectId);

                    if ($project) {
                        $this->profitabilityService
                            ->synchronize($project);
                    }
                }

                return $expense->refresh();
            }
        );
    }

    public function changeStatus(
        Expense $expense,
        ExpenseStatus $newStatus,
        ?string $paidAt = null
    ): void {
        if ($expense->is_voided) {
            throw ValidationException::withMessages([
                'status' =>
                    'A voided expense cannot be updated.',
            ]);
        }

        if ($expense->status !== ExpenseStatus::Pending) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only a pending expense can have its status changed.',
            ]);
        }

        if ($newStatus === ExpenseStatus::Pending) {
            throw ValidationException::withMessages([
                'status' =>
                    'Select Paid or Cancelled.',
            ]);
        }

        DB::transaction(
            function () use (
                $expense,
                $newStatus,
                $paidAt
            ): void {
                $expense = Expense::query()
                    ->lockForUpdate()
                    ->findOrFail($expense->id);

                $expense->update([
                    'status' => $newStatus->value,

                    'paid_at' =>
                        $newStatus === ExpenseStatus::Paid
                            ? (
                                $paidAt
                                ?: $expense
                                    ->expense_date
                                    ->toDateString()
                            )
                            : null,
                ]);

                if ($expense->project) {
                    $this->profitabilityService
                        ->synchronize(
                            $expense->project
                        );
                }
            }
        );
    }

    public function void(
        Expense $expense,
        User $voidedBy,
        string $reason
    ): void {
        if ($expense->is_voided) {
            throw ValidationException::withMessages([
                'void_reason' =>
                    'This expense has already been voided.',
            ]);
        }

        if ($expense->status !== ExpenseStatus::Paid) {
            throw ValidationException::withMessages([
                'void_reason' =>
                    'Only a paid expense requires voiding. Pending expenses should be cancelled.',
            ]);
        }

        DB::transaction(
            function () use (
                $expense,
                $voidedBy,
                $reason
            ): void {
                $expense = Expense::query()
                    ->lockForUpdate()
                    ->findOrFail($expense->id);

                $expense->update([
                    'voided_by' => $voidedBy->id,
                    'voided_at' => now(),
                    'void_reason' => $reason,
                ]);

                if ($expense->project) {
                    $this->profitabilityService
                        ->synchronize(
                            $expense->project
                        );
                }
            }
        );
    }
}