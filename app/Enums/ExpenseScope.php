<?php

namespace App\Enums;

enum ExpenseScope: string
{
    case Project = 'project';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Project Expense',
            self::Business => 'General Business Expense',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Project =>
                'bg-indigo-50 text-indigo-700 ring-indigo-600/20',

            self::Business =>
                'bg-violet-50 text-violet-700 ring-violet-600/20',
        };
    }
}