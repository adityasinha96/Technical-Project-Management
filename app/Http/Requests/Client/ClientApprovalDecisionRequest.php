<?php

namespace App\Http\Requests\Client;

use App\Enums\ClientApprovalDecision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ClientApprovalDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('client')->check();
    }

    public function rules(): array
    {
        return [
            'decision' => [
                'required',

                Rule::in([
                    ClientApprovalDecision::Approved
                        ->value,

                    ClientApprovalDecision::ChangesRequested
                        ->value,
                ]),
            ],

            'feedback' => [
                'nullable',
                'string',
                'max:30000',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (
                Validator $validator
            ): void {
                if (
                    $this->input('decision') ===
                    ClientApprovalDecision::ChangesRequested
                        ->value
                    && !$this->filled('feedback')
                ) {
                    $validator->errors()->add(
                        'feedback',
                        'Feedback is required when requesting changes.'
                    );
                }
            },
        ];
    }
}