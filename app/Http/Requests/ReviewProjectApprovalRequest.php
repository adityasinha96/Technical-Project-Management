<?php

namespace App\Http\Requests;

use App\Enums\ApprovalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewProjectApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'approvals.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    ApprovalStatus::Approved->value,
                    ApprovalStatus::ChangesRequested->value,
                    ApprovalStatus::Rejected->value,
                ]),
            ],

            'client_reviewer_name' => [
                Rule::requiredIf(
                    $this->input('status') ===
                    ApprovalStatus::Approved->value
                ),
                'nullable',
                'string',
                'max:255',
            ],

            'client_reviewer_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'client_reviewer_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'client_remarks' => [
                Rule::requiredIf(
                    in_array(
                        $this->input('status'),
                        [
                            ApprovalStatus::ChangesRequested->value,
                            ApprovalStatus::Rejected->value,
                        ],
                        true
                    )
                ),
                'nullable',
                'string',
                'max:10000',
            ],

            'internal_remarks' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ];
    }
}