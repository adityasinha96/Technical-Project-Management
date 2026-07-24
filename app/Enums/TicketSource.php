<?php

namespace App\Enums;

enum TicketSource: string
{
    case Internal = 'internal';
    case ClientCall = 'client_call';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Meeting = 'meeting';
    case Website = 'website';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal Team',
            self::ClientCall => 'Client Phone Call',
            self::WhatsApp => 'WhatsApp',
            self::Email => 'Email',
            self::Meeting => 'Meeting',
            self::Website => 'Website',
            self::Other => 'Other',
        };
    }
}