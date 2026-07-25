<?php

namespace App\Enums;

enum ReportExportStatus: string
{
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Processing =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Completed =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Failed =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}