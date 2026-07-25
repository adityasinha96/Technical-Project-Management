<?php

namespace App\Notifications;

use App\Enums\NotificationDeliveryStatus;
use App\Models\NotificationDispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailySummaryNotification extends Notification implements
    ShouldQueue,
    ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        public array $summary,
        public array $channels,
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

    public function databaseType(
        object $notifiable
    ): string {
        return 'digest.daily';
    }

    public function toDatabase(
        object $notifiable
    ): array {
        return [
            'event_key' => 'digest.daily',

            'title' =>
                "Daily work summary — {$this->summary['date']}",

            'message' =>
                'Your project, task, ticket and payment summary is ready.',

            'url' =>
                route('dashboard'),

            'severity' => 'info',
            'icon' => 'summary',
            'context' => $this->summary,
        ];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {
        $mail = (new MailMessage)
            ->subject(
                "UIPRO PMS Daily Summary — {$this->summary['date']}"
            )
            ->greeting(
                "Good morning {$notifiable->name},"
            )
            ->line(
                'Here is your daily operational summary.'
            )
            ->line(
                "Tasks due today: {$this->summary['tasks_due_today']}"
            )
            ->line(
                "Overdue tasks: {$this->summary['overdue_tasks']}"
            )
            ->line(
                "Projects due within three days: {$this->summary['projects_due_soon']}"
            )
            ->line(
                "Overdue projects: {$this->summary['overdue_projects']}"
            )
            ->line(
                "Assigned open tickets: {$this->summary['assigned_tickets']}"
            )
            ->line(
                "Escalated tickets: {$this->summary['escalated_tickets']}"
            )
            ->line(
                "Payment follow-ups due: {$this->summary['payment_followups_due']}"
            );

        if (
            $this->summary[
                'market_outstanding'
            ] !== null
        ) {
            $mail->line(
                'Market outstanding: ₹'
                . number_format(
                    (float)
                    $this->summary[
                        'market_outstanding'
                    ],
                    2
                )
            );
        }

        return $mail
            ->action(
                'Open Dashboard',
                route('dashboard')
            )
            ->line(
                'Please review the items requiring action.'
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
            ]);
    }
}