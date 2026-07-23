<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategoryScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'expense-categories.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'expense_categories',
                    'name'
                )->whereNull('deleted_at'),
            ],

            'scope' => [
                'required',
                Rule::enum(
                    ExpenseCategoryScope::class
                ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:99999',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' =>
                $this->boolean('is_active'),

            'sort_order' =>
                $this->input('sort_order', 0),
        ]);
    }
}