<?php

namespace App\Notifications;

use App\Models\SecurityIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityIncidentNotification extends Notification implements
    ShouldQueue
{
    use Queueable;

    public function __construct(
        public SecurityIncident $incident
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
        return 'security.incident';
    }

    public function toDatabase(
        object $notifiable
    ): array {
        return [
            'event_key' =>
                'security.incident',

            'title' =>
                $this->incident->title,

            'message' =>
                $this->incident
                    ->description,

            'url' =>
                route(
                    'security.incidents.show',
                    $this->incident
                ),

            'severity' =>
                $this->incident
                    ->severity
                    ->value,

            'context' => [
                'incident_uuid' =>
                    $this->incident
                        ->incident_uuid,

                'incident_type' =>
                    $this->incident
                        ->incident_type
                        ->value,
            ],
        ];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {
        return (new MailMessage)
            ->subject(
                "Security Alert: {$this->incident->title}"
            )
            ->greeting(
                "Hello {$notifiable->name},"
            )
            ->line(
                $this->incident
                    ->description
            )
            ->line(
                "Severity: {$this->incident->severity->label()}"
            )
            ->action(
                'Review Security Incident',
                route(
                    'security.incidents.show',
                    $this->incident
                )
            )
            ->line(
                'Please review and acknowledge this incident in the administrative control centre.'
            );
    }
}