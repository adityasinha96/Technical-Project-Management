<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientPortalInvitationNotification extends Notification implements
    ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public string $invitationUrl
    ) {
        $this->afterCommit();
    }

    public function via(
        object $notifiable
    ): array {
        return ['mail'];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {
        return (new MailMessage)
            ->subject(
                'Invitation to UIPRO Client Portal'
            )
            ->greeting(
                "Hello {$notifiable->name},"
            )
            ->line(
                "You have been invited to access the client portal for {$this->project->name}."
            )
            ->line(
                'Through the portal, you can review project progress, approvals, shared files, payment history and project tickets according to your access permissions.'
            )
            ->action(
                'Activate Client Portal',
                $this->invitationUrl
            )
            ->line(
                'This invitation expires in seven days.'
            );
    }
}