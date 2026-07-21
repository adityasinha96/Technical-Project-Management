<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'payments.update'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    PaymentStatus::Cleared->value,
                    PaymentStatus::Failed->value,
                    PaymentStatus::Cancelled->value,
                ]),
            ],
        ];
    }
}