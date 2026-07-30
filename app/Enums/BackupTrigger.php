<?php

namespace App\Enums;

enum BackupTrigger: string
{
    case Manual = 'manual';
    case Scheduled = 'scheduled';
    case PreDeployment = 'pre_deployment';
    case PreMigration = 'pre_migration';

    public function label(): string
    {
        return str($this->value)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}