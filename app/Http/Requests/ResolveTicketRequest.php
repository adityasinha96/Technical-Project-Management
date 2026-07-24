<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.resolve'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'resolution_summary' => [
                'required',
                'string',
                'min:10',
                'max:30000',
            ],

            'root_cause' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'preventive_action' => [
                'nullable',
                'string',
                'max:20000',
            ],
        ];
    }
}