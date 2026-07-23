<?php

namespace App\Enums;

enum ExpenseCategoryScope: string
{
    case Project = 'project';
    case Business = 'business';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Project Only',
            self::Business => 'Business Only',
            self::Both => 'Project and Business',
        };
    }

    public function allows(ExpenseScope $scope): bool
    {
        return match ($this) {
            self::Both => true,

            self::Project =>
                $scope === ExpenseScope::Project,

            self::Business =>
                $scope === ExpenseScope::Business,
        };
    }
}