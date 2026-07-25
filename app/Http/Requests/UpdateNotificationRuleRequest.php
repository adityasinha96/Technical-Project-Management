<?php

namespace App\Http\Requests;

use App\Enums\NotificationRecipientStrategy;
use App\Enums\NotificationSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'notifications.manage-rules'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'severity' => [
                'required',

                Rule::enum(
                    NotificationSeverity::class
                ),
            ],

            'recipient_strategy' => [
                'required',

                Rule::enum(
                    NotificationRecipientStrategy::class
                ),
            ],

            'channels' => [
                'required',
                'array',
                'min:1',
            ],

            'channels.*' => [
                'required',
                Rule::in([
                    'database',
                    'mail',
                ]),
            ],

            'lead_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:525600',
            ],

            'repeat_minutes' => [
                'required',
                'integer',
                'min:15',
                'max:525600',
            ],

            'maximum_occurrences' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],

            'is_enabled' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_enabled' =>
                $this->boolean(
                    'is_enabled'
                ),
        ]);
    }
}