<?php

namespace App\Enums;

enum ProjectNoteType: string
{
    case General = 'general';
    case Requirement = 'requirement';
    case Decision = 'decision';
    case ClientFeedback = 'client_feedback';
    case Risk = 'risk';
    case Credentials = 'credentials';
    case Financial = 'financial';
    case Handover = 'handover';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General Note',
            self::Requirement => 'Requirement',
            self::Decision => 'Decision',
            self::ClientFeedback => 'Client Feedback',
            self::Risk => 'Risk or Blocker',
            self::Credentials => 'Credentials or Access',
            self::Financial => 'Financial Note',
            self::Handover => 'Handover Information',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::General =>
                'bg-slate-100 text-slate-700 ring-slate-500/20',

            self::Requirement =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Decision =>
                'bg-violet-50 text-violet-700 ring-violet-600/20',

            self::ClientFeedback =>
                'bg-cyan-50 text-cyan-700 ring-cyan-600/20',

            self::Risk =>
                'bg-red-50 text-red-700 ring-red-600/20',

            self::Credentials =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::Financial =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Handover =>
                'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        };
    }
}