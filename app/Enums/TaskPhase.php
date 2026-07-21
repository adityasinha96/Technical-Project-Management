<?php

namespace App\Enums;

enum TaskPhase: string
{
    case Planning = 'planning';
    case Design = 'design';
    case Frontend = 'frontend';
    case Backend = 'backend';
    case Testing = 'testing';
    case Deployment = 'deployment';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Design => 'UI/UX Design',
            self::Frontend => 'Frontend',
            self::Backend => 'Backend',
            self::Testing => 'Testing',
            self::Deployment => 'Deployment',
            self::General => 'General',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Planning => 'bg-violet-50 text-violet-700',
            self::Design => 'bg-pink-50 text-pink-700',
            self::Frontend => 'bg-blue-50 text-blue-700',
            self::Backend => 'bg-indigo-50 text-indigo-700',
            self::Testing => 'bg-amber-50 text-amber-700',
            self::Deployment => 'bg-emerald-50 text-emerald-700',
            self::General => 'bg-slate-100 text-slate-700',
        };
    }
}