<?php

namespace App\Enums;

enum ClientProjectRole: string
{
    case PrimaryContact = 'primary_contact';
    case DecisionMaker = 'decision_maker';
    case FinanceContact = 'finance_contact';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::PrimaryContact => 'Primary Contact',
            self::DecisionMaker => 'Decision Maker',
            self::FinanceContact => 'Finance Contact',
            self::Viewer => 'Viewer',
        };
    }
}