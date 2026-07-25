<?php

namespace App\Http\Requests;

use App\Support\NotificationCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'notifications.manage-preferences'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'in_app_notifications_enabled' => [
                'nullable',
                'boolean',
            ],

            'email_notifications_enabled' => [
                'nullable',
                'boolean',
            ],

            'daily_digest_enabled' => [
                'nullable',
                'boolean',
            ],

            'daily_digest_time' => [
                'required',
                'date_format:H:i',
            ],

            'timezone' => [
                'required',
                'timezone',
                'max:64',
            ],

            'preferences' => [
                'nullable',
                'array',
            ],

            'preferences.*.event_key' => [
                'required',
                'string',

                Rule::in(
                    array_keys(
                        NotificationCatalog::all()
                    )
                ),
            ],

            'preferences.*.in_app_enabled' => [
                'nullable',
                'boolean',
            ],

            'preferences.*.email_enabled' => [
                'nullable',
                'boolean',
            ],

            'preferences.*.include_in_daily_digest' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $preferences = collect(
            $this->input(
                'preferences',
                []
            )
        )
            ->map(
                fn (array $preference) => [
                    ...$preference,

                    'in_app_enabled' =>
                        filter_var(
                            $preference[
                                'in_app_enabled'
                            ] ?? false,
                            FILTER_VALIDATE_BOOLEAN
                        ),

                    'email_enabled' =>
                        filter_var(
                            $preference[
                                'email_enabled'
                            ] ?? false,
                            FILTER_VALIDATE_BOOLEAN
                        ),

                    'include_in_daily_digest' =>
                        filter_var(
                            $preference[
                                'include_in_daily_digest'
                            ] ?? false,
                            FILTER_VALIDATE_BOOLEAN
                        ),
                ]
            )
            ->values()
            ->all();

        $this->merge([
            'in_app_notifications_enabled' =>
                $this->boolean(
                    'in_app_notifications_enabled'
                ),

            'email_notifications_enabled' =>
                $this->boolean(
                    'email_notifications_enabled'
                ),

            'daily_digest_enabled' =>
                $this->boolean(
                    'daily_digest_enabled'
                ),

            'preferences' =>
                $preferences,
        ]);
    }
}