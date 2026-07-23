<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateExpenseCategoryRequest extends StoreExpenseCategoryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['name'] = [
            'required',
            'string',
            'max:255',

            Rule::unique(
                'expense_categories',
                'name'
            )
                ->ignore(
                    $this->route('expenseCategory')
                )
                ->whereNull('deleted_at'),
        ];

        return $rules;
    }
}