<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case PendingClient = 'pending_client';
    case OnHold = 'on_hold';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::PendingClient => 'Pending Client',
            self::OnHold => 'On Hold',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open =>
                'bg-blue-50 text-blue-700 ring-blue-600/20',

            self::Assigned =>
                'bg-indigo-50 text-indigo-700 ring-indigo-600/20',

            self::InProgress =>
                'bg-cyan-50 text-cyan-700 ring-cyan-600/20',

            self::PendingClient =>
                'bg-amber-50 text-amber-700 ring-amber-600/20',

            self::OnHold =>
                'bg-orange-50 text-orange-700 ring-orange-600/20',

            self::Resolved =>
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

            self::Closed =>
                'bg-slate-950 text-white ring-slate-900/20',

            self::Reopened =>
                'bg-violet-50 text-violet-700 ring-violet-600/20',

            self::Cancelled =>
                'bg-red-50 text-red-700 ring-red-600/20',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Closed,
            self::Cancelled,
        ], true);
    }

    public function isCompleted(): bool
    {
        return in_array($this, [
            self::Resolved,
            self::Closed,
            self::Cancelled,
        ], true);
    }

    public function pausesSla(): bool
    {
        return in_array($this, [
            self::PendingClient,
            self::OnHold,
        ], true);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [
                self::Assigned,
                self::InProgress,
                self::PendingClient,
                self::OnHold,
                self::Resolved,
                self::Cancelled,
            ],

            self::Assigned => [
                self::InProgress,
                self::PendingClient,
                self::OnHold,
                self::Resolved,
                self::Cancelled,
            ],

            self::InProgress => [
                self::PendingClient,
                self::OnHold,
                self::Resolved,
                self::Cancelled,
            ],

            self::PendingClient => [
                self::InProgress,
                self::Resolved,
                self::Cancelled,
            ],

            self::OnHold => [
                self::InProgress,
                self::Resolved,
                self::Cancelled,
            ],

            self::Resolved => [
                self::Closed,
                self::Reopened,
            ],

            self::Closed => [
                self::Reopened,
            ],

            self::Reopened => [
                self::Assigned,
                self::InProgress,
                self::PendingClient,
                self::OnHold,
                self::Resolved,
                self::Cancelled,
            ],

            self::Cancelled => [],
        };
    }

    public function canTransitionTo(
        self $newStatus
    ): bool {
        return in_array(
            $newStatus,
            $this->allowedTransitions(),
            true
        );
    }

    public static function activeValues(): array
    {
        return [
            self::Open->value,
            self::Assigned->value,
            self::InProgress->value,
            self::PendingClient->value,
            self::OnHold->value,
            self::Reopened->value,
        ];
    }
}