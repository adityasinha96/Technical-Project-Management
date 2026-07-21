<?php

namespace App\Enums;

enum TaskStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case UnderReview = 'under_review';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::InProgress => 'In Progress',
            self::Blocked => 'Blocked',
            self::UnderReview => 'Under Review',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NotStarted =>
                'bg-slate-100 text-slate-700 ring-slate-500/20',

            self::InProgress =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Blocked =>
                'bg-red-50 text-red-700 ring-red-600/20',

            self::UnderReview =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Completed =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Cancelled =>
                'bg-slate-100 text-slate-500 ring-slate-400/20',
        };
    }
}