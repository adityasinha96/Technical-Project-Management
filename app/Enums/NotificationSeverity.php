<?php

namespace App\Enums;

enum NotificationSeverity: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Info => 'Information',
            self::Success => 'Success',
            self::Warning => 'Warning',
            self::Danger => 'Urgent',
            self::Critical => 'Critical',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Info =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Success =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Warning =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Danger =>
                'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::Critical =>
                'bg-red-600 text-white ring-red-600/20',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Info => 'information',
            self::Success => 'check',
            self::Warning => 'clock',
            self::Danger => 'warning',
            self::Critical => 'alert',
        };
    }
}