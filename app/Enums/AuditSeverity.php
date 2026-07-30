<?php

namespace App\Enums;

enum AuditSeverity: string
{
    case Info = 'info';
    case Notice = 'notice';
    case Warning = 'warning';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Info => 'Information',
            self::Notice => 'Notice',
            self::Warning => 'Warning',
            self::High => 'High',
            self::Critical => 'Critical',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Info =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Notice =>
                'bg-slate-100 text-slate-700 ring-slate-600/20',

            self::Warning =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::High =>
                'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::Critical =>
                'bg-red-600 text-white ring-red-600/20',
        };
    }
}