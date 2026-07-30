<?php

namespace App\Http\Requests\Admin;

use App\Enums\SecurityIncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveSecurityIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'security.manage-incidents'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',

                Rule::in([
                    SecurityIncidentStatus::Acknowledged
                        ->value,

                    SecurityIncidentStatus::Resolved
                        ->value,

                    SecurityIncidentStatus::Dismissed
                        ->value,
                ]),
            ],

            'resolution_notes' => [
                'required',
                'string',
                'max:20000',
            ],

            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists(
                    'users',
                    'id'
                ),
            ],
        ];
    }
}