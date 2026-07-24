<?php

namespace App\Enums;

enum WorkLogType: string
{
    case Planning = 'planning';
    case Design = 'design';
    case Development = 'development';
    case Testing = 'testing';
    case Meeting = 'meeting';
    case Research = 'research';
    case Deployment = 'deployment';
    case Support = 'support';
    case Management = 'management';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Design => 'Design',
            self::Development => 'Development',
            self::Testing => 'Testing',
            self::Meeting => 'Meeting',
            self::Research => 'Research',
            self::Deployment => 'Deployment',
            self::Support => 'Support',
            self::Management => 'Project Management',
            self::Other => 'Other',
        };
    }
}