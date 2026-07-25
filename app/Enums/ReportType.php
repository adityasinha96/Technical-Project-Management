<?php

namespace App\Enums;

enum ReportType: string
{
    case Projects = 'projects';
    case TeamPerformance = 'team_performance';
    case Collections = 'collections';
    case Profitability = 'profitability';
    case TicketSla = 'ticket_sla';

    public function label(): string
    {
        return match ($this) {
            self::Projects => 'Project Analytics',
            self::TeamPerformance => 'Team Performance',
            self::Collections => 'Collection Report',
            self::Profitability => 'Profitability Report',
            self::TicketSla => 'Ticket SLA Report',
        };
    }

    public function filenamePrefix(): string
    {
        return match ($this) {
            self::Projects => 'project-analytics',
            self::TeamPerformance => 'team-performance',
            self::Collections => 'collection-report',
            self::Profitability => 'profitability-report',
            self::TicketSla => 'ticket-sla-report',
        };
    }

    public function containsFinancialData(): bool
    {
        return in_array($this, [
            self::Collections,
            self::Profitability,
        ], true);
    }
}