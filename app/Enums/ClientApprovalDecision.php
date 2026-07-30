<?php

namespace App\Enums;

enum ClientApprovalDecision: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case ChangesRequested = 'changes_requested';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting Client',
            self::Approved => 'Approved by Client',
            self::ChangesRequested => 'Changes Requested',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Approved =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::ChangesRequested =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }
}