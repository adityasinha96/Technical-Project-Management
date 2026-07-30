<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ClientPortalResetPasswordNotification extends ResetPassword
{
    public function toMail(
        $notifiable
    ): MailMessage {
        $url = route(
            'client.password.reset',
            [
                'token' => $this->token,
                'email' => $notifiable->email,
            ]
        );

        return (new MailMessage)
            ->subject(
                'Reset Client Portal Password'
            )
            ->greeting(
                "Hello {$notifiable->name},"
            )
            ->line(
                'A password reset was requested for your UIPRO client portal account.'
            )
            ->action(
                'Reset Password',
                $url
            )
            ->line(
                'The reset link will expire automatically.'
            )
            ->line(
                'Ignore this email when you did not request a password reset.'
            );
    }
}

