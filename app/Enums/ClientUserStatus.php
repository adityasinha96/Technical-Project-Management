<?php

namespace App\Enums;

enum ClientUserStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Invited => 'Invited',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Revoked => 'Revoked',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Invited =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Active =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Suspended =>
                'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::Revoked =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}