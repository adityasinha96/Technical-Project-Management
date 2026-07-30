<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientPortalAlertNotification extends Notification implements
    ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $severity = 'info',
        public array $context = []
    ) {
        $this->afterCommit();
    }

    public function via(
        object $notifiable
    ): array {
        return [
            'database',
            'mail',
        ];
    }

    public function databaseType(
        object $notifiable
    ): string {
        return 'client_portal_alert';
    }

    public function toDatabase(
        object $notifiable
    ): array {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'severity' => $this->severity,
            'context' => $this->context,
        ];
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
                'Open Client Portal',
                $this->url
            );
        }

        return $mail->line(
            'This message was sent from UIPRO Project Management System.'
        );
    }
}