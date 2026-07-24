<?php

namespace App\Enums;

enum WorkLogStatus: string
{
    case Completed = 'completed';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::InProgress => 'In Progress',
            self::Blocked => 'Blocked',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Completed =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::InProgress =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Blocked =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}