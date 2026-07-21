<?php

namespace App\Http\Requests;

use App\Enums\ApprovalStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitProjectApprovalRequest extends FormRequest
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
            'stage' => [
                'required',
                Rule::enum(ApprovalStage::class),
            ],

            'submission_notes' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'internal_remarks' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'proof' => [
                'nullable',
                'file',
                'mimes:pdf,png,jpg,jpeg,webp',
                'max:10240',
            ],
        ];
    }
}