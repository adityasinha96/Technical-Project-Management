<?php

namespace App\Enums;

enum BackupVerificationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Invalid = 'invalid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Verification',
            self::Valid => 'Verified',
            self::Invalid => 'Invalid',
        };
    }
}