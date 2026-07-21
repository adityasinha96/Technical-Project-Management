<?php

namespace App\Enums;

enum PaymentFollowupChannel: string
{
    case Phone = 'phone';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Meeting = 'meeting';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Phone => 'Phone Call',
            self::WhatsApp => 'WhatsApp',
            self::Email => 'Email',
            self::Meeting => 'Meeting',
            self::Other => 'Other',
        };
    }
}