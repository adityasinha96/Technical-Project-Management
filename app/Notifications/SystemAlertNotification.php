<?php

namespace App\Notifications;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationSeverity;
use App\Models\NotificationDispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification implements
    ShouldQueue,
    ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        public string $eventKey,
        public string $title,
        public string $message,
        public ?string $url,
        public NotificationSeverity $severity,
        public array $context = [],
        public array $channels = ['database'],
        public array $dispatchIds = []
    ) {
        $this->afterCommit();
    }

    public function via(
        object $notifiable
    ): array {
        return $this->channels;
    }

    public function viaConnections(): array
    {
        return [
            'database' => 'sync',

            'mail' => (string) config(
                'queue.default',
                'database'
            ),
        ];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications',
            'database' => 'notifications',
        ];
    }

    public function shouldSend(
        object $notifiable,
        string $channel
    ): bool {
        if (
            property_exists(
                $notifiable,
                'status'
            )
            && $notifiable->status !== 'active'
        ) {
            return false;
        }

        if (
            $channel === 'mail'
            && blank($notifiable->email)
        ) {
            return false;
        }

        return true;
    }

    public function toDatabase(
        object $notifiable
    ): array {
        return [
            'event_key' => $this->eventKey,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,

            'severity' =>
                $this->severity->value,

            'icon' =>
                $this->severity->icon(),

            'context' => $this->context,
        ];
    }

    public function databaseType(
        object $notifiable
    ): string {
        return $this->eventKey;
    }

    public function toMail(
        object $notifiable
    ): MailMessage {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting(
                "Hello {$notifiable->name},"
            )
            ->line($this->message);

        if ($this->url) {
            $mail->action(
                'Open in UIPRO PMS',
                $this->url
            );
        }

        if (
            $this->severity ===
            NotificationSeverity::Critical
        ) {
            $mail->line(
                'This alert requires immediate attention.'
            );
        }

        return $mail->line(
            'This is an automated notification from UIPRO Project Management System.'
        );
    }

    public function afterSending(
        object $notifiable,
        string $channel,
        mixed $response
    ): void {
        $dispatchId =
            $this->dispatchIds[$channel]
            ?? null;

        if (!$dispatchId) {
            return;
        }

        NotificationDispatch::query()
            ->whereKey($dispatchId)
            ->update([
                'status' =>
                    NotificationDeliveryStatus::Sent
                        ->value,

                'sent_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ]);
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }
}