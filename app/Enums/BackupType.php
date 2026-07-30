<?php

namespace App\Enums;

enum BackupType: string
{
    case Database = 'database';
    case Files = 'files';
    case Full = 'full';

    public function label(): string
    {
        return match ($this) {
            self::Database => 'Database Only',
            self::Files => 'Project Files Only',
            self::Full => 'Database and Project Files',
        };
    }
}