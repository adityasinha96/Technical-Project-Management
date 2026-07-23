<?php

namespace App\Http\Requests;

use App\Enums\ExpenseScope;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentMode;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'expenses.create'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'scope' => [
                'required',
                Rule::enum(ExpenseScope::class),
            ],

            'project_id' => [
                Rule::requiredIf(
                    $this->input('scope') ===
                    ExpenseScope::Project->value
                ),

                Rule::prohibitedIf(
                    $this->input('scope') ===
                    ExpenseScope::Business->value
                ),

                'nullable',

                Rule::exists('projects', 'id')
                    ->whereNull('deleted_at'),
            ],

            'expense_category_id' => [
                'required',

                Rule::exists(
                    'expense_categories',
                    'id'
                )
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'status' => [
                'required',

                Rule::in([
                    ExpenseStatus::Pending->value,
                    ExpenseStatus::Paid->value,
                ]),
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999999.99',
            ],

            'tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:amount',
            ],

            'expense_date' => [
                'required',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:expense_date',
            ],

            'paid_at' => [
                Rule::requiredIf(
                    $this->input('status') ===
                    ExpenseStatus::Paid->value
                ),

                'nullable',
                'date',
            ],

            'payment_mode' => [
                Rule::requiredIf(
                    $this->input('status') ===
                    ExpenseStatus::Paid->value
                ),

                'nullable',
                Rule::enum(PaymentMode::class),
            ],

            'vendor_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bill_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'transaction_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'max:10000',
            ],

            'internal_notes' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'receipt' => [
                'nullable',
                'file',
                'mimes:pdf,png,jpg,jpeg,webp',
                'max:10240',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    !$this->filled(
                        'expense_category_id'
                    ) ||
                    !$this->filled('scope')
                ) {
                    return;
                }

                $category = ExpenseCategory::query()
                    ->find(
                        $this->integer(
                            'expense_category_id'
                        )
                    );

                if (!$category) {
                    return;
                }

                $scope = ExpenseScope::tryFrom(
                    $this->input('scope')
                );

                if (
                    $scope &&
                    !$category->scope->allows($scope)
                ) {
                    $validator->errors()->add(
                        'expense_category_id',
                        'The selected category cannot be used for this expense type.'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tax_amount' =>
                $this->input('tax_amount', 0),

            'paid_at' =>
                $this->input('paid_at')
                ?: (
                    $this->input('status') ===
                    ExpenseStatus::Paid->value
                        ? $this->input('expense_date')
                        : null
                ),
        ]);
    }
}