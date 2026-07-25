<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Services\Notifications\NotificationPreferenceService;
use App\Support\NotificationCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class NotificationPreferenceController extends Controller
{
    public function __construct(
        private readonly NotificationPreferenceService $preferenceService
    ) {
    }

    public function edit(): View
    {
        $user = request()->user();

        return view(
            'notification-settings.edit',
            [
                'settings' =>
                    $this
                        ->preferenceService
                        ->settingsFor(
                            $user
                        ),

                'preferences' =>
                    $user
                        ->notificationPreferences()
                        ->get()
                        ->keyBy('event_key'),

                'catalog' =>
                    NotificationCatalog::grouped(),
            ]
        );
    }

    public function update(
        UpdateNotificationPreferencesRequest $request
    ): RedirectResponse {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(
            function () use (
                $user,
                $validated
            ): void {
                $user
                    ->notificationSetting()
                    ->updateOrCreate(
                        [],
                        [
                            'in_app_notifications_enabled' =>
                                $validated[
                                    'in_app_notifications_enabled'
                                ],

                            'email_notifications_enabled' =>
                                $validated[
                                    'email_notifications_enabled'
                                ],

                            'daily_digest_enabled' =>
                                $validated[
                                    'daily_digest_enabled'
                                ],

                            'daily_digest_time' =>
                                $validated[
                                    'daily_digest_time'
                                ],

                            'timezone' =>
                                $validated[
                                    'timezone'
                                ],
                        ]
                    );

                foreach (
                    $validated[
                        'preferences'
                    ] ?? []
                    as $preference
                ) {
                    $user
                        ->notificationPreferences()
                        ->updateOrCreate(
                            [
                                'event_key' =>
                                    $preference[
                                        'event_key'
                                    ],
                            ],
                            [
                                'in_app_enabled' =>
                                    $preference[
                                        'in_app_enabled'
                                    ],

                                'email_enabled' =>
                                    $preference[
                                        'email_enabled'
                                    ],

                                'include_in_daily_digest' =>
                                    $preference[
                                        'include_in_daily_digest'
                                    ],
                            ]
                        );
                }
            }
        );

        return back()->with(
            'success',
            'Notification preferences updated successfully.'
        );
    }
}