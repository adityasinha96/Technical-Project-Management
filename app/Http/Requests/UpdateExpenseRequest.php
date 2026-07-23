<?php

namespace App\Http\Requests;

class UpdateExpenseRequest extends StoreExpenseRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'expenses.update'
        ) ?? false;
    }
}