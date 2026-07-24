<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketSlaPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.manage-sla'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'first_response_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:525600',
            ],

            'resolution_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:525600',
            ],

            'warning_before_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:10080',
            ],

            'level_two_after_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:10080',
            ],

            'level_three_after_minutes' => [
                'required',
                'integer',
                'gte:level_two_after_minutes',
                'max:43200',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' =>
                $this->boolean('is_active'),
        ]);
    }
}