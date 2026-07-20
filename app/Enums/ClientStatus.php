<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Active = 'active';
    case Prospect = 'prospect';
    case Inactive = 'inactive';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Prospect => 'Prospect',
            self::Inactive => 'Inactive',
            self::Blocked => 'Blocked',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Prospect => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            self::Inactive => 'bg-slate-100 text-slate-600 ring-slate-500/20',
            self::Blocked => 'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}