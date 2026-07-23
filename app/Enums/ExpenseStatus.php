<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Paid =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Rejected =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }
}