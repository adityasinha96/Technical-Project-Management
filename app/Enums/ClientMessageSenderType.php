<?php

namespace App\Enums;

enum ClientMessageSenderType: string
{
    case Client = 'client';
    case InternalUser = 'internal_user';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::InternalUser => 'UIPRO Team',
            self::System => 'System',
        };
    }
}