<?php

namespace App\Enums;

enum PaymentFollowupStatus: string
{
    case Planned = 'planned';
    case Contacted = 'contacted';
    case Promised = 'promised';
    case NoResponse = 'no_response';
    case Disputed = 'disputed';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Contacted => 'Client Contacted',
            self::Promised => 'Payment Promised',
            self::NoResponse => 'No Response',
            self::Disputed => 'Payment Disputed',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Planned =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Contacted =>
                'bg-cyan-50 text-cyan-700 ring-cyan-600/20',

            self::Promised =>
                'bg-indigo-50 text-indigo-700 ring-indigo-600/20',

            self::NoResponse =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Disputed =>
                'bg-red-50 text-red-700 ring-red-600/20',

            self::Paid =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Cancelled =>
                'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [
            self::Paid,
            self::Cancelled,
        ], true);
    }

    public static function closedValues(): array
    {
        return [
            self::Paid->value,
            self::Cancelled->value,
        ];
    }
}