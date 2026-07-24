<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
            self::Critical => 'Critical',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Low =>
                'bg-slate-100 text-slate-700 ring-slate-500/20',

            self::Medium =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::High =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Urgent =>
                'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::Critical =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Medium => 2,
            self::High => 3,
            self::Urgent => 4,
            self::Critical => 5,
        };
    }
}