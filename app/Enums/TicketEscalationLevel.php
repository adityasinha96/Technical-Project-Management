<?php

namespace App\Enums;

enum TicketEscalationLevel: int
{
    case Warning = 1;
    case Overdue = 2;
    case Critical = 3;

    public function label(): string
    {
        return match ($this) {
            self::Warning => 'SLA Warning',
            self::Overdue => 'SLA Overdue',
            self::Critical => 'Critical Escalation',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Warning =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Overdue =>
                'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::Critical =>
                'bg-red-600 text-white ring-red-600/20',
        };
    }
}