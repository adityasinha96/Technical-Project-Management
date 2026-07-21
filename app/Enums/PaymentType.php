<?php

namespace App\Enums;

enum PaymentType: string
{
    case Advance = 'advance';
    case Milestone = 'milestone';
    case Partial = 'partial';
    case Final = 'final';
    case Maintenance = 'maintenance';
    case AdditionalWork = 'additional_work';
    case Refund = 'refund';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Advance => 'Advance Payment',
            self::Milestone => 'Milestone Payment',
            self::Partial => 'Partial Payment',
            self::Final => 'Final Payment',
            self::Maintenance => 'Maintenance Payment',
            self::AdditionalWork => 'Additional Work',
            self::Refund => 'Refund',
            self::Other => 'Other',
        };
    }
}