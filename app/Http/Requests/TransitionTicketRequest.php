<?php

namespace App\Http\Requests;

use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransitionTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.update'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(TicketStatus::class),
            ],

            'reason' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (
                Validator $validator
            ): void {
                $status =
                    TicketStatus::tryFrom(
                        $this->input(
                            'status'
                        )
                    );

                if (
                    in_array(
                        $status,
                        [
                            TicketStatus::PendingClient,
                            TicketStatus::OnHold,
                            TicketStatus::Cancelled,
                        ],
                        true
                    )
                    && !$this->filled('reason')
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'reason',
                            'A reason is required for this status.'
                        );
                }
            },
        ];
    }
}