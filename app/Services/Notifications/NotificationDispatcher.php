<?php

namespace App\Services\Notifications;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationSeverity;
use App\Models\NotificationDispatch;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class NotificationDispatcher
{
    public function __construct(
        private readonly NotificationPreferenceService $preferenceService
    ) {
    }

    public function send(
        User|Collection|array $recipients,
        string $eventKey,
        string $title,
        string $message,
        ?string $url = null,
        NotificationSeverity $severity =
            NotificationSeverity::Info,
        ?Model $subject = null,
        array $context = [],
        ?array $requestedChannels = null,
        ?string $dedupeBucket = null
    ): int {
        $users = $this
            ->normaliseRecipients(
                $recipients
            );

        $sentToUsers = 0;

        foreach ($users as $user) {
            $channels =
                $this->preferenceService
                    ->channelsFor(
                        user: $user,
                        eventKey: $eventKey,
                        severity: $severity,

                        requestedChannels:
                            $requestedChannels
                    );

            if ($channels === []) {
                continue;
            }

            $batchUuid =
                (string) Str::uuid();

            $dispatchIds = [];
            $activeChannels = [];

            foreach ($channels as $channel) {
                $dedupeKey =
                    $this->dedupeKey(
                        user: $user,
                        eventKey: $eventKey,
                        channel: $channel,
                        subject: $subject,

                        bucket:
                            $dedupeBucket
                            ?: $batchUuid
                    );

                $existing =
                    NotificationDispatch::query()
                        ->where(
                            'dedupe_key',
                            $dedupeKey
                        )
                        ->first();

                if (
                    $existing
                    && in_array(
                        $existing->status,
                        [
                            NotificationDeliveryStatus::Queued,
                            NotificationDeliveryStatus::Sent,
                        ],
                        true
                    )
                ) {
                    continue;
                }

                $dispatch =
                    NotificationDispatch::updateOrCreate(
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
                                $eventKey,

                            'subject_type' =>
                                $subject
                                    ?->getMorphClass(),

                            'subject_id' =>
                                $subject
                                    ?->getKey(),

                            'channel' =>
                                $channel,

                            'status' =>
                                NotificationDeliveryStatus::Queued
                                    ->value,

                            'payload' => [
                                'title' => $title,
                                'message' => $message,
                                'url' => $url,

                                'severity' =>
                                    $severity->value,

                                'context' =>
                                    $context,
                            ],

                            'scheduled_for' =>
                                now(),

                            'sent_at' => null,
                            'failed_at' => null,
                            'error_message' => null,
                        ]
                    );

                $dispatchIds[$channel] =
                    $dispatch->id;

                $activeChannels[] =
                    $channel;
            }

            if ($activeChannels === []) {
                continue;
            }

            try {
                $user->notify(
                    new SystemAlertNotification(
                        eventKey: $eventKey,
                        title: $title,
                        message: $message,
                        url: $url,
                        severity: $severity,
                        context: $context,

                        channels:
                            $activeChannels,

                        dispatchIds:
                            $dispatchIds
                    )
                );

                $sentToUsers++;
            } catch (Throwable $exception) {
                NotificationDispatch::query()
                    ->whereIn(
                        'id',
                        array_values(
                            $dispatchIds
                        )
                    )
                    ->update([
                        'status' =>
                            NotificationDeliveryStatus::Failed
                                ->value,

                        'failed_at' => now(),

                        'error_message' =>
                            str($exception->getMessage())
                                ->limit(5000),
                    ]);

                report($exception);
            }
        }

        return $sentToUsers;
    }

    private function normaliseRecipients(
        User|Collection|array $recipients
    ): Collection {
        if ($recipients instanceof User) {
            return collect([$recipients]);
        }

        return collect($recipients)
            ->filter(
                fn ($recipient) =>
                    $recipient instanceof User
            )
            ->unique('id')
            ->values();
    }

    private function dedupeKey(
        User $user,
        string $eventKey,
        string $channel,
        ?Model $subject,
        string $bucket
    ): string {
        return hash(
            'sha256',
            implode('|', [
                $user->id,
                $eventKey,
                $channel,
                $subject?->getMorphClass()
                    ?: 'none',
                $subject?->getKey()
                    ?: 'none',
                $bucket,
            ])
        );
    }
}