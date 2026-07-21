<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case Submitted = 'submitted';
    case ChangesRequested = 'changes_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::ChangesRequested => 'Changes Requested',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Submitted =>
                'bg-cyan-50 text-cyan-700 ring-cyan-600/20',

            self::ChangesRequested =>
                'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::Approved =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Rejected =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::ChangesRequested,
            self::Approved,
            self::Rejected,
        ], true);
    }
}