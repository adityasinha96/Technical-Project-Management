<?php

namespace App\Enums;

enum ProjectNoteVisibility: string
{
    case Team = 'team';
    case Management = 'management';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Team => 'Project Team',
            self::Management => 'Management Only',
            self::Private => 'Private to Author',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Team =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Management =>
                'bg-violet-50 text-violet-700 ring-violet-600/20',

            self::Private =>
                'bg-slate-950 text-white ring-slate-900/20',
        };
    }
}