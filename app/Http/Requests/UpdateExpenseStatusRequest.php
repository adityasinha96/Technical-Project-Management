<?php

namespace App\Http\Requests;

use App\Enums\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'expenses.update'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',

                Rule::in([
                    ExpenseStatus::Paid->value,
                    ExpenseStatus::Cancelled->value,
                ]),
            ],

            'paid_at' => [
                Rule::requiredIf(
                    $this->input('status') ===
                    ExpenseStatus::Paid->value
                ),

                'nullable',
                'date',
            ],
        ];
    }
}