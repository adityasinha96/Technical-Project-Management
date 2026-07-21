<?php

namespace App\Enums;

enum ApprovalStage: string
{
    case Frontend = 'frontend';
    case Backend = 'backend';

    public function label(): string
    {
        return match ($this) {
            self::Frontend => 'Frontend Approval',
            self::Backend => 'Backend Approval',
        };
    }
}