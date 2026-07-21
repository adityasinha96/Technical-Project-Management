<?php

namespace App\Http\Requests;

use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'payments.create'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'kind' => [
                'required',
                Rule::enum(PaymentKind::class),
            ],

            'payment_type' => [
                'required',
                Rule::enum(PaymentType::class),
            ],

            'payment_mode' => [
                'required',
                Rule::enum(PaymentMode::class),
            ],

            'status' => [
                'required',
                Rule::in([
                    PaymentStatus::Pending->value,
                    PaymentStatus::Cleared->value,
                ]),
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999999.99',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'expected_clearance_date' => [
                Rule::requiredIf(
                    $this->input('status') ===
                    PaymentStatus::Pending->value
                ),
                'nullable',
                'date',
                'after_or_equal:payment_date',
            ],

            'received_from' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bank_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'transaction_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'proof' => [
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
                $kind = $this->input('kind');
                $type = $this->input('payment_type');

                if (
                    $kind === PaymentKind::Refund->value &&
                    $type !== PaymentType::Refund->value
                ) {
                    $validator->errors()->add(
                        'payment_type',
                        'Refund entries must use the Refund payment type.'
                    );
                }

                if (
                    $kind === PaymentKind::Receipt->value &&
                    $type === PaymentType::Refund->value
                ) {
                    $validator->errors()->add(
                        'payment_type',
                        'A received payment cannot use the Refund payment type.'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        if (
            $this->input('kind') ===
            PaymentKind::Refund->value
        ) {
            $this->merge([
                'payment_type' =>
                    PaymentType::Refund->value,
            ]);
        }
    }
}