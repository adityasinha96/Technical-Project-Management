<?php

namespace App\Enums;

enum SecurityIncidentStatus: string
{
    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Acknowledged => 'Acknowledged',
            self::Resolved => 'Resolved',
            self::Dismissed => 'Dismissed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open =>
                'bg-red-50 text-red-700 ring-red-600/20',

            self::Acknowledged =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Resolved =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Dismissed =>
                'bg-slate-100 text-slate-700 ring-slate-600/20',
        };
    }
}