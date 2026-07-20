<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case NewProject = 'new';
    case Planning = 'planning';

    case FrontendInProgress = 'frontend_in_progress';
    case FrontendSubmitted = 'frontend_submitted';
    case FrontendRevision = 'frontend_revision';
    case FrontendApproved = 'frontend_approved';

    case BackendInProgress = 'backend_in_progress';
    case BackendSubmitted = 'backend_submitted';
    case BackendRevision = 'backend_revision';
    case BackendApproved = 'backend_approved';

    case Deployment = 'deployment';
    case Completed = 'completed';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NewProject => 'New Project',
            self::Planning => 'Planning',
            self::FrontendInProgress => 'Frontend in Progress',
            self::FrontendSubmitted => 'Frontend Submitted',
            self::FrontendRevision => 'Frontend Revision',
            self::FrontendApproved => 'Frontend Approved',
            self::BackendInProgress => 'Backend in Progress',
            self::BackendSubmitted => 'Backend Submitted',
            self::BackendRevision => 'Backend Revision',
            self::BackendApproved => 'Backend Approved',
            self::Deployment => 'Final Deployment',
            self::Completed => 'Completed',
            self::OnHold => 'On Hold',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NewProject => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            self::Planning => 'bg-violet-50 text-violet-700 ring-violet-600/20',

            self::FrontendInProgress,
            self::BackendInProgress => 'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::FrontendSubmitted,
            self::BackendSubmitted => 'bg-cyan-50 text-cyan-700 ring-cyan-600/20',

            self::FrontendRevision,
            self::BackendRevision => 'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::FrontendApproved,
            self::BackendApproved => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Deployment => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            self::Completed => 'bg-green-50 text-green-700 ring-green-600/20',
            self::OnHold => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Cancelled => 'bg-red-50 text-red-700 ring-red-600/20',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [
            self::Completed,
            self::Cancelled,
        ], true);
    }

    public static function closedValues(): array
    {
        return [
            self::Completed->value,
            self::Cancelled->value,
        ];
    }
}