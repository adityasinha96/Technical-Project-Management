<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'expenses.delete'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'void_reason' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ];
    }
}