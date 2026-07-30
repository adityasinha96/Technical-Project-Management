<?php

namespace App\Enums;

enum BackupStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::Running => 'Running',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Deleted => 'Deleted',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Queued =>
                'bg-blue-50 text-blue-700',

            self::Running =>
                'bg-amber-50 text-amber-700',

            self::Completed =>
                'bg-emerald-50 text-emerald-700',

            self::Failed =>
                'bg-red-50 text-red-700',

            self::Deleted =>
                'bg-slate-100 text-slate-600',
        };
    }
}