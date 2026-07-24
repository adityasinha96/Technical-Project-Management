<?php

namespace App\Enums;

enum TicketCommentType: string
{
    case Discussion = 'discussion';
    case InternalNote = 'internal_note';
    case ResolutionNote = 'resolution_note';

    public function label(): string
    {
        return match ($this) {
            self::Discussion => 'Discussion',
            self::InternalNote => 'Internal Note',
            self::ResolutionNote => 'Resolution Note',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Discussion =>
                'bg-blue-50 text-blue-700',

            self::InternalNote =>
                'bg-amber-50 text-amber-700',

            self::ResolutionNote =>
                'bg-emerald-50 text-emerald-700',
        };
    }
}