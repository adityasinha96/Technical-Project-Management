<?php

namespace App\Enums;

enum AuditCategory: string
{
    case Authentication = 'authentication';
    case Authorization = 'authorization';
    case DataChange = 'data_change';
    case Security = 'security';
    case Backup = 'backup';
    case Administration = 'administration';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Authentication => 'Authentication',
            self::Authorization => 'Authorization',
            self::DataChange => 'Data Change',
            self::Security => 'Security',
            self::Backup => 'Backup',
            self::Administration => 'Administration',
            self::System => 'System',
        };
    }
}