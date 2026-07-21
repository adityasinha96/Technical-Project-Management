<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'payments.delete'
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