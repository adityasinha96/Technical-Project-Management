<?php

namespace App\Enums;

enum TicketCommentVisibility: string
{
    case Internal = 'internal';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal Only',
            self::Client => 'Visible to Client',
        };
    }
}