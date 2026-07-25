<?php

namespace App\Enums;

enum NotificationRecipientStrategy: string
{
    case ProjectManager = 'project_manager';
    case ProjectTeam = 'project_team';
    case TaskAssignee = 'task_assignee';
    case AssignedUser = 'assigned_user';
    case Accounts = 'accounts';
    case Management = 'management';
    case ManagerAndAccounts = 'manager_and_accounts';

    public function label(): string
    {
        return match ($this) {
            self::ProjectManager => 'Project Manager',
            self::ProjectTeam => 'Entire Project Team',
            self::TaskAssignee => 'Task Assignee and Manager',
            self::AssignedUser => 'Assigned User',
            self::Accounts => 'Accounts Team',
            self::Management => 'Management',
            self::ManagerAndAccounts => 'Project Manager and Accounts',
        };
    }
}