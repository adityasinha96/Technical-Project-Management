<?php

namespace App\Services\Notifications;

use App\Enums\NotificationSeverity;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Models\UserNotificationSetting;
use App\Support\NotificationCatalog;

class NotificationPreferenceService
{
    public function settingsFor(
        User $user
    ): UserNotificationSetting {
        return $user
            ->notificationSetting()
            ->firstOrCreate([], [
                'timezone' => 'Asia/Kolkata',
                'daily_digest_time' => '08:30:00',
            ]);
    }

    public function preferenceFor(
        User $user,
        string $eventKey
    ): ?UserNotificationPreference {
        return $user
            ->notificationPreferences()
            ->where(
                'event_key',
                $eventKey
            )
            ->first();
    }

    public function channelsFor(
        User $user,
        string $eventKey,
        NotificationSeverity $severity,
        ?array $requestedChannels = null
    ): array {
        if ($user->status !== 'active') {
            return [];
        }

        $definition =
            NotificationCatalog::get(
                $eventKey
            );

        $channels =
            $requestedChannels
            ?: $definition['channels'];

        $settings =
            $this->settingsFor($user);

        $preference =
            $this->preferenceFor(
                $user,
                $eventKey
            );

        return collect($channels)
            ->filter(
                function (
                    string $channel
                ) use (
                    $user,
                    $settings,
                    $preference,
                    $severity
                ): bool {
                    if ($channel === 'database') {
                        /*
                         * Critical operational alerts
                         * always remain visible in-app.
                         */
                        if (
                            $severity ===
                            NotificationSeverity::Critical
                        ) {
                            return true;
                        }

                        return
                            $settings
                                ->in_app_notifications_enabled
                            && (
                                $preference
                                    ?->in_app_enabled
                                ?? true
                            );
                    }

                    if ($channel === 'mail') {
                        return
                            filled($user->email)
                            && $settings
                                ->email_notifications_enabled
                            && (
                                $preference
                                    ?->email_enabled
                                ?? true
                            );
                    }

                    return false;
                }
            )
            ->unique()
            ->values()
            ->all();
    }

    public function includeInDigest(
        User $user,
        string $eventKey
    ): bool {
        $preference =
            $this->preferenceFor(
                $user,
                $eventKey
            );

        if ($preference) {
            return $preference
                ->include_in_daily_digest;
        }

        return (bool) (
            NotificationCatalog::get(
                $eventKey
            )['digest'] ?? true
        );
    }
}