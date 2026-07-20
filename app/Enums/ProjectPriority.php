<?php

namespace App\Enums;

enum ProjectPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            self::Medium => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            self::High => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Urgent => 'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}