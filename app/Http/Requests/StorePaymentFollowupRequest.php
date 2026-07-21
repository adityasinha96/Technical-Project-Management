<?php

namespace App\Http\Requests;

use App\Enums\PaymentFollowupChannel;
use App\Enums\PaymentFollowupStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentFollowupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'payments.followup'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'channel' => [
                'required',
                Rule::enum(
                    PaymentFollowupChannel::class
                ),
            ],

            'status' => [
                'required',
                Rule::enum(
                    PaymentFollowupStatus::class
                ),
            ],

            'followup_at' => [
                'required',
                'date',
            ],

            'next_followup_at' => [
                'nullable',
                'date',
                'after:followup_at',
            ],

            'promised_payment_date' => [
                'nullable',
                'date',
            ],

            'promised_amount' => [
                'nullable',
                'numeric',
                'gt:0',
                'max:999999999999.99',
            ],

            'client_contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'client_response' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where(
                        fn (Builder $query) =>
                            $query->where(
                                'status',
                                'active'
                            )
                    ),
            ],
        ];
    }
}