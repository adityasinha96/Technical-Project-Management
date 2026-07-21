<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Cleared = 'cleared';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Clearance',
            self::Cleared => 'Cleared',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Cleared =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Failed =>
                'bg-red-50 text-red-700 ring-red-600/20',

            self::Cancelled =>
                'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }

    public function affectsBalance(): bool
    {
        return $this === self::Cleared;
    }
}