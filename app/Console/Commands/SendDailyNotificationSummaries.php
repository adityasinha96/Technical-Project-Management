<?php

namespace App\Console\Commands;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationSeverity;
use App\Models\NotificationDispatch;
use App\Models\UserNotificationSetting;
use App\Notifications\DailySummaryNotification;
use App\Services\Notifications\DailySummaryService;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendDailyNotificationSummaries extends Command
{
    protected $signature =
        'notifications:send-daily-summaries';

    protected $description =
        'Send due daily summaries using each user notification timezone and time';

    public function handle(
        DailySummaryService $summaryService,
        NotificationPreferenceService $preferenceService
    ): int {
        $sent = 0;

        UserNotificationSetting::query()
            ->where(
                'daily_digest_enabled',
                true
            )
            ->with('user')
            ->chunkById(
                100,
                function (
                    $settings
                ) use (
                    $summaryService,
                    $preferenceService,
                    &$sent
                ): void {
                    foreach ($settings as $setting) {
                        $user = $setting->user;

                        if (
                            !$user
                            || $user->status !==
                                'active'
                        ) {
                            continue;
                        }

                        $localNow = now(
                            $setting->timezone
                        );

                        $digestTime =
                            $localNow
                                ->copy()
                                ->setTimeFromTimeString(
                                    $setting
                                        ->daily_digest_time
                                );

                        $minutesDifference =
                            abs(
                                $localNow
                                    ->diffInMinutes(
                                        $digestTime,
                                        false
                                    )
                            );

                        if (
                            $minutesDifference > 14
                        ) {
                            continue;
                        }

                        if (
                            $setting
                                ->last_daily_digest_sent_on
                            ?->isSameDay($localNow)
                        ) {
                            continue;
                        }

                        DB::transaction(
                            function () use (
                                $setting,
                                $user,
                                $localNow,
                                $summaryService,
                                $preferenceService,
                                &$sent
                            ): void {
                                $locked =
                                    UserNotificationSetting::query()
                                        ->lockForUpdate()
                                        ->findOrFail(
                                            $setting->id
                                        );

                                if (
                                    $locked
                                        ->last_daily_digest_sent_on
                                    ?->isSameDay(
                                        $localNow
                                    )
                                ) {
                                    return;
                                }

                                $channels =
                                    $preferenceService
                                        ->channelsFor(
                                            user: $user,

                                            eventKey:
                                                'digest.daily',

                                            severity:
                                                NotificationSeverity::Info
                                        );

                                if ($channels === []) {
                                    $locked->update([
                                        'last_daily_digest_sent_on' =>
                                            $localNow
                                                ->toDateString(),
                                    ]);

                                    return;
                                }

                                $summary =
                                    $summaryService
                                        ->buildFor(
                                            $user
                                        );

                                $batchUuid =
                                    (string) Str::uuid();

                                $dispatchIds = [];

                                foreach (
                                    $channels
                                    as $channel
                                ) {
                                    $dedupeKey = hash(
                                        'sha256',
                                        implode('|', [
                                            $user->id,
                                            'digest.daily',
                                            $channel,

                                            $localNow
                                                ->toDateString(),
                                        ])
                                    );

                                    $dispatch =
                                        NotificationDispatch::firstOrCreate(
                                            [
                                                'dedupe_key' =>
                                                    $dedupeKey,
                                            ],
                                            [
                                                'batch_uuid' =>
                                                    $batchUuid,

                                                'user_id' =>
                                                    $user->id,

                                                'event_key' =>
                                                    'digest.daily',

                                                'channel' =>
                                                    $channel,

                                                'status' =>
                                                    NotificationDeliveryStatus::Queued
                                                        ->value,

                                                'payload' =>
                                                    $summary,

                                                'scheduled_for' =>
                                                    now(),
                                            ]
                                        );

                                    $dispatchIds[
                                        $channel
                                    ] = $dispatch->id;
                                }

                                $user->notify(
                                    new DailySummaryNotification(
                                        summary:
                                            $summary,

                                        channels:
                                            $channels,

                                        dispatchIds:
                                            $dispatchIds
                                    )
                                );

                                $locked->update([
                                    'last_daily_digest_sent_on' =>
                                        $localNow
                                            ->toDateString(),
                                ]);

                                $sent++;
                            }
                        );
                    }
                }
            );

        $this->info(
            "{$sent} daily summary notification(s) queued."
        );

        return self::SUCCESS;
    }
}   