<?php

namespace App\Enums;

enum ActivityVisibility: string
{
    case Team = 'team';
    case Management = 'management';
    case Financial = 'financial';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Team => 'Project Team',
            self::Management => 'Management',
            self::Financial => 'Financial',
            self::Private => 'Private',
        };
    }
}