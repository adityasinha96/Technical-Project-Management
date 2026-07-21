<?php

namespace App\Enums;

enum PaymentKind: string
{
    case Receipt = 'receipt';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => 'Payment Received',
            self::Refund => 'Refund',
        };
    }

    public function multiplier(): int
    {
        return match ($this) {
            self::Receipt => 1,
            self::Refund => -1,
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Receipt =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Refund =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}